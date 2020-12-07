<?php
/**
 * Implements a WC_Payment_Gateway.
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce;

use Exception;
use SodiumException;
use TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\API\API;
use TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\API\REST;
use WC_AJAX;
use WC_HTTPS;
use WC_Payment_Gateway;
use \Datetime;

if (!defined('ABSPATH')) {
   exit;
}


/**
 * Class TripleA_Payment_Gateway
 *
 * @package TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WooCommerce
 */
class TripleA_Payment_Gateway extends WC_Payment_Gateway {
   
   /**
    * @var API
    */
   protected $api;
   
   /**
    * Test mode enabled or not. If enabled, only Admin-role users
    * can view the payment option at checkout.
    *
    * @var string
    * @since 1.0.0
    */
   protected $triplea_mode;
   
   /**
    * used-by v1.4.3 and earlier
    * @deprecated
    * @var string
    */
   protected $triplea_notifications_email;
   /**
    * used-by v1.4.3 and earlier
    * @deprecated
    * @var string
    */
   protected $triplea_client_secret_key;
   /**
    * used-by v1.4.3
    * @deprecated
    * @var string
    */
   protected $triplea_client_public_key;
   /**
    * used-by v1.4.3
    * @deprecated
    * @var string
    */
   protected $triplea_server_public_enc_key_btc;
   /**
    * used-by v1.4.3
    * @deprecated
    * @var string
    */
   protected $triplea_server_public_enc_key_conversion;
   
   
   /************
    *
    *   v1.5.0+ properties
    *
    ************/
   
   
   /**
    * @since v1.5.0
    * @var string
    */
   protected $triplea_payment_mode;
   /**
    * @since v1.5.0
    * @var string
    */
   protected $triplea_sandbox_payment_mode;
   /**
    * @since v1.5.0
    * @var string
    */
   protected $triplea_active_api_id;
   
   
   /**
    * Merchant local currency settlement account's API access key
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_merchant_key;
   /**
    * Merchant local currency settlement account's API client id
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_client_id;
   /**
    * Merchant local currency settlement account's API client secret
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_client_secret;
   /**
    * TripleA OAuth token
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_oauth_token;
   /**
    * Expiry date for the TripleA Oauth token
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_oauth_token_expiry;
   /**
    * Merchant local currency settlement account's name
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_merchant_name;
   /**
    * Merchant local currency settlement account's email
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_merchant_email;
   /**
    * Merchant local currency settlement account's phone number
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_merchant_phone;
   /**
    * Merchant local currency settlement account's preferred local currency
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_merchant_local_currency;
   /**
    * TripleA API ID for this account
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_api_id;
   /**
    * TripleA API ID for this sandbox account
    * (for local currency settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2fiat_sandbox_api_id;
   
   
   /**
    * Merchant bitcoin settlement account's API access key
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_merchant_key;
   /**
    * Merchant bitcoin settlement account's API client id
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_client_id;
   /**
    * Merchant bitcoin settlement account's API client secret
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_client_secret;
   /**
    * TripleA OAuth token
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_oauth_token;
   /**
    * Expiry date for the TripleA Oauth token
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_oauth_token_expiry;
   /**
    * Merchant bitcoin settlement account's name
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_merchant_name;
   /**
    * Merchant bitcoin settlement account's email
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_merchant_email;
   /**
    * Merchant bitcoin settlement account's phone number
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_merchant_phone;
   /**
    * TripleA API ID for this account
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_api_id;
   /**
    * (Partial) master public key to help the user identify his wallet if needed
    * (for bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_pubkey;
   
   
   /**
    * Merchant bitcoin settlement account's API access key
    * (for testnet bitcoin settlement account)
    *
    * @var string
    */
   protected $triplea_btc2btc_sandbox_merchant_key;
   /**
    * Merchant bitcoin settlement account's API client id
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_client_id;
   /**
    * Merchant bitcoin settlement account's API client secret
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_client_secret;
   /**
    * TripleA OAuth token
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_oauth_token;
   /**
    * Expiry date for the TripleA Oauth token
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_oauth_token_expiry;
   /**
    * Merchant bitcoin settlement account's name
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_merchant_name;
   /**
    * Merchant bitcoin settlement account's email
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_merchant_email;
   /**
    * Merchant bitcoin settlement account's phone number
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_merchant_phone;
   /**
    * TripleA API ID for this account
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_api_id;
   /**
    * (Partial) master public key to help user identify the wallet used
    * (for testnet bitcoin settlement account)
    *
    * @since v1.5.0
    * @var string
    */
   protected $triplea_btc2btc_sandbox_pubkey;
   
   
   /**
    * TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment constructor.
    *
    * @throws SodiumException
    */
   public function __construct() {
      
      $this->api = API::get_instance();
      
      $this->id           = 'triplea_payment_gateway';
//      $this->method_title = '<img src="https://triple-a.io/wp-content/uploads/2020/08/TripleA_MainLogo_Transparent-e1596423970124.png" alt="Footer Logo" class="webpexpress-processed" style="max-width: 149px; vertical-align: text-bottom;display: inline-block;position: relative;top: 5px;margin-right: 15px;">'.__('Bitcoin Payment Gateway', 'triplea-cryptocurrency-payment-gateway-for-woocommerce').'</img>';
      $this->method_title = ''.__('Bitcoin Payment Gateway', 'triplea-cryptocurrency-payment-gateway-for-woocommerce').'';
      $this->has_fields   = TRUE;
      $this->supports     = [
         'products',
      ];
      
      //$this->order_button_text = 'Pay with Bitcoin';
      $this->order_button_text = 'Waiting for payment';
      
      /**
       * Display text customisation options
       */
      $this->description = __('Secure and easy payment with Bitcoin using the TripleA.io service.', 'triplea-cryptocurrency-payment-gateway-for-woocommerce');
      $this->init_form_fields();
      $this->init_settings();
      
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      
      $debug_log_clear_action = $this->get_option('debug_log_clear_action') === 'yes';
      if ($debug_log_clear_action) {
         triplea_write_log('Clearing debug log..', $debug_log_enabled);
         $uploads  = wp_upload_dir(NULL, FALSE);
         $logs_dir = $uploads['basedir'] . '/triplea-bitcoin-payment-logs';
         // $log_file = fopen( $logs_dir . '/' . 'triple-bitcoin-payment-logs.log', 'r+' );
         $log_file = $logs_dir . '/' . 'triplea-bitcoin-payment-logs.log';
         file_put_contents($log_file, '');
         $this->update_option('debug_log_clear_action', FALSE);
         triplea_write_log('Debug log cleared!', $debug_log_enabled);
      }
      
      $this->triplea_mode                = $this->get_option('triplea_mode');
      $this->triplea_notifications_email = $this->get_option('triplea_notifications_email');
      
      $this->triplea_btc2fiat_merchant_key = $this->get_option('triplea_btc2fiat_merchant_key');
      $this->triplea_btc2fiat_client_id    = $this->get_option('triplea_btc2fiat_client_id');
      $this->triplea_btc2fiat_client_secret = $this->get_option('triplea_btc2fiat_client_secret');
      $this->triplea_btc2fiat_oauth_token             = $this->get_option('triplea_btc2fiat_oauth_token');
      $this->triplea_btc2fiat_oauth_token_expiry      = $this->get_option('triplea_btc2fiat_oauth_token_expiry');
      $this->triplea_btc2fiat_merchant_name           = $this->get_option('triplea_btc2fiat_merchant_name');
      $this->triplea_btc2fiat_merchant_email          = $this->get_option('triplea_btc2fiat_merchant_email');
      $this->triplea_btc2fiat_merchant_phone          = $this->get_option('triplea_btc2fiat_merchant_phone');
      $this->triplea_btc2fiat_merchant_local_currency = $this->get_option('triplea_btc2fiat_merchant_local_currency');
      
      $this->triplea_btc2btc_merchant_key = $this->get_option('triplea_btc2btc_merchant_key');
      $this->triplea_btc2btc_client_id    = $this->get_option('triplea_btc2btc_client_id');
      $this->triplea_btc2btc_client_secret    = $this->get_option('triplea_btc2btc_client_secret');
      $this->triplea_btc2btc_merchant_name  = $this->get_option('triplea_btc2btc_merchant_name');
      $this->triplea_btc2btc_merchant_email = $this->get_option('triplea_btc2btc_merchant_email');
      $this->triplea_btc2btc_merchant_phone = $this->get_option('triplea_btc2btc_merchant_phone');
      $this->triplea_btc2btc_pubkey = $this->get_option('triplea_btc2btc_merchant_phone');
      $this->triplea_btc2btc_oauth_token = $this->get_option('triplea_btc2btc_oauth_token');
      $this->triplea_btc2btc_oauth_token_expiry = $this->get_option('triplea_btc2btc_oauth_token_expiry');
   
      $this->triplea_btc2btc_sandbox_merchant_key = $this->get_option('triplea_btc2btc_sandbox_merchant_key');
      $this->triplea_btc2btc_sandbox_client_id    = $this->get_option('triplea_btc2btc_sandbox_client_id');
      $this->triplea_btc2btc_sandbox_client_secret    = $this->get_option('triplea_btc2btc_sandbox_client_secret');
      $this->triplea_btc2btc_sandbox_merchant_name  = $this->get_option('triplea_btc2btc_sandbox_merchant_name');
      $this->triplea_btc2btc_sandbox_merchant_email = $this->get_option('triplea_btc2btc_sandbox_merchant_email');
      $this->triplea_btc2btc_sandbox_merchant_phone = $this->get_option('triplea_btc2btc_sandbox_merchant_phone');
      $this->triplea_btc2btc_sandbox_oauth_token = $this->get_option('triplea_btc2btc_sandbox_oauth_token');
      $this->triplea_btc2btc_sandbox_oauth_token_expiry = $this->get_option('triplea_btc2btc_sandbox_oauth_token_expiry');
      
      // If a pubkey was given, we only store the first bit.
      $this->triplea_btc2btc_pubkey = $this->get_option('triplea_btc2btc_pubkey');
      // If a sandbox pubkey was given, we only store the first bit.
      $this->triplea_btc2btc_sandbox_pubkey = $this->get_option('triplea_btc2btc_sandbox_pubkey');
      
      // Bitcoin settlement
      $this->triplea_btc2btc_api_id         = $this->get_option('triplea_btc2btc_api_id');
      $this->triplea_btc2btc_sandbox_api_id = $this->get_option('triplea_btc2btc_sandbox_api_id');
      // Local currency settlement
      $this->triplea_btc2fiat_api_id         = $this->get_option('triplea_btc2fiat_api_id');
      $this->triplea_btc2fiat_sandbox_api_id = $this->get_option('triplea_btc2fiat_sandbox_api_id');
      // Active account
      $this->triplea_active_api_id = $this->get_option('triplea_active_api_id');
      
      $this->triplea_payment_mode         = $this->get_option('triplea_payment_mode');
      $this->triplea_sandbox_payment_mode = $this->get_option('triplea_sandbox_payment_mode');
      if (empty($this->triplea_btc2btc_sandbox_api_id) && empty($this->triplea_btc2fiat_sandbox_api_id)) {
         $this->triplea_sandbox_payment_mode = FALSE;
      }
      $this->update_option('triplea_sandbox_payment_mode', $this->triplea_sandbox_payment_mode);
      
      // TODO consider logging whenever a btc/fiat or sandbox toggle is made
      if (!isset($this->triplea_active_api_id) || empty($this->triplea_active_api_id)) {
         triplea_write_log('Not enabling payments. Active API ID is and remains empty.', $debug_log_enabled);
      }
      elseif ($this->triplea_active_api_id === $this->triplea_btc2btc_api_id) {
         triplea_write_log('Enabling btc2btc live payment mode', $debug_log_enabled);
         $this->triplea_payment_mode = 'bitcoin-to-bitcoin';
         $this->update_option('triplea_payment_mode', $this->triplea_payment_mode);
         $this->triplea_sandbox_payment_mode = FALSE;
         $this->update_option('triplea_sandbox_payment_mode', FALSE);
      }
      elseif ($this->triplea_active_api_id === $this->triplea_btc2btc_sandbox_api_id) {
         triplea_write_log('Enabling btc2btc sandbox payment mode', $debug_log_enabled);
         $this->triplea_payment_mode = 'bitcoin-to-bitcoin';
         $this->update_option('triplea_payment_mode', $this->triplea_payment_mode);
         $this->triplea_sandbox_payment_mode = TRUE;
         $this->update_option('triplea_sandbox_payment_mode', TRUE);
      }
      elseif ($this->triplea_active_api_id === $this->triplea_btc2fiat_api_id) {
         triplea_write_log('Enabling btc2fiat live payment mode', $debug_log_enabled);
         $this->triplea_payment_mode = 'bitcoin-to-cash';
         $this->update_option('triplea_payment_mode', $this->triplea_payment_mode);
         $this->triplea_sandbox_payment_mode = FALSE;
         $this->update_option('triplea_sandbox_payment_mode', FALSE);
      }
      elseif ($this->triplea_active_api_id === $this->triplea_btc2fiat_sandbox_api_id) {
         triplea_write_log('Enabling btc2fiat sandbox payment mode', $debug_log_enabled);
         $this->triplea_payment_mode = 'bitcoin-to-cash';
         $this->update_option('triplea_payment_mode', $this->triplea_payment_mode);
         $this->triplea_sandbox_payment_mode = TRUE;
         $this->update_option('triplea_sandbox_payment_mode', TRUE);
      }
      else {
         $this->update_option('triplea_payment_mode', '');
         $this->update_option('triplea_sandbox_payment_mode', TRUE);
         triplea_write_log('ERROR! TripleA Payment mode not set. (Selecting an active wallet will update this.)', $debug_log_enabled);
      }
      
      /**
       *  Set the active account, based on which payment mode is chosen
       *  and whether sandbox is on/off.
       */
      if (isset($this->triplea_payment_mode)) {
         if ('bitcoin-to-bitcoin' === $this->triplea_payment_mode) {
            if ($this->triplea_sandbox_payment_mode) {
               // Set sandbox account as active.
               if (!isset($this->triplea_active_api_id) || empty($this->triplea_active_api_id) || $this->triplea_active_api_id !== $this->triplea_btc2btc_sandbox_api_id) {
                  $this->triplea_active_api_id = $this->triplea_btc2btc_sandbox_api_id;
                  $this->update_option('triplea_active_api_id', $this->triplea_btc2btc_sandbox_api_id);
                  triplea_write_log('Making sandbox bitcoin-to-bitcoin settlement account the active account. API ID = "' . $this->triplea_btc2btc_sandbox_api_id . '".', $debug_log_enabled);
               }
            }
            else {
               // Set live account as active.
               if (!isset($this->triplea_active_api_id) || empty($this->triplea_active_api_id) || $this->triplea_active_api_id !== $this->triplea_btc2btc_api_id) {
                  $this->triplea_active_api_id = $this->triplea_btc2btc_api_id;
                  $this->update_option('triplea_active_api_id', $this->triplea_btc2btc_api_id);
                  triplea_write_log('Making live bitcoin-to-bitcoin settlement account the active account. API ID = "' . $this->triplea_btc2btc_api_id . '".', $debug_log_enabled);
               }
            }
            
         }
         elseif ('bitcoin-to-cash' === $this->triplea_payment_mode) {
            if ($this->triplea_sandbox_payment_mode) {
               // Set sandbox account as active.
               if (!isset($this->triplea_active_api_id) || empty($this->triplea_active_api_id) || $this->triplea_active_api_id !== $this->triplea_btc2fiat_sandbox_api_id) {
                  $this->triplea_active_api_id = $this->triplea_btc2fiat_sandbox_api_id;
                  $this->update_option('triplea_active_api_id', $this->triplea_btc2fiat_sandbox_api_id);
                  triplea_write_log('Making sandbox bitcoin-to-local currency settlement account the active account. API ID = "' . $this->triplea_btc2fiat_sandbox_api_id . '".', $debug_log_enabled);
               }
            }
            else {
               // Set live account as active.
               if (!isset($this->triplea_active_api_id) || empty($this->triplea_active_api_id) || $this->triplea_active_api_id !== $this->triplea_btc2fiat_api_id) {
                  $this->triplea_active_api_id = $this->triplea_btc2fiat_api_id;
                  $this->update_option('triplea_active_api_id', $this->triplea_btc2fiat_api_id);
                  triplea_write_log('Making live bitcoin-to-local currency settlement account the active account. API ID = "' . $this->triplea_btc2fiat_api_id . '".', $debug_log_enabled);
               }
            }
            
         }
      }
      
      
      // TODO detect if v1.4.x or previous was installed, and if yes, redirect user to settings page after installation and prompt the user to re-enable the plugin
      
      $is_enabled = 'yes'; //$this->get_option('enabled');
      if (!isset($this->triplea_active_api_id) || empty($this->triplea_active_api_id)) {
         $is_enabled = 'no';
         triplea_write_log('Disabling plugin, not accepting bitcoin payments as there is no active API ID.', $debug_log_enabled);
      }
      $this->enabled = $is_enabled;
      $this->update_option('enabled', $is_enabled);
      
      $this->method_description = sprintf(
         __(
            '<img src="https://triple-a.io/wp-content/uploads/2020/08/TripleA_MainLogo_Transparent-e1596423970124.png" alt="Footer Logo" class="webpexpress-processed" style="max-width: 149px; vertical-align: text-bottom;display: inline-block;position: relative;top: 5px;margin-right: 15px;"> With <a href="https://triple-a.io">TripleA</a> you get to choose how your bitcoin payments are settled to you: <strong>in bitcoin</strong> or <strong>in your local currency</strong>.',
            'triplea-cryptocurrency-payment-gateway-for-woocommerce'
         )
      );
      
      if ('test' === $this->triplea_mode) {
         $this->description = $this->description . sprintf('<br>' . '<strong>' . __('TripleA TEST MODE enabled.', 'triplea-cryptocurrency-payment-gateway-for-woocommerce') . '</strong>');
         $this->description = trim($this->description);
      }
      
      // Save settings page options, defined in standard settings page file.
      if (is_admin()) {
         add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            [
               $this,
               'process_admin_options',
            ]
         );
      }
   
      // Save settings page options as defined in nested/injected HTML content.
      add_action('woocommerce_update_options_payment_gateways_' . $this->id, [
         $this,
         'save_plugin_options',
      ]);
      
      // We need custom JavaScript to run in the front-end (checkout page)
      add_action('wp_enqueue_scripts', [$this, 'payment_scripts']);
   
      // Refresh oauth tokens
      $this->refreshOauthTokens();
      
   }
   
   public function customize_thank_you_title($old_title, $order) {}
   
   public function customize_thank_you_text($old_text, $order) {}
   
   /**
    * @param array|object     $payment_data
    * @param \WC_Order|null $wc_order
    * @param bool      $placing_new_order
    *
    * @return array|void
    */
   public static function update_order_status($payment_data, $wc_order, $placing_new_order = false, $unix_timestamp = null, $hex_signature = null) {
      $triplea = new TripleA_Payment_Gateway();
      
      $debug_log_enabled = $triplea->get_option('debug_log_enabled') === 'yes';
   
      triplea_write_log( 'update_order_status():', $debug_log_enabled );
      
      $notes = [];
      $return_error = false;
      $return_payment_tier = '';
      $return_order_status = '';
      
      if (!isset($wc_order) || empty($wc_order)) {
         // No order provided. If the payment data contains an order_txid,
         // we can use it to find the matching order.
         if (isset($payment_data->webhook_data)
             && isset($payment_data->webhook_data->order_txid)
             && !empty($payment_data->webhook_data->order_txid)) {
   
            // Get the WooCommerce order ID with the matching client tx ID.
            $rest = new REST( $triplea->api );
            $order_id = $rest->triplea_get_orderid_from_txid( $payment_data->webhook_data->order_txid, $debug_log_enabled );
            if ( $order_id < 0 ) {
               triplea_write_log( 'update_order_status() : ERROR. No matching order found for tx id ' . $payment_data->webhook_data->order_txid . '.', $debug_log_enabled );
            }
            else {
               $wc_order = wc_get_order( $order_id );
               triplea_write_log( 'update_order_status() : Found matching order ' . $order_id . ' found for tx id ' . $payment_data->webhook_data->order_txid . '.', $debug_log_enabled );
            }
         }
      }
      // Else given an existing (newly placed) order
      
      
      if (!isset($wc_order) || empty($wc_order)) {
         triplea_write_log('process_payment() : ERROR! Missing WooCommerce order, cannot continue.', $debug_log_enabled);
         $return_error = true;
   
         if ($placing_new_order) {
            $return_values = [
               'result'       => 'failure',
               'payment_tier' => $return_payment_tier,
               'order_status' => $return_order_status,
               'error'        => $return_error,
            ];
            triplea_write_log('update_order_status() : Return values: ' . $return_values, $debug_log_enabled);
            return $return_values;
         } else return;
      }
      $order_id = $wc_order->get_id();
      $tx_id = get_post_meta($order_id, '_triplea_tx_id');
      
      
      if (isset($triplea->settings['triplea_woocommerce_order_states']) && isset($triplea->settings['triplea_woocommerce_order_states']['paid'])) {
         $order_status_paid      = $triplea->settings['triplea_woocommerce_order_states']['paid'];
         $order_status_confirmed = $triplea->settings['triplea_woocommerce_order_states']['confirmed'];
         //			$order_status_refunded  = $this->settings['triplea_woocommerce_order_states']['refunded'];
         $order_status_invalid = $triplea->settings['triplea_woocommerce_order_states']['invalid'];
      }
      else {
         // default values returned by get_status()
         $order_status_paid      = 'wc-on-hold'; // paid but still unconfirmed
         $order_status_confirmed = 'wc-processing';
         //			$order_status_refunded  = 'wc-refunded';
         $order_status_invalid = 'wc-failed';
      }
      
      if (isset($payment_data->error)) {
         triplea_write_log("update_order_status() : payment status check returned an ERROR : \n" . print_r($payment_data, TRUE), $debug_log_enabled);
         
         $return_order_status = $order_status_paid;
         $return_error = true;
   
         $notes[] = 'Could not verify the payment status, server returned an error. If the bitcoin payments debug log is enabled, the error will be in the log. Please <a href="mailto:support@triple-a.io">share that with us at support@triple-a.io</a>';
   
         if ($placing_new_order) {
            // We tried to verify the payment status (anything paid or not? how much?).
            // However something went wrong. That does not mean the user did not pay...
            // We save the order, mark it as ON HOLD.
            // but!!! we add a note to indicate to the merchant that there might be a problem with this order, not sure if a payment was made or not.
            $wc_order->update_status($order_status_paid); // on hold, might be paid (but not confirmed)
   
            $notes[] = 'There was a problem when connecting to the TripleA server. The user may or may not have paid.' . '<br>'
                       . 'If a payment was made, this order should automatically update within 10 minutes to 1 hour.';
   
            $notes[] = 'If the order does not get updated or if you have any question, please contact us at <a href="mailto:support@triple-a.io">support@triple-a.io</a> and share with us the order transaction id = \'' . $tx_id . '\'.';
         }
         
      }
      else {
         $bitcoin_address = $payment_data->crypto_address;
   
         $unconf_crypto_amount = floatval( $payment_data->unconfirmed_crypto_amt ) ? floatval( $payment_data->unconfirmed_crypto_amt ) : 0.0;
         $conf_crypto_amount = floatval( $payment_data->confirmed_crypto_amt ) ? floatval( $payment_data->confirmed_crypto_amt ) : 0.0;
         $crypto_amount_paid = $unconf_crypto_amount + $conf_crypto_amount;
   
         $unconf_order_amount = floatval( $payment_data->unconfirmed_order_amt ) ? floatval( $payment_data->unconfirmed_order_amt ) : 0.0;
         $conf_order_amount = floatval( $payment_data->confirmed_order_amt ) ? floatval( $payment_data->confirmed_order_amt ) : 0.0;
         $order_amount_paid = $unconf_order_amount + $conf_order_amount;
         
         if ($placing_new_order) {
            $notes[] = 'Amount due: <strong>BTC ' . number_format($payment_data->crypto_amount, 8) . '</strong>'.'<br>'
                       .'Value: '.$payment_data->order_currency.' '.$payment_data->order_amount.'<br>'
                       .' <br> '
                       .'To be paid to BTC address:' . '<br>'
                       . $bitcoin_address . "<br>"
                       . "<a href='https://www.blockchain.com/search?search=" . $bitcoin_address . "' target='_blank'>(View details on the blockchain)</a>";
         }
         
         // Depending on the results, update the order state.
         switch ($payment_data->payment_tier) {
            case 'none':
               // No payment received yet.
               // Order was placed by front-end but we don't know if there will be a payment or not.
               $wc_order->update_status($order_status_paid); // on hold, might be paid (but not confirmed)
   
               $return_payment_tier = $payment_data->payment_tier;
               $return_order_status = $order_status_paid;
               
               if ($placing_new_order) {
                  triplea_write_log('update_order_status() : No payment received (yet). Order was placed by front-end despite no payment having been detected.', $debug_log_enabled);
   
                  $notes[] = 'No payment detected yet for bitcoin address ' . $bitcoin_address . '.' . '<br>' . 'The user may have paid, payment form could have expired before the transaction was detected. (This can happen with some exchanges that delay transactions.)'
                             . '<br>' . 'It may also be that the user <strong>did not pay</strong> and just placed the order.';
                  $notes[] = 'If a payment was made, this order will be updated automatically. If you have any doubt, <a href="mailto:support@triple-a.io">feel free to contact us</a>.';
               }
               else {
                  triplea_write_log('update_order_status() : No payment received (yet).', $debug_log_enabled);
               }
               
               break;
            
            case 'hold':
               
               $wc_order->update_status($order_status_paid); // on hold, might be paid (but not confirmed)
   
               $return_payment_tier = $payment_data->payment_tier;
               $return_order_status = $order_status_paid;
            
               if ($placing_new_order) {
                  triplea_write_log('update_order_status() : Confirmed that a payment was made, order payment still waiting for validation.', $debug_log_enabled);
                  triplea_write_log('update_order_status() : Current payment status: ' . $payment_data->status, $debug_log_enabled);
   
                  $notes[] = 'Payment detected, awaiting validation.';
               }
               else {
                  triplea_write_log('update_order_status() : Order payment still waiting for validation.', $debug_log_enabled);
               }
               
               // TODO add a message specifying details about how much was paid (enough or not? how much paid or missing?)
               
               break;
            
            case 'short':
               
               $wc_order->update_status($order_status_invalid);
   
               $return_payment_tier = $payment_data->payment_tier;
               $return_order_status = $order_status_invalid;
               
               $notes[] = 'Paid: <strong>'.$payment_data->crypto_currency.'</strong>'
                          .' <strong>'. $crypto_amount_paid . '</strong>'.'<br>'
                          .'Value: '.$payment_data->order_currency.' '.$order_amount_paid.'<br>'
                          .'<br>'
                          .'Paid to bitcoin address:' . '<br>'
                          . $bitcoin_address . "<br>"
                          . "<a href='https://www.blockchain.com/search?search=" . $bitcoin_address . "' target='_blank'>(View details on the blockchain)</a>";
               
               $notes[] = '<strong>BTC amount paid is insufficient!</strong>'.'<br>'
                  .'Missing '.(number_format($payment_data->crypto_amount, 8) - $crypto_amount_paid).' '.$payment_data->crypto_currency;
               
               triplea_write_log('update_order_status() : Confirmed that a payment was made, for an insufficient amount.', $debug_log_enabled);
               
               // TODO come up with a process to help merchants handle this scenario (request extra payment or refund or ..?)
               
               break;
            
            case 'good':
               
               $wc_order->update_status($order_status_confirmed); // on hold, might be paid (but not confirmed)
   
               $return_payment_tier = $payment_data->payment_tier;
               $return_order_status = $order_status_confirmed;
   
               $notes[] = 'Paid: <strong>BTC ' . number_format($crypto_amount_paid, 8) . '</strong>'.'<br>'
                          .'Value: '.$payment_data->order_currency.' '.$order_amount_paid.'<br>'
                          .'<br>'
                          .'Paid to bitcoin address:' . '<br>'
                          . $bitcoin_address . "<br>"
                          . "<a href='https://www.blockchain.com/search?search=" . $bitcoin_address . "' target='_blank'>(View details on the blockchain)</a>";
               
               if ($crypto_amount_paid > $payment_data->crypto_amount) {
                  $notes[] = 'User paid too much.'.'<br>'
                     .'The user may contact you to ask for a refund.'.'<br>'
                     .'If you need assistance, <a href="mailto:support@triple-a.io">simply email us at support@triple-a.io</a>.';
               }
               else {
                  $notes[] = 'Correct amount paid.';
               }
               $notes[] = 'Order completed.';
               
               triplea_write_log('update_order_status() : Confirmed that a sufficient payment was made.', $debug_log_enabled);
               
               break;
            
            case 'invalid':
               
               $wc_order->update_status($order_status_invalid);
   
               $return_payment_tier = $payment_data->payment_tier;
               $return_order_status = $order_status_invalid;
               
               $notes[] = 'Payment failed or is invalid.' . '<br>';
               //. 'Payment might have expired due to a very low transaction fee paid by the user or a double-spend attempt might have occurred.';
            
               triplea_write_log('update_order_status() : Payment failed or invalid. Payment might have expired due to super low transaction fee or a double-spend attempt might have occurred.', $debug_log_enabled);
               
               break;
            
            default:
               triplea_write_log('update_order_status() : Unknown payment_tier received. Value:"' . $payment_data->payment_tier . '".', $debug_log_enabled);
   
               $return_payment_tier = $payment_data->payment_tier;
               $return_error = true;
         }
   
         if (0 === count(get_post_meta($order_id, '_triplea_order_status'))) {
            add_post_meta($order_id, '_triplea_order_status', $return_order_status);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_payment_tier'))) {
            add_post_meta($order_id, '_triplea_payment_tier', $return_payment_tier);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_payment_status'))) {
            add_post_meta($order_id, '_triplea_payment_status', $payment_data->status);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_order_amount'))) {
            add_post_meta($order_id, '_triplea_order_amount', $payment_data->order_amount);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_order_crypto_amount'))) {
            add_post_meta($order_id, '_triplea_order_crypto_amount', $payment_data->crypto_amount);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_amount_paid'))) {
            add_post_meta($order_id, '_triplea_amount_paid', $order_amount_paid);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_crypto_amount_paid'))) {
            add_post_meta($order_id, '_triplea_crypto_amount_paid', $crypto_amount_paid);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_crypto_currency'))) {
            add_post_meta($order_id, '_triplea_crypto_currency', $payment_data->crypto_currency);
         }
         if (0 === count(get_post_meta($order_id, '_triplea_order_currency'))) {
            add_post_meta($order_id, '_triplea_order_currency', $payment_data->order_currency);
         }
         
      }
   
      // Save the order notes, empty the cart, inform the Checkout page the order has been saved.
      foreach ($notes as $note) {
         $wc_order->add_order_note(__($note, 'triplea-cryptocurrency-payment-gateway-for-woocommerce'));
      }
      
      if ($placing_new_order) {
         $return_values = [
            'result'       => 'success',
            'payment_tier' => $return_payment_tier,
            'order_status' => $return_order_status,
            'error'        => $return_error,
         ];
         triplea_write_log('update_order_status() : Return values: ' . print_r($return_values, true), $debug_log_enabled);
         return $return_values;
      } else return;
   }
   
   /*
    * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
    */
   public function payment_scripts() {
      if ('no' === $this->enabled) {
         return;
      }
      
      // we need JavaScript to process payment form only on cart/checkout pages
      if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
         return;
      }
      
      wp_enqueue_script('triplea_payment_gateway_embedded_payment_form_js', plugins_url('../Frontend/js/triplea_payment_gateway_embedded_payment_form.js', __FILE__), ['jquery']);
   }
   
   
   /**
    * @see WC_Settings_API::init_form_fields()
    */
   public function init_form_fields() {
      $this->form_fields = include 'triplea-payment-gateway-settings-page.php';
      //wp_enqueue_media();
      $this->init_extra_form_fields();
   }
   
   public function init_extra_form_fields() {
      /**
       *  Fields below need to be specified like this because they're not added
       * in the usual way, rather they're part of an imported template.
       */
      $extra_fields = [
         // 'woocommerce_triplea_payment_gateway_triplea_dashboard_email_btc' => [],
      ];
      $post_data    = $this->get_post_data();
      
      foreach ($extra_fields as $key => $field) {
         try {
            if (!empty($this->get_field_value($key, $field, $post_data))) {
               $this->settings[$key] = $this->get_field_value($key, $field, $post_data);
            }
         }
         catch (Exception $e) {
            $this->add_error($e->getMessage());
         }
      }
      
      /**
       *  Below fields are used in this class and need to be set/saved/updated.
       */
      $private_fields = [
         'triplea_client_public_key' => [],
         'triplea_client_secret_key' => [],
      ];
      foreach ($private_fields as $key => $field) {
         try {
            if (!isset($this->settings[$key])) {
               $this->settings[$key] = $this->get_field_value($key, $field, '');
            }
         }
         catch (Exception $e) {
            $this->add_error($e->getMessage());
         }
      }
      
      // Update the settings saved as an option,
      return update_option($this->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings), 'yes');
   }
   
   /**
    *
    */
   public function save_plugin_options() {
      $triplea_statuses = [
         //'new'       => 'New Order',
         'paid'      => 'Paid (awaiting confirmation)',
         'confirmed' => 'Paid (confirmed)',
         // 'complete' => 'Complete',
         //'refunded'  => 'Refunded',
         'invalid'   => 'Invalid',
      ];
      
      $wcStatuses = wc_get_order_statuses();
      
      if (isset($_POST['triplea_woocommerce_order_states'])) {
         
         if (isset($this->settings['triplea_woocommerce_order_states']))
            $orderStates = $this->settings['triplea_woocommerce_order_states'];
         else
            $orderStates = [];
         
         foreach ($triplea_statuses as $triplea_state => $triplea_name) {
            if (FALSE === isset($_POST['triplea_woocommerce_order_states'][$triplea_state])) {
               continue;
            }
            
            $wcState = $_POST['triplea_woocommerce_order_states'][$triplea_state];
            
            if (TRUE === array_key_exists($wcState, $wcStatuses)) {
               $orderStates[$triplea_state] = $wcState;
            }
         }
         
         if (isset($_POST['triplea_bitcoin_logo_option'])) {
            $this->settings['triplea_bitcoin_logo_option'] = $_POST['triplea_bitcoin_logo_option'];
         }
         else {
            $this->settings['triplea_bitcoin_logo_option'] = 'large-logo';
         }
         
         $this->settings['triplea_woocommerce_order_states'] = $orderStates;
      }
      
      if (isset($_POST['triplea_bitcoin_text_custom_value'])) {
         $this->settings['triplea_bitcoin_text_custom_value'] = $_POST['triplea_bitcoin_text_custom_value'];
      }
      
      if (isset($_POST['triplea_bitcoin_text_option'])) {
         $this->settings['triplea_bitcoin_text_option'] = $_POST['triplea_bitcoin_text_option'];
      }
      else {
         $this->settings['triplea_bitcoin_text_option'] = 'default-text';
      }
      
      if (isset($_POST['triplea_bitcoin_logo_option'])) {
         $this->settings['triplea_bitcoin_logo_option'] = $_POST['triplea_bitcoin_logo_option'];
      }
      else {
         $this->settings['triplea_bitcoin_logo_option'] = 'large-logo';
      }
      
      if (isset($_POST['triplea_bitcoin_descriptiontext_option'])) {
         $this->settings['triplea_bitcoin_descriptiontext_option'] = $_POST['triplea_bitcoin_descriptiontext_option'];
      }
      else {
         $this->settings['triplea_bitcoin_descriptiontext_option'] = 'desc-default';
      }
      
      if (isset($_POST['triplea_bitcoin_descriptiontext_value'])) {
         $this->settings['triplea_bitcoin_descriptiontext_value'] = $_POST['triplea_bitcoin_descriptiontext_value'];
      }
      
      // Update the settings saved as an option,
      update_option($this->get_option_key(), apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings), 'yes');
   }
   
   public function payment_fields() {
      echo $this->get_description('');
      echo "<br><br>";
      echo $this->display_embedded_payment_form_button('');
      
      $cart_totals_hash = (!empty(WC()->cart->get_cart_contents_total()) ? WC()->cart->get_cart_contents_total() : '2').'_' .(!empty(WC()->cart->get_cart_discount_total()) ? WC()->cart->get_cart_discount_total() : '3').'_'. (!empty(WC()->cart->get_cart_shipping_total()) ? WC()->cart->get_cart_shipping_total() : '4');
      
      echo "<!-- anti-checkout.js-fragment-cache '" . md5($cart_totals_hash) . "' -->";
   }
   
   /**
    *  Check if endpoint token has been generated.
    *  Endpoint token is part of the web hook URL provided to the TripleA API.
    *  Incoming requests without a correct token are filtered out (spam filter).
    *
    * @param $debug_log_enabled
    */
   protected function triplea_set_api_endpoint_token($debug_log_enabled) {
      if (empty(get_option('triplea_api_endpoint_token'))) {
         if (function_exists('openssl_random_pseudo_bytes')) {
            $api_endpoint_token = md5(bin2hex(openssl_random_pseudo_bytes(16)) . (uniqid(rand(), TRUE)));
         }
         else {
            $api_endpoint_token = md5((uniqid(rand(), TRUE)) . (uniqid(rand(), TRUE)));
         }
         add_option('triplea_api_endpoint_token', $api_endpoint_token);
         triplea_write_log('Setting endpoint token: '.get_option('triplea_api_endpoint_token'), $debug_log_enabled);
      }
      else {
         //triplea_write_log('EXISTING ENDPOINT TOKEN: '.get_option('triplea_api_endpoint_token'), $debug_log_enabled);
      }
   }
   
   /**
    * Handle AJAX request to start checkout flow, first triggering form
    * validation if necessary.
    *
    * @since 1.6.0
    */
   public static function wc_ajax_start_checkout() {
      if (!wp_verify_nonce($_GET['_wpnonce'], '_wc_triplea_start_checkout_nonce')) {
         wp_die(__('Bad attempt', 'triplea-cryptocurrency-payment-gateway-for-woocommerce'));
      }
      
      add_action('woocommerce_after_checkout_validation', [
         self::class,
         'triplea_checkout_check',
      ], 10, 2);
      WC()->checkout->process_checkout();
   }
   
   /**
    * Report validation errors if any, or else save form data in session and
    * proceed with checkout flow.
    *
    * @param      $data
    * @param null $errors
    *
    * @since 1.5.0
    */
   public static function triplea_checkout_check($data, $errors = NULL) {
      if (is_null($errors)) {
         // Compatibility with WC <3.0: get notices and clear them so they don't re-appear.
         $error_messages = wc_get_notices('error');
         wc_clear_notices();
      }
      else {
         $error_messages = $errors->get_error_messages();
      }
      
      if (empty($error_messages)) {
         //triplea_write_log('triplea_checkout_check() success', TRUE);
         wp_send_json_success(
            [
               'status'   => 'ok',
            ]
         );
      }
      else {
         //triplea_write_log('triplea_checkout_check() failed', TRUE);
         wp_send_json_error(
            [
               'messages' => $error_messages,
               'status'   => 'notok',
            ]
         );
      }
      exit;
   }
   
   /**
    * Report validation errors if any, or else save form data in session and
    * proceed with checkout flow.
    *
    * @param      $data
    * @param null $errors
    *
    * @since 1.6.4
    */
   public static function start_checkout_check($data, $errors = NULL) {
      if (is_null($errors)) {
         // Compatibility with WC <3.0: get notices and clear them so they don't re-appear.
         $error_messages = wc_get_notices('error');
         wc_clear_notices();
      }
      else {
         $error_messages = $errors->get_error_messages();
      }
      
      if (empty($error_messages)) {
         $customer_data = self::get_customer_data($_POST);
         
         wp_send_json_success(
            [
               // 'token' => ...
               // 'order_payload' => ...
               'status'        => 'ok',
               'customer_data' => $customer_data,
            ]
         );
      }
      else {
         wp_send_json_error(
            [
               'messages' => $error_messages,
               'status'   => 'notok',
            ]
         );
      }
      exit;
   }
   
   protected static function get_customer_data($post_data) {
      $customer = [];
      
      $billing_first_name = empty($post_data['billing_first_name']) ? '' : wc_clean($post_data['billing_first_name']);
      $billing_last_name  = empty($post_data['billing_last_name']) ? '' : wc_clean($post_data['billing_last_name']);
      $billing_country    = empty($post_data['billing_country']) ? '' : wc_clean($post_data['billing_country']);
      $billing_address_1  = empty($post_data['billing_address_1']) ? '' : wc_clean($post_data['billing_address_1']);
      $billing_address_2  = empty($post_data['billing_address_2']) ? '' : wc_clean($post_data['billing_address_2']);
      $billing_city       = empty($post_data['billing_city']) ? '' : wc_clean($post_data['billing_city']);
      $billing_state      = empty($post_data['billing_state']) ? '' : wc_clean($post_data['billing_state']);
      $billing_postcode   = empty($post_data['billing_postcode']) ? '' : wc_clean($post_data['billing_postcode']);
      $billing_phone      = empty($post_data['billing_phone']) ? '' : wc_clean($post_data['billing_phone']);
      $billing_email      = empty($post_data['billing_email']) ? '' : wc_clean($post_data['billing_email']);
      
      if (isset($post_data['ship_to_different_address'])) {
         $shipping_first_name = empty($post_data['shipping_first_name']) ? '' : wc_clean($post_data['shipping_first_name']);
         $shipping_last_name  = empty($post_data['shipping_last_name']) ? '' : wc_clean($post_data['shipping_last_name']);
         $shipping_country    = empty($post_data['shipping_country']) ? '' : wc_clean($post_data['shipping_country']);
         $shipping_address_1  = empty($post_data['shipping_address_1']) ? '' : wc_clean($post_data['shipping_address_1']);
         $shipping_address_2  = empty($post_data['shipping_address_2']) ? '' : wc_clean($post_data['shipping_address_2']);
         $shipping_city       = empty($post_data['shipping_city']) ? '' : wc_clean($post_data['shipping_city']);
         $shipping_state      = empty($post_data['shipping_state']) ? '' : wc_clean($post_data['shipping_state']);
         $shipping_postcode   = empty($post_data['shipping_postcode']) ? '' : wc_clean($post_data['shipping_postcode']);
      }
      else {
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
   
   public function triplea_cryptocurrency_payment_gateway_for_woocommerce_notice_payments_enabled() {
      echo '<div class="updated notice is-dismissable"><p>' . __('You are now accepting bitcoin payments.', 'triplea-cryptocurrency-payment-gateway-for-woocommerce') . '</p></div>';
   }
   
   public function triplea_cryptocurrency_payment_gateway_for_woocommerce_notice_payments_disabled() {
      echo '<div class="updated notice is-dismissable"><p>' . __('Bitcoin Payments disabled.', 'triplea-cryptocurrency-payment-gateway-for-woocommerce') . '</p></div>';
   }
   
   public function triplea_cryptocurrency_payment_gateway_for_woocommerce_notice_payments_disabled_missing_apiid() {
      echo '<div class="error notice is-dismissable"><p>' . __('Bitcoin payments disabled, no active wallets.', 'triplea-cryptocurrency-payment-gateway-for-woocommerce') . '</p></div>';
   }
   
   /**
    * Generate a strong randomly generated token,
    * used to identify a user's cart order before and after the order has been
    * placed.
    *
    * @return string
    */
   protected function generate_order_txid() {
      if (function_exists('openssl_random_pseudo_bytes')) {
         $data_tx_id_token = md5(bin2hex(openssl_random_pseudo_bytes(16)) . (uniqid(rand(), TRUE)));
      }
      else {
         $data_tx_id_token = md5((uniqid(rand(), TRUE)) . (uniqid(rand(), TRUE)));
      }
      return $data_tx_id_token;
   }
   
   protected function prepare_encrypted_order_payload($client_txid) {
      /**
       *   Order hasn't been created yet.
       *   We can generate a payload and save it in the session of the current user or visitor.
       */
      
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      
      $user_id = '';
      $api_id  = $this->triplea_active_api_id; // = $this->get_option('triplea_active_api_id');
      
      if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
         $order_id = get_query_var('order-pay');
         $order    = wc_get_order($order_id);
         
         $amount   = esc_attr(((WC()->version < '2.7.0') ? $order->order_total : $order->get_total()));
         $currency = esc_attr(((WC()->version < '2.7.0') ? $order->order_currency : $order->get_currency()));
      }
      else {
         $amount   = esc_attr(WC()->cart->total);
         $currency = esc_attr(strtoupper(get_woocommerce_currency()));
      }
      
      $payload_cleartext = [
         'client_txid'    => $client_txid,
         'user_id'        => $user_id,
         'order_amount'   => $amount,
         'api_id'         => $api_id,
         'local_currency' => $currency,
      ];
      
      $payload_jsontext = json_encode($payload_cleartext);
      
      $client_secret_key = $this->triplea_client_secret_key;
      if (!isset($client_secret_key) || empty($client_secret_key)) {
         triplea_write_log('ERROR. No client keypair found', $debug_log_enabled);
         return '[missing client keypair]';
      }
      // triplea_write_log('local secret key: ' . $client_secret_key, $debug_log_enabled);
      
      if ($this->get_option('triplea_payment_mode') === 'bitcoin-to-cash') {
         $server_public_key = $this->get_option('triplea_server_public_enc_key_conversion');
      }
      elseif ($this->get_option('triplea_payment_mode') === 'bitcoin-to-bitcoin') {
         $server_public_key = $this->get_option('triplea_server_public_enc_key_btc');
      }
      else {
         triplea_write_log('ERROR. prepare_encrypted_order_payload(): Incorrect or missing payment mode = ' . print_r($this->get_option('triplea_payment_mode'), TRUE), $debug_log_enabled);
      }
      
      if (empty($server_public_key)) {
         $fallback          = TRUE;
         $server_public_key = 'A4cxSkcL/QLPaEE5AKFevgGgSLe+/RtAov7iDf0e1Rw=';
      }
      else {
         $fallback = FALSE;
      }
      
      $client_to_server_keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(
         base64_decode($client_secret_key),
         base64_decode($server_public_key)
      );
      
      $message_nonce     = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
      $payload_encrypted = sodium_crypto_box(
         $payload_jsontext,
         $message_nonce,
         $client_to_server_keypair
      );
      
      if ($fallback) {
         return base64_encode($payload_encrypted) . ':' . base64_encode($message_nonce) . ':' . 'fallback';
      }
      return base64_encode($payload_encrypted) . ':' . base64_encode($message_nonce);
   }
   
   /**
    * @return string
    * @throws SodiumException
    */
   protected function prepare_encrypted_public_key_shared() {
      
      $debug_log_enabled = 'yes' === $this->get_option('debug_log_enabled') ? TRUE : FALSE;
      
      $client_public_key = $this->triplea_client_public_key;
      $client_secret_key = $this->triplea_client_secret_key;
      
      if (!isset($client_public_key) || empty($client_public_key)
          || !isset($client_secret_key) || empty($client_secret_key)) {
         triplea_write_log('Prepare_encrypted_public_key_shared(): No keypair found', $debug_log_enabled);
         return '[missing client keypair]';
      }
      
      if ($this->get_option('triplea_payment_mode') === 'bitcoin-to-cash') {
         $server_public_key = $this->get_option('triplea_server_public_enc_key_conversion');
      }
      elseif ($this->get_option('triplea_payment_mode') === 'bitcoin-to-bitcoin') {
         $server_public_key = $this->get_option('triplea_server_public_enc_key_btc');
      }
      else {
         triplea_write_log('ERROR. prepare_encrypted_public_key_shared(): Incorrect or missing payment mode = ' . print_r($this->get_option('triplea_payment_mode'), TRUE), $debug_log_enabled);
      }
      
      if (empty($server_public_key)) {
         $fallback          = TRUE;
         $server_public_key = 'A4cxSkcL/QLPaEE5AKFevgGgSLe+/RtAov7iDf0e1Rw=';
      }
      else {
         $fallback = FALSE;
      }
      
      if (!function_exists('sodium_crypto_box_keypair_from_secretkey_and_publickey')) {
         triplea_write_log('ERROR! Missing sodium_crypto_ functions', $debug_log_enabled);
         return '[missing crypto functions]';
      }
      
      $message = $client_public_key; // We're providing the public key of the client (= us here)
      
      $message_nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
      $ciphertext    = sodium_crypto_secretbox(
         base64_decode($message),
         $message_nonce,
         base64_decode($server_public_key)
      );
      if ($fallback) {
         $encrypted_public_key_shared = base64_encode($ciphertext) . ':' . base64_encode($message_nonce) . ':' . 'fallback';
      }
      else {
         $encrypted_public_key_shared = base64_encode($ciphertext) . ':' . base64_encode($message_nonce);
      }
      
      return $encrypted_public_key_shared;
   }
   
   public function get_title() {
      if ('custom-text' === $this->get_option('triplea_bitcoin_text_option')) {
         $title_text = stripcslashes($this->get_option('triplea_bitcoin_text_custom_value'));
         $title      = __($title_text, 'triplea-cryptocurrency-payment-gateway-for-woocommerce');
         
      }
      else {
         $title = __('Bitcoin', 'triplea-cryptocurrency-payment-gateway-for-woocommerce');
      }
      
      return apply_filters('woocommerce_gateway_title', $title, $this->id);
   }
   
   public function get_description() {
      if ('desc-default' === $this->get_option('triplea_bitcoin_descriptiontext_option') || empty($this->get_option('triplea_bitcoin_descriptiontext_option'))) {
         $description = __('Secure and easy payment with Bitcoin', 'triplea-cryptocurrency-payment-gateway-for-woocommerce');
      }
      elseif ('desc-custom' === $this->get_option('triplea_bitcoin_descriptiontext_option')
              && !empty($this->get_option('triplea_bitcoin_descriptiontext_value'))) {
         $title_text  = stripcslashes($this->get_option('triplea_bitcoin_descriptiontext_value'));
         $description = __($title_text, 'triplea-cryptocurrency-payment-gateway-for-woocommerce');
         
      }
      else {
         $description = __('Secure and easy payment with Bitcoin', 'triplea-cryptocurrency-payment-gateway-for-woocommerce');
      }
      
      return apply_filters('woocommerce_gateway_description', $description, $this->id);
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
      
      $logo = $this->get_option('triplea_bitcoin_logo_option');
      
      switch ($logo) {
         case NULL:
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
      
      $icon_url = TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_URL_PATH . 'assets/img/';
      if (is_ssl()) {
         $icon_url = WC_HTTPS::force_https_url($icon_url);
      }
      $icon = '<img src="' . $icon_url . $iconfile . '" alt="Bitcoin logo" ' . $style . ' />';
      
      return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
   }
   
   /**
    * @return bool
    */
   public function is_available() {
      
      if ('yes' === $this->enabled) {
         if (!$this->triplea_mode && is_checkout()) {
            return FALSE;
         }
         if ($this->triplea_mode !== 'test' || is_admin() || current_user_can('editor') || current_user_can('administrator')) {
            return TRUE;
         }
      }
      return FALSE;
   }

   
   /**
    * Returns true or false depending on how validation of input fields went.
    *
    * @return bool
    */
   function validate_fields() {
      return TRUE;
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
   public function process_payment($order_id) {
      global $wp_version; // or use //include( ABSPATH . WPINC . '/version.php' );
      
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes' ? TRUE : FALSE;
      
      triplea_write_log('process_payment() : Order ' . $order_id . ' placed. Updating payment information.', $debug_log_enabled);
      
      $wc_order = wc_get_order($order_id);
      if (empty($wc_order)) {
         triplea_write_log('process_payment() : ERROR! Empty woocommerce order. Order was not placed.', $debug_log_enabled);
         
         return [
            'reload'   => FALSE,
            'refresh'  => FALSE,
            'result'   => 'failure',
            'messages' => 'Empty woocommerce order. Order was not placed.',
         ];
      }
      
      if (isset($this->settings['triplea_woocommerce_order_states']) && isset($this->settings['triplea_woocommerce_order_states']['paid'])) {
         $order_status_paid      = $this->settings['triplea_woocommerce_order_states']['paid'];
         $order_status_confirmed = $this->settings['triplea_woocommerce_order_states']['confirmed'];
         //			$order_status_refunded  = $this->settings['triplea_woocommerce_order_states']['refunded'];
         $order_status_invalid = $this->settings['triplea_woocommerce_order_states']['invalid'];
      }
      else {
         // default values returned by get_status()
         $order_status_paid      = 'wc-on-hold'; // paid but still unconfirmed
         $order_status_confirmed = 'wc-processing';
         //			$order_status_refunded  = 'wc-refunded';
         $order_status_invalid = 'wc-failed';
      }
      
      /*
       *  We set a transaction id token, securely randomly generated.
       *  This helps when receiving payment update notifications from the API,
       *  to match the notification with the related order.
       *  (No order ID is available for matching until after payment and order creation, which explains the need for this.)
       */
      $tx_id = WC()->session->get('generate_order_txid'); // $_POST['triplea_order_txid']; // dont trust frontend !
      if (empty($tx_id)) {
         return [
            'reload'   => FALSE,
            'refresh'  => FALSE,
            'result'   => 'failure',
            'messages' => 'Session is missing order tx id.',
         ];
      }
      if (0 === count(get_post_meta($order_id, '_triplea_tx_id'))) {
         add_post_meta($order_id, '_triplea_tx_id', $tx_id);
         triplea_write_log('process_payment() : Adding order_txid to new order metadata', $debug_log_enabled);
      }
      else {
         update_post_meta($order_id, '_triplea_tx_id', $tx_id);
         triplea_write_log('process_payment() : Updating order_txid in new order metadata', $debug_log_enabled);
      }
      
      // Get payment reference from session (don't trust front-end form data).
      $payment_reference = WC()->session->get('triplea_payment_reference');
      if (empty($payment_reference)) {
         return [
            'reload'   => FALSE,
            'refresh'  => FALSE,
            'result'   => 'failure',
            'messages' => 'Session is missing payment reference.',
         ];
      }
      if (0 === count(get_post_meta($order_id, '_triplea_payment_reference'))) {
         add_post_meta($order_id, '_triplea_payment_reference', $payment_reference);
         triplea_write_log('process_payment() : Adding payment_reference to new order metadata', $debug_log_enabled);
      }
      else {
         update_post_meta($order_id, '_triplea_payment_reference', $payment_reference);
         triplea_write_log('process_payment() : Updating payment_reference in new order metadata', $debug_log_enabled);
      }
      
      // Get access token from session (don't trust front-end form data).
      $access_token = WC()->session->get('triplea_payment_access_token');
      if (empty($access_token)) {
         return [
            'reload'   => FALSE,
            'refresh'  => FALSE,
            'result'   => 'failure',
            'messages' => 'Session is missing access token.',
         ];
      }
      if (0 === count(get_post_meta($order_id, '_triplea_access_token'))) {
         add_post_meta($order_id, '_triplea_access_token', $access_token);
         triplea_write_log('process_payment() : Adding access_token to new order metadata', $debug_log_enabled);
      }
      else {
         update_post_meta($order_id, '_triplea_access_token', $access_token);
         triplea_write_log('process_payment() : Updating access_token in new order metadata', $debug_log_enabled);
      }
      
      // Get notify_secret from session (don't trust front-end form data).
      $notify_secret = WC()->session->get('triplea_payment_notify_secret');
      if (empty($notify_secret)) {
         return [
            'reload'   => FALSE,
            'refresh'  => FALSE,
            'result'   => 'failure',
            'messages' => 'Session is missing a notify secret.',
         ];
      }
      if (0 === count(get_post_meta($order_id, '_triplea_notify_secret'))) {
         add_post_meta($order_id, '_triplea_notify_secret', $notify_secret);
         triplea_write_log('process_payment() : Adding notify_secret to new order metadata', $debug_log_enabled);
      }
      else {
         update_post_meta($order_id, '_triplea_notify_secret', $notify_secret);
         triplea_write_log('process_payment() : Updating notify_secret in new order metadata', $debug_log_enabled);
      }
      
      // Get crypto address from session (don't trust front-end form data).
      $crypto_address = WC()->session->get('triplea_payment_crypto_address');
      if (empty($crypto_address)) {
         return [
            'reload'   => FALSE,
            'refresh'  => FALSE,
            'result'   => 'failure',
            'messages' => 'Session is missing a crypto address.',
         ];
      }
      if (0 === count(get_post_meta($order_id, '_triplea_crypto_address'))) {
         add_post_meta($order_id, '_triplea_crypto_address', $crypto_address);
         triplea_write_log('process_payment() : Adding crypto_address to new order metadata', $debug_log_enabled);
      }
      else {
         update_post_meta($order_id, '_triplea_crypto_address', $crypto_address);
         triplea_write_log('process_payment() : Updating crypto_address in new order metadata', $debug_log_enabled);
      }
      
      // Could repeat the above, if needed, for order currency, order amount, exchange rate, and more.
      
      
      // Call TripleA API to get payment details (paid or not? enough or too little?).
      $payment_data = $this->get_payment_form_status_update($payment_reference);
      triplea_write_log("process_payment() : payment status check, received data : \n" . print_r($payment_data, TRUE), $debug_log_enabled);
   
      $status_info = null;
      if ( !isset($payment_data->error) ) {
         $status_info = self::update_order_status($payment_data, $wc_order, TRUE);
      }
      
      if (isset($payment_data->error) || $status_info['error']) {
         wc_add_notice(__('Payment Failed', 'triplea-cryptocurrency-payment-gateway-for-woocommerce'), $notice_type = 'error');
   
         return [
            'reload'   => FALSE,
            'refresh'  => FALSE,
            'result'   => 'failure',
            'messages' => 'Exception occured. Message: failed API status payment status check',
         ];
      }
   
      WC()->cart->empty_cart();
      
      // Clearing session data for payment, so that a new payment could be made.
      WC()->session->set('triplea_payment_hosted_url', null);
      WC()->session->set('triplea_payment_reference', null);
      WC()->session->set('triplea_payment_access_token', null);
      WC()->session->set('triplea_payment_access_token_expiry', null);
      WC()->session->set('triplea_payment_notify_secret', null);
      WC()->session->set('triplea_payment_crypto_currency', null);
      WC()->session->set('triplea_payment_crypto_address', null);
      WC()->session->set('triplea_payment_crypto_amount', null);
      WC()->session->set('triplea_payment_order_currency', null);
      WC()->session->set('triplea_payment_order_amount', null);
      WC()->session->set('triplea_payment_exchange_rate', null);
      WC()->session->set('triplea_cart_total', null);
      WC()->session->set('generate_order_txid', null);
   
      
      return [
         'result'   => 'success',
         'redirect' => $this->get_return_url($wc_order),
      ];
   }
   
   /**
    * @param $balance_payload_full
    * @param $wc_order
    *
    * @return array
    */
   protected
   function decrypt_payload(
      $balance_payload_full, $wc_order
   ) {
      
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes' ? TRUE : FALSE;
      
      // Init needed values
      $payment_mode                     = $this->triplea_payment_mode;
      $server_public_enc_key_btc        = $this->triplea_server_public_enc_key_btc;
      $server_public_enc_key_conversion = $this->triplea_server_public_enc_key_conversion;
      $client_secret_key                = $this->triplea_client_secret_key;
      
      if ($payment_mode === 'bitcoin-to-bitcoin') {
         $triplea_public_enc_key = $server_public_enc_key_btc;
      }
      elseif ($payment_mode === 'bitcoin-to-cash') {
         $triplea_public_enc_key = $server_public_enc_key_conversion;
      }
      else {
         triplea_write_log('decrypt_payload() : ERROR! Unknown or missing $payment_mode = ' . $payment_mode, $debug_log_enabled);
      }
      
      if (empty($triplea_public_enc_key)) {
         $triplea_public_enc_key = 'A4cxSkcL/QLPaEE5AKFevgGgSLe+/RtAov7iDf0e1Rw=';
      }
      
      $client_secret_enc_key = $client_secret_key;
      
      $payload_status_data = $this->api->triplea_cryptocurrency_payment_gateway_for_woocommerce_decrypt_payload($balance_payload_full, $client_secret_enc_key, $triplea_public_enc_key);
      
      return $payload_status_data;
   }
   
   /**
    * Helper function to get client connection details.
    *
    * @return array
    */
   public
   function get_clients_details() {
      return [
         'IP'      => $_SERVER['REMOTE_ADDR'],
         'Agent'   => $_SERVER['HTTP_USER_AGENT'],
         'Referer' => $_SERVER['HTTP_REFERER'],
      ];
   }
   
   /**
    * Process refund.
    *
    * If the gateway declares 'refunds' support, this will allow it to refund.
    * a passed in amount.
    *
    * @param int    $order_id Order ID.
    * @param float  $amount   Refund amount.
    * @param string $reason   Refund reason.
    *
    * @return boolean True or false based on success, or a WP_Error object.
    */
   public
   function process_refund(
      $order_id, $amount = NULL, $reason = ''
   ) {
      return FALSE;
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
public function generate_triplea_pubkeyid_script_html($key, $data) {
   
   $field_key = $this->get_field_key($key);
   
   $TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_API_ENDPOINT = site_url() . '?rest_route=/triplea/v1/tx_update/' . get_option('triplea_api_endpoint_token');
   
   ob_start();
   ?>
   </table>

   <table class="form-table">
      <?php
      
      return ob_get_clean();
      }
      
      /**
       * Generate normal text (not in a table, rather a single line of text,
       * paragraph style).
       *
       * @param string $key  Field key.
       * @param array  $data Field data.
       *
       * @return string
       * @since  1.0.0
       */
      public function generate_paragraph_html($key, $data) {
      $field_key = $this->get_field_key($key);
      $defaults  = [
         'title' => '',
         'class' => '',
      ];
      $data      = wp_parse_args($data, $defaults);
      ob_start();
      ?>
   </table>
   <p class="wc-settings-sub-title <?php echo esc_attr($data['class']); ?>"
      id="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></p>
   <table class="form-table">
      <?php
      return ob_get_clean();
      }
      
      public function generate_anchor_html($key, $data) {
      $defaults = [
         'title' => '',
         'class' => '',
      ];
      $data     = wp_parse_args($data, $defaults);
      ob_start();
      ?>
   </table>
   <a id="<?php echo wp_kses_post($data['title']); ?>"></a>
   <a name="<?php echo wp_kses_post($data['title']); ?>"></a>
   <table class="form-table">
      <?php
      return ob_get_clean();
      }
      
      public function generate_custom_html($key, $data) {
      $defaults = [
         'markup' => '',
      ];
      $data     = wp_parse_args($data, $defaults);
      ob_start();
      ?>
   </table>
   <?php echo $data['markup']; ?>
   <table class="form-table">
      <?php
      return ob_get_clean();
      }
      
      public function generate_text_ifnotempty_html($key, $data) {
         if (empty($this->get_option($key))) {
            return '';
         }
         else {
            return $this->generate_text_html($key, $data);
         }
      }
      
      /**
       * Generate hidden Input HTML.
       *
       * @param string $key  Field key.
       * @param array  $data Field data.
       *
       * @return string
       * @since  1.0.0
       */
      public function generate_hidden_html($key, $data) {
         $field_key = $this->get_field_key($key);
         $defaults  = [
            'type' => 'hidden',
         ];
         $data      = wp_parse_args($data, $defaults);
         
         ob_start();
         ?>
         <input type="<?php echo esc_attr($data['type']); ?>"
                name="<?php echo esc_attr($field_key); ?>"
                id="<?php echo esc_attr($field_key); ?>"
                value="<?php echo esc_attr($this->get_option($key)); ?>"
         />
         <?php
         return ob_get_clean();
      }
      
      public function generate_table_markup_html($key, $data) {
         $field_key = $this->get_field_key($key);
         $defaults  = [
            'title'       => '',
            'class'       => '',
            'description' => '',
            'markup'      => '',
         ];
         $data      = wp_parse_args($data, $defaults);
         ob_start();
         ?>
         <tr valign="top" class="<?php echo esc_attr($data['class']); ?>">
            <th scope="row" class="titledesc">
               <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
            </th>
            <td class="forminp">
               <?php echo $data['markup']; ?>
            </td>
         </tr>
         <?php
         return ob_get_clean();
      }
      
      public function generate_order_states_html($data) {
      $defaults = [
         'markup' => '',
      ];
      $data     = wp_parse_args($data, $defaults);
      
      ob_start();
      ?>
   </table>
   <?php echo $data['markup']; ?>
   <table class="form-table">
   <?php
   return ob_get_clean();
}
   
   /**
    * TripleA APIv1 new code
    */
   
   public
   function display_embedded_payment_form_button(
      $button_html
   ) {
      global $wp;
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      triplea_write_log("display_embedded_payment_form_button() starting", $debug_log_enabled);
      
      $nonce_action               = '_wc_triplea_get_payment_form_data';
      $paymentform_ajax_url       = WC_AJAX::get_endpoint('wc_triplea_get_payment_form_data');
      $paymentform_ajax_nonce_url = wp_nonce_url($paymentform_ajax_url, $nonce_action);
      $output_paymentform_url     = '<div id="triplea-payment-gateway-payment-form-request-ajax-url" data-value="' . $paymentform_ajax_nonce_url . '" style="display:none;"></div>';
   
      $nonce_action             = '_wc_triplea_start_checkout_nonce';
      $start_checkout_url       = WC_AJAX::get_endpoint('wc_triplea_start_checkout');
      $start_checkout_nonce_url = wp_nonce_url($start_checkout_url, $nonce_action);
      $output_startcheckoutcheck = "<div id='triplea-payment-gateway-start-checkout-check-url' style='display:none;' data-value='$start_checkout_nonce_url'></div>";
      
      $order_button_text = 'Pay with bitcoin';
      $output            = '<button type="button"
      style="margin: 0 auto; display: block;"
      class="button alt"
      onclick="triplea_validateCheckout()"
      name="triplea_embedded_payment_form_btn"
      id="triplea_embedded_payment_form_btn"
      value="' . esc_attr($order_button_text) . '"
      data-value="' . esc_attr($order_button_text) . '">' . esc_html($order_button_text) . '</button>';
   
      $output            .= '<div style="margin: 0 auto; display: none; text-align: center;"
      id="triplea_embedded_payment_form_loading_txt"
      >loading...</div>';
      
      // TODO Remove this debug code
      $output .= '<!--small><pre>' . $paymentform_ajax_nonce_url . '</pre></small-->';
      
      return $button_html . $output . $output_paymentform_url . $output_startcheckoutcheck;
   }
   
   /**
    * Handle AJAX request to start checkout flow, first triggering form
    * validation if necessary.
    *
    * @since 1.6.0
    */
   public
   static function triplea_ajax_get_payment_form_data() {
   
      if (!wp_verify_nonce($_REQUEST['_wpnonce'], '_wc_triplea_get_payment_form_data')) {
         wp_die(__('Bad attempt', 'triplea-cryptocurrency-payment-gateway-for-woocommerce'));
      }
      
      $user_firstname = wc_get_var($_REQUEST['billing_first_name'], null);
      $user_lastname = wc_get_var($_REQUEST['billing_last_name'], null);
      $user_email = wc_get_var($_REQUEST['billing_email'], null);
      $user_phone = wc_get_var($_REQUEST['billing_phone'], null);
      
      $user_address_company = wc_get_var($_REQUEST['billing_company'], null);
      $user_address_address1 = wc_get_var($_REQUEST['billing_address_1'], null);
      $user_address_address2 = wc_get_var($_REQUEST['billing_address_2'], null);
      $user_address_city = wc_get_var($_REQUEST['billing_city'], null);
      $user_address_state = wc_get_var($_REQUEST['billing_state'], null);
      $user_address_postcode = wc_get_var($_REQUEST['billing_postcode'], null);
      $user_address_country = wc_get_var($_REQUEST['billing_country'], null);
      $user_address_temp = join(', ', array($user_address_company, $user_address_address1, $user_address_address2, $user_address_city, $user_address_state, $user_address_country, $user_address_postcode));
      $user_address = ltrim(rtrim($user_address_temp, ', '), ', ');
      
      $triplea           = new TripleA_Payment_Gateway();
      $debug_log_enabled = $triplea->get_option('debug_log_enabled') === 'yes';
      
      $payment_reference        = $access_token = $hosted_url = $data_order_txid = NULL;
      $need_data                = TRUE;
      $payment_form_data_exists = FALSE;
      
      $loop_count = 2;
      do {
         
         if (!WC()->session->has_session()) {
            $session_exists           = FALSE;
            $payment_form_data_exists = FALSE;
            
            $data_order_txid = $triplea->generate_order_txid();
            triplea_write_log('triplea_ajax_get_payment_form_data() : Generated new order_txid as there was no session yet : ' . $data_order_txid, $debug_log_enabled);
            
         }
         else {
            $session_exists = TRUE;
            
            $payment_reference   = WC()->session->get('triplea_payment_reference');
            $access_token        = WC()->session->get('triplea_payment_access_token');
            $hosted_url          = WC()->session->get('triplea_payment_hosted_url');
            $access_token_expiry = WC()->session->get('triplea_payment_access_token_expiry');
            $cart_total = WC()->session->get('triplea_cart_total');
   
   
            if (!empty($payment_reference)
                && !empty($access_token)
                && !empty($hosted_url)
                && !empty($access_token_expiry)) {
               $date_now = (new DateTime())->getTimestamp();
               // Just to avoid loading a second before expiry of token.
               $five_minutes = 5 * 60;
               //if ($access_token_expiry > $date_now + $five_minutes) {
               if ($access_token_expiry < $date_now + $five_minutes) {
                  triplea_write_log('triplea_ajax_get_payment_form_data() : access token expired, ' . $access_token_expiry . ' < ' . ($date_now + $five_minutes), $debug_log_enabled);
                  $need_data = TRUE;
               }
               elseif ($cart_total != WC()->cart->total) {
                  triplea_write_log('triplea_ajax_get_payment_form_data(): updating cart total! ' . WC()->cart->total . ' != ' . $cart_total, $debug_log_enabled);
                  $need_data = TRUE;
                  WC()->session->set('triplea_cart_total', WC()->cart->total);
               }
               else {
                  $need_data = FALSE;
               }
               $payment_form_data_exists = TRUE;
            }
            
            $data_order_txid = WC()->session->get('generate_order_txid');
            if (empty($data_order_txid)) {
               $data_order_txid = $triplea->generate_order_txid();
               WC()->session->set('generate_order_txid', $data_order_txid);
               triplea_write_log('triplea_ajax_get_payment_form_data() : Generated new order_txid because there was none yet in the existing session : ' . $data_order_txid, $debug_log_enabled);
            }
            
         }
         
         $is_data_expired = FALSE;
         if ($need_data) {
            triplea_write_log('Preparing to make payment form request using order_txid "' . $data_order_txid . '".', $debug_log_enabled);
            
            $payment_form_data = $triplea->get_payment_form_request(
                  $data_order_txid,
                  $user_firstname,
                  $user_lastname,
                  $user_email,
                  $user_phone,
                  $user_address
            );
            
            if (isset($payment_form_data->error) || !isset($payment_form_data->payment_reference)) {
               triplea_write_log('Error. Ajax payment form request failed', $debug_log_enabled);
               echo json_encode(
                  [
                     'status'  => 'notok',
                     'code'    => isset($payment_form_data->code) ? $payment_form_data->code : 'Unknown error code.',
                     'message' => isset($payment_form_data->message) ? $payment_form_data->message : 'Unknown error message.',
                     'error'   => isset($payment_form_data->error) ? $payment_form_data->error : 'Unknown error.',
                  ]
               );
               return;
            }
            
            triplea_write_log('Ajax payment form request succeeded', $debug_log_enabled);
            
            // Needed in the checkout front-end page
            WC()->session->set('triplea_payment_hosted_url', $payment_form_data->hosted_url);
            // TODO ! Get this from session during process_payment order placing call.
            WC()->session->set('triplea_payment_reference', $payment_form_data->payment_reference);
            WC()->session->set('triplea_payment_access_token', $payment_form_data->access_token);
            WC()->session->set('triplea_payment_access_token_expiry', (new DateTime())->getTimestamp() + $payment_form_data->expires_in);
            WC()->session->set('triplea_payment_notify_secret', $payment_form_data->notify_secret);
            WC()->session->set('triplea_payment_crypto_currency', $payment_form_data->crypto_currency);
            WC()->session->set('triplea_payment_crypto_address', $payment_form_data->crypto_address);
            WC()->session->set('triplea_payment_crypto_amount', $payment_form_data->crypto_amount);
            WC()->session->set('triplea_payment_order_currency', $payment_form_data->order_currency);
            WC()->session->set('triplea_payment_order_amount', $payment_form_data->order_amount);
            WC()->session->set('triplea_payment_exchange_rate', $payment_form_data->exchange_rate);
            
            $payment_reference             = $payment_form_data->payment_reference;
            $access_token                  = $payment_form_data->access_token;
            $hosted_url                    = $payment_form_data->hosted_url;
            $access_token_expiry_time_left = $payment_form_data->expires_in;
            if ($access_token_expiry_time_left < (5 * 60)) {
               $is_data_expired = TRUE;
            }
         }
         
         
         // TODO verify payment status, make sure the session's data hasn't expired yet..
         triplea_write_log('Checking payment status to make sure we dont use expired cached form data', $debug_log_enabled);
         
         // Access token expiry is what we're interested in, so the below check can be avoided?
         //$is_data_expired = $triplea->get_payment_form_status_update();
         
         if ($is_data_expired) {
            triplea_write_log('Cached payment status has expired. Resetting form data to force refresh.', $debug_log_enabled);
            WC()->session->set('triplea_payment_reference', NULL);
            WC()->session->set('triplea_payment_access_token', NULL);
            WC()->session->set('triplea_payment_hosted_url', NULL);
         }
         else {
            triplea_write_log('Payment status data is up-to-date, ready to use for the checkout page.', $debug_log_enabled);
         }
         
         $loop_count -= 1;
      } while ($is_data_expired && $loop_count >= 0);
      
      echo json_encode(
         [
            'status'            => 'ok',
            'message'           => 'Payment form data ready.',
            'payment_reference' => $payment_reference,
            'access_token'      => $access_token,
            'url'               => $hosted_url,
            'order_txid'        => $data_order_txid,
            'meta'              => [
               'session_exists'           => $session_exists,
               'payment_form_data_exists' => $payment_form_data_exists,
            ],
         ]
      );
   }
   
   private function refreshOauthTokens() {
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      
      $date_now          = (new DateTime())->getTimestamp();
      $date_expiry_limit = $date_now - 864000;   // 10 days before the 10 year limit is over
      
      if (isset($this->triplea_btc2fiat_client_id) && !empty($this->triplea_btc2fiat_client_id)
          && isset($this->triplea_btc2fiat_client_secret) && !empty($this->triplea_btc2fiat_client_secret)
          && (!isset($this->triplea_btc2fiat_oauth_token)
              || empty($this->triplea_btc2fiat_oauth_token)
              || $this->triplea_btc2fiat_oauth_token_expiry <= $date_expiry_limit)) {
         if ($this->triplea_btc2fiat_oauth_token_expiry <= $date_expiry_limit) {
            triplea_write_log('refreshOauthToken() OAuth token (for local currency settlement) expires in less than 10 days. Requesting a new oauth token.', $debug_log_enabled);
         }
         else {
            triplea_write_log('refreshOauthToken() OAuth token (for local currency settlement) is missing. Requesting a new oauth token.', $debug_log_enabled);
         }
         
         $new_token_data = $this->getOauthToken($this->triplea_btc2fiat_client_id, $this->triplea_btc2fiat_client_secret);
         
         triplea_write_log('refreshOauthToken() OAuth token data received : ' . print_r($new_token_data, TRUE), $debug_log_enabled);
         
         if ($new_token_data !== FALSE
             && isset($new_token_data->access_token)
             && !empty($new_token_data->access_token)
             && isset($new_token_data->expires_in)
             && !empty($new_token_data->expires_in)) {
            $this->triplea_btc2fiat_oauth_token        = $new_token_data->access_token;
            $this->triplea_btc2fiat_oauth_token_expiry = $date_now + $new_token_data->expires_in;
            $this->update_option('triplea_btc2fiat_oauth_token', $this->triplea_btc2fiat_oauth_token);
            $this->update_option('triplea_btc2fiat_oauth_token_expiry', $this->triplea_btc2fiat_oauth_token_expiry);
            triplea_write_log('refreshOauthToken() Obtained and saved a new oauth token.', $debug_log_enabled);
         }
         else {
            triplea_write_log("refreshOauthToken() A problem happened, could not get a new oauth token. \n" . print_r($new_token_data, TRUE), $debug_log_enabled);
            $this->triplea_btc2fiat_oauth_token        = NULL;
            $this->triplea_btc2fiat_oauth_token_expiry = NULL;
            $this->update_option('triplea_btc2fiat_oauth_token', $this->triplea_btc2fiat_oauth_token);
            $this->update_option('triplea_btc2fiat_oauth_token_expiry', $this->triplea_btc2fiat_oauth_token_expiry);
         }
      }
   
      if (isset($this->triplea_btc2btc_client_id) && !empty($this->triplea_btc2btc_client_id)
          && isset($this->triplea_btc2btc_client_secret) && !empty($this->triplea_btc2btc_client_secret)
          && (!isset($this->triplea_btc2btc_oauth_token)
              || empty($this->triplea_btc2btc_oauth_token)
              || $this->triplea_btc2btc_oauth_token_expiry <= $date_expiry_limit)) {
         if ($this->triplea_btc2btc_oauth_token_expiry <= $date_expiry_limit) {
            triplea_write_log('refreshOauthToken() OAuth token (for live btc settlement) expires in less than 10 days. Requesting a new oauth token.', $debug_log_enabled);
         }
         else {
            triplea_write_log('refreshOauthToken() OAuth token (for live btc settlement) is missing. Requesting a new oauth token.', $debug_log_enabled);
         }
         $new_token_data = $this->getOauthToken($this->triplea_btc2btc_client_id, $this->triplea_btc2btc_client_secret);
      
         triplea_write_log('refreshOauthToken() OAuth token data received ', $debug_log_enabled);
      
         if ($new_token_data !== FALSE
             && isset($new_token_data->access_token)
             && !empty($new_token_data->access_token)
             && isset($new_token_data->expires_in)
             && !empty($new_token_data->expires_in)) {
            $this->triplea_btc2btc_oauth_token        = $new_token_data->access_token;
            $this->triplea_btc2btc_oauth_token_expiry = $date_now + $new_token_data->expires_in;
            $this->update_option('triplea_btc2btc_oauth_token', $this->triplea_btc2btc_oauth_token);
            $this->update_option('triplea_btc2btc_oauth_token_expiry', $this->triplea_btc2btc_oauth_token_expiry);
            triplea_write_log('refreshOauthToken() Obtained and saved a new oauth token.', $debug_log_enabled);
         }
         else {
            triplea_write_log("refreshOauthToken() A problem happened, could not get a new oauth token. \n" . print_r($new_token_data, TRUE), $debug_log_enabled);
            $this->triplea_btc2btc_oauth_token        = NULL;
            $this->triplea_btc2btc_oauth_token_expiry = NULL;
            $this->update_option('triplea_btc2btc_oauth_token', $this->triplea_btc2btc_oauth_token);
            $this->update_option('triplea_btc2btc_oauth_token_expiry', $this->triplea_btc2btc_oauth_token_expiry);
         }
      }
   
      if ($this->triplea_btc2btc_sandbox_client_id === $this->triplea_btc2btc_client_id && $this->triplea_btc2btc_sandbox_client_secret === $this->triplea_btc2btc_client_secret) {
         triplea_write_log('refreshOauthToken() Sandbox BTC account using same client credentials as Live BTC settlement account. Skipping oauth renewal.', $debug_log_enabled);
         if ($this->triplea_btc2btc_sandbox_oauth_token !== $this->triplea_btc2btc_oauth_token || $this->triplea_btc2btc_sandbox_oauth_token_expiry !== $this->triplea_btc2btc_oauth_token_expiry) {
            triplea_write_log('refreshOauthToken() Different values detected. Syncing oauth token/expiry for Sandbox BTC account with those from Live BTC settlement account.', $debug_log_enabled);
         }
      }
      else if (isset($this->triplea_btc2btc_sandbox_client_id) && !empty($this->triplea_btc2btc_sandbox_client_id)
          && isset($this->triplea_btc2btc_sandbox_client_secret) && !empty($this->triplea_btc2btc_sandbox_client_secret)
          && (!isset($this->triplea_btc2btc_sandbox_oauth_token)
              || empty($this->triplea_btc2btc_sandbox_oauth_token)
              || $this->triplea_btc2btc_sandbox_oauth_token_expiry <= $date_expiry_limit)) {
         
         if ($this->triplea_btc2btc_sandbox_oauth_token_expiry <= $date_expiry_limit) {
            triplea_write_log('refreshOauthToken() OAuth token (for sandbox btc settlement) expires in less than 10 days. Requesting a new oauth token.', $debug_log_enabled);
         }
         else {
            triplea_write_log('refreshOauthToken() OAuth token (for sandbox btc settlement) is missing. Requesting a new oauth token.', $debug_log_enabled);
         }
         $new_token_data = $this->getOauthToken($this->triplea_btc2btc_sandbox_client_id, $this->triplea_btc2btc_sandbox_client_secret);
      
         triplea_write_log('refreshOauthToken() OAuth token data received : ' . print_r($new_token_data, TRUE), $debug_log_enabled);
      
         if ($new_token_data !== FALSE
             && isset($new_token_data->access_token)
             && !empty($new_token_data->access_token)
             && isset($new_token_data->expires_in)
             && !empty($new_token_data->expires_in)) {
            $this->triplea_btc2btc_sandbox_oauth_token        = $new_token_data->access_token;
            $this->triplea_btc2btc_sandbox_oauth_token_expiry = $date_now + $new_token_data->expires_in;
            $this->update_option('triplea_btc2btc_sandbox_oauth_token', $this->triplea_btc2btc_sandbox_oauth_token);
            $this->update_option('triplea_btc2btc_sandbox_oauth_token_expiry', $this->triplea_btc2btc_sandbox_oauth_token_expiry);
            
            triplea_write_log('refreshOauthToken() Obtained and saved a new oauth token.', $debug_log_enabled);
         }
         else {
            triplea_write_log("refreshOauthToken() A problem happened, could not get a new oauth token. \n" . print_r($new_token_data, TRUE), $debug_log_enabled);
            $this->triplea_btc2btc_sandbox_oauth_token        = NULL;
            $this->triplea_btc2btc_sandbox_oauth_token_expiry = NULL;
            $this->update_option('triplea_btc2btc_sandbox_oauth_token', $this->triplea_btc2btc_sandbox_oauth_token);
            $this->update_option('triplea_btc2btc_sandbox_oauth_token_expiry', $this->triplea_btc2btc_sandbox_oauth_token_expiry);
         }
      }
   }
   
   private
   function getOauthToken(
      $client_id, $client_secret
   ) {
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      
      $post_url = 'https://api.triple-a.io/api/v1/oauth/token';
      $body     = [
         'client_id'     => $client_id,
         'client_secret' => $client_secret,
         'grant_type'    => 'client_credentials',
      ];
      //   $body = http_build_query($body);
      triplea_write_log("Making an oauth token request with body: \n" . print_r($body, TRUE), $debug_log_enabled);
      
      $result = wp_remote_post($post_url, [
         'method'      => 'POST',
         'headers'     => [
            'content-type' => 'application/x-www-form-urlencoded; charset=utf-8',
         ],
         //'sslverify' => false,
         'body'        => $body,
         'data_format' => 'body',
      ]);
      
      if (is_wp_error($result)) {
         return ['error' => 'Error happened, could not complete the oauth token request.'];
      }
      triplea_write_log("Oauth token request object: \n" . print_r($result['body'], TRUE), $debug_log_enabled);
      
      return json_decode($result['body']);
   }
   
   /**
    * Make a payment form request to TripleA API.
    * Returns a object containing (amongst others)
    * a payment_reference, access_token, notify_secret and hosted_url.
    *
    * @return mixed|string[]|object
    */
   private
   function get_payment_form_request(
      $order_txid,
      $user_firstname,
      $user_lastname,
      $user_email,
      $user_phone,
      $user_address
   ) {
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      
      $this->activePaymentAccountNeeded();
      
      $payment_mode         = $this->get_option('triplea_payment_mode');
      $sandbox_payment_mode = $this->get_option('triplea_sandbox_payment_mode');
      
      if ($payment_mode === 'bitcoin-to-bitcoin' && !$sandbox_payment_mode) {
         // btc2btc live payments
         $oauth_token = $this->get_option('triplea_btc2btc_oauth_token');
         if (empty($oauth_token)) {
            wp_die('Missing oauth token for live bitcoin payments to bitcoin wallet.');
         }
      }
      elseif ($payment_mode === 'bitcoin-to-bitcoin' && $sandbox_payment_mode) {
         // btc2btc live payments
         $oauth_token = $this->get_option('triplea_btc2btc_sandbox_oauth_token');
         if (empty($oauth_token)) {
            wp_die('Missing oauth token for sandbox bitcoin payments to bitcoin wallet.');
         }
      }
      else {
         $oauth_token = $this->get_option('triplea_btc2fiat_oauth_token');
         if (empty($oauth_token)) {
            wp_die('Missing oauth token for bitcoin payments with local currency settlement.');
         }
      }
      
      $post_url = 'https://api.triple-a.io/api/v1/payment/request';
      $body     = $this->preparePaymentFormRequestBody(
         $order_txid,
         $user_firstname,
         $user_lastname,
         $user_email,
         $user_phone,
         $user_address
      );
      
      triplea_write_log("Making a payment form API request with body: \n" . print_r($body, TRUE), $debug_log_enabled);
      
      $result = wp_remote_post($post_url, [
         'method'      => 'POST',
         'headers'     => [
            'Authorization' => 'Bearer ' . $oauth_token,
            'Content-Type'  => 'application/json; charset=utf-8',
         ],
         //'sslverify' => false,
         'body'        => json_encode($body),
         'data_format' => 'body',
      ]);
      
      if (is_wp_error($result)) {
         return ['error' => 'Error happened, could not complete the payment form request.'];
      }
      triplea_write_log("Payment form request response code: \n" . print_r($result['response']['code'], TRUE), $debug_log_enabled);
      
      triplea_write_log("Payment form request response: \n" . print_r($result['body'], TRUE), $debug_log_enabled);
      
      if ($result['response']['code'] > 299) {
         return json_encode([
            'error'   => 'Error happened, could not complete the payment form request.',
            'code'    => $result['response']['code'],
            'message' => $result['response']['message'],
         ]);
      }
      
      $json_result = json_decode($result['body']);
      if (!isset($json_result->payment_reference)) {
         return json_encode([
            'error' => 'Error happened, wrong payment form request data format received.',
         ]);
      }
      
      return $json_result;
   }
   
   /**
    * Get the payment details. Allows instant checking of the up-to-date payment
    * status. API doc: https://doc.triple-a.io/#operation/PaymentDetails
    *
    * Sample response:
    * {
    * "payment_reference": "SDF-453672-PMT",
    * "crypto_currency": "testBTC",
    * "crypto_address": "1NcAyv8YVCnQGCrDb4kiUm1jj6GLyowxER",
    * "crypto_amount": 0.001067203,
    * "order_currency": "USD",
    * "order_amount": 10,
    * "exchange_rate": 9370.28,
    * "expiry_date": "2020-01-26T03:57:22Z",
    * "unconfirmed_crypto_amt": 0,
    * "unconfirmed_order_amt": 0,
    * "confirmed_crypto_amt": 0.00023,
    * "confirmed_order_amt": 2.3,
    * "status": "confirmed",
    * "status_date": "2020-01-26T03:57:22Z",
    * "payment_tier": "good",
    * "payment_tier_date": "2020-01-26T03:57:22Z",
    * "payment_amount": 2.3,
    * "cart": {
    * "items": [
    * {
    * "sku": "ABC8279289",
    * "label": "A tale of 2 cities",
    * "quantity": 10,
    * "amount": 7
    * }
    * ],
    * "shipping_cost": 2,
    * "shipping_discount": 1,
    * "tax_cost": 2
    * },
    * "expires_in": 3599,
    * "site_name": "TripleA Gift Cards Pte Ltd",
    * "success_url": "https://www.success.io/success.html",
    * "cancel_url": "https://www.failure.io/cancel.html",
    * "hosted_page": {
    * "version": 1,
    * "name": "Gift Cards Galore",
    * "logo_url": "https://triple-a.io/logo.png",
    * "tagline": "Tons of gift cards as long as they are Amazon",
    * "btn_primary_background_color": "#46d5ba",
    * "btn_primary_color": "#ffffff",
    * "page_background_color": "#2da2fb"
    * },
    * "payer_id": "TRE1787238200",
    * "payer_name": "Alice Tan",
    * "payer_email": "alice.tan@triple-a.io",
    * "payer_phone": "+6591234567",
    * "payer_address": "1 Parliament Place, Singapore 178880",
    * "payer_poi":
    * "https://icatcare.org/app/uploads/2018/07/Thinking-of-getting-a-cat.png",
    * "payer_required_data": {
    * "payer_email_or_phone": true,
    * "payer_name": false,
    * "payer_address": false,
    * "payer_poi": false
    * }
    * }
    *
    * @param $payment_reference
    *
    * @return array|mixed|string[]
    */
   private function get_payment_form_status_update($payment_reference) {
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      
      $this->activePaymentAccountNeeded();
   
      $payment_mode         = $this->get_option('triplea_payment_mode');
      $sandbox_payment_mode = $this->get_option('triplea_sandbox_payment_mode');
   
      if ($payment_mode === 'bitcoin-to-bitcoin' && !$sandbox_payment_mode) {
         // btc2btc live payments
         $oauth_token = $this->get_option('triplea_btc2btc_oauth_token');
         if (empty($oauth_token)) {
            wp_die('Missing oauth token for live bitcoin payments to bitcoin wallet.');
         }
      }
      elseif ($payment_mode === 'bitcoin-to-bitcoin' && $sandbox_payment_mode) {
         // btc2btc live payments
         $oauth_token = $this->get_option('triplea_btc2btc_sandbox_oauth_token');
         if (empty($oauth_token)) {
            wp_die('Missing oauth token for sandbox bitcoin payments to bitcoin wallet.');
         }
      }
      else {
         $oauth_token = $this->get_option('triplea_btc2fiat_oauth_token');
         if (empty($oauth_token)) {
            wp_die('Missing oauth token for bitcoin payments with local currency settlement.');
         }
      }
      
      $post_url = "https://api.triple-a.io/api/v1/payment/$payment_reference";
      
      $result = wp_remote_get($post_url, [
         'headers'     => [
            'Authorization' => 'Bearer ' . $oauth_token,
         ],
         //'sslverify' => false,
         //'body'        => json_encode($body),
         'data_format' => 'body',
      ]);
      
      if (is_wp_error($result)) {
         wp_die('Could not complete the payment status API request.');
      }
      
      if ($result['response']['code'] > 299) {
         return (object) [
            'error'   => 'Error happened, could not complete the payment form request.',
            'code'    => $result['response']['code'],
            'message' => $result['response']['message'],
         ];
      }
      
      $json_result = json_decode($result['body']);
      if (!isset($json_result->payment_reference)) {
         return [
            'error' => 'Error happened, wrong payment form request data format received.',
         ];
      }
      
      return $json_result;
   }
   
   /**
    * Returns an array containing all required data (request body) about the
    * order for which a payment form request will be sent.
    *
    * @return array
    */
   private
   function preparePaymentFormRequestBody(
      $order_txid,
      $user_firstname,
      $user_lastname,
      $user_email,
      $user_phone,
      $user_address
   ) {
      $debug_log_enabled = $this->get_option('debug_log_enabled') === 'yes';
      
      // Need to know whether it's btc or fiat settlement, sandbox or live payments
      $payment_mode         = $this->get_option('triplea_payment_mode');
      $sandbox_payment_mode = $this->get_option('triplea_sandbox_payment_mode');
      
      if ($payment_mode === 'bitcoin-to-cash' && $sandbox_payment_mode) {
         $api_id          = $this->get_option('triplea_btc2fiat_sandbox_api_id');
         $crypto_currency = 'testBTC';
      }
      elseif ($payment_mode === 'bitcoin-to-cash' && !$sandbox_payment_mode) {
         $api_id          = $this->get_option('triplea_btc2fiat_api_id');
         $crypto_currency = 'BTC';
      }
      elseif ($payment_mode === 'bitcoin-to-bitcoin' && $sandbox_payment_mode) {
         $api_id          = $this->get_option('triplea_btc2btc_sandbox_api_id');
         $crypto_currency = 'testBTC';
      }
      elseif ($payment_mode === 'bitcoin-to-bitcoin' && !$sandbox_payment_mode) {
         $api_id          = $this->get_option('triplea_btc2btc_api_id');
         $crypto_currency = 'BTC';
      }
      else {
         wp_die('Invalid payment mode. Cannot proceed with payment form request.');
      }
      
      // widget(embedded) or triplea(external)
      $payment_form_type = 'widget';
      
      if (is_checkout_pay_page()) {
         $data_order_id = get_query_var('order-pay');
         $order         = wc_get_order($data_order_id);
         
         $order_amount   = esc_attr(((WC()->version < '2.7.0') ? $order->order_total : $order->get_total()));
         $order_currency = esc_attr(((WC()->version < '2.7.0') ? $order->order_currency : $order->get_currency()));
      }
      else {
         $order_amount   = esc_attr(WC()->cart->total);
         $order_currency = esc_attr(strtoupper(get_woocommerce_currency()));
      }
   
      triplea_write_log('Order WC()->cart->total: '.WC()->cart->total, $debug_log_enabled);
      triplea_write_log('Order WC()->cart->get_cart_total(): '.WC()->cart->get_cart_total(), $debug_log_enabled);
      triplea_write_log('Order WC()->cart->get_cart_contents_total(): '.WC()->cart->get_cart_contents_total(), $debug_log_enabled);
      triplea_write_log('Order WC()->cart->get_cart_discount_total(): '.WC()->cart->get_cart_discount_total(), $debug_log_enabled);
      triplea_write_log('Order WC()->cart->get_cart_shipping_total(): '.WC()->cart->get_cart_shipping_total(), $debug_log_enabled);
      
      $tax_cost          = NULL; //WC()->cart->get_tax_totals();
      $shipping_cost     = empty(WC()->cart->get_cart_shipping_total()) ? NULL : WC()->cart->get_cart_shipping_total();
      $shipping_discount = NULL;
      
      $extra_metadata = [
         //'order_txid' => WC()->session->get('generate_order_txid'),
         'order_txid' => $order_txid,
      ];
      
      $notify_url = get_rest_url(NULL, 'triplea/v1/triplea_webhook/' . get_option('triplea_api_endpoint_token'));
      
      if (isset(WC()->customer) && WC()->customer->get_id()) {
         $payer_id    = WC()->customer->get_id() . '__' . WC()->customer->get_username();
         $payer_name  = (WC()->customer->get_first_name() ? WC()->customer->get_first_name() : WC()->customer->get_billing_first_name()) . ' ' . (WC()->customer->get_last_name() ? WC()->customer->get_last_name() : WC()->customer->get_billing_last_name());
         $payer_email = empty(WC()->customer->get_email()) ? WC()->customer->get_billing_email() : WC()->customer->get_email();
         // phone number validation could too easily cause problem, add in metadata
         $payer_phone   = NULL;
         $payer_address = join(',', [
            WC()->customer->get_billing_address(),
            WC()->customer->get_billing_address_1(),
            WC()->customer->get_billing_address_2(),
            WC()->customer->get_billing_city(),
            WC()->customer->get_billing_state(),
            WC()->customer->get_billing_country(),
            WC()->customer->get_billing_postcode(),
         ]);
         
         if (!empty(WC()->customer->get_username())) {
            $extra_metadata['username'] = WC()->customer->get_username();
         }
         if (!empty(WC()->customer->get_id())) {
            $extra_metadata['userid'] = WC()->customer->get_id();
         }
         
         if (!empty(WC()->customer->get_billing_phone())) {
            $extra_metadata['payer_phone'] = WC()->customer->get_billing_phone();
         }
         
         if (!empty(WC()->customer->get_billing_email())) {
            $extra_metadata['billing_email'] = WC()->customer->get_billing_email();
         }
         if (!empty(WC()->customer->get_billing_city())) {
            $extra_metadata['billing_city'] = WC()->customer->get_billing_city();
         }
         if (!empty(WC()->customer->get_billing_country())) {
            $extra_metadata['billing_country'] = WC()->customer->get_billing_country();
         }
         if (!empty(WC()->customer->get_billing_company())) {
            $extra_metadata['billing_company'] = WC()->customer->get_billing_company();
         }
         if (!empty(WC()->customer->get_billing_first_name())) {
            $extra_metadata['billing_first_name'] = WC()->customer->get_billing_first_name();
         }
         if (!empty(WC()->customer->get_billing_last_name())) {
            $extra_metadata['billing_last_name'] = WC()->customer->get_billing_last_name();
         }
         
         if (!empty(WC()->customer->get_shipping_address())) {
            $extra_metadata['shipping_address'] = WC()->customer->get_shipping_address();
         }
         if (!empty(WC()->customer->get_shipping_address_1())) {
            $extra_metadata['shipping_address_1'] = WC()->customer->get_shipping_address_1();
         }
         if (!empty(WC()->customer->get_shipping_address_2())) {
            $extra_metadata['shipping_address_2'] = WC()->customer->get_shipping_address_2();
         }
         if (!empty(WC()->customer->get_shipping_city())) {
            $extra_metadata['shipping_city'] = WC()->customer->get_shipping_city();
         }
         if (!empty(WC()->customer->get_shipping_country())) {
            $extra_metadata['shipping_country'] = WC()->customer->get_shipping_country();
         }
         if (!empty(WC()->customer->get_shipping_company())) {
            $extra_metadata['shipping_company'] = WC()->customer->get_shipping_company();
         }
         if (!empty(WC()->customer->get_shipping_postcode())) {
            $extra_metadata['shipping_postcode'] = WC()->customer->get_shipping_postcode();
         }
         if (!empty(WC()->customer->get_shipping_state())) {
            $extra_metadata['shipping_state'] = WC()->customer->get_shipping_state();
         }
         if (!empty(WC()->customer->get_shipping_first_name())) {
            $extra_metadata['shipping_first_name'] = WC()->customer->get_shipping_first_name();
         }
         if (!empty(WC()->customer->get_shipping_last_name())) {
            $extra_metadata['shipping_last_name'] = WC()->customer->get_shipping_last_name();
         }
         
      }
      else {
         
         if (!empty($user_email)) {
            $payer_id      = 'guest_' . $user_email;
         }
         else {
            $payer_id      = 'guest_' . $this->randomString() . '.';
         }
         $payer_name    = $user_firstname .' '. $user_lastname;
         $payer_email   = $user_email;
         $payer_phone   = null;
         $payer_address = $user_address;
         
         $extra_metadata['payer_id'] = $payer_id;
         
         if (!empty(WC()->customer->get_username())) {
            $extra_metadata['username'] = 'anonymous';
         }
      }
      
      $cart_items = [];
      foreach (WC()->cart->get_cart() as $cart_item) {
         $product = $cart_item['data'];
         if (!empty($product)) {
            $new_item = [];
            
            $new_item['label'] = !empty($product->get_name()) ? $product->get_name() : 'unknown product name';
            $new_item['sku']   = !empty($product->get_sku()) ? $product->get_sku() : 'no_sku';
            
            if (!empty($cart_item['quantity'])) {
               $new_item['quantity'] = floatval($cart_item['quantity']);
            }
            if (!empty($product->get_price())) {
               $new_item['amount'] = floatval($product->get_price());
            }
            if (!empty($new_item)) {
               $cart_items[] = $new_item;
            }
         }
      }
      
      // Not including notify_email, account's default will be used.
      // Not including notify_secret, server will generate one for each request.
      
      $body = [
         "type"            => $payment_form_type,
         "api_id"          => $api_id,
         "crypto_currency" => $crypto_currency,
         "order_currency"  => $order_currency,
         "order_amount"    => $order_amount,
         //"notify_email"    => $notify_email,
         "notify_url"      => $notify_url,
         //"notify_secret"   => $notify_secret,
         // either user_id or guest+random token
         "payer_id"        => $payer_id,
         // only if available
         "payer_name"      => $payer_name,
         // only if available
         "payer_email"     => $payer_email,
         // only if available
         "payer_phone"     => $payer_phone,
         // only if available
         "payer_address"   => $payer_address,
         //"payer_poi"       => $payer_poi,
         //"success_url"     => "https://www.success.io/success.html",
         //"cancel_url"      => "https://www.failure.io/cancel.html",
         "cart"            =>
            [
               "items"             => $cart_items,
               "shipping_cost"     => 0,
               "tax_cost"          => 0,
               "shipping_discount" => 0,
            ],
         "webhook_data"    => $extra_metadata,
      ];
      
      if (!empty($cart_items)) {
         $body['cart']['items'] = $cart_items;
         
         if (!empty($shipping_cost)) {
            $body['cart']['shipping_cost'] = floatval($shipping_cost);
         }
         if (!empty($shipping_discount)) {
            $body['cart']['shipping_discount'] = floatval($shipping_discount);
         }
         if (!empty($tax_cost)) {
            $body['cart']['tax_cost'] = floatval($tax_cost);
         }
      }
      
      return $body;
   }
   
   private
   function activePaymentAccountNeeded() {
      $triplea_active_api_id = $this->get_option('triplea_active_api_id');
      if (!isset($triplea_active_api_id) || empty($triplea_active_api_id)) {
         wp_die('Error. No active payment account found.');
      }
   }
   
   private
   function randomString(
      $length = 24
   ) {
      if (PHP_VERSION >= 7) {
         $bytes = random_bytes($length);
      }
      else {
         $bytes = openssl_random_pseudo_bytes($length);
      }
      
      return bin2hex($bytes); // 48 characters
   }
   
}

