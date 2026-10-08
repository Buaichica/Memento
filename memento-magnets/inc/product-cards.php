<?php
/**
 * Shared product cards — used by the Products (shop) page and the homepage
 * "Browse Our Collections" section so both always look identical.
 *
 * - Sorted by pack size (3 → 6 → 9 → 12), non-photo products last.
 * - Branded magnet-tile visual when a product has no image yet.
 * - Badge (per-product "Card badge" field, with pack-size defaults) + short description.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const MEMENTO_CARD_BADGE_META = '_memento_card_badge';

/** Number of photos/magnets in a product (0 = not a photo product). */
function memento_card_pack_count( $product ) {
    if ( function_exists( 'memento_get_photo_count' ) ) {
        return (int) memento_get_photo_count( $product ); // Memento Personalizer plugin.
    }
    $count = (int) $product->get_meta( '_memento_photo_count', true );
    if ( ! $count && preg_match( '/\b(12|9|6|3)\b/', $product->get_name(), $m ) ) {
        $count = (int) $m[1];
    }
    return $count;
}

/** Defaults used when a product has no short description / badge set. */
function memento_card_defaults( $count ) {
    $defaults = [
        3  => [ __( 'Popular', 'memento-magnets' ), __( 'A sweet trio of your most treasured photos — a heartfelt little keepsake.', 'memento-magnets' ) ],
        6  => [ '', __( 'Tell your story six photos at a time. Great for gifting friends and family.', 'memento-magnets' ) ],
        9  => [ __( 'Best Seller', 'memento-magnets' ), __( 'Fill your fridge with memories — a mini gallery wall of favourite moments.', 'memento-magnets' ) ],
        12 => [ __( 'Best Value', 'memento-magnets' ), __( 'The ultimate collection for memory lovers. A whole year of moments.', 'memento-magnets' ) ],
    ];
    return $defaults[ $count ] ?? [ '', '' ];
}

function memento_card_badge( WC_Product $product, $count ) {
    if ( metadata_exists( 'post', $product->get_id(), MEMENTO_CARD_BADGE_META ) ) {
        $badge = (string) get_post_meta( $product->get_id(), MEMENTO_CARD_BADGE_META, true );
    } else {
        $badge = memento_card_defaults( $count )[0];
    }
    if ( '' === $badge && $product->is_on_sale() ) {
        $badge = __( 'Sale', 'memento-magnets' );
    }
    return $badge;
}

function memento_card_description( WC_Product $product, $count ) {
    $text = $product->get_short_description();
    if ( '' === trim( wp_strip_all_tags( $text ) ) ) {
        return memento_card_defaults( $count )[1];
    }
    return wp_trim_words( wp_strip_all_tags( $text ), 18 );
}

/**
 * Branded stand-in image: a grid of magnet tiles (one per photo in the pack).
 */
function memento_magnet_tiles_visual( $count, $extra_class = '' ) {
    $count = max( 1, min( 12, (int) $count ) );
    $cols  = $count >= 12 ? 4 : min( 3, $count );
    $html  = '<div class="mm-tiles mm-tiles--' . (int) $count . ' ' . esc_attr( $extra_class ) . '" style="--mm-cols:' . (int) $cols . '" role="img" aria-label="' .
        esc_attr( sprintf( /* translators: %d: number of magnets */ _n( '%d photo magnet', '%d photo magnets', $count, 'memento-magnets' ), $count ) ) . '">';
    for ( $i = 0; $i < $count; $i++ ) {
        $html .= '<span class="mm-tiles__tile"></span>';
    }
    return $html . '</div>';
}

/** Card data for a WooCommerce product. */
function memento_product_card_data( WC_Product $product ) {
    $count = memento_card_pack_count( $product );
    $image = $product->get_image_id()
        ? wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, [ 'loading' => 'lazy', 'alt' => $product->get_name() ] )
        : ( $count ? memento_magnet_tiles_visual( $count ) : '<span class="mm-card__emoji" aria-hidden="true">🧲</span>' );

    return [
        'url'    => $product->get_permalink(),
        'name'   => $product->get_name(),
        'desc'   => memento_card_description( $product, $count ),
        'price'  => $product->get_price_html(),
        'badge'  => memento_card_badge( $product, $count ),
        'image'  => $image,
        'button' => $count ? __( 'Upload Now', 'memento-magnets' ) : __( 'View Product', 'memento-magnets' ),
    ];
}

/** Render one card. $card comes from memento_product_card_data() (or a static fallback). */
function memento_render_product_card( array $card ) {
    ?>
    <article class="mm-card fade-in-up">
        <a href="<?php echo esc_url( $card['url'] ); ?>" class="mm-card__media" tabindex="-1" aria-hidden="true">
            <?php echo $card['image']; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts / WP image functions. ?>
            <?php if ( ! empty( $card['badge'] ) ) : ?>
                <span class="mm-card__badge"><?php echo esc_html( $card['badge'] ); ?></span>
            <?php endif; ?>
        </a>
        <div class="mm-card__body">
            <h3 class="mm-card__title"><a href="<?php echo esc_url( $card['url'] ); ?>"><?php echo esc_html( $card['name'] ); ?></a></h3>
            <?php if ( ! empty( $card['desc'] ) ) : ?>
                <p class="mm-card__desc"><?php echo esc_html( $card['desc'] ); ?></p>
            <?php endif; ?>
            <div class="mm-card__price"><?php echo wp_kses_post( $card['price'] ); ?></div>
            <a href="<?php echo esc_url( $card['url'] ); ?>" class="mm-card__button">
                <?php echo esc_html( $card['button'] ); ?>
                <span class="screen-reader-text"> — <?php echo esc_html( $card['name'] ); ?></span>
            </a>
        </div>
    </article>
    <?php
}

/* ── Pack-size ordering ─────────────────────────────────────── */

/**
 * Order product queries flagged with `memento_pack_sort` by photo count:
 * 3, 6, 9, 12 … then non-photo products, then menu order / title.
 */
add_filter( 'posts_clauses', function ( $clauses, $query ) {
    if ( ! $query->get( 'memento_pack_sort' ) ) {
        return $clauses;
    }
    global $wpdb;
    $clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} AS mm_pack ON ( mm_pack.post_id = {$wpdb->posts}.ID AND mm_pack.meta_key = '_memento_photo_count' )";
    $clauses['orderby'] = "( COALESCE( mm_pack.meta_value + 0, 0 ) = 0 ) ASC, ( mm_pack.meta_value + 0 ) ASC, {$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_title ASC";
    $clauses['groupby'] = "{$wpdb->posts}.ID";
    return $clauses;
}, 20, 2 );

// Shop + product category/tag pages: pack-size order unless a sort was explicitly requested.
add_action( 'pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() || isset( $_GET['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
        return;
    }
    if ( $query->is_post_type_archive( 'product' ) || $query->is_tax( [ 'product_cat', 'product_tag' ] ) ) {
        $query->set( 'memento_pack_sort', true );
    }
} );

/* ── "Card badge" product field ─────────────────────────────── */

add_action( 'woocommerce_product_options_general_product_data', function () {
    global $post;
    $exists = metadata_exists( 'post', $post->ID, MEMENTO_CARD_BADGE_META );
    echo '<div class="options_group">';
    woocommerce_wp_text_input( [
        'id'          => MEMENTO_CARD_BADGE_META,
        'label'       => __( 'Card badge', 'memento-magnets' ),
        'value'       => $exists ? get_post_meta( $post->ID, MEMENTO_CARD_BADGE_META, true ) : '',
        'placeholder' => $exists ? '' : __( 'Default for pack size', 'memento-magnets' ),
        'description' => __( 'Short label on the product card, e.g. "Best Seller". Leave blank for the pack-size default; type "none" to hide it.', 'memento-magnets' ),
        'desc_tip'    => true,
    ] );
    echo '</div>';
} );

add_action( 'woocommerce_admin_process_product_object', function ( $product ) {
    // WooCommerce verifies the product edit nonce before this hook runs.
    if ( ! isset( $_POST[ MEMENTO_CARD_BADGE_META ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
        return;
    }
    $value = sanitize_text_field( wp_unslash( $_POST[ MEMENTO_CARD_BADGE_META ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
    if ( '' === $value ) {
        $product->delete_meta_data( MEMENTO_CARD_BADGE_META );      // Use default.
    } elseif ( 'none' === strtolower( $value ) ) {
        $product->update_meta_data( MEMENTO_CARD_BADGE_META, '' );  // Explicitly no badge.
    } else {
        $product->update_meta_data( MEMENTO_CARD_BADGE_META, mb_substr( $value, 0, 24 ) );
    }
} );

/* ── Single product page: same branded visual instead of the grey placeholder ── */

add_filter( 'woocommerce_single_product_image_thumbnail_html', function ( $html, $attachment_id ) {
    global $product;
    if ( $attachment_id || ! $product instanceof WC_Product ) {
        return $html;
    }
    $count = memento_card_pack_count( $product );
    if ( ! $count ) {
        return $html;
    }
    return '<div class="woocommerce-product-gallery__image--placeholder mm-single-visual">' . memento_magnet_tiles_visual( $count, 'mm-tiles--large' ) . '</div>';
}, 10, 2 );

// Zoom/lightbox are off, so drop the link around product photos — clicking shouldn't open the raw image file.
add_filter( 'woocommerce_single_product_image_thumbnail_html', function ( $html ) {
    return preg_replace( '#<a\b[^>]*>(.*?)</a>#s', '$1', $html );
}, 20 );

/* ── Single product page: related products as shared cards ───── */

// Replace WooCommerce's default related/upsell loops (grey placeholders, different design).
add_action( 'wp', function () {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
    add_action( 'woocommerce_after_single_product_summary', 'memento_output_related_cards', 20 );
} );

/**
 * "You may also like" — upsells first, then related products, then any other
 * visible products as a fallback; max 4, ordered by pack size.
 */
function memento_output_related_cards() {
    global $product;
    if ( ! $product instanceof WC_Product ) {
        return;
    }
    $limit = 4;
    $ids   = array_merge( $product->get_upsell_ids(), wc_get_related_products( $product->get_id(), $limit ) );
    if ( count( array_unique( $ids ) ) < $limit ) {
        // Small catalogue: fill with other products so the section is never sparse.
        $ids = array_merge( $ids, wc_get_products( [
            'status'     => 'publish',
            'visibility' => 'catalog',
            'exclude'    => array_merge( [ $product->get_id() ], $ids ),
            'limit'      => $limit,
            'return'     => 'ids',
        ] ) );
    }
    $ids = array_slice( array_values( array_unique( array_diff( array_map( 'absint', $ids ), [ $product->get_id() ] ) ) ), 0, $limit );
    if ( ! $ids ) {
        return;
    }

    $query = new WP_Query( [
        'post_type'         => 'product',
        'post_status'       => 'publish',
        'post__in'          => $ids,
        'posts_per_page'    => $limit,
        'memento_pack_sort' => true,
        'no_found_rows'     => true,
    ] );
    $cards = [];
    foreach ( $query->posts as $post_obj ) {
        $related = wc_get_product( $post_obj );
        if ( $related && $related->is_visible() ) {
            $cards[] = memento_product_card_data( $related );
        }
    }
    if ( ! $cards ) {
        return;
    }
    ?>
    <section class="related products mm-related" aria-labelledby="mm-related-heading">
        <h2 id="mm-related-heading"><?php esc_html_e( 'You may also like', 'memento-magnets' ); ?></h2>
        <div class="mm-card-grid" style="--mm-grid-cols:<?php echo (int) max( 3, count( $cards ) ); ?>">
            <?php foreach ( $cards as $card ) { memento_render_product_card( $card ); } ?>
        </div>
    </section>
    <?php
}
