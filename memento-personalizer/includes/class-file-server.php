<?php
/**
 * Controlled file retrieval: the only way to read a stored photo.
 *
 *   admin-ajax.php?action=memento_pz_file&id={upload_id}&size=thumb|full[&download=1]
 *
 * Temporary uploads: readable by the session owner (cookie) or shop managers.
 * Final order files: readable by shop managers, or the logged-in customer who
 * placed the order. Changing the ID never grants access to someone else's file.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_File_Server {

	public static function init() {
		add_action( 'wp_ajax_memento_pz_file', [ __CLASS__, 'serve' ] );
		add_action( 'wp_ajax_nopriv_memento_pz_file', [ __CLASS__, 'serve' ] );
	}

	public static function url( $upload, $size = 'thumb', $download = false ) {
		$args = [
			'action' => 'memento_pz_file',
			'id'     => $upload->upload_id,
			'size'   => 'full' === $size ? 'full' : 'thumb',
			'v'      => substr( md5( $upload->updated_at . $upload->storage_key ), 0, 8 ),
		];
		if ( $download ) {
			$args['download'] = 1;
		}
		return add_query_arg( $args, admin_url( 'admin-ajax.php' ) );
	}

	public static function can_access( $upload ) {
		if ( current_user_can( 'edit_shop_orders' ) ) {
			return true;
		}
		if ( 'temporary' === $upload->state ) {
			return Memento_PZ_Upload_Session::owns( Memento_PZ_Repository::get_session( $upload->session_id ) );
		}
		if ( 'final' === $upload->state && is_user_logged_in() ) {
			$order = wc_get_order( (int) $upload->order_id );
			return $order && (int) $order->get_customer_id() === get_current_user_id();
		}
		return false;
	}

	public static function serve() {
		$upload = Memento_PZ_Repository::get_upload( sanitize_key( wp_unslash( $_GET['id'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $upload || 'deleted' === $upload->state || ! self::can_access( $upload ) ) {
			status_header( 404 );
			nocache_headers();
			exit;
		}

		$size = ( isset( $_GET['size'] ) && 'full' === $_GET['size'] ) ? 'full' : 'thumb'; // phpcs:ignore WordPress.Security.NonceVerification
		$key  = ( 'thumb' === $size && $upload->thumb_key ) ? $upload->thumb_key : $upload->storage_key;
		$path = Memento_PZ_Storage::path( $key );
		if ( ! $path || ! is_file( $path ) ) {
			Memento_PZ_Logger::warning( 'Stored file missing for upload ' . $upload->upload_id );
			status_header( 404 );
			nocache_headers();
			exit;
		}

		$ext      = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
		$filename = sprintf( 'photo-%02d.%s', (int) $upload->slot_index + 1, $ext );
		if ( $upload->order_id ) {
			$filename = sprintf( 'MM-%d-item%d-%02d.%s', $upload->order_id, $upload->order_item_id, (int) $upload->slot_index + 1, $ext );
		}
		$mime = ( $key === $upload->storage_key && in_array( $upload->mime_type, [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif' ], true ) ) ? $upload->mime_type : 'image/jpeg';
		$disposition = ! empty( $_GET['download'] ) ? 'attachment' : 'inline'; // phpcs:ignore WordPress.Security.NonceVerification

		while ( ob_get_level() ) {
			ob_end_clean();
		}
		status_header( 200 );
		header( 'Content-Type: ' . $mime );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'Content-Disposition: ' . $disposition . '; filename="' . $filename . '"' );
		header( 'Cache-Control: private, no-store, max-age=0' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Robots-Tag: noindex, nofollow' );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
}
