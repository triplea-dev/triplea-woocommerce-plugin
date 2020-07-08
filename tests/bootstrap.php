<?php
/**
 * @package           TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 */

$GLOBALS['project_root_dir']   = $project_root_dir  = dirname( __FILE__, 2 );
$GLOBALS['plugin_root_dir']    = $plugin_root_dir   = $project_root_dir . '/src';
$GLOBALS['plugin_name']        = $plugin_name       = basename( $project_root_dir );
$GLOBALS['plugin_name_php']    = $plugin_name_php   = $plugin_name . '.php';
$GLOBALS['plugin_path_php']    = $plugin_root_dir . '/' . $plugin_name_php;
$GLOBALS['plugin_basename']    = $plugin_name . '/' . $plugin_name_php;
$GLOBALS['wordpress_root_dir'] = $project_root_dir . '/vendor/wordpress/wordpress/src';

require_once $plugin_root_dir . '/autoload.php';


$class_map = array(
	'WC_Payment_Gateway' => $project_root_dir . '/wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-payment-gateway.php',
	'WC_Settings_API'    => $project_root_dir . '/wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-settings-api.php',
);


spl_autoload_register(
	function ( $classname ) use ( $class_map ) {

		if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
			require_once $class_map[ $classname ];
		}
	}
);
