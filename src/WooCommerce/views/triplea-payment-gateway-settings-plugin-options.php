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


   <div>
	  <hr>
	  <br>
	  <h2>
		 Support & information
	  </h2>
	  <p>
		 If you have feedback or questions, reach out to us at
		 <a href="mailto:support@triple-a.io">support@triple-a.io</a>.
		 <br>
		 We will respond within 24 hours.
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
