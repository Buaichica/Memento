<?php
/**
 * Single Blog Post Template
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero (mini) -->
    <div class="page-hero" style="padding-bottom:var(--space-8);">
        <div class="container">
            <?php if ( have_posts() ) : the_post(); ?>
            <div class="post-category"><?php
                $cats = get_the_category();
                if ( $cats ) echo esc_html( $cats[0]->name );
            ?></div>
            <h1 style="max-width:800px;margin:0 auto var(--space-4);"><?php the_title(); ?></h1>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'memento-magnets' ); ?>">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <a href="<?php echo get_post_type_archive_link('post'); ?>"><?php _e( 'Blogs', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php the_title(); ?></span>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <section class="single-post">
        <div class="container">
            <div class="post-layout">

                <!-- Main Article -->
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <header class="post-header">
                        <div class="post-meta">
                            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                <?php echo get_the_date(); ?>
                            </time>
                            <span>·</span>
                            <span><?php echo esc_html( memento_reading_time() ); ?></span>
                            <span>·</span>
                            <span><?php _e( 'By Memento Magnets', 'memento-magnets' ); ?></span>
                        </div>
                    </header>

                    <?php if ( has_post_thumbnail() ) : ?>
                    <figure class="post-featured-image">
                        <?php the_post_thumbnail( 'memento-hero', [ 'loading' => 'eager', 'alt' => get_the_title() ] ); ?>
                    </figure>
                    <?php endif; ?>

                    <div class="post-content" itemprop="articleBody">
                        <?php the_content(); ?>
                    </div>

                    <!-- Post Tags -->
                    <?php
                    $tags = get_the_tags();
                    if ( $tags ) :
                    ?>
                    <div style="margin-top:var(--space-8);padding-top:var(--space-6);border-top:2px solid var(--color-cream);display:flex;flex-wrap:wrap;gap:var(--space-2);align-items:center;">
                        <span style="font-size:var(--text-sm);font-weight:600;color:var(--color-mid-grey);"><?php _e( 'Tags:', 'memento-magnets' ); ?></span>
                        <?php foreach ( $tags as $tag ) : ?>
                        <a href="<?php echo get_tag_link( $tag->term_id ); ?>"
                           style="background:var(--color-cream);color:var(--color-dark-grey);padding:4px 12px;border-radius:var(--radius-full);font-size:var(--text-sm);text-decoration:none;">
                            #<?php echo esc_html( $tag->name ); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Post nav -->
                    <nav style="margin-top:var(--space-10);display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4);" aria-label="<?php esc_attr_e( 'Post navigation', 'memento-magnets' ); ?>">
                        <?php
                        $prev = get_previous_post();
                        $next = get_next_post();
                        if ( $prev ) :
                        ?>
                        <a href="<?php echo get_permalink( $prev ); ?>" style="padding:var(--space-4);background:var(--color-cream);border-radius:var(--radius-lg);text-decoration:none;">
                            <span style="font-size:var(--text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--color-mid-grey);display:block;margin-bottom:var(--space-1);">&larr; <?php _e( 'Previous', 'memento-magnets' ); ?></span>
                            <span style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);"><?php echo get_the_title( $prev ); ?></span>
                        </a>
                        <?php else : ?><div></div><?php endif; ?>
                        <?php if ( $next ) : ?>
                        <a href="<?php echo get_permalink( $next ); ?>" style="padding:var(--space-4);background:var(--color-cream);border-radius:var(--radius-lg);text-align:right;text-decoration:none;">
                            <span style="font-size:var(--text-xs);text-transform:uppercase;letter-spacing:0.08em;color:var(--color-mid-grey);display:block;margin-bottom:var(--space-1);"><?php _e( 'Next', 'memento-magnets' ); ?> &rarr;</span>
                            <span style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);"><?php echo get_the_title( $next ); ?></span>
                        </a>
                        <?php endif; ?>
                    </nav>

                </article>

                <!-- Sidebar -->
                <aside class="post-sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'memento-magnets' ); ?>">
                    <?php if ( is_active_sidebar( 'sidebar-blog' ) ) : ?>
                        <?php dynamic_sidebar( 'sidebar-blog' ); ?>
                    <?php else : ?>
                        <!-- Default sidebar content -->
                        <div style="background:var(--color-cream);border-radius:var(--radius-xl);padding:var(--space-6);margin-bottom:var(--space-6);">
                            <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-4);"><?php _e( 'Create Your Magnets', 'memento-magnets' ); ?></h3>
                            <p style="font-size:var(--text-sm);color:var(--color-mid-grey);margin-bottom:var(--space-4);"><?php _e( 'Turn your favourite photos into beautiful personalised fridge magnets.', 'memento-magnets' ); ?></p>
                            <a href="<?php echo esc_url( home_url( '/custom-magnets/' ) ); ?>" class="btn btn--primary" style="width:100%;justify-content:center;"><?php _e( 'Shop Now', 'memento-magnets' ); ?></a>
                        </div>

                        <!-- Recent posts -->
                        <?php
                        $recent = get_posts( [ 'numberposts' => 4, 'exclude' => [ get_the_ID() ] ] );
                        if ( $recent ) :
                        ?>
                        <div>
                            <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-4);"><?php _e( 'Recent Posts', 'memento-magnets' ); ?></h3>
                            <ul style="list-style:none;display:flex;flex-direction:column;gap:var(--space-3);">
                                <?php foreach ( $recent as $rp ) : ?>
                                <li style="padding-bottom:var(--space-3);border-bottom:1px solid #EDE5E1;">
                                    <a href="<?php echo get_permalink( $rp ); ?>" style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);"><?php echo get_the_title( $rp ); ?></a>
                                    <span style="display:block;font-size:var(--text-xs);color:var(--color-mid-grey);margin-top:2px;"><?php echo get_the_date( '', $rp ); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </aside>

            </div>
        </div>
    </section>

    <!-- Related posts -->
    <?php
    $cats = get_the_category();
    if ( $cats ) :
        $related = get_posts( [
            'category__in'   => [ $cats[0]->term_id ],
            'exclude'        => [ get_the_ID() ],
            'numberposts'    => 3,
            'orderby'        => 'rand',
        ] );
        if ( $related ) :
    ?>
    <section class="section section--alt" aria-labelledby="related-heading">
        <div class="container">
            <div class="section-heading">
                <h2 id="related-heading"><?php _e( 'You Might Also Like', 'memento-magnets' ); ?></h2>
            </div>
            <div class="blog-grid">
                <?php foreach ( $related as $rp ) : setup_postdata( $rp ); ?>
                <article class="blog-card fade-in-up">
                    <?php if ( has_post_thumbnail( $rp ) ) : ?>
                    <a href="<?php echo get_permalink( $rp ); ?>" class="blog-card__image" tabindex="-1" aria-hidden="true">
                        <?php echo get_the_post_thumbnail( $rp->ID, 'memento-blog-card', [ 'loading' => 'lazy', 'alt' => '' ] ); ?>
                    </a>
                    <?php endif; ?>
                    <div class="blog-card__body">
                        <h3 class="blog-card__title" style="font-size:var(--text-lg);">
                            <a href="<?php echo get_permalink( $rp ); ?>"><?php echo get_the_title( $rp ); ?></a>
                        </h3>
                        <div class="blog-card__meta">
                            <time datetime="<?php echo get_the_date( 'c', $rp ); ?>"><?php echo get_the_date( '', $rp ); ?></time>
                        </div>
                    </div>
                </article>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <?php endif; endif; ?>

</main>

<?php get_footer(); ?>
