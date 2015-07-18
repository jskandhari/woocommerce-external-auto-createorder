<?php
/*
Plugin Name: WooCommerce External/Affiliate Products Auto Create Order
Plugin URI: http://iskandariya.solutions/plugins
Description: Plugin allows you to automatically Add Order on External/Affiliate Product Button click, allowing you to track all the clicks and turn-overs.
Author: Iskandariya Solutions
Author URI: http://iskandariya.solutions
Version: 1.0.0

	Copyright: © 2015 Iskandariya Solutions (email : whois@iskandariya.solutions)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
/**
 * Check if Accessed Directly
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if ( ! class_exists( 'WC_Eaco' ) ) { 
		
		/**
		 * Localisation
		 **/
		load_plugin_textdomain( 'wc_eaco', false, dirname( plugin_basename( __FILE__ ) ) . '/' );

		class WC_Eaco {
			/**
			* Initializes the plugin
			*
			* @since 1.0
			*/
			public function __construct() {
				global $wpdb;
				
				//Template Overwrite
				//add_filter( 'woocommerce_locate_template', 'myplugin_woocommerce_locate_template', 10, 3 );
				 
				//Called to Enqeue JS
				add_action('wp_head', array( &$this, 'load_js_file'));
				
				// called just before the woocommerce template functions are included
				add_action( 'init', array( &$this, 'include_template_functions' ), 20 );
				
				//Order Call
				add_action( 'wp_ajax_pending_order', array( &$this, 'create_order'));
				add_action( 'wp_ajax_nopriv_pending_order', array( &$this, 'create_order'));
				
				// include required files
				//$this->includes();
				
			}
			
			/**
			 * Override any of the template functions from woocommerce/woocommerce-template.php 
			 * with our own template functions file
			 */
			public function include_template_functions() {
				include( 'woocommerce-external-auto-createorder-template.php' );
			}
			
			/**
			* Include required files
			*
			* @since 1.0
			*/
			private function includes() {
			// External Auto Create Order Class
			require ( 'classes/class-wc-eaco-main.php' );
			$this->embed = new WC_Eaco_Main();
			}
			
			//Load the Javascript
			function load_js_file()
			{
				wp_enqueue_script('jquery');
				wp_enqueue_script('the_js', plugins_url('js/eaco_click.js',__FILE__) );
				wp_localize_script( 
									'the_js', 
									'wp_ajax_eaco',
										array( 
											'ajaxurl'     => admin_url( 'admin-ajax.php' ),
											'ajaxnonce'   => wp_create_nonce( 'ajax_post_validation' ) 
										) 
				);
			}
			
			//overwrite The default Template
			function myplugin_plugin_path() {
 
				// gets the absolute path to this plugin directory
 
				return untrailingslashit( plugin_dir_path( __FILE__ ) );
 
			}
 
 
			function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {
 
				global $woocommerce;
  
				$_template = $template;
 
				if ( ! $template_path ) $template_path = $woocommerce->template_url;
				$plugin_path  = myplugin_plugin_path() . '/woocommerce/templates';
 
				// Look within passed path within the theme - this is priority
				$template = locate_template(
					array(
					$template_path . $template_name,
					$template_name
					)
				);
 
 
				// Modification: Get the template from this plugin, if it exists
				if ( ! $template && file_exists( $plugin_path . $template_name ) )
				$template = $plugin_path . $template_name;
 
				// Use default template
				if ( ! $template ) 
				$template = $_template;

				// Return what we found
				return $template;
 
			}
			//Create Order
			function create_order()
			{
				global $woocommerce, $wp;
				// Security check
				check_ajax_referer( 'ajax_post_validation', 'security' );
				//Getting Product ID
				$id = $_POST['prod_id'];

			
				// create order
				$address = array(
						'first_name' => 'Iskandariya',
						'last_name'  => 'Solutions',
						'company'    => 'Iskandariya.Solutions',
						'phone'      => '+91-9999999999',
						'address_1'  => 'Chandigarh',
						'address_2'  => 'Mohali,Punjab', 
						'city'       => 'Chandigarh',
						'state'      => 'PB',
						'postcode'   => '160001',
						'country'    => 'IN'
					);

				$order_data = array(
						'customer_id'		=> get_current_user_id()
						);

				$order = wc_create_order($order_data);
				$order->add_product( get_product( $id ), 1 ); //(get_product with id and next is for quantity)
				$order->set_address( $address, 'billing' );
				$order->set_address( $address, 'shipping' );
				$order->calculate_totals();
				wp_die();
			} 

		}
		
		// Instantiate the plugin class and add it to the set of globals
		$GLOBALS['wc_eaco'] = new WC_Eaco();
		

	} 
}
