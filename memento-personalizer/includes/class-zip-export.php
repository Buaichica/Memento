<?php
/**
 * "Download Production ZIP" (admin only). Read-only: source images are never modified.
 *
 *   MM-{order number}.zip
 *     manifest.json
 *     order.txt
 *     item-01/01.jpg, 02.jpg …
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Zip_Export {

	public static function init() {
		add_action( 'admin_post_memento_pz_zip', [ __CLASS__, 'handle' ] );
	}

	public static function url( $order_id ) {
		return wp_nonce_url( admin_url( 'admin-post.php?action=memento_pz_zip&order_id=' . (int) $order_id ), 'memento_pz_zip_' . (int) $order_id );
	}

	public static function handle() {
		$order_id = absint( $_GET['order_id'] ?? 0 );
		check_admin_referer( 'memento_pz_zip_' . $order_id );
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'memento-personalizer' ), 403 );
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_die( esc_html__( 'Order not found.', 'memento-personalizer' ), 404 );
		}
		if ( ! class_exists( 'ZipArchive' ) ) {
			wp_die( esc_html__( 'The PHP zip extension is not available on this server. Ask SiteGround support to enable it, or download photos individually.', 'memento-personalizer' ) );
		}

		Memento_PZ_Order_Files::finalize( $order );
		$items = Memento_PZ_Order_Files::production_items( $order );
		$name  = Memento_PZ_Order_Files::folder_name( $order );

		$work = Memento_PZ_Storage::root() . '/tmp';
		wp_mkdir_p( $work );
		$zip_path = $work . '/' . Memento_PZ_Storage::random_id() . '.zip';

		$zip = new ZipArchive();
		if ( true !== $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			Memento_PZ_Logger::error( 'Could not create ZIP for order ' . $order_id );
			wp_die( esc_html__( 'Could not create the ZIP file. Please try again.', 'memento-personalizer' ) );
		}

		$manifest = Memento_PZ_Order_Files::manifest( $order, $items );
		$zip->addFromString( 'manifest.json', wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
		$zip->addFromString( 'order.txt', self::order_txt( $order, $items ) );

		foreach ( $items as $entry ) {
			foreach ( $entry['files'] as $file ) {
				if ( '' !== $file['path'] ) {
					$zip->addFile( $file['path'], $file['rel'] );
					if ( method_exists( $zip, 'setCompressionName' ) ) {
						$zip->setCompressionName( $file['rel'], ZipArchive::CM_STORE ); // JPEGs are already compressed.
					}
				}
			}
		}
		$zip->close();

		while ( ob_get_level() ) {
			ob_end_clean();
		}
		nocache_headers();
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $name . '.zip"' );
		header( 'Content-Length: ' . filesize( $zip_path ) );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $zip_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		@unlink( $zip_path ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		exit;
	}

	private static function order_txt( WC_Order $order, array $items ) {
		list( $expected, $actual ) = Memento_PZ_Order_Files::counts( $items );
		$lines   = [];
		$lines[] = 'Memento Magnets — Production Sheet';
		$lines[] = 'Order: #' . $order->get_order_number() . ' (ID ' . $order->get_id() . ')';
		$lines[] = 'Placed: ' . ( $order->get_date_created() ? $order->get_date_created()->date_i18n( 'Y-m-d H:i' ) : '' );
		$lines[] = 'Photos: ' . $actual . ' of ' . $expected . ( $actual === $expected ? ' ✓' : ' — MISSING FILES, check before printing' );
		$lines[] = '';
		foreach ( $items as $entry ) {
			$item    = $entry['item'];
			$lines[] = sprintf( '%s  %s  × %d  (%d photos per set)', $entry['folder'], $item->get_name(), $item->get_quantity(), $entry['required'] );
			foreach ( $entry['files'] as $file ) {
				$flag    = '' === $file['path'] ? '  [MISSING]' : ( $file['upload'] && 'low' === $file['upload']->quality ? '  [low resolution]' : '' );
				$lines[] = '    ' . $file['rel'] . $flag;
			}
			$lines[] = '';
		}
		return implode( "\n", $lines );
	}

}
