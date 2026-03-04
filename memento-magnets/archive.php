<?php
/**
 * Blog Archive Template (Blogs)
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <h1>
                <?php
                if ( is_category() ) {
                    single_cat_title();
                } elseif ( is_tag() ) {
                    single_tag_title( __( 'Posts tagged: ', 'memento-magnets' ) );
                } elseif ( is_author() ) {
                    the_author();
                } elseif ( is_year() ) {
                    get_the_date( 'Y' );
                } elseif ( is_month() ) {
                    get_the_date( 'F Y' );
                } else {
                    _e( 'Blogs', 'memento-magnets' );
                }
                ?>
            </h1>
            <?php if ( is_category() && category_description() ) : ?>
            <p><?php echo category_description(); ?></p>
            <?php else : ?>
            <p><?php _e( 'Tips, ideas and inspiration for personalised magnets and thoughtful gifting.', 'memento-magnets' ); ?></p>
            <?php endif; ?>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Blogs', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <!-- Featured Blog Posts (pinned) -->
    <?php if ( is_home() || is_archive() && ! is_category() && ! is_tag() ) : ?>
    <section class="section section--cream" aria-labelledby="featured-blogs-heading">
        <div class="container">

            <div class="section-heading">
                <h2 id="featured-blogs-heading"><?php _e( 'Featured Articles', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Our most popular reads — tips, gift ideas, and magnet inspiration for New Zealanders.', 'memento-magnets' ); ?></p>
            </div>

            <?php
            $featured_blogs = [
                [
                    'url'      => home_url( '/personalised-magnets-perfect-gift-nz/' ),
                    'category' => __( 'Gift Ideas', 'memento-magnets' ),
                    'title'    => __( '5 Reasons Personalised Photo Magnets Make the Perfect Gift in New Zealand', 'memento-magnets' ),
                    'excerpt'  => __( 'Can\'t find a gift that feels truly personal? Discover why thousands of New Zealanders choose custom photo magnets for every occasion — from birthdays to weddings.', 'memento-magnets' ),
                    'read'     => '5 min read',
                    'date'     => 'March 4, 2026',
                    'emoji'    => '🎁',
                    'featured' => true,
                ],
                [
                    'url'      => home_url( '/how-to-choose-best-photo-for-custom-magnet/' ),
                    'category' => __( 'Tips & Advice', 'memento-magnets' ),
                    'title'    => __( 'How to Choose the Best Photo for Your Custom Fridge Magnet', 'memento-magnets' ),
                    'excerpt'  => __( 'The quality of your photo makes all the difference. Learn exactly what to look for — resolution, lighting, cropping and more — to get a magnet you\'ll love.', 'memento-magnets' ),
                    'read'     => '6 min read',
                    'date'     => 'March 4, 2026',
                    'emoji'    => '📷',
                    'featured' => false,
                ],
                [
                    'url'      => home_url( '/custom-magnets-for-every-occasion-nz/' ),
                    'category' => __( 'Inspiration', 'memento-magnets' ),
                    'title'    => __( 'Custom Photo Magnets for Every Occasion in New Zealand', 'memento-magnets' ),
                    'excerpt'  => __( 'Weddings, Christmas, baby showers, graduations — explore how personalised magnets work beautifully as gifts and keepsakes for every milestone moment in New Zealand.', 'memento-magnets' ),
                    'read'     => '7 min read',
                    'date'     => 'March 4, 2026',
                    'emoji'    => '🎉',
                    'featured' => false,
                ],
            ];
            ?>

            <!-- Hero featured card (first blog) -->
            <div style="margin-bottom:var(--space-6);">
                <a href="<?php echo esc_url( $featured_blogs[0]['url'] ); ?>" class="blog-card fade-in-up" style="display:grid;grid-template-columns:1fr 1fr;gap:0;text-decoration:none;max-width:100%;">
                    <!-- Image placeholder -->
                    <div style="background:var(--gradient-hero);display:flex;align-items:center;justify-content:center;min-height:280px;border-radius:var(--radius-lg) 0 0 var(--radius-lg);">
                        <div style="text-align:center;color:white;">
                            <div style="font-size:5rem;"><?php echo $featured_blogs[0]['emoji']; ?></div>
                            <!-- Replace with real featured image once available -->
                        </div>
                    </div>
                    <!-- Body -->
                    <div class="blog-card__body" style="display:flex;flex-direction:column;justify-content:center;padding:var(--space-10);">
                        <div class="blog-card__category"><?php echo esc_html( $featured_blogs[0]['category'] ); ?></div>
                        <h3 class="blog-card__title" style="font-size:var(--text-2xl);"><?php echo esc_html( $featured_blogs[0]['title'] ); ?></h3>
                        <p class="blog-card__excerpt"><?php echo esc_html( $featured_blogs[0]['excerpt'] ); ?></p>
                        <div class="blog-card__meta">
                            <time><?php echo esc_html( $featured_blogs[0]['date'] ); ?></time>
                            <span>·</span>
                            <span><?php echo esc_html( $featured_blogs[0]['read'] ); ?></span>
                        </div>
                        <div style="margin-top:var(--space-5);">
                            <span class="btn btn--primary"><?php _e( 'Read Article', 'memento-magnets' ); ?></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Two smaller cards (blogs 2 & 3) -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);">
                <?php foreach ( array_slice( $featured_blogs, 1 ) as $blog ) : ?>
                <a href="<?php echo esc_url( $blog['url'] ); ?>" class="blog-card fade-in-up" style="text-decoration:none;">
                    <div style="background:var(--gradient-hero);display:flex;align-items:center;justify-content:center;min-height:180px;">
                        <div style="font-size:4rem;"><?php echo $blog['emoji']; ?></div>
                        <!-- Replace with real image once available -->
                    </div>
                    <div class="blog-card__body">
                        <div class="blog-card__category"><?php echo esc_html( $blog['category'] ); ?></div>
                        <h3 class="blog-card__title" style="font-size:var(--text-lg);"><?php echo esc_html( $blog['title'] ); ?></h3>
                        <p class="blog-card__excerpt"><?php echo esc_html( $blog['excerpt'] ); ?></p>
                        <div class="blog-card__meta">
                            <time><?php echo esc_html( $blog['date'] ); ?></time>
                            <span>·</span>
                            <span><?php echo esc_html( $blog['read'] ); ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
    </section>
    <?php endif; ?>

    <!-- Blog Listing -->
    <section class="blog-archive">
        <div class="container">

            <?php if ( have_posts() ) : ?>

            <h2 style="font-size:var(--text-3xl);margin-bottom:var(--space-8);padding-bottom:var(--space-4);border-bottom:2px solid var(--color-cream);">
                <?php _e( 'All Posts', 'memento-magnets' ); ?>
            </h2>

            <div class="blog-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-card fade-in-up' ); ?>>

                    <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="blog-card__image" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'memento-blog-card', [ 'loading' => 'lazy', 'alt' => '' ] ); ?>
                    </a>
                    <?php endif; ?>

                    <div class="blog-card__body">
                        <div class="blog-card__category">
                            <?php
                            $cats = get_the_category();
                            if ( $cats ) {
                                echo esc_html( $cats[0]->name );
                            }
                            ?>
                        </div>

                        <h2 class="blog-card__title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <p class="blog-card__excerpt"><?php the_excerpt(); ?></p>

                        <div class="blog-card__meta">
                            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                <?php echo get_the_date(); ?>
                            </time>
                            <span>·</span>
                            <span><?php echo esc_html( memento_reading_time() ); ?></span>
                        </div>
                    </div>

                </article>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <nav class="pagination" aria-label="<?php esc_attr_e( 'Blog pagination', 'memento-magnets' ); ?>">
                <?php
                echo paginate_links( [
                    'type'      => 'list',
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                ] );
                ?>
            </nav>

            <?php else : ?>

            <div style="text-align:center;padding:var(--space-16) 0;">
                <p style="font-size:var(--text-xl);margin-bottom:var(--space-6);"><?php _e( 'No blog posts yet — check back soon!', 'memento-magnets' ); ?></p>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary"><?php _e( 'Back to Home', 'memento-magnets' ); ?></a>
            </div>

            <?php endif; ?>

        </div>
    </section>

</main>

<?php get_footer(); ?>
