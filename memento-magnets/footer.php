<!-- Site Footer -->
<footer class="site-footer" role="contentinfo">
    <div class="container">

        <!-- Footer Grid -->
        <div class="footer-grid">

            <!-- Column 1: Brand -->
            <div class="footer-col footer-col--brand">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo('name'); ?> home">
                    <span class="footer-logo-text">Memento Magnets</span>
                </a>
                <p class="footer-tagline">Capture Every Moment. Keep It Forever.</p>
                <p style="font-size:var(--text-sm);color:rgba(255,255,255,0.55);margin-bottom:var(--space-5);line-height:1.6;">
                    <?php _e( 'Personalised fridge magnets custom-made with love, delivering across New Zealand and Australia.', 'memento-magnets' ); ?>
                </p>

                <!-- Social icons -->
                <nav class="social-links" aria-label="<?php esc_attr_e( 'Social media', 'memento-magnets' ); ?>">
                    <a href="https://www.facebook.com/mementomagnets" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/mementomagnets" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                        </svg>
                    </a>
                    <a href="https://www.tiktok.com/@mementomagnets" class="social-link" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.28 6.28 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.54V6.78a4.85 4.85 0 01-1.01-.09z"/>
                        </svg>
                    </a>
                </nav>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col">
                <h4><?php _e( 'Quick Links', 'memento-magnets' ); ?></h4>
                <nav aria-label="<?php esc_attr_e( 'Footer quick links', 'memento-magnets' ); ?>">
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'footer',
                        'menu_class'     => 'footer-links',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => 'memento_footer_nav_fallback',
                    ] );
                    ?>
                </nav>
            </div>

            <!-- Column 3: Policies -->
            <div class="footer-col">
                <h4><?php _e( 'Policies', 'memento-magnets' ); ?></h4>
                <nav aria-label="<?php esc_attr_e( 'Policy links', 'memento-magnets' ); ?>">
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'policies',
                        'menu_class'     => 'footer-links',
                        'container'      => false,
                        'depth'          => 1,
                        'fallback_cb'    => 'memento_policy_nav_fallback',
                    ] );
                    ?>
                </nav>
            </div>

            <!-- Column 4: Contact -->
            <div class="footer-col">
                <h4><?php _e( 'Get In Touch', 'memento-magnets' ); ?></h4>
                <ul class="footer-contact-info">
                    <li class="footer-contact-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <a href="mailto:hello@mementomagnets.co.nz" style="color:rgba(255,255,255,0.65);">hello@mementomagnets.co.nz</a>
                    </li>
                    <li class="footer-contact-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span><?php _e( 'Serving New Zealand Only', 'memento-magnets' ); ?></span>
                    </li>
                </ul>

                <p style="font-size:var(--text-sm);font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:var(--space-3);">
                    <?php _e( 'Stay in the loop', 'memento-magnets' ); ?>
                </p>
                <form class="footer-newsletter-mini" action="#" method="post" aria-label="<?php esc_attr_e( 'Newsletter signup', 'memento-magnets' ); ?>">
                    <?php wp_nonce_field( 'newsletter_signup', 'newsletter_nonce' ); ?>
                    <input type="email" name="email" placeholder="<?php esc_attr_e( 'Your email', 'memento-magnets' ); ?>" required aria-label="<?php esc_attr_e( 'Email address', 'memento-magnets' ); ?>">
                    <button type="submit"><?php _e( 'Go', 'memento-magnets' ); ?></button>
                </form>
            </div>

        </div><!-- .footer-grid -->

        <!-- Footer Bottom Bar -->
        <div class="footer-bottom">
            <p class="footer-copyright">
                &copy; <?php echo date( 'Y' ); ?> Memento Magnets. <?php _e( 'All rights reserved.', 'memento-magnets' ); ?>
            </p>

            <div class="payment-icons" aria-label="<?php esc_attr_e( 'Accepted payment methods', 'memento-magnets' ); ?>">
                <span class="payment-icon">VISA</span>
                <span class="payment-icon">MC</span>
                <span class="payment-icon">PayPal</span>
                <span class="payment-icon">⌘ Pay</span>
            </div>
        </div>

    </div><!-- .container -->
</footer>

<!-- Scroll to Top -->
<button class="scroll-to-top" id="scrollToTop" aria-label="<?php esc_attr_e( 'Scroll to top', 'memento-magnets' ); ?>" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <polyline points="18 15 12 9 6 15"/>
    </svg>
</button>

<?php
// ── LocalBusiness JSON-LD ────────────────────────────────────────
$local_business = memento_local_business_schema();
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $local_business, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ); ?>
</script>

<?php
// ── Breadcrumb JSON-LD ───────────────────────────────────────────
$breadcrumb = memento_breadcrumb_schema();
if ( $breadcrumb ) :
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $breadcrumb, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ); ?>
</script>
<?php endif; ?>

<?php
// ── BlogPosting JSON-LD (single posts) ──────────────────────────
if ( is_singular( 'post' ) ) :
    $post_schema = [
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'headline'         => get_the_title(),
        'description'      => wp_trim_words( get_the_excerpt(), 30 ),
        'datePublished'    => get_the_date( 'c' ),
        'dateModified'     => get_the_modified_date( 'c' ),
        'author'           => [
            '@type' => 'Organization',
            'name'  => 'Memento Magnets',
            'url'   => get_site_url(),
        ],
        'publisher'        => [
            '@type' => 'Organization',
            'name'  => 'Memento Magnets',
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => MEMENTO_URI . '/assets/img/logo.png',
            ],
        ],
        'url'              => get_permalink(),
        'mainEntityOfPage' => get_permalink(),
    ];
    if ( has_post_thumbnail() ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'memento-hero' );
        if ( $img ) $post_schema['image'] = $img[0];
    }
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $post_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ); ?>
</script>
<?php endif; ?>

<?php wp_footer(); ?>

<?php
/**
 * Footer nav fallbacks
 */
function memento_footer_nav_fallback() {
    ?>
    <ul class="footer-links">
        <li><a href="<?php echo home_url('/'); ?>">Home</a></li>
        <li><a href="<?php echo home_url('/custom-magnets/'); ?>">Custom Magnets</a></li>
        <li><a href="<?php echo home_url('/blogs/'); ?>">Blogs</a></li>
        <li><a href="<?php echo home_url('/faq/'); ?>">FAQ</a></li>
        <li><a href="<?php echo home_url('/contact/'); ?>">Contact Us</a></li>
    </ul>
    <?php
}

function memento_policy_nav_fallback() {
    ?>
    <ul class="footer-links">
        <li><a href="<?php echo home_url('/privacy-policy/'); ?>">Privacy Policy</a></li>
        <li><a href="<?php echo home_url('/refund-policy/'); ?>">Refund Policy</a></li>
        <li><a href="<?php echo home_url('/terms-of-service/'); ?>">Terms of Service</a></li>
        <li><a href="<?php echo home_url('/shipping-policy/'); ?>">Shipping Policy</a></li>
    </ul>
    <?php
}
?>
</body>
</html>
