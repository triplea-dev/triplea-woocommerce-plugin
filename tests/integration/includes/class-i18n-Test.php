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
