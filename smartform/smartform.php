<?php
		/**
		* Plugin Name: Smart Forms
        * Depends: Gravity Forms,
		* Plugin URI: http://semperplugins.com
		* Description: 
		* Version: 0.1
		* Author: Semper Fi Web Design
		* Author URI: http://semperfiwebdesign.com/	 
		* Version: 1.0
		* Text Domain: SFMS
		* Domain Path: languages
		*
		*/
        if ( ! defined( 'ABSPATH' ) ) {
                exit; // Exit if accessed directly.
            }
        if ( ! class_exists( 'SmartForm' ) ) { 
            class SmartForm {
                /* plugin version */
                public $smartform_plugin_version = '1.0';
                /* plugin site url*/
                public $siteurl = "" ;
                public $options = "" ;
                protected static $_instance = null;
                public static function instance() {
                    if ( is_null( self::$_instance ) ) {
                        self::$_instance = new self();
                    }
                    return self::$_instance;
                }
                public function __construct() {
                    global $wpdb;
                    $this->siteurl = get_bloginfo('url');
                    $this->define_constants();
                    $this->includes();
				    $this->init_hooks();
                }
                private function define_constants() {
				        //Define Constants
                    if ( ! defined( 'SF_VERSION' ) ) {
                        $this->define( 'SF_VERSION', '1.0.0' );
                    }
                    // Plugin Folder Path
                    if ( ! defined( 'SF_PLUGIN_DIR' ) ) {
                        define( 'SF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
                    }
                    // Plugin Folder URL
                    if ( ! defined( 'SF_PLUGIN_URL' ) ) {
                        $this->define( 'SF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
                    }
                    // Plugin Root File
                    if ( ! defined( 'SF_PLUGIN_FILE' ) ) {
                        $this->define( 'SF_PLUGIN_FILE', __FILE__ );

                    }
                }
                private function define( $name, $value ) {
                    if ( ! defined( $name ) ) {
                        define( $name, $value );
                    }
                }
                private function smartform_plugin_uninstall() {
				        global $wpdb; 
			     }
                public function includes() {
					//Register scripts
				
                    wp_register_script("map-library",plugins_url('smartform/js/map_library.js'),'','',true);
                    wp_register_script("map-color",plugins_url('smartform/js/color.js'),'','',true);
                    wp_register_script("map-part",plugins_url('smartform/js/usmap.js'),'','',true);
                    wp_register_script("smart-js-library",plugins_url('smartform/js/scripts.js'),'','',true);
						wp_localize_script( 'smart-js-library', 'sfajax',array( 'url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
                    
                    wp_register_style( 'smartform-stylesheet', plugins_url('smartform/css/smartform.css'), array(), '', 'all' );
	

					//Enquee scripts
                    wp_enqueue_style( 'smartform-stylesheet' );
                    wp_enqueue_script("map-library");
                    wp_enqueue_script("map-color");
                    wp_enqueue_script("map-part");
                    wp_enqueue_script("smart-js-library");
                    require_once (SF_PLUGIN_DIR.'MPDF/mpdf.php');
				    require_once (SF_PLUGIN_DIR.'includes/post-types.php');
                    require_once (SF_PLUGIN_DIR.'includes/form-field-hooks.php');
                }
                
                private function init_hooks() {
                    register_activation_hook( __FILE__,array( $this, 'smartform_plugin_install'));
                    $plugin = plugin_basename(__FILE__); 
                    register_deactivation_hook( __FILE__,array($this,'smartform_plugin_uninstall'));
			     }
                   
            }
        }
        function smartform() {
            $smf = new SmartForm();
            return $smf->instance();
        }
        // Global for backwards compatibility.
        $GLOBALS['smartform'] = smartform();
?>