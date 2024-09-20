<?php

// TODO: use wp_remote_get when the backend fix query params
// TODO: recursive get until no more data
function request_merchants($env) {
  $base_url = $env === 'staging'
    ? 'https://api.staging.bitfinex.com'
    : 'https://api.bitfinex.com';

  $url = $base_url . '/v2/ext/merchant/map/locations/list?page=1&pageSize=500';
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $result = curl_exec($ch);
  curl_close($ch);

  return $result;
}

function list_merchants(WP_REST_Request $request) {
  $env = $request->get_param('env');
  $cache_key = 'bfx-crypto-map.merchant_list.' . $env;
  $exp_seconds = 60 * 10; // 10 mins


  try {
    $result = get_transient($cache_key);
    if ($result === false) {
      $response = request_merchants($env);
      $result = json_decode($response);
      set_transient($cache_key, $result, $exp_seconds);
    }

    return new WP_REST_Response($result, 200);
  } catch (\Throwable $th) {
    error_log($th);
    return new WP_REST_Response(array("message" => "error"), 400);
  }
}

add_action('rest_api_init', function () {
  register_rest_route( 'bfx-crypto-map/v1', '/merchants', array(
    'methods' => 'POST',
    'callback' => 'list_merchants',
  ));
});
?>
