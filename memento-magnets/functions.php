<?php
/**
 * Memento Magnets Theme Functions
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MEMENTO_VERSION', '1.0.0' );
define( 'MEMENTO_DIR', get_template_directory() );
define( 'MEMENTO_URI', get_template_directory_uri() );

// Register Custom Magnets as a selectable page template (bypasses WordPress file-scan cache).
add_filter( 'theme_page_templates', function ( $templates ) {
    $templates['page-custom-magnets.php'] = __( 'Custom Magnets', 'memento-magnets' );
    return $templates;
} );

// Assign Custom Magnets template to page ID 16 (runs once).
add_action( 'init', function () {
    if ( get_post_meta( 16, '_memento_template_assigned', true ) ) {
        return;
    }
    update_post_meta( 16, '_page_template', 'page-custom-magnets.php' );
    update_post_meta( 16, '_memento_template_assigned', '1' );
} );

// ============================================================
// THEME SETUP
// ============================================================

function memento_setup() {
    load_theme_textdomain( 'memento-magnets', MEMENTO_DIR . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ] );
    add_theme_support( 'custom-logo', [
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );

    // WooCommerce support
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    // Image sizes
    add_image_size( 'memento-product', 600, 600, true );
    add_image_size( 'memento-hero', 1400, 700, true );
    add_image_size( 'memento-blog-thumb', 800, 500, true );
    add_image_size( 'memento-blog-card', 400, 260, true );

    // Register nav menus
    register_nav_menus( [
        'primary'  => __( 'Primary Navigation', 'memento-magnets' ),
        'footer'   => __( 'Footer Navigation', 'memento-magnets' ),
        'policies' => __( 'Policy Links', 'memento-magnets' ),
    ] );
}
add_action( 'after_setup_theme', 'memento_setup' );

// ============================================================
// ENQUEUE SCRIPTS & STYLES
// ============================================================

/**
 * Asset version = theme version + file modification time, so browsers and
 * SiteGround's CDN fetch fresh CSS/JS automatically after every upload.
 */
function memento_asset_ver( $relative ) {
    $mtime = @filemtime( MEMENTO_DIR . '/' . ltrim( $relative, '/' ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
    return MEMENTO_VERSION . ( $mtime ? '.' . $mtime : '' );
}

function memento_enqueue_assets() {
    // Google Fonts
    wp_enqueue_style(
        'memento-google-fonts',
        'https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@400;600;700;800;900&family=Nothing+You+Could+Do&family=Outfit:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    // Main stylesheet
    wp_enqueue_style(
        'memento-style',
        get_stylesheet_uri(),
        [ 'memento-google-fonts' ],
        memento_asset_ver( 'style.css' )
    );

    // Extended theme CSS
    wp_enqueue_style(
        'memento-theme',
        MEMENTO_URI . '/assets/css/theme.css',
        [ 'memento-style' ],
        memento_asset_ver( 'assets/css/theme.css' )
    );

    // WooCommerce adjustments (only if WooCommerce active)
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_style(
            'memento-woocommerce',
            MEMENTO_URI . '/assets/css/woocommerce.css',
            [ 'memento-theme', 'woocommerce-general' ],
            memento_asset_ver( 'assets/css/woocommerce.css' )
        );
    }

    // Theme JS
    wp_enqueue_script(
        'memento-theme',
        MEMENTO_URI . '/assets/js/theme.js',
        [],
        memento_asset_ver( 'assets/js/theme.js' ),
        true
    );

    // Pass data to JS
    wp_localize_script( 'memento-theme', 'mementoData', [
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'memento_nonce' ),
        'siteUrl'  => get_site_url(),
        'isHome'   => is_front_page() ? 'true' : 'false',
    ] );

    // Comments script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'memento_enqueue_assets' );

// Preconnect for Google Fonts performance
function memento_preconnect_hints() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'memento_preconnect_hints', 1 );

// ============================================================
// WIDGET AREAS
// ============================================================

function memento_register_sidebars() {
    $defaults = [
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ];

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Blog Sidebar', 'memento-magnets' ),
        'id'   => 'sidebar-blog',
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Footer Column 1', 'memento-magnets' ),
        'id'   => 'footer-1',
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Footer Column 2', 'memento-magnets' ),
        'id'   => 'footer-2',
    ] ) );

    register_sidebar( array_merge( $defaults, [
        'name' => __( 'Shop Sidebar', 'memento-magnets' ),
        'id'   => 'sidebar-shop',
    ] ) );
}
add_action( 'widgets_init', 'memento_register_sidebars' );

// Remove default WordPress widgets that clutter sidebars (Pages, Archives, Categories, etc.)
add_action( 'widgets_init', function () {
    unregister_widget( 'WP_Widget_Pages' );
    unregister_widget( 'WP_Widget_Archives' );
    unregister_widget( 'WP_Widget_Categories' );
    unregister_widget( 'WP_Widget_Meta' );
    unregister_widget( 'WP_Widget_Recent_Posts' );
    unregister_widget( 'WP_Widget_Recent_Comments' );
    unregister_widget( 'WP_Widget_RSS' );
    unregister_widget( 'WP_Widget_Tag_Cloud' );
    unregister_widget( 'WP_Widget_Calendar' );
}, 20 );

// Clear any persisted junk widget instances from the sidebar-shop area in the DB.
// Runs once on any admin page load; harmless after the area is already empty.
add_action( 'admin_init', function () {
    $sidebars = get_option( 'sidebars_widgets', [] );
    if ( ! empty( $sidebars['sidebar-shop'] ) ) {
        $sidebars['sidebar-shop'] = [];
        update_option( 'sidebars_widgets', $sidebars );
    }
} );

// ============================================================
// SEO & META HELPERS
// ============================================================

function memento_get_meta_description() {
    // Allow individual templates to override via add_filter( 'memento_meta_description', ... )
    $override = apply_filters( 'memento_meta_description', '' );
    if ( $override ) return $override;

    if ( is_front_page() ) {
        return __( 'Create personalised fridge magnets for every occasion in New Zealand. Custom photo magnets for home, gifts and events — order online from Memento Magnets.', 'memento-magnets' );
    }
    if ( is_singular() ) {
        global $post;
        $custom = get_post_meta( $post->ID, '_memento_meta_description', true );
        if ( $custom ) return $custom;
        $excerpt = wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 25, '...' );
        return $excerpt ?: get_bloginfo( 'description' );
    }
    if ( is_archive() ) {
        return __( 'Read our latest blog posts about personalised magnets, gift ideas, and inspiration for New Zealand and Australia.', 'memento-magnets' );
    }
    return get_bloginfo( 'description' );
}

function memento_get_og_image() {
    if ( is_singular() && has_post_thumbnail() ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id(), 'memento-hero' );
        return $img ? $img[0] : '';
    }
    return MEMENTO_URI . '/assets/img/og-default.jpg';
}

// ============================================================
// CUSTOM EXCERPT LENGTH
// ============================================================

function memento_excerpt_length( $length ) {
    return 25;
}
add_filter( 'excerpt_length', 'memento_excerpt_length' );

function memento_excerpt_more( $more ) {
    return '&hellip;';
}
add_filter( 'excerpt_more', 'memento_excerpt_more' );

// ============================================================
// READING TIME
// ============================================================

function memento_reading_time( $post_id = null ) {
    $post_id  = $post_id ?: get_the_ID();
    $content  = get_post_field( 'post_content', $post_id );
    $words    = str_word_count( strip_tags( $content ) );
    $minutes  = max( 1, (int) ceil( $words / 200 ) );
    return sprintf(
        _n( '%d min read', '%d min read', $minutes, 'memento-magnets' ),
        $minutes
    );
}

// ============================================================
// WOOCOMMERCE TWEAKS
// ============================================================

if ( class_exists( 'WooCommerce' ) ) {
    // Remove default WooCommerce styles (we handle them)
    add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

    // Change number of products per row
    add_filter( 'loop_shop_columns', function() { return 4; } );

    // Number of products per page
    add_filter( 'loop_shop_per_page', function() { return 12; }, 20 );

    // Show WooCommerce breadcrumbs
    add_action( 'memento_before_content', 'woocommerce_breadcrumb', 20 );

    // Remove "100 in stock" availability text on single product pages.
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_availability', 30 );

    // Hide archive title, result count, and sorting dropdown on shop/category pages.
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
    remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_orderby', 30 );
    add_filter( 'woocommerce_page_title', '__return_empty_string' );

    // Loop: change "Add to cart" → "Upload Now" and link to product page.
    add_filter( 'woocommerce_loop_add_to_cart_link', function ( $html, $product ) {
        return sprintf(
            '<a href="%s" class="button">%s</a>',
            esc_url( $product->get_permalink() ),
            esc_html__( 'Upload Now', 'memento-magnets' )
        );
    }, 10, 2 );

    // Single product: change "Add to cart" button label.
    add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
        return __( 'Add to Cart', 'memento-magnets' );
    } );

    // Breadcrumb: replace "Uncategorized" with "Products".
    add_filter( 'woocommerce_get_breadcrumb', function ( $crumbs ) {
        foreach ( $crumbs as &$crumb ) {
            if ( isset( $crumb[0] ) && strtolower( $crumb[0] ) === 'uncategorized' ) {
                $crumb[0] = __( 'Products', 'memento-magnets' );
            }
        }
        return $crumbs;
    } );

    // Remove duplicate "Description" H2 heading inside the Description tab.
    add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );

    // Remove Reviews tab and star rating from product pages.
    add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
        unset( $tabs['reviews'] );
        return $tabs;
    } );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
    add_filter( 'woocommerce_review_rating_html',  '__return_empty_string' );
    add_filter( 'comments_open', function ( $open, $post_id ) {
        if ( $post_id && get_post_type( $post_id ) === 'product' ) return false;
        return $open;
    }, 10, 2 );

    // Remove SKU / Category / Brand meta block.
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

    // Remove sidebar from shop/archive pages — we don't want it.
    remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar' );
}

// Inject shop/archive page styles inline.
add_action( 'wp_head', function () {
    if ( ! is_shop() && ! is_product_category() && ! is_product_tag() ) return;
    ?>
    <style id="memento-shop-critical">
    /* ── Hide title, count, sorting ── */
    .woocommerce-products-header,
    .woocommerce-products-header__title,
    .woocommerce-result-count,
    .woocommerce-ordering { display: none !important; }

    /* ── Product grid wrapper ── */
    .memento-products-wrap {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        padding: 2rem 1.5rem 4rem;
    }

    </style>
    <?php
}, 5 );

// Inject critical single-product layout CSS inline so layout works even
// before woocommerce.css is uploaded to the live server.
add_action( 'wp_head', function () {
    if ( ! is_product() ) return;
    ?>
    <style id="memento-product-critical">
    /* Hide stock availability ("100 in stock") */
    .single-product .stock,
    .single-product p.stock,
    .woocommerce-variation-availability { display: none !important; }
    /* ── Two-column layout ── */
    .single-product div.product {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 3rem !important;
        align-items: start !important;
        padding: 2rem 0 4rem !important;
        max-width: 1200px;
        margin: 0 auto;
    }
    .single-product .woocommerce-product-gallery { grid-column: 1; }
    .single-product .summary                     { grid-column: 2; }
    .woocommerce-tabs                            { grid-column: 1 / -1; }

    /* ── Gallery ── */
    .woocommerce-product-gallery__image,
    .woocommerce-product-gallery .flex-viewport {
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(26,26,26,.10);
    }
    .flex-control-thumbs {
        display: flex !important;
        gap: .5rem;
        margin-top: .75rem;
        padding: 0;
        list-style: none;
    }
    .flex-control-thumbs img {
        width: 64px; height: 64px;
        object-fit: cover;
        border-radius: 6px;
        border: 2px solid transparent;
        cursor: pointer;
    }
    .flex-control-thumbs img.flex-active,
    .flex-control-thumbs img:hover { border-color: #FF5FA0; }

    /* ── Summary typography ── */
    .single-product .product_title {
        font-family: 'Big Shoulders Display', sans-serif;
        font-size: clamp(1.875rem, 4vw, 2.25rem);
        font-weight: 900;
        color: #1A1A1A;
        line-height: 1.1;
        margin-bottom: .5rem;
    }
    .single-product .price {
        font-family: 'Big Shoulders Display', sans-serif;
        font-size: 1.875rem;
        font-weight: 800;
        background: linear-gradient(135deg, #FF5FA0 0%, #FFD54F 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: .25rem;
    }

    /* ── Quantity + add-to-cart ── */
    .single-product form.cart {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
        margin-top: .5rem;
    }
    .single-product form.cart .qty {
        width: 68px;
        padding: .6rem .75rem;
        text-align: center;
        font-size: 1rem;
        font-weight: 600;
        border: 2px solid #E8E0DC;
        border-radius: 10px;
        outline: none;
        -moz-appearance: textfield;
    }
    .single-product form.cart .qty:focus { border-color: #FF5FA0; box-shadow: 0 0 0 3px rgba(255,95,160,.15); }
    .single-product .single_add_to_cart_button {
        flex: 1;
        background: linear-gradient(135deg, #FF5FA0 0%, #FFD54F 100%) !important;
        color: #fff !important;
        font-size: 1rem !important;
        font-weight: 700 !important;
        padding: .75rem 2rem !important;
        border: none !important;
        border-radius: 9999px !important;
        cursor: pointer !important;
        box-shadow: 0 8px 24px rgba(255,95,160,.25) !important;
        transition: transform .25s ease, box-shadow .25s ease !important;
    }
    .single-product .single_add_to_cart_button:hover:not([disabled]) {
        transform: translateY(-2px) !important;
        box-shadow: 0 12px 32px rgba(255,95,160,.35) !important;
    }
    .single-product .single_add_to_cart_button.disabled,
    .single-product .single_add_to_cart_button[disabled] {
        opacity: .45 !important; cursor: not-allowed !important; transform: none !important;
    }

    /* ── Shipping note ── */
    .memento-shipping-note {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .875rem;
        font-weight: 500;
        color: #9B8E8A;
        background: #F8F4F2;
        border-radius: 9999px;
        padding: .4rem 1rem;
        margin-bottom: 1rem;
    }
    .memento-shipping-note svg { flex-shrink: 0; color: #FF5FA0; }

    /* ── Upload widget ── */
    .memento-upload-widget {
        background: #F8F4F2;
        border: 2px dashed #E0D5D0;
        border-radius: 14px;
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .memento-upload-widget__header {
        display: flex; align-items: center;
        justify-content: space-between;
        gap: 1rem; margin-bottom: .5rem; flex-wrap: wrap;
    }
    .memento-upload-widget__title {
        font-family: 'Big Shoulders Display', sans-serif;
        font-size: 1.125rem; font-weight: 700; color: #1A1A1A; margin: 0;
    }
    .upload-progress { display: flex; align-items: center; gap: .5rem; }
    .upload-progress__bar {
        width: 80px; height: 6px; background: #E0D5D0;
        border-radius: 9999px; overflow: hidden;
    }
    .upload-progress__fill {
        height: 100%;
        background: linear-gradient(135deg, #FF5FA0 0%, #FFD54F 100%);
        border-radius: 9999px; transition: width .25s ease;
    }
    .upload-progress__text { font-size: .8125rem; color: #9B8E8A; white-space: nowrap; }
    .upload-progress__count { color: #FF5FA0; font-weight: 700; }
    .memento-upload-widget__hint { font-size: .8125rem; color: #9B8E8A; margin-bottom: .75rem; line-height: 1.5; }
    .memento-upload-widget__cta-note {
        font-size: .8125rem; color: #FF8C6E;
        text-align: center; margin: .5rem 0 0; font-weight: 500;
    }
    .memento-upload-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .625rem;
        margin-bottom: .75rem;
    }
    .upload-slot { position: relative; aspect-ratio: 1 / 1; }
    .upload-slot__inner {
        position: relative; width: 100%; height: 100%;
        border-radius: 10px; border: 2px dashed #D0C8C4;
        background: #fff; overflow: hidden; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .upload-slot__inner:hover { border-color: #FF5FA0; box-shadow: 0 0 0 3px rgba(255,95,160,.1); }
    .upload-slot.is-uploaded .upload-slot__inner { border-color: #FF5FA0; border-style: solid; }
    .upload-slot__placeholder {
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: .25rem; color: #C0B8B4; text-align: center; padding: .5rem;
    }
    .upload-slot__placeholder span { font-size: .6875rem; line-height: 1.2; }
    .upload-slot__preview {
        position: absolute; inset: 0; width: 100%; height: 100%;
        object-fit: cover; border-radius: 8px;
    }
    .upload-slot__spinner {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.7); z-index: 2;
    }
    .upload-slot__spinner-ring {
        width: 26px; height: 26px;
        border: 3px solid rgba(255,95,160,.2);
        border-top-color: #FF5FA0;
        border-radius: 50%;
        animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .upload-slot__remove {
        position: absolute; top: 4px; right: 4px; z-index: 3;
        display: flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 50%;
        background: rgba(26,26,26,.65); color: #fff;
        border: none; cursor: pointer; font-size: 14px; line-height: 1;
        transition: background .15s ease; padding: 0;
    }
    .upload-slot__remove:hover { background: #FF5FA0; }
    .upload-slot__error {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: rgba(231,76,60,.9); color: #fff;
        font-size: .6875rem; padding: .2rem .4rem;
        text-align: center; z-index: 4;
    }

    /* ── Trust badges ── */
    .memento-trust-badges {
        display: grid; grid-template-columns: repeat(2, 1fr);
        gap: .625rem; margin-top: 1.25rem;
        padding-top: 1.25rem; border-top: 1px solid #F0E8E4;
    }
    .trust-badge {
        display: flex; align-items: center; gap: .5rem;
        font-size: .875rem; font-weight: 500; color: #4A3F3C;
    }
    .trust-badge svg { flex-shrink: 0; color: #FF5FA0; }

    /* ── Tabs ── */
    .woocommerce-tabs ul.tabs {
        display: flex; gap: 0; border-bottom: 2px solid #F0E8E4;
        margin-bottom: 1.5rem; padding: 0; list-style: none;
    }
    .woocommerce-tabs ul.tabs li { border: none; background: none; border-radius: 0; margin: 0; padding: 0; }
    .woocommerce-tabs ul.tabs li::before,
    .woocommerce-tabs ul.tabs li::after { display: none; }
    .woocommerce-tabs ul.tabs li a {
        display: block; font-size: 1rem; font-weight: 600;
        color: #9B8E8A; padding: .75rem 1.5rem;
        border-bottom: 2px solid transparent; margin-bottom: -2px;
        transition: color .15s, border-color .15s; text-decoration: none;
    }
    .woocommerce-tabs ul.tabs li.active a,
    .woocommerce-tabs ul.tabs li a:hover { color: #1A1A1A; border-bottom-color: #FF5FA0; }

    /* ── Sale badge ── */
    .single-product .onsale {
        background: #FF5FA0; color: #fff;
        font-size: .8125rem; font-weight: 700;
        border-radius: 9999px; padding: .2rem .75rem;
        top: .75rem; left: .75rem;
        min-height: unset; min-width: unset; line-height: 1.6;
    }

    /* ── Related products section ── */
    .single-product .related,
    .single-product .up-sells {
        margin-top: 3rem !important;
        padding-top: 2.5rem !important;
        border-top: 1px solid #F0E8E4 !important;
        grid-column: 1 / -1 !important;
    }
    .single-product .related > h2,
    .single-product .up-sells > h2 {
        font-family: 'Big Shoulders Display', sans-serif !important;
        font-size: 1.75rem !important;
        font-weight: 800 !important;
        color: #1A1A1A !important;
        margin-bottom: 1.5rem !important;
    }
    /* ── Mobile stack ── */
    @media (max-width: 768px) {
        .single-product div.product {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
            padding: 1.5rem 0 3rem !important;
        }
        .single-product .summary { grid-column: 1; }
        .woocommerce-tabs { grid-column: 1; }
    }
    </style>
    <?php
}, 5 );

// Shared product cards (Products page + homepage collection), pack-size ordering.
require_once MEMENTO_DIR . '/inc/product-cards.php';

// Creates the Blogs, FAQ and blog-article pages if they're missing (prevents 404s).
require_once MEMENTO_DIR . '/inc/page-setup.php';

// Contact form handler + saved messages (WP Admin → Messages).
require_once MEMENTO_DIR . '/inc/contact-form.php';

// My Account: menu items, status badges, account-only styles.
require_once MEMENTO_DIR . '/inc/account.php';

// Cart page: styling, progress bar, trust row, branded empty state.
require_once MEMENTO_DIR . '/inc/cart-page.php';

// Site search: instant suggestions, quick links, cleaner results.
require_once MEMENTO_DIR . '/inc/search.php';

// ============================================================
// SINGLE PRODUCT — TRUST BADGES
// Photo upload, editor, cart validation, order files and Dropbox sync
// live in the "Memento Personalizer" plugin (memento-personalizer/).
// ============================================================

// Warn admins if the personalisation plugin is not active.
add_action( 'admin_notices', function () {
    if ( defined( 'MEMENTO_PZ_VERSION' ) || ! current_user_can( 'activate_plugins' ) ) return;
    echo '<div class="notice notice-error"><p>' . esc_html__( 'Memento Magnets: the "Memento Personalizer" plugin is not active, so customers cannot upload photos. Activate it under Plugins.', 'memento-magnets' ) . '</p></div>';
} );

// Priority 35 — Trust badges (after add-to-cart at 30).
add_action( 'woocommerce_single_product_summary', function () {
    $badges = [
        [
            'icon' => '<path d="M5 12H3l9-9 9 9h-2"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7"/>',
            'label' => __( 'Fast NZ Shipping', 'memento-magnets' ),
        ],
        [
            'icon' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'label' => __( '5cm × 5cm per magnet', 'memento-magnets' ),
        ],
        [
            'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'label' => __( 'Vibrant Gloss Print Finish', 'memento-magnets' ),
        ],
        [
            'icon' => '<polyline points="20 6 9 17 4 12"/>',
            'label' => __( 'Handcrafted in New Zealand', 'memento-magnets' ),
        ],
    ];
    ?>
    <div class="memento-trust-badges">
        <?php foreach ( $badges as $b ) : ?>
        <div class="trust-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $b['icon']; ?></svg>
            <span><?php echo esc_html( $b['label'] ); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
}, 35 );

// Placeholder thumbnail strip — renders 3 image placeholders below the main product
// image when the product has no real gallery images assigned. This gives a visual
// carousel affordance and matches the existing .flex-control-thumbs strip styling.
add_action( 'woocommerce_product_thumbnails', function () {
    global $product;
    if ( ! $product ) return;
    if ( ! empty( $product->get_gallery_image_ids() ) ) return; // skip if real gallery exists
    if ( ! $product->get_image_id() && memento_card_pack_count( $product ) ) return; // magnet-tile visual already shown
    ?>
    <div class="memento-thumb-placeholders" aria-hidden="true">
        <?php for ( $i = 0; $i < 3; $i++ ) : ?>
        <div class="memento-thumb-placeholder<?php echo $i === 0 ? ' is-first' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
        </div>
        <?php endfor; ?>
    </div>
    <?php
}, 30 );

// "Added to your cart" message: bold product name(s), cleaner wording.
// Styled in assets/css/theme.css (STORE NOTICES).
add_filter( 'wc_add_to_cart_message_html', function ( $message, $products ) {
    $names = [];
    $count = 0;
    foreach ( (array) $products as $product_id => $qty ) {
        $names[] = '<strong>' . esc_html( wp_strip_all_tags( get_the_title( $product_id ) ) ) . '</strong>';
        $count  += max( 1, (int) $qty );
    }
    if ( ! $names ) {
        return $message;
    }
    $text = sprintf(
        /* translators: %s: product name(s) */
        _n( '%s has been added to your cart.', '%s have been added to your cart.', count( $names ), 'memento-magnets' ),
        wp_sprintf( '%l', $names )
    );
    return sprintf(
        '<span class="mm-notice__text">%s</span> <a href="%s" class="button wc-forward">%s</a>',
        $text,
        esc_url( wc_get_cart_url() ),
        esc_html__( 'View cart', 'memento-magnets' )
    );
}, 10, 2 );

// After a successful add-to-cart (form POST path), always redirect to cart.
// Without this WooCommerce defaults to redirecting back to the product page.
add_filter( 'woocommerce_add_to_cart_redirect', function() {
    return wc_get_cart_url();
} );

// ============================================================
// STRUCTURED DATA HELPERS
// ============================================================

function memento_local_business_schema() {
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'LocalBusiness',
        'name'        => 'Memento Magnets',
        'description' => 'Personalised fridge magnets custom-made for homes, gifts and events across New Zealand and Australia.',
        'url'         => get_site_url(),
        'logo'        => MEMENTO_URI . '/assets/img/logo.png',
        'image'       => MEMENTO_URI . '/assets/img/og-default.jpg',
        'email'       => 'hello@mementomagnets.co.nz',
        'areaServed'  => [ 'New Zealand', 'Australia' ],
        'sameAs'      => [
            'https://www.facebook.com/mementomagnets',
            'https://www.instagram.com/mementomagnets',
            'https://www.tiktok.com/@mementomagnets',
        ],
        'priceRange'  => '$$',
    ];
    return $schema;
}

function memento_breadcrumb_schema() {
    if ( is_front_page() ) return null;

    $items   = [];
    $items[] = [
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Home',
        'item'     => home_url( '/' ),
    ];

    if ( is_singular( 'post' ) ) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => 'Blogs',
            'item'     => get_post_type_archive_link( 'post' ),
        ];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => 3,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        ];
    } elseif ( is_page() ) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        ];
    } elseif ( is_archive() ) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => get_the_archive_title(),
            'item'     => get_post_type_archive_link( 'post' ),
        ];
    }

    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

// ============================================================
// CUSTOM BODY CLASSES
// ============================================================

function memento_body_classes( $classes ) {
    if ( is_front_page() ) {
        $classes[] = 'is-homepage';
    }
    if ( class_exists( 'WooCommerce' ) ) {
        $classes[] = 'has-woocommerce';
    }
    return $classes;
}
add_filter( 'body_class', 'memento_body_classes' );

// ============================================================
// CUSTOM POST META (meta description field)
// ============================================================

function memento_add_meta_box() {
    add_meta_box(
        'memento_seo',
        __( 'Memento SEO', 'memento-magnets' ),
        'memento_seo_meta_box_html',
        [ 'post', 'page', 'product' ],
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'memento_add_meta_box' );

function memento_seo_meta_box_html( $post ) {
    wp_nonce_field( 'memento_seo_nonce', 'memento_seo_nonce' );
    $desc = get_post_meta( $post->ID, '_memento_meta_description', true );
    ?>
    <p>
        <label for="memento_meta_description"><strong><?php _e( 'Meta Description', 'memento-magnets' ); ?></strong></label><br>
        <textarea id="memento_meta_description" name="memento_meta_description" rows="3" style="width:100%"><?php echo esc_textarea( $desc ); ?></textarea>
        <span class="description"><?php _e( 'Recommended: 150–160 characters.', 'memento-magnets' ); ?></span>
    </p>
    <?php
}

function memento_save_seo_meta( $post_id ) {
    if ( ! isset( $_POST['memento_seo_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['memento_seo_nonce'], 'memento_seo_nonce' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['memento_meta_description'] ) ) {
        update_post_meta(
            $post_id,
            '_memento_meta_description',
            sanitize_textarea_field( $_POST['memento_meta_description'] )
        );
    }
}
add_action( 'save_post', 'memento_save_seo_meta' );

// ============================================================
// SEARCH FORM HELPER
// ============================================================

function memento_get_search_form( $echo = false ) {
    ob_start();
    get_search_form();
    $form = ob_get_clean();
    if ( $echo ) {
        echo $form;
    }
    return $form;
}

// ============================================================
// CUSTOMIZER — REMOVE DEFAULT WORDPRESS SECTIONS
// ============================================================

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ) {
    // Sections to remove
    $sections = [
        'static_front_page',  // Homepage Settings
        'title_tagline',      // Site Identity (title/tagline — logo managed via theme option)
        'colors',             // Colors
        'background_image',   // Background Image
        'nav_menus',          // Menus panel (registered separately below)
        'widgets',            // Widgets
        'custom_css',         // Additional CSS
    ];
    foreach ( $sections as $section ) {
        $wp_customize->remove_section( $section );
    }

    // Remove the Menus panel entirely
    $wp_customize->remove_panel( 'nav_menus' );

    // Remove individual controls that may survive section removal
    $controls = [
        'blogname',
        'blogdescription',
        'header_textcolor',
        'display_header_text',
        'background_color',
        'background_image',
        'custom_css',
    ];
    foreach ( $controls as $control ) {
        $wp_customize->remove_control( $control );
    }
}, 30 );
