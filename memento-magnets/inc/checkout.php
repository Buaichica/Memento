<?php
/**
 * Checkout: branded styles (assets/css/checkout.css) and shipping-rate tidy-up.
 *
 * Shipping zones and rates themselves are WooCommerce settings
 * (WooCommerce → Settings → Shipping), not theme code.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', function () {
    if ( function_exists( 'is_checkout' ) && is_checkout() ) {
        wp_enqueue_style( 'memento-checkout', MEMENTO_URI . '/assets/css/checkout.css', [ 'memento-theme' ], memento_asset_ver( 'assets/css/checkout.css' ) );
    }
}, 20 );

/**
 * When an order qualifies for free shipping, show only the free option —
 * customers shouldn't be offered "$7.99" next to "Free".
 */
add_filter( 'woocommerce_package_rates', function ( $rates ) {
    $free = array_filter( $rates, function ( $rate ) {
        return 'free_shipping' === $rate->get_method_id();
    } );
    return $free ? $free : $rates;
}, 100 );
