<?php

$class_map = array(
	'TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Loader_Interface' => __DIR__ . '/vendor/WPPB/interface-wppb-loader.php',
	'TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Loader' => __DIR__ . '/vendor/WPPB/class-wppb-loader.php',
	'TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment' => __DIR__ . '/WooCommerce/triplea-payment-gateway-main-class.php'
);

spl_autoload_register(
	function ( $classname ) use ( $class_map ) {

		if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
			require_once $class_map[ $classname ];
		}
	}
);
