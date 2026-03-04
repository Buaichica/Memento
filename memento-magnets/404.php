<?php
/**
 * 404 Template
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">
    <section class="page-404">
        <div class="container">
            <div style="text-align:center;">
                <div class="error-code" aria-hidden="true">404</div>
                <h2><?php _e( 'Oops! Page Not Found', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'The page you\'re looking for seems to have wandered off — much like a magnet without a fridge!', 'memento-magnets' ); ?></p>
                <div class="ctas">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary btn--lg">
                        <?php _e( 'Back to Home', 'memento-magnets' ); ?>
                    </a>
                    <a href="<?php echo esc_url( home_url( '/custom-magnets/' ) ); ?>" class="btn btn--secondary btn--lg">
                        <?php _e( 'Shop Magnets', 'memento-magnets' ); ?>
                    </a>
                </div>

                <div style="margin-top:var(--space-10);max-width:400px;margin-left:auto;margin-right:auto;">
                    <p style="font-size:var(--text-sm);color:var(--color-mid-grey);margin-bottom:var(--space-3);">
                        <?php _e( 'Or try searching for what you need:', 'memento-magnets' ); ?>
                    </p>
                    <?php get_search_form(); ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
