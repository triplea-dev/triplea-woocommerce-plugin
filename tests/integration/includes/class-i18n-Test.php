<?php
/**
 * Tests for I18n. Tests load_plugin_textdomain.
 *
 * @package TripleA_Payment_Gateway_For_WooCommerce
 * @author  TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Payment_Gateway_For_WooCommerce\Includes;

use TripleA_Payment_Gateway_For_WooCommerce\Includes\I18n;

/**
 * Class TripleA_Payment_Gateway_For_WooCommerce_Test
 *
 * @see I18n
 */
class I18n_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify action to call load_plugin_textdomain is added.
	 */
	public function test_action_plugins_loaded_load_plugin_textdomain() {

		$action_name       = 'plugins_loaded';
		$expected_priority = 10;
		$class_type        = I18n::class;
		$method_name       = 'load_plugin_textdomain';

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
	 * AFAICT, this will fail until a translation has been added.
	 *
	 * @see load_plugin_textdomain()
	 * @see https://gist.github.com/GaryJones/c8259da3a4501fd0648f19beddce0249
	 */
	public function test_load_plugin_textdomain() {

		$this->markTestSkipped( 'Needs one translation before test might pass.' );

		global $plugin_root_dir;

		$this->assertTrue( file_exists( $plugin_root_dir . '/languages/' ), '/languages/ folder does not exist.' );

		// Seems to fail because there are no translations to load.
		$this->assertTrue( is_textdomain_loaded( 'triplea-payment-gateway-for-woocommerce' ), 'i18n text domain not loaded.' );

	}

}
