<?php
/**
 * Template Name: FAQ Page
 *
 * @package memento-magnets
 */

get_header();

// FAQ data — structured for both display and JSON-LD schema
$faq_groups = [
    [
        'group' => __( 'Ordering', 'memento-magnets' ),
        'items' => [
            [
                'q' => __( 'How do I place an order?', 'memento-magnets' ),
                'a' => __( 'Simply browse our products, select the size and quantity you want, upload your photo, and add to cart. You\'ll receive an order confirmation email once your payment is processed.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'What photo formats do you accept?', 'memento-magnets' ),
                'a' => __( 'We accept JPG, PNG, and HEIC formats. For the best quality results, we recommend uploading a high-resolution image (at least 800x800 pixels). Avoid heavily cropped or digitally zoomed photos.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'Can I preview my magnet before ordering?', 'memento-magnets' ),
                'a' => __( 'Yes! After uploading your photo, you\'ll see a preview of your magnet before adding it to cart. If you need adjustments, just re-upload an edited image.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'What payment methods do you accept?', 'memento-magnets' ),
                'a' => __( 'We accept Visa, Mastercard, PayPal, and Apple Pay. All transactions are secured with SSL encryption.', 'memento-magnets' ),
            ],
        ],
    ],
    [
        'group' => __( 'Shipping', 'memento-magnets' ),
        'items' => [
            [
                'q' => __( 'How long does delivery take?', 'memento-magnets' ),
                'a' => __( 'New Zealand orders typically arrive within 3–5 business days. Australian orders take 5–10 business days. Express options are available at checkout for faster delivery.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'Do you ship internationally outside NZ and AU?', 'memento-magnets' ),
                'a' => __( 'Currently we ship to New Zealand and Australia only. We\'re working on expanding internationally — sign up to our newsletter to be notified when this changes!', 'memento-magnets' ),
            ],
            [
                'q' => __( 'How much does shipping cost?', 'memento-magnets' ),
                'a' => __( 'Shipping is calculated at checkout based on your location and order size. We offer free standard shipping on New Zealand orders over $50 NZD. Flat-rate shipping options are available for Australia.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'Will I receive tracking information?', 'memento-magnets' ),
                'a' => __( 'Yes, once your order is dispatched you\'ll receive a shipping confirmation email with your tracking number so you can follow your parcel\'s journey.', 'memento-magnets' ),
            ],
        ],
    ],
    [
        'group' => __( 'Products', 'memento-magnets' ),
        'items' => [
            [
                'q' => __( 'What sizes are available?', 'memento-magnets' ),
                'a' => __( 'We offer a range of sizes including 5x5cm, 7x7cm, 10x10cm, and our popular 10x15cm photo strip. Custom sizes may be available — contact us for details.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'What are the magnets made of?', 'memento-magnets' ),
                'a' => __( 'Our magnets feature a high-quality glossy or matte printed surface laminated onto a flexible magnetic backing. They\'re durable, water-resistant, and built to last on your fridge for years.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'Are the magnets safe for children?', 'memento-magnets' ),
                'a' => __( 'Our magnets are suitable for general household use but are not recommended as toys for children under 3. The magnetic backing is flexible, not a hard choking hazard, but as with all small items, keep them out of reach of very young children.', 'memento-magnets' ),
            ],
        ],
    ],
    [
        'group' => __( 'Returns & Refunds', 'memento-magnets' ),
        'items' => [
            [
                'q' => __( 'What if I\'m not happy with my order?', 'memento-magnets' ),
                'a' => __( 'We want you to love your magnets! If there\'s a quality issue or we made an error, we\'ll reprint or refund your order — no questions asked. Please email us at hello@mementomagnets.co.nz with a photo of the issue within 14 days of receiving your order.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'Can I return my order if I change my mind?', 'memento-magnets' ),
                'a' => __( 'Because every order is custom-made to your specifications, we\'re unable to accept returns for change of mind. However, if there\'s a defect or error on our part, we\'ll always make it right.', 'memento-magnets' ),
            ],
            [
                'q' => __( 'My order arrived damaged — what should I do?', 'memento-magnets' ),
                'a' => __( 'We\'re sorry to hear that! Please take a photo of the damaged packaging and product, then email us at hello@mementomagnets.co.nz. We\'ll arrange a reprint or refund as quickly as possible.', 'memento-magnets' ),
            ],
        ],
    ],
];
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Frequently Asked Questions', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'Everything you need to know about ordering your personalised magnets.', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'FAQ', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <!-- FAQ Content -->
    <section class="faq-page">
        <div class="container" style="max-width:860px;">

            <div class="faq-groups">
                <?php foreach ( $faq_groups as $group ) : ?>
                <div class="faq-group">
                    <h3><?php echo esc_html( $group['group'] ); ?></h3>
                    <?php foreach ( $group['items'] as $item ) : ?>
                    <div class="faq-item">
                        <button class="faq-question" type="button">
                            <span><?php echo esc_html( $item['q'] ); ?></span>
                            <span class="faq-icon" aria-hidden="true">+</span>
                        </button>
                        <div class="faq-answer">
                            <p><?php echo esc_html( $item['a'] ); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align:center;margin-top:var(--space-12);padding:var(--space-8);background:var(--color-cream);border-radius:var(--radius-xl);">
                <h3 style="margin-bottom:var(--space-3);"><?php _e( 'Still have questions?', 'memento-magnets' ); ?></h3>
                <p style="margin-bottom:var(--space-5);color:var(--color-mid-grey);">
                    <?php _e( 'Can\'t find the answer you\'re looking for? Our friendly team is happy to help.', 'memento-magnets' ); ?>
                </p>
                <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn--primary">
                    <?php _e( 'Contact Us', 'memento-magnets' ); ?>
                </a>
            </div>

        </div>
    </section>

</main>

<?php
// ── FAQPage JSON-LD ──────────────────────────────────────────────
$faq_schema_items = [];
foreach ( $faq_groups as $group ) {
    foreach ( $group['items'] as $item ) {
        $faq_schema_items[] = [
            '@type'          => 'Question',
            'name'           => $item['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $item['a'],
            ],
        ];
    }
}
$faq_schema = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => $faq_schema_items,
];
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $faq_schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
</script>

<?php get_footer(); ?>
