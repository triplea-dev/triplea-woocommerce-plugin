<?php
if (!defined('ABSPATH')) {
   exit;
}

$payment_mode         = $this->get_option('triplea_payment_mode');
$sandbox_payment_mode = $this->get_option('triplea_sandbox_payment_mode');
//$notifications_email          = $this->get_option('triplea_notifications_email');
$fiat_merchant_key            = $this->get_option('triplea_btc2fiat_merchant_key');
$fiat_client_id               = $this->get_option('triplea_btc2fiat_client_id');
$fiat_client_secret           = substr($this->get_option('triplea_btc2fiat_client_secret'), 0, 5) . '...';
$fiat_merchant_name           = $this->get_option('triplea_btc2fiat_merchant_name');
$fiat_merchant_email          = $this->get_option('triplea_btc2fiat_merchant_email');
$fiat_merchant_phone          = $this->get_option('triplea_btc2fiat_merchant_phone');
$fiat_merchant_local_currency = $this->get_option('triplea_btc2fiat_merchant_local_currency');
$btc_merchant_key             = $this->get_option('triplea_btc2btc_merchant_key');
$btc_client_id                = $this->get_option('triplea_btc2btc_client_id');
$btc_client_secret            = substr($this->get_option('triplea_btc2btc_client_secret'), 0, 5) . '...';
$btc_merchant_name            = $this->get_option('triplea_btc2btc_merchant_name');
$btc_merchant_email           = $this->get_option('triplea_btc2btc_merchant_email');
$btc_merchant_phone           = $this->get_option('triplea_btc2btc_merchant_phone');
$btc_pubkey                   = $this->get_option('triplea_btc2btc_pubkey');
$btc_sandbox_merchant_key     = $this->get_option('triplea_btc2btc_sandbox_merchant_key');
$btc_sandbox_client_id        = $this->get_option('triplea_btc2btc_sandbox_client_id');
$btc_sandbox_client_secret    = substr($this->get_option('triplea_btc2btc_sandbox_client_secret'), 0, 5) . '...';
$btc_sandbox_merchant_name    = $this->get_option('triplea_btc2btc_sandbox_merchant_name');
$btc_sandbox_merchant_email   = $this->get_option('triplea_btc2btc_sandbox_merchant_email');
$btc_sandbox_merchant_phone   = $this->get_option('triplea_btc2btc_sandbox_merchant_phone');
$btc_sandbox_pubkey           = $this->get_option('triplea_btc2btc_sandbox_pubkey');

// BTC-2-BTC mode
$triplea_btc2btc_sandbox_api_id = $this->get_option('triplea_btc2btc_sandbox_api_id');
$triplea_btc2btc_api_id         = $this->get_option('triplea_btc2btc_api_id');
$output_btc2btc_api_id          = '';
if (!empty($triplea_btc2btc_api_id)) {
   $output_btc2btc_api_id = '<p>Your bitcoin-to-bitcoin API ID is <strong><u>' . $triplea_btc2btc_api_id . '</u></strong> .</p><br>';
}
// BTC-2-Cash conversion mode
$triplea_btc2fiat_sandbox_api_id = $this->get_option('triplea_btc2fiat_sandbox_api_id');
$triplea_btc2fiat_api_id         = $this->get_option('triplea_btc2fiat_api_id');
$output_btc2fiat_api_id          = '';
if (!empty($triplea_btc2fiat_api_id)) {
   $output_btc2fiat_api_id = '<p>Your bitcoin-to-local currency API ID is <strong><u>' . $triplea_btc2fiat_api_id . '</u></strong> .</p><br>';
}

// Only if no need for upgrade..
$btc2fiat_is_active    = FALSE;
$btc2btc_is_active     = FALSE;
$triplea_active_api_id = $this->get_option('triplea_active_api_id');
$btc2fiat_is_active    = (!empty($triplea_active_api_id) && ($triplea_active_api_id === $triplea_btc2fiat_api_id || $triplea_active_api_id === $triplea_btc2fiat_sandbox_api_id));
$btc2btc_is_active     = (!empty($triplea_active_api_id) && ($triplea_active_api_id === $triplea_btc2btc_api_id || $triplea_active_api_id === $triplea_btc2btc_sandbox_api_id));

if ($btc2btc_is_active && $triplea_active_api_id === $triplea_btc2btc_sandbox_api_id) {
   $sandbox_payment_mode = true;
}
elseif ($btc2fiat_is_active && $triplea_active_api_id === $triplea_btc2fiat_sandbox_api_id) {
   $sandbox_payment_mode = true;
}

$return_url     = get_rest_url(NULL, 'triplea/v1/tx_update/' . get_option('triplea_api_endpoint_token'));
$local_currency = strtoupper(get_woocommerce_currency());
$site_info      = get_bloginfo('name');


//$triplea_notifications_email = $this->get_option('triplea_notifications_email');
//if (empty($triplea_notifications_email)) {
//   $triplea_notifications_email = '';
//}
//$triplea_dashboard_email = $this->get_option('triplea_dashboard_email');
//if (empty($triplea_dashboard_email)) {
//   $triplea_dashboard_email = '';
//}
$tmp_old_btc_id = $this->get_option('triplea_pubkey_id');
$old_btc2btc_sandbox_api_id = $tmp_old_btc_id; // '';
$old_btc2btc_api_id = $tmp_old_btc_id; //'';
//if (!empty($tmp_old_btc_id)) {
//   if (substr_compare($tmp_old_btc_id, "_t", -2, 2) === 0 ) {
//      $old_btc2btc_sandbox_api_id = $tmp_old_btc_id;
//      $old_btc2btc_api_id = '';
//   }
//   else {
//      $old_btc2btc_sandbox_api_id = '';
//      $old_btc2btc_api_id = $tmp_old_btc_id;
//   }
//}
$old_btc2fiat_api_id = $this->get_option('triplea_pubkey_id_for_conversion');
$old_active_api_id = $this->get_option('triplea_active_pubkey_id');
$old_btc2fiat_is_active = isset($old_active_api_id) && !empty($old_active_api_id) && $old_active_api_id === $old_btc2fiat_api_id;
$old_btc2btc_is_active = isset($old_active_api_id) && !empty($old_active_api_id) && ($old_active_api_id === $old_btc2btc_api_id || $old_active_api_id === $old_btc2btc_sandbox_api_id);
$old_payment_mode = $old_btc2fiat_is_active ? 'bitcoin-to-cash' : 'bitcoin-to-bitcoin';
$old_sandbox_payment_mode = !empty($old_btc2btc_sandbox_api_id);


$debug_log_enabled = 'yes' === $this->get_option('debug_log_enabled');
$info_data         = [
   'type'      => 'woocommerce',
   'name'      => substr(get_bloginfo('name'), 0, 100),
   'url'       => substr(get_bloginfo('url'), 0, 250),
   'admin'     => substr(get_bloginfo('admin_email'), 0, 60),
   'wp_v'      => substr(get_bloginfo('version'), 0, 15),
   'lang'      => substr(get_bloginfo('language'), 0, 8),
   'plugin_v'  => TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION,
   'debug_log' => $debug_log_enabled ? 1 : 0,
   'php_v'     => phpversion(),
];

ob_start();
?>
   <style>

     /* Fade-In Effect */
     .visible {
       visibility: visible;
       opacity: 1;
       transition: opacity 0.5s linear;
     }

     /* Fade-Out Effect */
     .hidden {
       visibility: hidden;
       opacity: 0;
       transition: visibility 0s 0.5s, opacity 0.5s linear;
     }

     .triplea_card {
       background-image: linear-gradient(-200deg, white 45%, rgba(90, 0, 190, 0.01) 100%);
       box-shadow: 0 3px 6px 0 rgba(0, 0, 0, 0.16);
       border: none;
       border-radius: 4px;
       min-height: 100px;
       height: auto;
       padding: 20px 15px 15px;
       margin: 0 8px 10px;
       width: 350px;
       float: left;
       position: relative;
     }

     .triplea_card.active {
       border: 2px solid darkseagreen;
     }

     .triplea_card::before,
     .triplea_card::after {
       content: "";
       display: table;
       clear: both;
     }

     .triplea_card_icon {
       position: relative;
       display: inline-block;
       height: 32px;
       width: 32px;
       background: rgba(0, 0, 0, 0.1);
       top: 5px;
     }

     .triplea_card h3 {
       margin: -10px 0 10px;
     }

     .triplea_card p {
       font-size: 95% !important;
       padding-bottom: 8px;
     }

     .triplea_card input {
       width: 90% !important;
     }

     .triplea_card label {
       margin-top: 8px;
       margin-bottom: 8px;
       display: block;
     }

     .triplea_card button[type="button"] {
       background: white;
       border: 1px solid lightgrey;
       padding: 6px 12px;
       border-radius: 4px;
       display: block;
       margin: 0 auto;
     }

     .triplea_card button[type="button"].active-account {
       background: #5377c7; /* green: #25a125 */
       color: white;
       font-weight: bold;
     }
   </style>

   <!--payments enabled with either btc or fiat settlement-->
   <div class="triplea_step" id="step-enabled"
        style="display: <?php echo($btc2fiat_is_active || $btc2btc_is_active ? 'block' : 'none'); ?>;">
      <hr>

      <br>

      <div id="step-enabled--btc2btc-sandbox"
           style="display:<?php echo($btc2btc_is_active && $sandbox_payment_mode ? 'block' : 'none') ?> ;">

         <h3><span style="text-decoration: underline;">Sandbox</span> Bitcoin payments are enabled.</h3>
         
         <p>
            You have chosen to receive sandbox bitcoin payments straight in your own <strong>testnet bitcoin wallet</strong>.
         </p>
         <p>
            View transactions in your own wallet or <a href="https://dashboard.triple-a.io" target="_blank">in your TripleA dashboard</a> with the credentials you have received by email.
         </p>
         <p>
            You can use <a href="https://duckduckgo.com/?q=bitcoin+testnet+faucets" target="_blank">bitcoin testnet faucets</a> to easily test payments.
         </p>
         <br>
         <input type="button" class="button-primary" value="Show settlement options" onclick="jQuery(this).hide(); gotoStep1()">
      </div>

      <div id="step-enabled--btc2btc"
           style="display:<?php echo($btc2btc_is_active && !$sandbox_payment_mode ? 'block' : 'none') ?> ;">

         <h3><span style="text-decoration: underline;">Live</span> Bitcoin payments are enabled.</h3>

         <h4>You will receive Bitcoin in your wallet.</h4>

         <p>
            You have chosen to receive bitcoin payments straight in your own <strong>bitcoin wallet</strong>.
         </p>
         <p>
            Bitcoin payments you receive can be viewed in your own wallet or for more details,
            <br>
            you may access <a href="https://dashboard.triple-a.io" target="_blank">your TripleA dashboard</a> with the credentials you have received by email.
         </p>
         <br>
         <input type="button" class="button-primary" value="Show settlement options" onclick="jQuery(this).hide(); gotoStep1()">
      </div>
  
      <div id="step-enabled--btc2fiat"
           style="display:<?php echo($btc2fiat_is_active ? 'block' : 'none') ?>;">

         <h3 style="display:<?php echo($sandbox_payment_mode ? 'block' : 'none') ?>;"><span style="text-decoration: underline;">Sandbox</span> bitcoin payments are enabled.</h3>
         
         <h3 style="display:<?php echo(!$sandbox_payment_mode ? 'block' : 'none') ?>;"><span style="text-decoration: underline;">Live</span> bitcoin payments are enabled.</h3>
         <h3 style="display:<?php echo(!$sandbox_payment_mode ? 'block' : 'none') ?>;">
            You will receive local currency.
         </h3>

         <p id="btc2fiat-message">
            You have chosen to receive bitcoin payments in the form of
            <strong>local currency</strong>
            settled to <strong>your bank account</strong>.
            <span style="display:<?php echo($sandbox_payment_mode ? 'block' : 'none') ?>;">
               (This does not apply to sandbox payments :)
            </span>
         </p>
         <p>
            Visit <a href="https://dashboard.triple-a.io" target="_blank">your TripleA dashboard</a> to manage bank account information, settlements, refunds, invoicing and more.
         </p>
         <br>
         <br>
         <input type="button"
                style="display:<?php echo($sandbox_payment_mode ? 'block' : 'none') ?>;"
                class="button-primary"
                value="Switch to Live payments"
                onclick="triplea_setActiveAccount('btc2fiat', false)">
         <input type="button"
                style="display:<?php echo(!$sandbox_payment_mode ? 'block' : 'none') ?>;"
                class="button-primary"
                value="Switch to Sandbox payments"
                onclick="triplea_setActiveAccount('btc2fiat', true)">
         <br>
         <input type="button" class="button-secondary" value="Show settlement options" onclick="jQuery(this).hide(); gotoStep1()">
      </div>

      <br>
      <br>
   </div>

   <!--choice between settlement modes-->
   <div class="triplea_step" id="step-1"
        style="display: <?php echo(!$btc2fiat_is_active && !$btc2btc_is_active ? 'initial' : 'none') ?>">
      <hr>
      <br>
      <h3 style="display:<?php echo(!$btc2btc_is_active && !$btc2fiat_is_active ? "block" : 'none') ?>;">Get started</h3>
      <h3 style="display:<?php echo($btc2btc_is_active || $btc2fiat_is_active ? "block" : 'none') ?>;">Change your active bitcoin payments account</h3>
      <p>
         How do you wish to receive bitcoin payments?
      </p>

      <input type="radio"
             name="receive-choice"
             value="localcurrency"
         <?php echo($btc2fiat_is_active || $old_btc2fiat_is_active || !$btc2btc_is_active ? "checked" : '') ?>
             style="margin:10px;"><strong>recommended</strong> - receive local currency<br>
      <div class="receive-choice-wrapper"
           style="margin-left: 40px;display:<?php echo($btc2fiat_is_active ? "block" : 'none') ?>;"
           id="localcurrency-form-wrapper">
         <div style="display:<?php echo(!empty($old_btc2fiat_api_id) && empty($fiat_merchant_key) ? "block" : "none") ?>;" id="fiat-version-upgrade-form-wrapper">
            <hr>
            <div class="triplea_step" id="upgrade-fiat-form">
               <h3>
                  <small>
                     Upgrade your local currency settlement account
                  </small>
               </h3>
               <p>
                  Due to technical improvements and added features, your account needs to be upgraded (a simple click!).
                  <br>
                  After upgrading, your account will be linked to your TripleA dashboard.
                  <br>
                  Login credentials will be shared by e-mail.
               </p>
               <!--<p>
                  Existing API ID: <?php /*echo $old_btc2fiat_api_id; */?>
                  <span class="woocommerce-help-tip" data-tip="This is your TripleA account identifier"></span>
               </p>-->

               <input type="button"
                      class="button-primary"
                      value="Upgrade"
                      onclick="triplea_fiat_upgrade_emailvalidationstep()">
               <br>
               <br>
            </div>
            <div class="triplea_step"
                 id="upgrade-fiat-validate-email"
                 style="opacity: 0.5;">
               <hr>
               <h3>
                  <small>
                     Validate email
                  </small>
               </h3>
               <p>
                  Sending OTP (one-time password) to your email address...
                  <strong id="fiat-upgrade-email-sent" style="display:none;">
                     Email sent!
                  </strong>
               </p>
               <small style="color:darkred;display:none;"
                      class="triplea-error-msg error-otp-request"
                      id="fiat-upgrade-error-otp-request">
                  Something went wrong while requesting OTP code. Try again, or
                  contact
                  us at support@triple-a.io so that we can assist you.
               </small>
               <p id="fiat-upgrade-enter-otp" style="opacity: 0.5;">
                  Enter the One-Time Password code:
                  <input type="text"
                         id="fiat-upgrade-otp-value"
                         name="fiat-upgrade-otp-value"
                         value=""
                         placeholder="One-Time Password"
                         style="width:150px">
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-provide-otp"
                         id="bitcoin-upgrade-error-provide-otp">
                     Please provide the OTP code that was sent to you by email.
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-otp-wrong"
                         id="fiat-upgrade-error-otp-wrong">
                     Wrong OTP provided.
                  </small>
                  <br>
                  <br>
                  <input type="button"
                         id="fiat-upgrade-otp-submit"
                         class="button-primary"
                         value="Activate local currency account"
                         onclick="triplea_fiat_upgrade_validateEmailOtp()"
                         style="opacity: 0.5;">
                  <small id="fiat-upgrade-otp-check-loading"
                         class="triplea-error-msg otp-check-loading"
                         style="display:none;">
                     Loading...
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg account-creation-error"
                         id="fiat-upgrade-account-creation-error">
                     The provided OTP was correct. However, something went wrong
                     during
                     the creation of your local currency settlement account. Please inform us
                     at
                     support@triple-a.io so that we may assist you promptly.
                  </small>
               </p>
            </div>
         </div>
         <div class="receive-choice-update-wrapper"
              style="display:<?php echo(!empty($triplea_btc2fiat_sandbox_api_id) || !empty($triplea_btc2fiat_api_id) ? "block" : 'none') ?>;"
              id="localcurrency-update-form-wrapper">
            <hr>
            <br>
            <span><strong>Account e-mail:</strong> <?php echo $fiat_merchant_email; ?></span>
            <br>
            <br>
            <input type="button" class="button-primary" value="Sandbox payments enabled" onclick="" disabled="disabled" style="display:<?php echo($btc2fiat_is_active && $sandbox_payment_mode ? "block" : 'none') ?>;margin-bottom:8px;">
            <input type="button" class="button-primary" value="Live payments enabled" onclick="" disabled="disabled" style="display:<?php echo($btc2fiat_is_active && !$sandbox_payment_mode ? "block" : 'none') ?>;margin-bottom:8px;">
            <input type="button" class="button-primary" value="Switch to Sandbox payments" onclick="triplea_setActiveAccount('btc2fiat', true)" style="display:<?php echo($btc2fiat_is_active && $sandbox_payment_mode ? "none" : "block") ?>;margin-bottom:8px;">
            <input type="button" class="button-primary" value="Switch to Live payments" onclick="triplea_setActiveAccount('btc2fiat', false)" style="display:<?php echo($btc2fiat_is_active && !$sandbox_payment_mode ? "none" : "block") ?>;margin-bottom:8px;">
            <!--<input type="button" class="button-secondary" value="Update my testnet bitcoin wallet" onclick="jQuery('#testnetbitcoin-signup-form-wrapper').show()">-->
         </div>
         <div style="display:<?php echo(empty($triplea_btc2fiat_sandbox_api_id) && empty($old_btc2fiat_api_id) ? "block" : "none") ?>;" id="localcurrency-signup-form-wrapper">
            <hr>
            <div class="triplea_step" id="receive-localcurrency-form">
               <h3>
                  <small>
                     1. Provide information for your new TripleA account
                  </small>
               </h3>
               <!--<p>
                  Master public key of your Testnet bitcoin wallet:
                  <span class="woocommerce-help-tip" data-tip="Your public key allows us to generate unique payment addresses that can receive funds. It does not allow spending, no risk involved."></span>
                  <br>
                  <input type="text"
                         id="testnetbitcoin-master-public-key"
                         name="testnetbitcoin-master-public-key"
                         value=""
                         placeholder="master public key"
                         style="width: 330px;">
                  <span>Please provide a valid master public key (starts with 'tpub') for your Testnet bitcoin wallet.</span>
                  <span style="color: darkred;display:none;"
                        class="triplea-error-msg"
                        id="testnetbitcoin-wrong-pubkey-format"><br>The provided master public key does not have the right format.</span>
               </p>-->
               <p>
                  Provide an email address:
                  <span class="woocommerce-help-tip"
                        data-tip="Valid email address needed."></span>
                  <br>
                  <input type="text"
                         id="localcurrency-notif-email"
                         name="localcurrency-notif-email"
                         value=""
                         placeholder="email address"
                         style="width:330px">
                  <!--<span style="color:darkred;display:none;"
                        class="triplea-error-msg pubkey-wrong-email"
                        id="localcurrency-error-pubkey-wrong-email">
                     This master public key has been associated with another email address. <a href="mailto:support@triple-a.io">Contact us at support@triple-a.io</a> if you need assistance.-->
                  </span>
                  <br>
                  <small style="color: darkred;display:none;"
                         class="triplea-error-msg wrong-notif-email"
                         id="localcurrency-wrong-notif-email">
                     The provided email address seems wrong.
                  </small>
               </p>
               <p>
                  Your full name:
                  <span class="woocommerce-help-tip"
                        data-tip="We will follow up for KYC verification once your account is created."></span>
                  <br>
                  <input type="text"
                         id="localcurrency-fullname"
                         name="localcurrency-fullname"
                         value=""
                         placeholder="full name"
                         style="width:330px">
                  </span>
               </p>
               <input type="button"
                      class="button-primary"
                      value="Proceed"
                      onclick="triplea_localcurrency_emailvalidationstep()">
               <br>
               <br>
            </div>
            <div class="triplea_step"
                 id="receive-localcurrency-validate-email"
                 style="opacity: 0.5;">
               <hr>
               <h3>
                  <small>
                     2. Validate email
                  </small>
               </h3>
               <p>
                  Sending OTP (one-time password) to your email address...
                  <strong id="localcurrency-email-sent" style="display:none;">Email
                                                                               sent!</strong>
               </p>
               <small style="color:darkred;display:none;"
                      class="triplea-error-msg error-otp-request"
                      id="localcurrency-error-otp-request">
                  Something went wrong while requesting OTP code. Try again, or
                  contact
                  us at support@triple-a.io so that we can assist you.
               </small>
               <p id="localcurrency-enter-otp" style="opacity: 0.5;">
                  Enter the One-Time Password code:
                  <input type="text"
                         id="localcurrency-otp-value"
                         name="localcurrency-otp-value"
                         value=""
                         placeholder="One-Time Password"
                         style="width:150px">
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-provide-otp"
                         id="localcurrency-error-provide-otp">
                     Please provide the OTP code that was sent to you by email.
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-otp-wrong"
                         id="localcurrency-error-otp-wrong">
                     Wrong OTP provided.
                  </small>
                  <br>
                  <br>
                  <input type="button"
                         id="localcurrency-otp-submit"
                         class="button-primary"
                         value="Activate local currency account"
                         onclick="triplea_localcurrency_validateEmailOtp()"
                         style="opacity: 0.5;">
                  <small id="localcurrency-otp-check-loading"
                         class="triplea-error-msg otp-check-loading"
                         style="display:none;">
                     Loading...
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg account-creation-error"
                         id="localcurrency-account-creation-error">
                     The provided OTP was correct. However, something went wrong
                     during
                     the creation of your local currency settlement account. Please inform us
                     at
                     support@triple-a.io so that we may assist you promptly.
                  </small>
               </p>
            </div>
         </div>
         <br>
         <hr>
      </div>
      

      <input type="radio"
             name="receive-choice"
             value="bitcoin"
         <?php echo($btc2btc_is_active ? "checked" : '') ?>
             style="margin:10px;"><strong>expert</strong> - receive bitcoin<br>
      <div class="receive-choice-wrapper"
           style="margin-left: 40px;display:<?php echo($btc2btc_is_active ? "block" : 'none') ?>;"
           id="bitcoin-form-wrapper">
         <div style="display:<?php echo(!empty($old_btc2btc_api_id) && empty($btc_merchant_key) && empty($btc_sandbox_merchant_key) ? "block" : "none") ?>;" id="bitcoin-version-upgrade-form-wrapper">
            <hr>
            <div class="triplea_step" id="upgrade-bitcoin-form">
               <h3>
                  <small>
                     Upgrade your bitcoin account
                  </small>
               </h3>
               <p>
                  Due to technical improvements and added features, your account needs to be upgraded (a simple click!).
                  <br>
                  After upgrading, your account will be linked to your TripleA dashboard.
                  <br>
                  Login credentials will be shared by e-mail.
               </p>
               <!--<p>
                  Existing API ID: <?php /*echo $old_btc2btc_api_id; */?>
                  <span class="woocommerce-help-tip" data-tip="This is your TripleA account identifier"></span>
               </p>-->

               <input type="button"
                      class="button-primary"
                      value="Upgrade"
                      onclick="triplea_bitcoin_upgrade_emailvalidationstep()">
               <br>
               <br>
            </div>
            <div class="triplea_step"
                 id="upgrade-bitcoin-validate-email"
                 style="opacity: 0.5;">
               <hr>
               <h3>
                  <small>
                     Validate email
                  </small>
               </h3>
               <p>
                  Sending OTP (one-time password) to your email address...
                  <strong id="bitcoin-upgrade-email-sent" style="display:none;">
                     Email sent!
                  </strong>
               </p>
               <small style="color:darkred;display:none;"
                      class="triplea-error-msg error-otp-request"
                      id="bitcoin-upgrade-error-otp-request">
                  Something went wrong while requesting OTP code. Try again, or
                  contact
                  us at support@triple-a.io so that we can assist you.
               </small>
               <p id="bitcoin-upgrade-enter-otp" style="opacity: 0.5;">
                  Enter the One-Time Password code:
                  <input type="text"
                         id="bitcoin-upgrade-otp-value"
                         name="bitcoin-upgrade-otp-value"
                         value=""
                         placeholder="One-Time Password"
                         style="width:150px">
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-provide-otp"
                         id="bitcoin-upgrade-error-provide-otp">
                     Please provide the OTP code that was sent to you by email.
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-otp-wrong"
                         id="bitcoin-upgrade-error-otp-wrong">
                     Wrong OTP provided.
                  </small>
                  <br>
                  <br>
                  <input type="button"
                         id="bitcoin-upgrade-otp-submit"
                         class="button-primary"
                         value="Activate bitcoin account"
                         onclick="triplea_bitcoin_upgrade_validateEmailOtp()"
                         style="opacity: 0.5;">
                  <small id="bitcoin-upgrade-otp-check-loading"
                         class="triplea-error-msg otp-check-loading"
                         style="display:none;">
                     Loading...
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg account-creation-error"
                         id="bitcoin-upgrade-account-creation-error">
                     The provided OTP was correct. However, something went wrong
                     during
                     the creation of your live bitcoin account. Please inform us
                     at
                     support@triple-a.io so that we may assist you promptly.
                  </small>
               </p>
            </div>
         </div>
         <div class="receive-choice-update-wrapper"
              style="display:<?php echo(!empty($triplea_btc2btc_api_id) || !empty($triplea_btc2btc_sandbox_api_id) ? "block" : 'none') ?>;"
              id="bitcoin-update-form-wrapper">
            <hr>
            <br>
            <span style="display:<?php echo($btc2btc_is_active ? "block" : 'none') ?>;">
               <strong>Email:</strong> <?php if ($sandbox_payment_mode) {echo $btc_sandbox_merchant_email;} else {echo $btc_merchant_email;} ?>.
               
<!--               --><?php //if((!$sandbox_payment_mode && !empty($btc_pubkey)) || ($sandbox_payment_mode && !empty($btc_sandbox_pubkey))): ?>
<!--               <br><strong>Public key:</strong>-->
<!--                  --><?php //if ($sandbox_payment_mode) {echo substr($btc_sandbox_pubkey, 0, 8);} else {echo substr($btc_pubkey, 0, 8);} ?><!--...-->
<!--               --><?php //endif; ?>
               <br>
               <br>
            </span>
            <input type="button" class="button-primary" value="Live payments enabled" disabled="disabled" style="display:<?php echo(!empty($triplea_btc2btc_api_id) && $btc2btc_is_active && !$sandbox_payment_mode ? "block" : "none") ?>;margin-bottom:8px;">
            <input type="button" class="button-primary" value="Sandbox payments enabled" disabled="disabled" style="display:<?php echo(!empty($triplea_btc2btc_sandbox_api_id) && $btc2btc_is_active && $sandbox_payment_mode ? "block" : "none") ?>;margin-bottom:8px;">
            
            <input type="button" class="button-primary" value="Switch to Live payments" onclick="triplea_setActiveAccount('btc2btc', false)" style="display:<?php echo(!empty($triplea_btc2btc_api_id) && (!$btc2btc_is_active || $sandbox_payment_mode) ? "block" : "none") ?>;margin-bottom:15px;">
            <input type="button" class="button-primary" value="Switch to Sandbox payments" onclick="triplea_setActiveAccount('btc2btc', true)" style="display:<?php echo(!empty($triplea_btc2btc_sandbox_api_id) && (!$btc2btc_is_active || !$sandbox_payment_mode) ? "block" : "none") ?>;margin-bottom:15px;">
            <input type="button" class="button-secondary" value="Update my bitcoin wallet" onclick="jQuery('#bitcoin-signup-form-wrapper').show()">
            <br>
         </div>
         <div style="display:<?php echo(empty($triplea_btc2btc_api_id) && empty($triplea_btc2btc_sandbox_api_id) && empty($old_btc2btc_api_id) ? "block" : "none") ?>;" id="bitcoin-signup-form-wrapper">
            <hr>
            <div class="triplea_step" id="receive-bitcoin-form">
               <h3>
                  <small>
                     1. Provide bitcoin wallet information
                  </small>
               </h3>
               <p>
                  Master public key of your bitcoin wallet:
                  <span class="woocommerce-help-tip"
                        data-tip="Your master public key can only be used to generate payment addresses and view balances. We recommend creating a new, empty bitcoin wallet for use with this plugin."></span>
                  <br>
                  <input type="text"
                         id="bitcoin-master-public-key"
                         name="bitcoin-master-public-key"
                         value=""
                         placeholder="master public key"
                         style="width: 330px;">
                  <br>
                  <span>
                     Please provide a valid master public key for your bitcoin wallet (starting with 'xpub', 'ypub' or 'zpub').
                     <br>You can also use a testnet bitcoin wallet for sandbox testing (public key starting with 'tpub').
                     <br>Payment addresses are generated using the standard BIP-44 derivation path (the same one used by the Electrum wallet).
                  </span>
                  <span style="color: darkred;display:none;"
                        class="triplea-error-msg"
                        id="bitcoin-wrong-pubkey-format"><br>The provided master public key does not have the right format.</span>
               </p>
               <p>
                  Provide an email address:
                  <span class="woocommerce-help-tip"
                        data-tip="Valid email address needed for dashboard account linking and notifications."></span>
                  <br>
                  <input type="text"
                         id="bitcoin-notif-email"
                         name="bitcoin-notif-email"
                         value=""
                         placeholder="email address"
                         style="width:330px">
                  <span style="color:darkred;display:none;"
                        class="triplea-error-msg pubkey-wrong-email"
                        id="bitcoin-error-pubkey-wrong-email">
                     This master public key has been associated with another email address. <a href="mailto:support@triple-a.io">Contact us at support@triple-a.io</a> if you need assistance.
                  </span>
                  <span style="color:darkred;display:none;"
                        class="triplea-error-msg pubkey-wrong-email"
                        id="bitcoin-error-pubkey-exists">
                     This master public key has already been used on another website. Please create a new bitcoin wallet for this website. <a href="mailto:support@triple-a.io">Contact us at support@triple-a.io</a> if you need assistance.
                  </span>
                  <br>
                  <small style="color: darkred;display:none;"
                         class="triplea-error-msg wrong-notif-email"
                         id="bitcoin-wrong-notif-email">
                     The provided email address seems wrong.
                  </small>
               </p>
               <input type="button"
                      class="button-primary"
                      value="Proceed"
                      onclick="triplea_bitcoin_emailvalidationstep()">
               <br>
               <br>
            </div>
            <div class="triplea_step"
                 id="receive-bitcoin-validate-email"
                 style="opacity: 0.5;">
               <hr>
               <h3>
                  <small>
                     2. Validate email
                  </small>
               </h3>
               <p>
                  Sending OTP (one-time password) to your email address...
                  <strong id="bitcoin-email-sent" style="display:none;">Email sent!</strong>
               </p>
               <small style="color:darkred;display:none;"
                      class="triplea-error-msg error-otp-request"
                      id="bitcoin-error-otp-request">
                  Something went wrong while requesting OTP code. Try again, or
                  contact us at support@triple-a.io so that we can assist you.
               </small>
               <p id="bitcoin-enter-otp" style="opacity: 0.5;">
                  Enter the One-Time Password code:
                  <input type="text"
                         id="bitcoin-otp-value"
                         name="bitcoin-otp-value"
                         value=""
                         placeholder="One-Time Password"
                         style="width:150px">
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-provide-otp"
                         id="bitcoin-error-provide-otp">
                     Please provide the OTP code that was sent to you by email.
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-otp-wrong"
                         id="bitcoin-error-otp-wrong">
                     Wrong OTP provided.
                  </small>
                  <br>
                  <br>
                  <input type="button"
                         id="bitcoin-otp-submit"
                         class="button-primary"
                         value="Activate bitcoin account"
                         onclick="triplea_bitcoin_validateEmailOtp()"
                         style="opacity: 0.5;">
                  <small id="bitcoin-otp-check-loading"
                         class="triplea-error-msg otp-check-loading"
                         style="display:none;">
                     Loading...
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg account-creation-error"
                         id="bitcoin-account-creation-error">
                     The provided OTP was correct. However, something went wrong
                     during the creation of your testnet bitcoin account. Please inform us
                     at support@triple-a.io so that we may assist you promptly.
                  </small>
               </p>
            </div>
         </div>

         <br>
         <hr>
      </div>

      <br>
      <br>
   </div>

   <script src="https://unpkg.com/libphonenumber-js@1.7.56/bundle/libphonenumber-max.js"></script>
   <script>
     (function ($) {
       $(document).ready(function () {
         function updateReceiveChoiceOptions() {
           let selectedValue = $('input[name="receive-choice"]:checked').val();
           $('.receive-choice-wrapper').hide();
           if (selectedValue === 'localcurrency')
           {
             $('#localcurrency-form-wrapper').show();
           }
           else if (selectedValue === 'testnetbitcoin')
           {
             $('#testnetbitcoin-form-wrapper').show();
           }
           else if (selectedValue === 'bitcoin')
           {
             $('#bitcoin-form-wrapper').show();
           }
         }

         updateReceiveChoiceOptions();
         
         // detect "receive .." choice, set the correct radiobutton selection
         $('[name="receive-choice"]').change(function () {
           updateReceiveChoiceOptions();
         });

         let sandboxPaymentModeNode   = document.getElementById(settingsPrefix + '_' + 'triplea_sandbox_payment_mode');
         let sandbox = sandboxPaymentModeNode.value;
         if ( !!(sandbox) !== !!(<?php echo (!isset($sandbox_payment_mode) || empty($sandbox_payment_mode) ? "false" : "'true'"); ?>) )
         {
           window.location.reload(true);
         }
         
       });
     })(jQuery);
     

     /**
      * Shared helper functions
      */
     function triplea_getemailotp(email, callback, errorCallback)
     {
       console.log('Requesting OTP for ' + email);
       callback();
     }
   </script>
   <script>
     function gotoStep1()
     {
       triplea_helper_displayNode('step-1');
     }
   </script>
   <script>
     const return_url     = '<?php echo $return_url; ?>';
     const local_currency = '<?php echo $local_currency; ?>';
     // noinspection JSAnnotator
     const site_info      = <?php echo json_encode($info_data); ?>;
     const settingsPrefix = 'woocommerce_triplea_payment_gateway';
     let otpRequestEmail, otpRequestNotifEmail;
     let merchantAccountRequestResult;
     
     // Optional, available if upgrade needed from pre-v1.5.0:
     const old_btc2btc_api_id = <?php echo (!empty($old_btc2btc_api_id) ? "'$old_btc2btc_api_id'" : 'null'); ?>;
     const old_btc2btc_sandbox_api_id = <?php echo (!empty($old_btc2btc_sandbox_api_id) ? "'$old_btc2btc_sandbox_api_id'" : 'null'); ?>;
     const old_btc2fiat_api_id = <?php echo (!empty($old_btc2fiat_api_id) ? "'$old_btc2fiat_api_id'" : 'null'); ?>;
     const old_active_api_id = <?php echo (!empty($old_active_api_id) ? "'$old_active_api_id'" : 'null'); ?>;
     const old_btc2fiat_is_active = <?php echo (!empty($old_btc2fiat_is_active) ? "'$old_btc2fiat_is_active'" : 'null'); ?>;
     const old_btc2btc_is_active = <?php echo (!empty($old_btc2btc_is_active) ? "'$old_btc2btc_is_active'" : 'null'); ?>;
     
     /**
      *
      *  OLD BTC-to-BTC External wallet code
      *
      */

     function triplea_hideAllSteps()
     {
       let steps = document.getElementsByClassName('triplea_step');
       for (let i = 0; steps && i < steps.length; i++)
       {
         steps[i].style.display = 'none';
       }
     }

     function triplea_hideAllErrors()
     {
       jQuery('.triplea-error-msg').hide();
     }


     /**
      *
      *  BTC-to-Fiat TripleA wallet code
      *
      */

     function triplea_fiat_createMerchantAccount()
     {
       let merchantEmail, merchantPhone;

       // Get dashboard email.
       let merchantEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_email');
       merchantEmail         = merchantEmailNode.value;

       let merchantPhoneNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_phone');
       merchantPhone         = merchantPhoneNode.value;

       // Dashboard email validation.
       if (!merchantEmail
         || merchantEmail.indexOf('@') < 1
         || merchantEmail.lastIndexOf('.') < merchantEmail.indexOf('@'))
       {
         console.warn('TripleA Warning: Incorrect TripleA wallet email. Cannot proceed.');
         triplea_helper_displayNode(step + '-input-error');
         return;
       }
       triplea_hideAllErrors();


       // Remember for which email the OTP was last requested.
       otpRequestEmail      = merchantEmail;
       otpRequestNotifEmail = merchantEmail; // TODO need different Notification emails stored, for bitcoin or localcurrency !

       // Ask for OTP to be sent to email address.

       const url      = `https://moneyoverip.io/api/v1/merchant`;
       const callback = triplea_fiat_createMerchantAccountCallback;
       const method   = "POST";
       const data     = {
         name: site_info.name,
         email: merchantEmail,
         phone: merchantPhone || undefined,
         local_currency: 'USD',
         source: 'woocommerce',
         master_pubkey: undefined,
         direct: undefined,
         pid: undefined,
         plugin: {
           domain: window.location.hostname,
           plugin_ver: site_info.plugin_v,
           platform_ver: site_info.wp_v,
           platform: site_info.type,
           //// extra
           // url: site_info.url,
           // debug_log: site_info.debug_log,
           // lang: site_info.lang,
           // admin_email: site_info.admin
         }
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data));
     }

     function triplea_fiat_createMerchantAccountCallbackError(err)
     {
       console.warn('TripleA Error:  Problem requesting OTP for email validation.', err);
       // TODO display error in form
     }

     function triplea_fiat_createMerchantAccountCallback(result)
     {
       merchantAccountRequestResult = result;

       triplea_helper_displayNode("cash-email-sent");
       triplea_helper_displayNode("cash-enter-otp");
     }

     function triplea_fiat_validateEmailOtp()
     {

       let otpNode = document.getElementById('cash-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('cash-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('cash-otp-check-loading');

       const url      = `https://moneyoverip.io/api/v1/merchant/` + merchantAccountRequestResult.merchant_key + `/verify`;
       const callback = triplea_fiat_validateEmailOtpCallback;
       const method   = "PUT";
       const data     = {
         merchant_pin: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, triplea_fiat_validateEmailOtpCallbackError);
     }

     function triplea_fiat_validateEmailOtpCallbackError(err)
     {
       triplea_hideAllErrors();
       triplea_helper_displayNode('cash-error-otp-wrong');
     }

     /**
      * Account has been created.
      * OTP has been validated.
      * Time to save the account(s) now.
      */
     function triplea_fiat_validateEmailOtpCallback(result)
     {
       triplea_hideAllErrors();

       /// data prep..
       // triplea_addPub(data, triplea_fiat_addPubCallback);

       if (!merchantAccountRequestResult
         || !merchantAccountRequestResult.client_id
         || !merchantAccountRequestResult.client_secret
         || !merchantAccountRequestResult.merchant_key
         || !merchantAccountRequestResult.accounts
         || merchantAccountRequestResult.accounts.length === 0)
       {
         console.warn('TripleA Warning: missing merchant account data.');
         triplea_helper_displayNode("cash-account-creation-error");
         return;
       }

       // let accountEmailNode       = document.getElementById(settingsPrefix + '_' + 'triplea_dashboard_email');
       // accountEmailNode.value     = otpRequestEmail;
       // let hiddenNotifEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
       // hiddenNotifEmailNode.value = otpRequestNotifEmail;

       let hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantEmail   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_email');
       hiddenNodeMerchantEmail.value = merchantAccountRequestResult.email || '-missing email-';

       let hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '-missing phone number-';

       let hiddenNodeMerchantLocalCurrency   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_local_currency');
       hiddenNodeMerchantLocalCurrency.value = merchantAccountRequestResult.local_currency || '-missing local currency-';

       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];

         if (acc.crypto_currency === 'BTC' && !acc.sandbox && acc.type === 'triplea')
         {
           // TODO rename this new variable where needed everywhere in the code
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
           apiIdNode.value = acc.api_id;
         }
         else if (acc.crypto_currency === 'testBTC' && acc.sandbox  && acc.type === 'triplea')
         {
           // TODO create this new variable where needed all throughout the code
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }


       }

       // Sets active account then submits form
       let sandbox = false; // by default start in LIVE mode
       triplea_setActiveAccount('btc2fiat', sandbox);
     }


   </script>
   <script>

     /**
      *
      *  Testnet BTC-to-BTC account code
      *
      */

     /**
      *  Validate pubkey and email input fields.
      *  Then request merchant account creation (pending OTP validation).
      *  Display the OTP validation step.
      */
     function triplea_testnetbitcoin_emailvalidationstep()
     {

       // Reset errors, before validation
       jQuery('.triplea-error-msg').hide();

       // Do validation pubkey. Update wallet, if API ID set. Else request API ID for pubkey.
       let pubkey, apiId, apiIdNode;
       let pubkeyNode = document.getElementById('testnetbitcoin-master-public-key');
       pubkey         = pubkeyNode.value ? pubkeyNode.value : '';
       if (!pubkey)
       {
         console.warn('TripleA Warning: No public key provided. Cannot proceed.');
         jQuery('#testnetbitcoin-wrong-pubkey-format').show();
         return;
       }
       if (pubkey.indexOf('pub') !== 1 || !(pubkey.indexOf('tpub') === 0))
       {
         console.warn('TripleA Warning: Incorrect public key format. Cannot proceed.');
         jQuery('#testnetbitcoin-wrong-pubkey-format').show();
         return;
       }
       window.testbitcoin_pubkey = pubkey;

       // Do email validation.
       let testnetBitcoinEmail;
       let testnetBitcoinEmailNode = document.getElementById('testnetbitcoin-notif-email');
       testnetBitcoinEmail         = testnetBitcoinEmailNode.value;
       if (!testnetBitcoinEmail ||
         (testnetBitcoinEmail.indexOf('@') < 1 || testnetBitcoinEmail.lastIndexOf('.') < testnetBitcoinEmail.indexOf('@')))
       {
         console.warn('TripleA Warning: Incorrect notification email. Cannot proceed.');
         jQuery('#testnetbitcoin-wrong-notif-email').show();
         return;
       }
       // TODO ? the below code should be done upon successful account creation, not before..
       let hiddenTestnetBitcoinEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_email');
       hiddenTestnetBitcoinEmailNode.value = testnetBitcoinEmail;

       triplea_testnetbitcoin_createMerchantAccount();
     }

     /**
      *  Create the merchant account. Receive a merchant key in response,
      *  to be used when validating the email via OTP.
      */
     function triplea_testnetbitcoin_createMerchantAccount()
     {
       let merchantEmail, merchantPhone;

       // Get dashboard email.
       let merchantEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_email');
       merchantEmail         = merchantEmailNode.value;

       if (!window.testbitcoin_pubkey || window.testbitcoin_pubkey === '') {
         console.warn('TripleA Warning: Incorrect email. Cannot proceed.');
         return;
       }
       triplea_hideAllErrors();

       // Remember for which email the OTP was last requested.
       otpRequestEmail = merchantEmail;

       // Ask for OTP to be sent to email address.

       const url      = `https://moneyoverip.io/api/v1/merchant`;
       const callback = triplea_testnetbitcoin_createMerchantAccountCallback;
       const errorCallback = triplea_testnetbitcoin_createMerchantAccountCallbackError;
       const method   = "POST";
       const data     = {
         name: site_info.name,
         email: merchantEmail,
         phone: merchantPhone || undefined,
         local_currency: local_currency,
         source: 'woocommerce',
         master_pubkey: window.testbitcoin_pubkey,
         direct: undefined,
         pid: undefined,
         plugin: {
           domain: window.location.hostname,
           plugin_ver: site_info.plugin_v,
           platform_ver: site_info.wp_v,
           platform: site_info.type,
           php_ver: site_info.php_v,
           //// extra
           url: site_info.url,
           debug_log: site_info.debug_log,
           lang: site_info.lang,
           admin_email: site_info.admin
         }
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, errorCallback);

       jQuery('#receive-testnetbitcoin-validate-email').fadeTo(0, 1);
     }

     /**
      *  Something went wrong when creating the merchant account.
      *  Possibly the pubkey was already associated with another account.
      */
     function triplea_testnetbitcoin_createMerchantAccountCallbackError(err)
     {
       jQuery('#receive-testnetbitcoin-validate-email').fadeTo(0, 0.5);
       console.error('TripleA Error: Error with merchant account creation.', err.response);
       jsonErr = JSON.parse(err.response);
       if (jsonErr && jsonErr.message === 'pubkey_exists') {
         jQuery('#testnetbitcoin-error-pubkey-exists').show();
       }
       else
       {
         jQuery('#testnetbitcoin-error-pubkey-wrong-email').show();
       }
     }

     /**
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_testnetbitcoin_createMerchantAccountCallback(result)
     {
       jQuery('#receive-testnetbitcoin-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.log('Prepared account creation. Requested OTP code. Pending OTP validation..');
       jQuery('#testnetbitcoin-email-sent').fadeTo(0, 1);
       jQuery('#testnetbitcoin-enter-otp').fadeTo(1, 1);
       jQuery('#testnetbitcoin-otp-submit').fadeTo(1, 1);
       merchantAccountRequestResult = result;
     }

     /**
      *  Submit OTP and merchant key for validation.
      */
     function triplea_testnetbitcoin_validateEmailOtp()
     {
       let otpNode = document.getElementById('testnetbitcoin-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('testnetbitcoin-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('testnetbitcoin-otp-check-loading');

       const url      = `https://moneyoverip.io/api/v1/merchant/` + merchantAccountRequestResult.merchant_key + `/verify`;
       const callback = triplea_testnetbitcoin_validateEmailOtpCallback;
       const method   = "PUT";
       const data     = {
         merchant_pin: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, triplea_testnetbitcoin_validateEmailOtpCallbackError);
     }

     /**
      *  OTP validation failed.
      */
     function triplea_testnetbitcoin_validateEmailOtpCallbackError(err)
     {
       triplea_hideAllErrors();
       triplea_helper_displayNode('testnetbitcoin-error-otp-wrong');
     }

     /**
      *  OTP has been validated.
      *  Account has been created.
      *  Proceed with saving the account information
      *  and make this account the active one for bitcoin payments.
      */
     function triplea_testnetbitcoin_validateEmailOtpCallback(result)
     {
       triplea_hideAllErrors();

       if (!merchantAccountRequestResult
         || !merchantAccountRequestResult.client_id
         || !merchantAccountRequestResult.client_secret
         || !merchantAccountRequestResult.merchant_key
         || !merchantAccountRequestResult.accounts
         || merchantAccountRequestResult.accounts.length === 0)
       {
         console.warn('TripleA Warning: missing merchant account data.');
         triplea_helper_displayNode("testnetbitcoin-account-creation-error");
         return;
       }

       let hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '';

       let hiddenNodePubkey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_pubkey');
       hiddenNodePubkey.value = window.testbitcoin_pubkey || '';

       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];

         // if (acc.crypto_currency === 'BTC' && !acc.sandbox)
         // {
         //   // TODO rename this new variable where needed everywhere in the code
         //   let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
         //   apiIdNode.value = acc.api_id;
         // }
         if (acc.crypto_currency === 'testBTC' && acc.sandbox  && acc.type === 'self')
         {
           // TODO create this new variable where needed all throughout the code
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }
       }

       // Sets active account then submits form
       let sandbox = true;
       triplea_setActiveAccount('btc2btc', sandbox);
     }
     
   </script>
   <script>

     /**
      *
      *  (Mainnet/live) BTC-to-BTC account code
      *
      */

     /**
      *  Validate pubkey and email input fields.
      *  Then request merchant account creation (pending OTP validation).
      *  Display the OTP validation step.
      */
     function triplea_bitcoin_emailvalidationstep()
     {

       // Reset errors, before validation
       jQuery('.triplea-error-msg').hide();

       // Do validation pubkey. Update wallet, if API ID set. Else request API ID for pubkey.
       let pubkey, apiId, apiIdNode;
       let pubkeyNode = document.getElementById('bitcoin-master-public-key');
       pubkey         = pubkeyNode.value ? pubkeyNode.value : '';
       if (!pubkey)
       {
         console.warn('TripleA Warning: No public key provided. Cannot proceed.');
         jQuery('#bitcoin-wrong-pubkey-format').show();
         return;
       }
       if (pubkey.indexOf('pub') !== 1 || (pubkey.indexOf('xpub') !== 0 && pubkey.indexOf('ypub') !== 0 && pubkey.indexOf('zpub') !== 0 && pubkey.indexOf('tpub') !== 0))
       {
         console.warn('TripleA Warning: Incorrect public key format. Cannot proceed.');
         jQuery('#bitcoin-wrong-pubkey-format').show();
         return;
       }
       if (pubkey.indexOf('tpub') === 0) {
         window.bitcoin_pubkey = null;
         window.testbitcoin_pubkey = pubkey;
       }
       else {
         window.bitcoin_pubkey = pubkey;
         window.testbitcoin_pubkey = null;
       }

       // Do email validation.
       let bitcoinEmail;
       let bitcoinEmailNode = document.getElementById('bitcoin-notif-email');
       bitcoinEmail         = bitcoinEmailNode.value;
       if (!bitcoinEmail ||
         (bitcoinEmail.indexOf('@') < 1 || bitcoinEmail.lastIndexOf('.') < bitcoinEmail.indexOf('@')))
       {
         console.warn('TripleA Warning: Incorrect notification email. Cannot proceed.');
         jQuery('#bitcoin-wrong-notif-email').show();
         return;
       }
       // TODO ? the below code should be done upon successful account creation, not before..
       let hiddenBitcoinEmailNode;
       if (window.testbitcoin_pubkey)
         hiddenBitcoinEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_email');
       else
         hiddenBitcoinEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_email');
       hiddenBitcoinEmailNode.value = bitcoinEmail;

       triplea_bitcoin_createMerchantAccount();
     }

     /**
      *  Create the merchant account. Receive a merchant key in response,
      *  to be used when validating the email via OTP.
      */
     function triplea_bitcoin_createMerchantAccount()
     {
       let merchantEmail, merchantPhone;

       // Get dashboard email.
       let merchantEmailNode
       if (window.testbitcoin_pubkey)
         merchantEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_email');
       else
         merchantEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_email');
       merchantEmail         = merchantEmailNode.value;

       if ((!window.bitcoin_pubkey || window.bitcoin_pubkey === '') && (!window.testbitcoin_pubkey || window.testbitcoin_pubkey === '')) {
         console.warn('TripleA Warning: Incorrect master public key. Cannot proceed.');
         return;
       }
       triplea_hideAllErrors();

       // Remember for which email the OTP was last requested.
       otpRequestEmail = merchantEmail;

       // Ask for OTP to be sent to email address.

       const url      = `https://moneyoverip.io/api/v1/merchant`;
       const callback = triplea_bitcoin_createMerchantAccountCallback;
       const errorCallback = triplea_bitcoin_createMerchantAccountCallbackError;
       const method   = "POST";
       const data     = {
         name: site_info.name,
         email: merchantEmail,
         phone: merchantPhone || undefined,
         local_currency: local_currency,
         source: 'woocommerce',
         master_pubkey: window.bitcoin_pubkey || window.testbitcoin_pubkey,
         direct: undefined,
         pid: undefined,
         plugin: {
           domain: window.location.hostname,
           plugin_ver: site_info.plugin_v,
           platform_ver: site_info.wp_v,
           platform: site_info.type,
           php_ver: site_info.php_v,
           //// extra
           url: site_info.url,
           debug_log: site_info.debug_log,
           lang: site_info.lang,
           admin_email: site_info.admin
         }
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, errorCallback);

       jQuery('#receive-bitcoin-validate-email').fadeTo(0, 1);
     }

     /**
      *  Something went wrong when creating the merchant account.
      *  Possibly the pubkey was already associated with another account.
      */
     function triplea_bitcoin_createMerchantAccountCallbackError(err)
     {
       jQuery('#receive-bitcoin-validate-email').fadeTo(0, 0.5);
       console.error('TripleA Error: Error with merchant account creation.', err.response);
       let jsonErr = JSON.parse(err.response);
       if (jsonErr && jsonErr.message === 'pubkey_exists') {
         jQuery('#bitcoin-error-pubkey-exists').show();
       }
       else
       {
         jQuery('#bitcoin-error-pubkey-wrong-email').show();
       }
     }

     /**
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_bitcoin_createMerchantAccountCallback(result)
     {
       jQuery('#receive-bitcoin-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.log('Prepared account creation. Requested OTP code. Pending OTP validation..');
       jQuery('#bitcoin-email-sent').fadeTo(0, 1);
       jQuery('#bitcoin-enter-otp').fadeTo(1, 1);
       jQuery('#bitcoin-otp-submit').fadeTo(1, 1);
       merchantAccountRequestResult = result;
     }

     /**
      *  Submit OTP and merchant key for validation.
      */
     function triplea_bitcoin_validateEmailOtp()
     {
       let otpNode = document.getElementById('bitcoin-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('bitcoin-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('bitcoin-otp-check-loading');

       const url      = `https://moneyoverip.io/api/v1/merchant/` + merchantAccountRequestResult.merchant_key + `/verify`;
       const callback = triplea_bitcoin_validateEmailOtpCallback;
       const method   = "PUT";
       const data     = {
         merchant_pin: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, triplea_bitcoin_validateEmailOtpCallbackError);
     }

     /**
      *  OTP validation failed.
      */
     function triplea_bitcoin_validateEmailOtpCallbackError(err)
     {
       triplea_hideAllErrors();
       triplea_helper_displayNode('bitcoin-error-otp-wrong');
     }

     /**
      *  OTP has been validated.
      *  Account has been created.
      *  Proceed with saving the account information
      *  and make this account the active one for bitcoin payments.
      */
     function triplea_bitcoin_validateEmailOtpCallback(result)
     {
       triplea_hideAllErrors();
       
       if (!merchantAccountRequestResult
         || !merchantAccountRequestResult.client_id
         || !merchantAccountRequestResult.client_secret
         || !merchantAccountRequestResult.merchant_key
         || !merchantAccountRequestResult.accounts
         || merchantAccountRequestResult.accounts.length === 0)
       {
         console.warn('TripleA Warning: missing merchant account data.');
         triplea_helper_displayNode("bitcoin-account-creation-error");
         return;
       }

       let hiddenNodeMerchantKey;
       if (window.testbitcoin_pubkey)
         hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_key');
       else
         hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId;
       if (window.testbitcoin_pubkey)
         hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_id');
       else
         hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret;
       if (window.testbitcoin_pubkey)
         hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_secret');
       else
         hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName;
       if (window.testbitcoin_pubkey)
         hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_name');
       else
         hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantPhone;
       if (window.testbitcoin_pubkey)
         hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_phone');
       else
         hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '';

       let hiddenNodePubkey;
       if (window.testbitcoin_pubkey)
         hiddenNodePubkey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_pubkey');
       else
         hiddenNodePubkey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_pubkey');
       hiddenNodePubkey.value = window.bitcoin_pubkey || window.testbitcoin_pubkey;

       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];

         if (window.bitcoin_pubkey && acc.crypto_currency === 'BTC' && !acc.sandbox  && acc.type === 'self')
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
           apiIdNode.value = acc.api_id;
         }
         else if (window.testbitcoin_pubkey && acc.crypto_currency === 'testBTC' && acc.sandbox)
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }
       }

       // Sets active account then submits form
       let sandbox = !!window.testbitcoin_pubkey;
       triplea_setActiveAccount('btc2btc', sandbox);
     }

   </script>
   <script>

     /**
      *
      *  BTC-to-FIAT (local currency settlement) account code
      *  1. starts by creating an account,
      *  2. stores both live and sandbox credentials,
      *  3. activates the sandbox mode by default, first.
      *  4. user can then switch to Live instantly.
      *
      */

     /**
      *  Validate pubkey and email input fields.
      *  Then request merchant account creation (pending OTP validation).
      *  Display the OTP validation step.
      */
     function triplea_localcurrency_emailvalidationstep()
     {

       // Reset errors, before validation
       jQuery('.triplea-error-msg').hide();
       
       window.testbitcoin_pubkey = null; // keep things clean, make sure this is unset
       window.bitcoin_pubkey = null; // keep things clean, make sure this is unset

       // Do email validation.
       let localcurrencyEmail;
       let localcurrencyEmailNode = document.getElementById('localcurrency-notif-email');
       localcurrencyEmail         = localcurrencyEmailNode.value;
       if (!localcurrencyEmail ||
         (localcurrencyEmail.indexOf('@') < 1 || localcurrencyEmail.lastIndexOf('.') < localcurrencyEmail.indexOf('@')))
       {
         console.warn('TripleA Warning: Incorrect email. Cannot proceed.');
         jQuery('#localcurrency-wrong-notif-email').show();
         return;
       }
       // TODO ? the below code should be done upon successful account creation, not before..
       let hiddenLocalcurrencyEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_email');
       hiddenLocalcurrencyEmailNode.value = localcurrencyEmail;

       triplea_localcurrency_createMerchantAccount();
     }

     /**
      *  Create the merchant account for local currency settlement.
      *  Receive a merchant key in response, to be used when validating the email via OTP.
      */
     function triplea_localcurrency_createMerchantAccount()
     {
       let merchantEmail, merchantPhone;

       // Get dashboard email.
       let merchantEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_email');
       merchantEmail         = merchantEmailNode.value;

       // Get full name (if provided).
       let fullNameNode = document.getElementById('localcurrency-fullname');
       fullName         = fullNameNode.value || '';

       triplea_hideAllErrors();

       // Remember for which email the OTP was last requested.
       otpRequestEmail = merchantEmail;

       // Ask for OTP to be sent to email address.

       const url      = `https://moneyoverip.io/api/v1/merchant`;
       const callback = triplea_localcurrency_createMerchantAccountCallback;
       const errorCallback = triplea_localcurrency_createMerchantAccountCallbackError;
       const method   = "POST";
       const data     = {
         name: site_info.name,
         email: merchantEmail,
         phone: merchantPhone || undefined,     // TODO fiat: add phone input field!
         local_currency: local_currency,        // TODO fiat: give choice of preferred local currency for settlement
         source: 'woocommerce',
         master_pubkey: undefined,
         direct: undefined,
         pid: undefined,
         plugin: {
           domain: window.location.hostname,
           plugin_ver: site_info.plugin_v,
           platform_ver: site_info.wp_v,
           platform: site_info.type,
           php_ver: site_info.php_v,
           //// extra
           full_name: fullName || '',
           url: site_info.url,
           debug_log: site_info.debug_log,
           lang: site_info.lang,
           admin_email: site_info.admin
         }
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, errorCallback);

       jQuery('#receive-localcurrency-validate-email').fadeTo(0, 1);
     }

     /**
      *  Something went wrong when creating the merchant account.
      */
     function triplea_localcurrency_createMerchantAccountCallbackError(err)
     {
       jQuery('#receive-localcurrency-validate-email').fadeTo(0, 0.5);
       console.error('TripleA Error: Error with merchant account creation.', err);
       jQuery('#localcurrency-error-pubkey-wrong-email').show();
     }

     /**
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_localcurrency_createMerchantAccountCallback(result)
     {
       jQuery('#receive-localcurrency-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.log('Prepared account creation. Requested OTP code. Pending OTP validation..');
       jQuery('#localcurrency-email-sent').fadeTo(0, 1);
       jQuery('#localcurrency-enter-otp').fadeTo(1, 1);
       jQuery('#localcurrency-otp-submit').fadeTo(1, 1);
       merchantAccountRequestResult = result;
     }

     /**
      *  Submit OTP and merchant key for validation.
      */
     function triplea_localcurrency_validateEmailOtp()
     {
       let otpNode = document.getElementById('localcurrency-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('localcurrency-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('localcurrency-otp-check-loading');

       const url      = `https://moneyoverip.io/api/v1/merchant/` + merchantAccountRequestResult.merchant_key + `/verify`;
       const callback = triplea_localcurrency_validateEmailOtpCallback;
       const method   = "PUT";
       const data     = {
         merchant_pin: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, triplea_localcurrency_validateEmailOtpCallbackError);
     }

     /**
      *  OTP validation failed.
      */
     function triplea_localcurrency_validateEmailOtpCallbackError(err)
     {
       triplea_hideAllErrors();
       triplea_helper_displayNode('localcurrency-error-otp-wrong');
     }

     /**
      *  OTP has been validated.
      *  Account has been created.
      *  Proceed with saving the account information
      *  and make this account the active one for bitcoin payments.
      */
     function triplea_localcurrency_validateEmailOtpCallback(result)
     {
       triplea_hideAllErrors();

       if (!merchantAccountRequestResult
         || !merchantAccountRequestResult.client_id
         || !merchantAccountRequestResult.client_secret
         || !merchantAccountRequestResult.merchant_key
         || !merchantAccountRequestResult.accounts
         || merchantAccountRequestResult.accounts.length === 0)
       {
         console.warn('TripleA Warning: missing merchant account data.');
         triplea_helper_displayNode("localcurrency-account-creation-error");
         return;
       }

       let hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '';

       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];

         if (acc.crypto_currency === 'BTC' && !acc.sandbox && acc.type === 'triplea')
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
           apiIdNode.value = acc.api_id;
         }
         if (acc.crypto_currency === 'testBTC' && acc.sandbox && acc.type === 'triplea')
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }
       }

       // Sets active account then submits form
       let sandbox = false;
       triplea_setActiveAccount('btc2fiat', sandbox);
     }

   </script>

   <script>
     /**
      *
      *  BTC2BTC Testnet UPGRADE path
      *
      */

     /**
      *  Request upgrade of the API ID.
      *  This triggers an OTP email for email validation.
      *  Display the OTP validation step.
      */
     function triplea_testnetbitcoin_upgrade_emailvalidationstep()
     {
       // Reset errors, before validation
       jQuery('.triplea-error-msg').hide();
       
       // Optional validation of fields before proceeding
       
       triplea_testnetbitcoin_upgradeMerchantAccount();
     }

     /**
      *  Request merchant account upgrade from API v0 to v1.
      *  Receive a merchant key in response,
      *  to be used when validating the email via OTP.
      */
     function triplea_testnetbitcoin_upgradeMerchantAccount()
     {
       triplea_hideAllErrors();
       
       const apiId = old_btc2btc_sandbox_api_id;

       // Ask for upgrade and OTP to be sent to the API ID's associated email.
       const url      = `https://moneyoverip.io/api/v1/merchant/upgrade`;
       const callback = triplea_testnetbitcoin_upgradeMerchantAccountCallback;
       const errorCallback = triplea_testnetbitcoin_upgradeMerchantAccountCallbackError;
       const method   = "POST";
       const data     = {
         api_id: apiId,
         source: 'woocommerce',
         plugin: {
           domain: window.location.hostname,
           plugin_ver: site_info.plugin_v,
           platform_ver: site_info.wp_v,
           platform: site_info.type,
           //// extra
           php_ver: site_info.php_v,
           url: site_info.url,
           debug_log: site_info.debug_log,
           lang: site_info.lang,
           admin_email: site_info.admin
         }
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, errorCallback);

       jQuery('#upgrade-testnetbitcoin-validate-email').fadeTo(0, 1);
     }

     /**
      *  Something went wrong when upgrading the merchant account.
      */
     function triplea_testnetbitcoin_upgradeMerchantAccountCallbackError(err)
     {
       jQuery('#upgrade-testnetbitcoin-validate-email').fadeTo(0, 0.5);
       console.error('TripleA Error: Error with merchant account upgrade.', err);
       jQuery('#testnetbitcoin-upgrade-error-something-wrong').show();
     }

     /**
      *  Upgrade request succeeded. Received some account data to display to the user.
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_testnetbitcoin_upgradeMerchantAccountCallback(result)
     {
       jQuery('#upgrade-testnetbitcoin-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.log('Prepared account upgrade. OTP code sent to ' + result.email + '. Pending OTP validation..');
       
       jQuery('#testnetbitcoin-upgrade-email-sent').html('Email sent to <em>'+ result.email +'</em>!');
       jQuery('#testnetbitcoin-upgrade-email-sent').fadeTo(0, 1);
       jQuery('#testnetbitcoin-upgrade-enter-otp').fadeTo(1, 1);
       jQuery('#testnetbitcoin-upgrade-otp-submit').fadeTo(1, 1);
       merchantAccountRequestResult = result;
     }

     /**
      *  Submit OTP and merchant key for validation for account upgrade request.
      */
     function triplea_testnetbitcoin_upgrade_validateEmailOtp()
     {
       let otpNode = document.getElementById('testnetbitcoin-upgrade-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('testnetbitcoin-upgrade-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('testnetbitcoin-upgrade-otp-check-loading');

       const url      = `https://api.triple-a.io/api/v1/merchant/` + merchantAccountRequestResult.merchant_key + `/upgrade/verify`;
       const callback = triplea_testnetbitcoin_upgrade_validateEmailOtpCallback;
       const method   = "PUT";
       const data     = {
         merchant_pin: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, triplea_testnetbitcoin_upgrade_validateEmailOtpCallbackError);
     }

     /**
      *  OTP validation failed.
      */
     function triplea_testnetbitcoin_upgrade_validateEmailOtpCallbackError(err)
     {
       triplea_hideAllErrors();
       triplea_helper_displayNode('testnetbitcoin-upgrade-error-otp-wrong');
     }

     /**
      *  OTP has been validated.
      *  Account has been upgraded.
      *  Proceed with saving the received additional account information
      *  and make this account the active one for bitcoin payments.
      */
     function triplea_testnetbitcoin_upgrade_validateEmailOtpCallback(result)
     {
       triplea_hideAllErrors();

       if (!merchantAccountRequestResult
         || !merchantAccountRequestResult.name
         || !merchantAccountRequestResult.email
         || !merchantAccountRequestResult.merchant_key)
       {
         console.warn('TripleA Warning: missing merchant account data from initial upgrade call.');
         triplea_helper_displayNode("testnetbitcoin-upgrade-account-creation-error");
         return;
       }

       if (!result
         || !result.client_id
         || !result.client_secret
         || !result.merchant_key
         || !result.accounts
         || result.accounts.length === 0)
       {
         console.warn('TripleA Warning: missing merchant account data after otp verification.');
         triplea_helper_displayNode("testnetbitcoin-upgrade-account-creation-error");
         return;
       }
       
       // We already had some info, now add/update with what we received.
       Object.assign(merchantAccountRequestResult, result);

       let hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantEmail   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_email');
       hiddenNodeMerchantEmail.value = merchantAccountRequestResult.email || '';

       let hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '';

       let hiddenNodePubkey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_pubkey');
       hiddenNodePubkey.value = window.testbitcoin_pubkey || '';

       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];

         if (acc.crypto_currency === 'testBTC' && acc.sandbox && acc.type === 'self')
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }
       }

       // Sets active account then submits form
       let sandbox = true;
       triplea_setActiveAccount('btc2btc', sandbox);
     }
     
   </script>

   <script>
     /**
      *
      *  BTC2BTC Live UPGRADE path
      *
      */

     /**
      *  Request upgrade of the API ID.
      *  This triggers an OTP email for email validation.
      *  Display the OTP validation step.
      */
     function triplea_bitcoin_upgrade_emailvalidationstep()
     {
       // Reset errors, before validation
       jQuery('.triplea-error-msg').hide();

       // Optional validation of fields before proceeding

       triplea_bitcoin_upgradeMerchantAccount();
     }

     /**
      *  Request merchant account upgrade from API v0 to v1.
      *  Receive a merchant key in response,
      *  to be used when validating the email via OTP.
      */
     function triplea_bitcoin_upgradeMerchantAccount()
     {
       triplea_hideAllErrors();

       const apiId = old_btc2btc_api_id;

       // Ask for upgrade and OTP to be sent to the API ID's associated email.
       const url      = `https://moneyoverip.io/api/v1/merchant/upgrade`;
       const callback = triplea_bitcoin_upgradeMerchantAccountCallback;
       const errorCallback = triplea_bitcoin_upgradeMerchantAccountCallbackError;
       const method   = "POST";
       const data     = {
         api_id: apiId,
         source: 'woocommerce',
         plugin: {
           domain: window.location.hostname,
           plugin_ver: site_info.plugin_v,
           platform_ver: site_info.wp_v,
           platform: site_info.type,
           //// extra
           php_ver: site_info.php_v,
           url: site_info.url,
           debug_log: site_info.debug_log,
           lang: site_info.lang,
           admin_email: site_info.admin
         }
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, errorCallback);

       jQuery('#upgrade-bitcoin-validate-email').fadeTo(0, 1);
     }

     /**
      *  Something went wrong when upgrading the merchant account.
      */
     function triplea_bitcoin_upgradeMerchantAccountCallbackError(err)
     {
       jQuery('#upgrade-bitcoin-validate-email').fadeTo(0, 0.5);
       console.error('TripleA Error: Error with merchant account upgrade.', err);
       jQuery('#bitcoin-upgrade-error-something-wrong').show();
     }

     /**
      *  Upgrade request succeeded. Received some account data to display to the user.
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_bitcoin_upgradeMerchantAccountCallback(result)
     {
       jQuery('#upgrade-bitcoin-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.log('Prepared account upgrade. OTP code sent to ' + result.email + '. Pending OTP validation..');

       jQuery('#bitcoin-upgrade-email-sent').html('Email sent to <em>'+ result.email +'</em>!');
       jQuery('#bitcoin-upgrade-email-sent').fadeTo(0, 1);
       jQuery('#bitcoin-upgrade-enter-otp').fadeTo(1, 1);
       jQuery('#bitcoin-upgrade-otp-submit').fadeTo(1, 1);
       merchantAccountRequestResult = result;
     }

     /**
      *  Submit OTP and merchant key for validation for account upgrade request.
      */
     function triplea_bitcoin_upgrade_validateEmailOtp()
     {
       let otpNode = document.getElementById('bitcoin-upgrade-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('bitcoin-upgrade-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('bitcoin-upgrade-otp-check-loading');

       const url      = `https://api.triple-a.io/api/v1/merchant/` + merchantAccountRequestResult.merchant_key + `/upgrade/verify`;
       const callback = triplea_bitcoin_upgrade_validateEmailOtpCallback;
       const method   = "PUT";
       const data     = {
         merchant_pin: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, triplea_bitcoin_upgrade_validateEmailOtpCallbackError);
     }

     /**
      *  OTP validation failed.
      */
     function triplea_bitcoin_upgrade_validateEmailOtpCallbackError(err)
     {
       triplea_hideAllErrors();
       triplea_helper_displayNode('bitcoin-upgrade-error-otp-wrong');
     }

     /**
      *  OTP has been validated.
      *  Account has been upgraded.
      *  Proceed with saving the received additional account information
      *  and make this account the active one for bitcoin payments.
      */
     function triplea_bitcoin_upgrade_validateEmailOtpCallback(result)
     {
       triplea_hideAllErrors();

       if (!merchantAccountRequestResult
         || !merchantAccountRequestResult.name
         || !merchantAccountRequestResult.email
         || !merchantAccountRequestResult.merchant_key)
       {
         console.warn('TripleA Warning: missing merchant account data from initial upgrade call.');
         triplea_helper_displayNode("bitcoin-upgrade-account-creation-error");
         return;
       }

       if (!result
         || !result.client_id
         || !result.client_secret
         || !result.merchant_key
         || !result.accounts
         || result.accounts.length === 0)
       {
         console.warn('TripleA Warning: missing merchant account data after otp verification.');
         triplea_helper_displayNode("bitcoin-upgrade-account-creation-error");
         return;
       }

       // We already had some info, now add/update with what we received.
       Object.assign(merchantAccountRequestResult, result);

       let hiddenNodeMerchantKey;
       if (window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0)
         hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_key');
       else
         hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId;
       if (window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0)
         hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_id');
       else
         hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret;
       if (window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0)
         hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_client_secret');
       else
         hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName;
       if (window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0)
         hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_name');
       else
         hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantEmail;
       if (window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0)
         hiddenNodeMerchantEmail   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_email');
       else
         hiddenNodeMerchantEmail   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_email');
       hiddenNodeMerchantEmail.value = merchantAccountRequestResult.email || '';

       let hiddenNodeMerchantPhone;
       if (window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0)
         hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_merchant_phone');
       else
         hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '';

       let hiddenNodePubkey;
       if (window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0)
       {
         console.log('Testnet bitcoin wallet linked to account');
         hiddenNodePubkey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_pubkey');
         hiddenNodePubkey.value = window.testbitcoin_pubkey;
       }
       else
       {
         console.log('Bitcoin wallet linked to account');
         hiddenNodePubkey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_pubkey');
         hiddenNodePubkey.value = window.bitcoin_pubkey;
       }

       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];
         // console.debug(' - existing account found: ', acc);
         // console.debug(' - testbitcoin_pubkey: ', window.testbitcoin_pubkey);
         // console.debug(' - bitcoin_pubkey: ', window.bitcoin_pubkey);
         // console.debug(' - old_btc2btc_api_id: ', old_btc2btc_api_id);
         // console.debug(' - old_btc2btc_sandbox_api_id: ', old_btc2btc_sandbox_api_id);

         if ((window.bitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') < 0) && acc.crypto_currency === 'BTC' && !acc.sandbox && acc.type === 'self')
         {
           // console.debug(' + account match for btc2btc: ');
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
           apiIdNode.value = acc.api_id;
         }
         if ((window.testbitcoin_pubkey || old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0) && acc.crypto_currency === 'testBTC' && acc.sandbox && acc.type === 'self')
         {
           // console.debug(' + account match for testnet btc2btc: ');
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }
       }

       if (!window.testbitcoin_pubkey && !window.bitcoin_pubkey && !old_btc2btc_api_id) {
         console.warn('TripleA Warning: Problem, missing pubkey');
         return;
       }
       
       // Sets active account then submits form
       let sandbox = !!window.testbitcoin_pubkey || !!(old_btc2btc_api_id.toLowerCase().indexOf('_t') > 0);
       triplea_setActiveAccount('btc2btc', sandbox);
     }

   </script>

   <script>
     /**
      *
      *  BTC2FIAT Live UPGRADE path
      *
      */

     /**
      *  Request upgrade of the API ID.
      *  This triggers an OTP email for email validation.
      *  Display the OTP validation step.
      */
     function triplea_fiat_upgrade_emailvalidationstep()
     {
       // Reset errors, before validation
       jQuery('.triplea-error-msg').hide();

       // Optional validation of fields before proceeding

       triplea_fiat_upgradeMerchantAccount();
     }

     /**
      *  Request merchant account upgrade from API v0 to v1.
      *  Receive a merchant key in response,
      *  to be used when validating the email via OTP.
      */
     function triplea_fiat_upgradeMerchantAccount()
     {
       triplea_hideAllErrors();

       const apiId = old_btc2fiat_api_id;

       // Ask for upgrade and OTP to be sent to the API ID's associated email.
       const url      = `https://moneyoverip.io/api/v1/merchant/upgrade`;
       const callback = triplea_fiat_upgradeMerchantAccountCallback;
       const errorCallback = triplea_fiat_upgradeMerchantAccountCallbackError;
       const method   = "POST";
       const data     = {
         api_id: apiId,
         source: 'woocommerce',
         plugin: {
           domain: window.location.hostname,
           plugin_ver: site_info.plugin_v,
           platform_ver: site_info.wp_v,
           platform: site_info.type,
           //// extra
           php_ver: site_info.php_v,
           url: site_info.url,
           debug_log: site_info.debug_log,
           lang: site_info.lang,
           admin_email: site_info.admin
         }
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, errorCallback);

       jQuery('#upgrade-fiat-validate-email').fadeTo(0, 1);
     }

     /**
      *  Something went wrong when upgrading the merchant account.
      */
     function triplea_fiat_upgradeMerchantAccountCallbackError(err)
     {
       jQuery('#upgrade-fiat-validate-email').fadeTo(0, 0.5);
       console.error('TripleA Error: Error with merchant account upgrade.', err);
       jQuery('#fiat-upgrade-error-something-wrong').show();
     }

     /**
      *  Upgrade request succeeded. Received some account data to display to the user.
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_fiat_upgradeMerchantAccountCallback(result)
     {
       jQuery('#upgrade-fiat-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.log('Prepared account upgrade. OTP code sent to ' + result.email + '. Pending OTP validation..');

       jQuery('#fiat-upgrade-email-sent').html('Email sent to <em>'+ result.email +'</em>!');
       jQuery('#fiat-upgrade-email-sent').fadeTo(0, 1);
       jQuery('#fiat-upgrade-enter-otp').fadeTo(1, 1);
       jQuery('#fiat-upgrade-otp-submit').fadeTo(1, 1);
       merchantAccountRequestResult = result;
     }

     /**
      *  Submit OTP and merchant key for validation for account upgrade request.
      */
     function triplea_fiat_upgrade_validateEmailOtp()
     {
       let otpNode = document.getElementById('fiat-upgrade-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('fiat-upgrade-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('fiat-upgrade-otp-check-loading');

       const url      = `https://api.triple-a.io/api/v1/merchant/` + merchantAccountRequestResult.merchant_key + `/upgrade/verify`;
       const callback = triplea_fiat_upgrade_validateEmailOtpCallback;
       const method   = "PUT";
       const data     = {
         merchant_pin: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data), null, triplea_fiat_upgrade_validateEmailOtpCallbackError);
     }

     /**
      *  OTP validation failed.
      */
     function triplea_fiat_upgrade_validateEmailOtpCallbackError(err)
     {
       triplea_hideAllErrors();
       triplea_helper_displayNode('fiat-upgrade-error-otp-wrong');
     }

     /**
      *  OTP has been validated.
      *  Account has been upgraded.
      *  Proceed with saving the received additional account information
      *  and make this account the active one for bitcoin payments.
      */
     function triplea_fiat_upgrade_validateEmailOtpCallback(result)
     {
       triplea_hideAllErrors();

       if (!merchantAccountRequestResult
         || !merchantAccountRequestResult.name
         || !merchantAccountRequestResult.email
         || !merchantAccountRequestResult.merchant_key)
       {
         console.warn('TripleA Warning: missing merchant account data from initial upgrade call.');
         triplea_helper_displayNode("fiat-upgrade-account-creation-error");
         return;
       }

       if (!result
         || !result.client_id
         || !result.client_secret
         || !result.merchant_key
         || !result.accounts
         || result.accounts.length === 0)
       {
         console.warn('TripleA Warning: missing merchant account data after otp verification.');
         triplea_helper_displayNode("fiat-upgrade-account-creation-error");
         return;
       }

       // We already had some info, now add/update with what we received.
       Object.assign(merchantAccountRequestResult, result);

       let hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantEmail   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_email');
       hiddenNodeMerchantEmail.value = merchantAccountRequestResult.email || '';

       let hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '';
       
       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];
         // console.debug(' - existing account found: ', acc);

         if (acc.crypto_currency === 'BTC' && acc.type === 'triplea' && !acc.sandbox)
         {
           // We know which API ID we're upgrading. If there are any other 'BTC' accounts, ignore them.
           if (acc.api_id !== old_btc2fiat_api_id) {
             // console.debug(' - - skipping account with API ID not matching');
             continue;
           }
           
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
           apiIdNode.value = acc.api_id;
         }
         else if (acc.crypto_currency === 'testBTC' && acc.sandbox && acc.type === 'triplea')
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }
       }

       // Sets active account then submits form
       let sandbox = false;
       triplea_setActiveAccount('btc2fiat', sandbox);
     }

   </script>

   <script>

     /**
      *
      *  Helper functions
      *
      */

     function triplea_helper_displayNode(nodeId)
     {
       let displayNode = document.getElementById(nodeId);
       if (displayNode) displayNode.style.display = 'block';
     }

     function triplea_helper_hideNode(nodeId)
     {
       let displayNode = document.getElementById(nodeId);
       if (displayNode) displayNode.style.display = 'none';
     }

     function triplea_submitForm()
     {
       let submitBtnNodes = document.getElementsByName('save');
       if (submitBtnNodes && submitBtnNodes.length > 0)
       {
         console.log('Submitting form');
         submitBtnNodes[0].click();
       }
     }

     /**
      * Toggle the active account based on settlement mode and sandbox on/off preference.
      *
      * This function won't have any effect if the target account is not available.
      * (This can happen mostly for btc2btc where we might have added a wallet
      * for either testnet bitcoin or live bitcoin.)
      *
      * @param walletType
      * @param sandbox
      */
     function triplea_setActiveAccount(walletType, sandbox)
     {
       let apiId, apiIdNode;
       console.log('Setting active account : ' + walletType + ' ' + (sandbox ? 'testnet' : ''));
       switch (walletType)
       {
         case 'btc2btc':
           apiIdNode = sandbox
             ? document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_api_id')
             : document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
           if (apiIdNode.value !== '')
           {
             apiId = apiIdNode.value;
           }
           else
           {
             console.warn('TripleA Warning: Cannot change active account. Target account not found.');
             return;
           }
           break;
         case 'btc2fiat':
           // console.debug('switching btc2fiat sandbox from ' + !sandbox + ' to ' + (sandbox) + '.');
           apiIdNode = sandbox
             ? document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_sandbox_api_id')
             : document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
           if (apiIdNode.value !== '')
           {
             // console.debug('using ' + apiIdNode.value + ' as target.');
             apiId = apiIdNode.value;
           }
           else
           {
             console.warn('TripleA Warning: Cannot change active account. Target account not found.');
             return;
           }
           break;
       }

       let activeApiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_active_api_id');
       activeApiIdNode.value = apiId;
       // console.debug('active API ID value set to ' + apiId);

       let sandboxPaymentModeNode   = document.getElementById(settingsPrefix + '_' + 'triplea_sandbox_payment_mode');
       sandboxPaymentModeNode.value = sandbox;

       console.log('Enabling ' + (sandbox ? 'sandbox ' : ' ') + 'account for ' + (walletType === 'btc2btc' ? 'bitcoin' : 'local currency') + ' settlement.');

       setTimeout(function () {
         triplea_submitForm();
       }, 200);
     }

     function triplea_ajax_action(url, callback, _method, _data, _authToken = null, errorCallback)
     {
       let xmlhttp                = new XMLHttpRequest();
       xmlhttp.onreadystatechange = function () {
         if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
         {
           try
           {
             var data = JSON.parse(xmlhttp.responseText);
           }
           catch (err)
           {
             console.err('TripleA Warning: Error parsing JSON response. ' + err.message + " in " + xmlhttp.responseText);
             if (errorCallback) errorCallback(err);
             return;
           }
           callback(data);
         }
         else if (xmlhttp.readyState === 4 && errorCallback) {
           errorCallback(xmlhttp);
         }
       };
       xmlhttp.open(_method, url, true);
       xmlhttp.setRequestHeader('Content-Type', 'application/json');
       if (_authToken)
       {
         xmlhttp.setRequestHeader("Authorization", "Bearer " + _authToken);
       }
       xmlhttp.send(_data);
     }

   </script>

   <!--section id="TripleA-debugging-information" data-info="Share this via support@triple-a.io if asked by TripleA support">
   <pre><?php

      echo print_r(get_option('woocommerce_triplea_payment_gateway_settings'), TRUE);

      ?></pre>
   </section-->

<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
