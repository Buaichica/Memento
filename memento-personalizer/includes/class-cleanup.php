<?php
/**
 * Scheduled cleanup (hourly WP-Cron event `memento_pz_cleanup`).
 *
 *  1. Expired, unclaimed upload sessions → delete temporary files + records.
 *  2. Orphaned temp directories with no session record → delete.
 *  3. Stale processing scratch files → delete.
 *  4. Optional retention: purge order files after the configured period.
 *
 * Paid/claimed order files are never touched by steps 1–3: they live under
 * orders/ and their records are in state "final". Every step is idempotent.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Cleanup {

	const HOOK = 'memento_pz_cleanup';

	public static function init() {
		add_action( self::HOOK, [ __CLASS__, 'run' ] );
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			self::schedule();
		}
	}

	public static function schedule() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + 300, 'hourly', self::HOOK );
		}
	}

	public static function unschedule() {
		wp_clear_scheduled_hook( self::HOOK );
	}

	public static function run() {
		$lock = 'memento_pz_cleanup_lock';
		if ( get_transient( $lock ) ) {
			return;
		}
		set_transient( $lock, 1, 10 * MINUTE_IN_SECONDS );

		$stats = [
			'sessions' => self::expire_sessions(),
			'orphans'  => self::remove_orphan_dirs(),
			'scratch'  => self::remove_scratch_files(),
			'retained' => self::apply_retention(),
		];

		delete_transient( $lock );
		if ( array_sum( $stats ) ) {
			Memento_PZ_Logger::info( 'Cleanup: ' . wp_json_encode( $stats ) );
		}
		return $stats;
	}

	private static function expire_sessions() {
		$removed = 0;
		foreach ( Memento_PZ_Repository::expired_sessions( 500 ) as $session ) {
			$has_final = false;
			foreach ( Memento_PZ_Repository::session_uploads( $session->session_id ) as $upload ) {
				if ( 'final' === $upload->state ) {
					$has_final = true; // Owned by an order — never delete.
					continue;
				}
				Memento_PZ_Upload_Handler::destroy_upload( $upload );
			}
			Memento_PZ_Storage::delete_dir( 'temp/' . $session->session_id );
			if ( $has_final ) {
				Memento_PZ_Repository::update_session( $session->session_id, [ 'state' => 'claimed' ] );
			} else {
				Memento_PZ_Repository::delete_session( $session->session_id );
			}
			$removed++;
		}
		return $removed;
	}

	private static function remove_orphan_dirs() {
		$removed = 0;
		$cutoff  = time() - HOUR_IN_SECONDS * Memento_PZ_Settings::int( 'temp_ttl_hours' );
		foreach ( Memento_PZ_Storage::list_dirs( 'temp' ) as $name => $mtime ) {
			if ( $mtime > $cutoff || ! preg_match( '/^[a-f0-9]{32}$/', $name ) ) {
				continue;
			}
			$session = Memento_PZ_Repository::get_session( $name );
			if ( $session && in_array( $session->state, [ 'open', 'in_cart' ], true ) ) {
				continue; // Still live; handled by expire_sessions() when it expires.
			}
			Memento_PZ_Storage::delete_dir( 'temp/' . $name );
			$removed++;
		}
		return $removed;
	}

	private static function remove_scratch_files() {
		$dir     = Memento_PZ_Storage::root() . '/tmp';
		$removed = 0;
		if ( ! is_dir( $dir ) ) {
			return 0;
		}
		foreach ( new DirectoryIterator( $dir ) as $file ) {
			if ( $file->isFile() && $file->getMTime() < time() - DAY_IN_SECONDS ) {
				@unlink( $file->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
				$removed++;
			}
		}
		return $removed;
	}

	/**
	 * Purge production files after the configured retention periods.
	 * Order records, line items and the manifest data in meta are kept.
	 * Dropbox copies are retained independently (see README).
	 */
	private static function apply_retention() {
		$purged = 0;
		$rules  = [
			[ Memento_PZ_Settings::int( 'retention_days' ), [ 'completed', 'refunded' ] ],
			[ Memento_PZ_Settings::int( 'unpaid_retention_days' ), [ 'cancelled', 'failed' ] ],
		];
		foreach ( $rules as list( $days, $statuses ) ) {
			if ( $days <= 0 ) {
				continue;
			}
			$orders = wc_get_orders( [
				'status'        => $statuses,
				'date_modified' => '<' . ( time() - DAY_IN_SECONDS * $days ),
				'meta_key'      => '_memento_pz_has_photos', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'    => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
				'limit'         => 50,
				'return'        => 'objects',
			] );
			foreach ( $orders as $order ) {
				$purged += self::purge_order_files( $order );
			}
		}
		return $purged;
	}

	public static function purge_order_files( WC_Order $order ) {
		$count = 0;
		foreach ( Memento_PZ_Repository::uploads_for_orders( [ $order->get_id() ] ) as $upload ) {
			Memento_PZ_Storage::delete( $upload->storage_key );
			if ( $upload->thumb_key ) {
				Memento_PZ_Storage::delete( $upload->thumb_key );
			}
			Memento_PZ_Repository::update_upload( $upload->upload_id, [ 'state' => 'deleted' ] );
			$count++;
		}
		Memento_PZ_Storage::delete_dir( 'orders/' . $order->get_id() );
		$order->update_meta_data( '_memento_pz_has_photos', 'purged' );
		$order->add_order_note( sprintf(
			/* translators: %d: number of files */
			__( 'Retention policy: %d customer photo file(s) deleted from the website. Any Dropbox copy is kept separately.', 'memento-personalizer' ),
			$count
		) );
		$order->save();
		return $count;
	}
}
