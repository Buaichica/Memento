<?php
/**
 * Activation, schema and one-off migrations.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Installer {

	const DB_OPTION = 'memento_pz_db_version';

	public static function activate() {
		self::create_tables();
		Memento_PZ_Storage::ensure_root();
		self::migrate_product_photo_counts();
		Memento_PZ_Cleanup::schedule();
		update_option( self::DB_OPTION, MEMENTO_PZ_DB_VERSION );
	}

	public static function deactivate() {
		Memento_PZ_Cleanup::unschedule();
	}

	public static function maybe_upgrade() {
		if ( get_option( self::DB_OPTION ) !== MEMENTO_PZ_DB_VERSION ) {
			self::activate();
		}
	}

	public static function sessions_table() {
		global $wpdb;
		return $wpdb->prefix . 'memento_pz_sessions';
	}

	public static function uploads_table() {
		global $wpdb;
		return $wpdb->prefix . 'memento_pz_uploads';
	}

	private static function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset  = $wpdb->get_charset_collate();
		$sessions = self::sessions_table();
		$uploads  = self::uploads_table();

		dbDelta( "CREATE TABLE {$sessions} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_id char(32) NOT NULL,
			owner_hash char(64) NOT NULL,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			product_id bigint(20) unsigned NOT NULL DEFAULT 0,
			variation_id bigint(20) unsigned NOT NULL DEFAULT 0,
			required_count smallint(5) unsigned NOT NULL DEFAULT 0,
			state varchar(20) NOT NULL DEFAULT 'open',
			order_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			expires_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY session_id (session_id),
			KEY state_expires (state,expires_at),
			KEY order_id (order_id)
		) {$charset};" );

		dbDelta( "CREATE TABLE {$uploads} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			upload_id char(32) NOT NULL,
			session_id char(32) NOT NULL DEFAULT '',
			slot_index smallint(5) unsigned NOT NULL DEFAULT 0,
			product_id bigint(20) unsigned NOT NULL DEFAULT 0,
			variation_id bigint(20) unsigned NOT NULL DEFAULT 0,
			order_id bigint(20) unsigned NOT NULL DEFAULT 0,
			order_item_id bigint(20) unsigned NOT NULL DEFAULT 0,
			storage_key varchar(255) NOT NULL,
			thumb_key varchar(255) NOT NULL DEFAULT '',
			mime_type varchar(50) NOT NULL DEFAULT 'image/jpeg',
			width int(10) unsigned NOT NULL DEFAULT 0,
			height int(10) unsigned NOT NULL DEFAULT 0,
			bytes bigint(20) unsigned NOT NULL DEFAULT 0,
			src_width int(10) unsigned NOT NULL DEFAULT 0,
			src_height int(10) unsigned NOT NULL DEFAULT 0,
			source varchar(20) NOT NULL DEFAULT 'original',
			quality varchar(10) NOT NULL DEFAULT 'good',
			state varchar(20) NOT NULL DEFAULT 'temporary',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY upload_id (upload_id),
			KEY session_slot (session_id,slot_index),
			KEY order_item (order_id,order_item_id),
			KEY state (state)
		) {$charset};" );
	}

	/**
	 * One-off: give every product that relied on title parsing an explicit
	 * `_memento_photo_count`, so the deprecated fallback is no longer needed.
	 * Products without a number in the title are left untouched (and are then
	 * treated as non-personalised until an admin sets "Required Photos").
	 */
	private static function migrate_product_photo_counts() {
		if ( get_option( 'memento_pz_counts_migrated' ) ) {
			return;
		}
		$ids = get_posts( [
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		] );
		foreach ( $ids as $id ) {
			if ( '' !== (string) get_post_meta( $id, Memento_PZ_Product_Config::META_KEY, true ) ) {
				continue;
			}
			$from_title = Memento_PZ_Product_Config::count_from_title( get_the_title( $id ) );
			if ( $from_title ) {
				update_post_meta( $id, Memento_PZ_Product_Config::META_KEY, $from_title );
				Memento_PZ_Logger::info( sprintf( 'Migrated product %d: Required Photos = %d (from title).', $id, $from_title ) );
			}
		}
		update_option( 'memento_pz_counts_migrated', 1, false );
	}
}
