<?php

function list_merchants($data) {
  $data = wp_json_file_decode(plugin_dir_path( __FILE__ ) . './merchants.json');

  return new WP_REST_Response( $data, 200 );
}

add_action('rest_api_init', function () {
  register_rest_route( 'bfx-crypto-map/v1', '/merchants', array(
    'methods' => 'POST',
    'callback' => 'list_merchants',
  ) );
});

?>
