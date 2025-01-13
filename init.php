<?php
/*
Plugin Name: BFX crypto map
Plugin URI: https://bitfinex.com
description: BFX crypto map
Version: 1.4.16
Author: BFX
Author URI: https://bitfinex.com
License: GPL2
*/

include_once(plugin_dir_path( __FILE__ ) . './translations.php');
include_once(plugin_dir_path( __FILE__ ) . './rest.php');

function bfx_crypto_map_version() {
  if(!function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  }
  $plugin_data = get_plugin_data(__FILE__, array('Version'));
  return $plugin_data['Version'];
}

function bfx_gen_tag_filter_list($translator) {
  $available_tags = [
    'restaurant',
    'take_away',
    'boutique',
    'hair_stylist',
    'bar_and_cafe',
    'electronics',
    'entertainment',
    'sports_and_leisure',
    'jewelry',
    'pharmacy',
    'kiosk',
    'flower_shop',
    'service_provider',
    'book_shop',
    'optician',
    'art_gallery',
    'stationary_shop',
    'beauty_salon',
    'education',
    'grocery_store',
    'hotel',
    'taxi',
    'auto_and_moto',
    'retail',
    'home_and_garden',
    'local_food_products'
  ];

  $tag_filter_list = [];

  for ($i = 0; $i < count($available_tags); $i++) {
    array_push($tag_filter_list, bfx_gen_tag_filter_item($available_tags[$i], $translator));
  }

  $tag_filter_html = join('', $tag_filter_list);
  return $tag_filter_html;
}

function bfx_ccy_name($ccy) {
  $names = [
    'BTC' => 'BTC Lightning',
    'UST' => 'USDt',
    'LVGA' => 'LVGA',
    'NAKA_CARD' => 'NAKA Card',
  ];

  return $names[$ccy];
}

function bfx_gen_tag_filter_item($tag, $translator) {
  $label = $translator->translate($tag);
  return <<<HTML
    <div class="filter-checkbox">
      <input type="checkbox" id="bfx_filter_$tag" name="category" value="$tag" />
      <label for="bfx_filter_$tag">$label</label>
    </div>
  HTML;
}

function bfx_get_ccy_list($str) {
  if (!$str) {
    return [];
  }

  return explode(',', $str);
}

function bfx_gen_filter_checkbox_item($ccy, $asset_url) {
  $name = bfx_ccy_name($ccy);
  return <<<HTML
    <div class="filter-checkbox">
      <input type="checkbox" id="bfx_filter_$ccy" name="accepted_cryptos" value="$ccy" />
      <label for="bfx_filter_$ccy">
        <img src="$asset_url/$ccy.svg" width="25" height="22" />
        $name
      </label>
    </div>
  HTML;
}

function bfx_gen_filter_checkbox_list($arr, $asset_url) {
  $list_item = array_map(function($item) use ($asset_url) {
    return bfx_gen_filter_checkbox_item($item, $asset_url);
  }, $arr);
  return join(' ', $list_item);
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
    'env' => 'production',
    'ccy_list' => 'BTC,UST,LVGA,NAKA_CARD',
    'region' => '',
    'theme' => 'default',
    'default_lat' => '',
    'default_lng' => ''
  ), $atts);

  $map_w = $mapped_atts['width'];
  $map_h = $mapped_atts['height'];
  $lang = $mapped_atts['lang'];
  $env = $mapped_atts['env'];
  $map_mobile_w = $mapped_atts['mobile_width'];
  $map_mobile_h = $mapped_atts['mobile_height'];
  $ccy_list = bfx_get_ccy_list($mapped_atts['ccy_list']);
  $map_region = $mapped_atts['region'];
  $theme = $mapped_atts['theme'];
  $default_lat = $mapped_atts['default_lat'];
  $default_lng = $mapped_atts['default_lng'];
  $merchants_data_url = '/wp-json/bfx-crypto-map/v1/merchants?env=' . $env;
  $asset_url = plugin_dir_url(__FILE__) . 'assets';


  $translator = new BfxTranslations($lang);
  $tag_filter_html = bfx_gen_tag_filter_list($translator);
  $filter_checkbox_list_html = bfx_gen_filter_checkbox_list($ccy_list, $asset_url);

  $html = <<<HTML
  <div class="bfx-crypto-container bfx-crypto-theme-$theme">
    <div class="bfx-crypto-filter-container">
      <div class="bfx-crypto-filter bfx-crypto-filter-left">
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
          <button type="button" class="filter-btn" id="bfx-crypto-layer-select-mobile-btn">
            <div class="filter-icon-wrapper">
              <img src="$asset_url/layer.png" />
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
      </div>
      <div class="bfx-crypto-filter bfx-crypto-filter-right">
        <div class="bfx-crypto-filter-layer-select bfx-crypto-filter-box">
          <button type="button" class="filter-btn" id="bfx-crypto-layer-select-btn">
            <div class="filter-icon-wrapper">
              <img src="$asset_url/layer.png" />
            </div>
          </button>
        </div>
      </div>
      <div id="bfx-crypto-store-list-popup" class="bfx-crypto-filter-popup">
        <div class="filter-container">
        </div>
      </div>
      <div id="bfx-crypto-layer-popup" class="bfx-crypto-filter-popup">
        <div class="filter-container">
          <form id="bfx-crypto-layer-form">
            <div class="filter-list">
              <div class="filter-content">
              </div>
            </div>
          </form>
        </div>
      </div>
      <div id="bfx-crypto-filter-popup" class="bfx-crypto-filter-popup">
        <div class="filter-container">
          <form id="bfx-crypto-filter-form">
            <div class="filter-list">
              <div class="filter-title">{$translator->translate('category')}</div>
              <div class="filter-content">
                $tag_filter_html
              </div>
            </div>
            <div class="filter-list">
              <div class="filter-title">{$translator->translate('accepts')}</div>
              <div class="filter-content">
                $filter_checkbox_list_html
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
        <div class="bfx-marker-left-col">
          <div class="logo"></div>
          <div>
            <div class="bfx-marker-title"></div>
            <div class="bfx-marker-description"></div>
          </div>
        </div>
        <div class="bfx-marker-tags"></div>
      </div>
      <div class="footer">
        <div class="label">{$translator->translate('accepted_payment_methods')}</div>
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
      const mapboxKey = 'pk.eyJ1IjoicGxhbmJtYXAiLCJhIjoiY2xvNGd2ZnJqMDF2ZTJsbzJua3dyNzJ2YSJ9.tbgNNQ5wehycZSJyeSRCuA';
      const mapboxUsername = 'planbmap'

      const bfxCryptoMap = new BfxCryptoMap({
        isMobile: isMobile,
        assetUrl: '$asset_url',
        mapboxKey: mapboxKey,
        mapboxUsername: mapboxUsername,
        merchantDataUrl: '$merchants_data_url',
        translations: {
          no_store: "{$translator->translate('no_store')}",
        },
        theme: '$theme',
        region: '$map_region',
        defaultLat: '$default_lat',
        defaultLng: '$default_lng',
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
    wp_enqueue_script("jquery");
    wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null);
    wp_enqueue_script('leaflet-marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js', array('leaflet'), null);
    wp_enqueue_script('mapbox-gl', 'https://api.tiles.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js', array(), null);
    wp_enqueue_script('mapbox-gl-leaflet', plugin_dir_url(__FILE__) . 'assets/mapbox-gl-leaflet.js', array('leaflet', 'mapbox-gl'), null);
    wp_enqueue_script('leaflet-graphicscale', plugin_dir_url(__FILE__) . 'assets/Leaflet.GraphicScale.min.js', array('leaflet', 'mapbox-gl'), null);
    wp_enqueue_script('bfx-crypto-map', plugin_dir_url(__FILE__) . 'assets/crypto-map.js', array('mapbox-gl-leaflet'), $plugin_version);
    wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);
    wp_enqueue_style( 'leaflet-marker-cluster', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css', array('leaflet'), null);
    wp_enqueue_style( 'leaflet-marker-cluster-default', 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css', array('leaflet', 'leaflet-marker-cluster'), null);
    wp_enqueue_style( 'mapbox-gl', 'https://api.tiles.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css', array('leaflet'), null);
    wp_enqueue_style( 'leaflet-graphicscale', plugin_dir_url(__FILE__) . 'assets/Leaflet.GraphicScale.min.css', array('leaflet'), $plugin_version);
    wp_enqueue_style( 'leaflet-custom', plugin_dir_url(__FILE__) . 'assets/styles.css', array('leaflet'), $plugin_version);
  }
}

add_action( 'wp_enqueue_scripts', 'bfx_crypto_map_shortcode_scripts');
add_filter( 'style_loader_tag', 'add_style_attributes', 10, 2);
add_filter( 'script_loader_tag', 'add_script_attributes', 10, 2);
add_shortcode( 'bfx_crypto_map', 'bfx_crypto_map_handler' );
?>
