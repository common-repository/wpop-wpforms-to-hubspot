<?php
/**
 * Plugin Name: WPOP's WPForms to HubSpot
 * Description: Add WPForms Data to HubSpot Contact lists.
 * Author: WPoperation
 * Plugin URI: https://wordpress.org/plugins/wpop-wpforms-hubspot
 * Author URI: https://wpoperation.com
 * Version: 1.0.5
 * Tested up to: 6.0.2
 * Text Domain: wpop-wpforms-hubspot
 * Domain Path: /languages/
 **/
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;
if (!class_exists('HBWPFORMS_Integration')) {
    class HBWPFORMS_Integration
    {
        public function __construct(){
        
            /**
             * check for contact form 7
             */
            add_action('init', array($this,'hbwf_plugin_dependencies'));

            add_action( 'wpforms_builder_enqueues',array($this,'hbwf_register_backend_assets') );
            add_action('init', array($this,'init'));

            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'hbwf_pro_plugin_action_links') );
        }

        public function init(){
            load_plugin_textdomain('wpop-wpforms-hubspot', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        public function hbwf_plugin_dependencies() {
            define("HBWPFORMS_PATH", plugin_dir_path(__FILE__));
            define("HBWPFORMS_URL", plugin_dir_url(__FILE__));
            if (!defined('WPFORMS_VERSION')) {
                add_action('admin_notices',  array($this, 'hbwf_admin_notices'));
            } else {
                /**
                 * include settings
                 */
                require_once( HBWPFORMS_PATH . 'includes/hbwf-settings.php' );

                /**
                 * contact form 7 Subscribe class
                 */
                require_once( HBWPFORMS_PATH . 'includes/hbwf-subscribe.php' );                
            }
        }
        
        //Registering of backend js and css
        public function hbwf_register_backend_assets() {
            
            wp_enqueue_style( 'hbwf-admin-css', HBWPFORMS_URL.'assets/admin.css');   
        }

        public function hbwf_admin_notices() {
            $class = 'notice notice-error';
            $message = __('WPOP\'s WPForms to Hubspot requires WPForms to be installed and active.', 'wpop-wpforms-hubspot');
            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        }

        function hbwf_pro_plugin_action_links( $links ) {
         
            $links[] = '<a href="https://wpoperation.com/plugins/wpop-wpforms-hubspot-pro/" target="_blank" style="color:#05c305; font-weight:bold;">'.esc_html__('Go Pro','wpop-wpforms-hubspot').'</a>';
            return $links;
        }
    }
    new HBWPFORMS_Integration();
}
