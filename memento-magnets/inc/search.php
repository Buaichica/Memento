<?php
/**
 * Site search: instant suggestions data for the header search panel, quick
 * links, and cleaner results (no cart/checkout/account pages).
 * Markup: header.php (.search-overlay), searchform.php, search.php.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Small index of the most useful destinations, filtered in the browser as the
 * visitor types. Cached; refreshed whenever a product, page or post is saved.
 *
 * @return array[] { title, url, type, meta, keywords }
 */
function memento_search_index() {
    $cached = get_transient( 'memento_search_index' );
    if ( is_array( $cached ) ) {
        return $cached;
    }
    $items = [];

    if ( class_exists( 'WooCommerce' ) ) {
        $query = new WP_Query( [
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => 12,
            'memento_pack_sort' => true,
            'no_found_rows'     => true,
        ] );
        foreach ( $query->posts as $post_obj ) {
            $product = wc_get_product( $post_obj );
            if ( ! $product || ! $product->is_visible() ) {
                continue;
            }
            $count   = function_exists( 'memento_card_pack_count' ) ? memento_card_pack_count( $product ) : 0;
            $items[] = [
                'title'    => $product->get_name(),
                'url'      => $product->get_permalink(),
                'type'     => 'product',
                'meta'     => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price() ) ), ENT_QUOTES, 'UTF-8' ) . ( $count ? ' · ' . sprintf( /* translators: %d: magnets */ _n( '%d magnet', '%d magnets', $count, 'memento-magnets' ), $count ) : '' ),
                'keywords' => trim( $count . ' pack ' . wp_strip_all_tags( $product->get_short_description() ) ),
                'count'    => $count,
            ];
        }
    }

    $pages = [
        [ 'blogs', __( 'Tips, ideas & gift inspiration', 'memento-magnets' ), 'blog articles' ],
        [ 'faq', __( 'Ordering, shipping & photo questions', 'memento-magnets' ), 'help questions delivery shipping returns refund payment' ],
        [ 'contact', __( 'Get in touch with us', 'memento-magnets' ), 'help email support message' ],
        [ 'personalised-magnets-perfect-gift-nz', __( 'Article', 'memento-magnets' ), 'gift present' ],
        [ 'how-to-choose-best-photo-for-custom-magnet', __( 'Article', 'memento-magnets' ), 'photo quality resolution tips' ],
        [ 'custom-magnets-for-every-occasion-nz', __( 'Article', 'memento-magnets' ), 'wedding christmas birthday occasion' ],
    ];
    foreach ( $pages as list( $slug, $meta, $keywords ) ) {
        $page = get_page_by_path( $slug );
        if ( $page && 'publish' === $page->post_status ) {
            $items[] = [
                'title'    => get_the_title( $page ),
                'url'      => get_permalink( $page ),
                'type'     => 'page',
                'meta'     => $meta,
                'keywords' => $keywords,
                'count'    => 0,
            ];
        }
    }

    set_transient( 'memento_search_index', $items, 12 * HOUR_IN_SECONDS );
    return $items;
}

add_action( 'save_post', function ( $post_id, $post ) {
    if ( in_array( $post->post_type, [ 'product', 'page', 'post' ], true ) ) {
        delete_transient( 'memento_search_index' );
    }
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function () {
    wp_localize_script( 'memento-theme', 'mementoSearch', [
        'items'   => memento_search_index(),
        'strings' => [
            'products'   => __( 'Products', 'memento-magnets' ),
            'pages'      => __( 'Pages & articles', 'memento-magnets' ),
            /* translators: %s: search terms */
            'seeAll'     => __( 'See all results for “%s”', 'memento-magnets' ),
            'noMatches'  => __( 'No quick matches — press Enter to search everything.', 'memento-magnets' ),
            /* translators: %d: number of suggestions */
            'resultsFor' => __( '%d suggestions available. Use up and down arrows to browse.', 'memento-magnets' ),
        ],
    ] );
}, 20 );

/* ── Results: only useful content ───────────────────────────── */

add_action( 'pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
        return;
    }
    $exclude = [];
    if ( function_exists( 'wc_get_page_id' ) ) {
        foreach ( [ 'cart', 'checkout', 'myaccount', 'shop' ] as $page ) {
            $id = wc_get_page_id( $page );
            if ( $id > 0 ) {
                $exclude[] = $id;
            }
        }
    }
    $query->set( 'post__not_in', array_merge( (array) $query->get( 'post__not_in' ), $exclude ) );
    $query->set( 'post_type', [ 'product', 'post', 'page' ] );
    $query->set( 'posts_per_page', 24 );
} );
