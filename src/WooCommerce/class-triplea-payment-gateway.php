<?php
/**
 * Implements a WC_Payment_Gateway.
 */

namespace TripleA_Payment_Gateway_For_WooCommerce\WooCommerce;

use Exception;
use SodiumException;
use TripleA_Payment_Gateway_For_WooCommerce\API\API;
use WC_AJAX;
use WC_HTTPS;
use WC_Payment_Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TripleA_Payment_Gateway
 *
 * @package TripleA_Payment_Gateway_For_WooCommerce\WooCommerce
 */
class TripleA_Payment_Gateway extends WC_Payment_Gateway {

	/**
	 * @var API
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $triplea_mode;

	/**
	 * @var string
	 */
	protected $triplea_notifications_email;

	/**
	 * @var string
	 */
	protected $triplea_client_secret_key;

	/**
	 * @var string
	 */
	protected $triplea_client_public_key;

	/**
	 * @var string
	 */
	protected $triplea_pubkey_id;
	/**
	 * @var string
	 */
	protected $triplea_pubkey_id_for_conversion;

	/**
	 * @var string
	 */
	protected $triplea_active_pubkey_id;

	/**
	 * @var string
	 */
	protected $triplea_pubkey;

	/**
	 * @var string
	 */
	protected $triplea_payment_mode;
	
	/**
	 * @var string
	 */
	protected $triplea_server_public_enc_key_btc;
	/**
	 * @var string
	 */
	protected $triplea_server_public_enc_key_conversion;

	/**
	 * TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment constructor.
	 *
	 * @throws SodiumException
	 */
	public function __construct() {

		$this->api = API::get_instance();

		$this->id           = 'triplea_payment_gateway';
		$this->method_title = __( 'Bitcoin Payment Gateway (by TripleA)', 'triplea-payment-gateway-for-woocommerce' );
		$this->has_fields   = true;
		$this->supports     = array(
			'products',
		);
		$this->init_form_fields();
		$this->init_settings();

		$debug_log_enabled = $this->get_option( 'debug_log_enabled' ) === 'yes' ? true : false;
		// triplea_write_log('Debug logging enabled.', $debug_log_enabled);

		$debug_log_clear_action = $this->get_option( 'debug_log_clear_action' ) === 'yes' ? true : false;
		if ( $debug_log_clear_action ) {
			triplea_write_log( 'Clearing debug log..', $debug_log_enabled );
			$uploads  = wp_upload_dir( null, false );
			$logs_dir = $uploads['basedir'] . '/triplea-bitcoin-payment-logs';
			// $log_file = fopen( $logs_dir . '/' . 'triple-bitcoin-payment-logs.log', 'r+' );
			$log_file = $logs_dir . '/' . 'triplea-bitcoin-payment-logs.log';
			file_put_contents( $log_file, '' );
			$this->update_option( 'debug_log_clear_action', false );
			triplea_write_log( 'Debug log cleared!', $debug_log_enabled );
		}

		$this->triplea_set_api_endpoint_token( $debug_log_enabled );

		/**
		 * Display text customisation options
		 */
		$this->description = __( 'Secure and easy payment with Bitcoin using the TripleA.io service.', 'triplea-payment-gateway-for-woocommerce' );

		$this->triplea_mode                = $this->get_option( 'triplea_mode' );
		$this->triplea_notifications_email = $this->get_option( 'triplea_notifications_email' );

		$this->triplea_client_secret_key = $this->get_option( 'triplea_client_secret_key' );
		$this->triplea_client_public_key = $this->get_option( 'triplea_client_public_key' );

		// triplea_write_log('local secret key: ' . $this->triplea_client_secret_key, $debug_log_enabled);
		// triplea_write_log('local public key: ' . $this->triplea_client_public_key, $debug_log_enabled);

		if ( function_exists( 'sodium_crypto_box_keypair' ) ) {
			if ( ! isset( $this->triplea_client_public_key ) || empty( $this->triplea_client_public_key ) ) {
				$client_keypair                  = sodium_crypto_box_keypair();
				$this->triplea_client_secret_key = base64_encode( sodium_crypto_box_secretkey( $client_keypair ) );
				$this->triplea_client_public_key = base64_encode( sodium_crypto_box_publickey( $client_keypair ) );

				$this->update_option( 'triplea_client_secret_key', $this->triplea_client_secret_key );
				$this->update_option( 'triplea_client_public_key', $this->triplea_client_public_key );

				triplea_write_log( 'No keypair found. Generated new pair, pubkey = ' . $this->triplea_client_public_key, $debug_log_enabled );
			}
		} else {
			triplea_write_log( 'ERROR! Function sodium_crypto_box_keypair() not found.', $debug_log_enabled );
		}

		$this->triplea_pubkey = $this->get_option( 'triplea_pubkey' );
		if ( strlen( $this->triplea_pubkey ) > 12 ) {
			$short_pubkey         = substr( $this->triplea_pubkey, 0, 8 ) . '...';
			$this->triplea_pubkey = $short_pubkey;
			$this->update_option( 'triplea_pubkey', $this->triplea_pubkey );
		}

		$this->triplea_pubkey_id                = $this->get_option( 'triplea_pubkey_id' );
		$this->triplea_pubkey_id_for_conversion = $this->get_option( 'triplea_pubkey_id_for_conversion' );
		$this->triplea_active_pubkey_id         = $this->get_option( 'triplea_active_pubkey_id' );

		if ( $this->triplea_active_pubkey_id === $this->triplea_pubkey_id ) {

			$this->triplea_payment_mode = 'bitcoin-to-bitcoin';
			$this->update_option( 'triplea_payment_mode', $this->triplea_payment_mode );
		} elseif ( $this->triplea_active_pubkey_id === $this->triplea_pubkey_id_for_conversion ) {

			$this->triplea_payment_mode = 'bitcoin-to-cash';
			$this->update_option( 'triplea_payment_mode', $this->triplea_payment_mode );
		} else {
			$this->update_option( 'triplea_payment_mode', '' );
			triplea_write_log( 'ERROR! TripleA Payment mode not set. (Selecting active wallet will update this.)', $debug_log_enabled );
		}

		$this->triplea_server_public_enc_key_btc        = $this->get_option( 'triplea_server_public_enc_key_btc' );
		$this->triplea_server_public_enc_key_conversion = $this->get_option( 'triplea_server_public_enc_key_conversion' );

		$this->triplea_pubkey_id_accountname_for_conversion = $this->get_option( 'triplea_pubkey_id_accountname_for_conversion' );
		$this->triplea_pubkey_id_accountname                = $this->get_option( 'triplea_pubkey_id_accountname' );
		$this->triplea_dashboard_email                      = $this->get_option( 'triplea_dashboard_email' );

		/**
		 *  For test purposes only!
		 *  Resetting some variables.
		 *  Set a similar state to the plugin v1.0.5
		 *  (to test the upgrade path).
		 */
		$RESET_TO_V105    = false;
		$RESET_COMPLETELY = false;
		if ( $RESET_TO_V105 ) {
			$this->update_option( 'triplea_pubkey_id_accountname', null );
			$this->update_option( 'triplea_pubkey_id_accountname_for_conversion', null );
			$this->update_option( 'triplea_server_public_enc_key_btc', null );
			$this->update_option( 'triplea_server_public_enc_key_conversion', null );
			$this->update_option( 'triplea_dashboard_email', null );
			$this->update_option( 'triplea_active_pubkey_id', null );
			$this->update_option( 'triplea_pubkey_id', null );
			$this->update_option( 'triplea_pubkey_id_for_conversion', null );
			$this->update_option( 'triplea_payment_mode', null );
			$this->update_option( 'triplea_mode', null );
			triplea_write_log( 'VARIABLES STATE COMPLETELY RESET', $debug_log_enabled );
		}

		// Sensitive data
		// triplea_write_log('For Bitcoin settlement:', $debug_log_enabled);
		// triplea_write_log('__Public Key = ' . substr($this->triplea_pubkey,0,8) . '.', $debug_log_enabled);
		// triplea_write_log('__API ID = ' . $this->triplea_pubkey_id . '.', $debug_log_enabled);
		// triplea_write_log('__Svr Pub Enc Key = ' . $this->triplea_server_public_enc_key_btc . '.', $debug_log_enabled);
		// triplea_write_log('For Local currency settlement:', $debug_log_enabled);
		// triplea_write_log('__API ID = ' . $this->triplea_pubkey_id_for_conversion . '.', $debug_log_enabled);
		// triplea_write_log('__Svr Pub Enc Key = ' . $this->triplea_server_public_enc_key_conversion . '.', $debug_log_enabled);

		triplea_write_log( 'Chosen settlement mode: ' . $this->get_option( 'triplea_payment_mode' ), $debug_log_enabled );

		/**
		 *  Properly store data for both payment modes, separately.
		 */
		if ( isset( $this->triple_payment_mode ) ) {
			if ( 'bitcoin-to-bitcoin' === $this->triplea_payment_mode ) {
				if ( ! isset( $this->triplea_active_pubkey_id ) || $this->triplea_active_pubkey_id !== $this->triplea_pubkey_id ) {
					$api_pubkey_id                  = $this->triplea_pubkey_id;
					$this->triplea_active_pubkey_id = $api_pubkey_id;
					$this->update_option( 'triplea_active_pubkey_id', $api_pubkey_id );
					// triplea_write_log('Active API ID set to "'.$api_pubkey_id.'".', $debug_log_enabled);
				} else {
					// triplea_write_log('Active API ID was already set to this API ID. No change required.', $debug_log_enabled);
				}
			} elseif ( 'bitcoin-to-cash' === $this->triplea_payment_mode ) {
				if ( ! isset( $this->triplea_active_pubkey_id ) || $this->triplea_active_pubkey_id !== $this->triplea_pubkey_id_for_conversion ) {
					$api_pubkey_id                  = $this->triplea_pubkey_id_for_conversion;
					$this->triplea_active_pubkey_id = $api_pubkey_id;
					$this->update_option( 'triplea_active_pubkey_id', $api_pubkey_id );
					// triplea_write_log('Active API ID set to "'.$api_pubkey_id.'".', $debug_log_enabled);
				} else {
					// triplea_write_log('  Active API ID was already set to this API ID. No change required.', $debug_log_enabled);
				}
			}
		}

		$is_enabled = $this->get_option( 'enabled' );

		if ( ! isset( $this->triplea_active_pubkey_id ) || empty( $this->triplea_active_pubkey_id ) ) {
			$is_enabled = 'no';
			triplea_write_log( 'Disabling Bitcoin Payments, there is no active API ID.', $debug_log_enabled );
		}

		$this->enabled = $is_enabled;
		$this->update_option( 'enabled', $is_enabled );

		$this->method_description = sprintf(
			__(
				"With TripleA, you get to choose.<br>
You can receive your transaction payments in bitcoins or in your local currency. Just provide your wallet's public key to receive bitcoins.<br>If you want to receive fiat currency (USD, EUR, and more), create a TripleA wallet within seconds directly from WordPress.<br>You just need to provide your email address to get started.",
				'triplea-payment-gateway-for-woocommerce'
			)
		);

		if ( 'test' === $this->triplea_mode ) {
			$this->description = $this->description . sprintf( '<br>' . '<strong>' . __( 'TripleA TEST MODE enabled.', 'triplea-payment-gateway-for-woocommerce' ) . '</strong>' );
			$this->description = trim( $this->description );
		}

		// Save settings page options, defined in standard settings page file.
		if ( is_admin() ) {
			add_action(
				'woocommerce_update_options_payment_gateways_' . $this->id,
				array(
					$this,
					'process_admin_options',
				)
			);
		}

		// Save settings page options as defined in nested/injected HTML content.
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'save_plugin_options' ) );

		/**
		 * Remove the payment button.
		 */
		add_filter(
			'woocommerce_order_button_html',
			array(
				$this,
				'display_custom_payment_button',
			)
		);
		add_filter(
			'woocommerce_pay_order_button_html',
			array(
				$this,
				'display_custom_payment_button',
			)
		);

		// add_action( 'woocommerce_receipt_' . $this->id, array(
		// $this,
		// 'pay_for_order'
		// ) );
	}

	// public function payment_scripts() {
	// if ( ! is_product() && ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) && ! is_add_payment_method_page() && ! isset( $_GET['change_payment_method'] ) || ( is_order_received_page() ) ) { // wpcs: csrf ok.
	// if ( ! isset( $_GET['pay_for_order'] ) && ! isset( $_GET['change_payment_method'] ) || ( is_order_received_page() ) ) { // wpcs: csrf ok.
	// return;
	// }
	//
	// if ( 'no' === $this->enabled ) {
	// return;
	// }
	//
	// wp_register_script( 'woocommerce_stripe', plugins_url( 'assets/js/pay_for_order.js', WC_STRIPE_MAIN_FILE ), array( 'jquery-payment', 'stripe' ), WC_STRIPE_VERSION, true );
	// }

	/**
	 * @see WC_Settings_API::init_form_fields()
	 */
	public function init_form_fields() {
		$this->form_fields = include 'triplea-payment-gateway-settings-page.php';
		wp_enqueue_media();

		$this->init_extra_form_fields();
	}

	public function init_extra_form_fields() {
		/**
		 *  Fields below need to be specified like this because they're not added
		 * in the usual way, rather they're part of an imported template.
		 */
		$extra_fields = array(
		 // 'woocommerce_triplea_payment_gateway_triplea_dashboard_email_btc' => [],
		);
		$post_data = $this->get_post_data();

		foreach ( $extra_fields as $key => $field ) {
			try {
				if ( ! empty( $this->get_field_value( $key, $field, $post_data ) ) ) {
					$this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
				}
			} catch ( Exception $e ) {
				 $this->add_error( $e->getMessage() );
			}
		}

		/**
		 *  Below fields are used in this class and need to be set/saved/updated.
		 */
		$private_fields = array(
			'triplea_client_public_key' => array(),
			'triplea_client_secret_key' => array(),
		);
		foreach ( $private_fields as $key => $field ) {
			try {
				if ( ! isset( $this->settings[ $key ] ) ) {
					 $this->settings[ $key ] = $this->get_field_value( $key, $field, '' );
				}
			} catch ( Exception $e ) {
				$this->add_error( $e->getMessage() );
			}
		}

		// Update the settings saved as an option,
		return update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
	}

	/**
	 *
	 */
	public function save_plugin_options() {
		$triplea_statuses = array(
			'new'       => 'New Order',
			'paid'      => 'Paid (awaiting confirmation)',
			'confirmed' => 'Paid (confirmed)',
			// 'complete' => 'Complete',
			'refunded'  => 'Refunded',
			'invalid'   => 'Invalid',
		);

		$wcStatuses = wc_get_order_statuses();

		if ( isset( $_POST['triplea_woocommerce_order_states'] ) ) {

			$orderStates = $this->settings['triplea_woocommerce_order_states'];

			foreach ( $triplea_statuses as $triplea_state => $triplea_name ) {
				if ( false === isset( $_POST['triplea_woocommerce_order_states'][ $triplea_state ] ) ) {
					continue;
				}

				$wcState = $_POST['triplea_woocommerce_order_states'][ $triplea_state ];

				if ( true === array_key_exists( $wcState, $wcStatuses ) ) {
					$orderStates[ $triplea_state ] = $wcState;
				}
			}

			if ( isset( $_POST['triplea_bitcoin_logo_option'] ) ) {
				$this->settings['triplea_bitcoin_logo_option'] = $_POST['triplea_bitcoin_logo_option'];
			} else {
				$this->settings['triplea_bitcoin_logo_option'] = 'large-logo';
			}

			$this->settings['triplea_woocommerce_order_states'] = $orderStates;
		}

		if ( isset( $_POST['triplea_bitcoin_text_custom_value'] ) ) {
			$this->settings['triplea_bitcoin_text_custom_value'] = $_POST['triplea_bitcoin_text_custom_value'];
		}

		if ( isset( $_POST['triplea_bitcoin_text_option'] ) ) {
			$this->settings['triplea_bitcoin_text_option'] = $_POST['triplea_bitcoin_text_option'];
		} else {
			$this->settings['triplea_bitcoin_text_option'] = 'default-text';
		}

		if ( isset( $_POST['triplea_bitcoin_logo_option'] ) ) {
			$this->settings['triplea_bitcoin_logo_option'] = $_POST['triplea_bitcoin_logo_option'];
		} else {
			$this->settings['triplea_bitcoin_logo_option'] = 'large-logo';
		}

		if ( isset( $_POST['triplea_bitcoin_descriptiontext_option'] ) ) {
			$this->settings['triplea_bitcoin_descriptiontext_option'] = $_POST['triplea_bitcoin_descriptiontext_option'];
		} else {
			$this->settings['triplea_bitcoin_descriptiontext_option'] = 'desc-default';
		}

		if ( isset( $_POST['triplea_bitcoin_descriptiontext_value'] ) ) {
			$this->settings['triplea_bitcoin_descriptiontext_value'] = $_POST['triplea_bitcoin_descriptiontext_value'];
		}

		// Update the settings saved as an option,
		update_option( $this->get_option_key(), apply_filters( 'woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings ), 'yes' );
	}

	/**
	 *  Check if endpoint token has been generated.
	 *  Endpoint token is part of the web hook URL provided to the TripleA API.
	 *  Incoming requests without a correct token are filtered out (spam filter).
	 *
	 *  @param $debug_log_enabled
	 */
	protected function triplea_set_api_endpoint_token( $debug_log_enabled ) {
		if ( empty( get_option( 'triplea_api_endpoint_token' ) ) ) {
			if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
				$api_endpoint_token = md5( bin2hex( openssl_random_pseudo_bytes( 16 ) ) . ( uniqid( rand(), true ) ) );
			} else {
				$api_endpoint_token = md5( ( uniqid( rand(), true ) ) . ( uniqid( rand(), true ) ) );
			}
			add_option( 'triplea_api_endpoint_token', $api_endpoint_token );
		}
		// TODO if ($log_sensitive_data) triplea_write_log('Backend token: '.get_option('triplea_api_endpoint_token'), $debug_log_enabled);
	}

	/**
	 * Handle AJAX request to start checkout flow, first triggering form
	 * validation if necessary.
	 *
	 * @since 1.6.0
	 */
	public static function wc_ajax_start_checkout() {

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], '_wc_triplea_start_checkout_nonce' ) ) {
			wp_die( __( 'Bad attempt', 'triplea-payment-gateway-for-woocommerce' ) );
		}

		add_action( 'woocommerce_after_checkout_validation', array( self::class, 'start_checkout_check' ), 10, 2 );
		WC()->checkout->process_checkout();
	}

	/**
	 * Report validation errors if any, or else save form data in session and
	 * proceed with checkout flow.
	 *
	 * @param $data
	 * @param null $errors
	 *
	 * @since 1.6.4
	 */
	public static function start_checkout_check( $data, $errors = null ) {
		if ( is_null( $errors ) ) {
			// Compatibility with WC <3.0: get notices and clear them so they don't re-appear.
			$error_messages = wc_get_notices( 'error' );
			wc_clear_notices();
		} else {
			$error_messages = $errors->get_error_messages();
		}

		if ( empty( $error_messages ) ) {
			$customer_data = self::get_customer_data( $_POST );

			wp_send_json_success(
				array(
					// 'token' => ...
					// 'order_payload' => ...
					'status'        => 'ok',
					'customer_data' => $customer_data,
				)
			);
		} else {
			wp_send_json_error(
				array(
					'messages' => $error_messages,
					'status'   => 'notok',
				)
			);
		}
		exit;
	}

	protected static function get_customer_data( $post_data ) {
		$customer = array();

		$billing_first_name = empty( $post_data['billing_first_name'] ) ? '' : wc_clean( $post_data['billing_first_name'] );
		$billing_last_name  = empty( $post_data['billing_last_name'] ) ? '' : wc_clean( $post_data['billing_last_name'] );
		$billing_country    = empty( $post_data['billing_country'] ) ? '' : wc_clean( $post_data['billing_country'] );
		$billing_address_1  = empty( $post_data['billing_address_1'] ) ? '' : wc_clean( $post_data['billing_address_1'] );
		$billing_address_2  = empty( $post_data['billing_address_2'] ) ? '' : wc_clean( $post_data['billing_address_2'] );
		$billing_city       = empty( $post_data['billing_city'] ) ? '' : wc_clean( $post_data['billing_city'] );
		$billing_state      = empty( $post_data['billing_state'] ) ? '' : wc_clean( $post_data['billing_state'] );
		$billing_postcode   = empty( $post_data['billing_postcode'] ) ? '' : wc_clean( $post_data['billing_postcode'] );
		$billing_phone      = empty( $post_data['billing_phone'] ) ? '' : wc_clean( $post_data['billing_phone'] );
		$billing_email      = empty( $post_data['billing_email'] ) ? '' : wc_clean( $post_data['billing_email'] );

		if ( isset( $post_data['ship_to_different_address'] ) ) {
			$shipping_first_name = empty( $post_data['shipping_first_name'] ) ? '' : wc_clean( $post_data['shipping_first_name'] );
			$shipping_last_name  = empty( $post_data['shipping_last_name'] ) ? '' : wc_clean( $post_data['shipping_last_name'] );
			$shipping_country    = empty( $post_data['shipping_country'] ) ? '' : wc_clean( $post_data['shipping_country'] );
			$shipping_address_1  = empty( $post_data['shipping_address_1'] ) ? '' : wc_clean( $post_data['shipping_address_1'] );
			$shipping_address_2  = empty( $post_data['shipping_address_2'] ) ? '' : wc_clean( $post_data['shipping_address_2'] );
			$shipping_city       = empty( $post_data['shipping_city'] ) ? '' : wc_clean( $post_data['shipping_city'] );
			$shipping_state      = empty( $post_data['shipping_state'] ) ? '' : wc_clean( $post_data['shipping_state'] );
			$shipping_postcode   = empty( $post_data['shipping_postcode'] ) ? '' : wc_clean( $post_data['shipping_postcode'] );
		} else {
			$shipping_first_name = $billing_first_name;
			$shipping_last_name  = $billing_last_name;
			$shipping_country    = $billing_country;
			$shipping_address_1  = $billing_address_1;
			$shipping_address_2  = $billing_address_2;
			$shipping_city       = $billing_city;
			$shipping_state      = $billing_state;
			$shipping_postcode   = $billing_postcode;
		}

		$customer['shipping_country']   = $shipping_country;
		$customer['shipping_address']   = $shipping_address_1;
		$customer['shipping_address_2'] = $shipping_address_2;
		$customer['shipping_city']      = $shipping_city;
		$customer['shipping_state']     = $shipping_state;
		$customer['shipping_postcode']  = $shipping_postcode;

		$customer['shipping_first_name'] = $shipping_first_name;
		$customer['shipping_last_name']  = $shipping_last_name;
		$customer['billing_first_name']  = $billing_first_name;
		$customer['billing_last_name']   = $billing_last_name;

		$customer['billing_country']   = $billing_country;
		$customer['billing_address_1'] = $billing_address_1;
		$customer['billing_address_2'] = $billing_address_2;
		$customer['billing_city']      = $billing_city;
		$customer['billing_state']     = $billing_state;
		$customer['billing_postcode']  = $billing_postcode;
		$customer['billing_phone']     = $billing_phone;
		$customer['billing_email']     = $billing_email;

		return $customer;
	}

	public function triplea_payment_gateway_for_woocommerce_notice_payments_enabled() {
		echo '<div class="updated notice is-dismissable"><p>' . __( 'You are now accepting bitcoin payments.', 'triplea-payment-gateway-for-woocommerce' ) . '</p></div>';
	}

	public function triplea_payment_gateway_for_woocommerce_notice_payments_disabled() {
		echo '<div class="updated notice is-dismissable"><p>' . __( 'Bitcoin Payments disabled.', 'triplea-payment-gateway-for-woocommerce' ) . '</p></div>';
	}

	public function triplea_payment_gateway_for_woocommerce_notice_payments_disabled_missing_apiid() {
		echo '<div class="error notice is-dismissable"><p>' . __( 'Bitcoin payments disabled, no active wallets.', 'triplea-payment-gateway-for-woocommerce' ) . '</p></div>';
	}

	/**
	 * @param string $button_html
	 *
	 * @return string
	 */
	public function display_custom_payment_button( $button_html ) {
		global $wp;

		$output = '';

		$session_btc_addr      = '';
		$session_exchange_rate = 'empty';

		if ( WC()->session !== null ) {

			$data_tx_id_token = WC()->session->get( 'triplea_payment_client_txid' );
			if ( empty( $data_tx_id_token ) ) {
				$data_tx_id_token = $this->generate_order_txid();
				WC()->session->set( 'generate_order_txid', $data_tx_id_token );
			}

			$triplea_payment_payload = WC()->session->get( 'triplea_payment_payload' );
			// TODO should empty this value during order processing, if successful?

			// TODO remove debug 'true'
			if ( true || empty( $triplea_payment_payload ) ) {
				$triplea_payment_payload = $this->prepare_encrypted_order_payload( $data_tx_id_token );
				WC()->session->set( 'triplea_payment_payload', $triplea_payment_payload );
			}

			$public_key_shared = WC()->session->get( 'triplea_payment_public_key_shared' );

			// TODO remove debug 'true'
			if ( true || empty( $public_key_shared ) ) {
				$public_key_shared = $this->prepare_encrypted_public_key_shared();
				WC()->session->set( 'triplea_payment_public_key_shared', $public_key_shared );
			}
		} else {
			$data_tx_id_token = $this->generate_order_txid();
			WC()->session->set( 'generate_order_txid', $data_tx_id_token );

			$triplea_payment_payload = $this->prepare_encrypted_order_payload( $data_tx_id_token );
			WC()->session->set( 'triplea_payment_payload', $triplea_payment_payload );

			$public_key_shared = $this->prepare_encrypted_public_key_shared();
			WC()->session->set( 'triplea_payment_public_key_shared', $public_key_shared );
		}

		if ( is_checkout_pay_page() ) {
			$data_order_id = get_query_var( 'order-pay' );
			$order         = wc_get_order( $data_order_id );

			$data_amount   = esc_attr( ( ( WC()->version < '2.7.0' ) ? $order->order_total : $order->get_total() ) );
			$data_currency = esc_attr( ( ( WC()->version < '2.7.0' ) ? $order->order_currency : $order->get_currency() ) );
		} else {
			$data_amount   = esc_attr( WC()->cart->total );
			$data_currency = esc_attr( strtoupper( get_woocommerce_currency() ) );
		}

		$source_script = plugin_dir_url( __DIR__ ) . '/Frontend/js/triplea-payment-gateway-app.js';

		$nonce_action             = '_wc_triplea_start_checkout_nonce';
		$start_checkout_url       = WC_AJAX::get_endpoint( 'wc_triplea_start_checkout' );
		$start_checkout_nonce_url = wp_nonce_url( $start_checkout_url, $nonce_action );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() );
		if ( defined( 'TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION' ) ) {
			$plugin_version = TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION;
		} else {
			$plugin_version = 'missing';
		}

		$output .= "<div id='triplea-payment-gateway-checkout-wrapper' style='display:none;'>";
		$output .= "<div id='triplea-payment-gateway-start-checkout-check-url' style='display:none;' data-value='$start_checkout_nonce_url'></div>";
		$output .= "<button type='button' title='Show payment QR code' style='margin:auto;display:block;' class='button alt triplea-payment-gateway-btn'>Pay with Bitcoin</button>";
		$output .= "<div id='triplea-payment-gateway-script-wrapper'>";
		$output .= "<script src='$source_script' id='triplea-payment-gateway-script' data-tx-id='$data_tx_id_token' data-amount='$data_amount' data-currency='$data_currency' data-payment-addr='$session_btc_addr' data-xrate='$session_exchange_rate' data-payload='$triplea_payment_payload' data-pubkey-shared='$public_key_shared' data-api-id='$this->triplea_active_pubkey_id'></script>";

		ob_start();
		include realpath( __DIR__ . '/..' ) . '/Frontend/triplea-payment-gateway-template.php';
		$source_template_contents = ob_get_contents();
		ob_end_clean();
		 $output .= "\n" . $source_template_contents . "\n";

		$output .= '</div>';
		$output .= '</div>';
		$output .= '';

		?>
	<script>
	  (function ($, window, document) {
		'use strict';

		console.debug('action1');
		
		let isTripleaPaymentGateway = $(this).is('#payment_method_triplea_payment_gateway');

		  console.debug('action2');
		  
		  if (isTripleaPaymentGateway) {
			// Check if customer/billing/shipping form is correctly filled in, before proceeding with letting user pay.
			$( '#place_order' ).parent().children('button').hide();
			$( '#place_order' ).parent().children('[type="button"]').hide();
			$( '#place_order' ).parent().children('[type="submit"]').hide();
			$('#triplea-payment-gateway-checkout-wrapper').toggle(true);
		  }
		  else {
			$( '#place_order' ).parent().children('button').show();
			$( '#place_order' ).parent().children('[type="button"]').show();
			$( '#place_order' ).parent().children('[type="submit"]').show();
			$('#triplea-payment-gateway-checkout-wrapper').toggle(false);
		  }
		
		$('form.checkout, form#order_review').on('click', 'input[name="payment_method"]', function () {
		  let isTripleaPaymentGateway = $(this).is('#payment_method_triplea_payment_gateway');

		  console.debug('action2');
		  
		  if (isTripleaPaymentGateway) {
			// Check if customer/billing/shipping form is correctly filled in, before proceeding with letting user pay.
			$( '#place_order' ).parent().children('button').hide();
			$( '#place_order' ).parent().children('[type="button"]').hide();
			$( '#place_order' ).parent().children('[type="submit"]').hide();
			$('#triplea-payment-gateway-checkout-wrapper').toggle(true);
		  }
		  else {
			$( '#place_order' ).parent().children('button').show();
			$( '#place_order' ).parent().children('[type="button"]').show();
			$( '#place_order' ).parent().children('[type="submit"]').show();
			$('#triplea-payment-gateway-checkout-wrapper').toggle(false);
		  }
		});

		<?php if ( ! $is_ajax ) : ?>
		$.get('https://moneyoverip.io/api/ping_pageloaded?plugin_v=<?php echo $plugin_version; ?>&usage=woocommerce', function( data ) {});
		<?php endif; ?>

	  })(jQuery, window, document);
	</script>
		<?php

		return $button_html . $output;
	}

	/**
	 * Generate a strong randomly generated token,
	 * used to identify a user's cart order before and after the order has been
	 * placed.
	 *
	 * @return string
	 */
	protected function generate_order_txid() {
		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			$data_tx_id_token = md5( bin2hex( openssl_random_pseudo_bytes( 16 ) ) . ( uniqid( rand(), true ) ) );
		} else {
			$data_tx_id_token = md5( ( uniqid( rand(), true ) ) . ( uniqid( rand(), true ) ) );
		}
		return $data_tx_id_token;
	}

	protected function prepare_encrypted_order_payload( $client_txid ) {
		/**
		 *   Order hasn't been created yet.
		 *   We can generate a payload and save it in the session of the current user or visitor.
		 */

		$debug_log_enabled = $this->get_option( 'debug_log_enabled' ) === 'yes' ? true : false;

		$user_id = '';
		$api_id  = $this->triplea_active_pubkey_id; // = $this->get_option('triplea_active_pubkey_id');

		if ( function_exists( 'is_checkout_pay_page' ) && is_checkout_pay_page() ) {
			$order_id = get_query_var( 'order-pay' );
			$order    = wc_get_order( $order_id );

			$amount   = esc_attr( ( ( WC()->version < '2.7.0' ) ? $order->order_total : $order->get_total() ) );
			$currency = esc_attr( ( ( WC()->version < '2.7.0' ) ? $order->order_currency : $order->get_currency() ) );
		} else {
			$amount   = esc_attr( WC()->cart->total );
			$currency = esc_attr( strtoupper( get_woocommerce_currency() ) );
		}

		$payload_cleartext = array(
			'client_txid'    => $client_txid,
			'user_id'        => $user_id,
			'order_amount'   => $amount,
			'api_id'         => $api_id,
			'local_currency' => $currency,
		);

		$payload_jsontext = json_encode( $payload_cleartext );

		$client_secret_key = $this->triplea_client_secret_key;
		if ( ! isset( $client_secret_key ) || empty( $client_secret_key ) ) {
			triplea_write_log( 'ERROR. No client keypair found', $debug_log_enabled );
			return '[missing client keypair]';
		}
		// triplea_write_log('local secret key: ' . $client_secret_key, $debug_log_enabled);

		if ( $this->get_option( 'triplea_payment_mode' ) === 'bitcoin-to-cash' ) {
			$server_public_key = $this->get_option( 'triplea_server_public_enc_key_conversion' );
		} elseif ( $this->get_option( 'triplea_payment_mode' ) === 'bitcoin-to-bitcoin' ) {
			$server_public_key = $this->get_option( 'triplea_server_public_enc_key_btc' );
		} else {
			triplea_write_log( 'ERROR. prepare_encrypted_order_payload(): Incorrect or missing payment mode = ' . print_r( $this->get_option( 'triplea_payment_mode' ), true ), $debug_log_enabled );
		}

		if ( empty( $server_public_key ) ) {
			  $fallback          = true;
			  $server_public_key = 'A4cxSkcL/QLPaEE5AKFevgGgSLe+/RtAov7iDf0e1Rw=';
		} else {
			$fallback = false;
		}

		$client_to_server_keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(
			base64_decode( $client_secret_key ),
			base64_decode( $server_public_key )
		);

		$message_nonce     = random_bytes( SODIUM_CRYPTO_BOX_NONCEBYTES );
		$payload_encrypted = sodium_crypto_box(
			$payload_jsontext,
			$message_nonce,
			$client_to_server_keypair
		);

		if ( $fallback ) {
			return base64_encode( $payload_encrypted ) . ':' . base64_encode( $message_nonce ) . ':' . 'fallback';
		}
		return base64_encode( $payload_encrypted ) . ':' . base64_encode( $message_nonce );
	}

	/**
	 * @return string
	 * @throws SodiumException
	 */
	protected function prepare_encrypted_public_key_shared() {

		$debug_log_enabled = 'yes' === $this->get_option( 'debug_log_enabled' ) ? true : false;

		$client_public_key = $this->triplea_client_public_key;
		$client_secret_key = $this->triplea_client_secret_key;

		if ( ! isset( $client_public_key ) || empty( $client_public_key )
		|| ! isset( $client_secret_key ) || empty( $client_secret_key ) ) {
			triplea_write_log( 'Prepare_encrypted_public_key_shared(): No keypair found', $debug_log_enabled );
			return '[missing client keypair]';
		}

		if ( $this->get_option( 'triplea_payment_mode' ) === 'bitcoin-to-cash' ) {
			$server_public_key = $this->get_option( 'triplea_server_public_enc_key_conversion' );
		} elseif ( $this->get_option( 'triplea_payment_mode' ) === 'bitcoin-to-bitcoin' ) {
			$server_public_key = $this->get_option( 'triplea_server_public_enc_key_btc' );
		} else {
			triplea_write_log( 'ERROR. prepare_encrypted_public_key_shared(): Incorrect or missing payment mode = ' . print_r( $this->get_option( 'triplea_payment_mode' ), true ), $debug_log_enabled );
		}

		if ( empty( $server_public_key ) ) {
			$fallback          = true;
			$server_public_key = 'A4cxSkcL/QLPaEE5AKFevgGgSLe+/RtAov7iDf0e1Rw=';
		} else {
			$fallback = false;
		}

		if ( ! function_exists( 'sodium_crypto_box_keypair_from_secretkey_and_publickey' ) ) {
			triplea_write_log( 'ERROR! Missing sodium_crypto_ functions', $debug_log_enabled );
			 return '[missing crypto functions]';
		}

		$message = $client_public_key; // We're providing the public key of the client (= us here)

		$message_nonce = random_bytes( SODIUM_CRYPTO_BOX_NONCEBYTES );
		$ciphertext    = sodium_crypto_secretbox(
			base64_decode( $message ),
			$message_nonce,
			base64_decode( $server_public_key )
		);
		if ( $fallback ) {
			$encrypted_public_key_shared = base64_encode( $ciphertext ) . ':' . base64_encode( $message_nonce ) . ':' . 'fallback';
		} else {
			$encrypted_public_key_shared = base64_encode( $ciphertext ) . ':' . base64_encode( $message_nonce );
		}

		return $encrypted_public_key_shared;
	}

	public function get_title() {
		if ( 'custom-text' === $this->get_option( 'triplea_bitcoin_text_option' ) ) {
			$title_text = $this->get_option( 'triplea_bitcoin_text_custom_value' );
			$title      = __( $title_text, 'triplea-payment-gateway-for-woocommerce' );

		} else {
			$title = __( 'Bitcoin', 'triplea-payment-gateway-for-woocommerce' );
		}

		return apply_filters( 'woocommerce_gateway_title', $title, $this->id );
	}

	public function get_description() {
		if ( 'desc-default' === $this->get_option( 'triplea_bitcoin_descriptiontext_option' ) || empty( $this->get_option( 'triplea_bitcoin_descriptiontext_option' ) ) ) {
			$description = __( 'Secure and easy payment with Bitcoin', 'triplea-payment-gateway-for-woocommerce' );
		} elseif ( 'desc-custom' === $this->get_option( 'triplea_bitcoin_descriptiontext_option' )
		&& ! empty( $this->get_option( 'triplea_bitcoin_descriptiontext_value' ) ) ) {
			$title_text  = $this->get_option( 'triplea_bitcoin_descriptiontext_value' );
			$description = __( $title_text, 'triplea-payment-gateway-for-woocommerce' );

		} else {
			$description = __( 'Secure and easy payment with Bitcoin', 'triplea-payment-gateway-for-woocommerce' );
		}

		return apply_filters( 'woocommerce_gateway_description', $description, $this->id );
	}

	/**
	 * Return the `<img src...` HTML for the gateway icon.
	 *
	 * Options: large-logo|short-logo|no-logo. Default: large-logo.
	 *
	 * TODO: use CSS classes.
	 * TODO: don't use !important.
	 * TODO: Set large-logo default in form-fields.
	 *
	 * @return mixed|string|void
	 */
	public function get_icon() {

		$logo = $this->get_option( 'triplea_bitcoin_logo_option' );

		switch ( $logo ) {
			case null:
			case 'large-logo':
				$iconfile = 'bitcoin-full.png';
				$style    = 'style="max-width: 100px !important;max-height: none !important;"';
				break;

			case 'short-logo':
				$iconfile = 'bitcoin.png';
				$style    = 'style="max-width: 100px !important;max-height: 30px !important;"';
				break;

			case 'no-logo':
			default:
				return;
		}
      
      $icon_url = TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_URL_PATH . 'assets/img/';
      if (is_ssl()) {
         $icon_url = WC_HTTPS::force_https_url( $icon_url );
      }
      $icon = '<img src="' . $icon_url . $iconfile  . '" alt="Bitcoin logo" ' . $style . ' />';

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	/**
	 * @return bool
	 */
	public function is_available() {

		if ( 'yes' === $this->enabled ) {
			if ( ! $this->triplea_mode && is_checkout() ) {
				return false;
			}
			if ( $this->triplea_mode !== 'test' || is_admin() || current_user_can( 'editor' ) || current_user_can( 'administrator' ) ) {
				return true;
			}
		}
		return false;
	}

	public function get_payment_values() {
		$list = '<span style="padding: 5px;"><strong> ' . WC()->cart->total . ' ' . get_woocommerce_currency() . ' </strong><strong id="triplea-payment-gateway-pay_converted_amount"> </strong><br><u>' . $this->triplea_currency_converter_description . '</u></span>
                ';
		return $list;
	}

	/**
	 * Returns true or false depending on how validation of input fields went.
	 *
	 * @return bool
	 */
	function validate_fields() {
		return true;
	}

	/**
	 * Process Payment.
	 *
	 * Process the payment. Override this in your gateway.
	 * When implemented, this should return the success and redirect in an array.
	 * e.g:
	 *
	 *   return array(
	 *       'result'   => 'success',
	 *       'redirect' => $this->get_return_url( $order )
	 *   );
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		global $wp_version; // or use //include( ABSPATH . WPINC . '/version.php' );

		$debug_log_enabled = $this->get_option( 'debug_log_enabled' ) === 'yes' ? true : false;

		triplea_write_log( 'process_payment() : Order ' . $order_id . ' placed. Updating payment information.', $debug_log_enabled );

		$wc_order = wc_get_order( $order_id );
		if ( empty( $wc_order ) ) {
			triplea_write_log( 'process_payment() : ERROR! Empty woocommerce order. Order was not placed.', $debug_log_enabled );

			return array(
				'reload'   => false,
				'refresh'  => false,
				'result'   => 'failure',
				'messages' => 'Empty woocommerce order. Order was not placed.',
			);
		}

		if ( ! isset( $_POST['triplea_balance_payload'] ) ) {
			triplea_write_log( 'process_payment() : No balance payload provided.', $debug_log_enabled );
			return array(
				'reload'   => false,
				'refresh'  => false,
				'result'   => 'failure',
				'messages' => 'No balance payload provided.',
			);
		}

		if ( isset( $this->settings['triplea_woocommerce_order_states'] ) && isset( $this->settings['triplea_woocommerce_order_states']['new'] ) ) {
			$order_status_new       = $this->settings['triplea_woocommerce_order_states']['new'];
			$order_status_paid      = $this->settings['triplea_woocommerce_order_states']['paid'];
			$order_status_confirmed = $this->settings['triplea_woocommerce_order_states']['confirmed'];
			$order_status_complete  = $this->settings['triplea_woocommerce_order_states']['complete'];
			$order_status_refunded  = $this->settings['triplea_woocommerce_order_states']['refunded'];
			$order_status_invalid   = $this->settings['triplea_woocommerce_order_states']['invalid'];
		} else {
			// default values returned by get_status()
			$order_status_new       = 'wc-pending';
			$order_status_paid      = 'wc-on-hold'; // paid but still unconfirmed
			$order_status_confirmed = 'wc-processing';
			$order_status_complete  = 'wc-processing';
			$order_status_refunded  = 'wc-refunded';
			$order_status_invalid   = 'wc-failed';
		}

		// Before decrypting server payload, make sure there is a payload!
		if ( $_POST['triplea_balance_payload'] === 'failed_expired_paid_too_little'
		|| $_POST['triplea_balance_payload'] === 'failed_expired_no_payment_detected' ) {
			// User paid too little, then the payment form expired.
			// Placing the order and marking it as "failed", to keep a trace of this in case user wants refund.
			// Should the user make a second payment in time (just not detected before form expiry) to top up,
			// a Payment Update Notification can correct the payment status still.

			$wc_order->update_status( $order_status_invalid );
			$notes = array();
			if ( $_POST['triplea_balance_payload'] === 'failed_expired_paid_too_little' ) {
				$btc_addr        = $_POST['triplea_addr'];
				$amount_btc_paid = $_POST['triplea_amount_btc_paid'];

				$notes[] = 'Paid: <strong>BTC ' . $amount_btc_paid . '</strong><br>Paid to bitcoin address:<br>' . $btc_addr . "<br><a href='https://www.blockchain.com/search?search=" . $btc_addr . "'>(View details)</a>";

				$notes[] = "Customer <strong>paid insufficient amount</strong>.<br>Payment form expired before receiving any extra transaction to make up for the difference. If you are unclear about what happened, email us at <a href='mailto:support@triple-a.io'>support@triple-a.io</a> and provide us with the bitcoin address.";

				$notes[] = 'Waiting for confirmation (in case user made another payment to the same bitcoin address to make up for the difference).';
			} elseif ( $_POST['triplea_balance_payload'] === 'failed_expired_no_payment_detected' ) {
				$notes[] = 'Payment form expired before receiving any extra transaction to make up for the difference.';
			}
			foreach ( $notes as $note ) {
				$wc_order->add_order_note( __( $note, 'triplea-payment-gateway-for-woocommerce' ) );
			}

			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $wc_order ),
			);
			// Result=success : to force the checkout page to conclude order was placed (even to order status is failed)
		}

		// Payload received, let's try to decode it
		// and proceed with server provided, trustworthy metadata.
		try {
			$balance_payload_full = sanitize_text_field( $_POST['triplea_balance_payload'] );
			// triplea_write_log('Encrypted balance payload : '.$balance_payload_full, $debug_log_enabled);

			/**
			 *  Decrypt payload received from /balance API call in front-end.
			 */
			$payload_status_data = $this->decrypt_payload( $balance_payload_full, $wc_order );
			if ( $payload_status_data['status'] === 'failed' || $payload_status_data['payload'] === false ) {
				triplea_write_log( 'process_payment() : ERROR! Payload decrypt error or status failed. ' . $payload_status_data['status'], $debug_log_enabled );

				$wc_order->update_status( $order_status_invalid );

				return array(
					'reload'   => false,
					'refresh'  => false,
					'result'   => 'failure',
					'messages' => 'Payload decrypt error or invalid order.',
				);
			}
			$balance_payload_decrypted = $payload_status_data['payload'];

			if ( empty( $balance_payload_decrypted ) ) {
				triplea_write_log( 'process_payment() : ERROR! Decrypted balance payload.', $debug_log_enabled );
				return array(
					'reload'   => false,
					'refresh'  => false,
					'result'   => 'failure',
					'messages' => 'Empty decrypted balance payload.',
				);
			}
			// triplea_write_log('process_payment() : Decrypted balance payload : ', $debug_log_enabled);
			// triplea_write_log($balance_payload_decrypted, $debug_log_enabled);

			$balance_payload_data = json_decode( $balance_payload_decrypted );
			if ( $balance_payload_data === null ) {
				triplea_write_log( 'process_payment() : ERROR! Problem decoding json from balance payload.', $debug_log_enabled );
				// Not emptying cart
				return array(
					'reload'   => false,
					'refresh'  => false,
					'result'   => 'failure',
					'messages' => 'Problem decoding balance payload metadata.',
				);
			}

			/**
			 * Check balance of address.
			 * It has to be at equal or higher to the order's required amount.
			 */

			$addr           = $balance_payload_data->addr;
			$tx_id          = $balance_payload_data->client_txid;
			$tx_status      = $balance_payload_data->tx_status;
			$exchange_rate  = $balance_payload_data->exchange_rate;
			$local_currency = $balance_payload_data->local_currency;
			$order_amount   = $balance_payload_data->order_amount;

			/**
			 *  We set a client_txid token, securely randomly generated.
			 *  This helps when receiving payment update notifications from the API,
			 *  to match the notification with the related order.
			 *  (When creating the set_addr call and payload, no order ID is available yet. Hence this method.)
			 */
			if ( 0 === count( get_post_meta( $order_id, '_triplea_tx_id' ) ) ) {
				add_post_meta( $order_id, '_triplea_tx_id', $tx_id );
			} else {
				update_post_meta( $order_id, '_triplea_tx_id', $tx_id );
			}

			$crypto_amount             = floatval( $balance_payload_data->crypto_amount ) ? floatval( $balance_payload_data->crypto_amount ) : 0.0;
			$crypto_amount_paid_unconf = floatval( $balance_payload_data->crypto_amount_paid_unconf ) ? floatval( $balance_payload_data->crypto_amount_paid_unconf ) : 0.0;
			$crypto_amount_paid_conf   = floatval( $balance_payload_data->crypto_amount_paid_conf ) ? floatval( $balance_payload_data->crypto_amount_paid_conf ) : 0.0;
			$crypto_amount_paid_total  = $crypto_amount_paid_conf + $crypto_amount_paid_unconf;

			/*
			 ---------------------------------- */
			// Optional : Send data back to API regarding orders paid using TripleA
			// Create our own payment info object, to store as meta data for the order.
			/* ---------------------------------- */

			$notes = array();
			if ( $this->triplea_mode === 'test' ) {
				$notes[] = 'Order was made in <strong>TEST mode</strong>!';
			}

			// Indicate that payment was made.
			// Payment was made. Do this to take into account for stock management or other such actions.

			// $wc_order->payment_complete('BTC' . ' address: ' . $addr);

			triplea_write_log( 'process_payment() : Order placed, paid with Bitcoin. ', $debug_log_enabled );

			// Set the expected value for the order status update function.
			// This value is different from $order_status_paid because this function is
			// used to parse encrypted payloads from TripleA Payment Update Notifications.
			$order_status = 'paid';
			$this->api->triplea_update_bitcoin_payment_order_status( $order_status, $notes, $wc_order, $addr, $tx_status, $crypto_amount_paid_total, $crypto_amount, $local_currency, $order_amount, $exchange_rate );

			foreach ( $notes as $note ) {
				$wc_order->add_order_note( __( $note, 'triplea-payment-gateway-for-woocommerce' ) );
			}

			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $wc_order ),
			);
		} catch ( Exception $error ) {
			if ( method_exists( $error, 'getJsonBody' ) ) {
					$oops          = $error->getJsonBody();
					$error_message = $oops['error']['message'];
			} else {
				  $error_message = $error->getMessage();
			}

			if ( ! function_exists( 'wc_add_notice' ) ) {
				require_once ABSPATH . '/wp-content/plugins/woocommerce/includes/wc-notice-functions.php';
			}

			wc_add_notice( __( 'Payment Failed ', 'triplea-payment-gateway-for-woocommerce' ) . '( ' . $error_message . ' ).', $notice_type = 'error' );

			return array(
				'reload'   => false,
				'refresh'  => false,
				'result'   => 'failure',
				'messages' => 'Exception occured. Message: ' . $error_message,
			);
		}
	}

	/**
	 @param $balance_payload_full
	 @param $wc_order
	 *
	 @return array
	 */
	protected function decrypt_payload( $balance_payload_full, $wc_order ) {

		$debug_log_enabled = $this->get_option( 'debug_log_enabled' ) === 'yes' ? true : false;

		// Init needed values
		$payment_mode                     = $this->triplea_payment_mode;
		$server_public_enc_key_btc        = $this->triplea_server_public_enc_key_btc;
		$server_public_enc_key_conversion = $this->triplea_server_public_enc_key_conversion;
		$client_secret_key                = $this->triplea_client_secret_key;

		if ( $payment_mode === 'bitcoin-to-bitcoin' ) {
			$triplea_public_enc_key = $server_public_enc_key_btc;
		} elseif ( $payment_mode === 'bitcoin-to-cash' ) {
			$triplea_public_enc_key = $server_public_enc_key_conversion;
		} else {
			triplea_write_log( 'decrypt_payload() : ERROR! Unknown or missing $payment_mode = ' . $payment_mode, $debug_log_enabled );
		}

		if ( empty( $triplea_public_enc_key ) ) {
			$triplea_public_enc_key = 'A4cxSkcL/QLPaEE5AKFevgGgSLe+/RtAov7iDf0e1Rw=';
		}

		$client_secret_enc_key = $client_secret_key;

		$payload_status_data = $this->api->triplea_payment_gateway_for_woocommerce_decrypt_payload( $balance_payload_full, $client_secret_enc_key, $triplea_public_enc_key );

		return $payload_status_data;
	}

	/**
	 * Helper function to get client connection details.
	 *
	 * @return array
	 */
	public function get_clients_details() {
		return array(
			'IP'      => $_SERVER['REMOTE_ADDR'],
			'Agent'   => $_SERVER['HTTP_USER_AGENT'],
			'Referer' => $_SERVER['HTTP_REFERER'],
		);
	}

	/**
	 * Process refund.
	 *
	 * If the gateway declares 'refunds' support, this will allow it to refund.
	 * a passed in amount.
	 *
	 * @param  int    $order_id Order ID.
	 * @param  float  $amount   Refund amount.
	 * @param  string $reason   Refund reason.
	 *
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return false;
	}

	/**
	 * Extending WooCommerce settings fields, adding our specific script
	 * for the TripleA Pubkey ID request.
	 *
	 * @param string $key  Field key.
	 * @param array  $data Field data.
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function generate_triplea_pubkeyid_script_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );

		$TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_API_ENDPOINT = site_url() . '?rest_route=/triplea/v1/tx_update/' . get_option( 'triplea_api_endpoint_token' );

		ob_start();
		?>
</table>

<table class="form-table">
		<?php

		return ob_get_clean();
	}

	/**
	 Generate normal text (not in a table, rather a single line of text,
	 paragraph style).

	 @param string $key  Field key.
	 @param array  $data Field data.

	 @return string
	@since  1.0.0
	 */
	public function generate_paragraph_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title' => '',
			'class' => '',
		);
		$data      = wp_parse_args( $data, $defaults );
		ob_start();
		?>
</table>
<p class="wc-settings-sub-title <?php echo esc_attr( $data['class'] ); ?>"
   id="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></p>
<table class="form-table">
		<?php
		return ob_get_clean();
	}

	public function generate_anchor_html( $key, $data ) {
		$defaults = array(
			'title' => '',
			'class' => '',
		);
		$data     = wp_parse_args( $data, $defaults );
		ob_start();
		?>
</table>
<a id="<?php echo wp_kses_post( $data['title'] ); ?>"></a>
<a name="<?php echo wp_kses_post( $data['title'] ); ?>"></a>
<table class="form-table">
		<?php
		return ob_get_clean();
	}

	public function generate_custom_html( $key, $data ) {
		$defaults = array(
			'markup' => '',
		);
		$data     = wp_parse_args( $data, $defaults );
		ob_start();
		?>
</table>
		<?php echo $data['markup']; ?>
<table class="form-table">
		<?php
		return ob_get_clean();
	}

	public function generate_text_ifnotempty_html( $key, $data ) {
		if ( empty( $this->get_option( $key ) ) ) {
			return '';
		} else {
			return $this->generate_text_html( $key, $data );
		}
	}

	/**
	 * Generate hidden Input HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function generate_hidden_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'type' => 'hidden',
		);
		$data      = wp_parse_args( $data, $defaults );

		ob_start();
		?>
	<input type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>"
	/>
		<?php
		return ob_get_clean();
	}

	public function generate_table_markup_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'       => '',
			'class'       => '',
			'description' => '',
			'markup'      => '',
		);
		$data      = wp_parse_args( $data, $defaults );
		ob_start();
		?>
  <tr valign="top" class="<?php echo esc_attr( $data['class'] ); ?>">
	<th scope="row" class="titledesc">
	  <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
	</th>
	<td class="forminp">
		<?php echo $data['markup']; ?>
	</td>
  </tr>
		<?php
		return ob_get_clean();
	}

	public function generate_order_states_html( $data ) {
		$defaults = array(
			'markup' => '',
		);
		$data     = wp_parse_args( $data, $defaults );

		ob_start();
		?>
   </table>
		<?php echo $data['markup']; ?>
   <table class="form-table">
		 <?php
			return ob_get_clean();
	}

}

