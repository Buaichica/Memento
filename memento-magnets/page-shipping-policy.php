<?php
/**
 * Template Name: Shipping Policy
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Shipping Policy', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'Last updated: March 2026', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Shipping Policy', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="container" style="max-width:860px;">
            <div class="post-content">

                <h2><?php _e( 'Where We Ship', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Memento Magnets ships within New Zealand only. We currently do not offer international shipping.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Production Time', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'All magnets are custom-made to order. Please allow 2–4 business days for production before your order is dispatched.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Delivery Timeframes', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Once dispatched, estimated delivery times are:', 'memento-magnets' ); ?></p>
                <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:1rem;color:var(--color-dark-grey);">
                    <li><?php _e( 'North Island: 1–3 business days', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'South Island: 2–5 business days', 'memento-magnets' ); ?></li>
                    <li><?php _e( 'Rural addresses: Add 1–2 additional business days', 'memento-magnets' ); ?></li>
                </ul>
                <p><?php _e( 'These are estimates only and are not guaranteed. Delays may occur during peak periods (e.g. Christmas, Valentine\'s Day).', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Shipping Rates', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Standard shipping anywhere in New Zealand is a flat $7.99 NZD. Orders over $50 NZD ship free.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Tracking', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Once your order is dispatched, you will receive a shipping confirmation email with a tracking number so you can follow your parcel.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Delivery Address', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Please ensure your delivery address is correct at checkout. Memento Magnets is not responsible for orders delivered to an incorrect address provided by the customer.', 'memento-magnets' ); ?></p>
                <p><?php _e( 'If a parcel is returned to us due to an incorrect or incomplete address, we will contact you to arrange re-delivery. Additional shipping charges may apply.', 'memento-magnets' ); ?></p>

                <h2><?php _e( 'Lost or Missing Parcels', 'memento-magnets' ); ?></h2>
                <p><?php printf( __( 'If your parcel has not arrived within the expected timeframe, please contact us at <a href="mailto:orders@mementomagnets.com">orders@mementomagnets.com</a>. We will investigate with our courier and work to resolve the issue as quickly as possible.', 'memento-magnets' ) ); ?></p>

                <h2><?php _e( 'Contact', 'memento-magnets' ); ?></h2>
                <p><?php printf( __( 'For shipping enquiries, email us at <a href="mailto:orders@mementomagnets.com">orders@mementomagnets.com</a>.', 'memento-magnets' ) ); ?></p>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
