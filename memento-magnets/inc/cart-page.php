<?php
/**
 * Cart page: branded styling, progress bar + trust row, and a useful empty state.
 * Works with the WooCommerce Cart block. Styles: assets/css/cart.css.
 *
 * Extra markup is added OUTSIDE the Cart block (before/after it) so WooCommerce's
 * React rendering never removes it.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_cart' ) && is_cart() ) {
        wp_enqueue_style( 'memento-cart', MEMENTO_URI . '/assets/css/cart.css', [ 'memento-theme' ], memento_asset_ver( 'assets/css/cart.css' ) );
    }
}, 20 );

/** Simple Cart → Checkout → Done indicator. */
function memento_checkout_steps( $current = 1 ) {
    $steps = [ 1 => __( 'Cart', 'memento-magnets' ), 2 => __( 'Checkout', 'memento-magnets' ), 3 => __( 'Done', 'memento-magnets' ) ];
    $html  = '<ol class="mm-steps" aria-label="' . esc_attr__( 'Checkout progress', 'memento-magnets' ) . '">';
    foreach ( $steps as $n => $label ) {
        $state = $n < $current ? 'is-done' : ( $n === $current ? 'is-current' : '' );
        $html .= '<li class="mm-steps__step ' . $state . '"' . ( $n === $current ? ' aria-current="step"' : '' ) . '><span class="mm-steps__num">' . (int) $n . '</span>' . esc_html( $label ) . '</li>';
    }
    return $html . '</ol>';
}

add_filter( 'render_block', function ( $html, $block ) {
    if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
        return $html;
    }
    $name = $block['blockName'] ?? '';

    // Toolbar above and trust row below the whole Cart block.
    if ( 'woocommerce/cart' === $name ) {
        $shop  = get_permalink( wc_get_page_id( 'shop' ) );
        $top   = '<div class="mm-cart-bar">'
            . '<a class="mm-cart-bar__back" href="' . esc_url( $shop ) . '">← ' . esc_html__( 'Continue shopping', 'memento-magnets' ) . '</a>'
            . memento_checkout_steps( 1 )
            . '</div>';
        $trust = [
            [ '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>', __( 'Secure checkout', 'memento-magnets' ), __( 'SSL-encrypted payment', 'memento-magnets' ) ],
            [ '<path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>', __( 'Fast NZ shipping', 'memento-magnets' ), __( 'Free on orders over $50', 'memento-magnets' ) ],
            [ '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/>', __( 'Handmade in New Zealand', 'memento-magnets' ), __( 'Printed with care from your photos', 'memento-magnets' ) ],
        ];
        $row = '<ul class="mm-cart-trust">';
        foreach ( $trust as list( $icon, $title, $text ) ) {
            $row .= '<li><span class="mm-cart-trust__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $icon . '</svg></span>'
                . '<span><strong>' . esc_html( $title ) . '</strong><small>' . esc_html( $text ) . '</small></span></li>';
        }
        $row .= '</ul>';
        return $top . $html . $row;
    }

    // Empty cart: replace the sad-face heading with a friendly branded message.
    if ( 'core/heading' === $name && false !== strpos( $html, 'with-empty-cart-icon' ) ) {
        return '<div class="mm-empty-cart">'
            . ( function_exists( 'memento_magnet_tiles_visual' ) ? memento_magnet_tiles_visual( 3, 'mm-empty-cart__tiles' ) : '' )
            . '<h2 class="mm-empty-cart__title">' . esc_html__( 'Your cart is empty', 'memento-magnets' ) . '</h2>'
            . '<p class="mm-empty-cart__text">' . esc_html__( 'Pick a pack below, upload your favourite photos and we\'ll turn them into magnets.', 'memento-magnets' ) . '</p>'
            . '</div>';
    }

    // Empty cart: "New in store" grid → our product cards (with "Upload Now", since
    // photo products can't be added to the cart without photos).
    if ( 'woocommerce/product-new' === $name && function_exists( 'memento_render_product_card' ) ) {
        $query = new WP_Query( [
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => 4,
            'memento_pack_sort' => true,
            'no_found_rows'     => true,
            'tax_query'         => [ [
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'exclude-from-catalog',
                'operator' => 'NOT IN',
            ] ],
        ] );
        ob_start();
        echo '<div class="mm-card-grid mm-empty-cart__grid">';
        foreach ( $query->posts as $post_obj ) {
            $product = wc_get_product( $post_obj );
            if ( $product ) {
                memento_render_product_card( memento_product_card_data( $product ) );
            }
        }
        echo '</div>';
        return ob_get_clean();
    }

    // Empty cart: retitle "New in store" and drop the dotted separator.
    if ( 'core/separator' === $name && false !== strpos( $html, 'is-style-dots' ) ) {
        return '';
    }
    if ( 'core/heading' === $name && false !== strpos( $html, 'has-text-align-center' ) && false !== stripos( wp_strip_all_tags( $html ), __( 'New in store', 'woocommerce' ) ) ) {
        return '<h3 class="mm-empty-cart__subtitle">' . esc_html__( 'Choose your magnet pack', 'memento-magnets' ) . '</h3>';
    }

    return $html;
}, 10, 2 );
