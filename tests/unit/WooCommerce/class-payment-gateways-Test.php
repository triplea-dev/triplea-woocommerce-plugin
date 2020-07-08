<?php
/**
 * Tests for I18n. Tests load_plugin_textdomain.
 *
 * @package TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @author  TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce;

/**
 * Class Payment_Gateways_Test
 *
 * @see Payment_Gateways
 */
class Payment_Gateways_Test extends \Codeception\Test\Unit {

	/**
	 * woocommerce_payment_gateways
	 */
	public function test_gateway_is_registered_with_woocommerce() {

		$payment_gateways_class = new Payment_Gateways();

		$registered_payment_gateways = $payment_gateways_class->add_triplea_payment_gateway_to_woocommerce( array() );

		$this->assertContains( 'TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce\TripleA_Payment_Gateway', $registered_payment_gateways );

	}
}
