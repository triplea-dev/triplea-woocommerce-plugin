<?php

namespace TripleA_Payment_Gateway_For_WooCommerce\WooCommerce;

class Payment_Gateways_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Check the filter exists to register the payment gateway with WooCommerce.
	 *
	 * @see WC_Payment_Gateways::init()
	 */
	public function test_add_triplea_payment_gateway_to_woocommerce_hooked() {

		$action_name       = 'woocommerce_payment_gateways';
		$expected_priority = 10;
		$class_type        = Payment_Gateways::class;
		$method_name       = 'add_triplea_payment_gateway_to_woocommerce';

		global $wp_filter;

		$this->assertArrayHasKey( $action_name, $wp_filter, "$method_name definitely not hooked to $action_name" );

		$actions_hooked = $wp_filter[ $action_name ];

		$this->assertArrayHasKey( $expected_priority, $actions_hooked, "$method_name definitely not hooked to $action_name priority $expected_priority" );

		$hooked_method = null;
		foreach ( $actions_hooked[ $expected_priority ] as $action ) {
			$action_function = $action['function'];
			if ( is_array( $action_function ) ) {
				if ( $action_function[0] instanceof $class_type ) {
					$hooked_method = $action_function[1];
				}
			}
		}

		$this->assertNotNull( $hooked_method, "No methods on an instance of $class_type hooked to $action_name" );

		$this->assertEquals( $method_name, $hooked_method, "Unexpected method name for $class_type class hooked to $action_name" );

	}

	/**
	 * Test as though we were another plugin wanting to see the available gateways.
	 *
	 * @see \WC_Payment_Gateways::get_payment_gateway_ids()
	 */
	public function test_gateway_was_added() {

		$gateways = WC()->payment_gateways()->get_payment_gateway_ids();

		$this->assertContains( 'triplea_payment_gateway', $gateways );

	}

}
