/**
 * This file should be included via a script tag with a specific ID.
 */

;( function ( $, window, document ) {

  /**
   *
   * Find DOM element to inject button
   *
   */

  const TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION = '1.4.3';

  // GET  -> triplea_ajax_action(url, callback, "GET", null)
  // POST -> triplea_ajax_action(url, callback, "POST", data)
  function triplea_ajax_action(url, callback, _method, _data, sendJSON = true)
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
          console.log(err.message + " in " + xmlhttp.responseText, err);
          return;
        }
        callback(data);
      }
    };
    xmlhttp.open(_method, url, true);
    if (!sendJSON) {
      xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
    }
    xmlhttp.send(_data);
  }


  function triplea_submitForm(delay = 1500) {
    const timer = setTimeout(function(){
      let submitBtn = document.getElementById('triplea_submit_btn');
      if (submitBtn) {
        submitBtn.click();
      }
      else {
        console.warn('Missing submit button. Could not submit form to place order.');
      }
    }, delay);
  }

  function triplea_copyAddress()
  {
    let triplea_insertionLocation = document.getElementById(triplea_insertionId);
    const paymentAddress          = triplea_insertionLocation.getAttribute('data-payment-addr');
    triplea_copyToClipboard(paymentAddress);
    // display message 'copied'
    const msgNode         = document.getElementById('triplea_copy_btn_msg1');
    msgNode.style.display = 'block';
    // remove message 'copied'
    setTimeout(function () {
      const msgNode         = document.getElementById('triplea_copy_btn_msg1');
      msgNode.style.display = 'none';
    }, 1100);
  }

  function triplea_copyAmount()
  {
    const btcAmountNode = document.getElementById('triplea_amount_btc');
    const amount        = btcAmountNode.getAttribute('data-btc-amount');
    triplea_copyToClipboard(amount);

    // display message 'copied'
    const msgNode         = document.getElementById('triplea_copy_btn_msg2');
    msgNode.style.display = 'block';
    // remove message 'copied'
    setTimeout(function () {
      const msgNode         = document.getElementById('triplea_copy_btn_msg2');
      msgNode.style.display = 'none';
    }, 1100);
  }

  function triplea_getPaymentForm() {
    const urlNode = document.getElementById('triplea-payment-gateway-payment-form-request-ajax-url');
    if (!urlNode) {
      console.warn('problem finding payment form request ajax URL node');
      return;
    }
    const ajaxUrl = urlNode.getAttribute('data-value');
    if (!ajaxUrl) {
      console.warn('problem finding payment form request ajax URL');
      return;
    }
    const url                = ajaxUrl;
    const callback           = triplea_getPaymentFormCallback;
    const method             = "POST";
    const data               = null;

    triplea_ajax_action(url, callback, method, data);
  }

  /**
   * Provide a BTC address, and check the triplea_getBalance of that account.
   */
  function triplea_getPaymentStatus(paymentReference)
  {
    if (!paymentReference) return;

    const urlNode = document.getElementById('triplea-payment-gateway-payment-status-ajax-url');

    const url                = `https://api.triple-a.io/api/v1/payment/${paymentReference}`;
    const callback           = triplea_getPaymentStatusCallback;
    const method             = "GET";
    const data               = null;

    triplea_ajax_action(url, callback, method, data);
  }

  function triplea_getPaymentStatusCallback(result)
  {
    if (result.status !== 'ok')
    {
      console.error('Problem with data received from API');
      return;
    }

    // Check if some payment was made (whether full amount or not)
    if (!result.payment_detected) {
      console.log('Payment not detected yet');
      // make 'payment not detected, place order anyway' button available
      // If they paid enough (or topped up a short payment) and payment was not yet detected, they can place the order (in case an exchange takes too long to submit the transaction).
    }
    if (result.payment_detected) {
      console.log('Payment detected');
    }
    if (result.payment_is_short) {
      console.log('Payment is short');
      // make 'payment not detected, place order anyway' button available
      // If they paid enough (or topped up a short payment) and payment was not yet detected, they can place the order (in case an exchange takes too long to submit the transaction).
    }
    if (result.payment_is_good) {
      console.log('Payment completed');
      // make 'place order' button available/enabled
    }
    if (result.payment_is_failed) {
      console.log('Payment failed');
      // place order to keep track of the problem
    }

      // let total       = 0;
      // let confirmed   = 0;
      // let unconfirmed = 0;
      // if (result.balance.confirmed !== null && !isNaN(result.balance.confirmed) && Number(result.balance.confirmed) > 0)
      // {
      //   confirmed = Number(result.balance.confirmed);
      //   total += confirmed;
      // }
      // if (result.balance.unconfirmed !== null && !isNaN(result.balance.unconfirmed) && Number(result.balance.unconfirmed) > 0)
      // {
      //   unconfirmed = Number(result.balance.unconfirmed);
      //   total += unconfirmed;
      // }
      //
      // triplea_createHiddenInputData('triplea_amount_btc_paid', total);
      // triplea_createHiddenInputData('triplea_tx_count', tx_count);
      // triplea_createHiddenInputData('triplea_balance_payload', result.payload);
      //
      // let btcAmountNode = document.getElementById('triplea_amount_btc_input');
      // if (!btcAmountNode)
      // {
      //   console.error('Bitcoin amount not available');
      //   return;
      // }
      // let btcAmount = Number(btcAmountNode.value);
      //
      // // Note: In current version, we don't bother showing step 3. Might confuse users. Straight to step 4.
      //
      // if (btcAmount && total >= btcAmount)
      // {
      //   clearInterval(tripleaCountdownInterval);
      //   clearInterval(tripleaBalanceInterval);
      //
      //   // Show message: payment confirmed
      //   triplea_showStep(4);
      //
      //   triplea_submitForm();
      // }
      // else if (unconfirmed > 0)
      // {
      //   if (unconfirmed < btcAmount)
      //   {
      //     let paidAmountNode       = document.getElementById('triplea_amount_btc_paid_2');
      //     paidAmountNode.innerText = 'BTC ' + unconfirmed.toString();
      //
      //     let remainingAmountNode        = document.getElementById('triplea_amount_btc_remaining');
      //     remainingAmountNode.innerText  = triplea_formatCryptoPrice(btcAmount - unconfirmed);
      //     remainingAmountNode.setAttribute('data-paid-too-little', 'true');
      //
      //     console.warn('Bitcoin payment: too little was paid. ' + unconfirmed + ' instead of ' + btcAmount);
      //     triplea_showStep(5);
      //     // Show message: transaction arrived, not enough paid, please pay ### (remainder)
      //     tx_count += 1;
      //   }
      //   else
      //   {
      //     // Show message: transaction arrived, awaiting confirmation by blockchain
      //     intervalAmount = 30000; // Will take more time, slowing down polling
      //     triplea_showStep(3);
      //
      //     let remainingAmountNode = document.getElementById('triplea_amount_btc_remaining');
      //     remainingAmountNode.setAttribute('data-paid-too-little', 'false');
      //
      //     // Counter can stop, enough was paid. Should hide counter.
      //     clearInterval(tripleaCountdownInterval);
      //     let countDownNodes = document.getElementsByClassName('triplea-countdown-timer');
      //     countDownNodes.style.display = 'none';
      //
      //     // Can submit form right away, no need to wait for confirmation of transaction.
      //     triplea_submitForm();
      //   }
      //
      //   triplea_createHiddenInputData('triplea_balance_payload', result.payload);
      //   triplea_createHiddenInputData('triplea_tx_count', tx_count);
      //   triplea_createHiddenInputData('triplea_amount_btc_paid', unconfirmed);
      //
      //



    if (!tripleaBalanceInterval)
    {
      tripleaBalanceInterval = setInterval(function () {
        triplea_getBalance();
      }.bind(this), intervalAmount);
    }
  }

  function numberWithCommas(n) {
    n         = n.toString().replace(/,/g, "");
    let parts = parseFloat(n).toFixed(2).toString().split(".");
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
  }

  function triplea_copyToClipboard(value) {
    /**
     * Copy text to clipboard.
     */
    let tmp_link = document.createElement("input");
    document.body.appendChild(tmp_link);
    tmp_link.setAttribute("id", "triplea_tmp_link");
    document.getElementById("triplea_tmp_link").value = value;
    tmp_link.select();
    document.execCommand("copy");
    document.body.removeChild(tmp_link);
  }

  function triplea_createHiddenInputData(inputId, inputValue)
  {
    let hiddenInput;

    if (!!document.getElementById(inputId))
    {
      // Update hidden input element with id and value
      hiddenInput       = document.getElementById(inputId);
      hiddenInput.value = inputValue;
    }
    else
    {
      // create hidden input element with id and value
      hiddenInput = document.createElement("input");
      hiddenInput.setAttribute("id", inputId);
      hiddenInput.setAttribute("name", inputId);
      hiddenInput.setAttribute("type", "hidden");
      hiddenInput.value = inputValue;

      // Find checkout form, append input to the form
      let checkoutForm = document.getElementsByClassName('checkout woocommerce-checkout')['checkout'];
      checkoutForm.appendChild(hiddenInput);
    }
  }

  function triplea_formatCryptoPrice(value) {
    return (value / 1).toFixed(8);
  }

  function triplea_validateCheckout() {
    let checkoutCheckUrlNode = document.getElementById('triplea-payment-gateway-start-checkout-check-url');
    if (checkoutCheckUrlNode) {
      let url = checkoutCheckUrlNode.getAttribute('data-value');
      if (url) {
        let callback = triplea_validateCheckoutCallback;

        // Clear any errors from previous attempt.
        $( '.woocommerce-error', selector ).remove();

        let data = $( selector ).closest( 'form' )
          .serialize();

        // Call URL
        triplea_ajax_action(url, callback, "POST", data, false);

        // Upon return, if not successful let it display error messages...
        // If successful, trigger Bitcoin payment form display.
      }
    }
    else {
      console.error('Checkout validation callback URL not found.');
    }
  }

  function triplea_validateCheckoutCallback(response) {
    if ( response.data && response.success === false ) {
      var messageItems = response.data.messages.map( function( message ) {
        return '<li>' + message + '</li>';
      } ).join( '' );

      showError( '<ul class="woocommerce-error" role="alert">' + messageItems + '</ul>', selector );
      return null;
    }
    else if (response.result && response.result === 'failure' && response.messages && typeof response.messages === "string") {
      showError( response.messages, selector );
      return null;
    }

    let customerData;
    if (!response.data.customer_data) {
      customerData = {
        'error': 'No customer data provided.'
      };
    }
    else {
      customerData = response.data.customer_data;
    }

    triplea_showPaymentHtml(customerData);
  }

  // Show error notice at top of checkout form, or else within button container
  function showError( errorMessage, selector ) {
    var $container = $( '.woocommerce-notices-wrapper, form.checkout' );

    if ( ! $container || ! $container.length ) {
      $( selector ).prepend( errorMessage );
      return;
    } else {
      $container = $container.first();
    }

    // Adapted from https://github.com/woocommerce/woocommerce/blob/ea9aa8cd59c9fa735460abf0ebcb97fa18f80d03/assets/js/frontend/checkout.js#L514-L529
    $( '.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove();
    $container.prepend( '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + errorMessage + '</div>' );
    $container.find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).blur();

    var scrollElement = $( '.woocommerce-NoticeGroup-checkout' );
    if ( ! scrollElement.length ) {
      scrollElement = $container;
    }

    if ( $.scroll_to_notices ) {
      $.scroll_to_notices( scrollElement );
    } else {
      // Compatibility with WC <3.3
      $( 'html, body' ).animate( {
        scrollTop: ( $container.offset().top - 100 )
      }, 1000 );
    }

    $( document.body ).trigger( 'checkout_error' );
  }


  /*const tripleaPaymentButtons = document.getElementsByClassName('triplea-payment-gateway-btn');
  for (let i = 0; i < tripleaPaymentButtons.length; i++)
  {
    tripleaPaymentButtons[i].removeEventListener('click', triplea_validateCheckout);
    tripleaPaymentButtons[i].addEventListener('click', triplea_validateCheckout);
  }*/

} )( jQuery, window, document );
