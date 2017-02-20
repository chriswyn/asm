<?php 
	/**
	 * Plugin Name: ASM Import
	 * Plugin URI: http://semperplugins.com
	 * Description: 
	 * Version: 0.1
	 * Author: Semper Fi Web Design
	 * Author URI: http://semperfiwebdesign.com/	 * Version: 1.0.0
	 * Text Domain: ASMI
	 * Domain Path: languages
	 *
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly.
	}

	function asm_backend_menu() {
			add_menu_page('ASM Settings','ASM Record Import','manage_options','asm_settings','asm_forms', '', 6);

	}

				add_action('admin_menu','asm_backend_menu');
	if ( ! class_exists( 'Asm_Import' ) ) {
		final class Asm_Import {
			/* plugin version */
			public $asms_plugin_version = '1.0';

			/* plugin site url*/
			public $siteurl = "" ;

			public $options = "" ;
			public $physicianspdf_helper = "" ;
			public $menu_created = "0" ;

		

			protected static $_instance = null;

			public static function instance() {
				if ( is_null( self::$_instance ) ) {

					self::$_instance = new self();
					
				}
				return self::$_instance;
			}
			public function __construct() {
				global $wpdb;
				$this->define_constants();
				$this->includes();
				$this->menu_created = 0;
			}

			private function define_constants() {
				//Define Constants
				if ( ! defined( 'ASM_IMPORT_VERSION' ) ) {
					$this->define( 'ASM_IMPORT_VERSION', '1.0.0' );
				}
				// Plugin Folder Path
				if ( ! defined( 'ASM_IMPORT_PLUGIN_DIR' ) ) {
					define( 'ASM_IMPORT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
				}
				// Plugin Folder URL
				if ( ! defined( 'ASM_IMPORT_PLUGIN_URL' ) ) {
					$this->define( 'ASM_IMPORT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
				}
				// Plugin Root File
				if ( ! defined( 'ASM_IMPORT_PLUGIN_FILE' ) ) {
					$this->define( 'ASM_IMPORT_PLUGIN_FILE', __FILE__ );

				}
			}

			//Define function 
		
			private function define( $name, $value ) {
				if ( ! defined( $name ) ) {
					define( $name, $value );
				}
			}
			//Install
			private function asm_import_plugin_install() {
				global $wpdb,$wp_version; 
				global $asm_import_plugin_version;
				$asm_import_plugin_version = '1.0';
				
				add_option( 'asm_import_plugin_version', $asm_import_plugin_version );
			}

		
			// Add settings link on plugin page

			public static function asm_import_settings_link($links) { 
			  $settings_link = '<a href="'.asm_get_url('settings').'">ASM Import Settings</a>'; 
			  array_unshift($links, $settings_link); 
			  return $links; 
			}
			 
			//un install	

			private function asm_plugin_uninstall() {
				global $wpdb; 
			}

			//backend menu	

			//public 

			//includes 
			public function includes() {
				require_once ( ASM_IMPORT_PLUGIN_DIR.'includes/form.php' );
				$this->menu_created = 1;	
			}

			public function helper() {
				return physicianspdf_helper::instance();
			}

			private function init_hooks() {
				register_activation_hook( __FILE__,array( $this, 'asm_import_plugin_install'));
				$plugin = plugin_basename(__FILE__); 
				add_filter( "plugin_action_links_$plugin", array( __CLASS__, 'asm_import_settings_link' ) );
				register_deactivation_hook( __FILE__,array($this,'asm_import_plugin_uninstall'));
			}
		} 
	}
	function asm_import() {
		$ASM_IMPORT = new Asm_Import();
		return $ASM_IMPORT->instance();
	}
	// Global for backwards compatibility.
	$GLOBALS['asm_import'] = asm_import();
?>
