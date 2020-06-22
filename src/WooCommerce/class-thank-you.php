<?php
/**
 * Handle hooks for the WooCommerce Thank You page.
 *
 * @see templates/checkout/thankyou.php
 */

namespace TripleA_Payment_Gateway_For_WooCommerce\WooCommerce;

use WC_Order;

/**
 * Add a note to the Thank You page.
 *
 * Class Thank_You
 *
 * @package TripleA_Payment_Gateway_For_WooCommerce\WooCommerce
 */
class Thank_You {

	/**
	 * TODO If payment method was Bitcoin, and if our payment gateway was used, and tx result is paid too little...
	 * then display a message.
	 *
	 * @hooked woocommerce_thankyou_order_received_text
	 * @see templates/checkout/thankyou.php
	 *
	 * @param string   $str
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	function triplea_change_order_received_text( $str, $order ) {

		// if ($order->has_status( 'failed' )) {
		// $new_str = $str . '<br> If you .';
		// return $new_str;
		// }

		return $str;
	}

}
