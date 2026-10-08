<?php
/**
 * Plugin Name:       Memento Personalizer
 * Description:       Secure photo uploads, editing, order fulfilment, production ZIPs and Dropbox sync for Memento Magnets personalised products.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Memento Magnets
 * Text Domain:       memento-personalizer
 * WC requires at least: 7.0
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

define( 'MEMENTO_PZ_VERSION', '1.0.0' );
define( 'MEMENTO_PZ_DB_VERSION', '1' );
define( 'MEMENTO_PZ_FILE', __FILE__ );
define( 'MEMENTO_PZ_DIR', plugin_dir_path( __FILE__ ) );
define( 'MEMENTO_PZ_URL', plugin_dir_url( __FILE__ ) );

require_once MEMENTO_PZ_DIR . 'includes/class-settings.php';
require_once MEMENTO_PZ_DIR . 'includes/class-logger.php';
require_once MEMENTO_PZ_DIR . 'includes/class-installer.php';
require_once MEMENTO_PZ_DIR . 'includes/class-storage.php';
require_once MEMENTO_PZ_DIR . 'includes/class-repository.php';
require_once MEMENTO_PZ_DIR . 'includes/class-product-config.php';
require_once MEMENTO_PZ_DIR . 'includes/class-image-processor.php';
require_once MEMENTO_PZ_DIR . 'includes/class-upload-session.php';
require_once MEMENTO_PZ_DIR . 'includes/class-upload-handler.php';
require_once MEMENTO_PZ_DIR . 'includes/class-file-server.php';
require_once MEMENTO_PZ_DIR . 'includes/class-frontend.php';
require_once MEMENTO_PZ_DIR . 'includes/class-cart.php';
require_once MEMENTO_PZ_DIR . 'includes/class-order-files.php';
require_once MEMENTO_PZ_DIR . 'includes/class-cleanup.php';
require_once MEMENTO_PZ_DIR . 'includes/class-dropbox-client.php';
require_once MEMENTO_PZ_DIR . 'includes/class-dropbox-sync.php';
require_once MEMENTO_PZ_DIR . 'includes/class-zip-export.php';
require_once MEMENTO_PZ_DIR . 'includes/class-admin-order-panel.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MEMENTO_PZ_DIR . 'includes/class-cli.php';
}

register_activation_hook( __FILE__, [ 'Memento_PZ_Installer', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Memento_PZ_Installer', 'deactivate' ] );

// Declare HPOS (custom order tables) compatibility.
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Memento Personalizer requires WooCommerce to be active.', 'memento-personalizer' ) . '</p></div>';
		} );
		return;
	}

	Memento_PZ_Installer::maybe_upgrade();

	Memento_PZ_Product_Config::init();
	Memento_PZ_Upload_Handler::init();
	Memento_PZ_File_Server::init();
	Memento_PZ_Frontend::init();
	Memento_PZ_Cart::init();
	Memento_PZ_Order_Files::init();
	Memento_PZ_Cleanup::init();
	Memento_PZ_Dropbox_Sync::init();
	Memento_PZ_Zip_Export::init();
	Memento_PZ_Admin_Order_Panel::init();
} );

/**
 * Backwards-compatible helper used by the theme before the plugin existed.
 *
 * @param WC_Product|int $product Product object or ID.
 * @return int Required photo count (0 = not personalised).
 */
if ( ! function_exists( 'memento_get_photo_count' ) ) {
	function memento_get_photo_count( $product ) {
		return Memento_PZ_Product_Config::get_required_photos( $product );
	}
}
