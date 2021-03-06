<?php

class PaymentsPageCest {

	public function _before( AcceptanceTester $I ) {
		$I->loginAsAdmin();
	}

	/**
	 * If "Bitcoin Payment Gateway (by TripleA)" is not visible, there's a problem.
	 *
	 * The problem is being caused by bh-wc-set-gateway-by-url when it instantiates and called WC()->payment_gateways
	 * before TripleA has hooked in.
	 *
	 * @param AcceptanceTester $I
	 */
	public function testSettingsPageForName( AcceptanceTester $I ) {

		$I->amOnPluginsPage();
		$I->activatePlugin('woocommerce');
		$I->activatePlugin('triplea-cryptocurrency-payment-gateway-for-woocommerce');

		$I->amOnPage( '/wp-admin/admin.php?page=wc-settings&tab=checkout' );

		$I->canSee( 'Bitcoin Payment Gateway (by TripleA)' );
	}

}
