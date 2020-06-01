<?php

$class_map = array(
	'Triplea_Payment_Gateway_For_Woocommerce'             => __DIR__ . '/includes/class-triplea-payment-gateway.php',
	'TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Loader_Interface' => __DIR__ . '/vendor/WPPB/interface-wppb-loader.php',
	'TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Loader' => __DIR__ . '/vendor/WPPB/class-wppb-loader.php',
	'TripleA_Payment_Gateway_For_WooCommerce\WPPB\WPPB_Object' => __DIR__ . '/vendor/WPPB/class-wppb-object.php',
	'TripleA_Payment_Gateway_For_WooCommerce\Admin\Admin' => __DIR__ . '/Admin/class-admin.php',
	'TripleA_Payment_Gateway_For_WooCommerce\Admin\Plugins_Page' => __DIR__ . '/Admin/class-plugins-page.php',
	'TripleA_Payment_Gateway_For_WooCommerce\Includes\I18n' => __DIR__ . '/Includes/class-i18n.php',
	'TripleA_Payment_Gateway_For_WooCommerce\WooCommerce\Payment_Gateways' => __DIR__ . '/WooCommerce/class-payment-gateways.php',
	'TripleA_Payment_Gateway_For_WooCommerce\WooCommerce\Thank_You' => __DIR__ . '/WooCommerce/class-thank-you.php',
	'TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment'   => __DIR__ . '/WooCommerce/triplea-payment-gateway-main-class.php',
);


spl_autoload_register(
	function ( $classname ) use ( $class_map ) {

		if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
			require_once $class_map[ $classname ];
		}
	}
);
