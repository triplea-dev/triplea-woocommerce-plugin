<?php
/**
 * Tests for I18n. Tests load_plugin_textdomain.
 *
 * @package TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @author  TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\woocommerce;

use TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment;

/**
 * Class TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce_Test
 *
 * @see TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment
 */
class Gateway_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * When bh-wc-set-gateway-by-url filters "woocommerce_settings_api_form_fields_{$gateway_id}" the settings page
	 * breaks.
	 *
	 * Test if the data being used in the filter is initialized by the gateway.
	 *
	 * @see WC_Settings_API::get_form_fields()
	 */
	public function test_get_form_fields_data_initialized() {

		if ( ! defined( 'TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION' ) ) {
			define( 'TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION', 'tests' );
		}

		$gateway = new TripleA_Payment_Gateway();

		$this->assertNotNull( $gateway->id );

		$this->assertIsArray( $gateway->form_fields );

	}


}
