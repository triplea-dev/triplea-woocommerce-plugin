<?php
/**
 * Handle hooks for the WooCommerce Thank You page.
 *
 * @see templates/checkout/thankyou.php
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce;

use WC_Order;

/**
 * Add a note to the Thank You page.
 *
 * Class Thank_You
 *
 * @package TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce
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

	   // TODO customize status to match configured preference
      // TODO log to triplea debug log
	   
		 if ($order->has_status( 'failed' )) {
          $new_str = $str . '<br>'
                     .'Your order was placed. However, your payment was either insufficient or not detected.';
          return $new_str;
		 }

		return $str;
	}
   
//   function triplea_change_order_received_title( $str, $order ) {
//
//      // if ($order->has_status( 'failed' )) {
//      // $new_str = $str . '<br> If you .';
//      // return $new_str;
//      // }
//
//      return $str;
//   }
   
   public function thankyou_page_payment_details( $order_id ) {
   
      $wc_order = wc_get_order( $order_id );
      if (empty($wc_order)) {
         return;
      }
   
      $payment_tier = get_post_meta($order_id, '_triplea_payment_tier', true);
      $crypto_amount = get_post_meta($order_id, '_triplea_order_crypto_amount', true);
      $order_amount = get_post_meta($order_id, '_triplea_order_amount', true);
      $amount_paid = get_post_meta($order_id, '_triplea_amount_paid', true);
      $crypto_amount_paid = get_post_meta($order_id, '_triplea_crypto_amount_paid', true);
      $crypto_currency = get_post_meta($order_id, '_triplea_crypto_currency', true);
      $order_currency = get_post_meta($order_id, '_triplea_order_currency', true);
      
      if ( $payment_tier === 'short' ) {
         echo '<p style="font-size: 115%">';
         echo 'Your order was placed.'.'<br>';
         echo '<strong>It seems you paid too little</strong>. '.'<br>';
         echo 'You paid: '.'<strong>'.$crypto_currency.' '.number_format($crypto_amount_paid, 8).'</strong>'.' ('.$order_currency.' '.$amount_paid.')'.'<br>';
         echo 'instead of: '.'<strong>'.$crypto_currency.' '.number_format($crypto_amount, 8).'</strong>'.' ('.$order_currency.' '.$order_amount.').'.'<br>';
         echo '</p>';
         echo '<br>';
         
         // TODO Come up with information for the user : top up or get refund or so?
         
      }
      elseif ( $payment_tier === 'good' || $payment_tier === 'hold' ) {
         echo '<p style="font-size: 115%">';
         echo 'Your payment was detected.'.'<br>';
         echo 'As soon as your payment has been validated, your order will be processed.'.'<br>';
         echo '</p>';
         echo '<br>';
      }
      elseif (!empty($payment_tier)) {
         echo '<p style="font-size: 115%;">';
         echo '<span style="color: red;">There was an error detecting your payment.</span>'.'<br>';
         echo 'It might take a while for your payment transaction to be detected, after which your order will automatically be updated.'.'<br>';
         echo '</p>';
         echo '<br>';
      }
      
   }

}
