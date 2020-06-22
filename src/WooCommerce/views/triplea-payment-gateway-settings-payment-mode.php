<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$triplea_notifications_email = $this->get_option( 'triplea_notifications_email' );
if ( empty( $triplea_notifications_email ) ) {
	$triplea_notifications_email = '';
}

$triplea_server_public_enc_key_btc = $this->get_option( 'triplea_server_public_enc_key_btc' );
if ( empty( $triplea_server_public_enc_key_btc ) ) {
	$triplea_server_public_enc_key_btc = '';
}
$triplea_server_public_enc_key_conversion = $this->get_option( 'triplea_server_public_enc_key_conversion' );
if ( empty( $triplea_server_public_enc_key_conversion ) ) {
	$triplea_server_public_enc_key_conversion = '';
}

$triplea_dashboard_email = $this->get_option( 'triplea_dashboard_email' );
if ( empty( $triplea_dashboard_email ) ) {
	$triplea_dashboard_email = '';
}

// BTC-2-BTC mode

$triplea_pubkey_id = $this->get_option( 'triplea_pubkey_id' );
$output_pubkey_id  = '';
if ( ! empty( $triplea_pubkey_id ) ) {
	$output_pubkey_id = '<p>Your bitcoin-to-bitcoin API ID is <strong><u>' . $triplea_pubkey_id . '</u></strong> .</p><br>';
}

// BTC-2-Cash conversion mode

$triplea_pubkey_id_for_conversion = $this->get_option( 'triplea_pubkey_id_for_conversion' );
$output_conversion_pubkey_id      = '';
if ( ! empty( $triplea_pubkey_id_for_conversion ) ) {
	$output_conversion_pubkey_id = '<p>Your bitcoin-to-local currency API ID is <strong><u>' . $triplea_pubkey_id_for_conversion . '</u></strong> .</p><br>';
}

// Only if no need for upgrade..
$btc2cash_is_active       = false;
$btc2btc_is_active        = false;
$triplea_active_pubkey_id = $this->get_option( 'triplea_active_pubkey_id' );
$btc2cash_is_active       = ( ! empty( $triplea_active_pubkey_id ) && $triplea_active_pubkey_id == $triplea_pubkey_id_for_conversion );
$btc2btc_is_active        = ( ! empty( $triplea_active_pubkey_id ) && $triplea_active_pubkey_id == $triplea_pubkey_id );

$return_url     = get_rest_url( null, 'triplea/v1/tx_update/' . get_option( 'triplea_api_endpoint_token' ) );
$local_currency = strtoupper( get_woocommerce_currency() );
$site_info      = get_bloginfo( 'name' );

// xpub6BnuMHhi8VMaHsm7PFhSDSDPPiA8gKgdJLX3nwHZ2hrEtJAPfXWVCn61R3aBJKZV7AtcuUowDh8hgUUTTssKpB1hgX7XK35v4HxzKUpKsHC

$debug_log_enabled = 'yes' === $this->get_option( 'debug_log_enabled' );
$info_data         = array(
	'type'      => 'woocommerce',
	'name'      => substr( get_bloginfo( 'name' ), 0, 100 ),
	'url'       => substr( get_bloginfo( 'url' ), 0, 250 ),
	'admin'     => substr( get_bloginfo( 'admin_email' ), 0, 60 ),
	'wp_v'      => substr( get_bloginfo( 'version' ), 0, 15 ),
	'lang'      => substr( get_bloginfo( 'language' ), 0, 8 ),
	'plugin_v'  => TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION,
	'debug_log' => $debug_log_enabled ? 1 : 0,
);

ob_start();
?>
   <style>

	  /* Fade-In Effect */
	  .visible
	  {
		 visibility: visible;
		 opacity: 1;
		 transition: opacity 0.5s linear;
	  }
	  /* Fade-Out Effect */
	  .hidden
	  {
		 visibility: hidden;
		 opacity: 0;
		 transition: visibility 0s 0.5s, opacity 0.5s linear;
	  }

	  .triplea_card {
		 background-image: linear-gradient(-200deg, white 45%, rgba(90,0,190,0.01) 100%);
		 box-shadow: 0 3px 6px 0 rgba(0,0,0,0.16);
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
		 background: rgba(0,0,0,0.1);
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

   <div class="triplea_step" id="step-enabled" style="display:
   <?php
	if ( $btc2cash_is_active || $btc2btc_is_active ) {
		echo 'block';
	} else {
		echo 'none'; }
	?>
	">
	  <hr>
	  
	  <br>
	  <h3>Bitcoin payments are enabled.</h3>
	  <p id="btc2btc-message" style="display:
	  <?php
		if ( $btc2btc_is_active ) {
			echo 'block';
		} else {
			echo 'none'; }
		?>
		">
		 You have chosen to receive bitcoin payments <br>
		 straight <strong>into your own bitcoin wallet</strong>.
	  </p>
	  <p id="btc2cash-message" style="display:
	  <?php
		if ( $btc2cash_is_active ) {
			echo 'block';
		} else {
			echo 'none'; }
		?>
		">
		 You have chosen to receive bitcoin payments <br>
		 in the form of <strong>local currency</strong> sent to <strong>your bank account</strong>
		 <span class="woocommerce-help-tip" data-tip="Once you start receiving payments, we will contact you to obtain your bank account information to prepare payouts."></span>.
	  </p>
	  <br>
	  <br>
	  <div id="btc2btc-action-buttons" style="display:
	  <?php
		if ( $btc2btc_is_active ) {
			echo 'block';
		} else {
			echo 'none'; }
		?>
		">
		 <input type="button" class="button-primary" value="Update bitcoin wallet" onclick="gotoBtcStep2()">
		 <input type="button" class="button-primary" value="Receive local currency in bank account" onclick="gotoStep2('localcurrency')"><br>
	  </div>
	  <div id="btc2cash-action-buttons" style="display:
	  <?php
		if ( $btc2cash_is_active ) {
			echo 'block';
		} else {
			echo 'none'; }
		?>
		">
		 <!--<input type="button" class="button-primary" value="Update local currency wallet" onclick="gotoCashStep2()">-->
		 <input type="button" class="button-primary" value="Receive bitcoin in own wallet" onclick="gotoStep2('bitcoin')"><br>
	  </div>
	  <br>
	  <br>
   </div>

   <div class="triplea_step" id="step-1" style="display:
   <?php
	if ( ! $btc2cash_is_active && ! $btc2btc_is_active ) {
		echo 'block';
	} else {
		echo 'none'; }
	?>
	">
	  <hr>
	  <br>
	  <h3>Get started</h3>
	  <p>
		 How do you wish to receive bitcoin payments?
	  </p>
	  <input type="radio" name="receive-choice" value="bitcoin" 
	  <?php
		if ( $btc2btc_is_active ) {
			echo 'checked'; }
		?>
		 style="margin:10px;">receive bitcoin<br>
	  <input type="radio" name="receive-choice" value="localcurrency" 
	  <?php
		if ( $btc2cash_is_active ) {
			echo 'checked'; }
		?>
		 style="margin:10px;">receive local currency<br>
	  <br>
	  <input type="button" class="button-primary" value="Next" onclick="gotoStep2()">
	  <br>
	  <br>
   </div>

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
		 <span class="woocommerce-help-tip" data-tip="Your master public key can only be used to generate payment addresses and view balances. We recommend creating a new, empty bitcoin wallet for use with this plugin."></span>
		 <br>
		 <input type="text" id="master-public-key" name="master-public-key" value="" placeholder="master public key" style="width:330px">
   
   
		 <span style="color: darkred;display:none;" class="triplea-error-msg" id="wrong-pubkey-format">
			<br>
			The provided master public key does not have the right format.
			<br>
			Please provide a valid master public key (starting with 'xpub', 'ypub', 'zpub' or for testnet 'tpub').
		 </span>
	  </p>
	  <p>
		 Provide an email to receive payment notifications:
		 <br>
		 <input type="text" id="btc-notif-email" name="btc-notif-email" value="" placeholder="email address"  style="width:330px">
   
		 <span style="color: darkred;display:none;" class="triplea-error-msg" id="wrong-or-missing-email-format">
			<br>
			Please provide a correct email address.
		 </span>
	  </p>
	  <br>
	  <input type="button" class="button-primary" value="Activate" onclick="gotoBtcStep3()">
	  <br>
	  <br>
   </div>

   <div class="triplea_step" id="btc-step-3" style="display:none;">
	  <hr>
	  <br>
	  <h3>
		 Activating...
	  </h3>
	  <br>
	  <p>
		 Activating wallet... (this may take a few seconds)
	  </p>
	  <p id="btc-accountcreation-error" class="triplea-error-msg" style="display: none;">
		 Something went wrong. Please contact <a href="mailto:support@triple-a.io">support@triple-a.io</a> and provide the following output.
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


   <div class="triplea_step" id="cash-step-2" style="display:none;">
	  <hr>
	  <br>
	  <h3>
		 Receive local currency
	  </h3>
	  <p>
		 Provide an e-mail address:
		 <span class="woocommerce-help-tip" data-tip="We will contact you on this email address, to set up payments to your bank account."></span>
		 <br>
		 <input type="text" id="cash-notif-email" name="cash-notif-email" value="" placeholder="email address"  style="width:330px">
		 <br>
		 <small style="color: darkred;display:none;" class="triplea-error-msg" id="wrong-cash-notif-email">
			The provided master public key does not have the right format.
		 </small>
	  </p>
	  <p>
		 Note:
		 <br>
		 Bank transfer occurs daily once minimum withdrawal threshold of USD 100 is reached.
	  </p>
	  <br>
	  <input type="button" class="button-primary" value="Validate email" onclick="gotoCashStep3()">
	  <br>
	  <br>
   </div>

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
		 Sending OTP (one-time password) to your email address... <strong id="cash-email-sent" style="display:none;">Email sent!</strong>
	  </p>
	  <small style="color:darkred;display:none;" class="triplea-error-msg" id="error-otp-request">
		 Something went wrong while requesting OTP code. Try again, or contact us at support@triple-a.io so that we can assist you.
	  </small>
	  <p id="cash-enter-otp" style="display:none;">
		 Enter the OTP code:
		 <input type="text" id="cash-otp-value" name="cash-otp-value" value="" placeholder="One-Time Password"  style="width:150px">
		 <small style="color:darkred;display:none;" class="triplea-error-msg" id="error-provide-otp">
			Please provide the OTP code that was sent to you by email.
		 </small>
		 <small style="color:darkred;display:none;" class="triplea-error-msg" id="error-otp-wrong">
			Wrong OTP provided.
		 </small>
		 <br>
		 <br>
		 <input type="button" class="button-primary" value="Activate local currency wallet" onclick="triplea_fiat_validateEmail()">
		 <small id="otp-check-loading" class="triplea-error-msg" style="display:none;">
			Loading...
		 </small>
		 <small style="color:darkred;display:none;" class="triplea-error-msg" id="cash-account-creation-error">
			The provided OTP was correct. However, something went wrong during the creation of your local currency wallet. Please inform us at support@triple-a.io so that we may assist you promptly.
		 </small>
	  </p>
	  <br>
	  <br>
   </div>
<!---->
<!--   <div>-->
<!--      <hr>-->
<!--      <br>-->
<!--      <p>-->
<!--         <small>-->
<!--            If you have feedback or questions, reach out to us at-->
<!--            <a href="mailto:support@triple-a.io">support@triple-a.io</a>.-->
<!--            <br>-->
<!--            We will respond within 24 hours.-->
<!--         </small>-->
<!--      </p>-->
<!--      <br>-->
<!--      <br>-->
<!--   </div>-->

<script>
   
   function gotoStep1() {
	 triplea_hideAllSteps();
	 triplea_helper_displayNode('step-1');
   }
   
   function gotoStep2(choice) {
	 if (choice !== 'bitcoin' && choice !== 'localcurrency') {
	   var selector = document.querySelector('input[name="receive-choice"]:checked');
	   if(!selector) {
		 // todo display message or just do nothing
		 return;
	   }
	   choice = selector.value;
	 }

	 // else
	 if (choice === 'bitcoin') {

	   let apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id');
	   let apiId = apiIdNode.value;
	   
	   if (apiId) {
		 console.debug("Existing BTC wallet. Setting Active API ID (BTC) to " + apiId);
		 triplea_setActiveAccount('btc2btc');
		 triplea_submitForm();
	   }
	   else {
		 console.debug("No existing BTC wallet found. Showing step 2 for BTC.");
		 gotoBtcStep2();
	   }
	 }
	 if (choice === 'localcurrency') {

	   let apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id_for_conversion');
	   let apiId = apiIdNode.value;
	   
	   if (apiId) {
		 console.debug("Setting Active API ID (TripleA Wallet) to " + apiId);
		 triplea_setActiveAccount('btc2cash');
		 triplea_submitForm();
	   }
	   else {
		 console.debug("No existing TripleA wallet found. Showing step 2 for TripleA wallet.");
		 gotoCashStep2();
	   }
	 }
   }
   
   function gotoBtcStep2() {
	 triplea_hideAllSteps();
	 triplea_helper_displayNode('btc-step-2');
   }

   function gotoBtcStep3() {
	 // Verify form input first
	 
	 let pubkey, apiId, apiIdNode;
	 // Update wallet, if API ID set.
	 // Else request API ID for pubkey.
	 let pubkeyNode = document.getElementById('master-public-key');
	 pubkey = pubkeyNode.value ? pubkeyNode.value : '';
	 if (!pubkey) {
	   console.warn('No public key provided. Cannot proceed.');
	   triplea_helper_displayNode('wrong-pubkey-format');
	   return;
	 }
	 if (pubkey.indexOf('pub') !== 1) {
	   console.warn('Incorrect public key format. Cannot proceed.');
	   triplea_helper_displayNode('wrong-pubkey-format');
	   return;
	 }
	 console.debug("Received master public key : " + pubkey);

	 
	 let notifEmail;
	 // Get notification email (optional)
	 let notifEmailNode = document.getElementById('btc-notif-email');
	 notifEmail = notifEmailNode.value;

	 if (!notifEmail ||
	   (notifEmail.indexOf('@') < 3 || notifEmail.lastIndexOf('.') < notifEmail.indexOf('@')))
	 {
	   console.warn('Incorrect notification email. Cannot proceed.');
	   triplea_helper_displayNode('wrong-or-missing-email-format');
	   return;
	 }
	 let hiddenNotifEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
	 hiddenNotifEmailNode.value = notifEmail;


	 apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id');
	 if (apiIdNode && apiIdNode.value) {
	   apiId = apiIdNode.value;
	   console.debug("Found existing API ID : " + apiId);
	 }
	 else {
	   console.debug("Found no pre-existing API ID");
	 }
	 
	 triplea_hideAllErrors();
	 
	 // Take action
	 triplea_hideAllSteps();
	 triplea_helper_displayNode('btc-step-3');

	 triplea_btc_updateAccount();
   }
   
   function updateBtcStep3() {
	 triplea_helper_displayNode('btc-account-ready');
   }

   function gotoCashStep2() {
	 triplea_hideAllSteps();
	 triplea_helper_displayNode('cash-step-2');
   }

   function gotoCashStep3() {
	 
	 triplea_hideAllErrors();
	 
	 // validate email
	 let notifEmailNode = document.getElementById('cash-notif-email');
	 if (notifEmailNode)
	 {
	   notifEmail = notifEmailNode.value;
	 }
	 if (!notifEmail || (notifEmail.indexOf('@') < 3 || notifEmail.lastIndexOf('.') < notifEmail.indexOf('@')))
	 {
	   console.warn('Incorrect email provided. Cannot proceed.');
	   triplea_helper_displayNode('wrong-cash-notif-email');
	   return;
	 }
	 
	 // TODO need different notification email for TripleA wallet and bitcoin wallet !
	 let hiddenNotifEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
	 hiddenNotifEmailNode.value = notifEmail;
	 
	 triplea_hideAllSteps();
	 triplea_helper_displayNode('cash-step-3');
	 
	 // send otp
	 //  then check otp
	 //   then create/update account
	 triplea_fiat_updateAccount();
   }

   function updateBtcStep3() {
	 triplea_helper_displayNode('cash-account-ready');
   }
</script>

   <br>

   <script>
	 const return_url = '<?php echo $return_url; ?>';
	 const local_currency = '<?php echo $local_currency; ?>';
	 // noinspection JSAnnotator
	 const site_info = <?php echo json_encode( $info_data ); ?>;
	 const settingsPrefix = 'woocommerce_triplea_payment_gateway';
	 let otpRequestEmail, otpRequestNotifEmail;
	 
	 /**
	  *
	  *  BTC-to-BTC External wallet code
	  *
	  */
	 
	 function triplea_hideAllSteps() {
	   let steps = document.getElementsByClassName('triplea_step');
	   for (let i=0; steps && i < steps.length; i++) {
		 steps[i].style.display = 'none';
	   }
	 }
	 
	 function triplea_hideAllErrors() {
	   let errors = document.getElementsByClassName('triplea-error-msg');
	   for (let i=0; errors && i < errors.length; i++) {
		 errors[i].style.display = 'none';
	   }
	 }

	 function triplea_btc_updateAccount() {
	   let notifEmail;

	   // Get notification email (optional)
	   let notifEmailNode = document.getElementById('btc-notif-email');
	   notifEmail = notifEmailNode.value;

	   if (!notifEmail ||
		 (notifEmail.indexOf('@') < 3 || notifEmail.lastIndexOf('.') < notifEmail.indexOf('@')))
	   {
		 // already checked and handled before, no output or logging.. this is just fallback
		 return;
	   }
	   let hiddenNotifEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
	   hiddenNotifEmailNode.value = notifEmail;
	   
	   let pubkey, apiId, apiIdNode;

	   // Update wallet, if API ID set.
	   // Else request API ID for pubkey.
	   let pubkeyNode = document.getElementById('master-public-key');
	   pubkey = pubkeyNode.value ? pubkeyNode.value : '';
	   if (!pubkey) {
		 // already checked and handled before, no output or logging.. this is just fallback
		 return;
	   }
	   if (pubkey.indexOf('pub') !== 1) {
		 // already checked and handled before, no output or logging.. this is just fallback
		 return;
	   }

	   apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id');
	   if (apiIdNode && apiIdNode.value) {
		 apiId = apiIdNode.value;
	   }
	   else {
		 // already checked and handled before, no output or logging.. this is just fallback
	   }

	   const data = {
		 currency: 'BTC',
		 local_currency: local_currency,
		 notification_email: notifEmail,
		 return_url: return_url,
		 account_name: window.location.hostname,
		 account_type: 'ecommerce',
		 info: site_info,
		 // no wallet_type, autodetect
	   };
	   if (pubkey) {
		 data.pub = pubkey;
	   }
	   if (apiId) {
		 data.api_id = apiId;
	   }
	   
	   triplea_btc_addPub(data);
	 }
	 
	 function triplea_btc_addPub(data) {
	   const url      = `https://moneyoverip.io/api/add_pub`;
	   const callback = triplea_btc_addPubCallback;
	   const method   = "POST";

	   triplea_ajax_action(url, callback, method, JSON.stringify(data));
	 }

	 function triplea_btc_addPubCallback(result) {

	   if (result && result.return === "201") {
		 console.warn('Something went wrong. Wallet could not be updated. Raw data: ', result);
		 
		 let errorDetailsNode = document.getElementById("btc-accountcreation-error-details");
		 errorDetailsNode.innerHTML = JSON.stringify(result);
		 triplea_helper_displayNode('btc-accountcreation-error');
		 return;
	   }
	   if (!result || result.status !== "OK" || !result.api_id || !result.server_public_key) {
		 console.warn('Something went wrong. Wallet could not be updated. Missing required information. Raw data: ', result);

		 let errorDetailsNode = document.getElementById("btc-accountcreation-error-details");
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
	   encKeyNode = document.getElementById(settingsPrefix + '_' + 'triplea_server_public_enc_key_btc');
	   encKeyNode.value = result.server_public_key;
	   //console.debug("Set hidden value: server_public_key.", encKeyNode.value);

	   // Set hidden value: API ID.
	   let apiIdNode;
	   apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id');
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
	 
	 function triplea_fiat_updateAccount() {
	   let dashboardEmail, notifEmail;
	   
	   // Get dashboard email.
	   let dashboardEmailNode = document.getElementById('cash-notif-email');
	   dashboardEmail = dashboardEmailNode.value;

	   // Dashboard email validation.
	   if (!dashboardEmail
		 || dashboardEmail.indexOf('@') < 3
		 || dashboardEmail.lastIndexOf('.') < dashboardEmail.indexOf('@'))
	   {
		 console.debug('Incorrect TripleA wallet email. Cannot proceed.');
		 triplea_helper_displayNode(step + '-input-error');
		 return;
	   }
	   triplea_hideAllErrors();
	   // Set notification email to same value.
	   notifEmail = dashboardEmail;
	   
	   
	   // Remember for which email the OTP was last requested.
	   otpRequestEmail = dashboardEmail;
	   otpRequestNotifEmail = notifEmail; // TODO need different Notification emails stored, for bitcoin or localcurrency !

	   // Ask for OTP to be sent to email address.

	   const url                = `https://moneyoverip.io/api/send_otp`;
	   const callback           = triplea_fiat_createAccountCallback;
	   const method             = "POST";
	   const data               = {
		 email: dashboardEmail
	   };

	   triplea_ajax_action(url, callback, method, JSON.stringify(data));
	 }

	 function triplea_fiat_createAccountCallback(result) {
	   if (result.status !== "OK") {
		 console.error('ERROR. Problem requesting OTP for email validation.');
		 // TODO display error
		 return;
	   }

	   triplea_helper_displayNode("cash-email-sent");
	   triplea_helper_displayNode("cash-enter-otp");
	 }
	 
	 function triplea_fiat_validateEmail() {
	   let dashboardEmail;
	   if (otpRequestEmail) {
		 console.debug('otpRequestEmail: ', otpRequestEmail);
		 dashboardEmail = otpRequestEmail;
	   }

	   let otpNode = document.getElementById('cash-otp-value');
	   let otp     = otpNode.value;
	   if (!otp) {
		 triplea_helper_displayNode('error-provide-otp');
		 return;
	   }
	   triplea_hideAllErrors();
	   triplea_helper_displayNode('otp-check-loading');
	   
	   const url      = `https://moneyoverip.io/api/check_otp`;
	   const callback = triplea_fiat_validateEmailCallback;
	   const method   = "POST";
	   const data     = {
		 email: dashboardEmail,
		 otp: otp
	   };

	   triplea_ajax_action(url, callback, method, JSON.stringify(data));
	 }
	 
	 function triplea_fiat_validateEmailCallback(result) {
	   if (result.status !== "OK" || !result.token) {
		 triplea_hideAllErrors();
		 triplea_helper_displayNode('error-otp-wrong');
		 return;
	   }
	   let authToken = result.token;
	   
	   triplea_fiat_addPub(authToken);
	 }

	 function triplea_fiat_addPub(authToken) {
	   let pubkey, apiId, apiIdNode, notifEmail;
	   
	   // Update wallet, if API ID set. Else request new TripleA wallet.
	   apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id_for_conversion');
	   if (apiIdNode && apiIdNode.value) {
		 apiId = apiIdNode.value;
	   }
	   else {
		 pubkey = 'new_wallet';
	   }

	   notifEmail = otpRequestNotifEmail  || null;

	   const url      = `https://moneyoverip.io/api/add_pub`;
	   const callback = triplea_fiat_addPubCallback;
	   const method   = "POST";
	   const data     = {
		 currency: 'BTC',
		 local_currency: local_currency,
		 notification_email: notifEmail,
		 return_url: return_url,
		 account_name: window.location.hostname,
		 account_type: 'ecommerce',
		 info: site_info,
		 auth_token: authToken
		 // no wallet_type, autodetect
	   };
	   if (pubkey) {
		 data.pub = pubkey;
	   }
	   else if (apiId) {
		 data.api_id = apiId;
	   }

	   triplea_ajax_action(url, callback, method, JSON.stringify(data));
	 }
	 
	 function triplea_fiat_addPubCallback(result) {

	   triplea_hideAllErrors();
	   
	   if (result && result.return === "201") {
		 console.warn('Something went wrong. TripleA Wallet could not be updated. Possible reason: trying to associate a wallet with the wrong TripleA wallet if that wallet was already associated with another wallet. Raw data: ', JSON.stringify(result, null, 3));
		 triplea_helper_displayNode("cash-account-creation-error");
		 return;
	   }
	   else if (!result || result.status !== "OK" || !result.api_id || !result.server_public_key) {
		 console.debug('Something went wrong. TripleA Wallet could not be updated. Please contact support@triple-a.io and provide a screenshot of the following data: ', JSON.stringify(result, null, 3));
		 triplea_helper_displayNode("cash-account-creation-error");
		 return;
	   }
	   triplea_hideAllErrors();

	   let accountEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_dashboard_email');
	   accountEmailNode.value = otpRequestEmail;

	   let hiddenNotifEmailNode = document.getElementById(settingsPrefix + '_' + 'triplea_notifications_email');
	   hiddenNotifEmailNode.value = otpRequestNotifEmail;


	   // console.debug('Received API ID                       : ', result.api_id);
	   // console.debug('Received Server public encryption key : ', result.server_public_key);

	   // set hidden value: server_public_key
	   let encKeyNode;
	   encKeyNode = document.getElementById(settingsPrefix + '_' + 'triplea_server_public_enc_key_conversion');
	   encKeyNode.value = result.server_public_key;
	   
	   // set hidden value: API ID
	   let apiIdNode;
	   apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id_for_conversion');
	   apiIdNode.value = result.api_id;
	   
	   //console.debug('Setting active API ID to TripleA Local currency wallet ' + result.api_id);
		 
	   // Sets active account then submits form.
	   triplea_setActiveAccount('btc2cash');
	   
	   triplea_submitForm();
	 }
	 
	 
	 /**
	  *
	  *  Helper functions
	  *
	  */

	 function triplea_helper_displayNode(nodeId) {
	   let displayNode = document.getElementById(nodeId);
	   if (displayNode) displayNode.style.display = 'block';
	 }
	 
	 function triplea_helper_hideNode(nodeId) {
	   let displayNode = document.getElementById(nodeId);
	   if (displayNode) displayNode.style.display = 'none';
	 }
	 
	 function triplea_submitForm() {
	   let submitBtnNodes = document.getElementsByName('save');
	   if (submitBtnNodes && submitBtnNodes.length > 0) {
		 console.debug('Clicking Form Submit button');

		 submitBtnNodes[0].click();
	   }
	 }

	 function triplea_setActiveAccount(walletType) {
	   let apiIdNode;
	   if (walletType === 'btc2btc') {
		 apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id');
	   }
	   else {
		 // walletType = 'btc2cash'
		 apiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_pubkey_id_for_conversion');
	   }
	   let apiId = apiIdNode.value;

	   let activeApiIdNode = document.getElementById(settingsPrefix + '_' + 'triplea_active_pubkey_id');
	   activeApiIdNode.value = apiId;

	   console.debug('Submit FORM after verifying triplea_active_pubkey_id HIDDEN input value. Active API ID = ', activeApiIdNode.value);
	   setTimeout(function() {
		 triplea_submitForm();
	   }, 200);
	 }

	 function triplea_ajax_action(url, callback, _method, _data, _authToken = null) {
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
			 console.log(err.message + " in " + xmlhttp.responseText);
			 return;
		   }
		   callback(data);
		 }
	   };
	   xmlhttp.open(_method, url, true);
	   if (_authToken) {
		 xmlhttp.setRequestHeader("Authorization", "Bearer " + _authToken);
	   }
	   xmlhttp.send(_data);
	 }

   </script>

<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
