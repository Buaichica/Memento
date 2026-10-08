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
                <div class="contact-form-wrap" id="contact-form">
                    <h2><?php _e( 'Send Us a Message', 'memento-magnets' ); ?></h2>
                    <p style="color:var(--color-mid-grey);margin-bottom:var(--space-6);">
                        <?php _e( 'Fill in the form below and we\'ll get back to you within 1 business day.', 'memento-magnets' ); ?>
                    </p>

                    <?php
                    $cf7_id = memento_contact_cf7_id();
                    $state  = memento_contact_state();
                    $errors = $state['errors'];
                    $user   = wp_get_current_user();
                    $val    = function ( $key ) use ( $state, $user ) {
                        if ( isset( $state['values'][ $key ] ) ) {
                            return $state['values'][ $key ];
                        }
                        // Prefill for logged-in customers.
                        if ( 'name' === $key && $user->exists() ) {
                            return trim( $user->first_name . ' ' . $user->last_name ) ?: $user->display_name;
                        }
                        if ( 'email' === $key && $user->exists() ) {
                            return $user->user_email;
                        }
                        return '';
                    };
                    $field_error = function ( $key ) use ( $errors ) {
                        if ( ! empty( $errors[ $key ] ) ) {
                            echo '<p class="form-error" id="contact-' . esc_attr( $key ) . '-error">' . esc_html( $errors[ $key ] ) . '</p>';
                        }
                    };
                    $invalid = function ( $key ) use ( $errors ) {
                        if ( ! empty( $errors[ $key ] ) ) {
                            echo ' aria-invalid="true" aria-describedby="contact-' . esc_attr( $key ) . '-error"';
                        }
                    };
                    ?>

                    <?php if ( $cf7_id ) : ?>
                        <?php echo do_shortcode( '[contact-form-7 id="' . (int) $cf7_id . '"]' ); ?>
                    <?php else : ?>

                        <?php if ( isset( $_GET['sent'] ) && ! $errors ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
                            <div class="woocommerce-message contact-notice" role="status" tabindex="-1">
                                <span><strong><?php _e( 'Thanks — your message is on its way!', 'memento-magnets' ); ?></strong>
                                <?php _e( 'We\'ll reply by email within 1 business day.', 'memento-magnets' ); ?></span>
                            </div>
                        <?php elseif ( $errors ) : ?>
                            <div class="woocommerce-error contact-notice" role="alert" tabindex="-1">
                                <?php
                                echo esc_html(
                                    $errors['form'] ?? __( 'Please check the highlighted fields below.', 'memento-magnets' )
                                );
                                if ( $state['failed'] ) {
                                    echo ' <a href="mailto:orders@mementomagnets.com">orders@mementomagnets.com</a>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <form class="wpcf7-form native-contact-form" action="<?php echo esc_url( get_permalink() ); ?>#contact-form" method="post" novalidate>
                            <?php wp_nonce_field( 'contact_form_submit', 'contact_nonce' ); ?>

                            <!-- Spam trap: hidden from people, bots fill it in -->
                            <div class="contact-hp" aria-hidden="true">
                                <label for="contact-website"><?php _e( 'Leave this field empty', 'memento-magnets' ); ?></label>
                                <input type="text" id="contact-website" name="contact_website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact-name"><?php _e( 'Your Name *', 'memento-magnets' ); ?></label>
                                    <input type="text" id="contact-name" name="contact_name" required maxlength="100" autocomplete="name"
                                           value="<?php echo esc_attr( $val( 'name' ) ); ?>" placeholder="<?php esc_attr_e( 'Jane Smith', 'memento-magnets' ); ?>"<?php $invalid( 'name' ); ?>>
                                    <?php $field_error( 'name' ); ?>
                                </div>
                                <div class="form-group">
                                    <label for="contact-email"><?php _e( 'Email Address *', 'memento-magnets' ); ?></label>
                                    <input type="email" id="contact-email" name="contact_email" required autocomplete="email"
                                           value="<?php echo esc_attr( $val( 'email' ) ); ?>" placeholder="<?php esc_attr_e( 'jane@example.com', 'memento-magnets' ); ?>"<?php $invalid( 'email' ); ?>>
                                    <?php $field_error( 'email' ); ?>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact-subject"><?php _e( 'Topic *', 'memento-magnets' ); ?></label>
                                    <select id="contact-subject" name="contact_subject" required<?php $invalid( 'subject' ); ?>>
                                        <option value=""><?php _e( 'Select a topic…', 'memento-magnets' ); ?></option>
                                        <?php foreach ( memento_contact_subjects() as $key => $label ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $val( 'subject' ), $key ); ?>><?php echo esc_html( $label ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php $field_error( 'subject' ); ?>
                                </div>
                                <div class="form-group">
                                    <label for="contact-order"><?php _e( 'Order Number (if applicable)', 'memento-magnets' ); ?></label>
                                    <input type="text" id="contact-order" name="contact_order" maxlength="40"
                                           value="<?php echo esc_attr( $val( 'order' ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. #12345', 'memento-magnets' ); ?>"<?php $invalid( 'order' ); ?>>
                                    <?php $field_error( 'order' ); ?>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="contact-message"><?php _e( 'Message *', 'memento-magnets' ); ?></label>
                                <textarea id="contact-message" name="contact_message" required minlength="10" maxlength="5000" rows="6"
                                          placeholder="<?php esc_attr_e( 'Tell us how we can help…', 'memento-magnets' ); ?>"<?php $invalid( 'message' ); ?>><?php echo esc_textarea( $val( 'message' ) ); ?></textarea>
                                <?php $field_error( 'message' ); ?>
                            </div>

                            <button type="submit" class="btn btn--primary btn--lg wpcf7-submit">
                                <?php _e( 'Send Message', 'memento-magnets' ); ?>
                            </button>
                            <p class="contact-privacy-note">
                                <?php _e( 'We only use your details to reply to your message.', 'memento-magnets' ); ?>
                            </p>
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
                                    <a href="mailto:orders@mementomagnets.com">orders@mementomagnets.com</a>
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
