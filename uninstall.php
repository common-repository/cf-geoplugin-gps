<?php
/**
 * Uninstall plugin and clean everything
 *
 * @link              http://infinitumform.com/
 * @package           CF_Geoplugin
 */
 
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Plugin name
if (!defined('CFGP_GPS_NAME')) define('CFGP_GPS_NAME', 'cf-geoplugin-gps');

if(get_option(CFGP_GPS_NAME . '-ID')) {
	delete_option(CFGP_GPS_NAME . '-ID');
}

if(get_option(CFGP_GPS_NAME. '-activation')) {
	delete_option(CFGP_GPS_NAME . '-activation');
}

if(get_option(CFGP_GPS_NAME . '-deactivation')) {
	delete_option(CFGP_GPS_NAME . '-deactivation');
}

if(get_option(CFGP_GPS_NAME . '-debug')) {
	delete_option(CFGP_GPS_NAME . '-debug');
}