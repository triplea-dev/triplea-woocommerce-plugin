<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.3.2
 *
 * @package    TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @subpackage TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce/includes
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\Includes;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.3.2
 * @package    TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @subpackage TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce/includes
 * @author     TripleA <andy.hoebeke@triple-a.io>
 */
class I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'triplea-cryptocurrency-payment-gateway-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}
}
