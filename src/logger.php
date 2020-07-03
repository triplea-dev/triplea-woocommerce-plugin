<?php

// TODO: Namespace.

if ( ! function_exists( 'triplea_write_log' ) ) {

	function triplea_write_log( $log, $logging_enabled = false ) {

		$uploads  = wp_upload_dir( null, false );
		$logs_dir = $uploads['basedir'] . '/triplea-bitcoin-payment-logs';

		if ( ! is_dir( $logs_dir ) ) {
			mkdir( $logs_dir, 0755, true );
		}
		$file = $logs_dir . '/' . 'triplea-bitcoin-payment-logs.log';

		if ( $logging_enabled ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ), 3, $file );
			} else {
				$datetime = date( 'Y-m-d h:i:sa' );
				error_log( $datetime . ' : ' . $log, 3, $file );
			}
			error_log( PHP_EOL, 3, $file );
		}
	}
}
