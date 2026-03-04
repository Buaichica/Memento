<?php
/**
 * Template Name: Blog – Custom Magnets for Every Occasion NZ
 *
 * SEO Target: "custom magnets NZ occasions", "personalised magnets wedding birthday NZ"
 *
 * @package memento-magnets
 */

// Meta description — 155 characters
add_filter( 'memento_meta_description', function() {
    return 'From weddings to Christmas, explore how custom photo magnets make the perfect gift for every occasion in New Zealand. Order online from Memento Magnets NZ.';
} );

get_header();
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <div class="post-category"><?php _e( 'Inspiration', 'memento-magnets' ); ?></div>
            <h1 style="max-width:800px;margin:0 auto var(--space-4);">
                <?php _e( 'Custom Photo Magnets for Every Occasion in New Zealand', 'memento-magnets' ); ?>
            </h1>
            <div class="breadcrumbs">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <a href="<?php echo home_url('/blogs/'); ?>"><?php _e( 'Blogs', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'Custom Magnets for Every Occasion in New Zealand', 'memento-magnets' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Article -->
    <section class="single-post">
        <div class="container">
            <div class="post-layout">

                <article>

                    <!-- Post Meta -->
                    <div class="post-meta" style="margin-bottom:var(--space-8);">
                        <span>By Memento Magnets</span>
                        <span>·</span>
                        <time datetime="2026-03-04">March 4, 2026</time>
                        <span>·</span>
                        <span>7 min read</span>
                        <span>·</span>
                        <span>Inspiration · New Zealand</span>
                    </div>

                    <!-- Featured Image Placeholder -->
                    <figure class="post-featured-image" style="transform:rotate(-0.5deg);">
                        <div style="width:100%;aspect-ratio:16/7;background:var(--gradient-hero);display:flex;align-items:center;justify-content:center;border-radius:var(--radius-xl);">
                            <div style="text-align:center;color:white;">
                                <div style="font-size:5rem;margin-bottom:var(--space-3);">🎉</div>
                                <p style="font-family:var(--font-heading);font-size:var(--text-2xl);font-weight:700;color:white;">
                                    <!-- Replace with: A collage of magnets for different occasions — wedding, birthday, Christmas, baby -->
                                    Image Placeholder
                                </p>
                            </div>
                        </div>
                        <figcaption style="font-size:var(--text-sm);color:var(--color-mid-grey);text-align:center;margin-top:var(--space-2);">
                            <?php _e( 'From weddings to Christmas — Memento Magnets has a custom magnet for every moment.', 'memento-magnets' ); ?>
                        </figcaption>
                    </figure>

                    <!-- Post Content -->
                    <div class="post-content">

                        <p><?php _e( 'New Zealand is a country that loves to celebrate. From long summer afternoons at the beach to cosy winter birthdays, from big Auckland weddings to intimate South Island elopements — we know how to mark a moment. And what better way to preserve those moments than with a personalised photo magnet that lives on your fridge forever?', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'At Memento Magnets, we create custom photo magnets for New Zealanders across the country every single day. Here\'s a look at the most popular occasions our customers order for — and why magnets work so beautifully for each one.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( 'Weddings & Anniversaries', 'memento-magnets' ); ?></h2>

                        <!-- Image Placeholder -->
                        <div style="width:100%;aspect-ratio:16/9;background:var(--color-cream);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:var(--space-6) 0;border:2px dashed #DDD0CA;">
                            <div style="text-align:center;color:var(--color-mid-grey);">
                                <div style="font-size:3rem;margin-bottom:var(--space-2);">💍</div>
                                <p style="font-size:var(--text-sm);">
                                    <!-- Replace with: A beautiful wedding photo magnet set arranged on a white table with flowers -->
                                    Image Placeholder — Wedding magnet set display
                                </p>
                            </div>
                        </div>

                        <p><?php _e( 'Wedding photo magnets have become one of the most popular wedding favours in New Zealand — and it\'s easy to see why. They\'re personal, practical, and every guest takes a little piece of your special day home with them.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Popular wedding magnet ideas:', 'memento-magnets' ); ?></p>
                        <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:var(--space-5);color:var(--color-dark-grey);">
                            <li><?php _e( 'Engagement photo magnets as save-the-date announcements', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Wedding day photos as thank-you gifts for guests', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Anniversary magnets featuring photos from each year together', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Bridal party gift sets with a photo of the whole group', 'memento-magnets' ); ?></li>
                        </ul>

                        <blockquote>
                            <?php _e( '"We ordered 80 wedding magnets as favours for our guests — they were absolutely stunning and everyone loved them. Will definitely order again for our anniversary!" — Emma R., Wellington', 'memento-magnets' ); ?>
                        </blockquote>

                        <h2><?php _e( 'Birthdays — From 1st to 100th', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Whether it\'s a baby\'s first birthday, a milestone 21st, or a big 50th celebration, a personalised birthday magnet is a gift that stands out from the usual cards and vouchers. Choose a favourite photo of the birthday person and turn it into a keepsake they\'ll display with pride.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Milestone birthday ideas:', 'memento-magnets' ); ?></p>
                        <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:var(--space-5);color:var(--color-dark-grey);">
                            <li><?php _e( 'A collage magnet set of photos from each decade of their life', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'A group photo magnet from the birthday celebration itself', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'A then-and-now magnet — their baby photo alongside a current one', 'memento-magnets' ); ?></li>
                        </ul>

                        <!-- Image Placeholder -->
                        <div style="width:100%;aspect-ratio:16/9;background:var(--color-cream);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:var(--space-6) 0;border:2px dashed #DDD0CA;">
                            <div style="text-align:center;color:var(--color-mid-grey);">
                                <div style="font-size:3rem;margin-bottom:var(--space-2);">🎂</div>
                                <p style="font-size:var(--text-sm);">
                                    <!-- Replace with: A birthday gift bag with a personalised magnet peeking out, balloons in background -->
                                    Image Placeholder — Birthday magnet gift idea
                                </p>
                            </div>
                        </div>

                        <h2><?php _e( 'New Babies & Baby Showers', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'There is no photo more precious than a newborn baby\'s first days. A personalised magnet featuring that tiny scrunched-up face is the kind of gift new parents absolutely treasure — and grandparents go absolutely wild for.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Our newborn magnets make perfect baby shower gifts (order in advance and personalise with the due date or nursery theme photo), or wonderful keepsakes to give once the baby has arrived.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( 'Christmas & New Year', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Nothing says "Kiwi Christmas" quite like a personalised magnet featuring the family in matching ugly sweaters — or, more likely, in shorts and jandals at the bach. Our Christmas magnets are a firm favourite with New Zealand families, and a great alternative to the traditional Christmas card.', 'memento-magnets' ); ?></p>

                        <!-- Tip Box -->
                        <div style="background:var(--color-cream);border-left:4px solid var(--color-warm-gold);border-radius:0 var(--radius-lg) var(--radius-lg) 0;padding:var(--space-5) var(--space-6);margin:var(--space-6) 0;">
                            <p style="font-weight:700;font-family:var(--font-heading);font-size:var(--text-lg);margin-bottom:var(--space-2);">✦ Christmas Ordering Tip</p>
                            <p style="margin:0;color:var(--color-dark-grey);">
                                <?php _e( 'Christmas is our busiest time of year! To guarantee delivery before December 25th, we recommend ordering by early December. Sign up to our newsletter to be notified when our Christmas cut-off dates are announced.', 'memento-magnets' ); ?>
                            </p>
                        </div>

                        <h2><?php _e( 'Mother\'s Day & Father\'s Day', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Struggling to find something original for Mum or Dad? A personalised magnet is the answer. Choose a favourite family photo, a candid shot of them with the grandkids, or a picture from a special trip — and turn it into something they\'ll see and smile at every day.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'In New Zealand, Mother\'s Day falls in May and Father\'s Day in September — plenty of time to plan ahead and create something truly special.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( 'Graduations & School Milestones', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'From preschool leavers to university graduates, personalised magnets make wonderful mementos of academic milestones. A proud graduation photo on the family fridge is a constant reminder of hard work and achievement.', 'memento-magnets' ); ?></p>

                        <!-- Image Placeholder -->
                        <div style="width:100%;aspect-ratio:16/9;background:var(--color-cream);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:var(--space-6) 0;border:2px dashed #DDD0CA;">
                            <div style="text-align:center;color:var(--color-mid-grey);">
                                <div style="font-size:3rem;margin-bottom:var(--space-2);">🎓</div>
                                <p style="font-size:var(--text-sm);">
                                    <!-- Replace with: A graduation photo magnet displayed on a fridge next to other family photos -->
                                    Image Placeholder — Graduation magnet on fridge
                                </p>
                            </div>
                        </div>

                        <h2><?php _e( 'Pet Portraits', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Never underestimate the love New Zealanders have for their pets. Our pet portrait magnets are consistently among our most ordered products — there is simply nothing cuter than your dog, cat, or rabbit staring back at you from the fridge every morning.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Pet magnets also make brilliant gifts for animal-loving friends and family who might be harder to shop for.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( 'Housewarming Gifts', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Moving into a new home is a big milestone — and a personalised magnet is a thoughtful way to help someone make their new place feel like home right from day one. Choose a meaningful photo of the new homeowner, their family, or a shared memory, and give them something that\'s instantly "them."', 'memento-magnets' ); ?></p>

                        <!-- Final CTA -->
                        <div style="background:var(--gradient-brand);border-radius:var(--radius-xl);padding:var(--space-8);margin:var(--space-8) 0;text-align:center;">
                            <p style="font-family:var(--font-heading);font-size:var(--text-2xl);font-weight:800;color:white;margin-bottom:var(--space-3);">
                                <?php _e( 'Whatever the occasion — we\'ve got you covered.', 'memento-magnets' ); ?>
                            </p>
                            <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--space-5);">
                                <?php _e( 'Order online and we\'ll deliver your personalised magnets anywhere in New Zealand.', 'memento-magnets' ); ?>
                            </p>
                            <a href="<?php echo esc_url( home_url('/custom-magnets/') ); ?>" class="btn btn--outline-white btn--lg">
                                <?php _e( 'Shop All Magnets', 'memento-magnets' ); ?>
                            </a>
                        </div>

                    </div><!-- .post-content -->

                    <!-- Tags -->
                    <div style="margin-top:var(--space-8);padding-top:var(--space-6);border-top:2px solid var(--color-cream);display:flex;flex-wrap:wrap;gap:var(--space-2);align-items:center;">
                        <span style="font-size:var(--text-sm);font-weight:600;color:var(--color-mid-grey);"><?php _e( 'Tags:', 'memento-magnets' ); ?></span>
                        <?php
                        $tags = [
                            'Custom Magnets NZ',
                            'Wedding Gifts NZ',
                            'Christmas Gifts New Zealand',
                            'Birthday Ideas NZ',
                            'Personalised Gifts',
                        ];
                        foreach ( $tags as $tag ) :
                        ?>
                        <span style="background:var(--color-cream);color:var(--color-dark-grey);padding:4px 12px;border-radius:var(--radius-full);font-size:var(--text-sm);">
                            #<?php echo esc_html( $tag ); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>

                </article>

                <!-- Sidebar -->
                <aside aria-label="<?php esc_attr_e( 'Blog sidebar', 'memento-magnets' ); ?>">
                    <div style="background:var(--color-cream);border-radius:var(--radius-xl);padding:var(--space-6);margin-bottom:var(--space-6);position:sticky;top:calc(var(--header-height) + var(--space-6));">
                        <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-4);"><?php _e( 'Create Your Magnets', 'memento-magnets' ); ?></h3>
                        <p style="font-size:var(--text-sm);color:var(--color-mid-grey);margin-bottom:var(--space-5);">
                            <?php _e( 'Perfect for any occasion. Delivered across New Zealand.', 'memento-magnets' ); ?>
                        </p>
                        <a href="<?php echo esc_url( home_url('/custom-magnets/') ); ?>" class="btn btn--primary" style="width:100%;justify-content:center;">
                            <?php _e( 'Shop Now', 'memento-magnets' ); ?>
                        </a>
                        <div style="margin-top:var(--space-4);padding-top:var(--space-4);border-top:1px solid #E8D9D0;">
                            <p style="font-size:var(--text-xs);color:var(--color-mid-grey);text-align:center;">
                                ★★★★★ <?php _e( 'Rated 4.9/5 · Free shipping over $50 NZD', 'memento-magnets' ); ?>
                            </p>
                        </div>
                    </div>

                    <div style="background:var(--color-white);border-radius:var(--radius-xl);padding:var(--space-6);border:1px solid #EDE5E1;">
                        <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-4);"><?php _e( 'More From Our Blog', 'memento-magnets' ); ?></h3>
                        <ul style="list-style:none;display:flex;flex-direction:column;gap:var(--space-4);">
                            <li style="padding-bottom:var(--space-3);border-bottom:1px solid #EDE5E1;">
                                <a href="<?php echo esc_url( home_url('/personalised-magnets-perfect-gift-nz/') ); ?>" style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);">
                                    <?php _e( '5 Reasons Personalised Magnets Make the Perfect Gift in NZ', 'memento-magnets' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( home_url('/how-to-choose-best-photo-for-custom-magnet/') ); ?>" style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);">
                                    <?php _e( 'How to Choose the Best Photo for Your Custom Magnet', 'memento-magnets' ); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>

            </div><!-- .post-layout -->
        </div>
    </section>

    <?php get_template_part( 'template-parts/newsletter' ); ?>

</main>

<?php
$schema = [
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => 'Custom Photo Magnets for Every Occasion in New Zealand',
    'description'      => 'From weddings to Christmas, birthdays to baby showers — discover how custom photo magnets from Memento Magnets make the perfect gift for every occasion in New Zealand.',
    'datePublished'    => '2026-03-04',
    'dateModified'     => '2026-03-04',
    'author'           => [ '@type' => 'Organization', 'name' => 'Memento Magnets', 'url' => get_site_url() ],
    'publisher'        => [ '@type' => 'Organization', 'name' => 'Memento Magnets', 'logo' => [ '@type' => 'ImageObject', 'url' => MEMENTO_URI . '/assets/img/logo.png' ] ],
    'url'              => get_permalink(),
    'mainEntityOfPage' => get_permalink(),
    'keywords'         => 'custom magnets NZ occasions, wedding magnets New Zealand, Christmas gifts NZ, personalised birthday magnets New Zealand',
    'inLanguage'       => 'en-NZ',
    'areaServed'       => 'New Zealand',
];
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
</script>

<?php get_footer(); ?>
