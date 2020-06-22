<?php
/**
 * The plugins page functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    TripleA_Payment_Gateway_For_WooCommerce
 * @subpackage TripleA_Payment_Gateway_For_WooCommerce/admin
 */

namespace TripleA_Payment_Gateway_For_WooCommerce\Admin;

/**
 * The plugins page functionality of the plugin.
 *
 * Adds Settings link on the plugins page.
 *
 * @package    TripleA_Payment_Gateway_For_WooCommerce
 * @subpackage TripleA_Payment_Gateway_For_WooCommerce/admin
 * @author     TripleA <andy.hoebeke@triple-a.io>
 */
class Plugins_Page {

	/**
	 * Add a link to the configuration under WooCommerce's payment gateway settings page.
	 *
	 * TODO: When deactivate_plugin is forced, the Settings link still appears.
	 *
	 * @hooked plugin_action_links_{plugin basename}
	 * @see \WP_Plugins_List_Table::display_rows()
	 *
	 * @param string[] $links The links that will be shown below the plugin name on plugins.php.
	 *
	 * @return string[]
	 */
	public function display_plugin_action_links( $links ) {
		$setting_link    = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway' );
		$conversion_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway#conversion' );
		$plugin_links    = array(
			'<a href="' . $setting_link . '">' . __( 'Settings', 'triplea-payment-gateway-for-woocommerce' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

}
