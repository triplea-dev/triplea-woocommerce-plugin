<?php
/**
 * Class Plugin_Test. Tests the root plugin setup.
 *
 * @package TripleA_Payment_Gateway_For_WooCommerce
 * @author     TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Payment_Gateway_For_WooCommerce;

//use TripleA_Payment_Gateway_For_WooCommerce\includes\TripleA_Payment_Gateway_For_WooCommerce;
use TripleA_Payment_Gateway_For_Woocommerce;

/**
 * Verifies the plugin has been instantiated and added to PHP's $GLOBALS variable.
 */
class Plugin_Develop_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Test the main plugin object is added to PHP's GLOBALS and that it is the correct class.
	 */
	public function test_plugin_instantiated() {

		$this->assertArrayHasKey( 'triplea_payment_gateway_for_woocommerce', $GLOBALS );

		$this->assertInstanceOf( TripleA_Payment_Gateway_For_WooCommerce::class, $GLOBALS['triplea_payment_gateway_for_woocommerce'] );
	}

}
