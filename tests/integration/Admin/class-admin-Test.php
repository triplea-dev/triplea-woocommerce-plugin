<?php

use TripleA_Payment_Gateway_For_WooCommerce\Admin\Admin;

class Admin_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * The check for WooCommerce should be hooked to plugins_loaded
	 */
	public function test_woocommerce_check_on_plugins_loaded() {

		$action_name       = 'plugins_loaded';
		$expected_priority = 10;
		$class_type        = Admin::class;
		$method_name       = 'woocommerce_check';

		global $wp_filter;

		$this->assertArrayHasKey( $action_name, $wp_filter, "$method_name definitely not hooked to $action_name" );

		$actions_hooked = $wp_filter[ $action_name ];

		$this->assertArrayHasKey( $expected_priority, $actions_hooked, "$method_name definitely not hooked to $action_name priority $expected_priority" );

		$hooked_method = null;
		foreach ( $actions_hooked as $action ) {
			$action_from_identifier = array_pop( $action );
			$action_function        = $action_from_identifier['function'];
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
	 * settings_update_notice
	 */
	public function test_settings_update_notice_on_admin_notices() {

		$action_name       = 'admin_notices';
		$expected_priority = 99;
		$class_type        = Admin::class;
		$method_name       = 'settings_update_notice';

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

}
