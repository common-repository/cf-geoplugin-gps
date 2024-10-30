<?php
/**
 * @link              http://wpgeocontroller.com/
 * @since             1.0.0
 * @package           CF_Geoplugin_GPS
 *
 * @wordpress-plugin
 * Plugin Name:       Geo Controller GPS extension
 * Plugin URI:        http://wpgeocontroller.com/
 * Description:       WordPress GPS module for the Geo Controller Plugin.
 * Version:           2.1.4
 * Author:            INFINITUM FORM
 * Author URI:        https://infinitumform.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf-geoplugin-gps
 * Domain Path:       /languages
 * Network:           true
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
 
// If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Globals
global $cfgp_gps_version;

// Main plugin file
if ( ! defined( 'CFGP_GPS_FILE' ) ) define( 'CFGP_GPS_FILE', __FILE__ );

/*
 * Require plugin general setup
 */
include_once __DIR__ . DIRECTORY_SEPARATOR . 'constants.php';

/*
 * Requirements
 */
include_once CFGP_GPS_CLASS . DIRECTORY_SEPARATOR . 'Requirements.php';

/*
 * Check requiremant
 */
$CFGP_GPS_Requirements = new CFGP_GPS_Requirements(array('file' => CFGP_GPS_FILE));
if($CFGP_GPS_Requirements->passes()) :
	// Initializing class
	include_once CFGP_GPS_INC . DIRECTORY_SEPARATOR . 'Init.php';
	// Include dependencies
	CFGP_GPS_Init::dependencies();
	// Plugin activation
	CFGP_GPS_Init::activation();
	// Plugin deactivation
	CFGP_GPS_Init::deactivation();
	// Run plugin
	CFGP_GPS_Init::run();
endif;