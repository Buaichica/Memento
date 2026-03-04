<?php
/**
 * Template Name: Refund Policy
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Refund Policy', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'Last updated: March 2026', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Refund Policy', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container" style="max-width:860px;">
            <div class="post-content">

                <p><?php _e( 'At Memento Magnets, we want you to love your order. Please read our refund policy carefully before placing an order.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Custom & Personalised Products', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Because every Memento Magnet is custom-made to your specifications using photos you provide, we are unable to accept returns or offer refunds for change of mind. This is standard practice for personalised products.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Faulty or Damaged Items', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'If your order arrives damaged, defective, or significantly different from what you ordered, we will gladly offer you a free reprint or a full refund — no questions asked.', 'memento-magnets' ); ?></p>
                <p><?php _e( 'To be eligible, please:', 'memento-magnets' ); ?></p>
                <ol style="list-style:decimal;padding-left:1.5rem;margin-bottom:1rem;color:var(--color-dark-grey);">
                    <li><?php printf( __( 'Email us at <a href="mailto:hello@mementomagnets.co.nz">hello@mementomagnets.co.nz</a> within 14 days of receiving your order', 'memento-magnets' ) ); ?></li>
                    <li><?php _e( 'Include your order number and a clear photo of the issue', 'memento-magnets' ); ?></li>
                </ol>
                <p><?php _e( 'We will assess your claim and respond within 1–2 business days with a resolution.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Incorrect Photo or Order Error (Your Mistake)', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'If you uploaded the wrong photo or entered incorrect order details, we may not be able to offer a refund, as production will have already begun. However, please contact us as soon as possible — if we haven\'t started printing yet, we\'ll do our best to accommodate the change.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Your Rights Under New Zealand Law', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Our policy operates alongside your rights under the New Zealand Consumer Guarantees Act 1993 and the Fair Trading Act 1986. Nothing in this policy limits or excludes those rights.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'How Refunds Are Processed', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Approved refunds are returned to your original payment method within 5–10 business days, depending on your bank or payment provider.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Contact', 'memento-magnets' ); ?></h2>
                <p><?php printf( __( 'For any refund or return enquiries, please contact us at <a href="mailto:hello@mementomagnets.co.nz">hello@mementomagnets.co.nz</a>.', 'memento-magnets' ) ); ?></p>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
