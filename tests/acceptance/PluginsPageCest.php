<?php 

class PluginsPageCest
{
    public function _before(AcceptanceTester $I)
    {
	    $I->loginAsAdmin();

	    $I->amOnPluginsPage();

    }


	/**
	 *
	 * @param AcceptanceTester $I
	 */
    public function testPluginsPageForName(AcceptanceTester $I) {

    	$I->canSee('Bitcoin Payment Gateway for WooCommerce' );
    }


    public function testPluginSettingsLink(AcceptanceTester $I) {

    	// wp-admin/admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway

	    $url = 'http://localhost/triplea-payment-gateway-for-woocommerce/wp-admin/admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway';

	    $I->seeLink('Settings', $url );

    }




}
