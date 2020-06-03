<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://triple-a.io
 * @since      1.0.0
 *
 * @package    TripleA_Payment_Gateway_For_WooCommerce
 * @subpackage TripleA_Payment_Gateway_For_WooCommerce/includes
 */

namespace TripleA_Payment_Gateway_For_WooCommerce\Includes;

use TripleA_Payment_Gateway_For_WooCommerce\Admin\Admin;
use TripleA_Payment_Gateway_For_WooCommerce\Admin\Plugins_Page;
use TripleA_Payment_Gateway_For_WooCommerce\API\API;
use TripleA_Payment_Gateway_For_WooCommerce\API\REST;
use TripleA_Payment_Gateway_For_WooCommerce\WooCommerce\Payment_Gateways;
use TripleA_Payment_Gateway_For_WooCommerce\WooCommerce\Thank_You;
use TripleA_Payment_Gateway_For_WooCommerce\WooCommerce\TripleA_Payment_Gateway;
use TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Loader_Interface;
use TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Object;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    TripleA_Payment_Gateway_For_WooCommerce
 * @subpackage TripleA_Payment_Gateway_For_WooCommerce/includes
 * @author     TripleA <andy@triple-a.io>
 */
class TripleA_Payment_Gateway_For_WooCommerce extends WPPB_Object {

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	protected $loader;

	/**
	 * @var API
	 */
	protected $api;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 *
	 * @param WPPB_Loader_Interface $loader
	 */
	public function __construct( $loader ) {
		if ( defined( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION' ) ) {
			$version = TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION;
		} else {
			$version = '1.0.0-missing';
		}
		$plugin_name = 'triplea-payment-gateway-for-woocommerce';

		parent::__construct( $plugin_name, $version );

		$this->loader = $loader;

		$this->set_locale();

		// The guts of the plugin.
		$this->api = API::get_instance();

		$this->define_admin_hooks();
		$this->define_woocommerce_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the TripleA_Payment_Gateway_For_WooCommerce\I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected function set_locale() {

		$plugin_i18n = new I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}


	protected function define_admin_hooks() {

		$admin = new Admin();
		$this->loader->add_action( 'plugins_loaded', $admin, 'woocommerce_check', 99 );
		$this->loader->add_action( 'admin_notices', $admin, 'settings_update_notice', 99 );

		$plugins_page    = new Plugins_Page();
		$plugin_basename = $this->get_plugin_name() . '/' . $this->get_plugin_name() . '.php';
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugins_page, 'display_plugin_action_links' );
	}

	/**
	 * Enable endpoints for the return_url used by TripleA API for Tx validation updates.
	 */
	protected function define_rest_hooks() {

		$rest = new REST( $this->api );
		$this->loader->add_action( 'rest_api_init', $rest, 'rest_api_init' );
	}

	protected function define_woocommerce_hooks() {

		$thank_you = new Thank_You();
		$this->loader->add_filter( 'woocommerce_thankyou_order_received_text', $thank_you, 'triplea_change_order_received_text', 10, 2 );

	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}
}
