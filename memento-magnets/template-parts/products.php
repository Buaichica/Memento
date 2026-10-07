<?php
/**
 * Template Part: Products Grid ("Browse Our Collections")
 * Uses the same cards and pack-size order as the Products page (inc/product-cards.php).
 */
?>
<section class="section" id="products" aria-labelledby="products-heading">
    <div class="container">

        <div class="section-heading">
            <h2 id="products-heading"><?php _e( 'Browse Our Collections', 'memento-magnets' ); ?></h2>
            <p><?php _e( 'From family portraits to pet photos — turn your favourite moments into beautiful fridge magnets.', 'memento-magnets' ); ?></p>
        </div>

        <?php
        $cards = [];
        if ( class_exists( 'WooCommerce' ) ) {
            $products = new WP_Query( [
                'post_type'         => 'product',
                'post_status'       => 'publish',
                'posts_per_page'    => 4,
                'memento_pack_sort' => true,
                'no_found_rows'     => true,
                'tax_query'         => [ [
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'exclude-from-catalog',
                    'operator' => 'NOT IN',
                ] ],
            ] );
            foreach ( $products->posts as $post_obj ) {
                $product = wc_get_product( $post_obj );
                if ( $product ) {
                    $cards[] = memento_product_card_data( $product );
                }
            }
        }
        if ( ! $cards ) {
            $cards = memento_product_placeholder_cards();
        }
        ?>

        <div class="mm-card-grid">
            <?php foreach ( $cards as $card ) { memento_render_product_card( $card ); } ?>
        </div>

        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <p class="mm-card-grid__more">
            <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="btn btn--secondary"><?php _e( 'View all products', 'memento-magnets' ); ?></a>
        </p>
        <?php endif; ?>

    </div>
</section>

<?php
/**
 * Static cards shown only when WooCommerce is inactive or has no products yet.
 */
function memento_product_placeholder_cards() {
    $url   = home_url( '/custom-magnets/' );
    $cards = [];
    foreach ( [
        [ 'The Keepsake Magnets — 3 Pack', 18, 3 ],
        [ 'The Story Set Magnets — 6 Pack', 34, 6 ],
        [ 'The Memory Wall Magnets — 9 Pack', 45, 9 ],
        [ 'The Legacy Set Magnets — 12 Pack', 55, 12 ],
    ] as list( $name, $price, $count ) ) {
        list( $badge, $desc ) = memento_card_defaults( $count );
        $cards[] = [
            'url'    => $url,
            'name'   => $name,
            'desc'   => $desc,
            'price'  => '$' . number_format( $price, 2 ),
            'badge'  => $badge,
            'image'  => memento_magnet_tiles_visual( $count ),
            'button' => __( 'Upload Now', 'memento-magnets' ),
        ];
    }
    return $cards;
}
