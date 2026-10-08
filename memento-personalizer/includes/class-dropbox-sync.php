<?php
/**
 * Asynchronous, retryable, idempotent Dropbox sync of production files.
 *
 * Checkout never waits on Dropbox: paid orders are queued via Action Scheduler
 * (bundled with WooCommerce). Order meta tracks state:
 *
 *   _memento_pz_dropbox_status        pending|syncing|complete|failed|not_configured
 *   _memento_pz_dropbox_attempts      int
 *   _memento_pz_dropbox_last_attempt  unix timestamp
 *   _memento_pz_dropbox_error         safe error summary (no tokens)
 *   _memento_pz_dropbox_folder        Dropbox folder path
 *   _memento_pz_dropbox_done          [ dropbox path => bytes ] files confirmed uploaded
 *
 * Folder layout: {dropbox_root}/MM-{order number}/manifest.json, item-01/01.jpg …
 * Orders synced by the old theme code keep `_memento_dropbox_folder` and are
 * shown as "Complete (legacy)".
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Dropbox_Sync {

	const ACTION = 'memento_pz_dropbox_sync';
	const GROUP  = 'memento-personalizer';

	public static function init() {
		add_action( self::ACTION, [ __CLASS__, 'run' ] );
		add_action( 'woocommerce_payment_complete', [ __CLASS__, 'queue' ], 20 );
		add_action( 'woocommerce_order_status_processing', [ __CLASS__, 'queue' ], 20 );
		add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'queue' ], 20 );
		add_action( 'admin_post_memento_pz_dropbox_retry', [ __CLASS__, 'handle_retry' ] );
	}

	public static function status( WC_Order $order ) {
		$status = (string) $order->get_meta( '_memento_pz_dropbox_status' );
		if ( '' === $status && $order->get_meta( '_memento_dropbox_folder' ) ) {
			return 'legacy';
		}
		return $status;
	}

	/** Queue a sync for a paid order (no-op if complete or nothing to sync). */
	public static function queue( $order_id, $force = false ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		Memento_PZ_Order_Files::finalize( $order );
		$order = wc_get_order( $order_id ); // Reload after finalisation.

		if ( ! $order->get_meta( '_memento_pz_has_photos' ) || 'purged' === $order->get_meta( '_memento_pz_has_photos' ) ) {
			return;
		}
		$status = self::status( $order );
		if ( ! $force && in_array( $status, [ 'complete', 'legacy', 'pending', 'syncing' ], true ) ) {
			return;
		}

		if ( ! Memento_PZ_Dropbox_Client::is_configured() ) {
			$order->update_meta_data( '_memento_pz_dropbox_status', 'not_configured' );
			$order->update_meta_data( '_memento_pz_dropbox_error', 'Dropbox credentials are not set in wp-config.php.' );
			$order->save();
			return;
		}

		$order->update_meta_data( '_memento_pz_dropbox_status', 'pending' );
		if ( $force ) {
			$order->update_meta_data( '_memento_pz_dropbox_attempts', 0 );
		}
		$order->save();
		self::schedule( $order->get_id(), 0 );
	}

	private static function schedule( $order_id, $delay ) {
		$args = [ 'order_id' => (int) $order_id ];
		if ( function_exists( 'as_schedule_single_action' ) ) {
			// Only skip if one is already *pending*. (as_has_scheduled_action() also counts the
			// in-progress action, which would block a retry scheduled from inside a failing run.)
			$pending = as_get_scheduled_actions( [
				'hook'     => self::ACTION,
				'args'     => $args,
				'group'    => self::GROUP,
				'status'   => ActionScheduler_Store::STATUS_PENDING,
				'per_page' => 1,
			], 'ids' );
			if ( $pending ) {
				return;
			}
			if ( $delay > 0 ) {
				as_schedule_single_action( time() + $delay, self::ACTION, $args, self::GROUP );
			} else {
				as_enqueue_async_action( self::ACTION, $args, self::GROUP );
			}
			return;
		}
		wp_schedule_single_event( time() + max( 1, $delay ), self::ACTION, $args );
	}

	public static function run( $order_id ) {
		$order = wc_get_order( (int) $order_id );
		if ( ! $order ) {
			return;
		}
		$status       = self::status( $order );
		$last_attempt = (int) $order->get_meta( '_memento_pz_dropbox_last_attempt' );
		if ( 'complete' === $status || ( 'syncing' === $status && $last_attempt > time() - 15 * MINUTE_IN_SECONDS ) ) {
			return; // Done already, or another worker is mid-sync.
		}
		if ( ! Memento_PZ_Dropbox_Client::is_configured() ) {
			self::fail( $order, [ 'Dropbox credentials are not set in wp-config.php.' ], false, 'not_configured' );
			return;
		}

		$attempts = (int) $order->get_meta( '_memento_pz_dropbox_attempts' ) + 1;
		$order->update_meta_data( '_memento_pz_dropbox_status', 'syncing' );
		$order->update_meta_data( '_memento_pz_dropbox_attempts', $attempts );
		$order->update_meta_data( '_memento_pz_dropbox_last_attempt', time() );
		$order->save();

		Memento_PZ_Order_Files::finalize( $order );
		$items                       = Memento_PZ_Order_Files::production_items( $order );
		list( $expected, $actual )   = Memento_PZ_Order_Files::counts( $items );
		$folder                      = rtrim( (string) Memento_PZ_Settings::get( 'dropbox_root' ), '/' ) . '/' . Memento_PZ_Order_Files::folder_name( $order );
		$done                        = $order->get_meta( '_memento_pz_dropbox_done' );
		$done                        = is_array( $done ) ? $done : [];
		$errors                      = [];

		$token = Memento_PZ_Dropbox_Client::access_token();
		if ( is_wp_error( $token ) ) {
			self::fail( $order, [ $token->get_error_message() ], true );
			return;
		}

		$uploaded = 0;
		foreach ( $items as $entry ) {
			foreach ( $entry['files'] as $file ) {
				$dest = $folder . '/' . $file['rel'];
				if ( '' === $file['path'] ) {
					$errors[] = sprintf( '%s is missing on the server.', $file['rel'] );
					continue;
				}
				$bytes = (int) filesize( $file['path'] );
				if ( isset( $done[ $dest ] ) && (int) $done[ $dest ] === $bytes ) {
					$uploaded++;
					continue; // Already confirmed on a previous attempt.
				}
				$result = Memento_PZ_Dropbox_Client::upload( $token, $file['path'], $dest );
				if ( is_wp_error( $result ) ) {
					$errors[] = $file['rel'] . ': ' . $result->get_error_message();
					continue;
				}
				$done[ $dest ] = $bytes;
				$uploaded++;
				$order->update_meta_data( '_memento_pz_dropbox_done', $done );
				$order->save_meta_data();
			}
		}

		$manifest = wp_json_encode( Memento_PZ_Order_Files::manifest( $order, $items ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		$result   = Memento_PZ_Dropbox_Client::upload( $token, '', $folder . '/manifest.json', $manifest );
		if ( is_wp_error( $result ) ) {
			$errors[] = 'manifest.json: ' . $result->get_error_message();
		}

		if ( $expected !== $actual ) {
			$errors[] = sprintf( 'Expected %d photos but found %d on the server.', $expected, $actual );
		}

		$order->update_meta_data( '_memento_pz_dropbox_folder', $folder );
		if ( ! $errors && $uploaded === $expected ) {
			$order->update_meta_data( '_memento_pz_dropbox_status', 'complete' );
			$order->update_meta_data( '_memento_pz_dropbox_error', '' );
			$order->add_order_note( sprintf(
				/* translators: 1: number of files, 2: Dropbox folder */
				__( 'Dropbox sync complete: %1$d photo(s) in %2$s.', 'memento-personalizer' ),
				$uploaded,
				$folder
			) );
			$order->save();
			Memento_PZ_Logger::info( sprintf( 'Order %d synced to Dropbox (%d files).', $order->get_id(), $uploaded ) );
			return;
		}

		// Missing local files won't fix themselves; only retry transient failures.
		$retryable = (bool) array_filter( $errors, function ( $e ) {
			return false === strpos( $e, 'missing on the server' ) && 0 !== strpos( $e, 'Expected ' );
		} );
		self::fail( $order, $errors, $retryable );
	}

	private static function fail( WC_Order $order, array $errors, $retryable, $status = 'failed' ) {
		$summary  = Memento_PZ_Logger::redact( implode( ' | ', array_slice( $errors, 0, 3 ) ) . ( count( $errors ) > 3 ? sprintf( ' (+%d more)', count( $errors ) - 3 ) : '' ) );
		$attempts = (int) $order->get_meta( '_memento_pz_dropbox_attempts' );
		$order->update_meta_data( '_memento_pz_dropbox_status', $status );
		$order->update_meta_data( '_memento_pz_dropbox_error', $summary );
		$order->save();
		Memento_PZ_Logger::error( sprintf( 'Dropbox sync failed for order %d (attempt %d): %s', $order->get_id(), $attempts, $summary ) );

		if ( $retryable && $attempts < Memento_PZ_Settings::int( 'dropbox_max_retries' ) ) {
			// Back-off: 5 min, 20 min, 80 min, …
			self::schedule( $order->get_id(), 300 * ( 4 ** max( 0, $attempts - 1 ) ) );
		} else {
			$order->add_order_note( __( 'Dropbox sync failed. Use "Retry Dropbox Sync" in the Production panel once the issue is fixed.', 'memento-personalizer' ) . ' ' . $summary );
		}
	}

	public static function handle_retry() {
		$order_id = absint( $_GET['order_id'] ?? 0 );
		check_admin_referer( 'memento_pz_dropbox_retry_' . $order_id );
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'memento-personalizer' ), 403 );
		}
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->update_meta_data( '_memento_pz_dropbox_status', 'failed' ); // Allow re-queue even if stuck.
			$order->save();
			self::queue( $order_id, true );
			$order->add_order_note( __( 'Dropbox sync retry requested by admin.', 'memento-personalizer' ), false, true );
		}
		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
		exit;
	}
}
