<?php
/**
 * Blog Posts Page Template (home.php)
 * WordPress uses this file for the page set as "Posts page" in Settings → Reading.
 *
 * @package memento-magnets
 */

get_header();

get_template_part( 'template-parts/blog-index', null, [ 'posts_query' => $GLOBALS['wp_query'] ] );

get_footer();
