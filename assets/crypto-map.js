const BfxCryptoUtils = {
  debounce: function(func, timeout = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
  }
}


const BfxCryptoConstants = {
  availableStyles: [
    {
      id: 'clo4gke3m00ey01qx79wmdtot',
      key: 'Default'
    },
    {
      id: 'clt720hfv003f01qs48vb8130',
      key: 'Dark'
    },
    {
      id: 'clt7219a300xe01pia01yg7h7',
      key: 'Light'
    },
    {
      id: 'clt71wf0g000u01qraqmabdh4',
      key: 'Satellite'
    },
    {
      id: 'clt722n01004d01qv9kc5egwk',
      key: 'Contour'
    },
  ],
  localstorageStyleKey: 'mapbox_style_key',
}

function BfxCryptoMap(configuration) {
  const {
    isMobile,
    assetUrl,
    mapboxKey,
    mapboxUsername,
    merchantDataUrl,
    containerId = 'bfx-crypto-map',
    translations,
  } = configuration;

  this.mapboxUsername = mapboxUsername
  this.isMobile = isMobile;
  this.assetUrl = assetUrl;
  this.mapboxKey = mapboxKey;
  this.merchantDataUrl = merchantDataUrl;
  this.containerId = containerId;
  this.translations = translations;

  this.currentPin = null;
  this.needRelocate = false;
  this.MERCHANT_DATA = [];
  this.logoPlaceholder = assetUrl + '/placeholder.png';
  this.tokenMap = {
    BTC: {
      name: 'BTC Lightning',
      width: 25,
      height: 22,
      icon: assetUrl + '/BTC.png',
    },
    UST: {
      name: 'USDt',
      width: 22,
      height: 22,
      icon: assetUrl + '/UST.png',
    },
    LVGA: {
      name: 'LVGA',
      width: 22,
      height: 22,
      icon: assetUrl + '/LVGA.png',
    },
  };

  this.activeStyle = localStorage.getItem(BfxCryptoConstants.localstorageStyleKey) || BfxCryptoConstants.availableStyles[0].id
}

BfxCryptoMap.prototype.translate = function(text) {
  return this.translations[text] || text;
}

BfxCryptoMap.prototype.setup = function() {
  const self = this;
  const map = L
    .map(this.containerId, {
      zoomControl: false,
      maxZoom: 19,
    })
    .setView([46.005314, 8.953802], 17);

  map.attributionControl.setPrefix('© <a href="https://www.mapbox.com/feedback/">Mapbox</a> © <a href="https://leafletjs.com" title="A JavaScript library for interactive maps">Leaflet</a>');

  map.on('locationfound', this.onLocationFound.bind(self));

  const gl = L
    .mapboxGL({
      style: 'mapbox://styles/' + this.mapboxUsername  + '/' + this.activeStyle,
      accessToken: this.mapboxKey,
    })
    .addTo(map);

  const markerIcon = L.icon({
    iconUrl: this.assetUrl + '/marker-pin-inactive.png',
    iconSize: [21, 27],
    iconAnchor: [10, 27],
    popupAnchor: [1, -20],
  });

  const activeMarkerIcon = L.icon({
    iconUrl: this.assetUrl + '/marker-pin-active.png',
    iconSize: [21, 27],
    iconAnchor: [10, 27],
    popupAnchor: [1, -20],
  });

  const currentPinIcon = L.icon({
    iconUrl: this.assetUrl + '/current-pin.png',
    iconSize: [32, 32],
    iconAnchor: [16, 16],
  });

  const markerGroup = L.markerClusterGroup({ disableClusteringAtZoom: 19 });

  // Start Setup current position control
  L.Control.CurrentPosition = L.Control.extend({
    onAdd: function(map) {
      const control = L.DomUtil.create('div', 'leaflet-bar current-pin-area');
      const button = L.DomUtil.create('a', 'current-pin-btn', control);
      const img = L.DomUtil.create('img', '', button);

      L.DomEvent.on(
        button,
        'click',
        function () {
          self.map.locate({ watch: true });
          if (self.currentPin) {
            const { lat, lng } = self.currentPin.getLatLng();
            self.map.flyTo([lat, lng], 17);
          } else {
            // wait until first time located
            self.needRelocate = true;
          }
        },
      );

      img.src = self.assetUrl + '/location.png';
      img.style.width = '18px';
      img.style.height = '18px';

      return control;
    },

    onRemove: function(map) {
      // Nothing to do here
    }
  });

  L.control.currentPosition = function(opts) {
    return new L.Control.CurrentPosition(opts);
  }
  // End Setup current position control

  if (!this.isMobile) {
    L.control.zoom({ position: 'bottomright' }).addTo(map);
  }

  L.control.currentPosition({ position: 'bottomright' }).addTo(map);

  this.map = map;
  this.gl = gl;
  this.markerGroup = markerGroup;
  this.markerIcon = markerIcon;
  this.activeMarkerIcon = activeMarkerIcon;
  this.currentPinIcon = currentPinIcon;

  this.setupListener();
}

BfxCryptoMap.prototype.setupListener = function() {
  const self = this;
  jQuery('#bfx-crypto-search-input').keyup(BfxCryptoUtils.debounce(function() {
    self.filterMarkers();
    BfxCryptoMap.hideAllBfxCryptoPopup();
    self.showStoreListPopup();
  }, 300));

  jQuery('#bfx-crypto-filter-form .filter-checkbox input')
    .on('change', self.filterMarkers.bind(self));

  jQuery('#bfx-crypto-store-list-btn, #bfx-crypto-store-list-mobile-btn').on('click', function () {
    const isActive = jQuery('#bfx-crypto-store-list-popup').hasClass('active');
    BfxCryptoMap.hideAllBfxCryptoPopup();
    if (!isActive) {
      self.showStoreListPopup();
    }
  });

  jQuery('#bfx-crypto-filter-btn').on('click', function () {
    const isActive = jQuery('#bfx-crypto-filter-popup').hasClass('active');
    BfxCryptoMap.hideAllBfxCryptoPopup();
    if (!isActive) {
      const left = self.isMobile ? '8px' : '166px'
      BfxCryptoMap.showBfxCryptoPopup('#bfx-crypto-filter-popup', left);
    }
  });


  jQuery('#bfx-crypto-layer-select-btn, #bfx-crypto-layer-select-mobile-btn').on('click', function () {
    const isActive = jQuery('#bfx-crypto-layer-popup').hasClass('active');
    BfxCryptoMap.hideAllBfxCryptoPopup();
    if (!isActive) {
      self.showLayersPopup();
    }
  });


  jQuery('#bfx-crypto-popup-overlay').on('click', function () {
    BfxCryptoMap.hideAllBfxCryptoPopup();
  });

  jQuery('#bfx-crypto-clear-filter-btn').on('click', function () {
    jQuery('.filter-checkbox input')
      .each(function() {
        jQuery(this).prop('checked', false);
      });
    self.filterMarkers();
  });
}

BfxCryptoMap.prototype.clearMarkers = function() {
  if (this.markerGroup) {
    this.markerGroup.clearLayers();
  }
}

BfxCryptoMap.prototype.onLocationFound = function(ev) {
  const { latitude, longitude } = ev;
  if (this.currentPin) {
    this.map.removeLayer(this.currentPin);
  }
  this.currentPin = L
    .marker(
      [latitude, longitude],
      {
        icon: this.currentPinIcon,
      },
    )
    .addTo(this.map);

  if (this.needRelocate) {
    this.needRelocate = false;
    this.map.flyTo([latitude, longitude], 17);
  }
}

BfxCryptoMap.prototype.renderMarkers = function(data) {
  const self = this;
  this.clearMarkers();
  const popupOptions = {
    autoPanPadding: L.point(70, 70),
    maxWidth: 340,
    minWidth: 340,
    closeButton: false,
  };

  const markers = data
    .filter(function(merchant) {
      return merchant.lat && merchant.lng;
    })
    .map(function(merchant) {
      const marker = L
        .marker(
          [merchant.lat, merchant.lng],
          {
            merchantId: merchant.id,
            icon: self.markerIcon,
          },
        )
        .on('click', self.onMarkerClick.bind(self));

      if (!self.isMobile) {
        marker.bindPopup('', popupOptions);
      }

      return marker;
    });

  this.markerGroup.addLayers(markers);
  this.map.addLayer(this.markerGroup);
}

BfxCryptoMap.prototype.fetchData = function() {
  const self = this;
  jQuery
    .ajax({ url: this.merchantDataUrl })
    .done(function(data) {
      self.MERCHANT_DATA = data;
      self.renderMarkers(data);
    });
}

BfxCryptoMap.prototype.onMarkerClick = function(e) {
  const self = this;
  const merchant = self.MERCHANT_DATA.find(function (merchant) {
    return merchant.id === e.target.options.merchantId;
  });

  if (merchant) {
    e.target.setIcon(self.activeMarkerIcon);

    const tags = (merchant.tags || []).map(function (tag) {
      const tag_name = jQuery('#bfx_filter_' + tag).next().html() || tag;
      return '<span class="tag">' + tag_name + '</span>';
    }).join('');
    const tokens = (merchant.accepted_cryptos || []).map(function (token) {
      const tokenInfo = self.tokenMap[token];
      if (tokenInfo) {
        const img = '<img src="' + tokenInfo.icon + '" width="' + tokenInfo.width +'" height="' + tokenInfo.height + '" />';
        const label = '<span>' + tokenInfo.name + '</span>';
        return '<div class="token">' + img + label + '</div>';
      }
      return '';
    }).join('');

    const logoUrl = merchant.logo_url || self.logoPlaceholder;
    const logo = '<img src="' + logoUrl + '" width="44" height="44" />';
    const titleStr = merchant.title || '';
    const title = '<h3>' + titleStr + '</h3>' + tags;
    const description = merchant.address ? '<p>' + merchant.address + '</p>' : '';
    const website = merchant.website
      ? '<a href="' + merchant.website + '" target="_blank"><img src="' + self.assetUrl + '/globe.png" /></a>'
      : '';

    const latLng = merchant.lat + ',' + merchant.lng;
    const direction = '<a href="https://maps.google.com/?q=' + latLng +'" target="_blank"><img src="' + self.assetUrl + '/direction.png" /></a>';
    const websiteInner = website + direction;

    const popupTemplate = document.getElementById('bfx-crypto-popup-template');
    popupTemplate.querySelector('.logo').innerHTML = logo;
    popupTemplate.querySelector('.title').innerHTML = title;
    popupTemplate.querySelector('.description').innerHTML = description;
    popupTemplate.querySelector('.tokens').innerHTML = tokens;
    popupTemplate.querySelector('.website').innerHTML = websiteInner;

    const popup = self.setPopupContent(e, popupTemplate.innerHTML);

    popup.on('remove', function () {
      // silly work-around to avoid race-condition made by leaflet marker cluster
      setTimeout(function () {
        e.target.setIcon(self.markerIcon);
      }, 1000);
    });
  }
}

BfxCryptoMap.prototype.setPopupContent = function(e, content) {
  const markerPopup = e.target.getPopup();

  if (markerPopup) {
    return markerPopup.setContent(content);
  }

  const width = this.map.getSize().x - 60; // 60 is the padding of the popup
  const bounds = this.map.getBounds();
  const center = bounds.getCenter();
  const south = bounds.getSouth();
  const popupLatLng = L.latLng(south, center.lng);

  return L
    .popup({
      autoPan: false,
      closeButton: false,
      maxWidth: width,
      minWidth: width,
      className: 'bfx-mobile-popup',
      keepInView: true,
    })
    .setLatLng(popupLatLng)
    .setContent(content)
    .openOn(this.map);
}

BfxCryptoMap.prototype.getFilterData = function() {
  const searchValue = jQuery('#bfx-crypto-search-input').val().toLowerCase().trim();
  const formValues = jQuery('#bfx-crypto-filter-form').serializeArray();
  const categories = formValues
    .filter(function (item) {
      return item.name === 'category';
    })
    .map(function (item) {
      return item.value;
    });
  const acceptedCryptos = formValues
    .filter(function (item) {
      return item.name === 'accepted_cryptos';
    })
    .map(function (item) {
      return item.value;
    });

  const numberOfFilter = categories.length + acceptedCryptos.length;

  const filteredData = this.MERCHANT_DATA.filter(function (merchant) {
    const matchedSearch = !searchValue || searchValue === '' || merchant.title.toLowerCase().includes(searchValue);
    const hasCategory = categories.length === 0 || categories.some(function (category) {
      return (merchant.tags || []).includes(category);
    });
    const hasAcceptedCryptos = acceptedCryptos.length === 0 || acceptedCryptos.some(function (token) {
      return (merchant.accepted_cryptos || []).includes(token);
    });
    return matchedSearch && hasCategory && hasAcceptedCryptos;
  });

  return {
    numberOfFilter,
    searchValue,
    filteredData,
  };
}

BfxCryptoMap.prototype.filterMarkers = function() {
  const filterData = this.getFilterData();
  const numberOfFilter = filterData.numberOfFilter;
  const filteredData = filterData.filteredData;

  if (numberOfFilter > 0) {
    jQuery('#filter-number').html(numberOfFilter + '').addClass('active');
    jQuery('.bfx-crypto-filter-clear-all').removeClass('hidden');
  } else {
    jQuery('#filter-number').html('').removeClass('active');
    jQuery('.bfx-crypto-filter-clear-all').addClass('hidden');
  }

  this.renderMarkers(filteredData);

  if (jQuery('#bfx-crypto-store-list-popup').hasClass('active')) {
    this.showStoreList(filteredData);
  }
}

BfxCryptoMap.prototype.showStoreList = function(filteredData) {
  const self = this
  if (!filteredData || filteredData.length === 0) {
    const html = '<div class="center">' + this.translate('no_store') + '</div>';
    jQuery('#bfx-crypto-store-list-popup .filter-container').html(html);
    return;
  }

  const list = filteredData.map(function(merchant) {
    const logoUrl = merchant.logo_url || self.logoPlaceholder;
    const logo = '<img src="' + logoUrl + '" width="32" height="32" />';
    const titleStr = '<div class="bfx-crypto-title">' + merchant.title + '</div>';
    const description = merchant.address ? '<p>' + merchant.address + '</p>' : '';
    const right = '<div>' + titleStr + description + '</div>';
    const inner = logo + right;
    return '<li class="merchant-item" data-merchant-id="' + merchant.id + '">' + inner +'</li>';
  });
  const html = '<ul>' + list.join('') + '</ul';

  jQuery('#bfx-crypto-store-list-popup .filter-container').html(html);

  jQuery('#bfx-crypto-store-list-popup .filter-container .merchant-item')
    .on('click', function(e) {
      const merchantId = jQuery(this).data('merchant-id');
      self.storeClick(merchantId);
    })
}

BfxCryptoMap.prototype.storeClick = function(merchantId) {
  const markers = this.markerGroup.getLayers();
  const foundMarker = markers.find(function(marker) {
    return marker?.options?.merchantId === merchantId;
  });

  if (foundMarker) {
    BfxCryptoMap.hideAllBfxCryptoPopup();
    const latLngs = [ foundMarker.getLatLng() ];
    const markerBounds = L.latLngBounds(latLngs);
    this.map.fitBounds(markerBounds);
    setTimeout(() => {
      foundMarker.fire('click');
    }, 500);
  }
}

BfxCryptoMap.prototype.getVisibleMarkers = function() {
  const bounds = this.map.getBounds();
  const markers = this.markerGroup.getLayers();
  return markers.filter(function(marker) {
    return bounds.contains(marker.getLatLng());
  });
}

BfxCryptoMap.prototype.showStoreListPopup = function() {
  const filterData = this.getFilterData();
  const { filteredData, numberOfFilter, searchValue } = filterData;

  // TODO: add this filter in the future
  const showInbound = false
  if (showInbound) {
    const inboundList = this.getVisibleMarkers()
    const merchantIds = inboundList.map(function(marker) {
      return marker?.options?.merchantId;
    });
    const inboundMerchant = MERCHANT_DATA.filter((merchant) => merchantIds.includes(merchant.id));
    this.showStoreList(inboundMerchant);
  } else {
    this.showStoreList(filteredData);
  }
  BfxCryptoMap.showBfxCryptoPopup('#bfx-crypto-store-list-popup');
}

BfxCryptoMap.prototype.showLayersPopup = function() {
  const self = this;
  const activeStyle = this.activeStyle;
  const listItems = BfxCryptoConstants.availableStyles.map(({ id, key }) => {
    const icon = id === activeStyle ? 'radio-active.png' : 'radio-inactive.png';
    const img = `<img width="18" height="18" src="${self.assetUrl}/${icon}" />`;
    const label = `<label>${key}</label>`;

    return `
      <div class="filter-checkbox" data-id="${id}">
        ${img}
        ${label}
      </div>
    `
  });

  jQuery('#bfx-crypto-layer-popup .filter-content').html(listItems.join(''));

  jQuery('#bfx-crypto-layer-popup .filter-checkbox')
    .on('click', function(e) {
      const id = jQuery(this).data('id');
      self.changeLayer(id);
      BfxCryptoMap.hideAllBfxCryptoPopup();
    })

  BfxCryptoMap.showBfxCryptoPopup('#bfx-crypto-layer-popup', '', '8px');
}

BfxCryptoMap.prototype.changeLayer = function(id) {
  this.activeStyle = id;
  localStorage.setItem(BfxCryptoConstants.localstorageStyleKey, id);
  const mapbox = this.gl.getMapboxMap();
  mapbox.setStyle('mapbox://styles/' + this.mapboxUsername  + '/' + this.activeStyle);

}

// static functions
BfxCryptoMap.hideAllBfxCryptoPopup = function() {
  jQuery('#bfx-crypto-filter-popup').removeClass('active');
  jQuery('#bfx-crypto-store-list-popup').removeClass('active');
  jQuery('#bfx-crypto-layer-popup').removeClass('active');
  jQuery('#bfx-crypto-popup-overlay').removeClass('active');
}

BfxCryptoMap.showBfxCryptoPopup = function(selector, left = '', right = '') {
  jQuery(selector).addClass('active').css('left', left).css('right', right);
  jQuery('#bfx-crypto-popup-overlay').addClass('active');
}
