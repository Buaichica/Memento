<?php
/**
 * Default product photos for the 3/6/9/12 magnet packs.
 *
 * The images ship with the theme (assets/img/products/). Once per version, on an
 * admin page load, each pack product that has NO product image gets the matching
 * photo imported into the Media Library and set as its Product image. Products
 * that already have an image are never touched, so images set in WP Admin win.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const MEMENTO_PRODUCT_IMAGES_VERSION = '1';

/** Pack size => [ theme file, alt text ]. */
function memento_default_product_images() {
    return [
        3  => [ 'memento-magnets-3-pack.jpg', __( 'Three personalised photo fridge magnets — family, golden retriever and mountain lake', 'memento-magnets' ) ],
        6  => [ 'memento-magnets-6-pack.jpg', __( 'Six personalised photo fridge magnets laid out in two rows', 'memento-magnets' ) ],
        9  => [ 'memento-magnets-9-pack.jpg', __( 'Nine personalised photo fridge magnets in a three-by-three grid', 'memento-magnets' ) ],
        12 => [ 'memento-magnets-12-pack.jpg', __( 'Twelve personalised photo fridge magnets of family, pets and places', 'memento-magnets' ) ],
    ];
}

/**
 * Assign default images to pack products without one.
 *
 * @return array Report: product ID => attached|has_image|no_file|error
 */
function memento_setup_product_images() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return [];
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $images   = memento_default_product_images();
    $report   = [];
    $imported = []; // Reuse one attachment per pack size if several products share it.

    $products = wc_get_products( [ 'status' => [ 'publish', 'draft', 'private' ], 'limit' => -1 ] );
    foreach ( $products as $product ) {
        $count = function_exists( 'memento_card_pack_count' ) ? memento_card_pack_count( $product ) : 0;
        if ( ! isset( $images[ $count ] ) ) {
            continue;
        }
        if ( $product->get_image_id() ) {
            $report[ $product->get_id() ] = 'has_image';
            continue;
        }
        list( $file, $alt ) = $images[ $count ];
        $source = MEMENTO_DIR . '/assets/img/products/' . $file;
        if ( ! is_readable( $source ) ) {
            $report[ $product->get_id() ] = 'no_file';
            continue;
        }

        if ( empty( $imported[ $count ] ) ) {
            // media_handle_sideload() moves the file, so give it a temporary copy.
            $tmp = wp_tempnam( $file );
            copy( $source, $tmp );
            $attachment_id = media_handle_sideload( [ 'name' => $file, 'tmp_name' => $tmp ], 0, $product->get_name() );
            if ( is_wp_error( $attachment_id ) ) {
                @unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
                $report[ $product->get_id() ] = 'error: ' . $attachment_id->get_error_message();
                continue;
            }
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
            $imported[ $count ] = $attachment_id;
        }

        $product->set_image_id( $imported[ $count ] );
        $product->save();
        $report[ $product->get_id() ] = 'attached';
    }
    return $report;
}

/** Run once per version, race-safe (same atomic-lock approach as inc/page-setup.php). */
function memento_maybe_setup_product_images() {
    if ( get_option( 'memento_product_images_version' ) === MEMENTO_PRODUCT_IMAGES_VERSION ) {
        return null;
    }
    $lock = 'memento_product_images_lock';
    if ( ! add_option( $lock, time(), '', false ) ) {
        if ( (int) get_option( $lock ) < time() - 5 * MINUTE_IN_SECONDS ) {
            delete_option( $lock );
        }
        return null;
    }
    wp_cache_delete( 'alloptions', 'options' );
    if ( get_option( 'memento_product_images_version' ) === MEMENTO_PRODUCT_IMAGES_VERSION ) {
        delete_option( $lock );
        return null;
    }
    $report = memento_setup_product_images();
    update_option( 'memento_product_images_version', MEMENTO_PRODUCT_IMAGES_VERSION );
    delete_option( $lock );
    return $report;
}

add_action( 'admin_init', function () {
    if ( wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'edit_products' ) ) {
        return;
    }
    $report = memento_maybe_setup_product_images();
    if ( $report && in_array( 'attached', $report, true ) ) {
        set_transient( 'memento_product_images_notice', count( array_keys( $report, 'attached', true ) ), 10 * MINUTE_IN_SECONDS );
    }
} );

add_action( 'admin_notices', function () {
    $count = get_transient( 'memento_product_images_notice' );
    if ( ! $count ) {
        return;
    }
    delete_transient( 'memento_product_images_notice' );
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf(
        /* translators: %d: number of products */
        _n( 'Memento Magnets added a product photo to %d magnet pack.', 'Memento Magnets added product photos to %d magnet packs.', $count, 'memento-magnets' ),
        $count
    ) ) . '</p></div>';
} );
