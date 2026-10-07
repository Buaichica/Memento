<?php
/**
 * Template Part: Blog index (hero, featured articles, all posts).
 * Shared by home.php (when /blogs/ is the "Posts page") and page-blogs.php.
 *
 * @param array $args { @type WP_Query $posts_query Posts to list under "All Posts". }
 * @package memento-magnets
 */

$posts_query = ( isset( $args['posts_query'] ) && $args['posts_query'] instanceof WP_Query ) ? $args['posts_query'] : $GLOBALS['wp_query'];

// Dynamically find the 3 blog pages by their template names
$blog_templates = [
    'page-blog-personalised-magnets-perfect-gift-nz.php',
    'page-blog-how-to-choose-best-photo-for-custom-magnet.php',
    'page-blog-custom-magnets-every-occasion-nz.php',
];

$featured_blogs = [];
foreach ( $blog_templates as $template ) {
    $pages = get_pages( [
        'meta_key'   => '_wp_page_template',
        'meta_value' => $template,
        'number'     => 1,
    ] );
    if ( ! empty( $pages ) ) {
        $featured_blogs[] = [
            'url'     => get_permalink( $pages[0]->ID ),
            'title'   => $pages[0]->post_title,
            'excerpt' => wp_trim_words( $pages[0]->post_content, 25, '…' ),
        ];
    }
}

// Fallback: hardcode if pages not found in DB yet
if ( empty( $featured_blogs ) ) {
    $featured_blogs = [
        [
            'url'     => home_url( '/personalised-magnets-perfect-gift-nz/' ),
            'title'   => __( '5 Reasons Personalised Photo Magnets Make the Perfect Gift in New Zealand', 'memento-magnets' ),
            'excerpt' => __( 'Can\'t find a gift that feels truly personal? Discover why thousands of New Zealanders choose custom photo magnets for every occasion.', 'memento-magnets' ),
        ],
        [
            'url'     => home_url( '/how-to-choose-best-photo-for-custom-magnet/' ),
            'title'   => __( 'How to Choose the Best Photo for Your Custom Fridge Magnet', 'memento-magnets' ),
            'excerpt' => __( 'The quality of your photo makes all the difference. Learn exactly what to look for — resolution, lighting, cropping and more.', 'memento-magnets' ),
        ],
        [
            'url'     => home_url( '/custom-magnets-for-every-occasion-nz/' ),
            'title'   => __( 'Custom Photo Magnets for Every Occasion in New Zealand', 'memento-magnets' ),
            'excerpt' => __( 'Weddings, Christmas, baby showers, graduations — personalised magnets work beautifully as gifts for every milestone in New Zealand.', 'memento-magnets' ),
        ],
    ];
}

$blog_meta = [
    [ 'emoji' => '🎁', 'category' => 'Gift Ideas',    'read' => '5 min read' ],
    [ 'emoji' => '📷', 'category' => 'Tips & Advice', 'read' => '6 min read' ],
    [ 'emoji' => '🎉', 'category' => 'Inspiration',   'read' => '7 min read' ],
];
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Blogs', 'memento-magnets' ); ?></h1>
            <p><?php _e( 'Tips, ideas and inspiration for personalised magnets and thoughtful gifting across New Zealand.', 'memento-magnets' ); ?></p>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Blogs', 'memento-magnets' ); ?></span>
            </nav>
        </div>
    </div>

    <!-- Featured Blog Posts -->
    <section class="section section--cream" aria-labelledby="featured-blogs-heading">
        <div class="container">

            <div class="section-heading">
                <h2 id="featured-blogs-heading"><?php _e( 'Featured Articles', 'memento-magnets' ); ?></h2>
                <p><?php _e( 'Our most popular reads — tips, gift ideas, and magnet inspiration for New Zealanders.', 'memento-magnets' ); ?></p>
            </div>

            <!-- Hero featured card (first blog) -->
            <div style="margin-bottom:var(--space-6);">
                <a href="<?php echo esc_url( $featured_blogs[0]['url'] ); ?>"
                   class="blog-card fade-in-up"
                   style="display:grid;grid-template-columns:1fr 1fr;gap:0;text-decoration:none;">
                    <div style="background:var(--gradient-hero);display:flex;align-items:center;justify-content:center;min-height:280px;border-radius:var(--radius-lg) 0 0 var(--radius-lg);">
                        <div style="text-align:center;">
                            <div style="font-size:5rem;"><?php echo $blog_meta[0]['emoji']; ?></div>
                            <!-- Replace with a real featured image once available -->
                        </div>
                    </div>
                    <div class="blog-card__body" style="display:flex;flex-direction:column;justify-content:center;padding:var(--space-10);">
                        <div class="blog-card__category"><?php echo esc_html( $blog_meta[0]['category'] ); ?></div>
                        <h3 class="blog-card__title" style="font-size:var(--text-2xl);">
                            <?php echo esc_html( $featured_blogs[0]['title'] ); ?>
                        </h3>
                        <p class="blog-card__excerpt"><?php echo esc_html( $featured_blogs[0]['excerpt'] ); ?></p>
                        <div class="blog-card__meta">
                            <span><?php echo esc_html( $blog_meta[0]['read'] ); ?></span>
                        </div>
                        <div style="margin-top:var(--space-5);">
                            <span class="btn btn--primary"><?php _e( 'Read Article', 'memento-magnets' ); ?></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Two smaller cards (blogs 2 & 3) -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-6);">
                <?php foreach ( [ 1, 2 ] as $i ) :
                    if ( empty( $featured_blogs[ $i ] ) ) continue;
                ?>
                <a href="<?php echo esc_url( $featured_blogs[ $i ]['url'] ); ?>"
                   class="blog-card fade-in-up"
                   style="text-decoration:none;">
                    <div style="background:var(--gradient-hero);display:flex;align-items:center;justify-content:center;min-height:180px;">
                        <div style="font-size:4rem;"><?php echo $blog_meta[ $i ]['emoji']; ?></div>
                        <!-- Replace with a real featured image once available -->
                    </div>
                    <div class="blog-card__body">
                        <div class="blog-card__category"><?php echo esc_html( $blog_meta[ $i ]['category'] ); ?></div>
                        <h3 class="blog-card__title" style="font-size:var(--text-lg);">
                            <?php echo esc_html( $featured_blogs[ $i ]['title'] ); ?>
                        </h3>
                        <p class="blog-card__excerpt"><?php echo esc_html( $featured_blogs[ $i ]['excerpt'] ); ?></p>
                        <div class="blog-card__meta">
                            <span><?php echo esc_html( $blog_meta[ $i ]['read'] ); ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

        </div>
    </section>

    <!-- WordPress Posts Loop -->
    <section class="blog-archive">
        <div class="container">

            <?php if ( $posts_query->have_posts() ) : ?>

            <h2 style="font-size:var(--text-3xl);margin-bottom:var(--space-8);padding-bottom:var(--space-4);border-bottom:2px solid var(--color-cream);">
                <?php _e( 'All Posts', 'memento-magnets' ); ?>
            </h2>

            <div class="blog-grid">
                <?php while ( $posts_query->have_posts() ) : $posts_query->the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-card fade-in-up' ); ?>>

                    <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="blog-card__image" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'memento-blog-card', [ 'loading' => 'lazy', 'alt' => '' ] ); ?>
                    </a>
                    <?php endif; ?>

                    <div class="blog-card__body">
                        <div class="blog-card__category">
                            <?php $cats = get_the_category(); if ( $cats ) echo esc_html( $cats[0]->name ); ?>
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
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <nav class="pagination" aria-label="<?php esc_attr_e( 'Blog pagination', 'memento-magnets' ); ?>">
                <?php
                $pagination_args = [ 'type' => 'list', 'prev_text' => '&larr;', 'next_text' => '&rarr;', 'total' => $posts_query->max_num_pages ];
                if ( ! $posts_query->is_main_query() ) {
                    // Page template (/blogs/): paginate with /blogs/page/2/.
                    $pagination_args['current'] = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) );
                    $pagination_args['base']    = trailingslashit( get_permalink() ) . '%_%';
                    $pagination_args['format']  = 'page/%#%/';
                }
                echo paginate_links( $pagination_args );
                ?>
            </nav>

            <?php endif; ?>
            <!-- No "No posts" message here — the featured section above always shows content -->

        </div>
    </section>

</main>
