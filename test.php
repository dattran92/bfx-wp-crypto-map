<?php
use PHPUnit\Framework\TestCase;

// mock wordpress things
function plugin_dir_path() {}
function add_action() {}
function add_filter() {}
function add_shortcode() {}

include_once(__DIR__ . '/init.php');

class BfxCryptoMapTest extends TestCase {
  public function test_add_leaflet_script() {
    $output = add_script_attributes("<script type='text/javascript'", 'leaflet');
    $this->assertStringContainsString(
      "integrity='sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=' crossorigin=''", 
      $output
    );
  }
}
