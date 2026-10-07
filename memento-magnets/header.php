<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <?php
    // ── Title tag (wp_title fallback for older setups) ────────────
    // add_theme_support('title-tag') handles this in WP 4.1+
    ?>

    <?php
    // ── Meta description ──────────────────────────────────────────
    $meta_desc = memento_get_meta_description();
    if ( $meta_desc ) :
    ?>
    <meta name="description" content="<?php echo esc_attr( $meta_desc ); ?>">
    <?php endif; ?>

    <?php
    // ── Robots ────────────────────────────────────────────────────
    if ( is_search() || is_404() ) :
    ?>
    <meta name="robots" content="noindex, follow">
    <?php else : ?>
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
    <?php endif; ?>

    <?php
    // ── Canonical URL ─────────────────────────────────────────────
    $canonical = '';
    if ( is_singular() ) {
        $canonical = get_permalink();
    } elseif ( is_front_page() ) {
        $canonical = home_url( '/' );
    } elseif ( is_archive() || is_home() ) {
        $canonical = get_post_type_archive_link( 'post' );
    }
    if ( $canonical ) :
    ?>
    <link rel="canonical" href="<?php echo esc_url( $canonical ); ?>">
    <?php endif; ?>

    <?php
    // ── hreflang (GEO targeting) ──────────────────────────────────
    $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    ?>
    <link rel="alternate" hreflang="en-NZ" href="<?php echo esc_url( $current_url ); ?>">
    <link rel="alternate" hreflang="en-AU" href="<?php echo esc_url( $current_url ); ?>">
    <link rel="alternate" hreflang="en"    href="<?php echo esc_url( $current_url ); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo esc_url( home_url( '/' ) ); ?>">

    <?php
    // ── Open Graph ────────────────────────────────────────────────
    $og_title = is_singular() ? get_the_title() : get_bloginfo( 'name' );
    $og_desc  = $meta_desc;
    $og_image = memento_get_og_image();
    $og_url   = $canonical ?: home_url( '/' );
    $og_type  = is_singular( 'post' ) ? 'article' : 'website';
    ?>
    <meta property="og:type"        content="<?php echo esc_attr( $og_type ); ?>">
    <meta property="og:site_name"   content="<?php bloginfo( 'name' ); ?>">
    <meta property="og:title"       content="<?php echo esc_attr( $og_title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
    <meta property="og:url"         content="<?php echo esc_url( $og_url ); ?>">
    <?php if ( $og_image ) : ?>
    <meta property="og:image"       content="<?php echo esc_url( $og_image ); ?>">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    <?php if ( is_singular( 'post' ) ) : ?>
    <meta property="article:published_time" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
    <meta property="article:modified_time"  content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>">
    <?php endif; ?>

    <?php
    // ── Twitter Card ──────────────────────────────────────────────
    ?>
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@mementomagnets">
    <meta name="twitter:title"       content="<?php echo esc_attr( $og_title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $og_desc ); ?>">
    <?php if ( $og_image ) : ?>
    <meta name="twitter:image"       content="<?php echo esc_url( $og_image ); ?>">
    <?php endif; ?>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Announcement Bar -->
<div class="announcement-bar" role="region" aria-label="<?php esc_attr_e( 'Promotion', 'memento-magnets' ); ?>">
    <p>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="15" height="15" aria-hidden="true" style="display:inline-block;vertical-align:middle;margin-right:6px;">
            <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
        </svg>
        <?php _e( 'Free shipping on orders over <strong>NZD 50</strong> &nbsp;✦&nbsp; New Zealand wide', 'memento-magnets' ); ?>
    </p>
</div>

<!-- Search panel (opened by the search icon; behaviour in assets/js/theme.js) -->
<div class="search-overlay" role="dialog" aria-modal="true" aria-labelledby="search-panel-title" hidden>
    <div class="search-panel">
        <div class="search-panel__head">
            <h2 class="search-panel__title" id="search-panel-title"><?php esc_html_e( 'What are you looking for?', 'memento-magnets' ); ?></h2>
            <button type="button" class="search-panel__close js-search-close" aria-label="<?php esc_attr_e( 'Close search', 'memento-magnets' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" width="20" height="20" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <?php get_search_form(); ?>

        <div class="search-suggest" role="listbox" aria-label="<?php esc_attr_e( 'Suggestions', 'memento-magnets' ); ?>" hidden></div>
        <p class="sr-only search-suggest__status" role="status" aria-live="polite"></p>

        <div class="search-quick">
            <p class="search-quick__label"><?php esc_html_e( 'Popular', 'memento-magnets' ); ?></p>
            <div class="search-quick__chips">
                <?php
                $memento_quick = [];
                if ( function_exists( 'memento_search_index' ) ) {
                    foreach ( memento_search_index() as $memento_item ) {
                        if ( 'product' === $memento_item['type'] && $memento_item['count'] ) {
                            /* translators: %d: pack size */
                            $memento_quick[] = [ $memento_item['url'], sprintf( __( '%d-Pack', 'memento-magnets' ), $memento_item['count'] ) ];
                        }
                    }
                }
                $memento_quick[] = [ class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ), __( 'All magnets', 'memento-magnets' ) ];
                $memento_quick[] = [ home_url( '/faq/' ), __( 'FAQ', 'memento-magnets' ) ];
                $memento_quick[] = [ home_url( '/contact/' ), __( 'Contact us', 'memento-magnets' ) ];
                foreach ( $memento_quick as list( $memento_url, $memento_label ) ) {
                    echo '<a class="search-chip" href="' . esc_url( $memento_url ) . '">' . esc_html( $memento_label ) . '</a>';
                }
                ?>
            </div>
        </div>

        <p class="search-panel__hint" aria-hidden="true"><kbd>↑</kbd><kbd>↓</kbd> <?php esc_html_e( 'to browse', 'memento-magnets' ); ?> · <kbd>Enter</kbd> <?php esc_html_e( 'to open', 'memento-magnets' ); ?> · <kbd>Esc</kbd> <?php esc_html_e( 'to close', 'memento-magnets' ); ?></p>
    </div>
</div>

<!-- Site Header -->
<header class="site-header" role="banner">
    <div class="container">
        <div class="header-inner">

            <!-- Left: Logo -->
            <div class="header-left">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php bloginfo( 'name' ); ?> — <?php bloginfo( 'description' ); ?>">
                    <?php if ( has_custom_logo() ) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <img
                            src="<?php echo esc_url( MEMENTO_URI . '/assets/img/logo.png' ); ?>"
                            alt="<?php bloginfo( 'name' ); ?>"
                            width="52"
                            height="52"
                            loading="eager"
                        >
                    <?php endif; ?>
                    <span class="site-logo-text"><?php esc_html_e( 'Memento Magnets', 'memento-magnets' ); ?></span>
                </a>
            </div>

            <!-- Right: Nav + Actions -->
            <div class="header-right">

                <!-- Primary Navigation -->
                <nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'memento-magnets' ); ?>">
                    <?php
                    wp_nav_menu( [
                        'theme_location' => 'primary',
                        'menu_class'     => 'nav-list',
                        'container'      => false,
                        'fallback_cb'    => 'memento_fallback_nav',
                        'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
                    ] );
                    ?>
                </nav>

                <!-- Nav Actions -->
                <div class="nav-actions">

                    <!-- Search trigger -->
                    <button class="nav-icon-btn js-search-open" aria-label="<?php esc_attr_e( 'Search', 'memento-magnets' ); ?>" aria-haspopup="dialog" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </button>

                    <!-- Account / Login -->
                    <a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? ( wc_get_page_permalink( 'myaccount' ) ?: wp_login_url() ) : wp_login_url() ); ?>"
                       class="nav-icon-btn<?php echo is_user_logged_in() ? ' is-logged-in' : ''; ?>"
                       aria-label="<?php is_user_logged_in() ? esc_attr_e( 'My account', 'memento-magnets' ) : esc_attr_e( 'Log in', 'memento-magnets' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </a>

                    <!-- Cart (always present; count badge only when WooCommerce active) -->
                    <a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_cart_url() : home_url( '/cart/' ) ); ?>"
                       class="nav-icon-btn"
                       aria-label="<?php esc_attr_e( 'View cart', 'memento-magnets' ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <path d="M16 10a4 4 0 01-8 0"/>
                        </svg>
                        <?php if ( class_exists( 'WooCommerce' ) ) :
                            $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
                            if ( $cart_count > 0 ) : ?>
                        <span class="cart-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $cart_count, 'memento-magnets' ), $cart_count ) ); ?>">
                            <?php echo $cart_count; ?>
                        </span>
                        <?php endif; endif; ?>
                    </a>

                    <!-- Hamburger toggle -->
                    <button class="menu-toggle" id="menu-toggle" aria-controls="primary-nav" aria-expanded="false" type="button" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'memento-magnets' ); ?>">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                </div>

            </div><!-- .header-right -->

        </div><!-- .header-inner -->
    </div><!-- .container -->
</header>

<?php
/**
 * Fallback nav when no menu is assigned.
 */
function memento_fallback_nav() {
    $shop  = class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
    $items = [
        [ home_url( '/' ), __( 'Home', 'memento-magnets' ), is_front_page() ],
        [ $shop, __( 'Products', 'memento-magnets' ), function_exists( 'is_woocommerce' ) && ( is_shop() || is_product() || is_product_taxonomy() ) ],
        [ home_url( '/blogs/' ), __( 'Blogs', 'memento-magnets' ), ! is_front_page() && ( is_page( 'blogs' ) || is_home() || is_singular( 'post' ) || is_page_template( [ 'page-blog-personalised-magnets-perfect-gift-nz.php', 'page-blog-how-to-choose-best-photo-for-custom-magnet.php', 'page-blog-custom-magnets-every-occasion-nz.php' ] ) ) ],
        [ home_url( '/faq/' ), __( 'FAQ', 'memento-magnets' ), is_page( 'faq' ) ],
    ];
    echo '<ul class="nav-list" role="list">';
    foreach ( $items as list( $url, $label, $current ) ) {
        printf(
            '<li class="nav-item menu-item%s"><a href="%s"%s>%s</a></li>',
            $current ? ' current-menu-item' : '',
            esc_url( $url ),
            $current ? ' aria-current="page"' : '',
            esc_html( $label )
        );
    }
    echo '</ul>';
}
