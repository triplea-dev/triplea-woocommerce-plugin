<?php

class PluginsPageCest {

	public function _before( AcceptanceTester $I ) {
		$I->loginAsAdmin();

		$I->amOnPluginsPage();

	}

	/**
	 *
	 * @param AcceptanceTester $I
	 */
	public function testPluginsPageForName( AcceptanceTester $I ) {

		$I->canSee( 'Bitcoin Payment Gateway for WooCommerce' );
	}

	public function testPluginSettingsLink( AcceptanceTester $I ) {

		$I->activatePlugin('triplea-cryptocurrency-payment-gateway-for-woocommerce');

		$url = 'http://localhost/triplea-cryptocurrency-payment-gateway-for-woocommerce/wp-admin/admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway';

		$I->seeLink( 'Settings', $url );

	}

	/**
	 * Disable the plugin and ensure the WooCommerce requirement notice is displayed.
	 *
	 * @param AcceptanceTester $I
	 */
	public function testWooCommerceRequirement( AcceptanceTester $I ) {

		$I->activatePlugin( 'woocommerce' );

		$I->activatePlugin('triplea-cryptocurrency-payment-gateway-for-woocommerce');

		$I->deactivatePlugin( 'woocommerce' );

		$I->see( 'TripleA Bitcoin Payment Gateway plugin requires WooCommerce to be installed' );

	}
}
