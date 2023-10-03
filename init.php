<?php
/*
Plugin Name: BFX crypto map
Plugin URI: https://bitfinex.com
description: BFX crypto map
Version: 1.1.6
Author: BFX
Author URI: https://bitfinex.com
License: GPL2
*/

include_once(plugin_dir_path( __FILE__ ) . './translations.php');

// [bfx_crypto_map width="100%" height="100%" mode="desktop"]
function bfx_crypto_map_handler( $atts ) {
  $mapped_atts = shortcode_atts( array(
    'width' => '500px',
    'height' => '500px',
    'mobile_width' => '100%',
    'mobile_height' => 'calc(100vh - 100px)',
    'lang' => 'en',
  ), $atts);


  $map_w = $mapped_atts['width'];
  $map_h = $mapped_atts['height'];
  $lang = $mapped_atts['lang'];
  $map_mobile_w = $mapped_atts['mobile_width'];
  $map_mobile_h = $mapped_atts['mobile_height'];
  $merchants_data_url = plugin_dir_url(__FILE__) . 'assets/merchants.json';
  $asset_url = plugin_dir_url(__FILE__) . 'assets';

  $translator = new BfxTranslations($lang);

  $html = <<<HTML
  <div class="bfx-crypto-container">
    <div class="bfx-crypto-filter">
      <div class="bfx-crypto-filter-bar bfx-crypto-filter-box">
        <div class="search-container">
          <img src="$asset_url/search.png" width="14" height="13" />
          <input id="bfx-crypto-search-input" type="search" placeholder="{$translator->translate('search')}" />
        </div>
        <button type="button" class="filter-btn" id="bfx-crypto-filter-btn">
          <div class="filter-icon-wrapper">
            <img src="$asset_url/filter.png" />
            <div id="filter-number"></div>
          </div>
          <span>{$translator->translate('filter_by')}</span>
          <div class="arrow">
            <img src="$asset_url/arrow-down.png" />
          </div>
        </button>
      </div>
      <div class="bfx-crypto-filter-clear-all bfx-crypto-filter-box hidden">
        <button type="button" class="filter-btn" id="bfx-crypto-clear-filter-btn">
          <div class="filter-icon-wrapper">
            <img src="$asset_url/delete.png" />
          </div>
          <span>{$translator->translate('clear_filters')}</span>
        </button>
      </div>
      <div id="bfx-crypto-filter-popup" class="bfx-crypto-filter-popup">
        <div class="filter-container">
          <form id="bfx-crypto-filter-form">
            <div class="filter-list">
              <div class="filter-title">{$translator->translate('category')}</div>
              <div class="filter-content">
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_sports_and_leisure" name="category" value="sports_and_leisure" />
                  <label for="bfx_filter_sports_and_leisure">{$translator->translate('sports_and_leisure')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_services" name="category" value="services" />
                  <label for="bfx_filter_services">{$translator->translate('services')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_food_and_drink" name="category" value="food_and_drink" />
                  <label for="bfx_filter_food_and_drink">{$translator->translate('food_and_drink')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_fashion" name="category" value="fashion" />
                  <label for="bfx_filter_fashion">{$translator->translate('fashion')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_entertainment" name="category" value="entertainment" />
                  <label for="bfx_filter_entertainment">{$translator->translate('entertainment')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_home_and_garden" name="category" value="home_and_garden" />
                  <label for="bfx_filter_home_and_garden">{$translator->translate('home_and_garden')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_electronics" name="category" value="electronics" />
                  <label for="bfx_filter_electronics">{$translator->translate('electronics')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_retail" name="category" value="retail" />
                  <label for="bfx_filter_retail">{$translator->translate('retail')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_auto_and_moto" name="category" value="auto_and_moto" />
                  <label for="bfx_filter_auto_and_moto">{$translator->translate('auto_and_moto')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_toys" name="category" value="toys" />
                  <label for="bfx_filter_toys">{$translator->translate('toys')}</label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_other" name="category" value="other" />
                  <label for="bfx_filter_other">{$translator->translate('other')}</label>
                </div>
              </div>
            </div>
            <div class="filter-list">
              <div class="filter-title">{$translator->translate('accepts')}</div>
              <div class="filter-content">
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_BTC" name="accepted_cryptos" value="BTC" />
                  <label for="bfx_filter_BTC">
                    <img src="$asset_url/BTC.png" width="25" height="22" />
                    BTC Lightning
                  </label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_UST" name="accepted_cryptos" value="UST" />
                  <label for="bfx_filter_UST">
                    <img src="$asset_url/UST.png" width="22" height="22" />
                    USDt
                  </label>
                </div>
                <div class="filter-checkbox">
                  <input type="checkbox" id="bfx_filter_LVGA" name="accepted_cryptos" value="LVGA" />
                  <label for="bfx_filter_LVGA">
                    <img src="$asset_url/LVGA.png" width="22" height="22" />
                    LVGA
                  </label>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div id="bfx-crypto-filter-popup-overlay"></div>
    <div id="bfx-crypto-map"></div>
  </div>

  <div id="bfx-crypto-popup-template" style="display: none">
    <div class="bfx-marker-popup">
      <div class="header">
        <div class="logo">
        </div>
        <div>
          <div class="title"></div>
          <div class="description"></div>
        </div>
      </div>
      <div class="footer">
        <div class="label">{$translator->translate('accepted_tokens')}</div>
        <div class="footer-container">
          <div class="tokens">
          </div>
          <div class="website">
          </div>
        </div>
      </div>
    </div>
  </div>
  <style>
    #bfx-crypto-map {
      width: $map_w;
      height: $map_h;
    }

    .bfx-crypto-filter {
      max-width: $map_w;
    }

    @media screen and (max-width: 768px) {
      #bfx-crypto-map {
        height: $map_mobile_h;
        width: $map_mobile_w;
      }
    }

  </style>
  <script>
    let MERCHANT_DATA = [];
    const isMobile = document.body.clientWidth < 768;
    const logoPlaceholder = '$asset_url/placeholder.png';
    const tokenMap = {
      BTC: {
        name: 'BTC Lightning',
        width: 25,
        height: 22,
        icon: '$asset_url/BTC.png',
      },
      UST: {
        name: 'USDt',
        width: 22,
        height: 22,
        icon: '$asset_url/UST.png',
      },
      LVGA: {
        name: 'LVGA',
        width: 22,
        height: 22,
        icon: '$asset_url/LVGA.png',
      },
    };

    const map = L
      .map('bfx-crypto-map', {
        zoomControl: false,
      })
      .setView([46.005314, 8.953802], 17);

    map.attributionControl.setPrefix('<a href="https://leafletjs.com" title="A JavaScript library for interactive maps">Leaflet</a>');

    L.control.zoom({ position: 'topright' }).addTo(map);

    const tiles = L
      .tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      })
      .addTo(map);

    const markerGroup = L.markerClusterGroup();

    const markerIcon = L.icon({
      iconUrl: '$asset_url/marker-pin-inactive.png',
      iconSize: [21, 27],
      iconAnchor: [10, 27],
      popupAnchor: [1, -20],
    });

    const activeMarkerIcon = L.icon({
      iconUrl: '$asset_url/marker-pin-active.png',
      iconSize: [21, 27],
      iconAnchor: [10, 27],
      popupAnchor: [1, -20],
    });

    function setPopupContent(e, content) {
      const markerPopup = e.target.getPopup();

      if (markerPopup) {
        return markerPopup.setContent(content);
      }

      const width = map.getSize().x - 60; // 60 is the padding of the popup
      const bounds = map.getBounds();
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
        .openOn(map);
    }

    function onMarkerClick(e) {
      const merchant = MERCHANT_DATA.find(function (merchant) {
        return merchant.id === e.target.options.merchantId;
      });

      if (merchant) {
        e.target.setIcon(activeMarkerIcon);

        const tags = (merchant.tags || []).map(function (tag) {
          const tag_name = jQuery('#bfx_filter_' + tag).next().html() || tag;
          return '<span class="tag">' + tag_name + '</span>';
        }).join('');
        const tokens = (merchant.accepted_cryptos || []).map(function (token) {
          const tokenInfo = tokenMap[token];
          if (tokenInfo) {
            const img = '<img src="' + tokenInfo.icon + '" width="' + tokenInfo.width +'" height="' + tokenInfo.height + '" />';
            const label = '<span>' + tokenInfo.name + '</span>';
            return '<div class="token">' + img + label + '</div>';
          }
          return '';
        }).join('');

        const logoUrl = merchant.logo_url || logoPlaceholder;
        const logo = '<img src="' + logoUrl + '" width="44" height="44" />';
        const titleStr = merchant.title || '';
        const title = '<h3>' + titleStr + '</h3>' + tags;
        const description = merchant.address ? '<p>' + merchant.address + '</p>' : '';
        const website = merchant.website
          ? '<a href="' + merchant.website + '" target="_blank"><img src="$asset_url/globe.png" /></a>'
          : '';

        const latLng = merchant.lat + ',' + merchant.lng;
        const direction = '<a href="https://maps.google.com/?q=' + latLng +'" target="_blank"><img src="$asset_url/direction.png" /></a>';
        const websiteInner = website + direction;

        const popupTemplate = document.getElementById('bfx-crypto-popup-template');
        popupTemplate.querySelector('.logo').innerHTML = logo;
        popupTemplate.querySelector('.title').innerHTML = title;
        popupTemplate.querySelector('.description').innerHTML = description;
        popupTemplate.querySelector('.tokens').innerHTML = tokens;
        popupTemplate.querySelector('.website').innerHTML = websiteInner;

        const popup = setPopupContent(e, popupTemplate.innerHTML);

        popup.on('remove', function () {
          // silly work-around to avoid race-condition made by leaflet marker cluster
          setTimeout(function () {
            e.target.setIcon(markerIcon);
          }, 1000);
        });
      }
    }

    function clearMarkers() {
      if (markerGroup) {
        markerGroup.clearLayers();
      }
    }

    function renderMarkers(data) {
      clearMarkers();
      const popupOptions = {
        autoPanPadding: L.point(70, 70),
        maxWidth: 340,
        minWidth: 340,
        closeButton: false,
      };

      const markers = data.map(function (merchant) {
        const marker = L
          .marker(
            [merchant.lat, merchant.lng],
            {
              merchantId: merchant.id,
              icon: markerIcon,
            },
          )
          .on('click', onMarkerClick);

        if (!isMobile) {
          marker.bindPopup('', popupOptions)
        }

        return marker;
      });

      markerGroup.addLayers(markers);

      map.addLayer(markerGroup);
    }

    function filterMarkers() {
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

      if (numberOfFilter > 0) {
        jQuery('#filter-number').html(numberOfFilter + '').addClass('active');
        jQuery('.bfx-crypto-filter-clear-all').removeClass('hidden');
      } else {
        jQuery('#filter-number').html('').removeClass('active');
        jQuery('.bfx-crypto-filter-clear-all').addClass('hidden');
      }

      const filteredData = MERCHANT_DATA.filter(function (merchant) {
        const matchedSearch = !searchValue || searchValue === '' || merchant.title.toLowerCase().includes(searchValue);
        const hasCategory = categories.length === 0 || categories.some(function (category) {
          return (merchant.tags || []).includes(category);
        });
        const hasAcceptedCryptos = acceptedCryptos.length === 0 || acceptedCryptos.some(function (token) {
          return (merchant.accepted_cryptos || []).includes(token);
        });
        return matchedSearch && hasCategory && hasAcceptedCryptos;
      });

      renderMarkers(filteredData);
    }

    function debounce(func, timeout = 300){
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
    }

    jQuery('#bfx-crypto-search-input').keyup(debounce(function() {
      filterMarkers();
    }, 300));

    jQuery('#bfx-crypto-filter-form .filter-checkbox input')
      .on('change', filterMarkers);

    jQuery('#bfx-crypto-filter-btn').on('click', function () {
      jQuery('#bfx-crypto-filter-popup').toggleClass('active');
      jQuery('#bfx-crypto-filter-popup-overlay').toggleClass('active');
    });

    jQuery('#bfx-crypto-filter-popup-overlay').on('click', function () {
      jQuery('#bfx-crypto-filter-popup').removeClass('active');
      jQuery('#bfx-crypto-filter-popup-overlay').removeClass('active');
    });


    jQuery('#bfx-crypto-clear-filter-btn').on('click', function () {
      jQuery('.filter-checkbox input')
        .each(function() {
          jQuery(this).prop('checked', false);
        });
      filterMarkers();
    });

    jQuery
      .ajax({ url: '$merchants_data_url' })
      .done(function(data) {
        MERCHANT_DATA = data;
        renderMarkers(data);
      });
  </script>
  HTML;

  return $html;
}

function add_style_attributes( $html, $handle ) {
  if ( 'leaflet' === $handle ) {
    return str_replace( "media='all'", "media='all' integrity='sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=' crossorigin=''", $html );
  }

  return $html;
}

function add_script_attributes( $html, $handle ) {
  if ( 'leaflet' === $handle ) {
    return str_replace( "type='text/javascript'", "type='text/javascript' integrity='sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=' crossorigin=''", $html );
  }

  return $html;
}

function bfx_crypto_map_shortcode_scripts() {
  global $post;
  if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'bfx_crypto_map') ) {
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null);
    wp_enqueue_script('leaflet-marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js', array('leaflet'), null);
    wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);
    wp_enqueue_style( 'leaflet-marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css', array('leaflet'), null);
    wp_enqueue_style( 'leaflet-marker-cluster-default', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css', array('leaflet', 'leaflet-marker-cluster'), null);
    wp_enqueue_style( 'leaflet-custom', plugin_dir_url(__FILE__) . 'assets/styles.css', array('leaflet'));
  }
}

add_action( 'wp_enqueue_scripts', 'bfx_crypto_map_shortcode_scripts');
add_filter( 'style_loader_tag', 'add_style_attributes', 10, 2);
add_filter( 'script_loader_tag', 'add_script_attributes', 10, 2);
add_shortcode( 'bfx_crypto_map', 'bfx_crypto_map_handler' );
?>
