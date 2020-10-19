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
$old_btc2btc_api_id = 'something1';
$old_btc2btc_sandbox_api_id = 'something2';
$old_btc2fiat_api_id = 'something3';
$old_active_api_id = 'something2';


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


   <!--small>
      <pre>$payment_mode = <?php echo print_r($payment_mode, TRUE); ?></pre>
      <pre>$sandbox_payment_mode = <?php echo($sandbox_payment_mode ? 'true' : 'false'); ?></pre>
      <br>
      <pre>$fiat_merchant_key = <?php echo print_r($fiat_merchant_key, TRUE); ?></pre>
      <pre>$fiat_client_id = <?php echo print_r($fiat_client_id, TRUE); ?></pre>
      <pre>$fiat_client_secret = <?php echo print_r($fiat_client_secret, TRUE); ?></pre>
      <pre>$fiat_merchant_name = <?php echo print_r($fiat_merchant_name, TRUE); ?></pre>
      <pre>$fiat_merchant_email = <?php echo print_r($fiat_merchant_email, TRUE); ?></pre>
      <pre>$fiat_merchant_phone = <?php echo print_r($fiat_merchant_phone, TRUE); ?></pre>
      <pre>$fiat_merchant_local_currency = <?php echo print_r($fiat_merchant_local_currency, TRUE); ?></pre>
      <pre>$triplea_btc2fiat_api_id = <?php echo print_r($triplea_btc2fiat_api_id, TRUE); ?></pre>
      <pre>$triplea_btc2fiat_sandbox_api_id = <?php echo print_r($triplea_btc2fiat_sandbox_api_id, TRUE); ?></pre>
      <br>
      <pre>$btc_merchant_key = <?php echo print_r($btc_merchant_key, TRUE); ?></pre>
      <pre>$btc_client_id = <?php echo print_r($btc_client_id, TRUE); ?></pre>
      <pre>$btc_client_secret = <?php echo print_r($btc_client_secret, TRUE); ?></pre>
      <pre>$btc_merchant_name = <?php echo print_r($btc_merchant_name, TRUE); ?></pre>
      <pre>$btc_merchant_email = <?php echo print_r($btc_merchant_email, TRUE); ?></pre>
      <pre>$btc_merchant_phone = <?php echo print_r($btc_merchant_phone, TRUE); ?></pre>
      <pre>$btc_pubkey = <?php echo print_r($btc_pubkey, TRUE); ?></pre>
      <pre>$btc_sandbox_pubkey = <?php echo print_r($btc_sandbox_pubkey, TRUE); ?></pre>
      <pre>$triplea_btc2btc_api_id = <?php echo print_r($triplea_btc2btc_api_id, TRUE); ?></pre>
      <pre>$triplea_btc2btc_sandbox_api_id = <?php echo print_r($triplea_btc2btc_sandbox_api_id, TRUE); ?></pre>
      <br>
      <pre>$triplea_active_api_id = <?php echo print_r($triplea_active_api_id, TRUE); ?></pre>
      <pre>$btc2fiat_is_active = <?php echo($btc2fiat_is_active === TRUE ? 'true' : 'false'); ?></pre>
      <pre>$btc2btc_is_active = <?php echo($btc2btc_is_active === TRUE ? 'true' : 'false'); ?></pre>
      <br>
   </small-->

   <!--payments enabled with either btc or fiat settlement-->
   <div class="triplea_step" id="step-enabled"
        style="display: <?php echo($btc2fiat_is_active || $btc2btc_is_active ? 'block' : 'none'); ?>;">
      <hr>

      <br>

      <div id="step-enabled--btc2btc-sandbox"
           style="display:<?php echo($btc2btc_is_active && $sandbox_payment_mode ? 'block' : 'none') ?> ;">

         <h3><span style="text-decoration: underline;">Sandbox</span> Bitcoin payments are enabled.</h3>

         <h3>Settlement mode: straight to your testnet Bitcoin wallet.</h3>
<!--         <h4>-->
<!--            <em>Live</em> payments <em>enabled</em>-->
<!--            <small>(sandbox mode disabled)</small>-->
<!--         </h4>-->

         <p>
            You have chosen to receive testnet bitcoin payments straight in your own <strong>testnet bitcoin wallet</strong>.
         </p>
         <p>
            Testnet bitcoin payments you receive can be viewed in your own wallet or for more details,
            <br>
            you may access <a href="https://dashboard.triple-a.io" target="_blank">your TripleA dashboard</a> with the credentials you have received by email.
         </p>
         <p>
            To easily test payments, online faucet websites can be used. Find some here: <a href="https://duckduckgo.com/?q=bitcoin+testnet+faucets" target="_blank">bitcoin testnet faucets</a>.
            <br>
            - When making a bitcoin payment, simply copy the bitcoin address and the bitcoin amount into the faucet website's form.
            <br>
            - The faucet website will make a bitcoin payment on your behalf.
            <br>
            - The checkout page will detect the payment and place the order accordingly.
         </p>
         <br>
         <input type="button" class="button-primary" value="Show payment settlement and sandbox options" onclick="gotoStep1()">
         <p>You can instantly switch back and forth between
            settling payments in local currency or to your bitcoin
            wallet.
            <br>
            All payment account information is preserved.
         </p>
      </div>

      <div id="step-enabled--btc2btc"
           style="display:<?php echo($btc2btc_is_active && !$sandbox_payment_mode ? 'block' : 'none') ?> ;">

         <h3><span style="text-decoration: underline;">Live</span> Bitcoin payments are enabled.</h3>

         <h4>Settlement mode: Bitcoin wallet.</h4>

         <h4>
            Live payments enabled. <small>(sandbox payments disabled)</small>
         </h4>

         <p>
            You have chosen to receive bitcoin payments straight in your own <strong>bitcoin wallet</strong>.
         </p>
         <p>
            Bitcoin payments you receive can be viewed in your own wallet or for more details,
            <br>
            you may access <a href="https://dashboard.triple-a.io" target="_blank">your TripleA dashboard</a> with the credentials you have received by email.
         </p>
         <br>
         <input type="button" class="button-primary" value="Show payment settlement and sandbox options" onclick="gotoStep1()">
         <p>You can instantly switch back and forth between
            settling payments in local currency or to your bitcoin
            wallet.
            <br>
            All payment account information is preserved.
         </p>
      </div>

      
      <div id="step-enabled--btc2fiat"
           style="display:<?php echo($btc2fiat_is_active ? 'block' : 'none') ?>;">

         <h3 style="display:<?php echo($sandbox_payment_mode ? 'block' : 'none') ?>;"><span style="text-decoration: underline;">Sandbox</span> bitcoin payments are enabled.</h3>
         <h3 style="display:<?php echo(!$sandbox_payment_mode ? 'block' : 'none') ?>;"><span style="text-decoration: underline;">Live</span> bitcoin payments are enabled.</h3>
         <h3>
            Settlement mode: local currency.
            <!--(--><?php //echo $fiat_merchant_local_currency; ?><!--)-->
         </h3>
         <!--TODO do not display "preferred currency" here, refer to dashboard-->
         
         <h4 style="display:<?php echo(!$sandbox_payment_mode ? 'block' : 'none') ?>;">
            <span style="text-decoration: underline;">Live</span> payments enabled.
            <span>(sandbox mode disabled)</span>
         </h4>

         <p id="btc2fiat-message">
            You have chosen to receive bitcoin payments in the form of
            <strong>local currency
            <!--(--><?php //echo $fiat_merchant_local_currency; ?><!--)-->
            </strong>
            settled to <strong>your bank account</strong>.
            <span style="display:<?php echo($sandbox_payment_mode ? 'block' : 'none') ?>;">
               (Doesn't apply to sandbox payments :)
            </span>
         </p>
         <p>
            <strong>Important:</strong>
            <br>
            - Please login to <a href="https://dashboard.triple-a.io" target="_blank">your TripleA dashboard</a>.
            <br>
            - Your dashboard login and password have been sent by email.
            <br>
            - In your dashboard, you can select the local currency you prefer for bank settlements.
            <br>
            - Please also provide us with your <span style="text-decoration:underline;">bank account information</span> and KYC details, to start receiving your payments in your bank.<sup><strong>*</strong></sup>
         </p>
         <p>
            <sup><strong>*</strong></sup>
            Money is settled daily or weekly to your bank account if a minimum of USD 100 or equivalent has been reached.
            <br>
            At the end of the month, money is settled to your bank account (no minimum amount treshold). </p>
         <p style="display:<?php echo($sandbox_payment_mode ? 'block' : 'none') ?>;">
            <strong>Testing payments in sandbox mode:</strong>
            <br>
            - Testnet bitcoin is free and has no intrinsic value. It is used for testing payments using the same technology as bitcoin.
            <br>
            - To test bitcoin payments easily and without learning curve, online faucet websites can be used. Find some links here: <a href="https://duckduckgo.com/?q=bitcoin+testnet+faucets" target="_blank">find bitcoin testnet faucets</a>.
            <br>
            - When making a bitcoin payment, simply copy the bitcoin address and the bitcoin amount into the faucet website's form.
            <br>
            - The faucet website will make a bitcoin payment on your behalf.
            <br>
            - The checkout page will detect the payment and place the order accordingly.
         </p>
         <br>
         <br>
         <input type="button"
                style="display:<?php echo($sandbox_payment_mode ? 'block' : 'none') ?>;"
                class="button-primary"
                value="Turn sandbox payments OFF & Live payments ON"
                onclick="triplea_setActiveAccount('btc2fiat', false)">
         <input type="button"
                style="display:<?php echo(!$sandbox_payment_mode ? 'block' : 'none') ?>;"
                class="button-primary"
                value="Turn sandbox payments ON & Live payments OFF"
                onclick="triplea_setActiveAccount('btc2fiat', true)">
         <br>
         <input type="button" class="button-secondary" value="Show payment settlement and sandbox options" onclick="gotoStep1()">
         <p>You can instantly switch back and forth between
            settling payments in local currency or to your bitcoin
            wallet.
            <br>
            All payment account information is preserved.
         </p>
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
         <?php echo($btc2fiat_is_active ? "checked" : '') ?>
             style="margin:10px;">receive local currency<br>
      <div class="receive-choice-wrapper"
           style="margin-left: 40px;display:<?php echo($btc2fiat_is_active ? "block" : 'none') ?>;"
           id="localcurrency-form-wrapper">
         <div class="receive-choice-update-wrapper"
              style="display:<?php echo(!empty($triplea_btc2fiat_sandbox_api_id) || !empty($triplea_btc2fiat_api_id) ? "block" : 'none') ?>;"
              id="localcurrency-update-form-wrapper">
            <input type="button" class="button-primary" value="Already active" onclick="" disabled="disabled" style="display:<?php echo($btc2fiat_is_active && $sandbox_payment_mode ? "block" : 'none') ?>;margin-bottom:8px;">
            <span><strong>Account e-mail:</strong> <?php echo $fiat_merchant_email; ?></span>
            <br>
            <br>
            <input type="button" class="button-primary" value="Activate SANDBOX bitcoin payments to my own wallet" onclick="triplea_setActiveAccount('btc2fiat', true)" style="display:<?php echo($btc2fiat_is_active && $sandbox_payment_mode ? "none" : "block") ?>;margin-bottom:8px;">
            <input type="button" class="button-primary" value="Activate Live bitcoin payments to my own wallet" onclick="triplea_setActiveAccount('btc2fiat', false)" style="display:<?php echo($btc2fiat_is_active && !$sandbox_payment_mode ? "none" : "block") ?>;margin-bottom:8px;">
            <!--<input type="button" class="button-secondary" value="Update my testnet bitcoin wallet" onclick="jQuery('#testnetbitcoin-signup-form-wrapper').show()">-->
         </div>
         <div style="display:<?php echo(empty($triplea_btc2fiat_sandbox_api_id) ? "block" : "none") ?>;" id="localcurrency-signup-form-wrapper">
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
                         value="Activate (sandbox) local currency account"
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
             value="testnetbitcoin"
         <?php echo($btc2btc_is_active && $sandbox_payment_mode ? "checked" : '') ?>
             style="margin:10px;">receive Testnet bitcoin<br>
      <div class="receive-choice-wrapper"
           style="margin-left: 40px;display:<?php echo($btc2btc_is_active && $sandbox_payment_mode ? "block" : 'none') ?>;"
           id="testnetbitcoin-form-wrapper">
         <div style="display:<?php echo(!empty($old_btc2btc_sandbox_api_id) && empty($btc_sandbox_merchant_key) ? "block" : "none") ?>;" id="testnetbitcoin-version-upgrade-form-wrapper">
            <hr>
            <div class="triplea_step" id="upgrade-testnetbitcoin-form">
               <h3>
                  <small>
                     Upgrade your testnet bitcoin account
                  </small>
               </h3>
               <p>
                  Due to technical improvements and added features, your account needs to be upgraded to be compatible.
                  <br>
                  After upgrading, your account will be linked to your TripleA dashboard.
                  <br>
                  Login credentials will be shared by e-mail.
               </p>
               <p>
                  Existing API ID: <?php echo $old_btc2btc_sandbox_api_id; ?>
                  <span class="woocommerce-help-tip" data-tip="This is your TripleA account identifier"></span>
               </p>

               <input type="button"
                      class="button-primary"
                      value="Upgrade"
                      onclick="triplea_testnetbitcoin_upgrade_emailvalidationstep()">
               <br>
               <br>
            </div>
            <div class="triplea_step"
                 id="upgrade-testnetbitcoin-validate-email"
                 style="opacity: 0.5;">
               <hr>
               <h3>
                  <small>
                     Validate email
                  </small>
               </h3>
               <p>
                  Sending OTP (one-time password) to your email address...
                  <strong id="testnetbitcoin-upgrade-email-sent" style="display:none;">
                     Email sent!
                  </strong>
               </p>
               <small style="color:darkred;display:none;"
                      class="triplea-error-msg error-otp-request"
                      id="testnetbitcoin-upgrade-error-otp-request">
                  Something went wrong while requesting OTP code. Try again, or
                  contact
                  us at support@triple-a.io so that we can assist you.
               </small>
               <p id="testnetbitcoin-upgrade-enter-otp" style="opacity: 0.5;">
                  Enter the One-Time Password code:
                  <input type="text"
                         id="testnetbitcoin-upgrade-otp-value"
                         name="testnetbitcoin-upgrade-otp-value"
                         value=""
                         placeholder="One-Time Password"
                         style="width:150px">
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-provide-otp"
                         id="testnetbitcoin-upgrade-error-provide-otp">
                     Please provide the OTP code that was sent to you by email.
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-otp-wrong"
                         id="testnetbitcoin-upgrade-error-otp-wrong">
                     Wrong OTP provided.
                  </small>
                  <br>
                  <br>
                  <input type="button"
                         id="testnetbitcoin-upgrade-otp-submit"
                         class="button-primary"
                         value="Activate testnet bitcoin account"
                         onclick="triplea_testnetbitcoin_upgrade_validateEmailOtp()"
                         style="opacity: 0.5;">
                  <small id="testnetbitcoin-upgrade-otp-check-loading"
                         class="triplea-error-msg otp-check-loading"
                         style="display:none;">
                     Loading...
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg account-creation-error"
                         id="testnetbitcoin-upgrade-account-creation-error">
                     The provided OTP was correct. However, something went wrong
                     during
                     the creation of your Testnet bitcoin account. Please inform us
                     at
                     support@triple-a.io so that we may assist you promptly.
                  </small>
               </p>
            </div>
         </div>
         <div class="receive-choice-update-wrapper"
              style="display:<?php echo(!empty($triplea_btc2btc_sandbox_api_id) ? "block" : 'none') ?>;"
              id="testnetbitcoin-update-form-wrapper">
            <input type="button" class="button-primary" value="Already active" onclick="" disabled="disabled" style="display:<?php echo($btc2btc_is_active && $sandbox_payment_mode ? "block" : 'none') ?>;margin-bottom:8px;">
            <span style="display:<?php echo($btc2btc_is_active && $sandbox_payment_mode ? "block" : 'none') ?>;"><strong>Email:</strong> <?php echo $btc_sandbox_merchant_email; ?>.
               <br><strong>Public key:</strong> <?php echo substr($btc_sandbox_pubkey, 0, 8); ?>...</span>
            <br>
            <input type="button" class="button-primary" value="Activate testnet bitcoin payments to my own wallet" onclick="triplea_setActiveAccount('btc2btc', true)" style="display:<?php echo($btc2btc_is_active && $sandbox_payment_mode ? "none" : "block") ?>;margin-bottom:8px;">
            <input type="button" class="button-secondary" value="Update my testnet bitcoin wallet" onclick="jQuery('#testnetbitcoin-signup-form-wrapper').show()">
            <br>
         </div>
         <div style="display:<?php echo(empty($triplea_btc2btc_sandbox_api_id) ? "block" : "none") ?>;" id="testnetbitcoin-signup-form-wrapper">
            <hr>
            <div class="triplea_step" id="receive-testnetbitcoin-form">
               <h3>
                  <small>
                     1. Provide Testnet wallet information
                  </small>
               </h3>
               <p>
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
               </p>
               <p>
                  Provide an email address:
                  <span class="woocommerce-help-tip"
                        data-tip="Valid email address needed."></span>
                  <br>
                  <input type="text"
                         id="testnetbitcoin-notif-email"
                         name="testnetbitcoin-notif-email"
                         value=""
                         placeholder="email address"
                         style="width:330px">
                  <span style="color:darkred;display:none;"
                         class="triplea-error-msg pubkey-wrong-email"
                         id="testnetbitcoin-error-pubkey-wrong-email">
                     This master public key has been associated with another email address. <a href="mailto:support@triple-a.io">Contact us at support@triple-a.io</a> if you need assistance.
                  </span>
                  <br>
                  <small style="color: darkred;display:none;"
                         class="triplea-error-msg wrong-notif-email"
                         id="testnetbitcoin-wrong-notif-email">
                     The provided email address seems wrong.
                  </small>
               </p>
               <input type="button"
                      class="button-primary"
                      value="Proceed"
                      onclick="triplea_testnetbitcoin_emailvalidationstep()">
               <br>
               <br>
            </div>
            <div class="triplea_step"
                 id="receive-testnetbitcoin-validate-email"
                 style="opacity: 0.5;">
               <hr>
               <h3>
                  <small>
                     2. Validate email
                  </small>
               </h3>
               <p>
                  Sending OTP (one-time password) to your email address...
                  <strong id="testnetbitcoin-email-sent" style="display:none;">Email
                                                                               sent!</strong>
               </p>
               <small style="color:darkred;display:none;"
                      class="triplea-error-msg error-otp-request"
                      id="testnetbitcoin-error-otp-request">
                  Something went wrong while requesting OTP code. Try again, or
                  contact
                  us at support@triple-a.io so that we can assist you.
               </small>
               <p id="testnetbitcoin-enter-otp" style="opacity: 0.5;">
                  Enter the One-Time Password code:
                  <input type="text"
                         id="testnetbitcoin-otp-value"
                         name="testnetbitcoin-otp-value"
                         value=""
                         placeholder="One-Time Password"
                         style="width:150px">
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-provide-otp"
                         id="testnetbitcoin-error-provide-otp">
                     Please provide the OTP code that was sent to you by email.
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg error-otp-wrong"
                         id="testnetbitcoin-error-otp-wrong">
                     Wrong OTP provided.
                  </small>
                  <br>
                  <br>
                  <input type="button"
                         id="testnetbitcoin-otp-submit"
                         class="button-primary"
                         value="Activate testnet bitcoin account"
                         onclick="triplea_testnetbitcoin_validateEmailOtp()"
                         style="opacity: 0.5;">
                  <small id="testnetbitcoin-otp-check-loading"
                         class="triplea-error-msg otp-check-loading"
                         style="display:none;">
                     Loading...
                  </small>
                  <small style="color:darkred;display:none;"
                         class="triplea-error-msg account-creation-error"
                         id="testnetbitcoin-account-creation-error">
                     The provided OTP was correct. However, something went wrong
                     during
                     the creation of your Testnet bitcoin account. Please inform us
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
         <?php echo($btc2btc_is_active && !$sandbox_payment_mode ? "checked" : '') ?>
             style="margin:10px;">receive bitcoin<br>
      <div class="receive-choice-wrapper"
           style="margin-left: 40px;display:<?php echo($btc2btc_is_active && !$sandbox_payment_mode ? "block" : 'none') ?>;"
           id="bitcoin-form-wrapper">
         <div class="receive-choice-update-wrapper"
              style="display:<?php echo(!empty($triplea_btc2btc_api_id) ? "block" : 'none') ?>;"
              id="bitcoin-update-form-wrapper">
            <input type="button" class="button-primary" value="Already active" onclick="" disabled="disabled" style="display:<?php echo($btc2btc_is_active && !$sandbox_payment_mode ? "block" : 'none') ?>;margin-bottom:8px;">
            <span style="display:<?php echo($btc2btc_is_active && $sandbox_payment_mode ? "block" : 'none') ?>;"><strong>Email:</strong> <?php echo $btc_merchant_email; ?>.
               <br><strong>Public key:</strong> <?php echo substr($btc_pubkey, 0, 8); ?>...</span>
            <br>
            <input type="button" class="button-primary" value="Activate live bitcoin payments to my own wallet" onclick="triplea_setActiveAccount('btc2btc', false)" style="display:<?php echo($btc2btc_is_active && !$sandbox_payment_mode ? "none" : "block") ?>;margin-bottom:15px;">
            <input type="button" class="button-secondary" value="Update my bitcoin wallet" onclick="jQuery('#bitcoin-signup-form-wrapper').show()">
            <br>
         </div>
         <div style="display:<?php echo(empty($triplea_btc2btc_api_id) ? "block" : "none") ?>;" id="bitcoin-signup-form-wrapper">
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
                  <span>Please provide a valid master public key for your bitcoin wallet (starting with 'xpub', 'ypub' or 'zpub').</span>
                  <span style="color: darkred;display:none;"
                        class="triplea-error-msg"
                        id="bitcoin-wrong-pubkey-format"><br>The provided master public key does not have the right format.</span>
               </p>
               <p>
                  Provide an email address:
                  <span class="woocommerce-help-tip"
                        data-tip="Valid email address needed."></span>
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

   <!--provide bitcoin wallet's master public key and notification email-->
   <div class="triplea_step" id="btc-step-2" style="display:none;">
      <hr>
      <br>
      <h3>
         Receive bitcoin
         <br>
         <small style="line-height: 30px;">
            Provide wallet information
         </small>
      </h3>
      <p>
         Master public key of your bitcoin wallet:
         <span class="woocommerce-help-tip"
               data-tip="Your master public key can only be used to generate payment addresses and view balances. We recommend creating a new, empty bitcoin wallet for use with this plugin."></span>
         <br>
         <input type="text"
                id="master-public-key"
                name="master-public-key"
                value=""
                placeholder="master public key"
                style="width:330px">


         <span style="color: darkred;display:none;"
               class="triplea-error-msg"
               id="wrong-pubkey-format">
			<br>
			The provided master public key does not have the right format.
			<br>
			Please provide a valid master public key (starting with 'xpub', 'ypub', 'zpub' or for testnet 'tpub').
		 </span>
      </p>
      <p>
         Provide an email to receive payment notifications:
         <br>
         <input type="text"
                id="btc-notif-email"
                name="btc-notif-email"
                value=""
                placeholder="email address"
                style="width:330px">

         <span style="color: darkred;display:none;"
               class="triplea-error-msg"
               id="wrong-or-missing-email-format">
			<br>
			Please provide a correct email address.
		 </span>
      </p>
      <br>
      <input type="button"
             class="button-primary"
             value="Activate"
             onclick="gotoBtcStep3()">
      <br>
      <br>
   </div>

   <!--receive and enter otp to validate email (bitcoin settlement)-->
   <div class="triplea_step" id="btc-step-3" style="display:none;">
      <hr>
      <br>
      <h3>
         Receive bitcoin into your own bitcoin wallet
         <br>
         <small style="line-height: 30px;">
            Validate email
         </small>
      </h3>
      <p>
         Sending OTP (one-time password) to your email address...
         <strong id="btc-email-sent" style="display:none;">Email sent!</strong>
      </p>
      <small style="color:darkred;display:none;"
             class="triplea-error-msg"
             id="btc-error-otp-request">
         Something went wrong while requesting OTP code. Try again, or contact
         us at support@triple-a.io so that we can assist you.
      </small>
      <p id="btc-enter-otp" style="display:none;">
         Enter the OTP code:
         <input type="text"
                id="btc-otp-value"
                name="btc-otp-value"
                value=""
                placeholder="One-Time Password"
                style="width:150px">
         <small style="color:darkred;display:none;"
                class="triplea-error-msg"
                id="btc-error-provide-otp">
            Please provide the OTP code that was sent to you by email.
         </small>
         <small style="color:darkred;display:none;"
                class="triplea-error-msg"
                id="btc-error-otp-wrong">
            Wrong OTP provided.
         </small>
         <br>
         <br>
         <input type="button"
                class="button-primary"
                value="Activate settlement to own bitcoin wallet"
                onclick="triplea_btc_validateEmailOtp()">
         <small id="btc-otp-check-loading"
                class="triplea-error-msg"
                style="display:none;">
            Loading...
         </small>
         <small style="color:darkred;display:none;"
                class="triplea-error-msg"
                id="cash-account-creation-error">
            The provided OTP was correct. However, something went wrong during
            the creation of your local currency wallet. Please inform us at
            support@triple-a.io so that we may assist you promptly.
         </small>
      </p>
      <br>
      <br>
   </div>

   <!--activating bitc2btc wallet, then save form (which reloads page)-->
   <div class="triplea_step" id="btc-step-4" style="display:none;">
      <hr>
      <br>
      <h3>
         Activating...
      </h3>
      <br>
      <p>
         Activating wallet... (this may take a few seconds)
      </p>
      <p id="btc-accountcreation-error"
         class="triplea-error-msg"
         style="display: none;">
         Something went wrong. Please contact
         <a href="mailto:support@triple-a.io">support@triple-a.io</a> and
         provide the following output.
         <br>
         <code id="btc-accountcreation-error-details"></code>
      </p>
      <p id="btc-account-ready" style="display:none;">
         Ready!
         <br>
         Saving and reloading page...
         <!--Now please click
         <input type="button" class="button-primary" value="Save" onclick="triplea_submitForm()">-->
      </p>
      <br>
      <br>
   </div>

   <!--receive local currency, provide email address-->
   <div class="triplea_step" id="cash-step-2" style="display:none;">
      <hr>
      <br>
      <h3>
         Receive local currency
      </h3>
      <p>
         Provide an e-mail address:
         <span class="woocommerce-help-tip"
               data-tip="Valid email address needed! We will contact you on this email address, to set up payments to your bank account."></span>
         <br>
         <input type="text"
                id="cash-notif-email"
                name="cash-notif-email"
                value=""
                placeholder="email address"
                style="width:330px">
         <br>
         <small style="color: darkred;display:none;"
                class="triplea-error-msg"
                id="wrong-cash-notif-email">
            The provided email address seems wrong.
         </small>
      </p>
      <p>
         Provide a phone number (optional):
         <span class="woocommerce-help-tip"
               data-tip="We might reach out to you on this phone number should we have trouble contacting you by email."></span>
         <br>
         <input type="tel"
                id="cash-notif-phone"
                name="cash-notif-phone"
                value=""
                placeholder="phone number"
                style="width:330px">
         <br>
         <small style="color: darkred;display:none;"
                class="triplea-error-msg"
                id="wrong-cash-notif-phone">
            The provided phone number does not have the right format.
         </small>
      </p>
      <p>
         Note:
         <br>
         Bank transfer occurs daily once minimum withdrawal threshold of USD 100
         is reached.
      </p>
      <br>
      <input type="button"
             class="button-primary"
             value="Validate email"
             onclick="gotoCashStep3()">
      <br>
      <br>
   </div>

   <!--receive and enter otp to validate email-->
   <div class="triplea_step" id="cash-step-3" style="display:none;">
      <hr>
      <br>
      <h3>
         Receive local currency
         <br>
         <small style="line-height: 30px;">
            Validate email
         </small>
      </h3>
      <p>
         Sending OTP (one-time password) to your email address...
         <strong id="cash-email-sent" style="display:none;">Email sent!</strong>
      </p>
      <small style="color:darkred;display:none;"
             class="triplea-error-msg"
             id="cash-error-otp-request">
         Something went wrong while requesting OTP code. Try again, or contact
         us at support@triple-a.io so that we can assist you.
      </small>
      <p id="cash-enter-otp" style="display:none;">
         Enter the OTP code:
         <input type="text"
                id="cash-otp-value"
                name="cash-otp-value"
                value=""
                placeholder="One-Time Password"
                style="width:150px">
         <small style="color:darkred;display:none;"
                class="triplea-error-msg"
                id="cash-error-provide-otp">
            Please provide the OTP code that was sent to you by email.
         </small>
         <small style="color:darkred;display:none;"
                class="triplea-error-msg"
                id="cash-error-otp-wrong">
            Wrong OTP provided.
         </small>
         <br>
         <br>
         <input type="button"
                class="button-primary"
                value="Activate local currency wallet"
                onclick="triplea_fiat_validateEmailOtp()">
         <small id="cash-otp-check-loading"
                class="triplea-error-msg"
                style="display:none;">
            Loading...
         </small>
         <small style="color:darkred;display:none;"
                class="triplea-error-msg"
                id="cash-account-creation-error">
            The provided OTP was correct. However, something went wrong during
            the creation of your local currency wallet. Please inform us at
            support@triple-a.io so that we may assist you promptly.
         </small>
      </p>
      <br>
      <br>
   </div>

   <script src="https://unpkg.com/libphonenumber-js@1.7.56/bundle/libphonenumber-max.js"></script>
   <script>
     (function ($) {
       $(document).ready(function () {
         // detect "receive .." choice, set the correct radiobutton selection
         $('[name="receive-choice"]').change(function () {
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
         });

         let sandboxPaymentModeNode   = document.getElementById(settingsPrefix + '_' + 'triplea_sandbox_payment_mode');
         let sandbox = sandboxPaymentModeNode.value;
         // php's sandbox_payment_mode value = <?php echo $sandbox_payment_mode; ?>.
         if ( !!(sandbox) !== !!(<?php echo (!isset($sandbox_payment_mode) || empty($sandbox_payment_mode) ? "false" : "'true'"); ?>) )
         {
           console.debug('Forcing page reload, outdated options values detected.');
           window.location.reload(true);
         }
         
       });
     })(jQuery);
     

     /**
      * Shared helper functions
      */
     function triplea_getemailotp(email, callback, errorCallback)
     {
       console.debug('Requesting OTP for ' + email);
       // TODO request OTP for email >> then call Callback >> if error, ErrorCallback()

       callback();
     }
   </script>
   <script>

     function gotoStep1()
     {
       //triplea_hideAllSteps();
       triplea_helper_displayNode('step-1');
     }

     function gotoStep2(choice)
     {
       if (choice !== 'bitcoin' && choice !== 'localcurrency')
       {
         var selector = document.querySelector('input[name="receive-choice"]:checked');
         if (!selector)
         {
           // todo display message or just do nothing
           return;
         }
         choice = selector.value;
       }

       // else
       if (choice === 'bitcoin')
       {
         let apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
         let apiId     = apiIdNode.value;

         if (apiId)
         {
           console.debug("Existing BTC wallet. Setting Active API ID (BTC) to " + apiId);
           triplea_setActiveAccount('btc2btc', true); // sandbox enabled by default
           // triplea_submitForm();
         }
         else
         {
           console.debug("No existing BTC wallet found. Showing step 2 for BTC.");
           gotoBtcStep2();
         }
       }
       if (choice === 'localcurrency')
       {
         let apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
         let apiId     = apiIdNode.value;

         if (apiId)
         {
           console.debug("Setting Active API ID (TripleA Wallet) to " + apiId);
           triplea_setActiveAccount('btc2fiat', true); // sandbox enabled by default
           // triplea_submitForm();
         }
         else
         {
           console.debug("No existing TripleA wallet found. Showing step 2 for TripleA wallet.");
           gotoCashStep2();
         }
       }
     }

     function gotoBtcStep2()
     {
       triplea_hideAllSteps();
       triplea_helper_displayNode('btc-step-2');
     }

     function gotoBtcStep3()
     {
       // Verify form input first

       let pubkey, apiId, apiIdNode;
       // Update wallet, if API ID set.
       // Else request API ID for pubkey.
       let pubkeyNode = document.getElementById('master-public-key');
       pubkey         = pubkeyNode.value ? pubkeyNode.value : '';
       if (!pubkey)
       {
         console.warn('No public key provided. Cannot proceed.');
         triplea_helper_displayNode('wrong-pubkey-format');
         return;
       }
       if (pubkey.indexOf('pub') !== 1)
       {
         console.warn('Incorrect public key format. Cannot proceed.');
         triplea_helper_displayNode('wrong-pubkey-format');
         return;
       }
       console.debug("Received master public key : " + pubkey);


       let notifEmail;
       // Get notification email (optional)
       let notifEmailNode = document.getElementById('btc-notif-email');
       notifEmail         = notifEmailNode.value;

       if (!notifEmail ||
         (notifEmail.indexOf('@') < 1 || notifEmail.lastIndexOf('.') < notifEmail.indexOf('@')))
       {
         console.warn('Incorrect notification email. Cannot proceed.');
         triplea_helper_displayNode('wrong-or-missing-email-format');
         return;
       }
       let hiddenNotifEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
       hiddenNotifEmailNode.value = notifEmail;


       apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
       if (apiIdNode && apiIdNode.value)
       {
         apiId = apiIdNode.value;
         console.debug("Found existing API ID : " + apiId);
       }
       else
       {
         console.debug("Found no pre-existing API ID");
       }

       triplea_hideAllErrors();

       // Take action
       triplea_hideAllSteps();
       triplea_helper_displayNode('btc-step-3');

       // triplea_btc_createMerchantAccount();
     }

     function updateBtcStep3()
     {
       //triplea_helper_displayNode('btc-account-ready');
     }

     function gotoCashStep2()
     {
       triplea_hideAllSteps();
       triplea_helper_displayNode('cash-step-2');
     }

     function gotoCashStep3()
     {
       let notifEmail;

       triplea_hideAllErrors();

       // validate email
       let notifEmailNode = document.getElementById('cash-notif-email');
       if (notifEmailNode)
       {
         notifEmail = notifEmailNode.value;
       }
       if (!notifEmail || (notifEmail.indexOf('@') < 1 || notifEmail.lastIndexOf('.') < notifEmail.indexOf('@')))
       {
         console.warn('Incorrect email provided. Cannot proceed.');
         triplea_helper_displayNode('wrong-cash-notif-email');
         return;
       }


       // validate phone number
       let notifPhone     = '', phoneNumber;
       let notifPhoneNode = document.getElementById('cash-notif-phone');
       if (notifPhoneNode && notifPhoneNode.value)
       {
         notifPhone = notifPhoneNode.value;
         window.console.debug('phone number validation: ...');
         phoneNumber = libphonenumber.parsePhoneNumberFromString(notifPhone);
         if (phoneNumber.isValid())
         {
           window.console.debug('phone number validation: is valid');
           notifPhone = phoneNumber.formatInternational();
         }
         else
         {
           window.console.debug('phone number validation: is not valid');
           notifPhone = '';
           console.warn('Incorrect phone number format. Cannot proceed.');
           triplea_helper_displayNode('wrong-cash-notif-phone');
           return;
         }
       }


       // TODO need different notification email for TripleA wallet and bitcoin wallet !
       //let hiddenNotifEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
       let hiddenNotifEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_email');
       hiddenNotifEmailNode.value = notifEmail;
       let hiddenNotifPhoneNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_merchant_phone');
       hiddenNotifPhoneNode.value = notifPhone;

       triplea_hideAllSteps();
       triplea_helper_displayNode('cash-step-3');

       // send otp
       //  then check otp
       //   then create/update account
       triplea_fiat_createMerchantAccount();
     }

     function updateBtcStep4()
     {
       triplea_helper_displayNode('cash-account-ready');
     }
   </script>

   <br>

   <script>
     const return_url     = '<?php echo $return_url; ?>';
     const local_currency = '<?php echo $local_currency; ?>';
     // noinspection JSAnnotator
     const site_info      = <?php echo json_encode($info_data); ?>;
     const settingsPrefix = 'woocommerce_triplea_payment_gateway';
     let otpRequestEmail, otpRequestNotifEmail;

     let merchantAccountRequestResult;

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
       // let errors = document.getElementsByClassName('triplea-error-msg');
       // for (let i = 0; errors && i < errors.length; i++)
       // {
       //   errors[i].style.display = 'none';
       // }
     }

     /**
      * We validate form input first.
      * Then make an API request for an OTP to be sent to the
      * provided email address.
      * A callback function will handle the display of the next step.
      */
     function _OLD_triplea_btc_createMerchantAccount()
     {
       let notifEmail;

       // First verify if the provided inputs are correct.
       // Check the master public key, first.
       let pubkeyNode = document.getElementById('master-public-key');
       let pubkey     = pubkeyNode.value ? pubkeyNode.value : '';
       if (!pubkey)
       {
         // already checked and handled before, no output or logging.. this is just fallback
         return;
       }
       if (pubkey.indexOf('pub') !== 1)
       {
         // already checked and handled before, no output or logging.. this is just fallback
         return;
       }

       // Check the notification email (optional)
       let notifEmailNode = document.getElementById('btc-notif-email');
       notifEmail         = notifEmailNode.value;

       if (!notifEmail || notifEmail.indexOf('@') < 1 || notifEmail.lastIndexOf('.') < notifEmail.indexOf('@'))
       {
         // already checked and handled before, no output or logging.. this is just fallback
         return;
       }
       triplea_hideAllErrors();

       let hiddenNotifEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
       hiddenNotifEmailNode.value = notifEmail;
       let dashboardEmail         = notifEmail;

       // Remember for which email the OTP was last requested.
       otpRequestEmail      = dashboardEmail;
       otpRequestNotifEmail = notifEmail; // TODO need different Notification emails stored, for bitcoin or localcurrency !

       // Ask for OTP to be sent to email address.

       const url      = `https://moneyoverip.io/api/send_otp`;
       const callback = triplea_btc_createMerchantAccountCallback;
       const method   = "POST";
       const data     = {
         email: dashboardEmail
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data));
     }

     /**
      * After asking for an OTP to be sent to the indicated email,
      * we display the next step where the user can enter the OTP.
      * We verify if the OTP is correct via API call.
      * The callback (if successful) will then activate
      * the user's bitcoin wallet account and save it.
      */
     function _OLD_triplea_btc_createMerchantAccountCallback(result)
     {
       if (result.status !== "OK")
       {
         console.error('ERROR. Problem requesting OTP for email validation.');
         // TODO display error
         return;
       }

       triplea_helper_displayNode("btc-email-sent");
       triplea_helper_displayNode("btc-enter-otp");
     }

     function _OLD_triplea_btc_validateEmailOtp()
     {
       let dashboardEmail;
       if (otpRequestEmail)
       {
         console.debug('otpRequestEmail: ', otpRequestEmail);
         dashboardEmail = otpRequestEmail;
       }

       let otpNode = document.getElementById('btc-otp-value');
       let otp     = otpNode.value;
       if (!otp)
       {
         triplea_helper_displayNode('btc-error-provide-otp');
         return;
       }
       triplea_hideAllErrors();
       triplea_helper_displayNode('btc-otp-check-loading');

       const url      = `https://moneyoverip.io/api/check_otp`;
       const callback = triplea_btc_validateEmailOtpCallback;
       const method   = "POST";
       const data     = {
         email: dashboardEmail,
         otp: otp
       };

       triplea_ajax_action(url, callback, method, JSON.stringify(data));
     }

     function _OLD_triplea_btc_validateEmailOtpCallback(result)
     {
       if (result.status !== "OK" || !result.token)
       {
         triplea_hideAllErrors();
         triplea_helper_displayNode('btc-error-otp-wrong');
         return;
       }
       let authToken = result.token;

       let pubkey, apiId, apiIdNode;

       // Update wallet, if API ID set.
       // Else request API ID for pubkey.
       let pubkeyNode = document.getElementById('master-public-key');
       pubkey         = pubkeyNode.value ? pubkeyNode.value : '';
       if (!pubkey)
       {
         // already checked and handled before, no output or logging.. this is just fallback
         return;
       }
       if (pubkey.indexOf('pub') !== 1)
       {
         // already checked and handled before, no output or logging.. this is just fallback
         return;
       }

       apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
       if (apiIdNode && apiIdNode.value)
       {
         apiId = apiIdNode.value;
       }
       else
       {
         // already checked and handled before, no output or logging.. this is just fallback
       }

       let notifEmail = otpRequestNotifEmail || null;

       const data = {
         currency: 'BTC',
         local_currency: local_currency,
         notification_email: notifEmail,
         return_url: return_url,
         account_name: window.location.hostname,
         account_type: 'ecommerce',
         info: site_info,
         auth_token: authToken,
         // no wallet_type, autodetect
       };
       if (pubkey)
       {
         data.pub = pubkey;
       }
       if (apiId)
       {
         data.api_id = apiId;
       }

       triplea_addPub(data, triplea_btc_addPubCallback);
     }

     function _OLD_triplea_btc_addPubCallback(result)
     {

       if (result && result.return === "201")
       {
         console.warn('Something went wrong. Wallet could not be updated. Raw data: ', result);

         let errorDetailsNode       = document.getElementById("btc-accountcreation-error-details");
         errorDetailsNode.innerHTML = JSON.stringify(result);
         triplea_helper_displayNode('btc-accountcreation-error');
         return;
       }
       if (!result || result.status !== "OK" || !result.api_id || !result.server_public_key)
       {
         console.warn('Something went wrong. Wallet could not be updated. Missing required information. Raw data: ', result);

         let errorDetailsNode       = document.getElementById("btc-accountcreation-error-details");
         errorDetailsNode.innerHTML = JSON.stringify(result);
         triplea_helper_displayNode('btc-accountcreation-error');
         return;
       }

       /**
        *  add_pub API call succeeded.
        */
         // console.debug("Account creation/update API call succeeded:");
         // console.debug('API ID            : ', result.api_id);
         // console.debug('Server public key : ', result.server_public_key);

         // Set hidden value: server_public_key.
       let encKeyNode;
       encKeyNode       = document.getElementById(settingsPrefix + '_' + 'triplea_server_public_enc_key_btc');
       encKeyNode.value = result.server_public_key;
       //console.debug("Set hidden value: server_public_key.", encKeyNode.value);

       // Set hidden value: API ID.
       let apiIdNode;
       apiIdNode       = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
       apiIdNode.value = result.api_id;
       //console.debug("Set hidden value: API ID.", apiIdNode.value);

       triplea_hideAllErrors();
       updateBtcStep3();

       console.debug('Setting active API ID to External bitcoin wallet ' + result.api_id);
       // Sets active account then submits form.
       triplea_setActiveAccount('btc2btc');
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
         console.debug('Incorrect TripleA wallet email. Cannot proceed.');
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
       console.error('ERROR. Problem requesting OTP for email validation.');
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
         window.console.warn('TripleA Warning: missing merchant account data.');
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

         if (acc.crypto_currency === 'BTC' && !acc.sandbox)
         {
           // TODO rename this new variable where needed everywhere in the code
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
           apiIdNode.value = acc.api_id;
         }
         else if (acc.crypto_currency === 'testBTC' && acc.sandbox)
         {
           // TODO create this new variable where needed all throughout the code
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }


       }

       // Sets active account then submits form
       let sandbox = true; // by default start in sandbox mode
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
         console.warn('No public key provided. Cannot proceed.');
         jQuery('#testnetbitcoin-wrong-pubkey-format').show();
         return;
       }
       if (pubkey.indexOf('pub') !== 1 || !(pubkey.indexOf('tpub') === 0))
       {
         console.warn('Incorrect public key format. Cannot proceed.');
         jQuery('#testnetbitcoin-wrong-pubkey-format').show();
         return;
       }
       console.debug("Received master public key : " + pubkey);
       window.testbitcoin_pubkey = pubkey;

       // Do email validation.
       let testnetBitcoinEmail;
       let testnetBitcoinEmailNode = document.getElementById('testnetbitcoin-notif-email');
       testnetBitcoinEmail         = testnetBitcoinEmailNode.value;
       if (!testnetBitcoinEmail ||
         (testnetBitcoinEmail.indexOf('@') < 1 || testnetBitcoinEmail.lastIndexOf('.') < testnetBitcoinEmail.indexOf('@')))
       {
         console.warn('Incorrect notification email. Cannot proceed.');
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
         console.error('Incorrect email. Cannot proceed.');
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
       console.error('Error with merchant account creation.', err);
       jQuery('#testnetbitcoin-error-pubkey-wrong-email').show();
     }

     /**
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_testnetbitcoin_createMerchantAccountCallback(result)
     {
       jQuery('#receive-testnetbitcoin-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.debug('Prepared account creation. Requested OTP code. Pending OTP validation..');
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

       console.debug('triplea_testnetbitcoin_validateEmailOtpCallback() : result = ', result);
       console.debug('triplea_testnetbitcoin_validateEmailOtpCallback() : merchantAccountRequestResult = ', merchantAccountRequestResult);

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
         if (acc.crypto_currency === 'testBTC' && acc.sandbox)
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
         console.warn('No public key provided. Cannot proceed.');
         jQuery('#bitcoin-wrong-pubkey-format').show();
         return;
       }
       if (pubkey.indexOf('pub') !== 1 || (pubkey.indexOf('xpub') !== 0 && pubkey.indexOf('ypub') !== 0 && pubkey.indexOf('zpub') !== 0))
       {
         console.warn('Incorrect public key format. Cannot proceed.');
         jQuery('#bitcoin-wrong-pubkey-format').show();
         return;
       }
       console.debug("Received master public key : " + pubkey);
       window.bitcoin_pubkey = pubkey;

       // Do email validation.
       let bitcoinEmail;
       let bitcoinEmailNode = document.getElementById('bitcoin-notif-email');
       bitcoinEmail         = bitcoinEmailNode.value;
       if (!bitcoinEmail ||
         (bitcoinEmail.indexOf('@') < 1 || bitcoinEmail.lastIndexOf('.') < bitcoinEmail.indexOf('@')))
       {
         console.warn('Incorrect notification email. Cannot proceed.');
         jQuery('#bitcoin-wrong-notif-email').show();
         return;
       }
       // TODO ? the below code should be done upon successful account creation, not before..
       let hiddenBitcoinEmailNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_email');
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
       let merchantEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_email');
       merchantEmail         = merchantEmailNode.value;

       if (!window.bitcoin_pubkey || window.bitcoin_pubkey === '') {
         console.error('Incorrect master public key. Cannot proceed.');
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
         master_pubkey: window.bitcoin_pubkey,
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
       console.error('Error with merchant account creation.', err);
       jQuery('#bitcoin-error-pubkey-wrong-email').show();
     }

     /**
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_bitcoin_createMerchantAccountCallback(result)
     {
       jQuery('#receive-bitcoin-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.debug('Prepared account creation. Requested OTP code. Pending OTP validation..');
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

       console.debug('triplea_bitcoin_validateEmailOtpCallback() : result = ', result);
       console.debug('triplea_bitcoin_validateEmailOtpCallback() : merchantAccountRequestResult = ', merchantAccountRequestResult);

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

       let hiddenNodeMerchantKey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_key');
       hiddenNodeMerchantKey.value = merchantAccountRequestResult.merchant_key;

       let hiddenNodeClientId   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_client_id');
       hiddenNodeClientId.value = merchantAccountRequestResult.client_id;

       let hiddenNodeClientSecret   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_client_secret');
       hiddenNodeClientSecret.value = merchantAccountRequestResult.client_secret;

       let hiddenNodeMerchantName   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_name');
       hiddenNodeMerchantName.value = merchantAccountRequestResult.name || '-missing name-';

       let hiddenNodeMerchantPhone   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_merchant_phone');
       hiddenNodeMerchantPhone.value = merchantAccountRequestResult.phone || '';

       let hiddenNodePubkey   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_pubkey');
       hiddenNodePubkey.value = window.bitcoin_pubkey || '';

       for (let i = 0; i < merchantAccountRequestResult.accounts.length; ++i)
       {
         const acc = merchantAccountRequestResult.accounts[i];

         if (acc.crypto_currency === 'BTC' && !acc.sandbox)
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_api_id');
           apiIdNode.value = acc.api_id;
         }
         // if (acc.crypto_currency === 'testBTC' && acc.sandbox)
         // {
         //   let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2btc_sandbox_api_id');
         //   apiIdNode.value = acc.api_id;
         // }
       }

       // Sets active account then submits form
       let sandbox = false;
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
         console.warn('Incorrect email. Cannot proceed.');
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
       console.error('Error with merchant account creation.', err);
       jQuery('#localcurrency-error-pubkey-wrong-email').show();
     }

     /**
      *  OTP email sent confirmation. Enable OTP input and submit button.
      */
     function triplea_localcurrency_createMerchantAccountCallback(result)
     {
       jQuery('#receive-localcurrency-validate-email').fadeTo(0, 1);
       triplea_hideAllErrors();
       console.debug('Prepared account creation. Requested OTP code. Pending OTP validation..');
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

       console.debug('triplea_localcurrency_validateEmailOtpCallback() : result = ', result);
       console.debug('triplea_localcurrency_validateEmailOtpCallback() : merchantAccountRequestResult = ', merchantAccountRequestResult);

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

         if (acc.crypto_currency === 'BTC' && !acc.sandbox)
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
           apiIdNode.value = acc.api_id;
         }
         if (acc.crypto_currency === 'testBTC' && acc.sandbox)
         {
           let apiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_sandbox_api_id');
           apiIdNode.value = acc.api_id;
         }
       }

       // Sets active account then submits form
       let sandbox = true;
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
         console.debug('Clicking Form Submit button');

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
       console.debug('_setActiveAccount() for ' + walletType + ' ' + (sandbox ? 'testnet' : ''));
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
             console.warn('Cannot change active account. Target account not found.');
             return;
           }
           break;
         case 'btc2fiat':
           console.debug('switching btc2fiat sandbox from ' + !sandbox + ' to ' + (sandbox) + '.');
           apiIdNode = sandbox
             ? document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_sandbox_api_id')
             : document.getElementById(settingsPrefix + '_' + 'triplea_btc2fiat_api_id');
           if (apiIdNode.value !== '')
           {
             console.debug('using ' + apiIdNode.value + ' as target.');
             apiId = apiIdNode.value;
           }
           else
           {
             console.warn('Cannot change active account. Target account not found.');
             return;
           }
           break;
       }

       let activeApiIdNode   = document.getElementById(settingsPrefix + '_' + 'triplea_active_api_id');
       activeApiIdNode.value = apiId;
       console.debug('active API ID value set to ' + apiId);

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
             console.log('Error parsing JSON response. ' + err.message + " in " + xmlhttp.responseText);
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

   <section>
   <pre><?php
   
      echo print_r(get_option('woocommerce_triplea_payment_gateway_settings'), TRUE);
   
      ?></pre>
   </section>

<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
