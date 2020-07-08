<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://triple-a.io
 * @since      1.0.0
 *
 * @package    TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// define a vairbale and store an option name as the value.
$plugin_options = 'woocommerce_triplea_payment_gateway_settings'; // Format: $wc_plugin_id + $plugin_own_id + option key
$option_name    = $plugin_options;

// call delete option and use the variable inside the quotations.
update_option( $option_name, null );
delete_option( $option_name );

// for site options in Multisite.
// delete_site_option($option_name);
