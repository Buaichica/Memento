<?php
/**
 * Template Part: Newsletter CTA
 * Email signup section with gradient background.
 */
?>
<section class="newsletter-section" aria-labelledby="newsletter-heading">
    <div class="container">
        <div class="newsletter-inner">
            <h2 id="newsletter-heading"><?php _e( 'Get 10% Off Your First Order', 'memento-magnets' ); ?></h2>
            <p>
                <?php _e( 'Subscribe for exclusive deals, new product launches, and magnet inspo delivered to your inbox.', 'memento-magnets' ); ?>
            </p>

            <form class="newsletter-form" action="#" method="post" aria-label="<?php esc_attr_e( 'Newsletter signup', 'memento-magnets' ); ?>">
                <?php wp_nonce_field( 'newsletter_signup', 'newsletter_nonce' ); ?>
                <input
                    type="email"
                    name="email"
                    placeholder="<?php esc_attr_e( 'Enter your email address', 'memento-magnets' ); ?>"
                    required
                    aria-label="<?php esc_attr_e( 'Email address', 'memento-magnets' ); ?>"
                    autocomplete="email"
                >
                <button type="submit"><?php _e( 'Get 10% Off', 'memento-magnets' ); ?></button>
            </form>

        </div>
    </div>
</section>
