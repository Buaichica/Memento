<?php
/**
 * Customer AJAX endpoints (admin-ajax.php):
 *
 *   memento_pz_init    start or resume an upload session, returns a fresh nonce
 *   memento_pz_upload  upload/replace the photo in one slot
 *   memento_pz_delete  clear one slot
 *   memento_pz_swap    reorder two slots
 *
 * Every mutating call requires a valid nonce AND ownership of the session.
 * admin-ajax responses are never page-cached (SiteGround excludes admin-ajax).
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Upload_Handler {

	const NONCE = 'memento_pz_upload';

	public static function init() {
		foreach ( [ 'init', 'upload', 'delete', 'swap' ] as $action ) {
			add_action( "wp_ajax_memento_pz_{$action}", [ __CLASS__, "ajax_{$action}" ] );
			add_action( "wp_ajax_nopriv_memento_pz_{$action}", [ __CLASS__, "ajax_{$action}" ] );
		}
	}

	/* ── Endpoints ────────────────────────────────────────── */

	public static function ajax_init() {
		self::no_cache();
		if ( ! self::rate_limit( 'sessions', Memento_PZ_Settings::int( 'rate_limit_sessions' ) ) ) {
			self::fail( 'rate_limited', 429 );
		}

		$product_id   = absint( $_POST['product_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$variation_id = absint( $_POST['variation_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$session_id   = sanitize_key( wp_unslash( $_POST['session_id'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification

		$resolved = self::resolve_product( $product_id, $variation_id );
		if ( is_wp_error( $resolved ) ) {
			self::fail( $resolved->get_error_code() );
		}
		list( $product_id, $variation_id, $required ) = $resolved;

		$session = $session_id ? Memento_PZ_Repository::get_session( $session_id ) : null;
		$resume  = $session
			&& Memento_PZ_Upload_Session::owns( $session )
			&& Memento_PZ_Upload_Session::is_editable( $session )
			&& (int) $session->product_id === $product_id;

		if ( $resume ) {
			// Variation/pack size changed: adjust required count and drop surplus slots.
			if ( (int) $session->required_count !== $required || (int) $session->variation_id !== $variation_id ) {
				foreach ( Memento_PZ_Repository::session_uploads( $session->session_id ) as $upload ) {
					if ( (int) $upload->slot_index >= $required ) {
						self::destroy_upload( $upload );
					}
				}
				Memento_PZ_Repository::update_session( $session->session_id, [
					'required_count' => $required,
					'variation_id'   => $variation_id,
				] );
			}
			Memento_PZ_Repository::touch_session( $session->session_id, Memento_PZ_Settings::int( 'temp_ttl_hours' ) );
			$session = Memento_PZ_Repository::get_session( $session->session_id );
		} else {
			$token      = Memento_PZ_Upload_Session::owner_token( true );
			$session_id = Memento_PZ_Repository::create_session( [
				'owner_hash'     => Memento_PZ_Upload_Session::hash( $token ),
				'user_id'        => get_current_user_id(),
				'product_id'     => $product_id,
				'variation_id'   => $variation_id,
				'required_count' => $required,
			] );
			if ( ! $session_id ) {
				Memento_PZ_Logger::error( 'Could not create upload session (DB insert failed).' );
				self::fail( 'server' );
			}
			$session = Memento_PZ_Repository::get_session( $session_id );
		}

		wp_send_json_success( array_merge(
			Memento_PZ_Upload_Session::payload( $session ),
			[ 'nonce' => wp_create_nonce( self::NONCE ) ]
		) );
	}

	public static function ajax_upload() {
		self::no_cache();
		$session = self::require_editable_session();
		if ( ! self::rate_limit( 'uploads', Memento_PZ_Settings::int( 'rate_limit_uploads' ) ) ) {
			self::fail( 'rate_limited', 429 );
		}

		$slot = self::slot_param( 'slot', $session );

		if ( empty( $_FILES['photo'] ) || ! is_array( $_FILES['photo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			self::fail( 'no_file' );
		}
		$file  = $_FILES['photo']; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$error = (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE );
		if ( UPLOAD_ERR_INI_SIZE === $error || UPLOAD_ERR_FORM_SIZE === $error ) {
			self::fail( 'too_large' );
		}
		if ( UPLOAD_ERR_OK !== $error || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			Memento_PZ_Logger::warning( 'Upload rejected, PHP upload error code ' . $error );
			self::fail( 'no_file' );
		}

		$source    = ( isset( $_POST['source'] ) && 'edited' === $_POST['source'] ) ? 'edited' : 'original'; // phpcs:ignore WordPress.Security.NonceVerification
		$upload_id = Memento_PZ_Storage::random_id();
		$base      = 'temp/' . $session->session_id . '/' . $upload_id;

		$result = Memento_PZ_Image_Processor::process( $file['tmp_name'], $base . '.jpg', $base . '-thumb.jpg' );
		if ( is_wp_error( $result ) ) {
			Memento_PZ_Logger::info( sprintf( 'Upload rejected for session %s slot %d: %s', substr( $session->session_id, 0, 8 ), $slot, $result->get_error_code() ) );
			wp_send_json_error( [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ], 422 );
		}

		// Replace whatever was in this slot so edits don't accumulate orphans.
		$previous = Memento_PZ_Repository::slot_upload( $session->session_id, $slot );

		$inserted = Memento_PZ_Repository::insert_upload( [
			'upload_id'    => $upload_id,
			'session_id'   => $session->session_id,
			'slot_index'   => $slot,
			'product_id'   => (int) $session->product_id,
			'variation_id' => (int) $session->variation_id,
			'storage_key'  => $base . '.jpg',
			'thumb_key'    => $result['thumb_key'],
			'mime_type'    => $result['mime_type'],
			'width'        => $result['width'],
			'height'       => $result['height'],
			'bytes'        => $result['bytes'],
			'src_width'    => $result['src_width'],
			'src_height'   => $result['src_height'],
			'source'       => $source,
			'quality'      => $result['quality'],
			'state'        => 'temporary',
		] );
		if ( ! $inserted ) {
			Memento_PZ_Storage::delete( $base . '.jpg' );
			Memento_PZ_Storage::delete( $base . '-thumb.jpg' );
			Memento_PZ_Logger::error( 'Could not insert upload record.' );
			self::fail( 'server' );
		}
		if ( $previous ) {
			self::destroy_upload( $previous );
		}

		Memento_PZ_Repository::touch_session( $session->session_id, Memento_PZ_Settings::int( 'temp_ttl_hours' ) );
		Memento_PZ_Logger::debug( sprintf( 'Session %s slot %d stored (%s, %dpx, %s).', substr( $session->session_id, 0, 8 ), $slot, $source, $result['crop_px'], $result['quality'] ) );

		wp_send_json_success( Memento_PZ_Upload_Session::slot_payload( Memento_PZ_Repository::get_upload( $upload_id ) ) );
	}

	public static function ajax_delete() {
		self::no_cache();
		$session = self::require_editable_session();
		$slot    = self::slot_param( 'slot', $session );
		$upload  = Memento_PZ_Repository::slot_upload( $session->session_id, $slot );
		if ( $upload ) {
			self::destroy_upload( $upload );
		}
		wp_send_json_success( Memento_PZ_Upload_Session::payload( $session ) );
	}

	public static function ajax_swap() {
		self::no_cache();
		$session = self::require_editable_session();
		$from    = self::slot_param( 'from', $session );
		$to      = self::slot_param( 'to', $session );
		if ( $from !== $to ) {
			$a = Memento_PZ_Repository::slot_upload( $session->session_id, $from );
			$b = Memento_PZ_Repository::slot_upload( $session->session_id, $to );
			if ( $a ) {
				Memento_PZ_Repository::update_upload( $a->upload_id, [ 'slot_index' => $to ] );
			}
			if ( $b ) {
				Memento_PZ_Repository::update_upload( $b->upload_id, [ 'slot_index' => $from ] );
			}
		}
		wp_send_json_success( Memento_PZ_Upload_Session::payload( $session ) );
	}

	/* ── Helpers ──────────────────────────────────────────── */

	/**
	 * Resolve and validate the product (and variation) the customer is personalising.
	 *
	 * @return array|WP_Error [ product_id, variation_id, required ]
	 */
	public static function resolve_product( $product_id, $variation_id ) {
		$product = $product_id ? wc_get_product( $product_id ) : null;
		if ( ! $product || 'publish' !== $product->get_status() ) {
			return new WP_Error( 'bad_product' );
		}
		$target = $product;
		if ( $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( ! $variation || ! $variation->is_type( 'variation' ) || (int) $variation->get_parent_id() !== (int) $product_id ) {
				return new WP_Error( 'bad_product' );
			}
			$target = $variation;
		}
		$required = Memento_PZ_Product_Config::get_required_photos( $target );
		if ( $required < 1 ) {
			return new WP_Error( 'not_personalised' );
		}
		return [ (int) $product_id, (int) $variation_id, $required ];
	}

	/** Verify nonce + ownership + editable state, or exit with a JSON error. */
	private static function require_editable_session() {
		$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			self::fail( 'nonce', 403 );
		}
		$session = Memento_PZ_Repository::get_session( sanitize_key( wp_unslash( $_POST['session_id'] ?? '' ) ) );
		if ( ! $session || ! Memento_PZ_Upload_Session::owns( $session ) ) {
			Memento_PZ_Logger::warning( 'Upload request for a session the caller does not own.' );
			self::fail( 'session', 403 );
		}
		if ( ! Memento_PZ_Upload_Session::is_editable( $session ) ) {
			self::fail( 'session_locked', 409 );
		}
		return $session;
	}

	private static function slot_param( $name, $session ) {
		$raw = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! is_scalar( $raw ) || ! preg_match( '/^\d{1,3}$/', (string) $raw ) || (int) $raw >= (int) $session->required_count ) {
			self::fail( 'bad_slot' );
		}
		return (int) $raw;
	}

	/** Delete an upload's files and record. */
	public static function destroy_upload( $upload ) {
		if ( 'final' === $upload->state ) {
			return; // Never touch files that belong to an order.
		}
		Memento_PZ_Storage::delete( $upload->storage_key );
		if ( $upload->thumb_key ) {
			Memento_PZ_Storage::delete( $upload->thumb_key );
		}
		Memento_PZ_Repository::delete_upload( $upload->upload_id );
	}

	/**
	 * Simple fixed-window rate limiter keyed by a hash of the client IP.
	 */
	private static function rate_limit( $bucket, $limit ) {
		if ( $limit <= 0 ) {
			return true;
		}
		$ip     = class_exists( 'WC_Geolocation' ) ? WC_Geolocation::get_ip_address() : ( $_SERVER['REMOTE_ADDR'] ?? '' ); // phpcs:ignore
		$window = max( 60, Memento_PZ_Settings::int( 'rate_limit_window' ) );
		$key    = 'memento_pz_rl_' . md5( $bucket . '|' . $ip . '|' . floor( time() / $window ) );
		$count  = (int) get_transient( $key );
		if ( $count >= $limit ) {
			Memento_PZ_Logger::warning( "Rate limit hit for bucket {$bucket}." );
			return false;
		}
		set_transient( $key, $count + 1, $window );
		return true;
	}

	private static function no_cache() {
		nocache_headers();
		header( 'X-Robots-Tag: noindex' );
	}

	/** Exit with a customer-safe JSON error. */
	private static function fail( $code, $status = 400 ) {
		$messages = [
			'rate_limited'     => __( 'Too many requests. Please wait a few minutes and try again.', 'memento-personalizer' ),
			'bad_product'      => __( 'This product can\'t be personalised right now.', 'memento-personalizer' ),
			'not_personalised' => __( 'This product doesn\'t need photos.', 'memento-personalizer' ),
			'nonce'            => __( 'Your session has expired. Please refresh the page.', 'memento-personalizer' ),
			'session'          => __( 'Your photo session has expired. Please refresh the page.', 'memento-personalizer' ),
			'session_locked'   => __( 'These photos are already in your cart. Refresh the page to start a new set.', 'memento-personalizer' ),
			'bad_slot'         => __( 'Something went wrong with that photo slot. Please refresh the page.', 'memento-personalizer' ),
			'no_file'          => __( 'We didn\'t receive a photo. Please try again.', 'memento-personalizer' ),
			'too_large'        => Memento_PZ_Image_Processor::error( 'too_large' )->get_error_message(),
			'server'           => __( 'Something went wrong on our side. Please try again.', 'memento-personalizer' ),
		];
		wp_send_json_error( [ 'code' => $code, 'message' => $messages[ $code ] ?? $messages['server'] ], $status );
	}
}
