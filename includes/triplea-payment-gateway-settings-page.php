<?php
//if (!defined('ABSPATH')) {
//    exit;
//}

return array(

  'triplea_pubkey_id' => array(
    'type' => 'hidden',
  ),
  'triplea_pubkey_id_for_conversion' => array(
    'type' => 'hidden',
  ),
  'triplea_active_pubkey_id' => array(
    'type' => 'hidden',
  ),

  'triplea_server_public_enc_key_btc' => array(
    'type' => 'hidden',
  ),
  'triplea_server_public_enc_key_conversion' => array(
    'type' => 'hidden',
  ),
  
  'triplea_dashboard_email' => array(
    'type' => 'hidden',
  ),
  'triplea_notifications_email' => array(
    'type' => 'hidden',
  ),

  'triplea_payment_mode' => array(
    'type' => 'hidden',
  ),

  'enabled' => array(
        'title' => __('Bitcoin Payments', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
        'label' => __('Accept bitcoin payments', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
        'type' => 'hidden',
        'default' => 'yes',
    ),

    'triplea_mode' => array(
      'title' => __('Test or Live Mode', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
      'type' => 'hidden',
      'options' => array(
        'test' => __('Test Mode', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
        'live' => __('Live Mode', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN)
      ),
      'class' => 'wc-enhanced-select',
      'description' => __( 'Select LIVE mode when ready to accept bitcoin payments from the public. Payment option will only be visible to Admin users in TEST mode.',TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN ),
      'default' => 'live',
      'desc_tip' => false
    ),

    'triplea_anchor_conversion' => array(
      'title' => 'conversion',
      'type' => 'anchor'
    ),

    'triplea_payment_mode_form' => array(
      'title' => __('Payment mode', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
      'description' => __('Decide how you want your money to be delivered to you.', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
      'markup' => include('views/triplea-payment-gateway-settings-payment-mode.php'),
      'type' => 'custom'
      //'type' => 'table_markup'
    ),

    'triplea_pubkeyid_script' => array(
      'type' => 'triplea_pubkeyid_script',
    ),

    'triplea_plugin_options_form' => array(
      'title' => __('Plugin options', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
      'description' => __('Plugin options input fields and info', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
      'markup' => include('views/triplea-payment-gateway-settings-plugin-options.php'),
      'type' => 'custom'
      //'type' => 'table_markup'
    ),

  'debug_log_enabled' => array(
     'title' => __('Enable debug log', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
     'label' => __('Enable debug log', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN) . ' (<a target="_blank" href="../wp-content/uploads/triplea-bitcoin-payment-logs/triplea-bitcoin-payment-logs.log?'.time().'">'.__('view log', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN).'</a>)',
     'type' => 'checkbox',
     'default' => 'no',
  ),

  'debug_log_clear_action' => array(
     'title' => __('Clear debug log', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
     'label' => __('Clear the debug log (upon saving this form, log is cleared and this option is then toggled off again)', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
     'description' => __('On production, if you need to debug, clear the log afterwards!', TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_TEXTDOMAIN),
     'type' => 'checkbox',
     'default' => 'no',
  ),

//   'triplea_woocommerce_order_states' => array(
//      'type' => 'hidden',
//   ),
  
);
