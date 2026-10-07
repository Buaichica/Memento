<?php
/**
 * Upload-session ownership.
 *
 * Each browser gets a random, HttpOnly "owner" cookie. Sessions store only an
 * HMAC of that token, so a session ID on its own (e.g. leaked in a log or
 * guessed) is never enough to read or change someone else's photos.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Upload_Session {

	const COOKIE = 'memento_pz_owner';

	/** @var string|null Token set during this request (cookie not readable until next request). */
	private static $token = null;

	/** Get the caller's owner token, optionally issuing a new one. */
	public static function owner_token( $create = false ) {
		if ( self::$token ) {
			return self::$token;
		}
		$cookie = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';
		if ( preg_match( '/^[a-f0-9]{64}$/', $cookie ) ) {
			self::$token = $cookie;
			return $cookie;
		}
		if ( ! $create ) {
			return '';
		}
		self::$token = bin2hex( random_bytes( 32 ) );
		if ( ! headers_sent() ) {
			setcookie( self::COOKIE, self::$token, [
				'expires'  => time() + HOUR_IN_SECONDS * Memento_PZ_Settings::int( 'cart_ttl_hours' ),
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			] );
		}
		return self::$token;
	}

	public static function hash( $token ) {
		return hash_hmac( 'sha256', (string) $token, wp_salt( 'auth' ) );
	}

	/** Does the current request own this session? */
	public static function owns( $session ) {
		if ( ! $session ) {
			return false;
		}
		$token = self::owner_token();
		if ( $token && hash_equals( (string) $session->owner_hash, self::hash( $token ) ) ) {
			return true;
		}
		$user_id = get_current_user_id();
		return $user_id && (int) $session->user_id === $user_id;
	}

	public static function is_expired( $session ) {
		return strtotime( $session->expires_at . ' UTC' ) < time();
	}

	/** Can the session still be modified by the customer? */
	public static function is_editable( $session ) {
		return $session && 'open' === $session->state && ! self::is_expired( $session );
	}

	/** Customer-facing JSON for a session. */
	public static function payload( $session ) {
		$slots = [];
		foreach ( Memento_PZ_Repository::session_uploads( $session->session_id ) as $upload ) {
			if ( (int) $upload->slot_index < (int) $session->required_count ) {
				// Latest upload wins if duplicates exist for a slot.
				$slots[ (int) $upload->slot_index ] = self::slot_payload( $upload );
			}
		}
		return [
			'sessionId' => $session->session_id,
			'required'  => (int) $session->required_count,
			'slots'     => array_values( $slots ),
		];
	}

	public static function slot_payload( $upload ) {
		return [
			'slot'     => (int) $upload->slot_index,
			'uploadId' => $upload->upload_id,
			'thumbUrl' => Memento_PZ_File_Server::url( $upload, 'thumb' ),
			'fullUrl'  => Memento_PZ_File_Server::url( $upload, 'full' ),
			'width'    => (int) $upload->width,
			'quality'  => $upload->quality,
		];
	}
}
