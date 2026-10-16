<?php
/**
 * Default product photos for the 3/6/9/12 magnet packs.
 *
 * The images ship with the theme (assets/img/products/). Once per version, on an
 * admin page load, each pack product gets the matching photo imported into the
 * Media Library and set as its Product image — but only if the product has no
 * image, or its current image is an earlier theme default. Images chosen in
 * WP Admin are never replaced.
 *
 * Each attachment gets descriptive alt text, title, caption and description:
 * Google Images and AI answer engines read these to understand the product.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const MEMENTO_PRODUCT_IMAGES_VERSION = '2'; // 2: new photos, SEO filenames + full metadata.

/** Attachment meta marking an image the theme imported (value: pack size). */
const MEMENTO_DEFAULT_IMAGE_META = '_memento_default_image';

/** Pack size => file + attachment metadata. */
function memento_default_product_images() {
    $shared = __( 'Each magnet is 5 × 5 cm with a vibrant gloss finish, printed from your own photos and handcrafted in New Zealand. A personal gift for birthdays, weddings, Christmas and new babies — $7.99 flat-rate NZ shipping, free on orders over $50.', 'memento-magnets' );

    $images = [
        3  => [
            'file' => 'personalised-photo-fridge-magnets-3-pack-nz.jpg',
            'alt'  => __( 'Three personalised square photo fridge magnets showing a family hugging at sunset, a golden retriever and a mountain lake', 'memento-magnets' ),
        ],
        6  => [
            'file' => 'personalised-photo-fridge-magnets-6-pack-nz.jpg',
            'alt'  => __( 'Six personalised square photo fridge magnets showing a family, a smiling baby in a bear hood, a golden retriever, a birthday girl with her cake, a graduate and a mountain lake', 'memento-magnets' ),
        ],
        9  => [
            'file' => 'personalised-photo-fridge-magnets-9-pack-nz.jpg',
            'alt'  => __( 'Nine personalised square photo fridge magnets in a three-by-three grid showing family portraits, a baby, a golden retriever, a birthday, a graduation, a couple at sunset and beach and mountain lake scenery', 'memento-magnets' ),
        ],
        12 => [
            'file' => 'personalised-photo-fridge-magnets-12-pack-nz.jpg',
            'alt'  => __( 'Twelve personalised square photo fridge magnets showing family, a baby, grandparents with their grandson, a wedding couple, a graduate, a birthday, a golden retriever, a tabby cat and beach and mountain scenery', 'memento-magnets' ),
        ],
    ];

    foreach ( $images as $count => &$image ) {
        /* translators: %d: number of magnets in the pack */
        $image['title']       = sprintf( __( 'Personalised Photo Fridge Magnets – %d Pack | Memento Magnets NZ', 'memento-magnets' ), $count );
        /* translators: %d: number of magnets in the pack */
        $image['caption']     = sprintf( __( '%d custom 5 × 5 cm glossy photo magnets, handcrafted in New Zealand from your own photos.', 'memento-magnets' ), $count );
        $image['description'] = $shared;
    }
    unset( $image );

    return $images;
}

/**
 * Is this attachment one the theme imported (so it may be upgraded)?
 * Version 1 imports had no marker, so they're recognised by file name.
 */
function memento_is_default_product_image( $attachment_id, $count ) {
    if ( ! $attachment_id ) {
        return false;
    }
    if ( '' !== (string) get_post_meta( $attachment_id, MEMENTO_DEFAULT_IMAGE_META, true ) ) {
        return true;
    }
    $file = basename( (string) get_attached_file( $attachment_id ) );
    return 0 === strpos( $file, 'memento-magnets-' . (int) $count . '-pack' );
}

/**
 * Assign the current default images to pack products that have no image or an
 * older theme default.
 *
 * @return array Report: product ID => attached|updated|custom_image|no_file|error
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
        $current = (int) $product->get_image_id();
        if ( $current && ! memento_is_default_product_image( $current, $count ) ) {
            $report[ $product->get_id() ] = 'custom_image';
            continue;
        }
        $image  = $images[ $count ];
        $source = MEMENTO_DIR . '/assets/img/products/' . $image['file'];
        if ( ! is_readable( $source ) ) {
            $report[ $product->get_id() ] = 'no_file';
            continue;
        }
        if ( $current && $image['file'] === basename( (string) get_attached_file( $current ) ) ) {
            continue; // Already the current default.
        }

        if ( empty( $imported[ $count ] ) ) {
            // media_handle_sideload() moves the file, so give it a temporary copy.
            $tmp = wp_tempnam( $image['file'] );
            copy( $source, $tmp );
            $attachment_id = media_handle_sideload(
                [ 'name' => $image['file'], 'tmp_name' => $tmp ],
                0,
                $image['title'],
                [
                    'post_excerpt' => $image['caption'],     // Caption.
                    'post_content' => $image['description'], // Description.
                ]
            );
            if ( is_wp_error( $attachment_id ) ) {
                @unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
                $report[ $product->get_id() ] = 'error: ' . $attachment_id->get_error_message();
                continue;
            }
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $image['alt'] );
            update_post_meta( $attachment_id, MEMENTO_DEFAULT_IMAGE_META, $count );
            $imported[ $count ] = $attachment_id;
        }

        $product->set_image_id( $imported[ $count ] );
        $product->save();
        $report[ $product->get_id() ] = $current ? 'updated' : 'attached';
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
    if ( $report ) {
        $changed = count( array_keys( $report, 'attached', true ) ) + count( array_keys( $report, 'updated', true ) );
        if ( $changed ) {
            set_transient( 'memento_product_images_notice', $changed, 10 * MINUTE_IN_SECONDS );
        }
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
        _n( 'Memento Magnets updated the product photo on %d magnet pack.', 'Memento Magnets updated the product photos on %d magnet packs.', $count, 'memento-magnets' ),
        $count
    ) ) . '</p></div>';
} );
