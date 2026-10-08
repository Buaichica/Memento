<?php
/**
 * WP-CLI commands.
 *
 *   wp memento-pz migrate-legacy [--dry-run] [--delete-public]
 *   wp memento-pz cleanup
 *   wp memento-pz dropbox-sync <order_id>
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_CLI {

	/**
	 * Move pre-plugin order photos (public uploads/memento-orders URLs) into protected storage.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Report what would be migrated without changing anything.
	 *
	 * [--delete-public]
	 * : Delete the original public files after a successful copy.
	 *
	 * @subcommand migrate-legacy
	 */
	public function migrate_legacy( $args, $assoc ) {
		$dry    = ! empty( $assoc['dry-run'] );
		$delete = ! empty( $assoc['delete-public'] );
		$page   = 1;
		$totals = [ 'items' => 0, 'files' => 0, 'missing' => 0 ];

		do {
			$orders = wc_get_orders( [ 'limit' => 100, 'paged' => $page, 'orderby' => 'ID', 'order' => 'ASC', 'status' => array_keys( wc_get_order_statuses() ) ] );
			foreach ( $orders as $order ) {
				foreach ( $order->get_items() as $item_id => $item ) {
					$legacy = (string) $item->get_meta( Memento_PZ_Order_Files::LEGACY_META, true );
					if ( '' === $legacy || $item->get_meta( '_memento_pz_uploads', true ) ) {
						continue;
					}
					$urls = array_values( array_filter( array_map( 'trim', explode( "\n", $legacy ) ) ) );
					$totals['items']++;
					$ids     = [];
					$sources = [];
					foreach ( $urls as $slot => $url ) {
						$path = Memento_PZ_Order_Files::legacy_path( $url );
						if ( ! $path ) {
							$totals['missing']++;
							WP_CLI::warning( sprintf( 'Order %d item %d slot %d: file not found (%s)', $order->get_id(), $item_id, $slot + 1, $url ) );
							continue;
						}
						if ( $dry ) {
							$totals['files']++;
							continue;
						}
						$ext       = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
						$ext       = 'jpeg' === $ext ? 'jpg' : $ext;
						$upload_id = Memento_PZ_Storage::random_id();
						$key       = sprintf( 'orders/%d/%d/%02d-%s.%s', $order->get_id(), $item_id, $slot + 1, $upload_id, $ext );
						$dest      = Memento_PZ_Storage::path( $key );
						wp_mkdir_p( dirname( $dest ) );
						if ( ! $dest || ! copy( $path, $dest ) ) {
							WP_CLI::warning( sprintf( 'Order %d item %d slot %d: copy failed', $order->get_id(), $item_id, $slot + 1 ) );
							continue;
						}
						$info = @getimagesize( $dest ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
						$side = $info ? min( $info[0], $info[1] ) : 0;
						Memento_PZ_Repository::insert_upload( [
							'upload_id'     => $upload_id,
							'slot_index'    => $slot,
							'product_id'    => $item->get_product_id(),
							'variation_id'  => $item->get_variation_id(),
							'order_id'      => $order->get_id(),
							'order_item_id' => $item_id,
							'storage_key'   => $key,
							'mime_type'     => $info['mime'] ?? 'image/jpeg',
							'width'         => $info[0] ?? 0,
							'height'        => $info[1] ?? 0,
							'bytes'         => filesize( $dest ),
							'src_width'     => $info[0] ?? 0,
							'src_height'    => $info[1] ?? 0,
							'source'        => 'legacy',
							'quality'       => $side && $side < Memento_PZ_Settings::int( 'min_px_recommended' ) ? 'low' : 'good',
							'state'         => 'final',
						] );
						$ids[]     = $upload_id;
						$sources[] = $path;
						$totals['files']++;
					}
					if ( $dry || count( $ids ) !== count( $urls ) ) {
						continue; // Leave partially-migrated items readable via legacy meta.
					}
					$item->add_meta_data( '_memento_pz_uploads', $ids, true );
					$item->add_meta_data( '_memento_pz_required', count( $ids ), true );
					$item->add_meta_data( '_memento_pz_finalized', count( $ids ), true );
					$item->add_meta_data( '_memento_pz_legacy_urls', $legacy, true );
					$item->delete_meta_data( Memento_PZ_Order_Files::LEGACY_META );
					/* translators: %d: photo count */
					$item->add_meta_data( __( 'Custom Photos', 'memento-personalizer' ), sprintf( _n( '%d photo', '%d photos', count( $ids ), 'memento-personalizer' ), count( $ids ) ), true );
					$item->save();
					if ( ! $order->get_meta( '_memento_pz_has_photos' ) ) {
						$order->update_meta_data( '_memento_pz_has_photos', 1 );
						$order->save();
					}
					if ( $delete ) {
						foreach ( $sources as $src ) {
							@unlink( $src ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
						}
					}
				}
			}
			$page++;
		} while ( count( $orders ) === 100 );

		WP_CLI::success( sprintf( '%s %d item(s), %d file(s); %d missing.', $dry ? 'Would migrate' : 'Migrated', $totals['items'], $totals['files'], $totals['missing'] ) );
	}

	/** Run the scheduled cleanup now. */
	public function cleanup() {
		WP_CLI::success( 'Cleanup: ' . wp_json_encode( Memento_PZ_Cleanup::run() ) );
	}

	/**
	 * Sync one order to Dropbox immediately (bypasses the queue).
	 *
	 * <order_id>
	 * : WooCommerce order ID.
	 *
	 * @subcommand dropbox-sync
	 */
	public function dropbox_sync( $args ) {
		$order_id = absint( $args[0] );
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			WP_CLI::error( 'Order not found.' );
		}
		$order->update_meta_data( '_memento_pz_dropbox_status', 'failed' );
		$order->save();
		Memento_PZ_Dropbox_Sync::run( $order_id );
		$order = wc_get_order( $order_id );
		WP_CLI::log( 'Status: ' . Memento_PZ_Dropbox_Sync::status( $order ) . ' ' . $order->get_meta( '_memento_pz_dropbox_error' ) );
	}
}

WP_CLI::add_command( 'memento-pz', 'Memento_PZ_CLI' );
