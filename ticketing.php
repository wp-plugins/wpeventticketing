<?php
/*
Plugin Name: WP Event Ticketing
Plugin URI: http://9seeds.com/plugins/
Description: The WP Event Ticketing plugin makes it easy to sell and manage tickets for your event.
Author: 9seeds.com
Version: 1.4.0
Author URI: http://9seeds.com/
*/

define( 'WP_EVENT_TICKETING_BASE_NAME', plugin_basename( dirname( __FILE__ ) ) );
define( 'WP_EVENT_TICKETING_BASE_DIR', WP_PLUGIN_DIR . '/' . WP_EVENT_TICKETING_BASE_NAME . '/' );
define( 'WP_EVENT_TICKETING_LIB_DIR', WP_EVENT_TICKETING_BASE_DIR . 'lib/' );
define( 'WP_EVENT_TICKETING_URL', WP_PLUGIN_URL . '/' . WP_EVENT_TICKETING_BASE_NAME . '/' );

require_once WP_EVENT_TICKETING_LIB_DIR . 'functions.php';
require_once WP_EVENT_TICKETING_LIB_DIR . 'event_ticketing_system.php';

$ticketing_plugin = new eventTicketingSystem();

register_activation_hook(__FILE__, array( $ticketing_plugin, "activate"));
add_action( 'init', array( $ticketing_plugin, "onInit" ) );

register_activation_hook(__FILE__, array( $ticketing_plugin, "activate"));
add_action( 'init', array( $ticketing_plugin, "onInit" ) );
