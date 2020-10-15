/**
 * This file should be included via a script tag with a specific ID.
 */

;( function ( $, window, document ) {

  /**
   *
   * Find DOM element to inject button
   *
   */

  const TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION = '1.5.0';

  let triplea_QRCode;
  let tripleaBalanceInterval    = null;
  let tripleaCountdownInterval  = null;
  let triplea_insertionId       = 'triplea-payment-gateway-script';
  let tx_count                  = 1;
  let selector                  = '#triplea-payment-gateway-checkout-wrapper';

  // ~ 15 minutes locktime for exchange rate, after which form expired, needs refresh.
  let countDown = 25 * 60;

  /**
   *
   * Inject popup code only once (in case there are several buttons, later)
   *
   */
  function triplea_displayPaymentHtml()
  {
    const tripleaPaymentForm = document.getElementById('triplea-woocommerce-payment-content-inner');
    if (tripleaPaymentForm)
    {
      tripleaPaymentForm.style.display = 'block';
    }

    let triplea_paymentHtmlId = 'triplea-woocommerce-payment-content';
    if (!document.getElementById(triplea_paymentHtmlId))
    {
      let head       = document.getElementsByTagName('head')[0];
      let link       = document.createElement('style');
      link.rel       = 'stylesheet';
      link.type      = 'text/css';
      link.innerHTML = ".triplea-tx-spinner {vertical-align:middle;height: 20px;width: 20px;margin:0;position: relative;-webkit-animation: triplea-spinner-rotation 1.1s infinite linear;-moz-animation: triplea-spinner-rotation 1.1s infinite linear;-o-animation: triplea-spinner-rotation 1.1s infinite linear;animation: triplea-spinner-rotation 1.1s infinite linear;border-left: 3px solid rgba(0, 174, 239, .15);border-right: 3px solid rgba(0, 174, 239, .15);border-bottom: 3px solid rgba(0, 174, 239, .15);border-top: 3px solid rgba(0, 174, 239, .8);border-radius: 100%;display:inline-block;}\n" +
        "@-webkit-keyframes triplea-spinner-rotation {from {-webkit-transform: rotate(0deg);} to {-webkit-transform: rotate(359deg);}}\n" +
        "@-moz-keyframes triplea-spinner-rotation {from {-moz-transform: rotate(0deg);} to {-moz-transform: rotate(359deg);}}\n" +
        "@-o-keyframes triplea-spinner-rotation {from {-o-transform: rotate(0deg);} to {-o-transform: rotate(359deg);}}\n" +
        "@keyframes triplea-spinner-rotation {from {transform: rotate(0deg);} to {transform: rotate(359deg);}}\n" +
        " " +
        ".triplea_copy_btn { color: #555; border: 1px solid #aaa; padding: 4px 6px; border-radius: 4px;background: white; cursor:pointer; text-decoration: none !important;}\n" +
        ".checkmark__circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10; stroke: #7ac142; fill: none; animation: triplea_stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;}\n" +
        ".checkmark { width: 56px; height: 56px; border-radius: 50%; display: block; stroke-width: 2; stroke: #fff; stroke-miterlimit: 10; margin: 35px auto; box-shadow: inset 0px 0px 0px #7ac142; animation: triplea_fill .4s ease-in-out .4s forwards, triplea_scale .3s ease-in-out .9s both;}\n" +
        ".checkmark__check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; animation: triplea_stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;}\n" +
        "@keyframes triplea_stroke { 100% {stroke-dashoffset: 0;}}\n" +
        "@keyframes triplea_scale { 0%, 100% { transform: none;} 50% {transform: scale3d(1.1, 1.1, 1);}}\n" +
        "@keyframes triplea_fill { 100% {box-shadow: inset 0px 0px 0px 30px #7ac142;}}\n"
      ;
      head.appendChild(link);
    }
  }

  /**
   *
   * TripleA payment app code
   *
   */
  function triplea_showPaymentHtml(customerData = {})
  {
    let triplea_insertionId       = 'triplea-payment-gateway-script';
    let triplea_insertionLocation = document.getElementById(triplea_insertionId);
    const fiatCurrency            = triplea_insertionLocation.getAttribute('data-currency');
    const fiatAmount              = triplea_insertionLocation.getAttribute('data-amount');

    triplea_getBtcAmount('BTC', fiatCurrency.toUpperCase(), parseFloat(fiatAmount));

    triplea_loadQrCodeGenerator();

    const tripleaPaymentButtons = document.getElementsByClassName('triplea-payment-gateway-btn');
    for (let i = 0; i < tripleaPaymentButtons.length; i++)
    {
      tripleaPaymentButtons[i].style.display = 'none';
    }

    // triplea_displayPaymentHtml();

    const fiatCurrencyDisplay     = document.getElementById('triplea_fiat_currency_display');
    fiatCurrencyDisplay.innerText = fiatCurrency.toUpperCase();

    const fiatCurrencyDisplay2     = document.getElementById('triplea_fiat_currency_display_2');
    fiatCurrencyDisplay2.innerText = fiatCurrency.toUpperCase();

    const tripleaCopyBtnAddr = document.getElementById('triplea_copy_btn_address');
    tripleaCopyBtnAddr.removeEventListener('click', triplea_copyAddress);
    tripleaCopyBtnAddr.addEventListener('click', triplea_copyAddress);

    const tripleaCopyBtnAddr2 = document.getElementById('triplea_copy_btn_address_2');
    tripleaCopyBtnAddr2.removeEventListener('click', triplea_copyAddress);
    tripleaCopyBtnAddr2.addEventListener('click', triplea_copyAddress);

    const tripleaCopyBtnAmount = document.getElementById('triplea_copy_btn_amount');
    tripleaCopyBtnAmount.removeEventListener('click', triplea_copyAmount);
    tripleaCopyBtnAmount.addEventListener('click', triplea_copyAmount);

    const tripleaCopyBtnAmount2 = document.getElementById('triplea_copy_btn_amount_2');
    tripleaCopyBtnAmount2.removeEventListener('click', triplea_copyAmount);
    tripleaCopyBtnAmount2.addEventListener('click', triplea_copyAmount);

    // Load crypto payment qr code
    triplea_setAddr(false, customerData);
  }

  function triplea_showStep(step)
  {
    for (let i = 1; i <= Math.max(8, step); ++i)
    {
      const stepElem = document.getElementById('triplea_step_' + i);
      if (i === step)
      {
        stepElem.style.display = 'block';
      }
      else
      {
        stepElem.style.display = 'none';
      }
    }
  }

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

  function triplea_setPaymentQrCode(data, customerData = {})
  {
    if (data.status.toLowerCase() !== "ok")
    {
      console.error('Invalid PubKey ID provided to TripleA API');
    }
    else if (data.status.toLowerCase() === "ok")
    {
      const paymentAddress = data.addr;

      let triplea_insertionId       = 'triplea-payment-gateway-script';
      let triplea_insertionLocation = document.getElementById(triplea_insertionId);
      triplea_insertionLocation.setAttribute('data-payment-addr', paymentAddress);

      const email        = triplea_insertionLocation.getAttribute('data-email');
      const txid         = triplea_insertionLocation.getAttribute('data-tx-id');
      const amountBtc    = triplea_insertionLocation.getAttribute('data-amount-btc');
      const exchangeRate = triplea_insertionLocation.getAttribute('data-btc-exchange-rate');

      triplea_createHiddenInputData('triplea_notif_email', email);
      triplea_createHiddenInputData('triplea_addr', paymentAddress);
      triplea_createHiddenInputData('triplea_tx_id', txid);
      triplea_createHiddenInputData('triplea_exchange_rate', exchangeRate);  // TODO
      triplea_createHiddenInputData('triplea_amount_btc', amountBtc);   // TODO

      const qrTarget  = document.getElementById("triplea_payment_qr_img");
      const qrTarget2 = document.getElementById("triplea_payment_qr_img_2");
      const qrInit    = qrTarget.getAttribute('data-init');
      if (qrInit === 'false')
      {
        qrTarget.setAttribute('data-init', 'true');

        new triplea_QRCode(qrTarget, {text: `bitcoin:${paymentAddress}?amount=${amountBtc}`, width: 200, height: 200});
        new triplea_QRCode(qrTarget2, {text: `bitcoin:${paymentAddress}?amount=${amountBtc}`, width: 200, height: 200});

        qrTarget.href = `bitcoin:${paymentAddress}?amount=${amountBtc}`;
        qrTarget2.href = `bitcoin:${paymentAddress}?amount=${amountBtc}`;

        const btcAddrTxts = document.getElementsByClassName('triplea-address-txt');
        for (let i = 0; btcAddrTxts && i < btcAddrTxts.length; ++i)
        {
          btcAddrTxts[i].innerHTML = paymentAddress;
        }

        triplea_showStep(2);
        triplea_getBalance();

        // countDown
        let countDownNodes       = document.getElementsByClassName('triplea-countdown-timer');
        if (!tripleaCountdownInterval)
        {
          tripleaCountdownInterval = setInterval(function () {
            --countDown;
            for (let i = 0; i < countDownNodes.length; ++i)
            {
              countDownNodes[i].innerText = ' (' + Math.floor(countDown / 60) + ':' + (countDown % 60).toString().padStart(2, '0') + ')';
            }
            if (countDown < 1)
            {
              let tripleaRefreshBtns = document.getElementsByClassName('triplea_refresh_form_btn');
              for (let j = 0; j < tripleaRefreshBtns.length; j++)
              {
                tripleaRefreshBtns[j].removeEventListener('click', triplea_refreshForm);
                tripleaRefreshBtns[j].addEventListener('click', triplea_refreshForm);
              }

              let remainingAmountNode = document.getElementById('triplea_amount_btc_remaining');
              let paidTooLittle       = remainingAmountNode.getAttribute('data-paid-too-little') === 'true';
              if (paidTooLittle)
              {
                // Exchange rate expired. AND too little was paid.
                // Payment failed, order will be submitted as is
                // (to create the order and note the insufficient amount paid).
                triplea_createHiddenInputData('triplea_balance_payload', 'failed_expired_paid_too_little');
                triplea_showStep(8);

                triplea_submitForm();
              }
              else
              {
                // Exchange rate expired. Reload page or choose other payment option.
                triplea_createHiddenInputData('triplea_balance_payload', 'failed_expired_no_payment_detected');
                triplea_showStep(7);
              }

              clearInterval(tripleaCountdownInterval);
              clearInterval(tripleaBalanceInterval);
            }
          }.bind(this), 1000);
        }

        // Associate metadata with payment address
        for (let k = 0; k < Object.keys(customerData).length; ++k) {
          let key = Object.keys(customerData)[k];
          let fieldName = key;
          let fieldValue = customerData[key];
          triplea_postUserInfo(paymentAddress, fieldName, fieldValue);
        }
      }
    }
    else
    {
      console.error('Problem with payment address. Cannot get QR code image.');
    }
  }


  function triplea_postUserInfo(addr, fieldName, fieldValue) {
    let data = {
      info: fieldName + ':' + fieldValue,
      addr: addr
    };

    const url    = `https://moneyoverip.io/api/addr_info/`;
    const method = 'POST';
    triplea_ajax_action(url, triplea_postUserInfoCallback, method, JSON.stringify(data));
  }

  function triplea_postUserInfoCallback(data) {
    // Nothing needed here.
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

  /**
   * Convert from 1 'fromCurrency' into 'toCurrency'.
   * Then converts the cart total to BTC using the exchange rate.
   *
   * @param fromCurrency
   * @param toCurrency
   * @param cartTotal
   * @returns {number}
   */
  function triplea_getBtcAmount(fromCurrency, toCurrency, cartTotal)
  {
    let triplea_insertionId       = 'triplea-payment-gateway-script';
    let triplea_insertionLocation = document.getElementById(triplea_insertionId);
    const fiatCurrency            = triplea_insertionLocation.getAttribute('data-currency');
    const fiatAmount              = triplea_insertionLocation.getAttribute('data-amount');

    triplea_createHiddenInputData('triplea_fiat_currency', fiatCurrency);
    triplea_createHiddenInputData('triplea_amount_fiat', fiatAmount);

    const url    = `https://moneyoverip.io/api/exchange/${fromCurrency.toUpperCase()}/${toCurrency.toUpperCase()}/1`;
    const method = "GET";

    const setExchangeRate = function (data) {
      if (!data || !data.amount)
      {
        console.error('Error fetching bitcoin exchange rate data.');
        const exchangeRateNode     = document.getElementById('triplea_exchange_rate');
        exchangeRateNode.innerText = 'error';
        const amountBtc            = document.getElementById('triplea_amount_btc');
        amountBtc.innerText        = 'error';

        // Display error message:
        // Problem getting exchange rate info, chose another payment method.
        triplea_showStep(6);

        return;
      }

      const exchangeRateNode     = document.getElementById('triplea_exchange_rate');
      exchangeRateNode.innerText = numberWithCommas(data.amount);
      exchangeRateNode.setAttribute('data-btc-exchangerate', data.amount);

      const exchangeRateNode2     = document.getElementById('triplea_exchange_rate_2');
      exchangeRateNode2.innerText = numberWithCommas(data.amount);
      exchangeRateNode2.setAttribute('data-btc-exchangerate', data.amount);

      triplea_insertionLocation.setAttribute('data-btc-exchange-rate', data.amount);

      triplea_createHiddenInputData('triplea_exchange_rate_input', data.amount);

      let btcAmount           = cartTotal / data.amount;
      const btcAmountNode     = document.getElementById('triplea_amount_btc');
      btcAmountNode.innerText = triplea_formatCryptoPrice(btcAmount) + ' BTC';
      btcAmountNode.setAttribute('data-btc-amount', triplea_formatCryptoPrice(btcAmount));

      triplea_insertionLocation.setAttribute('data-amount-btc', triplea_formatCryptoPrice(btcAmount));

      triplea_createHiddenInputData('triplea_amount_btc_input', triplea_formatCryptoPrice(btcAmount));

      triplea_displayPaymentHtml();
    };

    triplea_ajax_action(url, setExchangeRate, method, {});
  }

  /**
   * Send the XPub key, receive a crypto payment address in return.
   */
  function triplea_setAddr(forceRefreshAddress = false, customerData)
  {
    let triplea_insertionId       = 'triplea-payment-gateway-script';
    let triplea_insertionLocation = document.getElementById(triplea_insertionId);

    const client_txid = triplea_insertionLocation.getAttribute('data-tx-id');
    if (!client_txid)
    {
      console.error('Missing client_txid');
      return;
    }

    let client_payload       = triplea_insertionLocation.getAttribute('data-payload') || '';
    let client_pubkey_shared = triplea_insertionLocation.getAttribute('data-pubkey-shared') || '';
    let client_api_id        = triplea_insertionLocation.getAttribute('data-api-id') || '';

    const sessionBtcAddr = triplea_insertionLocation.getAttribute('data-payment-addr');
    if (forceRefreshAddress || sessionBtcAddr === undefined || sessionBtcAddr === '')
    {
      const data     = {
        api_id: client_api_id,
        client_txid: client_txid,
        local_currency: 'USD',
        info: {
          type: 'woocommerce',
          plugin_v: TRIPLEA_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_VERSION,
        },
        payload: client_payload,
        public_key_shared: client_pubkey_shared,
      };
      const url      = `https://moneyoverip.io/api/set_addr`;
      const callback = function(data) {
        triplea_setPaymentQrCode(data, customerData);
      };
      const method   = "POST";

      triplea_ajax_action(url, callback, method, JSON.stringify(data));
    }
    else
    {
      const data = {
        status: 'ok',
        addr: sessionBtcAddr
      };
      // We already got a payment address. Keep using the same one until used for a tx.
      triplea_setPaymentQrCode(data);
    }
  }

  function triplea_checkBalance(result)
  {

    let intervalAmount = 4000; // every 4 seconds, re-check balance until tx is seen, then increase interval

    if (!result.balance)
    {
      console.error('Problem with data received from API');
    }
    else
    {
      let total       = 0;
      let confirmed   = 0;
      let unconfirmed = 0;
      if (result.balance.confirmed !== null && !isNaN(result.balance.confirmed) && Number(result.balance.confirmed) > 0)
      {
        confirmed = Number(result.balance.confirmed);
        total += confirmed;
      }
      if (result.balance.unconfirmed !== null && !isNaN(result.balance.unconfirmed) && Number(result.balance.unconfirmed) > 0)
      {
        unconfirmed = Number(result.balance.unconfirmed);
        total += unconfirmed;
      }

      triplea_createHiddenInputData('triplea_amount_btc_paid', total);
      triplea_createHiddenInputData('triplea_tx_count', tx_count);
      triplea_createHiddenInputData('triplea_balance_payload', result.payload);

      let btcAmountNode = document.getElementById('triplea_amount_btc_input');
      if (!btcAmountNode)
      {
        console.error('Bitcoin amount not available');
        return;
      }
      let btcAmount = Number(btcAmountNode.value);

      // Note: In current version, we don't bother showing step 3. Might confuse users. Straight to step 4.

      if (btcAmount && total >= btcAmount)
      {
        clearInterval(tripleaCountdownInterval);
        clearInterval(tripleaBalanceInterval);

        // Show message: payment confirmed
        triplea_showStep(4);

        triplea_submitForm();
      }
      else if (unconfirmed > 0)
      {
        if (unconfirmed < btcAmount)
        {
          let paidAmountNode       = document.getElementById('triplea_amount_btc_paid_2');
          paidAmountNode.innerText = 'BTC ' + unconfirmed.toString();

          let remainingAmountNode        = document.getElementById('triplea_amount_btc_remaining');
          remainingAmountNode.innerText  = triplea_formatCryptoPrice(btcAmount - unconfirmed);
          remainingAmountNode.setAttribute('data-paid-too-little', 'true');

          console.warn('Bitcoin payment: too little was paid. ' + unconfirmed + ' instead of ' + btcAmount);
          triplea_showStep(5);
          // Show message: transaction arrived, not enough paid, please pay ### (remainder)
          tx_count += 1;
        }
        else
        {
          // Show message: transaction arrived, awaiting confirmation by blockchain
          intervalAmount = 30000; // Will take more time, slowing down polling
          triplea_showStep(3);

          let remainingAmountNode = document.getElementById('triplea_amount_btc_remaining');
          remainingAmountNode.setAttribute('data-paid-too-little', 'false');

          // Counter can stop, enough was paid. Should hide counter.
          clearInterval(tripleaCountdownInterval);
          let countDownNodes = document.getElementsByClassName('triplea-countdown-timer');
          countDownNodes.style.display = 'none';

          // Can submit form right away, no need to wait for confirmation of transaction.
          triplea_submitForm();
        }

        triplea_createHiddenInputData('triplea_balance_payload', result.payload);
        triplea_createHiddenInputData('triplea_tx_count', tx_count);
        triplea_createHiddenInputData('triplea_amount_btc_paid', unconfirmed);

      }
      else
      {
        // QR code is shown... no change
      }

    }

    if (!tripleaBalanceInterval)
    {
      tripleaBalanceInterval = setInterval(function () {
        triplea_getBalance();
      }.bind(this), intervalAmount);
    }
  }

  /**
   * Provide a BTC address, and check the triplea_getBalance of that account.
   */
  function triplea_getBalance()
  {
    let triplea_insertionId       = 'triplea-payment-gateway-script';
    let triplea_insertionLocation = document.getElementById(triplea_insertionId);

    const tripleaPaymentAddr = triplea_insertionLocation.getAttribute('data-payment-addr');
    const url                = `https://moneyoverip.io/api/balance/${tripleaPaymentAddr}`;
    const callback           = triplea_checkBalance;
    const method             = "GET";
    const data               = null;

    triplea_ajax_action(url, callback, method, data);
  }

  function numberWithCommas(n)
  {
    n         = n.toString().replace(/,/g, "");
    let parts = parseFloat(n).toFixed(2).toString().split(".");
    return parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
  }

  function triplea_copyToClipboard(value)
  {
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

  function triplea_formatCryptoPrice(value)
  {
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
  var showError = function( errorMessage, selector ) {
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
  };


  const tripleaPaymentButtons = document.getElementsByClassName('triplea-payment-gateway-btn');
  for (let i = 0; i < tripleaPaymentButtons.length; i++)
  {
    tripleaPaymentButtons[i].removeEventListener('click', triplea_validateCheckout);
    tripleaPaymentButtons[i].addEventListener('click', triplea_validateCheckout);
  }

} )( jQuery, window, document );
