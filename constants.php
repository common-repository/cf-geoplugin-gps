<?php
/*
 * Plugin setup
 *
 * @author     Ivijan-Stefan Stipic <creativform@gmail.com>
 * @since      2.0.0
*/

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $WP_ADMIN_DIR, $WP_ADMIN_URL;

// Find wp-admin file path

if (!defined('WP_ADMIN_DIR')) {
	if( $WP_ADMIN_DIR ) {
		define('WP_ADMIN_DIR', $WP_ADMIN_DIR);
	} else {
		if( !$WP_ADMIN_URL ) {
			$WP_ADMIN_URL = admin_url('/');
		}
		
		if( strpos($WP_ADMIN_URL, 'wp-admin') !== false ) {
			$WP_ADMIN_DIR = rtrim(str_replace(home_url('/') , strtr(ABSPATH, '\\', '/'), $WP_ADMIN_URL) , '/\\');
		} else {
			$WP_ADMIN_DIR = dirname(WP_CONTENT_DIR) . DIRECTORY_SEPARATOR . 'wp-admin';
		}
		
		define('WP_ADMIN_DIR', $WP_ADMIN_DIR);
	}
}

if(file_exists(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'cf-geoplugin'))
{
	// Main Plugin root
	if ( ! defined( 'CFGP_ROOT' ) )		define( 'CFGP_ROOT', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'cf-geoplugin' );
	// Main plugin file
	if ( ! defined( 'CFGP_FILE' ) )		define( 'CFGP_FILE', CFGP_ROOT . DIRECTORY_SEPARATOR . 'cf-geoplugin.php' );
} else if(file_exists(WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'cf-geoplugin'))
{
	// Main Plugin root
	if ( ! defined( 'CFGP_ROOT' ) )		define( 'CFGP_ROOT', WPMU_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'cf-geoplugin' );
	// Main plugin file
	if ( ! defined( 'CFGP_FILE' ) )		define( 'CFGP_FILE', CFGP_ROOT . DIRECTORY_SEPARATOR . 'cf-geoplugin.php' );
} else {
	// Main Plugin root
	if ( ! defined( 'CFGP_ROOT' ) )		define( 'CFGP_ROOT', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'cf-geoplugin' );
	// Main plugin file
	if ( ! defined( 'CFGP_FILE' ) )		define( 'CFGP_FILE', CFGP_ROOT . DIRECTORY_SEPARATOR . 'cf-geoplugin.php' );
}
// Current plugin version ( if change, clear also session cache )
$cfgp_version = '';
if(file_exists(CFGP_FILE))
{
	if(function_exists('get_file_data') && $plugin_data = get_file_data( CFGP_FILE, array('Version' => 'Version'), false ))
		$cfgp_version = $plugin_data['Version'];
	if(!$cfgp_version && preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i',file_get_contents( CFGP_FILE ), $v))
		$cfgp_version = $v[1];
}
if ( ! defined( 'CFGP_VERSION' ) )		define( 'CFGP_VERSION', $cfgp_version);

// Main website
if ( ! defined( 'CFGP_STORE' ) )		define( 'CFGP_STORE', 'https://wpgeocontroller.com');

// Includes directory
if ( ! defined( 'CFGP_INC' ) )			define( 'CFGP_INC', CFGP_ROOT . DIRECTORY_SEPARATOR . 'inc' );

// Classes directory
if ( ! defined( 'CFGP_CLASS' ) )		define( 'CFGP_CLASS', CFGP_INC . DIRECTORY_SEPARATOR . 'classes' );

// Timestamp
if( ! defined( 'CFGP_TIME' ) )			define( 'CFGP_TIME', time() );

// Main plugin name
if ( ! defined( 'CFGP_NAME' ) )			define( 'CFGP_NAME', 'cf-geoplugin');

// Plugin session prefix (controlled by version)
if ( ! defined( 'CFGP_PREFIX' ) )		define( 'CFGP_PREFIX', 'cf_geo_'.preg_replace("/[^0-9]/Ui",'',CFGP_VERSION).'_');

// Plugin file
if ( ! defined( 'CFGP_GPS_FILE' ) )		define( 'CFGP_GPS_FILE', __FILE__ );

// Plugin root
if ( ! defined( 'CFGP_GPS_ROOT' ) )		define( 'CFGP_GPS_ROOT', rtrim(plugin_dir_path(CFGP_GPS_FILE), '/\\') );

// Plugin Inc root
if ( ! defined( 'CFGP_GPS_INC' ) )		define( 'CFGP_GPS_INC', CFGP_GPS_ROOT . DIRECTORY_SEPARATOR . 'inc' );

// Plugin Classes root
if ( ! defined( 'CFGP_GPS_CLASS' ) )	define( 'CFGP_GPS_CLASS', CFGP_GPS_INC . DIRECTORY_SEPARATOR . 'classes' );

// Plugin URL root
if ( ! defined( 'CFGP_GPS_URL' ) )		define( 'CFGP_GPS_URL', rtrim(plugin_dir_url( CFGP_GPS_FILE ), '/\\') );

// Plugin URL assets
if ( ! defined( 'CFGP_GPS_ASSETS' ) )	define( 'CFGP_GPS_ASSETS', CFGP_GPS_URL . '/assets' );

// Plugin URL root
if ( ! defined( 'CFGP_GPS_JS' ) )		define( 'CFGP_GPS_JS', CFGP_GPS_ASSETS . '/js' );

// Timestamp
if( ! defined( 'CFGP_GPS_TIME' ) )		define( 'CFGP_GPS_TIME', CFGP_TIME );

// Session
if( ! defined( 'CFGP_GPS_SESSION' ) )	define( 'CFGP_GPS_SESSION', 15 );

// Plugin name
if ( ! defined( 'CFGP_GPS_NAME' ) )		define( 'CFGP_GPS_NAME', 'cf-geoplugin-gps');

$cfgp_gps_version = NULL;
if(function_exists('get_file_data') && $plugin_data = get_file_data( CFGP_GPS_FILE, array('Version' => 'Version'), false ))
	$cfgp_gps_version = $plugin_data['Version'];
if(!$cfgp_gps_version && preg_match('/\*[\s\t]+?version:[\s\t]+?([0-9.]+)/i',file_get_contents( CFGP_GPS_FILE ), $v))
	$cfgp_gps_version = $v[1];
if ( ! defined( 'CFGP_GPS_VERSION' ) )	define( 'CFGP_GPS_VERSION', $cfgp_gps_version);

// Check if is multisite installation
if( ! defined( 'CFGP_GPS_MULTISITE' ) && defined( 'WP_ALLOW_MULTISITE' ) && WP_ALLOW_MULTISITE && defined( 'MULTISITE' ) && MULTISITE )			
{
	define( 'CFGP_GPS_MULTISITE', WP_ALLOW_MULTISITE );
}

if( ! defined( 'CFGP_GPS_MULTISITE' ) )			
{
    // New safer approach
    if( !function_exists( 'is_plugin_active_for_network' ) )
		include WP_ADMIN_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php';

	if(file_exists(WP_ADMIN_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php'))
		define( 'CFGP_GPS_MULTISITE', is_plugin_active_for_network( CFGP_GPS_ROOT . DIRECTORY_SEPARATOR . 'cf-geoplugin-gps.php' ) );
}

if( ! defined( 'CFGP_GPS_MULTISITE' ) ) define( 'CFGP_GPS_MULTISITE', false );


// Check is network admin
if( ! defined( 'CFGP_NETWORK_ADMIN' ) ) define( 'CFGP_NETWORK_ADMIN', ( function_exists('is_network_admin') && is_network_admin() ) );

// Check is defender activated
if( ! defined( 'CFGP_DEFENDER_ACTIVATED' ) ) define( 'CFGP_DEFENDER_ACTIVATED', false ); //- DEBUG