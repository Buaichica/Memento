<?php
/**
 * "Required Photos" product configuration.
 *
 * `_memento_photo_count` (existing meta key, kept for compatibility) is the
 * single source of truth:
 *   - integer > 0 → product is personalised and needs exactly that many photos
 *   - 0           → product is NOT personalised
 *   - unset       → deprecated fallback: parse "3/6/9/12" from the title
 *
 * Variations may override the parent value with their own meta.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Product_Config {

	const META_KEY = '_memento_photo_count';

	public static function init() {
		// Simple / parent product field.
		add_action( 'woocommerce_product_options_general_product_data', [ __CLASS__, 'render_field' ] );
		add_action( 'woocommerce_admin_process_product_object', [ __CLASS__, 'save_field' ] );

		// Variation field.
		add_action( 'woocommerce_product_after_variable_attributes', [ __CLASS__, 'render_variation_field' ], 10, 3 );
		add_action( 'woocommerce_save_product_variation', [ __CLASS__, 'save_variation_field' ], 10, 2 );

		// Expose per-variation counts to the front-end variation JSON.
		add_filter( 'woocommerce_available_variation', function ( $data, $product, $variation ) {
			$data['memento_photo_count'] = self::get_required_photos( $variation );
			return $data;
		}, 10, 3 );

		add_action( 'admin_notices', [ __CLASS__, 'fallback_notice' ] );
	}

	/**
	 * @param WC_Product|int $product
	 * @return int
	 */
	public static function get_required_photos( $product ) {
		$product = $product instanceof WC_Product ? $product : wc_get_product( $product );
		if ( ! $product ) {
			return 0;
		}

		$max = Memento_PZ_Settings::int( 'max_photos' );

		if ( $product->is_type( 'variation' ) ) {
			$own = $product->get_meta( self::META_KEY, true );
			if ( '' !== (string) $own && (int) $own > 0 ) {
				return min( $max, (int) $own );
			}
			$product = wc_get_product( $product->get_parent_id() );
			if ( ! $product ) {
				return 0;
			}
		}

		$raw = $product->get_meta( self::META_KEY, true );
		if ( '' !== (string) $raw ) {
			return max( 0, min( $max, (int) $raw ) );
		}

		// Deprecated: title parsing. Kept only so un-migrated products keep working.
		$from_title = self::count_from_title( $product->get_name() );
		if ( $from_title ) {
			Memento_PZ_Logger::debug( sprintf( 'Product %d uses deprecated title-based photo count.', $product->get_id() ) );
		}
		return $from_title;
	}

	public static function is_personalised( $product ) {
		return self::get_required_photos( $product ) > 0;
	}

	public static function count_from_title( $title ) {
		return preg_match( '/\b(12|9|6|3)\b/', (string) $title, $m ) ? (int) $m[1] : 0;
	}

	public static function render_field() {
		echo '<div class="options_group">';
		woocommerce_wp_text_input( [
			'id'                => self::META_KEY,
			'label'             => __( 'Required Photos', 'memento-personalizer' ),
			'description'       => __( 'Exact number of customer photos needed for this product. Use 0 for products that are not personalised.', 'memento-personalizer' ),
			'desc_tip'          => true,
			'type'              => 'number',
			'custom_attributes' => [ 'min' => 0, 'max' => Memento_PZ_Settings::int( 'max_photos' ), 'step' => 1 ],
		] );
		wp_nonce_field( 'memento_pz_product_save', 'memento_pz_product_nonce' );
		echo '</div>';
	}

	/** @param WC_Product $product */
	public static function save_field( $product ) {
		if ( ! isset( $_POST['memento_pz_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['memento_pz_product_nonce'] ), 'memento_pz_product_save' ) ) {
			return;
		}
		if ( ! isset( $_POST[ self::META_KEY ] ) ) {
			return;
		}
		$value = self::sanitize_count( wp_unslash( $_POST[ self::META_KEY ] ) );
		if ( null === $value ) {
			if ( class_exists( 'WC_Admin_Meta_Boxes' ) ) {
				WC_Admin_Meta_Boxes::add_error( __( 'Required Photos must be a whole number between 0 and the configured maximum. The previous value was kept.', 'memento-personalizer' ) );
			}
			return;
		}
		$product->update_meta_data( self::META_KEY, $value );
	}

	public static function render_variation_field( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( [
			'id'                => self::META_KEY . "_{$loop}",
			'name'              => self::META_KEY . "[{$loop}]",
			'value'             => get_post_meta( $variation->ID, self::META_KEY, true ),
			'label'             => __( 'Required Photos (leave blank to use the parent product value)', 'memento-personalizer' ),
			'type'              => 'number',
			'wrapper_class'     => 'form-row form-row-full',
			'custom_attributes' => [ 'min' => 1, 'max' => Memento_PZ_Settings::int( 'max_photos' ), 'step' => 1 ],
		] );
	}

	public static function save_variation_field( $variation_id, $loop ) {
		// WooCommerce verifies the save-variations nonce before this hook fires.
		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST[ self::META_KEY ][ $loop ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$raw = trim( (string) wp_unslash( $_POST[ self::META_KEY ][ $loop ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( '' === $raw ) {
			delete_post_meta( $variation_id, self::META_KEY );
			return;
		}
		$value = self::sanitize_count( $raw );
		if ( null !== $value && $value > 0 ) {
			update_post_meta( $variation_id, self::META_KEY, $value );
		}
	}

	/** @return int|null Null when invalid. */
	private static function sanitize_count( $raw ) {
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return 0;
		}
		if ( ! preg_match( '/^\d+$/', $raw ) ) {
			return null;
		}
		$value = (int) $raw;
		return $value <= Memento_PZ_Settings::int( 'max_photos' ) ? $value : null;
	}

	/** Warn on the product edit screen when a product still relies on its title. */
	public static function fallback_notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'product' !== $screen->id ) {
			return;
		}
		global $post;
		if ( ! $post || '' !== (string) get_post_meta( $post->ID, self::META_KEY, true ) ) {
			return;
		}
		$count = self::count_from_title( $post->post_title );
		if ( ! $count ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' . sprintf(
			/* translators: %d: photo count */
			esc_html__( 'This product\'s photo count (%d) is currently guessed from its title. Please set "Required Photos" in Product data → General and save.', 'memento-personalizer' ),
			(int) $count
		) . '</p></div>';
	}
}
