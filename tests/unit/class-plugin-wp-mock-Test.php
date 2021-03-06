<?php
/**
 * Tests for the root plugin file.
 *
 * @package TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @author  TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce;

use TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\Includes\TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce;

/**
 * Class Plugin_WP_Mock_Test
 */
class Plugin_WP_Mock_Test extends \Codeception\Test\Unit {

	protected function _before() {
		\WP_Mock::setUp();
	}

	/**
	 * Verifies the plugin initialization.
	 */
	public function test_plugin_include() {

		global $plugin_root_dir;

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'plugin_dir_url'
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

		require_once $plugin_root_dir . '/triplea-cryptocurrency-payment-gateway-for-woocommerce.php';

		$this->assertArrayHasKey( 'triplea_cryptocurrency_payment_gateway_for_woocommerce', $GLOBALS );

		$this->assertInstanceOf( TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce::class, $GLOBALS['triplea_cryptocurrency_payment_gateway_for_woocommerce'] );

	}


	/**
	 * Verifies the plugin does not output anything to screen.
	 */
	public function test_plugin_include_no_output() {

		$plugin_root_dir = dirname( __DIR__, 2 ) . '/src';

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'args'   => array( \WP_Mock\Functions::type( 'string' ) ),
				'return' => $plugin_root_dir . '/',
			)
		);

		\WP_Mock::userFunction(
			'register_activation_hook'
		);

		\WP_Mock::userFunction(
			'register_deactivation_hook'
		);

		ob_start();

		require_once $plugin_root_dir . '/triplea-cryptocurrency-payment-gateway-for-woocommerce.php';

		$printed_output = ob_get_contents();

		ob_end_clean();

		$this->assertEmpty( $printed_output );

	}

}
