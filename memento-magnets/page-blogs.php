<?php
/**
 * Template Name: Blogs Page
 *
 * Used automatically for the page with slug "blogs" (WordPress template hierarchy),
 * so /blogs/ works without changing Settings → Reading.
 *
 * @package memento-magnets
 */

get_header();

$posts_query = new WP_Query( [
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'paged'               => max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) ),
    'ignore_sticky_posts' => false,
] );

get_template_part( 'template-parts/blog-index', null, [ 'posts_query' => $posts_query ] );

get_footer();
