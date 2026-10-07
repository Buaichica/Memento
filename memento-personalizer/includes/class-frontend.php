<?php
/**
 * Single-product personalisation UI: upload grid, editor modal and review step.
 * Markup keeps the theme's existing `.memento-upload-widget` / `.upload-slot`
 * classes so the current storefront styling continues to apply.
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Frontend {

	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
		add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_widget' ], 28 );
		add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_editor' ], 29 );
		add_action( 'woocommerce_before_add_to_cart_button', [ __CLASS__, 'render_hidden_input' ] );
	}

	/** Required photo count to show initially (variable products: the parent/default value). */
	private static function current_count() {
		global $product;
		// Before the loop this global can be the product slug (a query var), so fall back to the queried ID.
		$p = $product instanceof WC_Product ? $product : wc_get_product( get_the_ID() );
		if ( ! $p ) {
			return 0;
		}
		$count = Memento_PZ_Product_Config::get_required_photos( $p );
		if ( ! $count && $p->is_type( 'variable' ) ) {
			foreach ( $p->get_children() as $child_id ) {
				$count = max( $count, Memento_PZ_Product_Config::get_required_photos( $child_id ) );
			}
		}
		return $count;
	}

	private static function ver( $rel ) {
		return MEMENTO_PZ_VERSION . '.' . (int) @filemtime( MEMENTO_PZ_DIR . $rel ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}

	/** Cart & checkout: photo tiles under each personalised item. */
	public static function enqueue_cart() {
		if ( ! function_exists( 'is_cart' ) || ! ( is_cart() || is_checkout() ) || is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}
		wp_enqueue_style( 'memento-personalizer', MEMENTO_PZ_URL . 'assets/css/personalizer.css', [], self::ver( 'assets/css/personalizer.css' ) );
		wp_enqueue_script( 'memento-personalizer-cart', MEMENTO_PZ_URL . 'assets/js/cart-photos.js', [ 'wp-data' ], self::ver( 'assets/js/cart-photos.js' ), true );
		wp_localize_script( 'memento-personalizer-cart', 'mementoPZCart', [
			/* translators: %d: photo number */
			'photo'      => __( 'Photo %d', 'memento-personalizer' ),
			'lowQuality' => __( 'Low resolution — may print blurry', 'memento-personalizer' ),
		] );
	}

	public static function enqueue() {
		self::enqueue_cart();
		if ( ! is_product() || ! self::current_count() ) {
			return;
		}
		wp_enqueue_style( 'cropperjs', MEMENTO_PZ_URL . 'assets/vendor/cropperjs/cropper.min.css', [], '1.6.2' );
		// File mtime in the version string busts browser/CDN caches on every deploy.
		$ver = function ( $rel ) {
			return MEMENTO_PZ_VERSION . '.' . (int) @filemtime( MEMENTO_PZ_DIR . $rel ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		};
		wp_enqueue_style( 'memento-personalizer', MEMENTO_PZ_URL . 'assets/css/personalizer.css', [ 'cropperjs' ], $ver( 'assets/css/personalizer.css' ) );
		wp_enqueue_script( 'cropperjs', MEMENTO_PZ_URL . 'assets/vendor/cropperjs/cropper.min.js', [], '1.6.2', true );
		wp_enqueue_script( 'memento-personalizer-editor', MEMENTO_PZ_URL . 'assets/js/editor.js', [ 'cropperjs' ], $ver( 'assets/js/editor.js' ), true );
		wp_enqueue_script( 'memento-personalizer', MEMENTO_PZ_URL . 'assets/js/uploader.js', [ 'memento-personalizer-editor', 'jquery' ], $ver( 'assets/js/uploader.js' ), true );

		wp_localize_script( 'memento-personalizer', 'mementoPZ', [
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'productId' => get_the_ID(),
			'config'   => [
				'maxFileMb'        => Memento_PZ_Settings::int( 'max_file_mb' ),
				'minPxRecommended' => Memento_PZ_Settings::int( 'min_px_recommended' ),
				'minPxReject'      => Memento_PZ_Settings::int( 'min_px_reject' ),
				'safeAreaPercent'  => Memento_PZ_Settings::int( 'safe_area_percent' ),
				'requireReview'    => (bool) Memento_PZ_Settings::get( 'require_review_check' ),
				'allowedMimes'     => array_values( (array) Memento_PZ_Settings::get( 'allowed_mimes' ) ),
			],
			'strings'  => [
				'photo'         => __( 'Photo', 'memento-personalizer' ),
				'addPhoto'      => __( 'Add photo', 'memento-personalizer' ),
				'uploading'     => __( 'Uploading…', 'memento-personalizer' ),
				'processing'    => __( 'Processing…', 'memento-personalizer' ),
				'ready'         => __( 'Ready', 'memento-personalizer' ),
				'retry'         => __( 'Retry', 'memento-personalizer' ),
				'edit'          => __( 'Edit / crop', 'memento-personalizer' ),
				'replace'       => __( 'Replace', 'memento-personalizer' ),
				'remove'        => __( 'Remove', 'memento-personalizer' ),
				'moveEarlier'   => __( 'Move earlier', 'memento-personalizer' ),
				'moveLater'     => __( 'Move later', 'memento-personalizer' ),
				'lowQuality'    => __( 'Low resolution — may print blurry', 'memento-personalizer' ),
				/* translators: %d: pixels */
				'cropLow'       => __( 'This crop is only %dpx wide and may print blurry. Try zooming out or using a larger photo.', 'memento-personalizer' ),
				/* translators: %d: pixels */
				'cropGood'      => __( 'Great — %dpx, sharp enough to print.', 'memento-personalizer' ),
				/* translators: %d: pixels */
				'cropTooSmall'  => __( 'This crop is only %dpx wide — too small to print. Please zoom out or choose a larger photo.', 'memento-personalizer' ),
				'invalidType'   => __( 'Please upload a JPG, PNG or WebP photo.', 'memento-personalizer' ),
				/* translators: %d: megabytes */
				'tooLarge'      => __( 'That photo is too large. The maximum is %dMB.', 'memento-personalizer' ),
				'uploadFail'    => __( 'Upload failed. Please check your connection and retry.', 'memento-personalizer' ),
				'sessionFail'   => __( 'We couldn\'t start your photo session. Please refresh the page.', 'memento-personalizer' ),
				/* translators: %d: number of photos */
				'needAll'       => __( 'Please upload all %d photos before adding to cart.', 'memento-personalizer' ),
				'needReview'    => __( 'Please confirm you\'ve reviewed your photos.', 'memento-personalizer' ),
				'busy'          => __( 'Please wait for your photos to finish uploading.', 'memento-personalizer' ),
				/* translators: 1: uploaded count, 2: required count */
				'progress'      => __( '%1$d of %2$d photos ready', 'memento-personalizer' ),
				/* translators: %d: number of photos */
				'reviewLow'     => __( '%d photo(s) are low resolution and may print blurry. You can still continue, or replace them.', 'memento-personalizer' ),
				'chooseOption'  => __( 'Please choose a pack size first.', 'memento-personalizer' ),
				'extraIgnored'  => __( 'Some photos weren\'t added because all slots are full.', 'memento-personalizer' ),
			],
		] );
	}

	public static function render_widget() {
		$count   = self::current_count();
		$product = wc_get_product( get_the_ID() );
		if ( ! $count ) {
			return;
		}
		$safe = Memento_PZ_Settings::int( 'min_px_recommended' );
		?>
		<div class="memento-upload-widget memento-pz" data-photo-count="<?php echo esc_attr( $count ); ?>" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">

			<div class="memento-upload-widget__header">
				<h4 class="memento-upload-widget__title">
					<span class="js-pz-title" data-singular="<?php esc_attr_e( 'Upload Your Photo', 'memento-personalizer' ); ?>" data-plural="<?php esc_attr_e( 'Upload Your %d Photos', 'memento-personalizer' ); ?>">
						<?php
						printf(
							/* translators: %d: number of photos */
							esc_html( _n( 'Upload Your Photo', 'Upload Your %d Photos', $count, 'memento-personalizer' ) ),
							(int) $count
						);
						?>
					</span>
				</h4>
				<div class="upload-progress" aria-hidden="true">
					<div class="upload-progress__bar"><div class="upload-progress__fill" style="width:0%"></div></div>
					<span class="upload-progress__text"><strong class="upload-progress__count">0</strong> / <span class="js-pz-required"><?php echo esc_html( $count ); ?></span></span>
				</div>
			</div>

			<p class="memento-upload-widget__hint">
				<?php
				printf(
					/* translators: %d: recommended pixel size */
					esc_html__( 'Tap a square or choose several photos at once. Each photo is cropped square — use Edit to adjust. For best print quality use photos at least %1$d×%1$dpx.', 'memento-personalizer' ),
					(int) $safe
				);
				?>
			</p>

			<div class="memento-pz-dropzone" tabindex="-1">
				<button type="button" class="memento-pz-pick btn btn--secondary"><?php esc_html_e( 'Choose photos', 'memento-personalizer' ); ?></button>
				<span class="memento-pz-dropzone__hint"><?php esc_html_e( 'or drag & drop them here', 'memento-personalizer' ); ?></span>
				<input type="file" class="memento-pz-multi-input" accept="image/jpeg,image/png,image/webp" multiple hidden>
			</div>

			<ul class="memento-upload-grid" aria-label="<?php esc_attr_e( 'Your photos', 'memento-personalizer' ); ?>"></ul>

			<p class="memento-pz-status" role="status" aria-live="polite"></p>

			<div class="memento-pz-review" hidden>
				<p class="memento-pz-review__summary"></p>
				<label class="memento-pz-review__confirm">
					<input type="checkbox" class="js-pz-review-check">
					<span><?php esc_html_e( 'I\'ve checked my photos and crops — they look good to print.', 'memento-personalizer' ); ?></span>
				</label>
			</div>

			<p class="memento-upload-widget__cta-note js-upload-cta-note">
				<?php
				printf(
					/* translators: %s: number of photos */
					esc_html__( 'Upload all %s photos to enable "Add to Cart"', 'memento-personalizer' ),
					'<span class="js-pz-required">' . esc_html( $count ) . '</span>'
				);
				?>
			</p>
		</div>
		<?php
	}

	public static function render_hidden_input() {
		if ( ! self::current_count() ) {
			return;
		}
		echo '<input type="hidden" name="memento_pz_session" class="memento-pz-session-input" value="">';
	}

	public static function render_editor() {
		if ( ! self::current_count() ) {
			return;
		}
		?>
		<div id="memento-editor-modal" class="memento-pz-editor" role="dialog" aria-modal="true" aria-labelledby="memento-editor-title" hidden>
			<div class="memento-editor-backdrop"></div>
			<div class="memento-editor-panel">
				<h2 id="memento-editor-title" class="memento-editor-title"><?php esc_html_e( 'Edit photo', 'memento-personalizer' ); ?></h2>
				<div class="memento-editor-canvas-wrap memento-pz-safe-area-on">
					<img id="memento-editor-img" src="" alt="<?php esc_attr_e( 'Photo being edited', 'memento-personalizer' ); ?>">
				</div>
				<p class="memento-editor-quality" aria-live="polite"></p>
				<p class="memento-editor-safe-note">
					<label><input type="checkbox" class="js-pz-safe-toggle" checked> <?php esc_html_e( 'Show print-safe area — keep faces and text inside the dashed line.', 'memento-personalizer' ); ?></label>
				</p>
				<div class="memento-editor-controls">
					<button type="button" data-action="zoom-in"><?php esc_html_e( '＋ Zoom in', 'memento-personalizer' ); ?></button>
					<button type="button" data-action="zoom-out"><?php esc_html_e( '－ Zoom out', 'memento-personalizer' ); ?></button>
					<button type="button" data-action="rotate-ccw"><?php esc_html_e( '↺ Rotate left', 'memento-personalizer' ); ?></button>
					<button type="button" data-action="rotate-cw"><?php esc_html_e( '↻ Rotate right', 'memento-personalizer' ); ?></button>
					<button type="button" data-action="reset"><?php esc_html_e( '↩ Reset', 'memento-personalizer' ); ?></button>
					<button type="button" data-action="replace"><?php esc_html_e( '⇄ Replace photo', 'memento-personalizer' ); ?></button>
				</div>
				<div class="memento-editor-actions">
					<button type="button" id="memento-editor-cancel"><?php esc_html_e( 'Cancel', 'memento-personalizer' ); ?></button>
					<button type="button" id="memento-editor-apply" class="btn btn--primary"><?php esc_html_e( 'Save crop', 'memento-personalizer' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}
}
