<?php
/**
 * Template Part: Products Grid
 * WooCommerce-aware product display with fallback cards.
 */
?>
<section class="section" id="products" aria-labelledby="products-heading">
    <div class="container">

        <div class="section-heading">
            <h2 id="products-heading"><?php _e( 'Browse Our Collections', 'memento-magnets' ); ?></h2>
            <p><?php _e( 'From family portraits to pet photos — turn your favourite moments into beautiful fridge magnets.', 'memento-magnets' ); ?></p>
        </div>

        <?php if ( class_exists( 'WooCommerce' ) ) : ?>

            <?php
            // WooCommerce product loop
            $args = [
                'post_type'      => 'product',
                'posts_per_page' => 4,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
                'post_status'    => 'publish',
                'meta_query'     => [ [ 'key' => '_visibility', 'value' => [ 'catalog', 'visible' ], 'compare' => 'IN' ] ],
            ];
            $products = new WP_Query( $args );
            ?>

            <?php if ( $products->have_posts() ) : ?>
            <div class="products-grid">
                <?php while ( $products->have_posts() ) : $products->the_post(); global $product; ?>
                <article class="product-card fade-in-up">
                    <a href="<?php the_permalink(); ?>" class="product-card__image" aria-label="<?php the_title_attribute(); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'memento-product', [ 'loading' => 'lazy', 'alt' => get_the_title() ] ); ?>
                        <?php else : ?>
                            <div style="width:100%;height:100%;background:var(--gradient-subtle);display:flex;align-items:center;justify-content:center;">
                                <span style="font-size:3rem;">🧲</span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $product && $product->is_on_sale() ) : ?>
                        <span class="product-card__badge"><?php _e( 'Sale', 'memento-magnets' ); ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="product-card__body">
                        <h3 class="product-card__name">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        <p class="product-card__desc">
                            <?php echo wp_trim_words( get_the_excerpt(), 15 ); ?>
                        </p>
                        <div class="product-card__footer">
                            <?php if ( $product ) : ?>
                            <div class="product-card__price">
                                <?php if ( $product->is_on_sale() ) : ?>
                                <span class="was"><?php echo $product->get_regular_price_html(); ?></span>
                                <?php endif; ?>
                                <?php echo $product->get_price_html(); ?>
                            </div>
                            <?php echo do_shortcode( '[add_to_cart id="' . get_the_ID() . '" show_price="false" style="" class="btn btn--primary"]' ); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <?php else : ?>
                <?php memento_product_placeholder_cards(); ?>
            <?php endif; ?>

        <?php else : ?>
            <?php memento_product_placeholder_cards(); ?>
        <?php endif; ?>

    </div>
</section>

<?php
/**
 * Placeholder product cards (no WooCommerce or no products).
 */
function memento_product_placeholder_cards() {
    $products = [
        [
            'name'  => 'The Keepsake Magnets',
            'pack'  => '3 Pack',
            'desc'  => 'A sweet trio of your most treasured photos. Perfect as a heartfelt personal keepsake.',
            'price' => '$18 NZD',
            'badge' => 'Popular',
            'color' => '#FFE4EF',
            'count' => 3,
        ],
        [
            'name'  => 'The Story Set Magnets',
            'pack'  => '6 Pack',
            'desc'  => 'Tell your story six photos at a time. Great for gifting friends and family.',
            'price' => '$34 NZD',
            'badge' => '',
            'color' => '#FFF0E6',
            'count' => 6,
        ],
        [
            'name'  => 'The Memory Wall Magnets',
            'pack'  => '9 Pack',
            'desc'  => 'Fill your fridge with memories. The perfect gallery wall of your favourite moments.',
            'price' => '$45 NZD',
            'badge' => 'Best Seller',
            'color' => '#F0E6FF',
            'count' => 9,
        ],
        [
            'name'  => 'The Legacy Set Magnets',
            'pack'  => '12 Pack',
            'desc'  => 'The ultimate collection for memory lovers. Share a full year of moments.',
            'price' => '$55 NZD',
            'badge' => 'Best Value',
            'color' => '#E6F4FF',
            'count' => 12,
        ],
    ];
    ?>
    <div class="products-grid">
        <?php foreach ( $products as $p ) : ?>
        <article class="product-card fade-in-up">
            <div class="product-card__image" style="background:<?php echo esc_attr( $p['color'] ); ?>;">
                <!-- Magnet grid visual -->
                <div class="product-placeholder-visual">
                    <?php for ( $i = 0; $i < $p['count']; $i++ ) : ?>
                    <div class="placeholder-magnet"></div>
                    <?php endfor; ?>
                </div>
                <?php if ( $p['badge'] ) : ?>
                <span class="product-card__badge"><?php echo esc_html( $p['badge'] ); ?></span>
                <?php endif; ?>
            </div>
            <div class="product-card__body">
                <h3 class="product-card__name"><?php echo esc_html( $p['name'] ); ?></h3>
                <p class="product-card__pack-label"><?php echo esc_html( $p['pack'] ); ?></p>
                <p class="product-card__desc"><?php echo esc_html( $p['desc'] ); ?></p>
                <div class="product-card__footer">
                    <div class="product-card__price"><?php echo esc_html( $p['price'] ); ?></div>
                    <a href="<?php echo esc_url( home_url( '/custom-magnets/' ) ); ?>" class="btn btn--primary">
                        <?php _e( 'Order Now', 'memento-magnets' ); ?>
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php
}
