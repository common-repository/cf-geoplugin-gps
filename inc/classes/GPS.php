<?php
/**
 * Initialize settings
 *
 * @version       8.0.0
 *
 */
 
if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if(!class_exists('CFGP_GPS')) : class CFGP_GPS extends CFGP_Global {
	
	// New API objects
	private $new_api_objects = array('street', 'street_number', 'city_code');
	
	private function __construct(){
		// Add extra GPS options
		$this->add_filter('cfgp/settings', 'settings', 2, 1);
		$this->add_filter('cfgp/settings/default', 'register_new_settings', 2, 1);
		// Stop script when all data is on the place
		if( isset($_GET['gps']) && $_GET['gps'] == 1 ) {
			CFGP_U::setcookie('cfgp_gps', 1, (MINUTE_IN_SECONDS * CFGP_GPS_SESSION));
			$this->add_action('wp_enqueue_scripts', 'deregister_scripts', 99);
		}
		// Stop script when cookie is setup
		if( isset($_COOKIE['cfgp_gps']) && $_COOKIE['cfgp_gps'] == 1 ) {
			$this->add_action('wp_enqueue_scripts', 'deregister_scripts', 99);
		} else {
			// Do AJAX
			$this->add_action('wp_ajax_cf_geoplugin_gps_set', 'ajax_set');
			$this->add_action('wp_ajax_nopriv_cf_geoplugin_gps_set', 'ajax_set');
			// Add preloader to pages
			if( CFGP_Options::get('enable_gps_preloader') ) {
				$this->add_action('wp_footer', 'append_preloader', -1, 0);
			}
		}
		// Add new API objects
		$this->add_action('cfgp/api/return', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/render/response', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/results', 'add_new_api_objects', 10, 1);
		$this->add_action('cfgp/api/default/fields', 'add_new_api_objects', 10, 1);
		// Redirection control
		$this->add_action('template_redirect', 'template_redirect', 999, 0);
		// Clear some cache on the plugin save
		$this->add_action('cfgp/options/action/set', 'clear_cache_on_options_save', 10, 5);
		// Add debug tab to debug page
		if( defined('CFGP_GPS_DEBUG') && CFGP_GPS_DEBUG ) {
			$this->add_action('cfgp/debug/nav-tab/after', 'debug_page_nav_tab');
			$this->add_action('cfgp/debug/tab-panel/after', 'debug_page_tab_panel');
		}
		
		if( !CFGP_Options::get('map_api_key', NULL) ) {
			$this->add_action( 'admin_notices', 'alert_google_map_api_key' );
		}
	}
	
	/**
	 * Alert that Google Map API key is empty
	 */
	public function alert_google_map_api_key () { ?>
<div class="notice notice-error" id="cfgp-gps-google-map-api-key">
	<h3><?php esc_html_e('Geo Controller GPS extension demands attention!', 'cf-geoplugin-gps'); ?></h3>
	<p><?php echo wp_kses_post( sprintf(
		__('In order to use the GPS extension, you must create a <a href="%2$s" target="_blank">Google Map API key</a> with an active <a href="%1$s" target="_blank">Geocode API</a>.', 'cf-geoplugin-gps'),
		'https://developers.google.com/maps/documentation/javascript/geocoding',
		'https://developers.google.com/maps/documentation/javascript/get-api-key'
	) ); ?></p>
	<p><?php echo wp_kses_post( sprintf(
		__('Place the created Google Map API key in the <a href="%1$s" target="_self">settings of your plugin</a> and activate it.', 'cf-geoplugin-gps'),
		esc_url( admin_url('/admin.php?page=cf-geoplugin-settings') . '#cfgp-section-gps-settings' )
	) ); ?></p>
</div>
	<?php }
	
	/**
	 * Add extra GPS options
	 */
	public function register_new_settings ($settings=[]) {
		$settings['enable_gps_preloader'] = 0;
		$settings['gps_preloader_image_src'] = '';
		return $settings;
	}
	
	/**
	 * Add extra GPS options
	 */
	public function settings ($options=[]) {
		// Add GPS extension settings
		$options[0]['sections'] = CFGP_U::array_insert_after_key($options[0]['sections'], 1, array(
			array(
				'id' => 'gps-settings',
				'title' => __('GPS Extension Settings', 'cf-geoplugin-gps'),
				'desc' => __('Set your GPS extension to work the way you want it to.', 'cf-geoplugin-gps'),
				'inputs' => array(
					// Replace Google Map API key
					array(
						'name' => 'map_api_key',
						'label' => __('Google Map API Key', 'cf-geoplugin'),
						'type' => 'text',
						'desc' => __('Enter the Google Map API key with Geocode API support activated.', 'cf-geoplugin-gps'),
						'default' => '',
						'attr' => array(
							'autocomplete'=>'off',
						)
					),
					// Enable preloader
					array(
						'name' => 'enable_gps_preloader',
						'label' => __('Enable GPS preloader', 'cf-geoplugin-gps'),
						'desc' => __('This option displays the preloader before the GPS displays the information. After that, the preloader is not displayed.', 'cf-geoplugin-gps'),
						'type' => 'radio',
						'options' => array(
							1 => __('Enable', 'cf-geoplugin-gps'),
							0 => __('Disable', 'cf-geoplugin-gps')
						),
						'default' => 0
					),
					// Replace preloader icon
					array(
						'name' => 'gps_preloader_image_src',
						'label' => __('Preloader image URL', 'cf-geoplugin-gps'),
						'type' => 'url',
						'desc' => __('If you have your own preloader icon and want to display it, you need to enter its URL here.', 'cf-geoplugin-gps'),
						'default' => '',
						'attr' => array(
							'autocomplete' => 'off',
							'placeholder' => $this->get_default_preloader()
						)
					)
				)
			)
		));
		
		// Replace default google map API key input field
		$options[1]['sections'][0]['inputs'][0] = array(
			'name' => 'google_map_api_key_info',
			'label' => __('Google Map API Key', 'cf-geoplugin'),
			'type' => 'info',
			'info' => sprintf(
				__('Google maps require an API key that you must add in the %1$s of this plugin under the %2$s.', 'cf-geoplugin-gps'),
				'<b>'.__('General Settings', 'cf-geoplugin').'</b>',
				'<b><a href="'.esc_url(admin_url('/admin.php?page=cf-geoplugin-settings')).'#gps-extension-settings">'.__('GPS Extension Settings', 'cf-geoplugin-gps').'</a></b>'
			)
		);
		
		return $options;
	}
	
	/**
	 * Clear some cache on the plugin save
	 */
	public function clear_cache_on_options_save($options, $default_options, $name_or_array, $value, $clear_cache) {
		if($clear_cache) {
			CFGP_U::setcookie('cfgp_gps', 0, ((YEAR_IN_SECONDS*2)-CFGP_TIME));
		}
	}
	
	/**
	 * Redirection control
	 */
	public function template_redirect(){
		if( ($_GET['gps'] ?? 0) == 1 ) {
			if( wp_safe_redirect( remove_query_arg(['gps', 'salt']) ) ) {
				exit;
			}
		}
	}
	
	/**
	 * Add new API objects
	 */
	public function add_new_api_objects( $array = array() ) {
		foreach($this->new_api_objects as $object) {
			if( !isset($array[$object]) ) {
				$array[$object] = NULL;
			}
		}
		return $array;
	}
	
	/**
	 * Deregister scripts
	 */
	public function deregister_scripts() {
		wp_deregister_script( CFGP_GPS_NAME . '-gps' );
	}
	
	/**
	 * Add script to footer
	 */
	public function ajax_set() {
		// GPS data missing
		if(!isset($_REQUEST['data'])) {
			wp_send_json_error(array(
				'error'=>true,
				'error_message'=>__('GPS data missing.', 'cf-geoplugin-gps')
			)); exit;
		}		
		// Gnerate session slug
		$ip_slug = CFGP_API::cache_key( CFGP_U::api('ip') );
		// Default results
		CFGP_U::api();
		$GEO = array();
		if( $transient = CFGP_DB_Cache::get("cfgp-api-{$ip_slug}") ) {
			$GEO = $transient;
		} else {
			wp_send_json_error(array(
				'error'=>true,
				'error_message'=>__('Could not retrieve geo data.', 'cf-geoplugin-gps')
			)); exit;
		}
		// Return new data
		$returns = array('error'=>false);
		// Get new data
		if($_REQUEST['data']) {
			$GEO['gps'] = 1;
			foreach( CFGP_Options::sanitize($_REQUEST['data']) as $key => $value ) {
				
				if( in_array($key, array('address', 'latitude', 'longitude', 'region', 'state', 'street', 'street_number', 'district')) ) {
					$returns[$key]= $GEO[$key] = $value;
				}
				
				if($key === 'countryCode'){
					$returns['country_code']= $GEO['country_code'] = $value;
				} else if($key === 'countryName'){
					$returns['country']= $GEO['country'] = $value;
				} else if($key === 'cityName'){
					$returns['city']= $GEO['city'] = $value;
				} else if($key === 'cityCode'){
					$returns['city_code']= $GEO['city_code'] = $value;
				} else if($key === 'district'){
					$returns['district']= $GEO['district'] = $value;
				}
			}
		}
		// Debug
		if( defined('CFGP_GPS_DEBUG') && CFGP_GPS_DEBUG ) {
			$debug = get_option(CFGP_GPS_NAME . '-debug', array());
			if( empty($debug) ) {
				$debug = array();
			}
			$debug[]= CFGP_Options::sanitize($_REQUEST['data']);
			update_option(CFGP_GPS_NAME . '-debug', $debug, false);
		} else if(get_option(CFGP_GPS_NAME . '-debug')) {
			delete_option(CFGP_GPS_NAME . '-debug');
		}
		// Set new data
		if( !empty($returns) ) {
			$GEO = array_merge($GEO, $returns);
			
			CFGP_DB_Cache::set("cfgp-api-{$ip_slug}", $GEO, (MINUTE_IN_SECONDS * CFGP_SESSION));
			
			if( CFGP_U::dev_mode() ) {
				wp_send_json_success(array(
					'returns' => $returns,
					'debug' => array(
						'transient' => "cfgp-api-{$ip_slug}",
						'geo' => (array)$GEO,
						'request_data' => CFGP_Options::sanitize( $_REQUEST['data'] ?? array() ) // Must be as this in development mode
					)
				), 200);
			} else {
				wp_send_json_success(array(
					'returns' => $returns
				), 200);
			}
			exit;
		}
		// Empty
		wp_send_json_error(array(
			'error'=>true,
			'error_message'=>__('No GPS data.', 'cf-geoplugin-gps')
		)); exit;
	}
	
	/**
	 * Debug navigaton tab
	 */
	public function debug_page_nav_tab() { ?>
		<a href="javascript:void(0);" class="nav-tab" data-id="#gps-debug"><i class="cfa cfa-map-marker"></i><span class="label"> <?php _e('GPS', 'cf-geoplugin'); ?></span></a>
	<?php }
	
	/**
	 * Debug tab container
	 */
	public function debug_page_tab_panel() { ?>
		<div class="cfgp-tab-panel cfgp-tab-panel-active" id="gps-debug">
			<?php CFGP_U::dump( get_option(CFGP_GPS_NAME . '-debug') ); ?>
		</div>
	<?php }
	
	public function get_default_preloader() {
		return apply_filters('cfgp_gps/preloader/default', esc_url( CFGP_GPS_ASSETS . '/images/cf-geoplugin-gps-preloader.gif') );
	}
	
	/**
	 * Append preloader to pages
	 */
	public function append_preloader() {
		
		$preloader = apply_filters('cfgp_gps/preloader', CFGP_Options::get(
			'gps_preloader_image_src',
			$this->get_default_preloader()
		) );
		
		if( apply_filters('cfgp_gps/preloader/css/enable', true) ) : ?><style><?php
			$css = '#cf-geoplugin-gps-preloader {
				position:fixed;
				top:0;
				right:0;				
				bottom:0;
				left:0;
				width:100%;
				max-width:100%;
				height:100vh;
				margin:0;
				padding:0;
				background-color:#fbfbfb;
				z-index:9000;
				opacity:0.9;
			}
			#cf-geoplugin-gps-preloader.hidden{
				display:none !important;
			}
			#cf-geoplugin-gps-preloader > #cf-geoplugin-gps-preloader-image-container {
				position:absolute;
				margin: 0 auto;
				padding: 15px;
				max-width:800px;
				top:50%;
				left:50%;
				transform: translate(-50%, -50%);
				-webkit-transform: translate(-50%, -50%);
				-moz-transform: translate(-50%, -50%);
				-ms-transform: translate(-50%, -50%);
				-o-transform: translate(-50%, -50%);
				z-index:1;
			}
			#cf-geoplugin-gps-preloader > #cf-geoplugin-gps-preloader-image-container > #cf-geoplugin-gps-preloader-image {
				width: auto;
				max-width: 100%;
				height: auto;
			}';
			
			echo wp_kses_post( apply_filters('cfgp_gps/preloader/css', $css, $preloader, $this->get_default_preloader() ) );
		?></style><?php endif; ob_start(); ?>
		<div id="cf-geoplugin-gps-preloader" class="hidden">
			<div id="cf-geoplugin-gps-preloader-image-container">
				<img id="cf-geoplugin-gps-preloader-image" src="<?php
					if( $preloader ) {
						echo esc_url($preloader);
					} else {
						echo esc_url( CFGP_GPS_ASSETS . '/images/cf-geoplugin-gps-preloader.gif' );
					}
				?>" alt="<?php esc_attr_e('Geo Controller GPS Preloader Icon', 'cf-geoplugin-gps'); ?>">
			</div>
		</div>
	<?php 
		echo wp_kses_post( apply_filters('cfgp_gps/preloader/html', ob_get_clean(), $preloader, $this->get_default_preloader() ) ); 
	}
	
	
	/* 
	 * Instance
	 * @verson    8.0.0
	 */
	public static function instance() {
		$class = self::class;
		$instance = CFGP_Cache::get($class);
		if ( !$instance ) {
			$instance = CFGP_Cache::set($class, new self());
		}
		return $instance;
	}	
} endif;