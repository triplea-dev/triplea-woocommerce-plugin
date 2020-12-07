<?php


namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\API;

use TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce\TripleA_Payment_Gateway;
use WC_Order_Query;
use WP_Error;
use WP_REST_Request;

class REST {

	/**
	 * @var API
	 */
	protected $api;

	/**
	 * REST constructor.
	 *
	 * @param API $api
	 */
	public function __construct( $api ) {
		$this->api = $api;
	}

	/**
	 * Register the REST endpoint with WordPress.
	 *
	 * @hooked rest_api_init
	 */
	public function rest_api_init() {

		register_rest_route(
			'triplea/v1',
			'/tx_update/(?P<token>[a-zA-Z0-9-_]+)',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'handle_api_tx_update' ),
            'permission_callback' => '__return_true',
			)
		);
      
      register_rest_route(
         'triplea/v1',
         '/triplea_webhook/(?P<token>[a-zA-Z0-9-_]+)',
         array(
            'methods'  => 'POST',
            'callback' => array( $this, 'handle_api_webhook_update' ),
            'permission_callback' => '__return_true',
         )
      );
	}
   
   /**
    * Web hook (remotely called URL) to which transaction update notifications
    * are sent by the TripleA service.
    *
    * @param WP_REST_Request $request
    */
   public function handle_api_webhook_update( WP_REST_Request $request ) {
      /**
       * Get plugin configuration settings.
       */
      $triplea = new TripleA_Payment_Gateway();
   
      $debug_log_enabled = $triplea->get_option('debug_log_enabled') === 'yes';
      
      // Load necessary plugin settings
      triplea_write_log( 'webhook_update : Received payment update notification. Status = ' . $request->get_param( 'status' ), $debug_log_enabled );
      triplea_write_log( 'webhook_update : - Headers = ' . print_r($request->get_headers(), true), $debug_log_enabled );
      triplea_write_log( 'webhook_update : - Params = ' . print_r($request->get_params(), true), $debug_log_enabled );
      triplea_write_log( 'webhook_update : - Body = ' . print_r($request->get_body(), true), $debug_log_enabled );
      
      $is_endpoint_token_valid = $this->verify_endpoint_token( $request->get_param( 'token' ) );
      if (!$is_endpoint_token_valid) {
         triplea_write_log( 'webhook_update(): endpoint token invalid, cannot proceed', $debug_log_enabled );
         return new WP_Error(
            'bad_token',
            'Bad token',
            array(
               'status'   => 403,
               //'settings' => $plugin_settings,
            )
         );
      }
      triplea_write_log( 'webhook_update(): valid endpoint token, processing received webhook data...', $debug_log_enabled );
   
      /**
       * Authentication of the incoming request
       */
      $order_id = null;
      $unix_timestamp = null;
      $hex_signature = null;
      $triplea_signature = $request->get_header('triplea_signature');
      //triplea_write_log( 'webhook_update(): sig header => ' . print_r($triplea_signature, true), $debug_log_enabled );
      
      if (isset($triplea_signature)) {
         $parts = preg_split("/[,]+/", $triplea_signature); // "t=<unix-timestamp>,v1=<hex-encoded-signature>"
         if (count($parts) === 2) {
            $unix_timestamp = preg_split("/[=]+/", $parts[0])[1];
            $hex_signature = preg_split("/[=]+/", $parts[1])[1];
            triplea_write_log( 'webhook_update(): sig timestamp ' . print_r($unix_timestamp, true), $debug_log_enabled );
            triplea_write_log( 'webhook_update(): sig hex sign. ' . print_r($hex_signature, true), $debug_log_enabled );

            $webhook_data  = $request->get_param( 'webhook_data' );
            triplea_write_log( 'webhook_update(): header  ' . print_r($webhook_data, true), $debug_log_enabled );
            if (!isset($webhook_data['order_txid']) || empty($webhook_data['order_txid'])) {
               triplea_write_log( 'webhook_update(): problem: missing txid in received notification webhook data.', $debug_log_enabled );
               return new WP_Error(
                  'missing_txid',
                  'Missing txid',
                  array(
                     'status'   => 400,
                  )
               );
            }
            triplea_write_log( 'webhook_update(): order txid : ' . print_r($webhook_data['order_txid'], true), $debug_log_enabled );
            $order_id = $this->triplea_get_orderid_from_txid( $webhook_data['order_txid'], $debug_log_enabled );
            if ( $order_id < 0 ) {
               triplea_write_log( 'webhook_update() : ERROR. No matching order found for tx id ' . $webhook_data['order_txid'] . '.', $debug_log_enabled );
            }
         }
         else {
            triplea_write_log( 'webhook_update(): problem with signature.', $debug_log_enabled );
            return new WP_Error(
               'bad_signature',
               'Bad signature',
               array(
                  'status'   => 403,
               )
            );
         }
      }
      else {
         triplea_write_log( 'webhook_update(): problem with signature..', $debug_log_enabled );
         return new WP_Error(
            'bad_signature',
            'Bad signature',
            array(
               'status'   => 403,
            )
         );
      }
   
      //$wc_order = wc_get_order( $order_id );
      $notify_secret = get_post_meta($order_id, '_triplea_notify_secret');
      if (is_array($notify_secret)) $notify_secret = $notify_secret[0];
      triplea_write_log( 'webhook_update(): notify_secret = '. ($notify_secret? 'true' : 'false'), $debug_log_enabled );
      $verify_signature = hash_hmac("SHA256", $unix_timestamp.'.'.$request->get_body(), $notify_secret);
      triplea_write_log( 'webhook_update(): input signature = '. $hex_signature, $debug_log_enabled );
      triplea_write_log( 'webhook_update(): local signature = '. $verify_signature, $debug_log_enabled );
      if (!$verify_signature) {
         triplea_write_log( 'webhook_update(): signature mismatch!', $debug_log_enabled );
         return new WP_Error(
            'signature_mismatch',
            'Signature mismatch',
            array(
               'status'   => 400,
            )
         );
      }
   
      $time_valid = abs(time() - $unix_timestamp) < 300;
      triplea_write_log( 'webhook_update(): time valid? = '. ($time_valid? 'true' : 'false'), $debug_log_enabled );
      if (!$time_valid) {
         triplea_write_log( 'webhook_update(): signature timestamp mismatch!', $debug_log_enabled );
         return new WP_Error(
            'signature_timestamp_mismatch',
            'Signature timestamp mismatch',
            array(
               'status'   => 400,
            )
         );
      }
      
      $payment_data = json_decode($request->get_body());
      $triplea::update_order_status($payment_data, NULL, false, $unix_timestamp, $hex_signature);
   
      return array(
         'status' => 'ok',
         'msg'    => 'Payment update notification well received and processed.',
      );
   }
	
   /**
    * Web hook (remotely called URL) to which transaction update notifications
    * are sent by the TripleA service.
    *
    * @param WP_REST_Request $request
    *
    * @return array|WP_Error
    * @throws \SodiumException
    */
	public function handle_api_tx_update( WP_REST_Request $request ) {

		/**
		 * Get plugin configuration settings.
		 */
		$plugin_options  = 'woocommerce_triplea_payment_gateway_settings'; // Format: $wc_plugin_id + $plugin_own_id + option key
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
      triplea_write_log( 'tx_update : - Params = ' . print_r($request->get_headers(), true), $debug_log_enabled );
      triplea_write_log( 'tx_update : - Params = ' . print_r($request->get_params(), true), $debug_log_enabled );
      triplea_write_log( 'tx_update : - Body = ' . print_r($request->get_body(), true), $debug_log_enabled );
      
      if ( isset( $plugin_settings['triplea_woocommerce_order_states'] ) && isset( $plugin_settings['triplea_woocommerce_order_states']['paid'] ) ) {
//			$order_status_new       = $plugin_settings['triplea_woocommerce_order_states']['new'];
			$order_status_paid      = $plugin_settings['triplea_woocommerce_order_states']['paid'];
			$order_status_confirmed = $plugin_settings['triplea_woocommerce_order_states']['confirmed'];
//			$order_status_complete  = $plugin_settings['triplea_woocommerce_order_states']['complete'];
//			$order_status_refunded  = $plugin_settings['triplea_woocommerce_order_states']['refunded'];
			$order_status_invalid   = $plugin_settings['triplea_woocommerce_order_states']['invalid'];
		} else {
			// default values returned by get_status()
//			$order_status_new       = 'wc-pending';
			$order_status_paid      = 'wc-on-hold'; // paid but still unconfirmed
			$order_status_confirmed = 'wc-processing';
//			$order_status_complete  = 'wc-processing';
//			$order_status_refunded  = 'wc-refunded';
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
			 triplea_write_log('tx_update : Client endpoint token given by TripleA API did not match. ', $debug_log_enabled);
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

		$result  = $request->get_param( 'return' );
		$status  = $request->get_param( 'status' );
		$payload = $request->get_param( 'payload' );

		triplea_write_log( 'tx_update : Received POST data. Status: ' . $status, $debug_log_enabled );
		// triplea_write_log('Received POST data: payload: ' . $payload, $debug_log_enabled);

		$payment_mode                     = $plugin_settings['triplea_payment_mode'];
		$server_public_enc_key_btc        = $plugin_settings['triplea_server_public_enc_key_btc'];
		$server_public_enc_key_conversion = $plugin_settings['triplea_server_public_enc_key_conversion'];
		if ( 'bitcoin-to-bitcoin' === $payment_mode ) {
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

		$payload_status_data = $this->api->triplea_cryptocurrency_payment_gateway_for_woocommerce_decrypt_payload( $payload, $client_secret_enc_key, $triplea_public_enc_key );
		if ( 'failed' === $payload_status_data['status'] || false === $payload_status_data['payload'] ) {
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
		if ( is_null( $balance_payload_decrypted ) ) {
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
		$order_id = $this->triplea_get_orderid_from_txid( $client_txid, $debug_log_enabled );
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
		if ( $wc_order->get_status() === $order_status_confirmed ) {
			// Order might have been marked as completed by the TripleA API, or manually by a site admin.
			triplea_write_log( 'tx_update : Order already marked as completed. Nothing to update.', $debug_log_enabled );
			// Nothing to update.
			return array(
				'status' => 'ok',
				'msg'    => 'Order already marked as completed.',
			);
		}
		
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
		$this->api->triplea_update_bitcoin_payment_order_status( $order_status, $notes, $wc_order, $addr, $tx_status, $crypto_amount_paid_total, $crypto_amount, $local_currency, $order_amount, $exchange_rate );

		foreach ( $notes as $note ) {
			$wc_order->add_order_note( $note );
		}

		return array( 'status' => 'ok' );
	}

	public function verify_endpoint_token($token) {
      // To compare and validate.
      $api_endpoint_token = get_option( 'triplea_api_endpoint_token' );
      
      /**
       * Validate security token (provided to TripleA API during TripleA PubKey ID retrieval).
       * If token is not part of the return_url invoked, this request is unauthorised.
       */
      if ( $api_endpoint_token === $token ) {
         return true;
      } else {
         //triplea_write_log('tx_update : Client endpoint token given by TripleA API did not match. ', $debug_log_enabled);
         //triplea_write_log('tx_update :   Local value: ' . $api_endpoint_token, $debug_log_enabled);
         //triplea_write_log('tx_update :   Given value: ' . $token, $debug_log_enabled);
         return false;
      }
   }

	/**
	 * Search for an order with the meta key `_triplea_tx_id` equal to the given $order_tx_id.
	 *
	 * @param string $order_tx_id
	 * @param bool   $debug_log_enabled
	 *
	 * @return int
	 */
	public function triplea_get_orderid_from_txid( $order_tx_id, $debug_log_enabled ) {
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

		try {
			$orders = $query->get_orders();
		} catch ( \Exception $e ) {
			// TODO: Log.
			return -1;
		}

		if ( count( $orders ) < 1 ) {
			return -1;
		}
		// triplea_write_log('triplea_get_orderid_from_txid(): Found orders for client_txid ' . $order_tx_id, $debug_log_enabled);
		$order_id = $orders[0];

		return $order_id;
	}
	
}

