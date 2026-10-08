<?php
/**
 * WooCommerce order screen "Production" panel + orders-list column.
 * Works with both legacy post storage and HPOS.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Admin_Order_Panel {

	public static function init() {
		add_action( 'add_meta_boxes', [ __CLASS__, 'register' ] );
		add_action( 'woocommerce_process_shop_order_meta', [ __CLASS__, 'save' ] );
		// After WooCommerce saves the status dropdown (priority 40), so our change isn't overwritten.
		add_action( 'woocommerce_process_shop_order_meta', [ __CLASS__, 'maybe_complete_shipped' ], 60 );

		// Orders list column (legacy + HPOS).
		add_filter( 'manage_edit-shop_order_columns', [ __CLASS__, 'add_column' ], 20 );
		add_action( 'manage_shop_order_posts_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ __CLASS__, 'add_column' ], 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );

		add_action( 'admin_notices', [ __CLASS__, 'storage_notice' ] );
	}

	public static function register() {
		add_meta_box(
			'memento-pz-production',
			__( 'Production — Customer Photos', 'memento-personalizer' ),
			[ __CLASS__, 'render' ],
			[ 'shop_order', 'woocommerce_page_wc-orders' ],
			'normal',
			'high'
		);
	}

	private static function order_from( $post_or_order ) {
		if ( $post_or_order instanceof WC_Order ) {
			return $post_or_order;
		}
		return $post_or_order ? wc_get_order( $post_or_order->ID ) : null;
	}

	public static function render( $post_or_order ) {
		$order = self::order_from( $post_or_order );
		if ( ! $order ) {
			return;
		}
		wp_nonce_field( 'memento_pz_save_production', 'memento_pz_production_nonce' );
		?>
		<p>
			<label for="memento-pz-tracking"><strong><?php esc_html_e( 'NZ Post tracking number', 'memento-personalizer' ); ?></strong></label><br>
			<input type="text" id="memento-pz-tracking" name="memento_pz_tracking_number" value="<?php echo esc_attr( Memento_PZ_Shipping_Tracking::number( $order ) ); ?>" maxlength="40" style="width:260px;text-transform:uppercase" autocomplete="off">
			<?php
			$tracking = Memento_PZ_Shipping_Tracking::number( $order );
			if ( $tracking ) {
				echo ' <a href="' . esc_url( Memento_PZ_Shipping_Tracking::url( $tracking ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Track', 'memento-personalizer' ) . '</a>';
			}
			?>
			<br><span class="description"><?php esc_html_e( 'Paste the tracking number, set Production status to Shipped and click Update: the order is marked Completed and the customer is emailed the tracking link.', 'memento-personalizer' ); ?></span>
		</p>
		<?php
		Memento_PZ_Order_Files::finalize( $order );
		$items = Memento_PZ_Order_Files::production_items( $order );

		if ( ! $items ) {
			echo '<p>' . esc_html__( 'This order has no personalised photo products.', 'memento-personalizer' ) . '</p>';
			return;
		}

		list( $expected, $actual ) = Memento_PZ_Order_Files::counts( $items );
		$current                   = $order->get_meta( '_memento_pz_production_status' ) ?: 'new';
		$dropbox                   = Memento_PZ_Dropbox_Sync::status( $order );
		$purged                    = 'purged' === $order->get_meta( '_memento_pz_has_photos' );
		?>
		<style>
			.mpz-bar{display:flex;flex-wrap:wrap;gap:12px 24px;align-items:center;margin:8px 0 16px}
			.mpz-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-weight:600;font-size:12px}
			.mpz-ok{background:#d7f3e3;color:#11643a}.mpz-bad{background:#fbe0e0;color:#8a1f1f}.mpz-warn{background:#fff1cc;color:#7a5600}.mpz-muted{background:#eee;color:#555}
			.mpz-item{border-top:1px solid #eee;padding:12px 0}
			.mpz-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-top:8px}
			.mpz-tile{position:relative;border:1px solid #ddd;border-radius:6px;overflow:hidden;background:#fafafa;font-size:11px}
			.mpz-tile img{display:block;width:100%;aspect-ratio:1/1;object-fit:cover}
			.mpz-tile .mpz-meta{display:flex;justify-content:space-between;padding:4px 6px}
			.mpz-tile .mpz-num{position:absolute;top:4px;left:4px;background:rgba(0,0,0,.65);color:#fff;border-radius:4px;padding:0 5px;font-weight:600}
			.mpz-missing{aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;color:#8a1f1f}
			.mpz-dropbox td{padding:2px 12px 2px 0;vertical-align:top}
		</style>

		<div class="mpz-bar">
			<label>
				<strong><?php esc_html_e( 'Production status', 'memento-personalizer' ); ?></strong><br>
				<select name="memento_pz_production_status">
					<?php foreach ( Memento_PZ_Order_Files::production_statuses() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current, $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<div>
				<strong><?php esc_html_e( 'Photos', 'memento-personalizer' ); ?></strong><br>
				<span class="mpz-badge <?php echo $actual === $expected ? 'mpz-ok' : 'mpz-bad'; ?>">
					<?php
					/* translators: 1: actual files, 2: expected files */
					printf( esc_html__( '%1$d of %2$d', 'memento-personalizer' ), (int) $actual, (int) $expected );
					?>
				</span>
			</div>
			<?php if ( ! $purged && $actual ) : ?>
				<a class="button button-primary" href="<?php echo esc_url( Memento_PZ_Zip_Export::url( $order->get_id() ) ); ?>"><?php esc_html_e( 'Download Production ZIP', 'memento-personalizer' ); ?></a>
			<?php endif; ?>
		</div>

		<table class="mpz-dropbox">
			<tr>
				<td><strong><?php esc_html_e( 'Dropbox', 'memento-personalizer' ); ?></strong></td>
				<td><?php echo self::dropbox_badge( $dropbox ); // phpcs:ignore WordPress.Security.EscapeOutput ?></td>
				<td>
					<?php if ( ! $purged && $order->is_paid() ) : ?>
						<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=memento_pz_dropbox_retry&order_id=' . $order->get_id() ), 'memento_pz_dropbox_retry_' . $order->get_id() ) ); ?>">
							<?php echo in_array( $dropbox, [ 'complete', 'legacy' ], true ) ? esc_html__( 'Re-sync to Dropbox', 'memento-personalizer' ) : esc_html__( 'Retry Dropbox Sync', 'memento-personalizer' ); ?>
						</a>
					<?php endif; ?>
				</td>
			</tr>
			<?php
			$folder   = $order->get_meta( '_memento_pz_dropbox_folder' ) ?: ( $order->get_meta( '_memento_dropbox_folder' ) ? '/Memento/Orders/' . $order->get_meta( '_memento_dropbox_folder' ) : '' );
			$last     = (int) $order->get_meta( '_memento_pz_dropbox_last_attempt' );
			$attempts = (int) $order->get_meta( '_memento_pz_dropbox_attempts' );
			$error    = (string) $order->get_meta( '_memento_pz_dropbox_error' );
			if ( $folder ) {
				echo '<tr><td>' . esc_html__( 'Folder', 'memento-personalizer' ) . '</td><td colspan="2"><code>' . esc_html( $folder ) . '</code></td></tr>';
			}
			if ( $last ) {
				echo '<tr><td>' . esc_html__( 'Last attempt', 'memento-personalizer' ) . '</td><td colspan="2">' . esc_html( wp_date( 'Y-m-d H:i', $last ) ) . ' (' . esc_html( sprintf( /* translators: %d: attempts */ _n( '%d attempt', '%d attempts', $attempts, 'memento-personalizer' ), $attempts ) ) . ')</td></tr>';
			}
			if ( $error && 'complete' !== $dropbox ) {
				echo '<tr><td>' . esc_html__( 'Error', 'memento-personalizer' ) . '</td><td colspan="2">' . esc_html( $error ) . '</td></tr>';
			}
			?>
		</table>

		<?php if ( $purged ) : ?>
			<p><em><?php esc_html_e( 'Photo files were removed from the website under the retention policy.', 'memento-personalizer' ); ?></em></p>
		<?php endif; ?>

		<?php foreach ( $items as $entry ) : ?>
			<?php
			$item     = $entry['item'];
			$present  = count( array_filter( $entry['files'], function ( $f ) {
				return '' !== $f['path'];
			} ) );
			$complete = $present === $entry['required'];
			?>
			<div class="mpz-item">
				<strong><?php echo esc_html( $entry['folder'] ); ?></strong> —
				<?php echo esc_html( $item->get_name() ); ?> × <?php echo (int) $item->get_quantity(); ?>
				<span class="mpz-badge <?php echo $complete ? 'mpz-ok' : 'mpz-bad'; ?>">
					<?php
					/* translators: 1: present, 2: required */
					printf( esc_html__( '%1$d / %2$d photos', 'memento-personalizer' ), (int) $present, (int) $entry['required'] );
					?>
				</span>
				<?php if ( $entry['legacy'] ) : ?>
					<span class="mpz-badge mpz-warn" title="<?php esc_attr_e( 'Uploaded before the personalizer plugin; file is in the public uploads folder. Run `wp memento-pz migrate-legacy` to protect it.', 'memento-personalizer' ); ?>"><?php esc_html_e( 'Legacy', 'memento-personalizer' ); ?></span>
				<?php endif; ?>

				<div class="mpz-grid">
					<?php foreach ( $entry['files'] as $file ) : ?>
						<div class="mpz-tile">
							<span class="mpz-num"><?php echo (int) $file['slot']; ?></span>
							<?php
							if ( '' === $file['path'] ) {
								echo '<div class="mpz-missing">' . esc_html__( 'Missing', 'memento-personalizer' ) . '</div>';
							} elseif ( $file['upload'] ) {
								$full  = Memento_PZ_File_Server::url( $file['upload'], 'full' );
								$thumb = Memento_PZ_File_Server::url( $file['upload'], 'thumb' );
								$dl    = Memento_PZ_File_Server::url( $file['upload'], 'full', true );
								echo '<a href="' . esc_url( $full ) . '" target="_blank" rel="noopener"><img src="' . esc_url( $thumb ) . '" alt="" loading="lazy"></a>';
								echo '<div class="mpz-meta"><span>' . (int) $file['upload']->width . 'px';
								if ( 'low' === $file['upload']->quality ) {
									echo ' <span class="mpz-badge mpz-warn" title="' . esc_attr__( 'Low resolution — may print blurry', 'memento-personalizer' ) . '">' . esc_html__( 'Low', 'memento-personalizer' ) . '</span>';
								}
								echo '</span><a href="' . esc_url( $dl ) . '">' . esc_html__( 'Download', 'memento-personalizer' ) . '</a></div>';
							} else {
								echo '<a href="' . esc_url( $file['url'] ) . '" target="_blank" rel="noopener"><img src="' . esc_url( $file['url'] ) . '" alt="" loading="lazy"></a>';
								echo '<div class="mpz-meta"><span></span><a href="' . esc_url( $file['url'] ) . '" download>' . esc_html__( 'Download', 'memento-personalizer' ) . '</a></div>';
							}
							?>
						</div>
					<?php endforeach; ?>
					<?php for ( $i = count( $entry['files'] ); $i < $entry['required']; $i++ ) : ?>
						<div class="mpz-tile"><span class="mpz-num"><?php echo (int) $i + 1; ?></span><div class="mpz-missing"><?php esc_html_e( 'Missing', 'memento-personalizer' ); ?></div></div>
					<?php endfor; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<?php
	}

	private static function dropbox_badge( $status ) {
		$map = [
			'complete'       => [ 'mpz-ok', __( 'Complete', 'memento-personalizer' ) ],
			'legacy'         => [ 'mpz-ok', __( 'Complete (legacy)', 'memento-personalizer' ) ],
			'pending'        => [ 'mpz-muted', __( 'Pending', 'memento-personalizer' ) ],
			'syncing'        => [ 'mpz-warn', __( 'Syncing', 'memento-personalizer' ) ],
			'failed'         => [ 'mpz-bad', __( 'Failed', 'memento-personalizer' ) ],
			'not_configured' => [ 'mpz-bad', __( 'Not configured', 'memento-personalizer' ) ],
			''               => [ 'mpz-muted', __( 'Waiting for payment', 'memento-personalizer' ) ],
		];
		$entry = $map[ $status ] ?? $map[''];
		return '<span class="mpz-badge ' . esc_attr( $entry[0] ) . '">' . esc_html( $entry[1] ) . '</span>';
	}

	public static function save( $order_id ) {
		if ( ! isset( $_POST['memento_pz_production_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['memento_pz_production_nonce'] ), 'memento_pz_save_production' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( isset( $_POST['memento_pz_tracking_number'] ) ) {
			$tracking = Memento_PZ_Shipping_Tracking::sanitize( wp_unslash( $_POST['memento_pz_tracking_number'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( $tracking !== Memento_PZ_Shipping_Tracking::number( $order ) ) {
				$order->update_meta_data( Memento_PZ_Shipping_Tracking::META, $tracking );
				$order->add_order_note(
					'' === $tracking
						? __( 'NZ Post tracking number removed.', 'memento-personalizer' )
						/* translators: %s: tracking number */
						: sprintf( __( 'NZ Post tracking number set: %s', 'memento-personalizer' ), $tracking ),
					false,
					true
				);
				$order->save();
			}
		}

		$status   = sanitize_key( wp_unslash( $_POST['memento_pz_production_status'] ?? '' ) );
		$statuses = Memento_PZ_Order_Files::production_statuses();
		if ( ! isset( $statuses[ $status ] ) ) {
			return;
		}
		$previous = $order->get_meta( '_memento_pz_production_status' );
		if ( $previous !== $status ) {
			$order->update_meta_data( '_memento_pz_production_status', $status );
			/* translators: %s: production status */
			$order->add_order_note( sprintf( __( 'Production status changed to %s.', 'memento-personalizer' ), $statuses[ $status ] ), false, true );
			$order->save();
		}
	}

	/**
	 * Shipped + tracking number on a Processing order → Completed, which sends
	 * WooCommerce's "Completed order" email (with the tracking block added by
	 * Memento_PZ_Shipping_Tracking).
	 */
	public static function maybe_complete_shipped( $order_id ) {
		if ( ! isset( $_POST['memento_pz_production_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['memento_pz_production_nonce'] ), 'memento_pz_save_production' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}
		$order = wc_get_order( $order_id ); // Fresh copy: WooCommerce has just saved the status.
		if ( ! $order || ! $order->has_status( 'processing' ) || ! Memento_PZ_Shipping_Tracking::number( $order ) ) {
			return;
		}
		// Orders without photo items have no status select; tracking alone is enough there.
		$shipped = 'shipped' === $order->get_meta( '_memento_pz_production_status' ) || ! isset( $_POST['memento_pz_production_status'] );
		if ( $shipped ) {
			$order->update_status( 'completed', __( 'Shipped with NZ Post.', 'memento-personalizer' ) );
		}
	}

	public static function add_column( $columns ) {
		$new = [];
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'order_status' === $key ) {
				$new['memento_pz'] = __( 'Production', 'memento-personalizer' );
			}
		}
		if ( ! isset( $new['memento_pz'] ) ) {
			$new['memento_pz'] = __( 'Production', 'memento-personalizer' );
		}
		return $new;
	}

	public static function render_column( $column, $post_or_order ) {
		if ( 'memento_pz' !== $column ) {
			return;
		}
		$order = $post_or_order instanceof WC_Order ? $post_or_order : wc_get_order( $post_or_order );
		$tracking = $order ? Memento_PZ_Shipping_Tracking::number( $order ) : '';
		if ( ! $order || ! $order->get_meta( '_memento_pz_has_photos' ) ) {
			echo $tracking ? '<span style="color:#555">' . esc_html( $tracking ) . '</span>' : '–';
			return;
		}
		$statuses = Memento_PZ_Order_Files::production_statuses();
		$status   = $order->get_meta( '_memento_pz_production_status' ) ?: 'new';
		echo esc_html( $statuses[ $status ] ?? $status );
		$dropbox = Memento_PZ_Dropbox_Sync::status( $order );
		if ( in_array( $dropbox, [ 'failed', 'not_configured' ], true ) ) {
			echo '<br><span style="color:#8a1f1f">' . esc_html__( 'Dropbox failed', 'memento-personalizer' ) . '</span>';
		}
		if ( $tracking ) {
			echo '<br><span style="color:#555">' . esc_html( $tracking ) . '</span>';
		}
	}

	/** Warn admins if photos are stored inside the public web root. */
	public static function storage_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! Memento_PZ_Storage::is_web_accessible_root() ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! in_array( $screen->id, [ 'dashboard', 'woocommerce_page_wc-status', 'plugins' ], true ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Memento Personalizer: customer photos are stored inside the web folder (protected by deny rules and random names). For maximum protection, define MEMENTO_PZ_STORAGE_DIR in wp-config.php pointing to a folder outside public_html.', 'memento-personalizer' ) . '</p></div>';
	}
}
