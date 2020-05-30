<?php

class SettingsPageCest
{
	public function _before(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	/**
	 * If "Bitcoin Payment Gateway (by TripleA)" is not visible, there's a problem.
	 *
	 * The problem is being caused by bh-wc-set-gateway-by-url when it modifies the $form_fields of the settings
	 * page to add its own entry.
	 *
	 * @param AcceptanceTester $I
	 */
	public function testSettingsPageForName(AcceptanceTester $I) {

		$I->amOnPluginsPage();

		$I->activatePlugin('bh-wc-set-gateway-by-url');

		$I->amOnPage('/wp-admin/admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway');

		$I->canSee('Bitcoin Payment Gateway (by TripleA)' );
	}

}
