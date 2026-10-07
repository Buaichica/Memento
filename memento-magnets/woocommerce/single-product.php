<?php
/**
 * Single Product template override.
 *
 * - Replaces the plain breadcrumb bar with the brand gradient page-hero,
 *   matching the style of the Products archive, Blogs, FAQ, and other inner pages.
 * - Intentionally omits get_sidebar() to prevent stale widget instances
 *   (Search, Pages, Archives, Categories) from rendering below the product.
 *
 * @package memento-magnets
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

// Gather product info for the hero.
$product_title    = get_the_title();
$product_cats     = wc_get_product_terms( get_the_ID(), 'product_cat', [ 'fields' => 'names' ] );
$product_cats     = array_filter( $product_cats, fn( $cat ) => strtolower( $cat ) !== 'uncategorized' && strtolower( $cat ) !== 'uncategorised' );
$product_subtitle = ! empty( $product_cats ) ? implode( ', ', $product_cats ) : '';
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero — gradient banner matching other inner pages -->
    <div class="page-hero">
        <div class="container">

            <h1><?php echo esc_html( $product_title ); ?></h1>

            <?php if ( $product_subtitle ) : ?>
            <p><?php echo esc_html( $product_subtitle ); ?></p>
            <?php endif; ?>

            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php _e( 'Products', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php echo esc_html( $product_title ); ?></span>
            </nav>

        </div>
    </div>

    <!-- Product content -->
    <div class="woocommerce">
        <div class="container">
            <?php
            while ( have_posts() ) {
                the_post();
                wc_get_template_part( 'content', 'single-product' );
            }
            ?>
        </div>
    </div>

</main>

<?php get_footer( 'shop' ); ?>
