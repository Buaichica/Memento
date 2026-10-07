<?php
/**
 * My Account area: menu items and account-only styles.
 * Dashboard markup: woocommerce/myaccount/dashboard.php. Styles: assets/css/account.css.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Dashboard, Orders, Addresses, Account details, Log out — no Downloads (no downloadable products).
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
    $order = [ 'dashboard', 'orders', 'edit-address', 'edit-account', 'customer-logout' ];
    unset( $items['downloads'] );
    $sorted = [];
    foreach ( $order as $key ) {
        if ( isset( $items[ $key ] ) ) {
            $sorted[ $key ] = $items[ $key ];
            unset( $items[ $key ] );
        }
    }
    // Keep any items added by plugins, just before "Log out".
    $logout = isset( $sorted['customer-logout'] ) ? [ 'customer-logout' => $sorted['customer-logout'] ] : [];
    unset( $sorted['customer-logout'] );
    return $sorted + $items + $logout;
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
    if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
        return;
    }
    wp_enqueue_style( 'memento-account', MEMENTO_URI . '/assets/css/account.css', [ 'memento-theme' ], memento_asset_ver( 'assets/css/account.css' ) );
}, 20 );

/** Status badge HTML for an order (shared by the dashboard; the orders table is styled in CSS). */
function memento_order_status_badge( WC_Order $order ) {
    return '<span class="mm-status mm-status--' . esc_attr( $order->get_status() ) . '">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span>';
}

// Status column in the Orders table: badge instead of plain text.
add_action( 'woocommerce_my_account_my_orders_column_order-status', function ( $order ) {
    echo memento_order_status_badge( $order ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper.
} );
