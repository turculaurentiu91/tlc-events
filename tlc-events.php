<?php
/*
Plugin Name: Evenementen module
Plugin URI: https://www.fiverr.com/laurentiuturcu
Author URI: https://www.fiverr.com/laurentiuturcu
Description: Evenementen module
Version: 1.0.0
Author: Turcu Laurentiu
Text Domain: tlc-events
*/

require "vendor/autoload.php";

new TlcEvents\Main();

register_activation_hook(__FILE__, '\TlcEvents\Main::activation_hook');
register_deactivation_hook(__FILE__, '\TlcEvents\Main::deactivation_hook');

function tlcEvents_load_textdomain() {
  load_plugin_textdomain( 'tlc-events', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'tlcEvents_load_textdomain' );