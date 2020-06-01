<?php

// if (!defined('ABSPATH') || !defined('TRIPLEA_UNLOCK_TEMPLATE')) {
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<style>
#triplea-woocommerce-payment-content-inner {
  text-align: center;
  display: none;
}

#triplea_step_2,
#triplea_step_3,
#triplea_step_4,
#triplea_step_5,
#triplea_step_6,
#triplea_step_7,
#triplea_step_8 {
  display: none;
  text-align: center;
}

.triplea-p-small {
  display: block;
  margin: 20px 0 0;
  font-size: 13px;
}

.triplea-address-txt {
  color: #333;
  font-weight: bold;
}

.triplea-address-txt {
  color: #333;
  font-weight: bold;
}

#triplea_payment_qr_img {
  margin: 10px auto 20px;
  width: 200px;
  height: 200px;
}

#triplea_amount_btc {
  font-size: 110%;
  color: #333;
  font-weight: bold;
}

#triplea_copy_btn_msg1,
#triplea_copy_btn_msg2 {
  display: none;
}

#triplea_amount_btc_remaining_2 {
  font-size: 110%;
  color: #333;
  font-weight: bold;
}

#triplea_payment_qr_img_2 {
margin: 10px auto 20px;
width: 200px;
height: 200px;
}

#triplea_amount_btc_remaining {
  font-size: 110%;
  color: #333;
  font-weight: bold;
}

#triplea_amount_btc_paid_2 {
  color: darkred;
  font-weight: 500;
}
</style>

<div id='triplea-woocommerce-payment-content-inner'>

	<div id='triplea_step_1'>
		<div id='triplea_loading_msg'>Loading payment address...</div>
	</div>


	<div id='triplea_step_2'>
		<div class='triplea_p'>
			Please send <span style='text-decoration:underline;'>exactly</span>&nbsp;
			<span id='triplea_amount_btc'>...</span>
			<br>to the following BTC address:
		</div>
		<br>
		<div class='triplea_p_small'>
			<div class='triplea-address-txt'></div>
		</div>
		<div>
			<a id='triplea_payment_qr_img' data-init='false' href="" target="_blank" style="display: block;"></a>
		</div>
		<div style='margin:0 0 10px;'>
			<a id='triplea_copy_btn_address' class='triplea_copy_btn'>
			Copy address
			</a> &nbsp; <a class='triplea_copy_btn' id='triplea_copy_btn_amount'>
				Copy amount
			</a>
			<br>
			<span id='triplea_copy_btn_msg1'>Copied!</span>
		</div>
		<div class='triplea_p_small'><span class='triplea-tx-spinner'></span> &nbsp; Waiting for payment
			<span class='triplea-countdown-timer'> </span>
			<br>
			<div style='font-size:80%;'>
				1 BTC = <span id='triplea_exchange_rate'>...</span>
				<span id='triplea_fiat_currency_display'>...</span>
			</div>
		</div>
	</div>


	<div id='triplea_step_3'>
		Payment made!
		<br>
		<svg class='checkmark' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 52 52'>
			<circle class='checkmark__circle' cx='26' cy='26' r='25' fill='none'/><path class='checkmark__check' fill='none' d='M14.1 27.2l7.1 7.2 16.7-16.8'/>
		</svg>
		<br>
		<small>Your order will be processed<br>once the transaction is validated.</small>
		<br>
		<button type='Submit' class="button alt" id="triplea_submit_btn">Submit order</button>
		<br>
	</div>

   <div id='triplea_step_4'>
	  <span>
		Transaction confirmed
	  </span>
	  <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
		 <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"></circle>
		 <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"></path>
	  </svg>
	  Thank you for your payment
	  <br>
	  <br>
	  <button type='Submit' class="button alt">Place order</button>
   </div>

   <div id='triplea_step_5'>
	  <div>Only <span id='triplea_amount_btc_paid_2'>...</span> paid.</div>
	  <div>
		 Please send <span style='text-decoration:underline;'>exactly</span>&nbsp;
		 BTC <span id='triplea_amount_btc_remaining'>...</span><br>to
		 the <em>same</em> BTC address:
	  </div>
	  <br>
	  <div class='triplea_p_small'>
		 <div class='triplea-address-txt'></div>
	  </div>
	  <div>
		 <a id='triplea_payment_qr_img_2' data-init='false' href="" target="_blank" style="display: block;">
		 </a>
	  </div>
	  <div style='margin:0 0 10px;'>
		 <a class='triplea_copy_btn' id='triplea_copy_btn_address_2'>Copy address
		 </a> &nbsp; <a class='triplea_copy_btn' id='triplea_copy_btn_amount_2'>Copy amount</a><br>
		 <span id='triplea_copy_btn_msg2'>Copied!</span>
	  </div>
	  <div class='triplea_p_small'><span class='triplea-tx-spinner'></span> Waiting for payment
		 <span class='triplea-countdown-timer'> </span>
		 <br>
		 <div style='font-size:80%;'>
			1 BTC = <span id='triplea_exchange_rate_2'>...</span>
			<span id='triplea_fiat_currency_display_2'>...</span>
		 </div>
	  </div>
   </div>

   <div id='triplea_step_6'>Error generating payment information.
	  <br>
	  <br>
	  <strong>
		 Please chose another payment method, or reload the page.
	  </strong>
	  <br>
	  <br>
   </div>
	<div id='triplea_step_7'>
	   <br>
	   <br>
	   <strong>Locked exchange rate expired.</strong>
	   <br>
	   <br>
	   <!--<span>Please reload the page.</span>-->
	   <br>
	   <br>
	   <br>
	   <span>Payment not detected on time?</span>
	   <br>
	   <button type='Submit' class="button alt" style="margin: 10px 0;">Place order</button>
	   <br>
	   <span>
		 Your order payment status will be updated shortly, once your payment is detected.
	   </span>
	   <br>
	   <br>
   </div>

	<div id='triplea_step_8'>
	   <br>
	   <br>
	   <strong>Locked exchange rate expired.</strong>
	   <br>
	   <br>
	   <span>Too little was paid.</span>
	   <br>
	   <span><strong>Payment failed.</strong></span>
	   <br>
	   <br>
	   <br>
	   <button type='Submit' class="button alt">Place order</button>
	   <br>
	   <br>
	</div>

</div>
