<?php
/**
 * Generic Page Template
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <h1><?php the_title(); ?></h1>
            <?php if ( has_excerpt() ) : ?>
            <p><?php the_excerpt(); ?></p>
            <?php endif; ?>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php the_title(); ?></span>
            </nav>
        </div>
    </div>

    <article id="post-<?php the_ID(); ?>" <?php post_class( 'section' ); ?>>
        <div class="container" style="max-width:860px;">
            <div class="post-content">
                <?php the_content(); ?>
            </div>

            <?php
            wp_link_pages( [
                'before' => '<div class="page-links"><strong>' . __( 'Pages:', 'memento-magnets' ) . '</strong>',
                'after'  => '</div>',
            ] );
            ?>
        </div>
    </article>

    <?php endwhile; ?>

    <?php
    // WooCommerce shop page — inherit WC templates
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        woocommerce_content();
    }
    ?>

</main>

<?php get_footer(); ?>
