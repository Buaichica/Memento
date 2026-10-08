<?php
/**
 * My Account dashboard (theme override of woocommerce/templates/myaccount/dashboard.php).
 *
 * Welcome, latest order, quick-action tiles. Keeps WooCommerce's dashboard hooks.
 *
 * @package memento-magnets
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$first_name = $current_user->first_name ? $current_user->first_name : $current_user->display_name;
$latest     = wc_get_orders( [
    'customer' => get_current_user_id(),
    'limit'    => 1,
    'orderby'  => 'date',
    'order'    => 'DESC',
    'status'   => array_keys( wc_get_order_statuses() ),
] );
$latest     = $latest ? $latest[0] : null;

$tiles = [
    [
        'url'   => get_permalink( wc_get_page_id( 'shop' ) ),
        'title' => __( 'Start a new order', 'memento-magnets' ),
        'text'  => __( 'Turn more favourite photos into magnets.', 'memento-magnets' ),
        'icon'  => '<path d="M12 5v14M5 12h14"/>',
        'class' => 'is-primary',
    ],
    [
        'url'   => wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ),
        'title' => __( 'My orders', 'memento-magnets' ),
        'text'  => __( 'Track orders and see your photos.', 'memento-magnets' ),
        'icon'  => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'class' => '',
    ],
    [
        'url'   => wc_get_endpoint_url( 'edit-address', '', wc_get_page_permalink( 'myaccount' ) ),
        'title' => __( 'Addresses', 'memento-magnets' ),
        'text'  => __( 'Update where we send your magnets.', 'memento-magnets' ),
        'icon'  => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'class' => '',
    ],
    [
        'url'   => wc_get_endpoint_url( 'edit-account', '', wc_get_page_permalink( 'myaccount' ) ),
        'title' => __( 'Account details', 'memento-magnets' ),
        'text'  => __( 'Change your name, email or password.', 'memento-magnets' ),
        'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'class' => '',
    ],
];
?>

<div class="mm-dash">

    <div class="mm-dash__welcome">
        <h2><?php printf( /* translators: %s: customer first name */ esc_html__( 'Hi %s!', 'memento-magnets' ), esc_html( $first_name ) ); ?></h2>
        <p><?php esc_html_e( 'Welcome back. Here you can check on your magnets, update your details and start a new order.', 'memento-magnets' ); ?></p>
    </div>

    <?php if ( $latest ) : ?>
        <div class="mm-dash__latest">
            <div class="mm-dash__latest-head">
                <span class="mm-dash__label"><?php esc_html_e( 'Latest order', 'memento-magnets' ); ?></span>
                <?php echo memento_order_status_badge( $latest ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
            </div>
            <div class="mm-dash__latest-body">
                <div>
                    <strong class="mm-dash__order-no"><?php printf( /* translators: %s: order number */ esc_html__( 'Order #%s', 'memento-magnets' ), esc_html( $latest->get_order_number() ) ); ?></strong>
                    <span class="mm-dash__order-meta">
                        <?php
                        $count = $latest->get_item_count();
                        echo esc_html( wc_format_datetime( $latest->get_date_created() ) );
                        echo ' · ';
                        /* translators: %d: number of items */
                        echo esc_html( sprintf( _n( '%d item', '%d items', $count, 'memento-magnets' ), $count ) );
                        echo ' · ';
                        echo wp_kses_post( $latest->get_formatted_order_total() );
                        ?>
                    </span>
                </div>
                <a class="mm-dash__btn" href="<?php echo esc_url( $latest->get_view_order_url() ); ?>"><?php esc_html_e( 'View order', 'memento-magnets' ); ?></a>
            </div>
        </div>
    <?php else : ?>
        <div class="mm-dash__latest mm-dash__latest--empty">
            <p><?php esc_html_e( 'You haven\'t placed an order yet — your magnets will show up here once you do.', 'memento-magnets' ); ?></p>
        </div>
    <?php endif; ?>

    <div class="mm-dash__tiles">
        <?php foreach ( $tiles as $tile ) : ?>
            <a class="mm-dash__tile <?php echo esc_attr( $tile['class'] ); ?>" href="<?php echo esc_url( $tile['url'] ); ?>">
                <span class="mm-dash__tile-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $tile['icon']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?></svg>
                </span>
                <span class="mm-dash__tile-title"><?php echo esc_html( $tile['title'] ); ?></span>
                <span class="mm-dash__tile-text"><?php echo esc_html( $tile['text'] ); ?></span>
            </a>
        <?php endforeach; ?>
    </div>

</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
