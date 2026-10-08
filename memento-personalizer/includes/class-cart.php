<?php
/**
 * Cart integration and server-side photo-count enforcement.
 *
 * Cart item data key `memento_pz`:
 *   [ 'session_id' => string, 'uploads' => string[] (slot order), 'required' => int ]
 *
 * Legacy cart items carrying `memento_photos` (public URLs, pre-plugin) are
 * still accepted at checkout so carts created before deployment don't break.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Cart {

	const KEY = 'memento_pz';

	public static function init() {
		add_filter( 'woocommerce_add_to_cart_validation', [ __CLASS__, 'validate_add' ], 10, 5 );
		add_filter( 'woocommerce_add_cart_item_data', [ __CLASS__, 'add_item_data' ], 10, 3 );
		add_action( 'woocommerce_add_to_cart', [ __CLASS__, 'lock_session' ], 10, 6 );
		add_filter( 'woocommerce_get_item_data', [ __CLASS__, 'display_item_data' ], 10, 2 );
		add_action( 'woocommerce_check_cart_items', [ __CLASS__, 'check_cart_items' ] );

		// Show the customer's own photos in the cart (thumbnail + tile strip).
		add_filter( 'woocommerce_cart_item_thumbnail', [ __CLASS__, 'classic_thumbnail' ], 10, 2 );
		add_filter( 'woocommerce_store_api_cart_item_images', [ __CLASS__, 'store_api_images' ], 10, 2 );
		if ( did_action( 'woocommerce_blocks_loaded' ) ) {
			self::register_store_api_data();
		} else {
			add_action( 'woocommerce_blocks_loaded', [ __CLASS__, 'register_store_api_data' ] );
		}
	}

	/**
	 * Tiles for a cart item's photos, in slot order.
	 *
	 * @return array[] Each: slot (1-based), thumb (URL), low (bool).
	 */
	public static function photo_tiles( $cart_item ) {
		$tiles = [];
		if ( empty( $cart_item[ self::KEY ]['uploads'] ) ) {
			return $tiles;
		}
		foreach ( array_values( $cart_item[ self::KEY ]['uploads'] ) as $i => $upload_id ) {
			$upload = Memento_PZ_Repository::get_upload( $upload_id );
			if ( $upload && 'deleted' !== $upload->state ) {
				$tiles[] = [
					'slot'  => $i + 1,
					'thumb' => Memento_PZ_File_Server::url( $upload, 'thumb' ),
					'low'   => 'low' === $upload->quality,
				];
			}
		}
		return $tiles;
	}

	/** Classic cart + mini cart: use photo 1 instead of the product placeholder. */
	public static function classic_thumbnail( $thumbnail, $cart_item ) {
		$tiles = self::photo_tiles( $cart_item );
		if ( ! $tiles ) {
			return $thumbnail;
		}
		return '<img src="' . esc_url( $tiles[0]['thumb'] ) . '" class="mm-cart-thumb" alt="' . esc_attr__( 'Your photo 1', 'memento-personalizer' ) . '" width="300" height="300" loading="lazy">';
	}

	/** Block cart/checkout: use photo 1 as the item image. */
	public static function store_api_images( $images, $cart_item ) {
		$tiles = self::photo_tiles( $cart_item );
		if ( ! $tiles ) {
			return $images;
		}
		return [ (object) [
			'id'        => 0,
			'src'       => $tiles[0]['thumb'],
			'thumbnail' => $tiles[0]['thumb'],
			'srcset'    => '',
			'sizes'     => '',
			'name'      => __( 'Your photo 1', 'memento-personalizer' ),
			'alt'       => __( 'Your photo 1', 'memento-personalizer' ),
		] ];
	}

	/** Expose photo tiles on each Store API cart item (read by assets/js/cart-photos.js). */
	public static function register_store_api_data() {
		if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			return;
		}
		woocommerce_store_api_register_endpoint_data( [
			'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema::IDENTIFIER,
			'namespace'       => 'memento-personalizer',
			'data_callback'   => function ( $cart_item ) {
				return [ 'photos' => self::photo_tiles( $cart_item ) ];
			},
			'schema_callback' => function () {
				return [
					'photos' => [
						'description' => __( 'Customer photo thumbnails in slot order.', 'memento-personalizer' ),
						'type'        => 'array',
						'context'     => [ 'view', 'edit' ],
						'readonly'    => true,
					],
				];
			},
			'schema_type'     => ARRAY_A,
		] );
	}

	/** Square tile strip markup (classic cart). */
	private static function tiles_html( array $tiles ) {
		$html = '<span class="mm-cart-photos">';
		foreach ( $tiles as $t ) {
			$html .= '<span class="mm-cart-photos__tile' . ( $t['low'] ? ' is-low' : '' ) . '"><img src="' . esc_url( $t['thumb'] ) . '" alt="' .
				/* translators: %d: photo number */
				esc_attr( sprintf( __( 'Photo %d', 'memento-personalizer' ), $t['slot'] ) ) . '" width="44" height="44" loading="lazy"></span>';
		}
		return $html . '</span>';
	}

	private static function posted_session_id() {
		// Nonce is not used here: WooCommerce's add-to-cart form has none. The
		// session is protected by the HttpOnly owner cookie instead.
		return isset( $_REQUEST['memento_pz_session'] ) ? sanitize_key( wp_unslash( $_REQUEST['memento_pz_session'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Validate a session's uploads for a given product/variation.
	 *
	 * @param bool $check_owner Require the current browser to own the session.
	 * @param string[] $states  Acceptable session states.
	 * @return array|WP_Error Upload IDs in slot order on success.
	 */
	public static function validate_session( $session_id, $product_id, $variation_id, $check_owner, array $states ) {
		if ( '' === (string) $session_id ) {
			// e.g. an ?add-to-cart= link: the customer never reached the upload step.
			return new WP_Error( 'no_session', __( 'Please upload your photos on the product page before adding this to your cart.', 'memento-personalizer' ) );
		}
		$session = Memento_PZ_Repository::get_session( $session_id );
		if ( ! $session || ( $check_owner && ! Memento_PZ_Upload_Session::owns( $session ) ) ) {
			return new WP_Error( 'session', __( 'Your photo session has expired. Please upload your photos again.', 'memento-personalizer' ) );
		}
		if ( ! in_array( $session->state, $states, true ) || Memento_PZ_Upload_Session::is_expired( $session ) ) {
			return new WP_Error( 'session_state', __( 'Your photo session has expired. Please upload your photos again.', 'memento-personalizer' ) );
		}
		if ( (int) $session->product_id !== (int) $product_id || (int) $session->variation_id !== (int) $variation_id ) {
			return new WP_Error( 'mismatch', __( 'Your photos were uploaded for a different product option. Please re-check your selection.', 'memento-personalizer' ) );
		}

		$required = Memento_PZ_Product_Config::get_required_photos( $variation_id ? $variation_id : $product_id );
		if ( $required < 1 || $required !== (int) $session->required_count ) {
			return new WP_Error( 'count_changed', __( 'The number of photos for this product has changed. Please refresh the page and check your photos.', 'memento-personalizer' ) );
		}

		// A "claimed" session belongs to an order that may still be unpaid (failed
		// payment, retry). Its final files stay valid for the cart in that case.
		$by_slot = [];
		foreach ( Memento_PZ_Repository::session_uploads( $session_id ) as $upload ) {
			$usable = 'temporary' === $upload->state
				|| ( 'final' === $upload->state && in_array( 'claimed', $states, true ) && Memento_PZ_Order_Files::is_unpaid_order( (int) $upload->order_id ) );
			if ( $usable ) {
				$by_slot[ (int) $upload->slot_index ] = $upload; // Latest per slot wins (ordered by id).
			}
		}

		$ids = [];
		for ( $slot = 0; $slot < $required; $slot++ ) {
			if ( empty( $by_slot[ $slot ] ) || ! Memento_PZ_Storage::exists( $by_slot[ $slot ]->storage_key ) ) {
				return new WP_Error( 'incomplete', sprintf(
					/* translators: %d: number of required photos */
					__( 'Please upload all %d photos before adding to cart.', 'memento-personalizer' ),
					$required
				) );
			}
			$ids[] = $by_slot[ $slot ]->upload_id;
		}
		if ( count( $by_slot ) !== $required ) {
			return new WP_Error( 'too_many', __( 'There are more photos than this product needs. Please refresh the page and check your photos.', 'memento-personalizer' ) );
		}
		return $ids;
	}

	public static function validate_add( $passed, $product_id, $quantity = 1, $variation_id = 0, $variations = [] ) {
		if ( ! $passed ) {
			return $passed;
		}
		$target = $variation_id ? $variation_id : $product_id;
		if ( ! Memento_PZ_Product_Config::is_personalised( $target ) ) {
			return $passed;
		}
		$result = self::validate_session( self::posted_session_id(), $product_id, $variation_id, true, [ 'open' ] );
		if ( is_wp_error( $result ) ) {
			wc_add_notice( $result->get_error_message(), 'error' );
			return false;
		}
		return $passed;
	}

	public static function add_item_data( $data, $product_id, $variation_id = 0 ) {
		$target = $variation_id ? $variation_id : $product_id;
		if ( ! Memento_PZ_Product_Config::is_personalised( $target ) ) {
			return $data;
		}
		$session_id = self::posted_session_id();
		$ids        = self::validate_session( $session_id, $product_id, $variation_id, true, [ 'open' ] );
		if ( ! is_wp_error( $ids ) ) {
			$data[ self::KEY ] = [
				'session_id' => $session_id,
				'uploads'    => $ids,
				'required'   => count( $ids ),
			];
		}
		return $data;
	}

	/** After a successful add-to-cart, freeze the session so the cart's photos can't change. */
	public static function lock_session( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( empty( $cart_item_data[ self::KEY ]['session_id'] ) ) {
			return;
		}
		$session_id = $cart_item_data[ self::KEY ]['session_id'];
		Memento_PZ_Repository::update_session( $session_id, [ 'state' => 'in_cart' ] );
		Memento_PZ_Repository::touch_session( $session_id, Memento_PZ_Settings::int( 'cart_ttl_hours' ) );
	}

	public static function display_item_data( $item_data, $cart_item ) {
		if ( ! empty( $cart_item[ self::KEY ]['uploads'] ) ) {
			$count       = count( $cart_item[ self::KEY ]['uploads'] );
			/* translators: %d: photo count */
			$label       = sprintf( _n( '%d photo', '%d photos', $count, 'memento-personalizer' ), $count );
			$item_data[] = [
				'key'     => __( 'Custom Photos', 'memento-personalizer' ),
				'value'   => $label,
				// Block cart/checkout strip <img> (tiles are added there by cart-photos.js),
				// so the text label must come first.
				'display' => esc_html( $label ) . self::tiles_html( self::photo_tiles( $cart_item ) ),
			];
		} elseif ( ! empty( $cart_item['memento_photos'] ) ) {
			$count       = count( (array) $cart_item['memento_photos'] );
			$item_data[] = [
				'key'   => __( 'Custom Photos', 'memento-personalizer' ),
				/* translators: %d: photo count */
				'value' => sprintf( _n( '%d photo uploaded', '%d photos uploaded', $count, 'memento-personalizer' ), $count ),
			];
		}
		return $item_data;
	}

	/** Re-validate every personalised item before checkout can proceed. */
	public static function check_cart_items() {
		if ( ! WC()->cart ) {
			return;
		}
		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id   = (int) $item['product_id'];
			$variation_id = (int) ( $item['variation_id'] ?? 0 );
			$target       = $variation_id ? $variation_id : $product_id;
			$name         = isset( $item['data'] ) && $item['data'] instanceof WC_Product ? $item['data']->get_name() : '';

			if ( ! empty( $item[ self::KEY ] ) ) {
				$ids = self::validate_session( $item[ self::KEY ]['session_id'], $product_id, $variation_id, false, [ 'in_cart', 'open', 'claimed' ] );
				if ( is_wp_error( $ids ) || $ids !== array_values( $item[ self::KEY ]['uploads'] ) ) {
					wc_add_notice( sprintf(
						/* translators: %s: product name */
						__( 'The photos for "%s" are no longer available. Please remove it from your cart and upload your photos again.', 'memento-personalizer' ),
						$name
					), 'error' );
				}
				continue;
			}

			if ( ! Memento_PZ_Product_Config::is_personalised( $target ) ) {
				continue;
			}

			// Legacy cart item from before the plugin (public URLs) — accept if the count matches.
			$required = Memento_PZ_Product_Config::get_required_photos( $target );
			if ( ! empty( $item['memento_photos'] ) && count( (array) $item['memento_photos'] ) === $required ) {
				continue;
			}

			wc_add_notice( sprintf(
				/* translators: %s: product name */
				__( '"%s" needs your photos. Please remove it from your cart and add it again from the product page.', 'memento-personalizer' ),
				$name
			), 'error' );
		}
	}

}
