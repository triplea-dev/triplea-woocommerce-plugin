<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the
 * plugin admin area. This file also includes all of the dependencies used by
 * the plugin, registers the activation and deactivation functions, and defines
 * a function that starts the plugin.
 *
 * @link              https://triple-a.io
 * @since             1.0.0
 * @package           TripleA_Payment_Gateway_For_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Bitcoin Payment Gateway for WooCommerce
 * Plugin URI:        https://triple-a.io/ecommerce/#woocommerce
 * Description:       Offer bitcoin as a payment option on your website and get access to even more clients. Receive payments in bitcoins or in your local currency, directly in your bank account. Enjoy an easy setup, no bitcoin expertise required. Powered by TripleA.
 * Version: 1.3.1
 * Author: TripleA
 * Author URI: https://triple-a.io
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: triplea-payment-gateway-for-woocommerce Domain
 * Path: /languages
 */


use TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Loader;

require 'vendor/autoload.php';

require_once __DIR__ . '/autoload.php';

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION' ) ) {
	define( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION', '1.3.1' );
}

if ( ! defined( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_URL_PATH' ) ) {
	define( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_URL_PATH', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_PATH' ) ) {
	define( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_FILE' ) ) {
	define( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_FILE', __FILE__ );
}

require_once __DIR__ . '/logger.php';

add_action( 'plugins_loaded', 'triplea_payment_gateway_for_woocommerce_check', 99 );
function triplea_payment_gateway_for_woocommerce_check() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	    add_action( 'admin_notices', 'triplea_payment_gateway_for_woocommerce_wc_needed', 99 );
		add_action( 'admin_notices', 'triplea_payment_gateway_for_woocommerce_wc_admin_notices', 99 );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
}



function triplea_payment_gateway_for_woocommerce_wc_needed() {

	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'TripleA Bitcoin Payment Gateway plugin requires WooCommerce to be installed and active. You can download %s here.', 'triplea-payment-gateway-for-woocommerce' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';

}

/**
 * The code that runs during plugin activation.
 */
function activate_triplea_payment_gateway_for_woocommerce() {

}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_triplea_payment_gateway_for_woocommerce() {

}

register_activation_hook( __FILE__, 'activate_triplea_payment_gateway_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_triplea_payment_gateway_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-triplea-payment-gateway.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_triplea_payment_gateway_for_woocommerce() {

	$loader = new WPPB_Loader();
	$plugin = new Triplea_Payment_Gateway_For_Woocommerce( $loader );

	return $plugin;
}

$GLOBALS['triplea_payment_gateway_for_woocommerce'] = instantiate_triplea_payment_gateway_for_woocommerce();




add_action( 'woocommerce_init', 'triplea_payment_gateway_for_woocommerce_run', 1 );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function triplea_payment_gateway_for_woocommerce_run() {
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}

	add_action( 'wc_ajax_wc_triplea_start_checkout', 'TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment::wc_ajax_start_checkout' );

	add_filter( 'woocommerce_thankyou_order_received_text', 'triplea_change_order_received_text', 10, 2 );

	if ( WC()->session === null ) {
		WC()->session = new WC_Session_Handler();
		WC()->session->init();
	}

	/**
	 * Enable endpoints for the return_url used by TripleA API for Tx validation updates.
	 */
	add_action(
		'rest_api_init',
		function () {

			register_rest_route(
				'triplea/v1',
				'/tx_update/(?P<token>[a-zA-Z0-9-_]+)',
				array(
					'methods'  => 'POST',
					'callback' => 'triplea_handle_api_tx_update',
				)
			);
		}
	);

	/** @var Triplea_Payment_Gateway_For_Woocommerce $plugin */
	$plugin = $GLOBALS['triplea_payment_gateway_for_woocommerce']; // TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment
	$plugin->run();

	add_action( 'admin_notices', 'triplea_payment_gateway_for_woocommerce_wc_admin_update_notices', 99 );
}


function triplea_change_order_received_text( $str, $order ) {

	// TODO If payment method was bitcoin, if our payment gateway was used, and tx result is paid too little.. then display a message.

	// if ($order->has_status( 'failed' )) {
	// $new_str = $str . '<br> If you .';
	// return $new_str;
	// }

	return $str;
}


/**
 * Web hook (remotely called URL) to which transaction update notifications are
 * sent by the TripleA service.
 *
 * @param WP_REST_Request $request
 *
 * @return array|WP_Error
 */
function triplea_handle_api_tx_update( WP_REST_Request $request ) {
	/**
	 * Get plugin configuration settings.
	 */

	$plugin_options  = 'woocommerce_' . 'triplea_payment_gateway' . '_settings'; // Format: $wc_plugin_id + $plugin_own_id + option key
	$plugin_settings = get_option( $plugin_options );
	if ( empty( $plugin_settings ) ) {
		return array(
			'btc_addr' => '',
			'status'   => 'notok',
			'error'    => 'configuration error, missing config',
		);
	}

	// Load necessary plugin settings
	$debug_log_enabled = $plugin_settings['debug_log_enabled'];
	triplea_write_log( 'tx_update : Received payment update notification. Status = ' . $request->get_param( 'status' ), $debug_log_enabled );
	// triplea_write_log($request->get_param('payload'), $debug_log_enabled);

	if ( isset( $plugin_settings['triplea_woocommerce_order_states'] ) && isset( $plugin_settings['triplea_woocommerce_order_states']['new'] ) ) {
		$order_status_new       = $plugin_settings['triplea_woocommerce_order_states']['new'];
		$order_status_paid      = $plugin_settings['triplea_woocommerce_order_states']['paid'];
		$order_status_confirmed = $plugin_settings['triplea_woocommerce_order_states']['confirmed'];
		$order_status_complete  = $plugin_settings['triplea_woocommerce_order_states']['complete'];
		$order_status_refunded  = $plugin_settings['triplea_woocommerce_order_states']['refunded'];
		$order_status_invalid   = $plugin_settings['triplea_woocommerce_order_states']['invalid'];
	} else {
		// default values returned by get_status()
		$order_status_new       = 'wc-pending';
		$order_status_paid      = 'wc-on-hold'; // paid but still unconfirmed
		$order_status_confirmed = 'wc-processing';
		$order_status_complete  = 'wc-processing';
		$order_status_refunded  = 'wc-refunded';
		$order_status_invalid   = 'wc-failed';
	}

	// There is an additional state (on hold) which is set by WooCommerce on order creation.
	$order_status_on_hold = 'wc-on-hold';

	// Token: part of the return_url.
	$token = $request->get_param( 'token' );
	// To compare and validate.
	$api_endpoint_token = get_option( 'triplea_api_endpoint_token' );

	/**
	 * Validate security token (provided to TripleA API during TripleA PubKey ID retrieval).
	 * If token is not part of the return_url invoked, this request is unauthorised.
	 */
	if ( $api_endpoint_token === $token ) {
		// All is good.
		triplea_write_log( 'tx_update : Endpoint token valid, request authorized.', $debug_log_enabled );
	} else {
		// triplea_write_log('tx_update : Client endpoint token given by TripleA API did not match. ', $debug_log_enabled);
		// triplea_write_log('tx_update :   Local value: ' . $api_endpoint_token, $debug_log_enabled);
		// triplea_write_log('tx_update :   Given value: ' . $token, $debug_log_enabled);

		return new WP_Error(
			'bad_token',
			'Bad token, should be: ' . $api_endpoint_token,
			array(
				'status'   => 403,
				'settings' => $plugin_settings,
			)
		);
	}

	/**
	 * API v2 sends us an encrypted payload:
	 * {
	 *   result: .. (ou return?)
	 *   status: ..
	 *   payload: {
	 * 'order_status' => 'failed_paid_too_little' || 'failed_expired' || 'paid' || 'paid_too_much'
	 * 'exchange_rate' => '9123.91645856853',
	 * 'local_currency' => 'USD',
	 * 'crypto_amount_paid_unconf' => '0',
	 * 'order_amount' => '0.56',
	 * 'exchange_rate_datetime' => '2019-08-16T11:25:15Z',
	 * 'tx' => [
	 * {
	 * 'id' => 'eba7d42ea53124749dfdec12fa2f3b6d3caa8089095dce122b960ceaa38e0635',
	 * 'created' => '2019-08-16T11:27:24Z',
	 * 'conf' => '5.432e-05',
	 * 'confirmations' => 1,
	 * 'unconf' => '0'
	 * }
	 * ],
	 * 'api_id' => 'HA1565669224NlgN_t',
	 * 'crypto_amount_paid_conf' => '5.432e-05',
	 * 'amount_paid' => '0.55',
	 * 'crypto_amount' => '0.00005523',
	 * 'tx_status' => 'confirmed'
	 * }
	 * }
	 */

	$result  = $request->get_param( 'return' );
	$status  = $request->get_param( 'status' );
	$payload = $request->get_param( 'payload' );

	triplea_write_log( 'tx_update : Received POST data. Status: ' . $status, $debug_log_enabled );
	// triplea_write_log('Received POST data: payload: ' . $payload, $debug_log_enabled);

	$payment_mode                     = $plugin_settings['triplea_payment_mode'];
	$server_public_enc_key_btc        = $plugin_settings['triplea_server_public_enc_key_btc'];
	$server_public_enc_key_conversion = $plugin_settings['triplea_server_public_enc_key_conversion'];
	if ( $payment_mode === 'bitcoin-to-bitcoin' ) {
		$triplea_public_enc_key = $server_public_enc_key_btc;
	} else {
		$triplea_public_enc_key = $server_public_enc_key_conversion;
	}
	if ( empty( $triplea_public_enc_key ) ) {
		$triplea_public_enc_key = 'A4cxSkcL/QLPaEE5AKFevgGgSLe+/RtAov7iDf0e1Rw=';
		$fallback               = true;
	} else {
		$fallback = false;
	}

	$client_secret_enc_key = $plugin_settings['triplea_client_secret_key'];

	$payload_status_data = triplea_payment_gateway_for_woocommerce_decrypt_payload( $payload, $client_secret_enc_key, $triplea_public_enc_key );
	if ( $payload_status_data['status'] === 'failed' || $payload_status_data['payload'] === false ) {
		// Cannot decrypt payload, meaning we don't have the client_txid to find and update the order.
		// This wouldn't happen but if it does, orders would remain in "on hold" status.
		triplea_write_log( 'tx_update : Error! Status failed or payload false.', $debug_log_enabled );
		return array(
			'order_metadata' => 'Fallback: ' . $fallback . '. Status: ' . $payload_status_data['status'] . '. Payload: ' . $payload . '. Payload result: ' . $payload_status_data['payload'],
			'status'         => 'notok',
			'msg'            => 'Payload decryption failed. Cannot find and update order.',
		);
	}
	$balance_payload_decrypted = json_decode( $payload_status_data['payload'] );
	if ( $balance_payload_decrypted === null ) {
		triplea_write_log( 'tx_update : Error! Problem decoding json from balance payload.', $debug_log_enabled );
		return array(
			'order_metadata' => '',
			'status'         => 'notok',
			'msg'            => 'Update notification: Problem decoding json from balance payload.',
		);
	}

	$client_txid    = $balance_payload_decrypted->client_txid;
	$addr           = $balance_payload_decrypted->addr;
	$tx_status      = $balance_payload_decrypted->tx_status;
	$order_status   = $balance_payload_decrypted->order_status;
	$exchange_rate  = $balance_payload_decrypted->exchange_rate;
	$local_currency = $balance_payload_decrypted->local_currency;
	$order_amount   = $balance_payload_decrypted->order_amount;

	triplea_write_log( 'tx_update : Decoded payload for address ' . $addr . '.', $debug_log_enabled );

	// Get the WooCommerce order ID with the matching client tx ID.
	$order_id = triplea_get_orderid_from_txid( $client_txid, $debug_log_enabled );
	if ( $order_id < 0 ) {
		triplea_write_log( 'tx_update : ERROR. No matching orders found for tx id ' . $client_txid . '.', $debug_log_enabled );
		return array(
			'order_metadata' => '',
			'status'         => 'notok',
			'msg'            => 'Order not updated because no matching order found for the given client_txid ' . $client_txid . '.',
		);
	}
	triplea_write_log( 'tx_update : Found matching order ' . $order_id . ' found for tx id ' . $client_txid . '.', $debug_log_enabled );

	// Get the WooCommerce order object.
	$wc_order = wc_get_order( $order_id );
	if ( $wc_order->get_status() === $order_status_complete ) {
		// Order might have been marked as completed by the TripleA API, or manually by a site admin.
		triplea_write_log( 'tx_update : Order already marked as completed. Nothing to update.', $debug_log_enabled );
		// Nothing to update.
		return array(
			'status' => 'ok',
			'msg'    => 'Order already marked as completed.',
		);
	}
	// elseif ($wc_order->get_status() !== $order_status_new
	// && $wc_order->get_status() !== $order_status_paid
	// && $wc_order->get_status() !== $order_status_on_hold) {
	// If we're here, the order is not waiting for payment or payment confirmation and has not been marked as completed either.
	// Which means something seems wrong.
	//
	// Log and return error.
	// triplea_write_log('tx_update : ERROR! Order ' . $order_id . ' has status ' . $wc_order->get_status() . '. Cannot proceed.', $debug_log_enabled);
	// return [
	// 'order_metadata' => '',
	// 'status'         => 'notok',
	// 'msg'            => 'Order ' . $order_id . ' has status ' . $wc_order->get_status() . '. Cannot proceed.',
	// ];
	// }

	// We care only about a transaction having been confirmed here.
	// Skip if confirmed balance is zero.

	if ( strtolower( $tx_status ) !== 'confirmed' ) {
		triplea_write_log( 'tx_update : Payment transaction(s) for this order still unconfirmed. No update needed for now.', $debug_log_enabled );
		return array(
			'status' => 'ok',
			'msg'    => 'Transaction(s) still unconfirmed.',
		);
	}
	// Else continue.
	// All transactions for the order payment have been confirmed.
	// Check payment status and paid amounts.

	$crypto_amount             = floatval( $balance_payload_decrypted->crypto_amount ) ? floatval( $balance_payload_decrypted->crypto_amount ) : 0.0;
	$crypto_amount_paid_unconf = floatval( $balance_payload_decrypted->crypto_amount_paid_unconf ) ? floatval( $balance_payload_decrypted->crypto_amount_paid_unconf ) : 0.0;
	$crypto_amount_paid_conf   = floatval( $balance_payload_decrypted->crypto_amount_paid_conf ) ? floatval( $balance_payload_decrypted->crypto_amount_paid_conf ) : 0.0;
	$crypto_amount_paid_total  = $crypto_amount_paid_conf + $crypto_amount_paid_unconf;

	triplea_write_log( 'tx_update : Crypto amount: ' . $crypto_amount . ', crypto amount paid total is ' . $crypto_amount_paid_total, $debug_log_enabled );

	$notes = array();

	// Compares order_status and tx_status, and decides what to do with the current order.
	// Updates the notes[] array with relevant information for the WooCommerce backend users.
	triplea_update_bitcoin_payment_order_status( $order_status, $notes, $wc_order, $addr, $tx_status, $crypto_amount_paid_total, $crypto_amount, $local_currency, $order_amount, $exchange_rate );

	foreach ( $notes as $note ) {
		$wc_order->add_order_note( $note, 'woocommerce' );
	}

	return array( 'status' => 'ok' );
}

/**
 * @param       $order_status
 * @param array                    $notes
 * @param       $wc_order
 * @param       $addr
 * @param       $tx_status
 * @param       $crypto_amount_paid_total
 * @param       $crypto_amount
 * @param       $local_currency
 * @param       $order_amount
 * @param       $exchange_rate
 */
function triplea_update_bitcoin_payment_order_status( $order_status, array &$notes, $wc_order, $addr, $tx_status, $crypto_amount_paid_total, $crypto_amount, $local_currency, $order_amount, $exchange_rate ) {

	$plugin_options    = 'woocommerce_' . 'triplea_payment_gateway' . '_settings'; // Format: $wc_plugin_id + $plugin_own_id + option key
	$plugin_settings   = get_option( $plugin_options );
	$debug_log_enabled = $plugin_settings['debug_log_enabled'];

	triplea_write_log( 'update_order_status : checking...', $debug_log_enabled );

	if ( isset( $plugin_settings['triplea_woocommerce_order_states'] ) && isset( $plugin_settings['triplea_woocommerce_order_states']['new'] ) ) {

		$order_status_new       = $plugin_settings['triplea_woocommerce_order_states']['new'];
		$order_status_paid      = $plugin_settings['triplea_woocommerce_order_states']['paid'];
		$order_status_confirmed = $plugin_settings['triplea_woocommerce_order_states']['confirmed'];
		$order_status_complete  = $plugin_settings['triplea_woocommerce_order_states']['complete'];
		$order_status_refunded  = $plugin_settings['triplea_woocommerce_order_states']['refunded'];
		$order_status_invalid   = $plugin_settings['triplea_woocommerce_order_states']['invalid'];
	} else {
		// default values returned by get_status()
		$order_status_new       = 'wc-pending';
		$order_status_paid      = 'wc-on-hold'; // paid but still unconfirmed
		$order_status_confirmed = 'wc-processing';
		$order_status_complete  = 'wc-processing';
		$order_status_refunded  = 'wc-refunded';
		$order_status_invalid   = 'wc-failed';
	}
	$order_status_on_hold = 'wc-on-hold';

	if ( empty( $wc_order ) ) {
		triplea_write_log( 'update_order_status : ERROR! Empty woocommerce order. Order was not placed.', $debug_log_enabled );
		return;
	}

	if ( $order_status === 'paid_expired' ) {
		// If payment form expires, order does not get placed.
		// However, leaving this here to document possible options.
		triplea_write_log( 'update_order_status : payment expired. ', $debug_log_enabled );
		$notes[] = 'Payment time expired. No payment detected during checkout.';

		$wc_order->update_status( $order_status_invalid );

		// TODO consider whether this should be 'failed' ?
		// User let the payment form expire. Usually this means the user did not make
		// a bitcoin payment in time. However in rare cases (due to user's browser plugins
		// or internet connection), it could happen that the user made a payment
		// which did not get detected before the payment form timer expired.
		// Which is why we place the order anyway and mark it as failed.
		// The user should be able (WooCommerce functionality) to select a different payment
		// option and make another payment.
		// With status 'on hold', hopefully it is possible for the user to choose
		// to try payment again or pick another payment method.
	} else {

		if ( $tx_status === 'confirmed' ) {
			triplea_write_log( 'update_order_status : Transaction confirmed.', $debug_log_enabled );

			if ( $order_status === 'paid' ) {
				// Transactions all confirmed, paid enough. Order payment fully done.
				triplea_write_log( 'update_order_status : Order paid (confirmed payment).', $debug_log_enabled );

				// Order has been paid, and the payment transaction(s) are all confirmed.
				$wc_order->update_status( $order_status_confirmed );
				$wc_order->payment_complete( 'BTC address ' . $addr );

				$payment_status_message = 'Correct amount paid.<br>Order completed.';
			} elseif ( $order_status === 'paid_too_much' ) {
				// Transactions all confirmed, paid enough. Order payment fully done.
				triplea_write_log( 'update_order_status : Paid too much for order (payment confirmed).', $debug_log_enabled );

				// Order has been paid, and the payment transaction(s) are all confirmed.
				$wc_order->update_status( $order_status_confirmed );
				$wc_order->payment_complete( 'BTC address ' . $addr . '' );

				$payment_status_message = 'User <u>paid too much</u>.<br>Order completed.';
			} elseif ( $order_status === 'failed_paid_too_little' ) {
				// Transactions all confirmed, paid too little. Order payment failed.
				triplea_write_log( 'update_order_status : Paid too little (payment confirmed).', $debug_log_enabled );
				$notes[] = '<strong>BTC amount paid is insufficient!</strong>';

				// Possible edge case to be aware of:
				// If 1 tx gets confirmed, paid too little.
				// Then before expiry another tx is made.
				$wc_order->update_status( $order_status_invalid );

				$payment_status_message = 'User <u>paid too little</u>.<br>Order <strong>failed</strong>.';
			} else {
				// Transactions all confirmed. Unknown order result however.
				// Should never happen. Adding as backup anyway.
				triplea_write_log( 'update_order_status : ERROR! Order status unknown. Please contact us at support@triple-a.io', $debug_log_enabled );

				$wc_order->update_status( $order_status_invalid );

				$payment_status_message = 'Code error, unknown order status value.<br>Order <strong>failed</strong>.';
			}

			$notes[] =
			'Transaction confirmation received.<br>' .
			'<br>' .
			'<strong>Amount due:</strong><br>' .
			'order_currency ' . number_format( $order_amount, 2 ) . '<br>' .
			"<small>1 BTC = $exchange_rate $local_currency</small><br>" .
			'BTC ' . number_format( $crypto_amount, 8 ) . '<br>' .
			'<br>' .
			'<strong>Amount paid:</strong> <br>' .
			'BTC ' . number_format( $crypto_amount_paid_total, 8 ) . '<br>' .
			'<br>' .
			"$payment_status_message";
		} else {
			triplea_write_log( 'update_order_status : Unconfirmed order status: ' . $order_status, $debug_log_enabled );

			// Status: unconfirmed
			// No matter if at the moment the order status is paid/too little/too much..
			// ..we need to wait for the confirmed transaction(s).

			if ( substr( $addr, 0, 1 ) === 'n' || substr( $addr, 0, 1 ) === 'm' ) {
				$blockchain_epxlorer_url = "https://www.blockchain.com/btctest/address/$addr";
			} else {
				$blockchain_epxlorer_url = "https://www.blockchain.com/btc/address/$addr";
			}

			$notes[] = 'Bitcoin payment made, awaiting validation. ' .
					"(<a href='$blockchain_epxlorer_url' target='_blank'>transaction details</a>).<br> <br>" .
					'<strong>Amount due:</strong><br>' .
					"$local_currency " . number_format( $order_amount, 2 ) . '<br>' .
					"<small>1 BTC = $exchange_rate $local_currency</small><br>" .
					'BTC ' . number_format( $crypto_amount, 8 ) . '<br>' .
					'<br>' .
					"<strong>Amount awaiting validation:</strong> <br>BTC $crypto_amount_paid_total <br>" .
					' <br>' .
					'Payment to bitcoin address: ' . $addr . '<br>';

			$wc_order->update_status( $order_status_paid );
		}
	}
	// Reminder: $notes array is passed by reference, the calling function will add all notes to the woocommerce order.
}


function triplea_get_orderid_from_txid( $order_tx_id, $debug_log_enabled ) {
	$query_args = array(
		'type'           => wc_get_order_types( 'view-orders' ),
		'limit'          => 3, // should only ever have one
	  // 'post_status' => ['on-hold'],
		'payment_method' => 'triplea_payment_gateway',
		'return'         => 'ids',
		'meta_key'       => '_triplea_tx_id',
		'meta_value'     => $order_tx_id,
	);
	$query      = new WC_Order_Query( $query_args );

	$orders = $query->get_orders();

	if ( count( $orders ) < 1 ) {
		return -1;
	}
	// triplea_write_log('triplea_get_orderid_from_txid(): Found orders for client_txid ' . $order_tx_id, $debug_log_enabled);
	$order_id = $orders[0];

	return $order_id;
}


/** --------------- **/

add_filter( 'woocommerce_payment_gateways', 'triplea_payment_gateway_for_woocommerce_add_gateway' );

function triplea_payment_gateway_for_woocommerce_add_gateway( $methods ) {
	$methods[] = 'TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment';
	return $methods;
}


function triplea_payment_gateway_for_woocommerce_wc_admin_notices() {
	// Suppress "Plugin activated" notice.
	unset( $_GET['activate'] );

	is_admin() && add_filter(
		'gettext',
		function ( $translated_text, $untranslated_text, $domain ) {
			$old = array(
				'Plugin <strong>activated</strong>.',
				'Selected plugins <strong>activated</strong>.',
			);
			$new = "<span style='color:red'>Bitcoin Payment Gateway for WooCommerce (by TripleA) </span> - Plugin needs WooCommerce to work.";

			if ( in_array( $untranslated_text, $old, true ) ) {
				   $translated_text = $new;
			}
			return $translated_text;
		},
		99,
		3
	);
}

/**
 * Display a backend notice to the admin, for as long as some sites
 * still need to upgrade from 1.0.5 or previous versions.
 *
 * This displays instructions for the update procedure.
 */
function triplea_payment_gateway_for_woocommerce_wc_admin_update_notices() {
	if ( ! is_admin() ) {
		return;
	}

	// Only show this if we detect an upgrade from an old version.
	if ( ! triplea_payment_gateway_settings_upgrade_required() ) {
		return;
	}

	// Display the notice
	$class        = 'notice notice-warning'; // or 'notice-success'
	$setting_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway' );
	$setting_text = __( 'Settings', 'triplea-payment-gateway-for-woocommerce' );
	$message      = 'Bitcoin Payment Gateway requires wallet updates. Please update your <a href="' . $setting_link . '" target="_self">' . $setting_text . '</a> to benefit from recent security improvements.';

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
}

/**
 * Returns TRUE if an upgrade from v1.0.5 or older is detected and settings
 * update is required. Returns FALSE if there is no upgrade from older version
 * required (never installed older version, or settings update is done).
 *
 * @return bool
 */
function triplea_payment_gateway_settings_upgrade_required() {
	// Format: $wc_plugin_id + $plugin_own_id + option key
	$plugin_options  = 'woocommerce_' . 'triplea_payment_gateway' . '_settings';
	$plugin_settings = get_option( $plugin_options );
	if ( ! empty( $plugin_settings ) ) {
		// Old version will have this
		$triplea_pubkey_id                = $plugin_settings['triplea_pubkey_id'];
		$triplea_pubkey_id_for_conversion = $plugin_settings['triplea_pubkey_id_for_conversion'];
		// Old version didn't have this
		$triplea_server_public_enc_key_btc        = $plugin_settings['triplea_server_public_enc_key_btc'];
		$triplea_server_public_enc_key_conversion = $plugin_settings['triplea_server_public_enc_key_conversion'];
		if ( ( empty( $triplea_pubkey_id ) || ! empty( $triplea_server_public_enc_key_btc ) )
		  && ( empty( $triplea_pubkey_id_for_conversion ) || ! empty( $triplea_server_public_enc_key_conversion ) )
		) {
			// For any existing wallets, we have the required server public encryption key.
			// No notices to display
			return false;
		} else {
			return true;
		}
	}
	return false;
}


function triplea_payment_gateway_for_woocommerce_decrypt_payload( $balance_payload_full, $client_secret_enc_key, $triplea_public_enc_key ) {

	$status                    = 'ok';
	$status_msg                = '';
	$balance_payload_decrypted = false;

	// Format: $wc_plugin_id + $plugin_own_id + option key
	$plugin_options    = 'woocommerce_' . 'triplea_payment_gateway' . '_settings';
	$plugin_settings   = get_option( $plugin_options );
	$debug_log_enabled = $plugin_settings['debug_log_enabled'];

	triplea_write_log( 'decrypt_payload : checking, preparing to decrypt payload ', $debug_log_enabled );

	if ( empty( $balance_payload_full ) ) {
		triplea_write_log( 'decrypt_payload : ERROR! Empty encrypted balance payload.', $debug_log_enabled );

		$status                    = 'failed';
		$status_msg                = 'Empty encrypted balance payload.';
		$balance_payload_decrypted = false;
	} else {
		$balance_payload_parts = explode( ':', $balance_payload_full );
		if ( count( $balance_payload_parts ) < 2 ) {
			triplea_write_log( 'decrypt_payload : ERROR. Encrypted balance payload wrong format or missing nonce.', $debug_log_enabled );

			$status                    = 'failed';
			$status_msg                = 'Encrypted balance payload wrong format or missing nonce.';
			$balance_payload_decrypted = false;
		} else {
			$balance_payload = $balance_payload_parts[0];
			$message_nonce   = $balance_payload_parts[1];

			// triplea_write_log('decrypt_payload : balance_payload ' . print_r($balance_payload, true));
			// triplea_write_log('decrypt_payload : message_nonce ' . print_r($message_nonce, true));
			// triplea_write_log('decrypt_payload : client_secret_enc_key ' . print_r($client_secret_enc_key, true));
			// triplea_write_log('decrypt_payload : triplea_public_enc_key ' . print_r($triplea_public_enc_key, true));

			$triplea_to_client_keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(
				base64_decode( $client_secret_enc_key ),
				base64_decode( $triplea_public_enc_key )
			);
			$balance_payload_decrypted = sodium_crypto_box_open(
				base64_decode( $balance_payload ),
				base64_decode( $message_nonce ),
				$triplea_to_client_keypair
			);
			if ( $balance_payload_decrypted === false ) {
				triplea_write_log( 'decrypt_payload : ERROR! Problem decrypting balance payload.', $debug_log_enabled );

				$status     = 'failed';
				$status_msg = 'Problem decrypting balance payload.';
			} else {
				// triplea_write_log('decrypt_payload : Decrypted: ', $debug_log_enabled);
				// triplea_write_log($balance_payload_decrypted, $debug_log_enabled);
			}
		}
	}

	triplea_write_log( '_decrypt_payload() : Result: ' . json_encode( $balance_payload_decrypted, JSON_PRETTY_PRINT ), $debug_log_enabled );

	return array(
		'status'     => $status,
		'status_msg' => $status_msg,
		'payload'    => $balance_payload_decrypted,
	);
}


