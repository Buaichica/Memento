<?php
/**
 * Template Part: Hero
 * Full-width gradient hero for the homepage.
 */
?>
<section class="hero" aria-label="<?php esc_attr_e( 'Welcome to Memento Magnets', 'memento-magnets' ); ?>">

    <!-- Floating decorators -->
    <span class="hero-decorator" aria-hidden="true">✦</span>
    <span class="hero-decorator" aria-hidden="true">✧</span>
    <span class="hero-decorator" aria-hidden="true">✦</span>

    <div class="container">
        <div class="hero-content">

            <span class="hero-sparkle" aria-hidden="true">✦ Made with love, just for you ✦</span>

            <h1 class="hero-title">
                <span><?php _e( 'Capture Every Moment.', 'memento-magnets' ); ?></span>
                <?php _e( 'Keep It Forever.', 'memento-magnets' ); ?>
            </h1>

            <p class="hero-subtitle">
                <?php _e( 'Personalised fridge magnets custom-made from your photos. Perfect for gifts, homes &amp; special occasions. Shipping across New Zealand.', 'memento-magnets' ); ?>
            </p>

            <div class="hero-ctas">
                <a href="<?php echo esc_url( home_url( '/custom-magnets/' ) ); ?>" class="btn btn--outline-white btn--lg">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" aria-hidden="true">
                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                    </svg>
                    <?php _e( 'Shop Now', 'memento-magnets' ); ?>
                </a>
                <a href="<?php echo esc_url( home_url( '#how-to-order' ) ); ?>" class="btn btn--secondary btn--lg">
                    <?php _e( 'How To Order', 'memento-magnets' ); ?>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </a>
            </div>

            <div class="hero-badges" role="list">
                <div class="hero-badge" role="listitem">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M5 12l5 5L20 7"/>
                    </svg>
                    <?php _e( 'Premium Quality', 'memento-magnets' ); ?>
                </div>
                <div class="hero-badge" role="listitem">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                    <?php _e( 'NZ Fast Delivery', 'memento-magnets' ); ?>
                </div>
                <div class="hero-badge" role="listitem">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <?php _e( 'Loved by customers', 'memento-magnets' ); ?>
                </div>
            </div>

        </div>
    </div>

</section>
