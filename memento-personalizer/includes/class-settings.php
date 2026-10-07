<?php
/**
 * Central configuration. Every threshold can be overridden in wp-config.php
 * with a MEMENTO_PZ_{KEY} constant (upper-cased) or via the
 * `memento_pz_settings` filter, so nothing is hardcoded in UI or logic.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Settings {

	/** @var array|null */
	private static $cache = null;

	public static function defaults() {
		return [
			// Upload limits.
			'max_file_mb'          => 15,       // Per uploaded file.
			'max_megapixels'       => 50,       // Reject decompression bombs before decoding.
			'max_photos'           => 48,       // Upper bound for "Required Photos".
			'allowed_mimes'        => [ 'image/jpeg', 'image/png', 'image/webp' ],

			// Image processing / print quality.
			'output_max_px'        => 2000,     // Production file longest side (never upscaled).
			'thumb_px'             => 400,
			'jpeg_quality'         => 92,
			'min_px_recommended'   => 600,      // Below this: "low quality" warning (5cm @ ~300dpi ≈ 590px).
			'min_px_reject'        => 250,      // Below this: upload rejected.
			'safe_area_percent'    => 8,        // Print-safe inset shown in the editor.

			// Sessions & cleanup.
			'temp_ttl_hours'       => 48,       // Abandoned upload sessions.
			'cart_ttl_hours'       => 168,      // Sessions already in a cart (WC session lifetime + margin).
			'retention_days'       => 0,        // Delete final order files N days after order completion. 0 = keep.
			'unpaid_retention_days' => 30,      // Delete files of cancelled/failed orders after N days. 0 = keep.
			'require_review_check' => true,     // Customer must tick "I've reviewed my photos".

			// Abuse protection (per IP hash).
			'rate_limit_uploads'   => 80,       // Uploads per window.
			'rate_limit_sessions'  => 30,       // Session inits per window.
			'rate_limit_window'    => 600,      // Seconds.

			// Dropbox.
			'dropbox_root'         => '/Orders', // Relative to the App Folder when using App Folder access.
			'dropbox_max_retries'  => 4,        // Automatic retries before staying "failed".

			// Diagnostics.
			'debug'                => false,    // Verbose logging (staging only).
		];
	}

	public static function all() {
		if ( null !== self::$cache ) {
			return self::$cache;
		}
		$settings = self::defaults();
		foreach ( $settings as $key => $value ) {
			$const = 'MEMENTO_PZ_' . strtoupper( $key );
			if ( defined( $const ) ) {
				$settings[ $key ] = constant( $const );
			}
		}
		self::$cache = (array) apply_filters( 'memento_pz_settings', $settings );
		return self::$cache;
	}

	public static function get( $key ) {
		$all = self::all();
		return $all[ $key ] ?? null;
	}

	public static function int( $key ) {
		return (int) self::get( $key );
	}
}
