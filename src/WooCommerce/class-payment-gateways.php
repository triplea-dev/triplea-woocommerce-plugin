<?php

namespace TripleA_Payment_Gateway_For_WooCommerce\WooCommerce;

use TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment;

class Payment_Gateways {

	public function triplea_payment_gateway_for_woocommerce_add_gateway( $methods ) {
		$methods[] = TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment::class;
		return $methods;
	}


}
