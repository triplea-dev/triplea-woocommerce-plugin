<?php
/**
 * @package           TripleA_Payment_Gateway_For_WooCommerce
 */

$GLOBALS['project_root_dir']   = $project_root_dir  = dirname( __FILE__, 2 );
$GLOBALS['plugin_root_dir']    = $plugin_root_dir   = $project_root_dir . '/src';
$GLOBALS['plugin_name']        = $plugin_name       = basename( $project_root_dir );
$GLOBALS['plugin_name_php']    = $plugin_name_php   = $plugin_name . '.php';
$GLOBALS['plugin_path_php']                         = $plugin_root_dir . '/' . $plugin_name_php;
$GLOBALS['plugin_basename']                         = $plugin_name . '/' . $plugin_name_php;
$GLOBALS['wordpress_root_dir']                      = $project_root_dir . '/vendor/wordpress/wordpress/src';


// Autoload

$class_map = array(
	'TripleA_Payment_Gateway_For_WooCommerce\includes\I18n' => $plugin_root_dir . '/includes/class-i18n.php',
	'TripleA_Bitcoin_Ecommerce_for_WooCommerce_Payment' => $plugin_root_dir . '/includes/triplea-payment-gateway-main-class.php',
	'TripleA_Payment_Gateway_For_Woocommerce' => $plugin_root_dir . '/includes/class-triplea-payment-gateway.php',

);

spl_autoload_register(
	function ( $classname ) use ( $class_map ) {

		if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
			require_once $class_map[ $classname ];
		}
	}
);
