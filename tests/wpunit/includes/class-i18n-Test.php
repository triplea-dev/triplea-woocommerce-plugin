<?php
/**
 * Tests for I18n. Tests load_plugin_textdomain.
 *
 * @package TripleA_Payment_Gateway_For_WooCommerce
 * @author  TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Payment_Gateway_For_WooCommerce\includes;

/**
 * Class TripleA_Payment_Gateway_For_WooCommerce_Test
 *
 * @see I18n
 */
class TripleA_Payment_Gateway_For_WooCommerce_I18n_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Checks if the filter run by WordPress in the load_plugin_textdomain() function is called.
	 *
	 * @see load_plugin_textdomain()
	 */
	public function test_load_plugin_textdomain_function() {

		$called        = false;
		$actual_domain = null;

		$filter = function( $locale, $domain ) use ( &$called, &$actual_domain ) {

			$called        = true;
			$actual_domain = $domain;

			return $locale;
		};

		add_filter( 'plugin_locale', $filter, 10, 2 );

		global $plugin_root_dir;
		require_once $plugin_root_dir . '/includes/class-i18n.php';

		$i18n = new I18n();

		$i18n->load_plugin_textdomain();

		$this->assertTrue( $called, 'plugin_locale filter not called within load_plugin_textdomain() suggesting it has not been set by the plugin.' );
		$this->assertEquals( 'triplea-payment-gateway-for-woocommerce', $actual_domain );

	}
}
