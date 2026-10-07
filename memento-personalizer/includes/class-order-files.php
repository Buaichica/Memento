<?php
/**
 * Order ↔ file mapping.
 *
 * Order line item meta (hidden, prefixed with underscore):
 *   _memento_pz_session    upload session ID
 *   _memento_pz_uploads    upload IDs in slot order (array)
 *   _memento_pz_required   required photo count at time of purchase
 *   _memento_pz_finalized  number of files moved into order storage
 * Visible line item meta: "Custom Photos" → "6 photos".
 *
 * Order meta:
 *   _memento_pz_has_photos         1 if any line item is personalised
 *   _memento_pz_production_status  new|photo_review|ready_to_print|printed|packed|shipped
 *
 * Legacy orders keep their visible "Customer Photos" line meta (newline-separated URLs).
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Order_Files {

	const LEGACY_META = 'Customer Photos';

	public static function init() {
		add_action( 'woocommerce_checkout_create_order_line_item', [ __CLASS__, 'add_line_item_meta' ], 10, 4 );

		// Finalise as soon as the order exists (classic + block checkout), and
		// again on payment as a safety net. finalize() is idempotent.
		add_action( 'woocommerce_checkout_order_created', [ __CLASS__, 'finalize' ] );
		add_action( 'woocommerce_store_api_checkout_order_processed', [ __CLASS__, 'finalize' ] );
		add_action( 'woocommerce_payment_complete', [ __CLASS__, 'finalize' ], 5 );
		add_action( 'woocommerce_order_status_processing', [ __CLASS__, 'finalize' ], 5 );

		// My Account → View order: show the customer's own photos under each item.
		add_action( 'woocommerce_order_item_meta_end', [ __CLASS__, 'view_order_photos' ], 10, 4 );

		// Hide internal meta from customers/admin line item display.
		add_filter( 'woocommerce_hidden_order_itemmeta', function ( $hidden ) {
			return array_merge( $hidden, [ '_memento_pz_session', '_memento_pz_uploads', '_memento_pz_required', '_memento_pz_finalized' ] );
		} );
	}

	public static function add_line_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( ! empty( $values[ Memento_PZ_Cart::KEY ]['uploads'] ) ) {
			$data  = $values[ Memento_PZ_Cart::KEY ];
			$count = count( $data['uploads'] );
			$item->add_meta_data( '_memento_pz_session', $data['session_id'], true );
			$item->add_meta_data( '_memento_pz_uploads', array_values( $data['uploads'] ), true );
			$item->add_meta_data( '_memento_pz_required', (int) $data['required'], true );
			/* translators: %d: photo count */
			$item->add_meta_data( __( 'Custom Photos', 'memento-personalizer' ), sprintf( _n( '%d photo', '%d photos', $count, 'memento-personalizer' ), $count ), true );
		} elseif ( ! empty( $values['memento_photos'] ) ) {
			// Legacy cart item (pre-plugin). Preserve the original storage format.
			$item->add_meta_data( self::LEGACY_META, implode( "\n", array_map( 'esc_url_raw', (array) $values['memento_photos'] ) ), true );
		}
	}

	/**
	 * Photo tiles on the customer's View order page only (never in emails,
	 * the thank-you page or admin). File access is enforced by the file
	 * server: only the logged-in order owner (or shop staff) can load them.
	 */
	public static function view_order_photos( $item_id, $item, $order, $plain_text = false ) {
		if ( $plain_text || ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'view-order' ) || ! $order instanceof WC_Order ) {
			return;
		}
		$uploads = Memento_PZ_Repository::item_uploads( $order->get_id(), $item_id );
		if ( ! $uploads ) {
			return;
		}
		echo '<span class="mm-cart-photos mm-order-photos">';
		foreach ( $uploads as $upload ) {
			echo '<span class="mm-cart-photos__tile' . ( 'low' === $upload->quality ? ' is-low' : '' ) . '"><img src="' . esc_url( Memento_PZ_File_Server::url( $upload, 'thumb' ) ) . '" alt="' .
				/* translators: %d: photo number */
				esc_attr( sprintf( __( 'Photo %d', 'memento-personalizer' ), (int) $upload->slot_index + 1 ) ) . '" width="44" height="44" loading="lazy"></span>';
		}
		echo '</span>';
	}

	/**
	 * Move an order's temporary uploads into protected order storage and
	 * record the immutable order ↔ file mapping. Safe to call repeatedly.
	 *
	 * @param WC_Order|int $order
	 */
	public static function finalize( $order ) {
		$order = $order instanceof WC_Order ? $order : wc_get_order( $order );
		if ( ! $order ) {
			return;
		}
		$order_id  = $order->get_id();
		$has_files = false;
		$changed   = false;

		foreach ( $order->get_items() as $item_id => $item ) {
			$upload_ids = $item->get_meta( '_memento_pz_uploads', true );
			if ( empty( $upload_ids ) || ! is_array( $upload_ids ) ) {
				if ( $item->get_meta( self::LEGACY_META, true ) ) {
					$has_files = true;
				}
				continue;
			}
			$has_files = true;
			if ( (int) $item->get_meta( '_memento_pz_finalized', true ) === count( $upload_ids ) ) {
				continue;
			}

			$session_id = (string) $item->get_meta( '_memento_pz_session', true );
			$finalized  = 0;

			foreach ( array_values( $upload_ids ) as $slot => $upload_id ) {
				$upload = Memento_PZ_Repository::get_upload( $upload_id );
				if ( ! $upload || $upload->session_id !== $session_id ) {
					Memento_PZ_Logger::error( sprintf( 'Order %d item %d: upload %s missing at finalisation.', $order_id, $item_id, $upload_id ) );
					continue;
				}
				if ( 'final' === $upload->state ) {
					if ( (int) $upload->order_id === $order_id && (int) $upload->order_item_id === (int) $item_id ) {
						$finalized++;
						continue;
					}
					// Checkout retried after a failed payment: WooCommerce rebuilt the line
					// items or created a new order. Re-home files only from unpaid orders.
					if ( (int) $upload->order_id !== $order_id && ! self::is_unpaid_order( (int) $upload->order_id ) ) {
						Memento_PZ_Logger::error( sprintf( 'Order %d item %d: upload %s already belongs to paid order %d.', $order_id, $item_id, $upload_id, $upload->order_id ) );
						continue;
					}
				}

				$base      = sprintf( 'orders/%d/%d/%02d-%s', $order_id, $item_id, $slot + 1, $upload->upload_id );
				$new_key   = $base . '.jpg';
				$new_thumb = $upload->thumb_key ? $base . '-thumb.jpg' : '';

				if ( ! Memento_PZ_Storage::move( $upload->storage_key, $new_key ) ) {
					Memento_PZ_Logger::error( sprintf( 'Order %d item %d: could not move upload %s into order storage.', $order_id, $item_id, $upload_id ) );
					continue;
				}
				if ( $new_thumb ) {
					Memento_PZ_Storage::move( $upload->thumb_key, $new_thumb );
				}
				Memento_PZ_Repository::update_upload( $upload->upload_id, [
					'state'         => 'final',
					'order_id'      => $order_id,
					'order_item_id' => (int) $item_id,
					'slot_index'    => $slot,
					'storage_key'   => $new_key,
					'thumb_key'     => $new_thumb,
				] );
				$finalized++;
			}

			$item->update_meta_data( '_memento_pz_finalized', $finalized );
			$item->save_meta_data();
			$changed = true;

			if ( $session_id ) {
				Memento_PZ_Repository::update_session( $session_id, [ 'state' => 'claimed', 'order_id' => $order_id ] );
				// Drop any superseded temporary files left in this session.
				foreach ( Memento_PZ_Repository::session_uploads( $session_id ) as $leftover ) {
					if ( 'temporary' === $leftover->state ) {
						Memento_PZ_Upload_Handler::destroy_upload( $leftover );
					}
				}
				Memento_PZ_Storage::delete_dir( 'temp/' . $session_id );
			}

			if ( $finalized !== count( $upload_ids ) ) {
				$order->add_order_note( sprintf(
					/* translators: 1: product name, 2: files found, 3: files expected */
					__( 'Photo check: "%1$s" has %2$d of %3$d expected photos. See WooCommerce → Status → Logs (memento-personalizer).', 'memento-personalizer' ),
					$item->get_name(),
					$finalized,
					count( $upload_ids )
				) );
			}
		}

		if ( $has_files && ! $order->get_meta( '_memento_pz_has_photos' ) ) {
			$order->update_meta_data( '_memento_pz_has_photos', 1 );
			if ( ! $order->get_meta( '_memento_pz_production_status' ) ) {
				$order->update_meta_data( '_memento_pz_production_status', 'new' );
			}
			$changed = true;
		}
		if ( $changed ) {
			$order->save();
		}
	}

	/**
	 * Production view of an order: personalised line items with their files
	 * in slot order. Shared by the admin panel, ZIP export and Dropbox sync.
	 *
	 * @return array[] Each: index, item_id, item, required, legacy, files[ slot, rel, path, upload, url ]
	 */
	public static function production_items( WC_Order $order ) {
		$items = [];
		$index = 0;
		foreach ( $order->get_items() as $item_id => $item ) {
			$upload_ids = $item->get_meta( '_memento_pz_uploads', true );
			$legacy     = (string) $item->get_meta( self::LEGACY_META, true );
			if ( empty( $upload_ids ) && '' === $legacy ) {
				continue;
			}
			$index++;
			$folder = sprintf( 'item-%02d', $index );
			$entry  = [
				'index'    => $index,
				'item_id'  => (int) $item_id,
				'item'     => $item,
				'folder'   => $folder,
				'legacy'   => empty( $upload_ids ),
				'required' => 0,
				'files'    => [],
			];

			if ( ! empty( $upload_ids ) ) {
				$entry['required'] = (int) $item->get_meta( '_memento_pz_required', true );
				foreach ( Memento_PZ_Repository::item_uploads( $order->get_id(), $item_id ) as $upload ) {
					$path             = Memento_PZ_Storage::path( $upload->storage_key );
					$entry['files'][] = [
						'slot'   => (int) $upload->slot_index + 1,
						'rel'    => sprintf( '%s/%02d.%s', $folder, (int) $upload->slot_index + 1, strtolower( pathinfo( $upload->storage_key, PATHINFO_EXTENSION ) ) ),
						'path'   => ( $path && is_file( $path ) ) ? $path : '',
						'upload' => $upload,
						'url'    => '',
					];
				}
			} else {
				$urls              = array_values( array_filter( array_map( 'trim', explode( "\n", $legacy ) ) ) );
				$product           = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
				$entry['required'] = Memento_PZ_Product_Config::get_required_photos( $product );
				if ( ! $entry['required'] ) {
					$entry['required'] = count( $urls );
				}
				foreach ( $urls as $i => $url ) {
					$path             = self::legacy_path( $url );
					$ext              = $path ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : 'jpg';
					$entry['files'][] = [
						'slot'   => $i + 1,
						'rel'    => sprintf( '%s/%02d.%s', $folder, $i + 1, $ext ),
						'path'   => $path ? $path : '',
						'upload' => null,
						'url'    => $url,
					];
				}
			}
			$items[] = $entry;
		}
		return $items;
	}

	/** True when an order doesn't exist or hasn't been paid (so its files may move to a retry order). */
	public static function is_unpaid_order( $order_id ) {
		$order = $order_id ? wc_get_order( $order_id ) : null;
		return ! $order || in_array( $order->get_status(), [ 'pending', 'failed', 'cancelled', 'checkout-draft' ], true );
	}

	/** Resolve a legacy public URL to a local file inside uploads/memento-orders, or ''. */
	public static function legacy_path( $url ) {
		$uploads   = wp_upload_dir( null, false );
		$base_path = trailingslashit( (string) wp_parse_url( $uploads['baseurl'], PHP_URL_PATH ) ) . 'memento-orders/';
		$url_path  = (string) wp_parse_url( $url, PHP_URL_PATH );
		if ( 0 !== strpos( $url_path, $base_path ) ) {
			return '';
		}
		$relative = substr( $url_path, strlen( $base_path ) );
		if ( ! preg_match( '#^[0-9]{4}/[0-9]{2}/[A-Za-z0-9\-]+\.(jpg|jpeg|png|webp|gif)$#i', $relative ) ) {
			return '';
		}
		$root = realpath( $uploads['basedir'] . '/memento-orders' );
		$path = realpath( $uploads['basedir'] . '/memento-orders/' . $relative );
		if ( ! $root || ! $path || 0 !== strpos( $path, $root . DIRECTORY_SEPARATOR ) ) {
			return '';
		}
		return $path;
	}

	/** Expected vs actual production files for the whole order. */
	public static function counts( array $production_items ) {
		$expected = 0;
		$actual   = 0;
		foreach ( $production_items as $entry ) {
			$expected += $entry['required'];
			$actual   += count( array_filter( $entry['files'], function ( $f ) {
				return '' !== $f['path'];
			} ) );
		}
		return [ $expected, $actual ];
	}

	/** Manifest used inside production ZIPs and Dropbox folders. */
	public static function manifest( WC_Order $order, array $production_items ) {
		$manifest = [
			'order_id'      => $order->get_id(),
			'order_number'  => $order->get_order_number(),
			'order_created' => $order->get_date_created() ? $order->get_date_created()->date( 'c' ) : '',
			'generated_at'  => gmdate( 'c' ),
			'items'         => [],
		];
		if ( apply_filters( 'memento_pz_manifest_include_customer', false, $order ) ) {
			$manifest['customer'] = trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() );
		}
		foreach ( $production_items as $entry ) {
			$item  = $entry['item'];
			$files = [];
			foreach ( $entry['files'] as $f ) {
				$u       = $f['upload'];
				$files[] = array_filter( [
					'slot'        => $f['slot'],
					'file'        => $f['rel'],
					'present'     => '' !== $f['path'],
					'width'       => $u ? (int) $u->width : null,
					'height'      => $u ? (int) $u->height : null,
					'bytes'       => $u ? (int) $u->bytes : null,
					'source'      => $u ? $u->source : 'legacy',
					'quality'     => $u ? $u->quality : null,
					'src_width'   => $u ? (int) $u->src_width : null,
					'src_height'  => $u ? (int) $u->src_height : null,
					'uploaded_at' => $u ? $u->created_at . 'Z' : null,
				], function ( $v ) {
					return null !== $v;
				} );
			}
			$product               = $item->get_product();
			$manifest['items'][]   = [
				'item'            => $entry['index'],
				'folder'          => $entry['folder'],
				'order_item_id'   => $entry['item_id'],
				'product_id'      => $item->get_product_id(),
				'variation_id'    => $item->get_variation_id(),
				'product'         => $item->get_name(),
				'sku'             => $product ? $product->get_sku() : '',
				'quantity'        => $item->get_quantity(),
				'required_photos' => $entry['required'],
				'files'           => $files,
			];
		}
		return $manifest;
	}

	public static function production_statuses() {
		return [
			'new'            => __( 'New', 'memento-personalizer' ),
			'photo_review'   => __( 'Photo Review', 'memento-personalizer' ),
			'ready_to_print' => __( 'Ready to Print', 'memento-personalizer' ),
			'printed'        => __( 'Printed', 'memento-personalizer' ),
			'packed'         => __( 'Packed', 'memento-personalizer' ),
			'shipped'        => __( 'Shipped', 'memento-personalizer' ),
		];
	}

	/** Deterministic, privacy-conscious folder/ZIP name: MM-{order number}. */
	public static function folder_name( WC_Order $order ) {
		return 'MM-' . preg_replace( '/[^A-Za-z0-9\-]/', '', (string) $order->get_order_number() );
	}
}
