<?php
/**
 * Template Name: Terms of Service
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Terms of Service', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'Last updated: March 2026', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Terms of Service', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container" style="max-width:860px;">
            <div class="post-content">

                <p><?php _e( 'Welcome to Memento Magnets. By placing an order or using our website, you agree to the following Terms of Service. Please read them carefully.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '1. About Us', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Memento Magnets is a New Zealand-based business selling personalised fridge magnets. We operate exclusively within New Zealand.', 'memento-magnets' ); ?></p>
                <p><?php printf( __( 'Contact: <a href="mailto:hello@mementomagnets.co.nz">hello@mementomagnets.co.nz</a>', 'memento-magnets' ) ); ?></p>

                <h2><?php _e( '2. Orders', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'By placing an order, you confirm that:', 'memento-magnets' ); ?></p>
                <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:1rem;color:var(--color-dark-grey);">
                    <li><?php _e( 'The photo(s) you upload are owned by you or you have the right to use them', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'The information you provide (name, address, email) is accurate', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'You are at least 18 years old, or have parental permission to purchase', 'memento-magnets' ); ?></li>
                </ul>
                <p><?php _e( 'We reserve the right to cancel any order that contains offensive, illegal, or inappropriate content.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '3. Photos & Intellectual Property', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'You retain ownership of any photos you upload. By uploading a photo, you grant Memento Magnets a limited licence to use that image solely for the purpose of producing your order.', 'memento-magnets' ); ?></p>
                <p><?php _e( 'You must not upload photos that:', 'memento-magnets' ); ?></p>
                <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:1rem;color:var(--color-dark-grey);">
                    <li><?php _e( 'Depict explicit, offensive, or illegal content', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Infringe on the copyright or privacy rights of others', 'memento-magnets' ); ?></li>
                </ul>
                <p><?php _e( 'Memento Magnets takes no responsibility for any legal issues arising from photos you upload.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '4. Pricing', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'All prices are displayed in New Zealand Dollars (NZD) and include GST where applicable. We reserve the right to change prices at any time. The price at the time of your order is the price you will be charged.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '5. Payment', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Payment is required in full at the time of placing your order. We accept credit and debit cards, including Visa and Mastercard, and Afterpay, processed securely by our payment provider Stripe. We do not store your card details.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '6. Production & Delivery', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'We aim to produce and dispatch orders within 2–4 business days. Delivery timeframes are estimates only. Memento Magnets is not liable for delays caused by courier services, rural delivery, or events outside our control.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '7. Refunds & Returns', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'As our products are custom-made, we do not accept returns or offer refunds for change of mind. If your order is faulty or damaged, please refer to our Refund Policy.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '8. Limitation of Liability', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'To the maximum extent permitted by New Zealand law, Memento Magnets will not be liable for any indirect, incidental, or consequential loss or damage arising from your use of our website or products.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '9. Your Consumer Rights', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Nothing in these Terms limits your rights under the New Zealand Consumer Guarantees Act 1993 or the Fair Trading Act 1986.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '10. Privacy', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Your personal information is handled in accordance with our Privacy Policy and the New Zealand Privacy Act 2020.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '11. Changes to These Terms', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'We may update these Terms of Service from time to time. The latest version will always be available on our website. Continued use of our website after changes are posted constitutes acceptance of the updated terms.', 'memento-magnets' ); ?></p>

                <h2><?php _e( '12. Governing Law', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'These Terms are governed by the laws of New Zealand. Any disputes will be subject to the jurisdiction of the New Zealand courts.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Contact', 'memento-magnets' ); ?></h2>
                <p><?php printf( __( 'For any questions about these Terms, please contact us at <a href="mailto:hello@mementomagnets.co.nz">hello@mementomagnets.co.nz</a>.', 'memento-magnets' ) ); ?></p>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
