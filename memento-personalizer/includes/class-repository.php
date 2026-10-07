<?php
/**
 * Database access for upload sessions and upload records.
 *
 * Session states: open → in_cart → claimed (owned by an order) | expired.
 * Upload states:  temporary → final (owned by an order item).
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Repository {

	private static function now() {
		return current_time( 'mysql', true );
	}

	/* ── Sessions ─────────────────────────────────────────── */

	public static function create_session( array $data ) {
		global $wpdb;
		$session_id = Memento_PZ_Storage::random_id();
		$now        = self::now();
		$wpdb->insert( Memento_PZ_Installer::sessions_table(), [
			'session_id'     => $session_id,
			'owner_hash'     => $data['owner_hash'],
			'user_id'        => (int) $data['user_id'],
			'product_id'     => (int) $data['product_id'],
			'variation_id'   => (int) $data['variation_id'],
			'required_count' => (int) $data['required_count'],
			'state'          => 'open',
			'created_at'     => $now,
			'updated_at'     => $now,
			'expires_at'     => gmdate( 'Y-m-d H:i:s', time() + HOUR_IN_SECONDS * Memento_PZ_Settings::int( 'temp_ttl_hours' ) ),
		] );
		return $wpdb->insert_id ? $session_id : false;
	}

	public static function get_session( $session_id ) {
		global $wpdb;
		if ( ! is_string( $session_id ) || ! preg_match( '/^[a-f0-9]{32}$/', $session_id ) ) {
			return null;
		}
		$table = Memento_PZ_Installer::sessions_table();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE session_id = %s", $session_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	public static function update_session( $session_id, array $fields ) {
		global $wpdb;
		$fields['updated_at'] = self::now();
		return false !== $wpdb->update( Memento_PZ_Installer::sessions_table(), $fields, [ 'session_id' => $session_id ] );
	}

	/** Extend a session's expiry by the given number of hours from now. */
	public static function touch_session( $session_id, $hours ) {
		return self::update_session( $session_id, [ 'expires_at' => gmdate( 'Y-m-d H:i:s', time() + HOUR_IN_SECONDS * (int) $hours ) ] );
	}

	public static function delete_session( $session_id ) {
		global $wpdb;
		return $wpdb->delete( Memento_PZ_Installer::sessions_table(), [ 'session_id' => $session_id ] );
	}

	/** Sessions that never became part of an order and have expired. */
	public static function expired_sessions( $limit = 200 ) {
		global $wpdb;
		$table = Memento_PZ_Installer::sessions_table();
		return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL
			"SELECT * FROM {$table} WHERE state IN ('open','in_cart','expired') AND expires_at < %s LIMIT %d",
			self::now(),
			$limit
		) );
	}

	/* ── Uploads ──────────────────────────────────────────── */

	public static function insert_upload( array $data ) {
		global $wpdb;
		$now                = self::now();
		$data['created_at'] = $now;
		$data['updated_at'] = $now;
		$wpdb->insert( Memento_PZ_Installer::uploads_table(), $data );
		return (bool) $wpdb->insert_id;
	}

	public static function update_upload( $upload_id, array $fields ) {
		global $wpdb;
		$fields['updated_at'] = self::now();
		return false !== $wpdb->update( Memento_PZ_Installer::uploads_table(), $fields, [ 'upload_id' => $upload_id ] );
	}

	public static function delete_upload( $upload_id ) {
		global $wpdb;
		return $wpdb->delete( Memento_PZ_Installer::uploads_table(), [ 'upload_id' => $upload_id ] );
	}

	public static function get_upload( $upload_id ) {
		global $wpdb;
		if ( ! is_string( $upload_id ) || ! preg_match( '/^[a-f0-9]{32}$/', $upload_id ) ) {
			return null;
		}
		$table = Memento_PZ_Installer::uploads_table();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE upload_id = %s", $upload_id ) ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	public static function session_uploads( $session_id ) {
		global $wpdb;
		$table = Memento_PZ_Installer::uploads_table();
		return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL
			"SELECT * FROM {$table} WHERE session_id = %s AND state <> 'deleted' ORDER BY slot_index ASC, id ASC",
			$session_id
		) );
	}

	public static function slot_upload( $session_id, $slot ) {
		global $wpdb;
		$table = Memento_PZ_Installer::uploads_table();
		return $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL
			"SELECT * FROM {$table} WHERE session_id = %s AND slot_index = %d AND state <> 'deleted' ORDER BY id DESC LIMIT 1",
			$session_id,
			$slot
		) );
	}

	public static function item_uploads( $order_id, $item_id ) {
		global $wpdb;
		$table = Memento_PZ_Installer::uploads_table();
		return $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL
			"SELECT * FROM {$table} WHERE order_id = %d AND order_item_id = %d AND state = 'final' ORDER BY slot_index ASC",
			$order_id,
			$item_id
		) );
	}

	/**
	 * Final uploads belonging to orders whose IDs are in $order_ids.
	 *
	 * @param int[] $order_ids
	 */
	public static function uploads_for_orders( array $order_ids ) {
		global $wpdb;
		$order_ids = array_filter( array_map( 'absint', $order_ids ) );
		if ( ! $order_ids ) {
			return [];
		}
		$table = Memento_PZ_Installer::uploads_table();
		$in    = implode( ',', $order_ids );
		return $wpdb->get_results( "SELECT * FROM {$table} WHERE state = 'final' AND order_id IN ({$in})" ); // phpcs:ignore WordPress.DB.PreparedSQL
	}

	/** Distinct order IDs that still own final files, oldest first. */
	public static function orders_with_files( $limit = 100 ) {
		global $wpdb;
		$table = Memento_PZ_Installer::uploads_table();
		return $wpdb->get_col( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL
			"SELECT DISTINCT order_id FROM {$table} WHERE state = 'final' AND order_id > 0 ORDER BY order_id ASC LIMIT %d",
			$limit
		) );
	}
}
