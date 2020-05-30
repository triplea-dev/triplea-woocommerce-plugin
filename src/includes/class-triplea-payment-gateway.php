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
 * @package    TripleA_Payment_Gateway_For_Woocommerce
 * @subpackage TripleA_Payment_Gateway_For_Woocommerce/includes
 */

use TripleA_Payment_Gateway_For_WooCommerce\Admin\Plugins_Page;
use TripleA_Payment_Gateway_For_WooCommerce\WooCommerce\Payment_Gateways;
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
 * @package    TripleA_Payment_Gateway_For_Woocommerce
 * @subpackage TripleA_Payment_Gateway_For_Woocommerce/includes
 * @author     TripleA <andy@triple-a.io>
 */
class TripleA_Payment_Gateway_For_Woocommerce extends WPPB_Object {

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

		require_once __DIR__ . '/class-i18n.php';
		$this->set_locale();

		$this->define_admin_hooks();
		$this->define_woocommerce_hooks();
	}
	
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the TripleA_Payment_Gateway_For_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected function set_locale() {

		$plugin_i18n = new I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_admin_hooks() {

		$plugins_page = new Plugins_Page();

		$plugin_basename = $this->get_plugin_name() . '/' . $this->get_plugin_name() . '.php';
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugins_page, 'display_plugin_action_links' );

	}

	private function define_woocommerce_hooks() {

		$payment_gateways = new Payment_Gateways();

		$this->loader->add_filter( 'woocommerce_payment_gateways', $payment_gateways, 'triplea_payment_gateway_for_woocommerce_add_gateway' );
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the TripleA_Payment_Gateway_For_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new TripleA_Payment_Gateway_For_WooCommerce\includes\I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	}

	/**
	 *
	 */
	}

}
