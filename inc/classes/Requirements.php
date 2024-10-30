<?php
/**
 * Requirements Check
 *
 * Check plugin requirements
 *
 * @link          http://infinitumform.com/
 * @since         2.0.0
 * @package       cf-geoplugin-gps
 * @author        Ivijan-Stefan Stipic
 * @version       1.0.0
 *
 */
 // If someone try to called this file directly via URL, abort.
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_GPS_Requirements')) : class CFGP_GPS_Requirements {
	
	// GPS plugin details
	private $title = 'CF Geo Plugin GPS';
	private $php = '7.0.0';
	private $wp = '5.4';
	private $slug = 'cf-geoplugin-gps';
	
	// Main plugin details
	private $cfgp_title = 'CF Geo Plugin';
	private $cfgp_version = '8.3.15';
	private $cfgp_slug = 'cf-geoplugin';
	
	// Main filename
	private $file;

	public function __construct( $args ) {
		foreach ( array( 'title', 'php', 'wp', 'file' ) as $setting ) {
			if ( isset($args[$setting]) && property_exists($this, $setting) ) {
				$this->{$setting} = $args[$setting];
			}
		}
		
		add_action( "in_plugin_update_message-{$this->slug}/{$this->slug}.php", array(&$this, 'in_plugin_update_message'), 10, 2 );
		add_action( 'admin_init', array(&$this, 'privacy_policy') );
	}
	
	/*
	 * Detect if plugin passes all checks 
	 */
	public function passes() {
		if (!function_exists('is_plugin_active')) {
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		$passes = ( $this->validate_php_version() && $this->validate_wp_version() && $this->validate_main_plugin() );
		if ( ! $passes ) {
			add_action( 'admin_notices', function () {
				if ( isset( $this->file ) ) {
					deactivate_plugins( plugin_basename( $this->file ) );
				}
			} );
		}
		return $passes;
	}
	
	/*
	 * Check main plugin 
	 */
	private function validate_main_plugin() {
		// If plugin exists
		
		$plugin_path = $plugin_active = false;
		
		if( file_exists(WP_PLUGIN_DIR . "/{$this->cfgp_slug}/{$this->cfgp_slug}.php") ) {
			$plugin_path = WP_PLUGIN_DIR . "/{$this->cfgp_slug}/{$this->cfgp_slug}.php";
			$plugin_active = is_plugin_active("{$this->cfgp_slug}/{$this->cfgp_slug}.php");
		}
		
		if( file_exists(WPMU_PLUGIN_DIR . "/{$this->cfgp_slug}/{$this->cfgp_slug}.php") ) {
			$plugin_path = WPMU_PLUGIN_DIR . "/{$this->cfgp_slug}/{$this->cfgp_slug}.php";
			$plugin_active = true;
		}
		
		if( !$plugin_path ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('You need first to install %1$s in order to use this %2$s addon.', 'cf-geoplugin-gps'), '<a href="https://wordpress.org/plugins/cf-geoplugin/" target="_blank">' . $this->cfgp_title . '</a>', "<b>{$this->title}</b>").'</p>';
				echo '</div>';
			} );
			return false;
		}
		// If plugin is in version
		$parent_plugin_data = get_plugin_data($plugin_path);
		if( $parent_plugin_data && version_compare( $parent_plugin_data['Version'], $this->cfgp_version, '<') ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
			echo '<p>'.sprintf(__('You need first to upgrade your %1$s to version %2$s or above in order to use this %3$s addon.', 'cf-geoplugin-gps'), "<b>{$this->cfgp_title}</b>", "<b>{$this->cfgp_version}</b>", "<b>{$this->title}</b>").'</p>';
				echo '</div>';
			} );
			return false;
		}
		// If plugin is active
		if( !$plugin_active ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('%1$s need to be activated in order to use this %2$s addon.', 'cf-geoplugin-gps'), "<b>{$this->cfgp_title}</b>", "<b>{$this->title}</b>").'</p>';
				echo '</div>';
			} );
			return false;
		}
		// Everything is OK
		return true;
	}

	/*
	 * Check PHP version 
	 */
	private function validate_php_version() {
		if ( version_compare( phpversion(), $this->php, '>=' ) ) {
			return true;
		} else {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('The %1$s cannot run on PHP versions older than PHP %2$s. Please contact your host and ask them to upgrade.', 'cf-geoplugin'), esc_html( $this->title ), $this->php).'</p>';
				echo '</div>';
			} );
			return false;
		}
	}

	/*
	 * Check WordPress version 
	 */
	private function validate_wp_version() {
		if ( version_compare( get_bloginfo( 'version' ), $this->wp, '>=' ) ) {
			return true;
		} else {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error">';
				echo '<p>'.sprintf(__('The %1$s cannot run on WordPress versions older than %2$s. Please update your WordPress installation.', 'cf-geoplugin'), esc_html( $this->title ), $this->wp).'</p>';
				echo '</div>';
			} );
			return false;
		}
	}
	
	/*
	 * Check WordPress version 
	 */
	function in_plugin_update_message($args, $response) {
		
	//	echo '<pre>', var_dump($response), '</pre>';
		
	   if (isset($response->upgrade_notice) && strlen(trim($response->upgrade_notice)) > 0) : ?>
<style>
.cf-geoplugin-upgrade-notice{
padding: 10px;
color: #000;
margin-top: 10px
}
.cf-geoplugin-upgrade-notice-list ol{
list-style-type: decimal;
padding-left:0;
margin-left: 15px;
}
.cf-geoplugin-upgrade-notice + p{
display:none;
}
.cf-geoplugin-upgrade-notice-info{
margin-top:32px;
font-weight:600;
}
</style>
<div class="cf-geoplugin-upgrade-notice">
<h3><?php printf(__('Important upgrade notice for the version %s:', 'cf-geoplugin'), $response->new_version); ?></h3>
<div class="cf-geoplugin-upgrade-notice-list">
	<?php echo str_replace(
		array(
			'<ul>',
			'</ul>'
		),array(
			'<ol>',
			'</ol>'
		),
		$response->upgrade_notice
	); ?>
</div>
<div class="cf-geoplugin-upgrade-notice-info">
	<?php _e('NOTE: Before doing the update, it would be a good idea to backup your WordPress installations and settings.', 'cf-geoplugin'); ?>
</div>
</div> 
		<?php endif;
	}
	
	/*
	 * Privacy Policy
	 */
	public function privacy_policy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
	 
		$privacy_poilicy = array(
			__( 'This site uses the WordPress Geo Plugin GPS extension (formerly: CF Geo Plugin GPS extension) to display public visitor information based on the GPS location that can then be collected or used for various purposes depending on the settings of the plugin.', 'cf-geoplugin' ),
			__( 'The WordPress Geo Plugin GPS extension allows all CF Geo Plugin users to locate their visitors using a GPS location. Using this plugin you solve the biggest problem of locating mobile visitors and correcting their location errors.', 'cf-geoplugin' ),
			sprintf( __( 'This website uses API services, technology and goods from the WordPress Geo Plugin GPS extension and that part belongs to the <a href="%s" target="_blank">WordPress Geo Plugin Privacy Policy</a>.', 'cf-geoplugin' ), CFGP_STORE . '/privacy-policy/' ),
			sprintf( __( 'Also, part of the services, technology and goods come from the Google Geocode API and that part belongs to the <a href="%s" target="_blank">Google Privacy Policy</a>', 'cf-geoplugin' ), 'https://policies.google.com/privacy' )
		);
	 
		wp_add_privacy_policy_content(
			__( 'WordPress Geo Plugin GPS extension', 'cf-geoplugin' ),
			wp_kses_post( wpautop( join((PHP_EOL . PHP_EOL), $privacy_poilicy), false ) )
		);
	}
} endif;