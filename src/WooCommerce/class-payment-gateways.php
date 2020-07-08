<?php
/**
 * This class handles any hooks fired by the WC_Payment_Gateways class.
 *
 * @see WC_Payment_Gateways
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce;

use WC_Payment_Gateway;

/**
 * Register our payment gateway with WooCommerce when it fires the WC_Payment_Gateways init hook.
 *
 * Class Payment_Gateways
 *
 * @package TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce
 */
class Payment_Gateways {

	/**
	 * Register the payment gateway with WooCommerce.
	 *
	 * @hooked woocommerce_payment_gateways
	 * @see WC_Payment_Gateways::init()
	 *
	 * @param string[] $methods
	 *
	 * @return string[]
	 */
	public function add_triplea_payment_gateway_to_woocommerce( $methods ) {
		$methods[] = TripleA_Payment_Gateway::class;
		return $methods;
	}
}
