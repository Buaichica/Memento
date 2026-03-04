<?php
/**
 * Template Name: Contact Page
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Contact Us', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'We\'d love to hear from you! Get in touch with any questions about your order.', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Contact Us', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <!-- Contact Content -->
    <section class="contact-page">
        <div class="container">
            <div class="contact-layout">

                <!-- Contact Form -->
                <div class="contact-form-wrap">
                    <h2><?php _e( 'Send Us a Message', 'memento-magnets' ); ?></h2>
                    <p style="color:var(--color-mid-grey);margin-bottom:var(--space-6);">
                        <?php _e( 'Fill in the form below and we\'ll get back to you within 1 business day.', 'memento-magnets' ); ?>
                    </p>

                    <?php if ( function_exists( 'wpcf7_contact_form' ) ) : ?>
                        <?php
                        // Contact Form 7 — replace "1" with your actual form ID
                        echo do_shortcode( '[contact-form-7 id="1" title="Contact form"]' );
                        ?>
                    <?php else : ?>
                        <!-- Native fallback form -->
                        <form class="wpcf7-form native-contact-form" action="<?php echo esc_url( home_url( '/contact/' ) ); ?>" method="post">
                            <?php wp_nonce_field( 'contact_form_submit', 'contact_nonce' ); ?>

                            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);margin-bottom:var(--space-4);">
                                <div class="form-group">
                                    <label for="contact-name"><?php _e( 'Your Name *', 'memento-magnets' ); ?></label>
                                    <input type="text" id="contact-name" name="contact_name" required placeholder="<?php esc_attr_e( 'Jane Smith', 'memento-magnets' ); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="contact-email"><?php _e( 'Email Address *', 'memento-magnets' ); ?></label>
                                    <input type="email" id="contact-email" name="contact_email" required placeholder="<?php esc_attr_e( 'jane@example.com', 'memento-magnets' ); ?>">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom:var(--space-4);">
                                <label for="contact-subject"><?php _e( 'Subject *', 'memento-magnets' ); ?></label>
                                <select id="contact-subject" name="contact_subject" required>
                                    <option value=""><?php _e( 'Select a topic…', 'memento-magnets' ); ?></option>
                                    <option value="order"><?php _e( 'Order enquiry', 'memento-magnets' ); ?></option>
                                    <option value="shipping"><?php _e( 'Shipping question', 'memento-magnets' ); ?></option>
                                    <option value="return"><?php _e( 'Return / refund', 'memento-magnets' ); ?></option>
                                    <option value="custom"><?php _e( 'Custom bulk order', 'memento-magnets' ); ?></option>
                                    <option value="other"><?php _e( 'Other', 'memento-magnets' ); ?></option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom:var(--space-4);">
                                <label for="contact-order"><?php _e( 'Order Number (if applicable)', 'memento-magnets' ); ?></label>
                                <input type="text" id="contact-order" name="contact_order" placeholder="<?php esc_attr_e( 'e.g. #12345', 'memento-magnets' ); ?>">
                            </div>

                            <div class="form-group" style="margin-bottom:var(--space-6);">
                                <label for="contact-message"><?php _e( 'Message *', 'memento-magnets' ); ?></label>
                                <textarea id="contact-message" name="contact_message" required placeholder="<?php esc_attr_e( 'Tell us how we can help…', 'memento-magnets' ); ?>" rows="6"></textarea>
                            </div>

                            <button type="submit" class="btn btn--primary btn--lg wpcf7-submit">
                                <?php _e( 'Send Message', 'memento-magnets' ); ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Contact Info Sidebar -->
                <aside class="contact-info" aria-label="<?php esc_attr_e( 'Contact information', 'memento-magnets' ); ?>">
                    <div style="background:var(--color-cream);border-radius:var(--radius-xl);padding:var(--space-8);">
                        <h3><?php _e( 'Get In Touch', 'memento-magnets' ); ?></h3>

                        <ul class="contact-info-list">
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <div>
                                    <strong><?php _e( 'Email', 'memento-magnets' ); ?></strong><br>
                                    <a href="mailto:hello@mementomagnets.co.nz">hello@mementomagnets.co.nz</a>
                                </div>
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <div>
                                    <strong><?php _e( 'Response Time', 'memento-magnets' ); ?></strong><br>
                                    <?php _e( 'Within 1 business day', 'memento-magnets' ); ?>
                                </div>
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <div>
                                    <strong><?php _e( 'Service Area', 'memento-magnets' ); ?></strong><br>
                                    <?php _e( 'New Zealand', 'memento-magnets' ); ?>
                                </div>
                            </li>
                        </ul>

                        <div style="border-top:1px solid #E8D9D0;padding-top:var(--space-5);margin-top:var(--space-5);">
                            <p style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);margin-bottom:var(--space-3);">
                                <?php _e( 'Follow Us', 'memento-magnets' ); ?>
                            </p>
                            <div style="display:flex;gap:var(--space-3);">
                                <a href="https://www.instagram.com/mementomagnets" target="_blank" rel="noopener noreferrer" class="social-link" style="background:rgba(255,95,160,0.1);color:var(--color-hot-pink);" aria-label="Instagram">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" aria-hidden="true">
                                        <rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                                    </svg>
                                </a>
                                <a href="https://www.facebook.com/mementomagnets" target="_blank" rel="noopener noreferrer" class="social-link" style="background:rgba(255,95,160,0.1);color:var(--color-hot-pink);" aria-label="Facebook">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                                        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
                                    </svg>
                                </a>
                                <a href="https://www.tiktok.com/@mementomagnets" target="_blank" rel="noopener noreferrer" class="social-link" style="background:rgba(255,95,160,0.1);color:var(--color-hot-pink);" aria-label="TikTok">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true">
                                        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.28 6.28 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.54V6.78a4.85 4.85 0 01-1.01-.09z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>


                </aside>

            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
