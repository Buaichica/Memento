<?php
/**
 * Built-in Contact form handler (used by page-contact.php when Contact Form 7
 * isn't configured).
 *
 * - Validates input, blocks spam (honeypot + per-visitor rate limit).
 * - Emails the site owner (Settings → General → Administration Email Address,
 *   or the `memento_contact_recipient` filter) with Reply-To set to the customer.
 * - Saves every message in WP Admin → Messages, so nothing is lost if email
 *   delivery fails.
 * - Post/Redirect/Get: a refresh after sending never re-sends.
 *
 * @package memento-magnets
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const MEMENTO_MESSAGE_CPT = 'memento_message';

/** Subject choices shown in the form (key => label). */
function memento_contact_subjects() {
    return [
        'order'    => __( 'Order enquiry', 'memento-magnets' ),
        'photos'   => __( 'Help with my photos', 'memento-magnets' ),
        'shipping' => __( 'Shipping question', 'memento-magnets' ),
        'return'   => __( 'Return / refund', 'memento-magnets' ),
        'custom'   => __( 'Custom or bulk order', 'memento-magnets' ),
        'other'    => __( 'Other', 'memento-magnets' ),
    ];
}

/**
 * Contact Form 7 form ID to use instead of the built-in form, or 0.
 * Set it with: add_filter( 'memento_cf7_form_id', fn() => 123 );
 * or in Appearance → Customize (theme mod `memento_cf7_form_id`).
 */
function memento_contact_cf7_id() {
    $id = (int) apply_filters( 'memento_cf7_form_id', (int) get_theme_mod( 'memento_cf7_form_id', 0 ) );
    if ( $id && function_exists( 'wpcf7_contact_form' ) && 'wpcf7_contact_form' === get_post_type( $id ) ) {
        return $id;
    }
    return 0;
}

/** Per-request form state read by page-contact.php. */
function memento_contact_state( $set = null ) {
    static $state = [ 'errors' => [], 'values' => [], 'failed' => false ];
    if ( null !== $set ) {
        $state = array_merge( $state, $set );
    }
    return $state;
}

/* ── Saved messages (WP Admin → Messages) ───────────────────── */

add_action( 'init', function () {
    register_post_type( MEMENTO_MESSAGE_CPT, [
        'labels'          => [
            'name'          => __( 'Messages', 'memento-magnets' ),
            'singular_name' => __( 'Message', 'memento-magnets' ),
            'all_items'     => __( 'All Messages', 'memento-magnets' ),
            'edit_item'     => __( 'Message', 'memento-magnets' ),
            'search_items'  => __( 'Search Messages', 'memento-magnets' ),
            'not_found'     => __( 'No messages yet.', 'memento-magnets' ),
        ],
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => true,
        'menu_icon'       => 'dashicons-email-alt',
        'menu_position'   => 26,
        'supports'        => [ 'title', 'editor' ],
        'capability_type' => 'post',
        'capabilities'    => [ 'create_posts' => 'do_not_allow' ], // Messages only come from the form.
        'map_meta_cap'    => true,
        'rewrite'         => false,
        'query_var'       => false,
    ] );
} );

add_filter( 'manage_' . MEMENTO_MESSAGE_CPT . '_posts_columns', function () {
    return [
        'cb'          => '<input type="checkbox">',
        'title'       => __( 'Subject', 'memento-magnets' ),
        'mm_from'     => __( 'From', 'memento-magnets' ),
        'mm_order'    => __( 'Order', 'memento-magnets' ),
        'mm_emailed'  => __( 'Emailed', 'memento-magnets' ),
        'date'        => __( 'Received', 'memento-magnets' ),
    ];
} );

add_action( 'manage_' . MEMENTO_MESSAGE_CPT . '_posts_custom_column', function ( $column, $post_id ) {
    if ( 'mm_from' === $column ) {
        $email = get_post_meta( $post_id, '_mm_email', true );
        echo esc_html( get_post_meta( $post_id, '_mm_name', true ) ) . '<br><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
    } elseif ( 'mm_order' === $column ) {
        echo esc_html( get_post_meta( $post_id, '_mm_order', true ) ?: '–' );
    } elseif ( 'mm_emailed' === $column ) {
        echo get_post_meta( $post_id, '_mm_emailed', true ) ? '✓' : '<span style="color:#b32d2e">' . esc_html__( 'Not sent', 'memento-magnets' ) . '</span>';
    }
}, 10, 2 );

/* ── Handle submissions ─────────────────────────────────────── */

add_action( 'template_redirect', function () {
    if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['contact_nonce'] ) ) {
        return;
    }

    $values = [
        'name'    => sanitize_text_field( wp_unslash( $_POST['contact_name'] ?? '' ) ),
        'email'   => sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) ),
        'subject' => sanitize_key( wp_unslash( $_POST['contact_subject'] ?? '' ) ),
        'order'   => sanitize_text_field( wp_unslash( $_POST['contact_order'] ?? '' ) ),
        'message' => sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ?? '' ) ),
    ];
    $errors   = [];
    $subjects = memento_contact_subjects();

    if ( ! wp_verify_nonce( sanitize_key( $_POST['contact_nonce'] ), 'contact_form_submit' ) ) {
        $errors['form'] = __( 'Your session expired. Please send your message again.', 'memento-magnets' );
    }

    // Honeypot: real visitors never see or fill this field. Pretend success to bots.
    if ( ! empty( $_POST['contact_website'] ) ) {
        wp_safe_redirect( add_query_arg( 'sent', '1', get_permalink() ) . '#contact-form' );
        exit;
    }

    if ( '' === $values['name'] || mb_strlen( $values['name'] ) > 100 ) {
        $errors['name'] = __( 'Please enter your name.', 'memento-magnets' );
    }
    if ( ! is_email( $values['email'] ) ) {
        $errors['email'] = __( 'Please enter a valid email address.', 'memento-magnets' );
    }
    if ( ! isset( $subjects[ $values['subject'] ] ) ) {
        $errors['subject'] = __( 'Please choose a topic.', 'memento-magnets' );
    }
    if ( mb_strlen( $values['order'] ) > 40 ) {
        $errors['order'] = __( 'That order number looks too long.', 'memento-magnets' );
    }
    if ( mb_strlen( trim( $values['message'] ) ) < 10 ) {
        $errors['message'] = __( 'Please write a little more so we can help (at least 10 characters).', 'memento-magnets' );
    } elseif ( mb_strlen( $values['message'] ) > 5000 ) {
        $errors['message'] = __( 'Please keep your message under 5,000 characters.', 'memento-magnets' );
    }

    // Rate limit: 5 messages per visitor per hour.
    $ip       = class_exists( 'WC_Geolocation' ) ? WC_Geolocation::get_ip_address() : ( $_SERVER['REMOTE_ADDR'] ?? '' ); // phpcs:ignore
    $rate_key = 'mm_contact_' . md5( (string) $ip );
    $sent     = (int) get_transient( $rate_key );
    if ( ! $errors && $sent >= 5 ) {
        $errors['form'] = __( 'You\'ve sent several messages already. Please wait a little while, or email us directly.', 'memento-magnets' );
    }

    if ( $errors ) {
        // Show what the customer typed (even an invalid email) so they can correct it.
        $values['email'] = sanitize_text_field( wp_unslash( $_POST['contact_email'] ?? '' ) );
        memento_contact_state( [ 'errors' => $errors, 'values' => $values ] );
        return; // Re-render the page with errors and the customer's text kept.
    }

    $topic = $subjects[ $values['subject'] ];

    // 1) Save first, so the message survives any email problem.
    $post_id = wp_insert_post( [
        'post_type'    => MEMENTO_MESSAGE_CPT,
        'post_status'  => 'private',
        'post_title'   => $topic . ' — ' . $values['name'],
        'post_content' => $values['message'],
        'meta_input'   => [
            '_mm_name'    => $values['name'],
            '_mm_email'   => $values['email'],
            '_mm_subject' => $values['subject'],
            '_mm_order'   => $values['order'],
        ],
    ], true );

    // 2) Email the shop.
    $to   = apply_filters( 'memento_contact_recipient', get_option( 'admin_email' ) );
    $body = sprintf(
        "%s\n\n%s: %s\n%s: %s\n%s: %s\n%s: %s\n\n%s\n%s\n",
        __( 'New message from the Memento Magnets contact form.', 'memento-magnets' ),
        __( 'Name', 'memento-magnets' ), $values['name'],
        __( 'Email', 'memento-magnets' ), $values['email'],
        __( 'Topic', 'memento-magnets' ), $topic,
        __( 'Order', 'memento-magnets' ), $values['order'] ? $values['order'] : '–',
        __( 'Message:', 'memento-magnets' ),
        $values['message']
    );
    if ( ! is_wp_error( $post_id ) ) {
        $body .= "\n" . __( 'Also saved in WP Admin → Messages:', 'memento-magnets' ) . ' ' . admin_url( 'post.php?post=' . (int) $post_id . '&action=edit' ) . "\n";
    }
    $mailed = wp_mail(
        $to,
        /* translators: 1: topic, 2: customer name */
        sprintf( __( '[Memento Magnets] %1$s from %2$s', 'memento-magnets' ), $topic, $values['name'] ),
        $body,
        [ 'Reply-To: ' . str_replace( [ "\r", "\n" ], '', $values['name'] ) . ' <' . $values['email'] . '>' ]
    );
    if ( ! is_wp_error( $post_id ) ) {
        update_post_meta( $post_id, '_mm_emailed', $mailed ? 1 : 0 );
    }

    if ( is_wp_error( $post_id ) && ! $mailed ) {
        // Neither saved nor emailed: tell the customer honestly and keep their text.
        memento_contact_state( [
            'values' => $values,
            'failed' => true,
            'errors' => [ 'form' => __( 'Sorry — we couldn\'t send your message just now. Please try again, or email us directly.', 'memento-magnets' ) ],
        ] );
        return;
    }

    set_transient( $rate_key, $sent + 1, HOUR_IN_SECONDS );
    wp_safe_redirect( add_query_arg( 'sent', '1', get_permalink() ) . '#contact-form' );
    exit;
} );
