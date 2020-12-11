<?php
/**
 * The admin functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @subpackage TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce/admin
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\Admin;

/**
 * The admin functionality of the plugin.
 *
 * Checks for WooCommerce and adds admin_notices.
 *
 * @package    TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @subpackage TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce/admin
 * @author     TripleA <andy.hoebeke@triple-a.io>
 */
class Admin {

	/**
	 * Check is WooCommerce active.
	 *
	 * If not, show a notice to the user that it is needed.
	 * Prevent the "plugin activated" notice from appearing.
	 * Deactivate this plugin.
	 *
	 * @hooked plugins_loaded
	 */
	public function woocommerce_check() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) || function_exists( 'woocommerce_cart_totals' ) || function_exists( 'woocommerce_content' ) ) {
			add_action( 'admin_notices', array( $this, 'print_wc_needed' ), 1 );
			add_action( 'admin_notices', array( $this, 'wc_admin_notices' ), 1 );
			deactivate_plugins( TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_FILE );
		}
	}

	/**
	 * Print the notice stating the WooCommerce requirement.
	 *
	 * TODO: Link to the Add Plugin page rather than WooCommerce.com.
	 * plugin-install.php?s=WooCommerce&tab=search&type=term
	 *
	 * @hooked admin_notices
	 */
	public function print_wc_needed() {

		/* translators: 1. URL link. */
		echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'TripleA Bitcoin Payment Gateway plugin requires WooCommerce to be installed and active. You can download %s here.', 'triplea-cryptocurrency-payment-gateway-for-woocommerce' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
	}

	/**
	 * Suppress "Plugin activated" notice.
	 *
	 * @hooked admin_notices
	 */
	public function wc_admin_notices() {

		// Since we're not using the value, we should be safe to unset it!
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		unset( $_GET['activate'] );

		add_filter(
			'gettext',
			function ( $translated_text, $untranslated_text, $domain ) {
				$old = array(
					'Plugin <strong>activated</strong>.',
					'Selected plugins <strong>activated</strong>.',
				);
				$new = "<span style='color:red'>Bitcoin Payment Gateway for WooCommerce (by TripleA) </span> - Plugin needs WooCommerce to work.";

				if ( in_array( $untranslated_text, $old, true ) ) {
					$translated_text = $new;
				}
				return $translated_text;
			},
			99,
			3
		);
	}

	/**
	 * Display an admin notice if settings need to be updated from pre v1.0.5 versions.
	 *
	 * This displays a link to settings where saving will update.
	 *
	 * @hooked admin_notices
	 */
	public function settings_update_notice() {
      global $pagenow;
      
		// Only show this if we detect an upgrade from an old version.

      $plugin_setting_option_name = 'woocommerce_triplea_payment_gateway_settings';
      $plugin_settings            = get_option( $plugin_setting_option_name );
      
      $old_btc2fiat_api_id = isset($plugin_settings['triplea_pubkey_id_for_conversion']) ? $plugin_settings['triplea_pubkey_id_for_conversion'] : null;
      $old_btc2btc_api_id = isset($plugin_settings['triplea_pubkey_id']) ? $plugin_settings['triplea_pubkey_id'] : null;
      $fiat_merchant_key = isset($plugin_settings['triplea_btc2fiat_merchant_key']) ? $plugin_settings['triplea_btc2fiat_merchant_key'] : null;
      $btc_merchant_key = isset($plugin_settings['triplea_btc2btc_merchant_key']) ? $plugin_settings['triplea_btc2btc_merchant_key'] : null;
      $btc_sandbox_merchant_key = isset($plugin_settings['triplea_btc2btc_sandbox_merchant_key']) ? $plugin_settings['triplea_btc2btc_sandbox_merchant_key'] : null;
      
      if ((!empty($old_btc2fiat_api_id) || !empty($old_btc2btc_api_id)) && empty($fiat_merchant_key) && empty($btc_merchant_key) && empty($btc_sandbox_merchant_key))
      {
         if ( $pagenow == 'admin.php' || $pagenow == 'plugins.php' || $pagenow == 'plugin-install.php' )
         {
            $class        = 'notice notice-info notice-large';
            $setting_link = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=triplea_payment_gateway' );
            $setting_text = __( 'Bitcoin payment settings', 'triplea-cryptocurrency-payment-gateway-for-woocommerce' );

            $message      = 'Your "Bitcoin Payment Gateway for WooCommerce" plugin has been disabled after the update. <br>
                          <b>Please take a short moment to update the settings</b> and re-enable bitcoin payments. &nbsp; &nbsp; <a href="' . $setting_link . '" target="_self">' . $setting_text . '</a>';
            $allowed_html = array(
               'a' => array(
                  'href'   => array(),
                  'target' => array(),
               ),
               'b' => array(),
               'br' => array(),
            );

            printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses( $message, $allowed_html ) );
         }
      }
	}

	/**
	 * Returns TRUE if an upgrade from v1.0.5 or older is detected and settings
	 * update is required. Returns FALSE if there is no upgrade from older version
	 * required (never installed older version, or settings update is done).
	 *
	 * @return bool
	 */
	protected function settings_upgrade_required() {

		// Format: $wc_plugin_id + $plugin_own_id + option key.
		$plugin_setting_option_name = 'woocommerce_triplea_payment_gateway_settings';
		$plugin_settings            = get_option( $plugin_setting_option_name );

		// If we have no settings to update.
		if ( empty( $plugin_settings ) ) {
			return false;
		}

		// If the old setting never existed.
		if ( ! isset( $plugin_settings['triplea_pubkey_id'] )
		  && ! isset( $plugin_settings['triplea_pubkey_id_for_conversion'] ) ) {
			return false;
		}

		// If the new settings have already been set.
		if ( isset( $plugin_settings['triplea_btc2fiat_api_id'] )
		  || isset( $plugin_settings['triplea_btc2fiat_sandbox_api_id'] )
		  || isset( $plugin_settings['triplea_btc2btc_api_id'] )
		  || isset( $plugin_settings['triplea_btc2btc_sandbox_api_id'] )
      ) {
			return false;
		}

		return true;

	}

}
