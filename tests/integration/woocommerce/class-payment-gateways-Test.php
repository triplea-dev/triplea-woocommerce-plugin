<?php



class Payment_Gateways_Test extends \Codeception\TestCase\WPTestCase {


	public function test_gateway_was_added() {

		$gateways = WC()->payment_gateways()->get_payment_gateway_ids();

		$this->assertContains( 'triplea_payment_gateway', $gateways );

	}

}
