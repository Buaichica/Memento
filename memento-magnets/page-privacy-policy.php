<?php
/**
 * Template Name: Privacy Policy
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Privacy Policy', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'Last updated: March 2026', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Privacy Policy', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container" style="max-width:860px;">
            <div class="post-content">

                <p><?php _e( 'At Memento Magnets, we are committed to protecting your personal information in accordance with the New Zealand Privacy Act 2020.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'What Information We Collect', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'When you place an order or contact us, we may collect:', 'memento-magnets' ); ?></p>
                <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:1rem;color:var(--color-dark-grey);">
                    <li><?php _e( 'Your name and email address', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Your delivery address', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Payment information (processed securely — we do not store card details)', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Photos you upload for your magnet order', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Any messages or communications you send us', 'memento-magnets' ); ?></li>
                </ul>

                <h2><?php _e( 'How We Use Your Information', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'We use your information to:', 'memento-magnets' ); ?></p>
                <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:1rem;color:var(--color-dark-grey);">
                    <li><?php _e( 'Process and fulfil your orders', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Send order confirmations and shipping updates', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Respond to your enquiries', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Send occasional marketing emails (only if you opt in — you can unsubscribe anytime)', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Improve our products and website', 'memento-magnets' ); ?></li>
                </ul>
                <p><?php _e( 'We do not sell, rent, or share your personal information with third parties except where required to fulfil your order (e.g. our printing or courier partners) or where required by New Zealand law.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Your Photos', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Photos you upload are used solely to produce your magnet order. We do not use your photos for marketing, social media, or any other purpose without your explicit written consent.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Data Retention', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'We retain your order information for up to 7 years as required by New Zealand tax law. You may request deletion of your personal data at any time by contacting us.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Your Rights', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Under the New Zealand Privacy Act 2020, you have the right to:', 'memento-magnets' ); ?></p>
                <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:1rem;color:var(--color-dark-grey);">
                    <li><?php _e( 'Access the personal information we hold about you', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Request correction of inaccurate information', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Ask us to delete your information (subject to legal obligations)', 'memento-magnets' ); ?></li>
                </ul>
                <p><?php printf( __( 'To exercise any of these rights, email us at <a href="mailto:orders@mementomagnets.com">orders@mementomagnets.com</a>.', 'memento-magnets' ) ); ?></p>

                <h2><?php _e( 'Cookies', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Our website uses cookies to improve your browsing experience and remember your cart. You can disable cookies in your browser settings, though some site features may not work as expected.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Contact', 'memento-magnets' ); ?></h2>
                <p><?php printf( __( 'If you have any questions about this Privacy Policy or how we handle your data, please contact us at <a href="mailto:orders@mementomagnets.com">orders@mementomagnets.com</a>.', 'memento-magnets' ) ); ?></p>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
