<?php
/**
 * Minimal Dropbox API v2 client.
 *
 * Credentials are read from wp-config.php constants or environment variables
 * (never from theme/plugin source or the database):
 *   MEMENTO_DROPBOX_APP_KEY, MEMENTO_DROPBOX_APP_SECRET, MEMENTO_DROPBOX_REFRESH_TOKEN
 *
 * Recommended Dropbox app: Scoped access → **App folder** (least privilege),
 * permissions files.content.write + files.content.read. Paths are then
 * relative to /Apps/{your app name}/.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Dropbox_Client {

	private static function credential( $name ) {
		if ( defined( $name ) && constant( $name ) ) {
			return (string) constant( $name );
		}
		$env = getenv( $name );
		return $env ? (string) $env : '';
	}

	public static function is_configured() {
		return self::credential( 'MEMENTO_DROPBOX_APP_KEY' ) && self::credential( 'MEMENTO_DROPBOX_APP_SECRET' ) && self::credential( 'MEMENTO_DROPBOX_REFRESH_TOKEN' );
	}

	/** @return string|WP_Error Short-lived access token. */
	public static function access_token() {
		$resp = wp_remote_post( 'https://api.dropboxapi.com/oauth2/token', [
			'timeout' => 20,
			'body'    => [
				'grant_type'    => 'refresh_token',
				'refresh_token' => self::credential( 'MEMENTO_DROPBOX_REFRESH_TOKEN' ),
				'client_id'     => self::credential( 'MEMENTO_DROPBOX_APP_KEY' ),
				'client_secret' => self::credential( 'MEMENTO_DROPBOX_APP_SECRET' ),
			],
		] );
		if ( is_wp_error( $resp ) ) {
			return new WP_Error( 'dropbox_network', 'Token request failed: ' . $resp->get_error_code() );
		}
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code || empty( $body['access_token'] ) ) {
			$reason = is_array( $body ) ? sanitize_text_field( $body['error'] ?? 'unknown' ) : 'unknown';
			return new WP_Error( 'dropbox_auth', sprintf( 'Token request rejected (HTTP %d, %s). Check the Dropbox credentials.', $code, $reason ) );
		}
		return $body['access_token'];
	}

	/**
	 * Upload a file, overwriting any previous copy at the same path, so
	 * retries are idempotent and never create "(1)" duplicates.
	 *
	 * @return array|WP_Error Dropbox file metadata (incl. content_hash).
	 */
	public static function upload( $token, $local_path, $dest, $contents = null ) {
		if ( null === $contents ) {
			$contents = is_readable( $local_path ) ? file_get_contents( $local_path ) : false; // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( false === $contents ) {
				return new WP_Error( 'local_missing', 'Local file not readable.' );
			}
		}
		$resp = wp_remote_post( 'https://content.dropboxapi.com/2/files/upload', [
			'timeout' => 90,
			'headers' => [
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/octet-stream',
				'Dropbox-API-Arg' => wp_json_encode( [
					'path'       => $dest,
					'mode'       => 'overwrite',
					'autorename' => false,
					'mute'       => true,
				] ),
			],
			'body'    => $contents,
		] );
		if ( is_wp_error( $resp ) ) {
			return new WP_Error( 'dropbox_network', 'Upload request failed: ' . $resp->get_error_code() );
		}
		$code = (int) wp_remote_retrieve_response_code( $resp );
		$body = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( 200 !== $code ) {
			$summary = is_array( $body ) && isset( $body['error_summary'] ) ? sanitize_text_field( $body['error_summary'] ) : 'no details';
			return new WP_Error( 'dropbox_http', sprintf( 'Upload rejected (HTTP %d, %s).', $code, $summary ) );
		}
		return is_array( $body ) ? $body : [];
	}
}
