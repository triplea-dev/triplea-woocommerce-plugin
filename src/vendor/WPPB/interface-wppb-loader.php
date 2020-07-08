<?php

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\WPPB;

interface WPPB_Loader_Interface {

	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 );

	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 );

	public function run();

}

