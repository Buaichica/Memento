<?php
/**
 * Fallback Template
 * WordPress requires this file. All real templates are handled elsewhere.
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <?php if ( is_home() && ! is_front_page() ) : ?>
    <div class="page-hero">
        <div class="container">
            <h1><?php _e( 'Latest Posts', 'memento-magnets' ); ?></h1>
        </div>
    </div>
    <?php endif; ?>

    <section class="section">
        <div class="container">
            <?php if ( have_posts() ) : ?>
            <div class="blog-grid">
                <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-card' ); ?>>
                    <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="blog-card__image" tabindex="-1" aria-hidden="true">
                        <?php the_post_thumbnail( 'memento-blog-card', [ 'loading' => 'lazy', 'alt' => '' ] ); ?>
                    </a>
                    <?php endif; ?>
                    <div class="blog-card__body">
                        <h2 class="blog-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p class="blog-card__excerpt"><?php the_excerpt(); ?></p>
                        <div class="blog-card__meta">
                            <time datetime="<?php echo esc_attr( get_the_date('c') ); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
            <?php else : ?>
            <p><?php _e( 'No content found.', 'memento-magnets' ); ?></p>
            <?php endif; ?>
        </div>
    </section>

</main>

<?php get_footer(); ?>
