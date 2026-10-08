<?php
/**
 * NZ Post tracking number: stored on the order (set in the Production panel),
 * added to the "Completed order" email and the customer's My Account order page.
 *
 * Order meta: _memento_tracking_number
 *
 * @package memento-personalizer
 */

defined( 'ABSPATH' ) || exit;

class Memento_PZ_Shipping_Tracking {

	const META = '_memento_tracking_number';

	public static function init() {
		add_action( 'woocommerce_email_before_order_table', [ __CLASS__, 'email_block' ], 5, 4 );
		add_filter( 'woocommerce_email_subject_customer_completed_order', [ __CLASS__, 'email_subject' ], 10, 2 );
		add_filter( 'woocommerce_email_heading_customer_completed_order', [ __CLASS__, 'email_heading' ], 10, 2 );
		add_action( 'woocommerce_order_details_before_order_table', [ __CLASS__, 'account_block' ] );
	}

	/** Uppercase letters and digits only (NZ Post numbers look like 00794210392477021 or AB123456789NZ). */
	public static function sanitize( $value ) {
		return substr( preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) $value ) ), 0, 40 );
	}

	public static function number( $order ) {
		return $order instanceof WC_Order ? (string) $order->get_meta( self::META ) : '';
	}

	public static function url( $number ) {
		return 'https://www.nzpost.co.nz/tools/tracking/item/' . rawurlencode( $number );
	}

	public static function email_block( $order, $sent_to_admin, $plain_text, $email = null ) {
		if ( $sent_to_admin || ! $email || 'customer_completed_order' !== $email->id ) {
			return;
		}
		$number = self::number( $order );
		if ( ! $number ) {
			return;
		}
		$url = self::url( $number );

		if ( $plain_text ) {
			echo esc_html__( 'Your magnets are on their way!', 'memento-personalizer' ) . "\n";
			/* translators: %s: tracking number */
			echo esc_html( sprintf( __( 'Shipped with NZ Post. Tracking number: %s', 'memento-personalizer' ), $number ) ) . "\n";
			/* translators: %s: tracking URL */
			echo esc_html( sprintf( __( 'Track your parcel: %s', 'memento-personalizer' ), $url ) ) . "\n\n";
			return;
		}
		?>
		<div style="margin:0 0 24px;padding:16px 20px;border:1px solid #FFD0E2;border-radius:12px;background:#FFF5F9;">
			<p style="margin:0 0 6px;font-size:16px;font-weight:bold;"><?php esc_html_e( 'Your magnets are on their way!', 'memento-personalizer' ); ?></p>
			<p style="margin:0 0 12px;">
				<?php esc_html_e( 'Shipped with NZ Post. Tracking number:', 'memento-personalizer' ); ?>
				<strong><?php echo esc_html( $number ); ?></strong>
			</p>
			<a href="<?php echo esc_url( $url ); ?>" style="display:inline-block;padding:10px 20px;border-radius:999px;background:#FF5FA0;color:#ffffff;font-weight:bold;text-decoration:none;"><?php esc_html_e( 'Track your parcel', 'memento-personalizer' ); ?></a>
		</div>
		<?php
	}

	public static function email_subject( $subject, $order ) {
		return self::number( $order ) ? __( 'Your Memento Magnets order is on its way', 'memento-personalizer' ) : $subject;
	}

	public static function email_heading( $heading, $order ) {
		return self::number( $order ) ? __( 'Your order is on its way', 'memento-personalizer' ) : $heading;
	}

	public static function account_block( $order ) {
		$number = self::number( $order );
		if ( ! $number ) {
			return;
		}
		?>
		<div class="woocommerce-info memento-tracking">
			<?php
			printf(
				/* translators: 1: tracking number, 2: tracking link */
				esc_html__( 'Shipped with NZ Post. Tracking number: %1$s — %2$s', 'memento-personalizer' ),
				'<strong>' . esc_html( $number ) . '</strong>',
				'<a href="' . esc_url( self::url( $number ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Track your parcel', 'memento-personalizer' ) . '</a>'
			);
			?>
		</div>
		<?php
	}
}
