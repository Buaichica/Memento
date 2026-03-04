<?php
/**
 * Template Part: Customer Reviews
 * Horizontally scrolling testimonial cards.
 */

$reviews = [
    [
        'stars'    => 5,
        'text'     => 'Absolutely love my magnets! The quality is outstanding and they arrived so quickly. Already ordered a second set for Mum\'s birthday.',
        'name'     => 'Sarah M.',
        'location' => 'Auckland, NZ',
        'initial'  => 'S',
    ],
    [
        'stars'    => 5,
        'text'     => 'Such a unique and personal gift idea. I got a set made from our family beach trip photos — they look amazing on the fridge!',
        'name'     => 'James T.',
        'location' => 'Hamilton, NZ',
        'initial'  => 'J',
    ],
    [
        'stars'    => 5,
        'text'     => 'Ordered wedding photo magnets as favours for our guests. Everyone commented on how beautiful they were. Great customer service too!',
        'name'     => 'Emma R.',
        'location' => 'Wellington, NZ',
        'initial'  => 'E',
    ],
    [
        'stars'    => 5,
        'text'     => 'My kids are obsessed — we have our puppy\'s face on the fridge now. The colours are so vibrant and the magnets are really strong.',
        'name'     => 'Rachel K.',
        'location' => 'Dunedin, NZ',
        'initial'  => 'R',
    ],
    [
        'stars'    => 5,
        'text'     => 'Fast shipping to Christchurch, great packaging and the magnets look exactly like the photo. Will definitely order again!',
        'name'     => 'Tom W.',
        'location' => 'Christchurch, NZ',
        'initial'  => 'T',
    ],
];
?>
<section class="section section--alt" id="reviews" aria-labelledby="reviews-heading">
    <div class="container">

        <div class="section-heading">
            <h2 id="reviews-heading"><?php _e( 'What Our Customers Say', 'memento-magnets' ); ?></h2>
            <p><?php _e( 'Lots of happy customers across New Zealand love their Memento Magnets.', 'memento-magnets' ); ?></p>
        </div>

        <div class="reviews-scroll" role="region" aria-label="<?php esc_attr_e( 'Customer reviews', 'memento-magnets' ); ?>" tabindex="0">
            <div class="reviews-track">
                <?php foreach ( $reviews as $review ) : ?>
                <article class="review-card">
                    <div class="review-stars" aria-label="<?php echo esc_attr( sprintf( _n( '%d star', '%d stars', $review['stars'], 'memento-magnets' ), $review['stars'] ) ); ?>">
                        <?php for ( $i = 0; $i < $review['stars']; $i++ ) : ?>
                        <span aria-hidden="true">★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="review-text"><?php echo esc_html( $review['text'] ); ?></p>
                    <div class="review-author">
                        <div class="review-avatar" aria-hidden="true"><?php echo esc_html( $review['initial'] ); ?></div>
                        <div class="review-author-info">
                            <strong><?php echo esc_html( $review['name'] ); ?></strong>
                            <span><?php echo esc_html( $review['location'] ); ?></span>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>


    </div>
</section>
