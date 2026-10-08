<?php
/**
 * Template Name: Custom Magnets
 *
 * Page template for the Custom Magnets shop page.
 * Assign via WP Admin → Pages → Edit → Page Attributes → Template.
 */

get_header();
?>

<main id="primary" class="site-main">

    <!-- ============================================================
         PAGE HERO
         ============================================================ -->
    <section class="custom-magnets-hero" aria-labelledby="cm-hero-heading">
        <div class="container">
            <div class="custom-magnets-hero__inner">
                <p class="custom-magnets-hero__eyebrow">
                    <?php _e( 'Printed in NZ &amp; shipped to your door', 'memento-magnets' ); ?>
                </p>
                <h1 id="cm-hero-heading" class="custom-magnets-hero__title">
                    <?php _e( 'Our Custom Magnets', 'memento-magnets' ); ?>
                </h1>
                <p class="custom-magnets-hero__tagline">
                    <?php _e( 'Turn your favourite photos into beautiful fridge magnets. Pick your pack, upload your photos, and we\'ll do the rest.', 'memento-magnets' ); ?>
                </p>
            </div>
        </div>

        <!-- Decorative sparkles -->
        <span class="cm-sparkle cm-sparkle--1" aria-hidden="true">✦</span>
        <span class="cm-sparkle cm-sparkle--2" aria-hidden="true">✧</span>
        <span class="cm-sparkle cm-sparkle--3" aria-hidden="true">✦</span>
    </section>

    <!-- ============================================================
         PRODUCT GRID
         ============================================================ -->
    <?php get_template_part( 'template-parts/products' ); ?>

    <!-- ============================================================
         QUALITY PROMISE STRIP (above How To Order)
         ============================================================ -->
    <section class="cm-promise section--cream" aria-label="<?php esc_attr_e( 'Our quality promise', 'memento-magnets' ); ?>">
        <div class="container">
            <div class="cm-promise__grid">

                <div class="cm-promise__item">
                    <span class="cm-promise__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </span>
                    <h3><?php _e( 'Premium Gloss Finish', 'memento-magnets' ); ?></h3>
                    <p><?php _e( '50&times;50mm magnets on high-quality glossy material that pops.', 'memento-magnets' ); ?></p>
                </div>

                <div class="cm-promise__item">
                    <span class="cm-promise__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </span>
                    <h3><?php _e( 'Fast Turnaround', 'memento-magnets' ); ?></h3>
                    <p><?php _e( 'Printed within 1&ndash;2 business days and shipped via NZ Post.', 'memento-magnets' ); ?></p>
                </div>

                <div class="cm-promise__item">
                    <span class="cm-promise__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                    </span>
                    <h3><?php _e( 'Made With Love', 'memento-magnets' ); ?></h3>
                    <p><?php _e( 'Every order is personally checked before it leaves our hands.', 'memento-magnets' ); ?></p>
                </div>

                <div class="cm-promise__item">
                    <span class="cm-promise__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="1" y="3" width="15" height="13" rx="1"/>
                            <path d="M16 8h4l3 3v5h-7V8z"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                        </svg>
                    </span>
                    <h3><?php _e( 'Fast NZ Shipping', 'memento-magnets' ); ?></h3>
                    <p><?php _e( 'Free shipping on all orders within New Zealand. Rural delivery available.', 'memento-magnets' ); ?></p>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================
         HOW TO ORDER
         ============================================================ -->
    <?php get_template_part( 'template-parts/how-to-order' ); ?>

    <!-- ============================================================
         NEWSLETTER
         ============================================================ -->
    <?php get_template_part( 'template-parts/newsletter' ); ?>

</main>

<style>
/* ============================================================
   CUSTOM MAGNETS PAGE — HERO
   ============================================================ */
.custom-magnets-hero {
    position: relative;
    background: var(--gradient-hero);
    padding: calc(var(--header-height) + var(--space-10)) 0 var(--space-10);
    text-align: center;
    overflow: hidden;
}

.custom-magnets-hero__inner {
    position: relative;
    z-index: 1;
    max-width: 680px;
    margin: 0 auto;
}

.custom-magnets-hero__eyebrow {
    font-family: var(--font-body);
    font-size: var(--text-sm);
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.85);
    margin-bottom: var(--space-3);
}

.custom-magnets-hero__title {
    font-family: var(--font-heading);
    font-size: var(--text-6xl);
    font-weight: 900;
    color: var(--color-white);
    line-height: 1.05;
    margin-bottom: var(--space-5);
    text-shadow: 0 2px 12px rgba(0,0,0,0.15);
}

.custom-magnets-hero__tagline {
    font-size: var(--text-lg);
    color: rgba(255,255,255,0.92);
    max-width: 560px;
    margin: 0 auto var(--space-8);
    line-height: 1.7;
}

.custom-magnets-hero__cta {
    display: inline-flex;
}

/* Sparkle decorators */
.cm-sparkle {
    position: absolute;
    font-size: 2rem;
    color: rgba(255,255,255,0.35);
    pointer-events: none;
    animation: sparkle-float 4s ease-in-out infinite;
}
.cm-sparkle--1 { top: 18%; left: 8%;  animation-delay: 0s;    font-size: 1.5rem; }
.cm-sparkle--2 { top: 60%; right: 6%; animation-delay: 1.5s;  font-size: 2.5rem; }
.cm-sparkle--3 { bottom: 15%; left: 18%; animation-delay: 0.8s; font-size: 1.2rem; }

@keyframes sparkle-float {
    0%, 100% { transform: translateY(0) rotate(0deg);   opacity: 0.35; }
    50%       { transform: translateY(-10px) rotate(20deg); opacity: 0.6;  }
}

/* ============================================================
   QUALITY PROMISE STRIP
   ============================================================ */
.cm-promise {
    padding: var(--space-16) 0;
}

.cm-promise__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-8);
    text-align: center;
}

.cm-promise__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: var(--radius-full);
    background: var(--gradient-brand);
    color: var(--color-white);
    margin: 0 auto var(--space-4);
}

.cm-promise__item h3 {
    font-family: var(--font-heading);
    font-size: var(--text-lg);
    color: var(--color-charcoal);
    margin-bottom: var(--space-2);
}

.cm-promise__item p {
    font-size: var(--text-sm);
    color: var(--color-mid-grey);
    line-height: 1.6;
    margin: 0;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
    .cm-promise__grid {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-6);
    }
}

@media (max-width: 768px) {
    .custom-magnets-hero__title {
        font-size: var(--text-4xl);
    }
    .custom-magnets-hero {
        padding: calc(var(--header-height) + var(--space-6)) 0 var(--space-6);
    }
    .cm-promise__grid {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--space-5);
    }
}

@media (max-width: 480px) {
    .custom-magnets-hero__title {
        font-size: var(--text-3xl);
    }
    .cm-promise__grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php get_footer(); ?>
