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
        MEMENTO_VERSION
    );

    // Extended theme CSS
    wp_enqueue_style(
        'memento-theme',
        MEMENTO_URI . '/assets/css/theme.css',
        [ 'memento-style' ],
        MEMENTO_VERSION
    );

    // WooCommerce adjustments (only if WooCommerce active)
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_style(
            'memento-woocommerce',
            MEMENTO_URI . '/assets/css/woocommerce.css',
            [ 'memento-theme', 'woocommerce-general' ],
            MEMENTO_VERSION
        );
    }

    // Theme JS
    wp_enqueue_script(
        'memento-theme',
        MEMENTO_URI . '/assets/js/theme.js',
        [],
        MEMENTO_VERSION,
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
}

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
