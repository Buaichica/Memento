<?php
/**
 * Template Part: How To Order
 * 3-step process section.
 */
?>
<section class="section how-to-order" id="how-to-order" aria-labelledby="how-to-order-heading">
    <div class="container">

        <div class="section-heading">
            <h2 id="how-to-order-heading"><?php _e( 'How To Order', 'memento-magnets' ); ?></h2>
            <p><?php _e( 'It\'s super simple! Your personalised magnets are just three steps away.', 'memento-magnets' ); ?></p>
        </div>

        <div class="steps-grid">

            <!-- Step 1: Upload photos -->
            <div class="step-card fade-in-up">
                <div class="step-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#FF5FA0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span class="step-badge" aria-hidden="true">1</span>
                </div>
                <h3><?php _e( 'Upload your photos', 'memento-magnets' ); ?></h3>
                <p><?php _e( 'Choose your bundle size and upload your favourite photos. Our cropping tool makes them perfectly square.', 'memento-magnets' ); ?></p>
            </div>

            <!-- Step 2: We print them -->
            <div class="step-card fade-in-up">
                <div class="step-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#FF5FA0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    <span class="step-badge" aria-hidden="true">2</span>
                </div>
                <h3><?php _e( 'We print them', 'memento-magnets' ); ?></h3>
                <p><?php _e( 'We print vibrant 50&times;50mm magnets on premium glossy material within 1&ndash;2 business days.', 'memento-magnets' ); ?></p>
            </div>

            <!-- Step 3: We ship to you -->
            <div class="step-card fade-in-up">
                <div class="step-icon-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#FF5FA0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12H3l9-9 9 9h-2"/>
                        <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7"/>
                        <rect x="9" y="12" width="6" height="7"/>
                    </svg>
                    <span class="step-badge" aria-hidden="true">3</span>
                </div>
                <h3><?php _e( 'We ship to you', 'memento-magnets' ); ?></h3>
                <p><?php _e( 'Your magnets are packed and shipped via NZ Post anywhere in New Zealand. Rural delivery available.', 'memento-magnets' ); ?></p>
            </div>

        </div>

        <div style="text-align:center;margin-top:var(--space-10);">
            <a href="<?php echo esc_url( home_url( '/custom-magnets/' ) ); ?>" class="btn btn--primary btn--lg">
                <?php _e( 'Start Your Order', 'memento-magnets' ); ?>
            </a>
        </div>

    </div>
</section>
