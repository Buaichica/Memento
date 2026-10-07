<?php
/**
 * Creates the theme's built-in pages (Blogs, FAQ, Contact, the three blog articles) if
 * they don't exist, so links in the header and articles never 404 on a fresh
 * or migrated site.
 *
 * Safe on a live site:
 *  - runs once per MEMENTO_PAGES_VERSION (deleting a page later won't bring it back)
 *  - never duplicates or overwrites an existing page; only fills in a missing template
 *  - pages in the trash are left alone
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const MEMENTO_PAGES_VERSION = '2'; // 2: added Contact.

function memento_builtin_pages() {
    return [
        'blogs' => [
            'title'    => __( 'Blogs', 'memento-magnets' ),
            'template' => '', // page-blogs.php applies automatically by slug.
            'content'  => '',
        ],
        'faq' => [
            'title'    => __( 'FAQ', 'memento-magnets' ),
            'template' => '', // page-faq.php applies automatically by slug.
            'content'  => '',
        ],
        'contact' => [
            'title'    => __( 'Contact Us', 'memento-magnets' ),
            'template' => '', // page-contact.php applies automatically by slug.
            'content'  => '',
        ],
        'personalised-magnets-perfect-gift-nz' => [
            'title'    => __( '5 Reasons Personalised Photo Magnets Make the Perfect Gift in New Zealand', 'memento-magnets' ),
            'template' => 'page-blog-personalised-magnets-perfect-gift-nz.php',
            'content'  => __( 'Can\'t find a gift that feels truly personal? Discover why thousands of New Zealanders choose custom photo magnets for every occasion.', 'memento-magnets' ),
        ],
        'how-to-choose-best-photo-for-custom-magnet' => [
            'title'    => __( 'How to Choose the Best Photo for Your Custom Fridge Magnet', 'memento-magnets' ),
            'template' => 'page-blog-how-to-choose-best-photo-for-custom-magnet.php',
            'content'  => __( 'The quality of your photo makes all the difference. Learn exactly what to look for — resolution, lighting, cropping and more.', 'memento-magnets' ),
        ],
        'custom-magnets-for-every-occasion-nz' => [
            'title'    => __( 'Custom Photo Magnets for Every Occasion in New Zealand', 'memento-magnets' ),
            'template' => 'page-blog-custom-magnets-every-occasion-nz.php',
            'content'  => __( 'Weddings, Christmas, baby showers, graduations — personalised magnets work beautifully as gifts for every milestone in New Zealand.', 'memento-magnets' ),
        ],
    ];
}

/**
 * Ensure the built-in pages exist.
 *
 * @return array Report: slug => created|template_set|exists|trashed
 */
function memento_setup_builtin_pages() {
    $report = [];
    foreach ( memento_builtin_pages() as $slug => $page ) {
        $existing = get_page_by_path( $slug, OBJECT, 'page' );

        if ( $existing ) {
            if ( 'trash' === $existing->post_status ) {
                $report[ $slug ] = 'trashed';
                continue;
            }
            $current = get_post_meta( $existing->ID, '_wp_page_template', true );
            if ( $page['template'] && ( ! $current || 'default' === $current ) ) {
                update_post_meta( $existing->ID, '_wp_page_template', $page['template'] );
                $report[ $slug ] = 'template_set';
            } else {
                $report[ $slug ] = 'exists';
            }
            continue;
        }

        $id = wp_insert_post( [
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_name'    => $slug,
            'post_title'   => $page['title'],
            'post_content' => $page['content'],
            'post_author'  => get_current_user_id() ?: 1,
            'meta_input'   => $page['template'] ? [ '_wp_page_template' => $page['template'] ] : [],
        ], true );
        $report[ $slug ] = is_wp_error( $id ) ? 'error: ' . $id->get_error_message() : 'created';
    }
    return $report;
}

// On theme activation, and once on the next admin load after this update.
/**
 * Run the page setup at most once per version, even when several admin
 * requests arrive at the same moment (dashboard + its background AJAX calls).
 * add_option() is atomic — it fails if the lock row already exists — so only
 * one request can ever do the work.
 *
 * @return array|null Report, or null if another request holds the lock / it's already done.
 */
function memento_maybe_setup_builtin_pages() {
    if ( get_option( 'memento_pages_version' ) === MEMENTO_PAGES_VERSION ) {
        return null;
    }
    $lock = 'memento_pages_setup_lock';
    if ( ! add_option( $lock, time(), '', false ) ) {
        // Clear a lock left behind by a crashed request, then let a later request retry.
        if ( (int) get_option( $lock ) < time() - MINUTE_IN_SECONDS ) {
            delete_option( $lock );
        }
        return null;
    }
    wp_cache_delete( 'memento_pages_version', 'options' );
    wp_cache_delete( 'alloptions', 'options' );
    if ( get_option( 'memento_pages_version' ) === MEMENTO_PAGES_VERSION ) { // Finished by another request meanwhile.
        delete_option( $lock );
        return null;
    }
    $report = memento_setup_builtin_pages();
    update_option( 'memento_pages_version', MEMENTO_PAGES_VERSION );
    delete_option( $lock );
    return $report;
}

add_action( 'after_switch_theme', 'memento_maybe_setup_builtin_pages' );

add_action( 'admin_init', function () {
    // Only on a real admin page load — never in background AJAX/cron requests.
    if ( wp_doing_ajax() || wp_doing_cron() || ! current_user_can( 'publish_pages' ) ) {
        return;
    }
    $report = memento_maybe_setup_builtin_pages();
    if ( ! $report ) {
        return;
    }

    $created = array_keys( array_filter( $report, function ( $r ) { return in_array( $r, [ 'created', 'template_set' ], true ); } ) );
    if ( $created ) {
        set_transient( 'memento_pages_notice', $created, MINUTE_IN_SECONDS * 10 );
    }
} );

add_action( 'admin_notices', function () {
    $created = get_transient( 'memento_pages_notice' );
    if ( ! $created ) {
        return;
    }
    delete_transient( 'memento_pages_notice' );
    echo '<div class="notice notice-success is-dismissible"><p>' .
        esc_html__( 'Memento Magnets set up these pages:', 'memento-magnets' ) . ' <code>/' .
        implode( '/</code>, <code>/', array_map( 'esc_html', $created ) ) . '/</code></p></div>';
} );
