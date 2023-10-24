<?php
/*
Plugin Name: BFX crypto map
Plugin URI: https://bitfinex.com
description: BFX crypto map
Version: 1.1.26
Author: BFX
Author URI: https://bitfinex.com
License: GPL2
*/

include_once(plugin_dir_path( __FILE__ ) . './translations.php');

function bfx_crypto_map_version() {
  $plugin_data = get_plugin_data(__FILE__, array('Version'));
  return $plugin_data['Version'];
}


// [bfx_crypto_map width="100%" height="100%" mode="desktop"]
function bfx_crypto_map_handler( $atts ) {
  $plugin_version = bfx_crypto_map_version();
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
  $merchants_data_url = plugin_dir_url(__FILE__) . 'assets/merchants.json?ver=' . $plugin_version;
  $asset_url = plugin_dir_url(__FILE__) . 'assets';

  $translator = new BfxTranslations($lang);

  $html = <<<HTML
  <div class="bfx-crypto-container">
    <div class="bfx-crypto-filter">
      <div class="bfx-crypto-filter-store-list bfx-crypto-filter-box">
        <button type="button" class="filter-btn" id="bfx-crypto-store-list-btn">
          <img src="$asset_url/list-icon.png" />
          <span>{$translator->translate('store_list')}</span>
          <div class="arrow">
            <img src="$asset_url/arrow-down.png" />
          </div>
        </button>
      </div>
      <div class="bfx-crypto-filter-bar bfx-crypto-filter-box">
        <div class="search-container">
          <img src="$asset_url/search.png" width="14" height="13" />
          <input id="bfx-crypto-search-input" type="search" placeholder="{$translator->translate('search')}" />
        </div>
        <button type="button" class="filter-btn" id="bfx-crypto-store-list-mobile-btn">
          <img src="$asset_url/list-icon.png" />
        </button>
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
      <div id="bfx-crypto-store-list-popup" class="bfx-crypto-filter-popup">
        <div class="filter-container">
        </div>
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
    <div id="bfx-crypto-popup-overlay"></div>
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
    jQuery(function() { 
      const isMobile = document.body.clientWidth < 768;
      const mapboxKey = 'pk.eyJ1IjoiZGF0dHJhbmJmeCIsImEiOiJjbG5reXVoYjEwenF4MmlzMzlmOWhpZ3J6In0.y3REJgotRpiNyo_tYAx2yQ';

      const bfxCryptoMap = new BfxCryptoMap({
        isMobile: isMobile,
        assetUrl: '$asset_url',
        mapboxKey: mapboxKey,
        merchantDataUrl: '$merchants_data_url',
      });

      bfxCryptoMap.setup();
      bfxCryptoMap.fetchData();
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
    $plugin_version = bfx_crypto_map_version();
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null);
    wp_enqueue_script('leaflet-marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js', array('leaflet'), null);
    wp_enqueue_script('mapbox-gl', 'https://api.tiles.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js', array(), null);
    wp_enqueue_script('mapbox-gl-leaflet', 'https://unpkg.com/mapbox-gl-leaflet/leaflet-mapbox-gl.js', array('leaflet', 'mapbox-gl'), null);
    wp_enqueue_script('bfx-crypto-map', plugin_dir_url(__FILE__) . 'assets/crypto-map.js', array('mapbox-gl-leaflet'), $plugin_version);
    wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);
    wp_enqueue_style( 'leaflet-marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css', array('leaflet'), null);
    wp_enqueue_style( 'leaflet-marker-cluster-default', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css', array('leaflet', 'leaflet-marker-cluster'), null);
    wp_enqueue_style( 'mapbox-gl', 'https://api.tiles.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css', array('leaflet'), null);
    wp_enqueue_style( 'leaflet-custom', plugin_dir_url(__FILE__) . 'assets/styles.css', array('leaflet'), $plugin_version);
  }
}

add_action( 'wp_enqueue_scripts', 'bfx_crypto_map_shortcode_scripts');
add_filter( 'style_loader_tag', 'add_style_attributes', 10, 2);
add_filter( 'script_loader_tag', 'add_script_attributes', 10, 2);
add_shortcode( 'bfx_crypto_map', 'bfx_crypto_map_handler' );
?>
