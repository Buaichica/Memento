<?php
/**
 * Search results.
 * Products show as the shared product cards; pages and articles as a list.
 * Cart/checkout/account pages are excluded in inc/search.php.
 *
 * @package memento-magnets
 */

get_header();

$memento_query    = get_search_query();
$memento_products = [];
$memento_pages    = [];

while ( have_posts() ) {
    the_post();
    if ( 'product' === get_post_type() && function_exists( 'wc_get_product' ) ) {
        $product = wc_get_product( get_the_ID() );
        if ( $product && $product->is_visible() ) {
            $memento_products[] = memento_product_card_data( $product );
        }
    } else {
        $memento_pages[] = [
            'url'     => get_permalink(),
            'title'   => get_the_title(),
            // Blog articles are stored as pages using the page-blog-*.php templates.
            'type'    => ( 'post' === get_post_type() || 0 === strpos( (string) get_page_template_slug(), 'page-blog-' ) ) ? __( 'Article', 'memento-magnets' ) : __( 'Page', 'memento-magnets' ),
            'excerpt' => wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 24, '…' ),
        ];
    }
}
$memento_total = count( $memento_products ) + count( $memento_pages );
?>

<main id="main" class="site-main" role="main">

    <div class="page-hero">
        <div class="container">
            <h1>
                <?php
                if ( '' !== $memento_query ) {
                    /* translators: %s: search terms */
                    printf( esc_html__( 'Results for “%s”', 'memento-magnets' ), esc_html( $memento_query ) );
                } else {
                    esc_html_e( 'Search', 'memento-magnets' );
                }
                ?>
            </h1>
            <p>
                <?php
                /* translators: %d: number of results */
                echo esc_html( sprintf( _n( '%d result', '%d results', $memento_total, 'memento-magnets' ), $memento_total ) );
                ?>
            </p>
        </div>
    </div>

    <section class="section search-results">
        <div class="container">

            <div class="search-results__form"><?php get_search_form(); ?></div>

            <?php if ( $memento_products ) : ?>
                <h2 class="search-results__heading"><?php esc_html_e( 'Products', 'memento-magnets' ); ?></h2>
                <div class="mm-card-grid">
                    <?php foreach ( $memento_products as $card ) { memento_render_product_card( $card ); } ?>
                </div>
            <?php endif; ?>

            <?php if ( $memento_pages ) : ?>
                <h2 class="search-results__heading"><?php esc_html_e( 'Pages & articles', 'memento-magnets' ); ?></h2>
                <ul class="search-results__list">
                    <?php foreach ( $memento_pages as $item ) : ?>
                        <li>
                            <a class="search-results__item" href="<?php echo esc_url( $item['url'] ); ?>">
                                <span class="search-results__type"><?php echo esc_html( $item['type'] ); ?></span>
                                <span class="search-results__title"><?php echo esc_html( $item['title'] ); ?></span>
                                <?php if ( $item['excerpt'] ) : ?>
                                    <span class="search-results__excerpt"><?php echo esc_html( $item['excerpt'] ); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( ! $memento_total ) : ?>
                <div class="search-empty">
                    <h2><?php esc_html_e( 'No results this time', 'memento-magnets' ); ?></h2>
                    <p><?php esc_html_e( 'Try a different word, check the spelling, or start from one of our magnet packs below.', 'memento-magnets' ); ?></p>
                </div>
                <?php if ( function_exists( 'memento_product_card_data' ) && class_exists( 'WooCommerce' ) ) : ?>
                    <div class="mm-card-grid">
                        <?php
                        $memento_suggest = new WP_Query( [
                            'post_type'         => 'product',
                            'post_status'       => 'publish',
                            'posts_per_page'    => 4,
                            'memento_pack_sort' => true,
                            'no_found_rows'     => true,
                        ] );
                        foreach ( $memento_suggest->posts as $post_obj ) {
                            $product = wc_get_product( $post_obj );
                            if ( $product && $product->is_visible() ) {
                                memento_render_product_card( memento_product_card_data( $product ) );
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php the_posts_pagination( [ 'prev_text' => '&larr;', 'next_text' => '&rarr;' ] ); ?>

        </div>
    </section>

</main>

<?php get_footer(); ?>
