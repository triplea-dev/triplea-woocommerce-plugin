<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$plugin_options           = 'woocommerce_' . 'triplea_payment_gateway' . '_settings';
$plugin_settings_defaults = array();
$plugin_settings          = get_option( $plugin_options, $plugin_settings_defaults );
// access plugin settings : $plugin_settings['setting_name']

$tripleaStatuses = array(
	// 'new'       => 'New Order',
	'paid'      => 'Paid (awaiting confirmation)',
	'confirmed' => 'Paid (confirmed)',
	// 'complete'  => 'Complete',
	// 'refunded'  => 'Refunded',  // refunds are possible, will be added to the roadmap
	'invalid'   => 'Invalid',
);
// There is an additional state (on hold) which is set by WooCommerce on order creation.

$statuses = array(
	//'new'       => 'wc-pending-payment',
	'paid'      => 'wc-on-hold',
	'confirmed' => 'wc-processing',
	// 'complete'  => 'wc-processing',
	// 'refunded'  => 'wc-refunded', // refunds are possible, will be added to the roadmap
	'invalid'   => 'wc-failed',
);

$wcStatuses = wc_get_order_statuses();

compact( 'tripleaStatuses', 'statuses', 'wcStatuses' );

$icon_url = TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_URL_PATH . 'assets/img/' ;
if (is_ssl()) {
   $icon_url = WC_HTTPS::force_https_url( $icon_url );
}

$logo_style = 'style="max-width: 100px !important;max-height: 30px !important;"';
$icon_large = '<img id="triplea_preview_logo_large" src="' . $icon_url . 'bitcoin-full.png' . '" alt="Bitcoin logo" ' . $logo_style . ' />';
$icon_short = '<img id="triplea_preview_logo_short" src="' . $icon_url . 'bitcoin.png' . '" alt="Bitcoin logo" ' . $logo_style . ' />';


ob_start();
?>

   <style>
	  .submit .woocommerce-save-button {
		 display: none;
	  }

	  .custom.submit .woocommerce-save-button {
		 display: initial;
	  }
   </style>

   <hr>

   <div id="link-plugin-options" class="triplea-menulink-anchor"></div>
   <h1>
	  Plugin settings
   </h1>

   <table class="form-table">
	  <tr valign="top">
		 <th scope="row" class="titledesc">Display customisation</th>
		 <td class="forminp" id="triplea_order_states">
			<table class="form-table">
			   <tr valign="top">
				  <th scope="row" class="titledesc">
					 Preview
				  </th>
				  <td class="forminp"
					  id="triplea_order_states"
					  style="font-size: 120%; line-height: 35px;">
					 <span id="triplea_preview_text">Pay with Bitcoin</span>
					 <?php echo $icon_large; ?>
					 <?php echo $icon_short; ?>
					 <br>
					 <span id="triplea_preview_description"
						   style="padding-top: 10px; font-size: 90%;">Secure and easy payment with Bitcoin</span>
					 <!--<br>
					 <br>
					 <button type="button" onclick="updatePreviewOnChange()">
						Update preview
					 </button>-->
				  </td>
			   </tr>
			   <tr>
				  <th>

				  </th>
				  <td>
					 <hr>
				  </td>
			   </tr>
			   <tr valign="top">
				  <th scope="row" class="titledesc">Bitcoin logo</th>
				  <td class="forminp forminp-radio" id="triplea_order_states">
					 <label for="" style="padding-right: 30px;">
						<input type="radio"
							   onchange="updatePreviewOnChange()"
							   id="logo_large"
							   name="triplea_bitcoin_logo_option"
						   <?php
							if ( empty( $plugin_settings['triplea_bitcoin_logo_option'] )
								 || 'large-logo' === $plugin_settings['triplea_bitcoin_logo_option'] ) {
								echo 'checked';
							}
							?>
							   value="large-logo">
						<?php echo $icon_large; ?>
					 </label>
					 <label for="" style="padding-right: 30px;">
						<input type="radio"
							   onchange="updatePreviewOnChange()"
							   id="logo_short"
							   name="triplea_bitcoin_logo_option"
						   <?php
							if ( isset( $plugin_settings['triplea_bitcoin_logo_option'] )
								  && 'short-logo' === $plugin_settings['triplea_bitcoin_logo_option'] ) {
								echo 'checked';
							}
							?>
							   value="short-logo">
						<?php echo $icon_short; ?>
					 </label>
					 <label for="">
						<input type="radio"
							   onchange="updatePreviewOnChange()"
							   id="logo_none"
							   name="triplea_bitcoin_logo_option"
						   <?php
							if ( isset( $plugin_settings['triplea_bitcoin_logo_option'] )
								&& 'no-logo' === $plugin_settings['triplea_bitcoin_logo_option'] ) {
								echo 'checked';
							}
							?>
							   value="no-logo">
						no logo
					 </label>
				  </td>
			   </tr>
			   <tr valign="top">
				  <th scope="row" class="titledesc">Bitcoin payment text</th>
				  <td class="forminp forminp-radio">
					 <fieldset class="">
						<label for="">
						   <input type="radio" id="text_default"
								  onchange="updatePreviewOnChange()"
								  name="triplea_bitcoin_text_option"
							  <?php
								if ( isset( $plugin_settings['triplea_bitcoin_text_option'] )
								   && 'default-text' === $plugin_settings['triplea_bitcoin_text_option'] ) {
									echo 'checked';
								}
								?>
								  value="default-text">
						   "<?php echo __( 'Bitcoin', 'triplea-cryptocurrency-payment-gateway-for-woocommerce' ); ?>"
						</label>
						<br>
						<label for="">
						   <input type="radio"
								  id="text_custom"
							  <?php
								if ( isset( $plugin_settings['triplea_bitcoin_text_option'] )
								   && 'custom-text' === $plugin_settings['triplea_bitcoin_text_option'] ) {
									echo 'checked';
								}
								?>
								  onchange="updatePreviewOnChange()"
								  value="custom-text"
								  name="triplea_bitcoin_text_option"
								  style="padding-right: 30px;">
						   Custom text:
						   <br>
						   <input type="text" id="text_custom_value"
								  onkeyup="updatePreviewOnChange()"
								  name="triplea_bitcoin_text_custom_value"
							  <?php
								if ( isset( $plugin_settings['triplea_bitcoin_text_custom_value'] ) ) {
									echo 'value="' . stripcslashes(htmlentities($plugin_settings['triplea_bitcoin_text_custom_value'])) . '"';
								} else {
									echo 'value="Pay with Bitcoin"';
								}
								?>
								  style="margin-top: 5px; margin-left: 24px">
						</label>
					 </fieldset>
				  </td>
			   </tr>
			   <tr valign="top">
				  <th scope="row" class="titledesc">Description text</th>
				  <td class="forminp forminp-radio">
					 <fieldset class="">
						<label for="">
						   <input type="radio" id="desc_default"
								  onchange="updatePreviewOnChange()"
								  value="desc-default"
							  <?php
								if ( empty( $plugin_settings['triplea_bitcoin_descriptiontext_option'] ) || 'desc-default' === $plugin_settings['triplea_bitcoin_descriptiontext_option'] ) {
									echo 'checked';
								}
								?>
								  name="triplea_bitcoin_descriptiontext_option">
						   "<?php echo __( 'Secure and easy payment with Bitcoin', 'triplea-cryptocurrency-payment-gateway-for-woocommerce' ); ?>"
						</label>
						<br>
						<label for="">
						   <input type="radio" id="desc_custom"
								  onchange="updatePreviewOnChange()"
								  value="desc-custom"
							  <?php
								if ( isset( $plugin_settings['triplea_bitcoin_descriptiontext_option'] ) && 'desc-custom' === $plugin_settings['triplea_bitcoin_descriptiontext_option'] ) {
									echo 'checked';
								}
								?>
								  name="triplea_bitcoin_descriptiontext_option"
								  style="padding-right: 30px;">
						   Custom text:
						   <br>
						   <input type="text" id="desc_custom_value"
								  onkeyup="updatePreviewOnChange()"
							  <?php
								if ( isset( $plugin_settings['triplea_bitcoin_descriptiontext_value'] ) ) {
									echo 'value="' . stripcslashes(htmlentities($plugin_settings['triplea_bitcoin_descriptiontext_value'])) . '"';
								} else {
									echo 'value="Pay with your Bitcoin wallet!"';
								}
								?>
								  name="triplea_bitcoin_descriptiontext_value"
								  style="margin-top: 5px; margin-left: 24px">
						</label>
					 </fieldset>
				  </td>
			   </tr>
			</table>
		 </td>
	  </tr>
	  <tr valign="top">
		 <th scope="row" class="titledesc">Order States:</th>
		 <td class="forminp" id="triplea_order_states">
			<table cellspacing="0" cellpadding="0" style="padding:0">
			   <?php foreach ( $tripleaStatuses as $tripleaState => $tripleaName ) : ?>
				  <tr>
					 <th>
						<label for="triplea_state_<?php echo $tripleaState; ?>"><?php echo $tripleaName; ?></label>
					 </th>
					 <td>
						<select id="triplea_state_<?php echo $tripleaState; ?>"
								onchange="updatePreviewOnChange()"
								name="triplea_woocommerce_order_states[<?php echo $tripleaState; ?>]">
						   <?php
							// $orderStates = get_option('woocommerce_triplea_payment_gateway_triplea_woocommerce_order_states');
							$orderStates = isset( $plugin_settings['triplea_woocommerce_order_states'] ) ? $plugin_settings['triplea_woocommerce_order_states'] : array();
							foreach ( $wcStatuses as $wcState => $wcName ) {
								$currentOption = isset( $orderStates[ $tripleaState ] ) ? $orderStates[ $tripleaState ] : $statuses[ $tripleaState ];
								echo "<option value='$wcState'";
								if ( $currentOption === $wcState ) {
									echo 'selected';
								}
								echo ">$wcName</option>";
							}
							?>
						</select>
					 </td>
                 <?php if(strpos($tripleaName, 'awaiting') !== FALSE): ?>
                    <td>
                       <p>Payment not guaranteed yet at this stage!<br>Do not yet provide the product or service.</p>
                    </td>
                 <?php endif; ?>
				  </tr>
			   <?php endforeach; ?>
			</table>
		 </td>
	  </tr>
   </table>

   <div id="link-faq" class="triplea-menulink-anchor"></div>
   <style>
     .triplea-faq-list {
       max-width: 900px;
     }

     .triplea-faq-list li {
     }

     .triplea-faq-list li .triplea-faq-collapsible {
       width: 100%;
       text-align: left;
       border: 1px solid lightgray;
       padding: 8px 10px;
       border-radius: 2px;
       font-weight: 500;
       background: white;
       font-size: 115%;
     }
     .triplea-faq-list li .triplea-faq-content {
       display: none;
       overflow: hidden;
       border: 1px solid lightgray;
       border-top: none;
       border-radius: 2px;
       background: white;
       padding: 5px 20px;
     }
     .triplea-faq-list li .triplea-faq-content p {
       font-size: 110%;
     }

     .triplea-faq-collapsible-active, .triplea-faq-collapsible:hover {
       /*background-color: #cccccc !important;*/
       color: #0071a1;
       border-color: #0071a1 !important;
     }
   </style>
   <div>
      <hr>
      <br>
      <h2>
         Frequently Asked Questions
      </h2>

      <h3>
         General
      </h3>
      <ol class="triplea-faq-list">
         <li>
            <button type="button" class="triplea-faq-collapsible">
               What is the difference between local currency settlement mode and bitcoin settlement mode?
            </button>
            <div class="triplea-faq-content">
               <p>
                  With local currency settlement, received bitcoin payments are instantly converted to their local currency value.
                  TripleA settles the amount due to your bank account minus a 0.8% commission, as soon as the amount due reaches 100 USD worth.
                  It is possible to request settlement in crypto or USDT (for this, please request this at
                  <a href="mailto:support@triple-a.io" target="_blank">support@triple-a.io</a>).
               </p>
               <p>
                  With bitcoin settlement, you receive bitcoin payments directly into your own bitcoin wallet. TripleA does not take any commission in this case.
                  You will be responsible for managing your wallet's security.
               </p>
               <p>
                  For both settlement modes, a TripleA dashboard account will be created where you can issue refunds, view transaction history, send bitcoin payment request links (for invoice payments).
                  If you create several accounts, these will all be available in your TripleA dashboard, linked to the email address used to create the accounts in the plugin.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               What is the TripleA Dashboard?
            </button>
            <div class="triplea-faq-content">
               <p>
                  For both settlement modes, a TripleA dashboard account will be created upon account activation.
                  In the dashboard you can view the transaction history for any one of your accounts, issue refunds, and send bitcoin payment request links (for invoice payments).
               </p>
               <p>
                  If you create several accounts, these will all be available in your TripleA dashboard, linked to the email address used to create the accounts in the plugin.
                  So if you sign up for a bitcoin settlement mode, then activate the local currency settlement mode with the same email address, both will appear in the TripleA dashboard.
               </p>
               <p>
                  Local currency settlement accounts are created with both a Live and a Sandbox account, both will appear in the dashboard to facilitate testing payments.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               Which bitcoin wallets are supported for customers to pay with?
            </button>
            <div class="triplea-faq-content">
               <p>
                  TripleA supports any proper standards-compatible wallet using either legacy or segwit addresses.
                  This should cover almost any good bitcoin wallet your customers may be using.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               Do you support bitcoin lightning payments?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Not yet, but this is planned.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               Do you support other cryptocurrencies for payment?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Not yet, but this is planned.
               </p>
            </div>
         </li>
      </ol>

      <h3>
         Local currency settlement
      </h3>
      <ol class="triplea-faq-list">
         <li>
            <button type="button" class="triplea-faq-collapsible">
               How will you settle to my bank? Where do I provide you with the information?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Once you sign up here in the plugin, your will receive an email with your TripleA dashboard login details.
                  You can then login to the dashboard, where you will be asked to provide your bank details.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               Do you require any KYC (identity verification) or KYB (business verification)?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Yes. We will reach out to you via e-mail once you have provided your bank information in the dashboard, or as soon as you start receiving bitcoin payments.
                  The documents required depend on the type of company or business.
               </p>
               <p>
                  If you are concerned about passing KYC/KYB verification you can easily reach out to us at <a href="mailto:compliance@triple-a.io">compliance@triple-a.io</a>. Meanwhile you can start using the plugin in Sandbox mode or disable the plugin (hide it from your checkout page).
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               What are all the fees?
            </button>
            <div class="triplea-faq-content">
               <p>
                  There is only a single fee: when we settle money to your bank account there is a 0.8% commission.
               </p>
               <p>
                  So when a customer pays, they pay their own bitcoin transaction fee. The bitcoin arrives to us, we note how much local currency we owe you for the given order.
                  We then pay out that owed local currency amount to your bank minus 0.8%.
               </p>
               <p>
                  Note: some banks may charge transfer fees on their end. If you are concerned about this, you may contact us to request weekly or monthly settlements rather than daily.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               How often and how quickly do you settle to my bank?
            </button>
            <div class="triplea-faq-content">
               <p>
                  The minimum threshold for settlement to happen is 100 USD. As soon as the amount due is 100 USD or higher (or equivalent value if in another currency), we will settle on the next business day.
               </p>
               <p>
                  Note: some banks may charge transfer fees on their end. If you are concerned about this, you may contact us to request weekly or monthly settlements rather than daily.
               </p>
            </div>
         </li>
      </ol>

      <h3>
         Bitcoin settlement
      </h3>
      <ol class="triplea-faq-list">
         <li>
            <button type="button" class="triplea-faq-collapsible">
               What is a master public key? Why do you ask for it?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Bitcoin wallets are controlled by a private and a public master key. The private key allows spending your funds (signing transactions). The public key allows generating bitcoin addresses.
               </p>
               <p>
                  In order to accept payments, a bitcoin address is needed. We use your master public key to generate a unique single-use bitcoin address for each payment. This protects your transaction history from your customers, and protects your customers privacy.
               </p>
               <p>
                  Note: a virtually infinite amount of addresses can be generated, all unique and different but all belong to your bitcoin wallet.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               Where can I find my master public key?
            </button>
            <div class="triplea-faq-content">
               <p>
                  If you use a non-custodial bitcoin wallet (one where you manage your own bitcoin, not some 3rd party company or platform) such as Exodus or
                  <a href="https://electrum.org">Electrum</a>, you can find instructions on Youtube or via a search engine. You will need to go in the settings or look through the menus.
               </p>
               <p>
                  Some online custodial wallets such as blockchain.com also allow you to view and copy your master public key. However, exchange and many custodial wallets won't make this available to you. In that case, you will need to either create a new wallet using Electrum or other such wallet or opt for local currency settlement.
               </p>
               <p>
                  Note: we recommend you create a new wallet for use only with TripleA and your current store. This will avoid mixing notifications in case you receive bitcoin payments from other sources.
               </p>
            </div>
         </li>
      </ol>
      <!--
      <h3>
         Local currency settlement
      </h3>
      <ol class="triplea-faq-list">
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>General</strong> -- What is the difference between local currency settlement mode and bitcoin settlement mode?
            </button>
            <div class="triplea-faq-content">
               <p>
                  With local currency settlement, received bitcoin payments are instantly converted to their local currency value.
                  TripleA settles the amount due to your bank account minus a 0.8% commission, as soon as the amount due reaches 100 USD worth.
                  It is possible to request settlement in crypto or USDT (for this, please request this at
                  <a href="mailto:support@triple-a.io" target="_blank">support@triple-a.io</a>).
               </p>
               <p>
                  With bitcoin settlement, you receive bitcoin payments directly into your own bitcoin wallet. TripleA does not take any commission in this case.
                  You will be responsible for managing your wallet's security.
               </p>
               <p>
                  For both settlement modes, a TripleA dashboard account will be created where you can issue refunds, view transaction history, send bitcoin payment request links (for invoice payments).
                  If you create several accounts, these will all be available in your TripleA dashboard, linked to the email address used to create the accounts in the plugin.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>General</strong> -- What is the TripleA Dashboard?
            </button>
            <div class="triplea-faq-content">
               <p>
                  For both settlement modes, a TripleA dashboard account will be created upon account activation.
                  In the dashboard you can view the transaction history for any one of your accounts, issue refunds, and send bitcoin payment request links (for invoice payments).
               </p>
               <p>
                  If you create several accounts, these will all be available in your TripleA dashboard, linked to the email address used to create the accounts in the plugin.
                  So if you sign up for a bitcoin settlement mode, then activate the local currency settlement mode with the same email address, both will appear in the TripleA dashboard.
               </p>
               <p>
                  Local currency settlement accounts are created with both a Live and a Sandbox account, both will appear in the dashboard to facilitate testing payments.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>General</strong> -- Which bitcoin wallets are supported for customers to pay with?
            </button>
            <div class="triplea-faq-content">
               <p>
                  TripleA supports any proper standards-compatible wallet using either legacy or segwit addresses.
                  This should cover almost any good bitcoin wallet your customers may be using.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>General</strong> -- Do you support bitcoin lightning payments?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Not yet, but this is planned.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>General</strong> -- Do you support other cryptocurrencies for payment?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Not yet, but this is planned.
               </p>
            </div>
         </li>

         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>Local currency settlement</strong> -- How will you settle to my bank? Where do I provide you with the information?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Once you sign up here in the plugin, your will receive an email with your TripleA dashboard login details.
                  You can then login to the dashboard, where you will be asked to provide your bank details.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>Local currency settlement</strong> -- Do you require any KYC (identity verification) or KYB (business verification)?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Yes. We will reach out to you via e-mail once you have provided your bank information in the dashboard, or as soon as you start receiving bitcoin payments.
                  The documents required depend on the type of company or business.
               </p>
               <p>
                  If you are concerned about passing KYC/KYB verification you can easily reach out to us at <a href="mailto:compliance@triple-a.io">compliance@triple-a.io</a>. Meanwhile you can start using the plugin in Sandbox mode or disable the plugin (hide it from your checkout page).
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>Local currency settlement</strong> -- What are all the fees?
            </button>
            <div class="triplea-faq-content">
               <p>
                  There is only a single fee: when we settle money to your bank account there is a 0.8% commission.
               </p>
               <p>
                  So when a customer pays, they pay their own bitcoin transaction fee. The bitcoin arrives to us, we note how much local currency we owe you for the given order.
                  We then pay out that owed local currency amount to your bank minus 0.8%.
               </p>
               <p>
                  Note: some banks may charge transfer fees on their end. If you are concerned about this, you may contact us to request weekly or monthly settlements rather than daily.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>Local currency settlement</strong> -- How often and how quickly do you settle to my bank?
            </button>
            <div class="triplea-faq-content">
               <p>
                  The minimum threshold for settlement to happen is 100 USD. As soon as the amount due is 100 USD or higher (or equivalent value if in another currency), we will settle on the next business day.
               </p>
               <p>
                  Note: some banks may charge transfer fees on their end. If you are concerned about this, you may contact us to request weekly or monthly settlements rather than daily.
               </p>
            </div>
         </li>

         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>Bitcoin settlement</strong> -- What is a master public key? Why do you ask for it?
            </button>
            <div class="triplea-faq-content">
               <p>
                  Bitcoin wallets are controlled by a private and a public master key. The private key allows spending your funds (signing transactions). The public key allows generating bitcoin addresses.
               </p>
               <p>
                  In order to accept payments, a bitcoin address is needed. We use your master public key to generate a unique single-use bitcoin address for each payment. This protects your transaction history from your customers, and protects your customers privacy.
               </p>
               <p>
                  Note: a virtually infinite amount of addresses can be generated, all unique and different but all belong to your bitcoin wallet.
               </p>
            </div>
         </li>
         <li>
            <button type="button" class="triplea-faq-collapsible">
               <strong>Bitcoin settlement</strong> -- Where can I find my master public key?
            </button>
            <div class="triplea-faq-content">
               <p>
                  If you use a non-custodial bitcoin wallet (one where you manage your own bitcoin, not some 3rd party company or platform) such as Exodus or
                  <a href="https://electrum.org">Electrum</a>, you can find instructions on Youtube or via a search engine. You will need to go in the settings or look through the menus.
               </p>
               <p>
                  Some online custodial wallets such as blockchain.com also allow you to view and copy your master public key. However, exchange and many custodial wallets won't make this available to you. In that case, you will need to either create a new wallet using Electrum or other such wallet or opt for local currency settlement.
               </p>
               <p>
                  Note: we recommend you create a new wallet for use only with TripleA and your current store. This will avoid mixing notifications in case you receive bitcoin payments from other sources.
               </p>
            </div>
         </li>
      </ol>-->
      
      <br>
      <br>
   </div>
   <script>
     let coll = document.getElementsByClassName("triplea-faq-collapsible");
     var i;

     for (i = 0; i < coll.length; i++) {
       coll[i].addEventListener("click", function() {
         this.classList.toggle("triplea-faq-collapsible-active");
         let content = this.nextElementSibling;
         if (content.style.display === "block") {
           content.style.display = "none";
         } else {
           let colls2close = document.getElementsByClassName("triplea-faq-collapsible");
           var j;
           for (j = 0; j < colls2close.length; j++) {
             let content2close = colls2close[j].nextElementSibling;
             if (content2close.style.display === "block") {
               colls2close[j].classList.remove('triplea-faq-collapsible-active');
               content2close.style.display = "none";
             }
           }
           content.style.display = "block";
         }
       });
     }
   </script>

   <div id="link-support-feedback" class="triplea-menulink-anchor"></div>
   <div>
	  <hr>
	  <br>
	  <h2>
		 Support & feedback
	  </h2>
	  <p>
		 The F.A.Q. might not be enough. If you have <strong>any issue</strong> or simply want to share a <strong>feature request</strong>, reach out to us at
		 <a href="mailto:support@triple-a.io">support@triple-a.io</a>.
	  </p>
      <p>
         We respond to each email as soon as possible. Due to possible timezone differences, we may reply the next day.
      </p>
	  <br>
	  <br>
   </div>

   <div>
	  <p class="custom submit">
		 <button name="save"
				 class="button-primary woocommerce-save-button"
				 type="submit"
				 value="Save changes">
			Save changes
		 </button>
		 <input type="hidden"
				name="_wp_http_referer"
				value="/wp-admin/admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway">
	  </p>
   </div>
   <br>
   <br>

   <script>
	 function updatePreviewOnChange()
	 {
	   // Nodes to update
	   let textNode      = document.getElementById('triplea_preview_text');
	   let descNode      = document.getElementById('triplea_preview_description');
	   let logoLargeNode = document.getElementById('triplea_preview_logo_large');
	   let logoShortNode = document.getElementById('triplea_preview_logo_short');

	   // Update logo preview
	   if (document.getElementById('logo_short').checked)
	   {
		 logoLargeNode.style.display = 'none';
		 logoShortNode.style.display = 'inline-block';
	   }
	   else if (document.getElementById('logo_large').checked)
	   {
		 logoLargeNode.style.display = 'inline-block';
		 logoShortNode.style.display = 'none';
	   }
	   else
	   {
		 logoLargeNode.style.display = 'none';
		 logoShortNode.style.display = 'none';
	   }

	   // Update description
	   if (document.getElementById('desc_default').checked)
	   {
		 descNode.innerText = "Secure and easy payment with Bitcoin";
		 descNode.innerText = "<?php echo __( 'Secure and easy payment with Bitcoin', 'triplea-cryptocurrency-payment-gateway-for-woocommerce' ); ?>";
	   }
	   else
	   {
		 descNode.innerText = document.getElementById('desc_custom_value').value;
	   }

	   // Update text (payment option title)
	   if (document.getElementById('text_default').checked)
	   {
		 textNode.innerText = "<?php echo __( 'Bitcoin', 'triplea-cryptocurrency-payment-gateway-for-woocommerce' ); ?>";
	   }
	   else
	   {
		 textNode.innerText = document.getElementById('text_custom_value').value;
	   }
	 }

	 updatePreviewOnChange();
   </script>

<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
