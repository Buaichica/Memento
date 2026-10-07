<?php
/**
 * WooCommerce Product Archive Template Override
 * Adds the brand gradient page-hero (matching Blogs) above the product grid.
 *
 * @package memento-magnets
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero (matches Blogs page style) -->
    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Products', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'Personalised fridge magnets made from your favourite photos.', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Products', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <!-- Product Grid -->
    <div class="memento-products-wrap">

        <?php if ( woocommerce_product_loop() ) : ?>

            <?php do_action( 'woocommerce_before_shop_loop' ); ?>

            <!-- Same cards as the homepage collection (inc/product-cards.php) -->
            <div class="mm-card-grid">
                <?php if ( wc_get_loop_prop( 'total' ) ) : ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php do_action( 'woocommerce_shop_loop' ); ?>
                        <?php
                        $product = wc_get_product( get_the_ID() );
                        if ( $product && $product->is_visible() ) {
                            memento_render_product_card( memento_product_card_data( $product ) );
                        }
                        ?>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <?php do_action( 'woocommerce_after_shop_loop' ); ?>

        <?php else : ?>

            <?php do_action( 'woocommerce_no_products_found' ); ?>

        <?php endif; ?>

    </div><!-- .memento-products-wrap -->

</main>

<?php get_footer(); ?>
