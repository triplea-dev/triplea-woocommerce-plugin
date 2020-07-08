<?php
/**
 * Tests for Admin.
 *
 * @package TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce
 * @author  TripleA <andy.hoebeke@triple-a.io>
 */

namespace TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\Admin;

use TripleA_Cryptocurrency_Payment_Gateway_for_WooCommerce\Admin\Admin;

/**
 * Class Admin_Test
 *
 * @see Admin
 */
class Admin_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * Hook into the active_plugins filter to make it seem WooCommerce is active.
	 * Verify two admin notices are added.
	 */
	public function test_admin_notice_when_woocommerce_inactive() {

		$plugin_basename = 'tripea-payment-gateway-for-woocommerce/tripea-payment-gateway-for-woocommerce.php';
		if ( ! defined( 'TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_FILE' ) ) {
			define( 'TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_FILE', $plugin_basename );
		}

		$expected_action = 'admin_notices';
		remove_all_filters( $expected_action );

		// Remove WooCommerce from the active plugins list.
		add_filter(
			'active_plugins',
			function( $active_plugins ) {
				$index = array_search( 'woocommerce/woocommerce.php', $active_plugins ); // search the value to find index
				if ( $index !== false ) {
					unset( $active_plugins[ $index ] );
				}
				return $active_plugins;
			}
		);

		$admin = new Admin();
		$admin->woocommerce_check();

		global $wp_filter;

		// After.
		$count_after_by_priority = array_map(
			function( $element ) {
				return count( $element );
			},
			$wp_filter[ $expected_action ]->callbacks
		);
		$count_after             = array_sum( $count_after_by_priority );

		$this->assertEquals( 2, $count_after );

	}


	public function test_no_admin_notice_when_woocommerce_active() {

		$admin = new Admin();

		$admin->woocommerce_check();

		// If WooCommerce is installed, nothing should happen

		// Make it looks as though WooCommerce is active.
		add_filter(
			'active_plugins',
			function( $active_plugins ) {
				$active_plugins[] = 'woocommerce/woocommerce.php';
				return $active_plugins;
			}
		);

	}
	/**
	 * Check the 'deactivated_plugin' action is called for this plugin when WooCommerce is not active.
	 *
	 * @see deactivate_plugins()
	 * @see get_option()
	 */
	public function test_deactivate_plugin_when_woocommerce_inactive() {

		$plugin_basename = 'tripea-payment-gateway-for-woocommerce/tripea-payment-gateway-for-woocommerce.php';
		if ( ! defined( 'TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_FILE' ) ) {
			define( 'TRIPLEA_CRYPTOCURRENCY_PAYMENT_GATEWAY_FOR_WOOCOMMERCE_MAIN_FILE', $plugin_basename );
		}

		// The plugin isn't active during unit tests. Add it to the option and the filter.
		add_filter(
			'pre_option_active_plugins',
			function( $retval, $option, $default ) use ( $plugin_basename ) {
				return array( $plugin_basename );
			},
			10,
			3
		);

		add_filter(
			'active_plugins',
			function( $active_plugins ) use ( $plugin_basename ) {
				$active_plugins[] = $plugin_basename;
				return $active_plugins;
			}
		);

		// TODO: Should activate WooCommerce
		// Remove WooCommerce from the active plugins list.
		add_filter(
			'active_plugins',
			function( $active_plugins ) {
				$index = array_search( 'woocommerce/woocommerce.php', $active_plugins ); // search the value to find index
				if ( $index !== false ) {
					unset( $active_plugins[ $index ] );  // $arr = ['b', 'c']
				}
				return $active_plugins;
			}
		);

		$is_plugin_deactivated = false;

		add_action(
			'deactivated_plugin',
			function( $plugin, $network_deactivating ) use ( $plugin_basename, &$is_plugin_deactivated ) {
				if ( $plugin_basename === $plugin ) {
					$is_plugin_deactivated = true;
				}
			},
			10,
			2
		);

		$admin = new Admin();
		$admin->woocommerce_check();

		$this->assertTrue( $is_plugin_deactivated );

	}

	/**
	 * If the old settings exist and the new do not, we need to update.
	 *
	 * Since the function is protected, we have to extend the class to access it.
	 *
	 * @see get_option()
	 */
	public function test_settings_upgrade_required_when_old_settings_present_new_settings_absent() {

		$plugin_settings_option_name = 'woocommerce_triplea_payment_gateway_settings';
		$plugin_settings             = array();

		// Old version will have this.
		$plugin_settings['triplea_pubkey_id']                = 'triplea_pubkey_id';
		$plugin_settings['triplea_pubkey_id_for_conversion'] = 'triplea_pubkey_id_for_conversion';

		$specify_settings = function( $retval, $option, $default ) use ( $plugin_settings ) {
			return $plugin_settings;
		};
		add_filter( 'pre_option_' . $plugin_settings_option_name, $specify_settings, 10, 3 );

		$admin = new class() extends Admin {
			public function is_settings_upgrade_required() {
				return $this->settings_upgrade_required();
			}
		};

		$this->assertTrue( $admin->is_settings_upgrade_required() );

	}

	/**
	 * If no settings exist, we do not need to (can not possibly) update.
	 *
	 * @see get_option()
	 */
	public function test_settings_upgrade_not_required_when_settings_absent() {

		$plugin_settings_option_name = 'woocommerce_triplea_payment_gateway_settings';
		$plugin_settings             = null;

		$specify_settings = function( $retval, $option, $default ) use ( $plugin_settings ) {
			return $plugin_settings;
		};
		add_filter( 'pre_option_' . $plugin_settings_option_name, $specify_settings, 10, 3 );

		$admin = new class() extends Admin {
			public function is_settings_upgrade_required() {
				return $this->settings_upgrade_required();
			}
		};

		$this->assertFalse( $admin->is_settings_upgrade_required() );

	}

	/**
	 * If the old settings do not exist, we do not need to (can not possibly) update.
	 *
	 * @see get_option()
	 */
	public function test_settings_upgrade_not_required_when_old_settings_absent() {

		$plugin_settings_option_name = 'woocommerce_triplea_payment_gateway_settings';
		$plugin_settings             = array();

		// Old version will have this.
		$plugin_settings['triplea_server_public_enc_key_btc']        = 'triplea_server_public_enc_key_btc';
		$plugin_settings['triplea_server_public_enc_key_conversion'] = 'triplea_server_public_enc_key_conversion';

		$specify_settings = function( $retval, $option, $default ) use ( $plugin_settings ) {
			return $plugin_settings;
		};
		add_filter( 'pre_option_' . $plugin_settings_option_name, $specify_settings, 10, 3 );

		$admin = new class() extends Admin {
			public function is_settings_upgrade_required() {
				return $this->settings_upgrade_required();
			}
		};

		$this->assertFalse( $admin->is_settings_upgrade_required() );

	}

	/**
	 * If the old settings exist and the new do, we do not need to update.
	 *
	 * @see get_option()
	 */
	public function test_settings_upgrade_not_required_when_new_settings_present() {

		$plugin_settings_option_name = 'woocommerce_triplea_payment_gateway_settings';
		$plugin_settings             = array();

		// Old version will have this.
		$plugin_settings['triplea_pubkey_id']                        = 'triplea_pubkey_id';
		$plugin_settings['triplea_pubkey_id_for_conversion']         = 'triplea_pubkey_id_for_conversion';
		$plugin_settings['triplea_server_public_enc_key_btc']        = 'triplea_server_public_enc_key_btc';
		$plugin_settings['triplea_server_public_enc_key_conversion'] = 'triplea_server_public_enc_key_conversion';

		$specify_settings = function( $retval, $option, $default ) use ( $plugin_settings ) {
			return $plugin_settings;
		};
		add_filter( 'pre_option_' . $plugin_settings_option_name, $specify_settings, 10, 3 );

		$admin = new class() extends Admin {
			public function is_settings_upgrade_required() {
				return $this->settings_upgrade_required();
			}
		};

		$this->assertFalse( $admin->is_settings_upgrade_required() );

	}

}
