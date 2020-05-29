<?php
/**
 * Tests for TripleA_Payment_Gateway_For_WooCommerce main setup class. Tests the actions are correctly added.
 *
 * @package TripleA_Payment_Gateway_For_WooCommerce
 * @author  TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Payment_Gateway_For_WooCommerce\includes;

/**
 * Class Develop_Test
 */
class TripleA_Payment_Gateway_For_WooCommerce_Develop_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Verify action to call load textdomain is added.
	 */
	public function test_action_plugins_loaded_load_plugin_textdomain() {

//		do_action('plugins_loaded');

		$action_name       = 'plugins_loaded';
		$expected_priority = 10;

		global $wp_filter;

		$this->assertArrayHasKey( $action_name, $wp_filter, 'load_plugin_textdomain definitely not hooked to plugins_loaded' );

		$actions_hooked = $wp_filter[ $action_name ];

		$this->assertArrayHasKey( $expected_priority, $actions_hooked, 'load_plugin_textdomain definitely not hooked to plugins_loaded priority 10' );


		$hooked_method = null;

		foreach( $actions_hooked as $action ) {
			$action_from_identifier = array_pop($action);
			$action_function = $action_from_identifier['function'];
			if(is_array($action_function)) {
				if( $action_function[0] instanceof I18n ) {
					$hooked_method = $action_function[1];
				}
			}
		}

		$this->assertNotNull( $hooked_method, 'No methods on an instance of I18n hooked to plugins_loaded' );

		$this->assertEquals( 'load_plugin_textdomain', $hooked_method, 'Unexpected method name for I18n class hooked to plugins_loaded' );

	}
}
