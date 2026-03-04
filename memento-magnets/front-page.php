<?php
/**
 * Homepage Template
 *
 * @package memento-magnets
 */

get_header();
?>

<main id="main" class="site-main" role="main">

    <?php get_template_part( 'template-parts/hero' ); ?>

    <?php get_template_part( 'template-parts/products' ); ?>

    <?php get_template_part( 'template-parts/how-to-order' ); ?>

    <?php get_template_part( 'template-parts/reviews' ); ?>

    <?php get_template_part( 'template-parts/newsletter' ); ?>

</main>

<?php get_footer(); ?>
