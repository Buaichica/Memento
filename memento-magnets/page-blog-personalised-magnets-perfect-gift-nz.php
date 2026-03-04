<?php
/**
 * Template Name: Blog – Personalised Magnets Perfect Gift NZ
 *
 * SEO Target: "personalised magnets New Zealand", "custom photo magnets NZ gift"
 *
 * @package memento-magnets
 */

// Meta description — 157 characters
add_filter( 'memento_meta_description', function() {
    return 'Discover why custom photo magnets are the perfect gift for any occasion in NZ. Shop personalised magnets from Memento Magnets — delivered across New Zealand.';
} );

get_header();
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <div class="post-category"><?php _e( 'Gift Ideas', 'memento-magnets' ); ?></div>
            <h1 style="max-width:800px;margin:0 auto var(--space-4);">
                <?php _e( '5 Reasons Personalised Photo Magnets Make the Perfect Gift in New Zealand', 'memento-magnets' ); ?>
            </h1>
            <div class="breadcrumbs">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <a href="<?php echo home_url('/blogs/'); ?>"><?php _e( 'Blogs', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( '5 Reasons Personalised Photo Magnets Make the Perfect Gift', 'memento-magnets' ); ?></span>
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
                        <span>5 min read</span>
                        <span>·</span>
                        <span>Gift Ideas · New Zealand</span>
                    </div>

                    <!-- Featured Image Placeholder -->
                    <figure class="post-featured-image" style="transform:rotate(-0.5deg);">
                        <div style="width:100%;aspect-ratio:16/7;background:var(--gradient-hero);display:flex;align-items:center;justify-content:center;border-radius:var(--radius-xl);">
                            <div style="text-align:center;color:white;">
                                <div style="font-size:5rem;margin-bottom:var(--space-3);">🧲</div>
                                <p style="font-family:var(--font-heading);font-size:var(--text-2xl);font-weight:700;color:white;">
                                    <!-- Replace with: A flat-lay photo of colourful personalised magnets arranged on a white fridge -->
                                    Image Placeholder
                                </p>
                            </div>
                        </div>
                        <figcaption style="font-size:var(--text-sm);color:var(--color-mid-grey);text-align:center;margin-top:var(--space-2);">
                            <?php _e( 'Personalised photo magnets from Memento Magnets — made with love in New Zealand.', 'memento-magnets' ); ?>
                        </figcaption>
                    </figure>

                    <!-- Post Content -->
                    <div class="post-content">

                        <p><?php _e( 'Finding the perfect gift in New Zealand is no easy task. With so many options online and in stores, it can be hard to find something truly thoughtful — something that will last beyond the moment it\'s unwrapped. That\'s where personalised photo magnets come in.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'At Memento Magnets, we\'ve helped thousands of New Zealanders turn their favourite photos into beautiful keepsakes. Whether it\'s a birthday, a wedding, a new baby, or just because — a custom magnet is a gift that keeps giving, every time someone walks past the fridge.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Here are five reasons why personalised photo magnets are the perfect gift for any occasion in New Zealand.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( '1. They\'re Deeply Personal', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Unlike generic gifts from a shop shelf, a personalised magnet is made specifically for the person you\'re giving it to. It features a photo that holds meaning — a shared memory, a beloved pet, a milestone moment. That thoughtfulness is immediately visible the moment the gift is opened.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'In a world of mass-produced presents, something custom-made stands out. It says: "I thought about you. I chose this photo. I made this just for you."', 'memento-magnets' ); ?></p>

                        <!-- Image Placeholder 2 -->
                        <div style="width:100%;aspect-ratio:16/9;background:var(--color-cream);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:var(--space-8) 0;border:2px dashed #DDD0CA;">
                            <div style="text-align:center;color:var(--color-mid-grey);">
                                <div style="font-size:3rem;margin-bottom:var(--space-2);">📸</div>
                                <p style="font-size:var(--text-sm);">
                                    <!-- Replace with: A close-up of someone holding a magnet showing a family photo, smiling -->
                                    Image Placeholder — Someone holding a personalised photo magnet
                                </p>
                            </div>
                        </div>

                        <h2><?php _e( '2. They Last a Lifetime', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Flowers wilt. Chocolates get eaten. Candles burn down. But a personalised magnet? It lives on your fridge for years — sometimes decades. Every morning when you grab the milk, there\'s that photo of Nana at Christmas, or your puppy on the day you brought them home, or your best friend at her hen\'s do.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Our magnets are printed on premium materials with vibrant, fade-resistant inks and a durable laminated finish — built to last through every season, even the sticky New Zealand summer humidity.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( '3. They Suit Every Budget', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'One of the best things about personalised magnets is that you don\'t need to spend a fortune to give something meaningful. Our magnets start from just $14.99 NZD — making them perfect as an add-on gift, a stocking stuffer, a party favour, or a standalone present for someone who\'s hard to buy for.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Want to go bigger? Our multi-magnet sets and gift packs make a gorgeous, premium gift that looks like you spent far more than you did.', 'memento-magnets' ); ?></p>

                        <!-- Callout box -->
                        <div style="background:var(--gradient-brand);border-radius:var(--radius-xl);padding:var(--space-8);margin:var(--space-8) 0;text-align:center;">
                            <p style="font-family:var(--font-heading);font-size:var(--text-2xl);font-weight:800;color:white;margin-bottom:var(--space-3);">
                                <?php _e( 'Free shipping on NZ orders over $50', 'memento-magnets' ); ?>
                            </p>
                            <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--space-5);">
                                <?php _e( 'Order by Thursday for weekend delivery across New Zealand.', 'memento-magnets' ); ?>
                            </p>
                            <a href="<?php echo esc_url( home_url('/custom-magnets/') ); ?>" class="btn btn--outline-white btn--lg">
                                <?php _e( 'Shop Now', 'memento-magnets' ); ?>
                            </a>
                        </div>

                        <h2><?php _e( '4. They Work for Every Occasion', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'New Zealanders celebrate life in so many ways — and personalised magnets are the rare gift that fits all of them. Here are just a few occasions our customers order for:', 'memento-magnets' ); ?></p>

                        <ul style="list-style:none;padding:0;margin-bottom:var(--space-6);">
                            <?php
                            $occasions = [
                                ['🎂', __( 'Birthdays — from 1st to 100th', 'memento-magnets' )],
                                ['💍', __( 'Weddings & anniversaries', 'memento-magnets' )],
                                ['👶', __( 'New babies & baby showers', 'memento-magnets' )],
                                ['🎓', __( 'Graduations & school milestones', 'memento-magnets' )],
                                ['🎄', __( 'Christmas & New Year', 'memento-magnets' )],
                                ['💝', __( 'Mother\'s Day & Father\'s Day', 'memento-magnets' )],
                                ['🐾', __( 'Pet portraits for animal lovers', 'memento-magnets' )],
                                ['🏠', __( 'Housewarming gifts', 'memento-magnets' )],
                            ];
                            foreach ( $occasions as $o ) :
                            ?>
                            <li style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-3) 0;border-bottom:1px solid var(--color-cream);font-size:var(--text-base);color:var(--color-dark-grey);">
                                <span style="font-size:1.5rem;"><?php echo $o[0]; ?></span>
                                <?php echo esc_html( $o[1] ); ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <h2><?php _e( '5. They\'re Delivered Right to Your Door Across New Zealand', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Living in Auckland, Wellington, Christchurch, or a rural town in Southland — it doesn\'t matter. We deliver personalised magnets to every corner of New Zealand. Simply order online, upload your photo, and we\'ll handle the rest.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Most orders are dispatched within 2–4 business days, with North Island delivery typically arriving in 1–3 business days and South Island in 2–5 business days. Need it fast? Express options are available at checkout.', 'memento-magnets' ); ?></p>

                        <!-- Image Placeholder 3 -->
                        <div style="width:100%;aspect-ratio:16/9;background:var(--color-cream);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:var(--space-8) 0;border:2px dashed #DDD0CA;">
                            <div style="text-align:center;color:var(--color-mid-grey);">
                                <div style="font-size:3rem;margin-bottom:var(--space-2);">🚚</div>
                                <p style="font-size:var(--text-sm);">
                                    <!-- Replace with: A NZ courier package being delivered to a house with magnets visible inside -->
                                    Image Placeholder — NZ delivery of personalised magnets
                                </p>
                            </div>
                        </div>

                        <h2><?php _e( 'Ready to Create Your Perfect Gift?', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Whether you\'re buying for a loved one or treating yourself, a personalised photo magnet from Memento Magnets is a gift that will be treasured for years. Browse our collections today and start turning your favourite memories into something beautiful.', 'memento-magnets' ); ?></p>

                        <div style="display:flex;gap:var(--space-4);flex-wrap:wrap;margin-top:var(--space-6);">
                            <a href="<?php echo esc_url( home_url('/custom-magnets/') ); ?>" class="btn btn--primary btn--lg">
                                <?php _e( 'Shop Personalised Magnets', 'memento-magnets' ); ?>
                            </a>
                            <a href="<?php echo esc_url( home_url('/blogs/') ); ?>" class="btn btn--secondary btn--lg">
                                <?php _e( 'Read More Blogs', 'memento-magnets' ); ?>
                            </a>
                        </div>

                    </div><!-- .post-content -->

                    <!-- Tags -->
                    <div style="margin-top:var(--space-8);padding-top:var(--space-6);border-top:2px solid var(--color-cream);display:flex;flex-wrap:wrap;gap:var(--space-2);align-items:center;">
                        <span style="font-size:var(--text-sm);font-weight:600;color:var(--color-mid-grey);"><?php _e( 'Tags:', 'memento-magnets' ); ?></span>
                        <?php
                        $tags = [
                            'Personalised Gifts NZ',
                            'Custom Magnets New Zealand',
                            'Photo Magnets NZ',
                            'Gift Ideas New Zealand',
                            'Memento Magnets',
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
                            <?php _e( 'Turn your favourite photos into beautiful personalised fridge magnets. Delivered across New Zealand.', 'memento-magnets' ); ?>
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

                    <!-- Related posts -->
                    <div style="background:var(--color-white);border-radius:var(--radius-xl);padding:var(--space-6);border:1px solid #EDE5E1;">
                        <h3 style="font-size:var(--text-lg);margin-bottom:var(--space-4);"><?php _e( 'More From Our Blog', 'memento-magnets' ); ?></h3>
                        <ul style="list-style:none;display:flex;flex-direction:column;gap:var(--space-4);">
                            <li style="padding-bottom:var(--space-3);border-bottom:1px solid #EDE5E1;">
                                <a href="<?php echo esc_url( home_url('/how-to-choose-best-photo-for-custom-magnet/') ); ?>" style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);">
                                    <?php _e( 'How to Choose the Best Photo for Your Custom Magnet', 'memento-magnets' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo esc_url( home_url('/custom-magnets-for-every-occasion-nz/') ); ?>" style="font-size:var(--text-sm);font-weight:600;color:var(--color-charcoal);">
                                    <?php _e( 'Custom Magnets for Every Occasion in New Zealand', 'memento-magnets' ); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </aside>

            </div><!-- .post-layout -->
        </div>
    </section>

    <!-- Newsletter CTA -->
    <?php get_template_part( 'template-parts/newsletter' ); ?>

</main>

<?php
// BlogPosting JSON-LD
$schema = [
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => '5 Reasons Personalised Photo Magnets Make the Perfect Gift in New Zealand',
    'description'      => 'Discover why personalised photo magnets are the perfect gift for any occasion in New Zealand. Shop custom magnets from Memento Magnets, delivered across NZ.',
    'datePublished'    => '2026-03-04',
    'dateModified'     => '2026-03-04',
    'author'           => [ '@type' => 'Organization', 'name' => 'Memento Magnets', 'url' => get_site_url() ],
    'publisher'        => [ '@type' => 'Organization', 'name' => 'Memento Magnets', 'logo' => [ '@type' => 'ImageObject', 'url' => MEMENTO_URI . '/assets/img/logo.png' ] ],
    'url'              => get_permalink(),
    'mainEntityOfPage' => get_permalink(),
    'keywords'         => 'personalised magnets NZ, custom photo magnets New Zealand, gift ideas New Zealand, personalised gifts NZ',
    'inLanguage'       => 'en-NZ',
    'areaServed'       => 'New Zealand',
];
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
</script>

<?php get_footer(); ?>
