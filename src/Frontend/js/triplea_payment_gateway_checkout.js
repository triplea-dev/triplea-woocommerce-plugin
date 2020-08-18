(function($) {

  //console.debug('triplea_payment_gateway_checkout start');


  /**
   * When the "Place Order" button is pressed, verify if a payment was made.
   * If no payment was made yet, display a message above the button asking
   * the user if he is certain about wanting to place an order without having
   * made a payment.
   */
  function verifyPlaceOrder() {
    console.debug('verifyPlaceOrder()');

    let checkout_form = $( 'form.woocommerce-checkout' );

    // TODO display a message

    // deactivate the tokenRequest function event
    checkout_form.off( 'checkout_place_order', verifyPlaceOrder );
    // submit the form now
    //checkout_form.submit();
    // TODO Something wrong here, does not properly place the order.. returns to same page (checkout page)

    //return false; // Prevents page from submitting/placing the order
  }

  // jQuery(function($){
  //   let checkout_form = $( 'form.woocommerce-checkout' );
  //   checkout_form.on( 'checkout_place_order', verifyPlaceOrder );
  // });

  //console.debug('triplea_payment_gateway_checkout finished');

})(jQuery);
