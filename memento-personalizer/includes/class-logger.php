<?php
/**
 * Logging via the WooCommerce logger (WooCommerce → Status → Logs, source
 * "memento-personalizer"). Never pass image content, tokens or secrets here.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Logger {

	const SOURCE = 'memento-personalizer';

	public static function error( $message, array $context = [] ) {
		self::log( 'error', $message, $context );
	}

	public static function warning( $message, array $context = [] ) {
		self::log( 'warning', $message, $context );
	}

	public static function info( $message, array $context = [] ) {
		self::log( 'info', $message, $context );
	}

	public static function debug( $message, array $context = [] ) {
		if ( Memento_PZ_Settings::get( 'debug' ) ) {
			self::log( 'debug', $message, $context );
		}
	}

	private static function log( $level, $message, array $context ) {
		$context['source'] = self::SOURCE;
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->log( $level, self::redact( $message ), $context );
			return;
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[memento-personalizer][' . $level . '] ' . self::redact( $message ) );
	}

	/** Strip anything that looks like a bearer token or secret from a message. */
	public static function redact( $message ) {
		$message = (string) $message;
		$message = preg_replace( '/Bearer\s+[A-Za-z0-9\-\._~\+\/]+=*/', 'Bearer [redacted]', $message );
		foreach ( [ 'MEMENTO_DROPBOX_APP_SECRET', 'MEMENTO_DROPBOX_REFRESH_TOKEN', 'MEMENTO_DROPBOX_APP_KEY' ] as $const ) {
			if ( defined( $const ) && constant( $const ) ) {
				$message = str_replace( (string) constant( $const ), '[redacted]', $message );
			}
		}
		return $message;
	}
}
