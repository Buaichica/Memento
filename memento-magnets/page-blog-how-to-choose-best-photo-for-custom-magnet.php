<?php
/**
 * Template Name: Blog – How to Choose the Best Photo for Your Custom Magnet
 *
 * SEO Target: "best photo for custom magnet NZ", "how to choose photo fridge magnet New Zealand"
 *
 * @package memento-magnets
 */

// Meta description — 156 characters
add_filter( 'memento_meta_description', function() {
    return 'Get the best results from your custom magnet. Expert tips on photo resolution, lighting and cropping from Memento Magnets NZ — your photo guide starts here.';
} );

get_header();
?>

<main id="main" class="site-main" role="main">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="container">
            <div class="post-category"><?php _e( 'Tips & Advice', 'memento-magnets' ); ?></div>
            <h1 style="max-width:800px;margin:0 auto var(--space-4);">
                <?php _e( 'How to Choose the Best Photo for Your Custom Fridge Magnet', 'memento-magnets' ); ?>
            </h1>
            <div class="breadcrumbs">
                <a href="<?php echo home_url('/'); ?>"><?php _e( 'Home', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <a href="<?php echo home_url('/blogs/'); ?>"><?php _e( 'Blogs', 'memento-magnets' ); ?></a>
                <span class="sep" aria-hidden="true">/</span>
                <span class="current" aria-current="page"><?php _e( 'How to Choose the Best Photo for Your Custom Magnet', 'memento-magnets' ); ?></span>
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
                        <span>6 min read</span>
                        <span>·</span>
                        <span>Tips &amp; Advice · New Zealand</span>
                    </div>

                    <!-- Featured Image Placeholder -->
                    <figure class="post-featured-image" style="transform:rotate(-0.5deg);">
                        <div style="width:100%;aspect-ratio:16/7;background:var(--gradient-hero);display:flex;align-items:center;justify-content:center;border-radius:var(--radius-xl);">
                            <div style="text-align:center;color:white;">
                                <div style="font-size:5rem;margin-bottom:var(--space-3);">📷</div>
                                <p style="font-family:var(--font-heading);font-size:var(--text-2xl);font-weight:700;color:white;">
                                    <!-- Replace with: A person scrolling through photos on their phone next to a printed magnet on the fridge -->
                                    Image Placeholder
                                </p>
                            </div>
                        </div>
                        <figcaption style="font-size:var(--text-sm);color:var(--color-mid-grey);text-align:center;margin-top:var(--space-2);">
                            <?php _e( 'The right photo makes all the difference. Here\'s how to pick the perfect one.', 'memento-magnets' ); ?>
                        </figcaption>
                    </figure>

                    <!-- Post Content -->
                    <div class="post-content">

                        <p><?php _e( 'You\'ve decided to order a personalised fridge magnet — great choice! But now comes the fun (and sometimes tricky) part: choosing the right photo. The quality of your image has the biggest impact on how your magnet turns out, so it\'s worth taking a moment to pick the best one.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'At Memento Magnets, we print hundreds of custom magnets for New Zealanders every week. Over time, we\'ve learned exactly what makes a photo look stunning on a magnet — and what to avoid. Here\'s everything you need to know.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( '1. Use a High-Resolution Image', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Resolution is the single most important factor. A low-resolution image will look blurry or pixelated when printed, no matter how beautiful the moment it captures.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'As a general rule:', 'memento-magnets' ); ?></p>

                        <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:var(--space-5);color:var(--color-dark-grey);">
                            <li><?php _e( 'Use the original file from your camera or phone — not a screenshot or a photo that\'s been shared via WhatsApp or Facebook (these are compressed and lose quality)', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Aim for at least 800 x 800 pixels for a small magnet, and 1500 x 1500 pixels or more for larger sizes', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Modern smartphone cameras (iPhone, Samsung, etc.) taken in good light are more than good enough', 'memento-magnets' ); ?></li>
                        </ul>

                        <!-- Tip Box -->
                        <div style="background:var(--color-cream);border-left:4px solid var(--color-hot-pink);border-radius:0 var(--radius-lg) var(--radius-lg) 0;padding:var(--space-5) var(--space-6);margin:var(--space-6) 0;">
                            <p style="font-weight:700;font-family:var(--font-heading);font-size:var(--text-lg);margin-bottom:var(--space-2);">✦ Quick Tip</p>
                            <p style="margin:0;color:var(--color-dark-grey);">
                                <?php _e( 'Go into your phone\'s camera settings and make sure you\'re shooting in the highest resolution available. On iPhone, go to Settings → Camera → Formats → Most Compatible for the best quality file.', 'memento-magnets' ); ?>
                            </p>
                        </div>

                        <h2><?php _e( '2. Check the Lighting', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Good lighting makes faces pop, colours look true, and details stay crisp. Poor lighting — especially dark or blurry indoor shots — can result in a muddy-looking print.', 'memento-magnets' ); ?></p>

                        <!-- Image Placeholder -->
                        <div style="width:100%;aspect-ratio:16/9;background:var(--color-cream);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:var(--space-6) 0;border:2px dashed #DDD0CA;">
                            <div style="text-align:center;color:var(--color-mid-grey);">
                                <div style="font-size:3rem;margin-bottom:var(--space-2);">☀️</div>
                                <p style="font-size:var(--text-sm);">
                                    <!-- Replace with: Side-by-side comparison of a well-lit photo vs dark photo on a magnet -->
                                    Image Placeholder — Well-lit vs poorly lit photo comparison
                                </p>
                            </div>
                        </div>

                        <p><?php _e( 'The best lighting for magnet photos:', 'memento-magnets' ); ?></p>

                        <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:var(--space-5);color:var(--color-dark-grey);">
                            <li><?php _e( 'Natural daylight — outdoors or near a window — is always your best friend', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Overcast days in New Zealand are actually ideal — the clouds act as a natural diffuser, creating soft, even light', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Avoid flash if possible — it can wash out faces and create harsh shadows', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Avoid backlit shots where the subject is in front of a bright window or sunny sky', 'memento-magnets' ); ?></li>
                        </ul>

                        <h2><?php _e( '3. Make Sure the Subject Is in Focus', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'It sounds obvious, but it\'s easy to miss a slightly blurry photo when you\'re scrolling through hundreds on your phone. Always zoom in on the faces or main subject before uploading to make sure everything is sharp.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Motion blur (from people moving) and out-of-focus backgrounds are fine — it\'s the main subject that matters. If the faces are sharp, your magnet will look great.', 'memento-magnets' ); ?></p>

                        <h2><?php _e( '4. Think About Composition and Cropping', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Our magnets are square or rectangular, so consider how your photo will be cropped. A wide landscape shot of a beach in Queenstown might need to be cropped down significantly for a small square magnet — and you might lose parts of the image you love.', 'memento-magnets' ); ?></p>

                        <!-- Image Placeholder -->
                        <div style="width:100%;aspect-ratio:16/9;background:var(--color-cream);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:var(--space-6) 0;border:2px dashed #DDD0CA;">
                            <div style="text-align:center;color:var(--color-mid-grey);">
                                <div style="font-size:3rem;margin-bottom:var(--space-2);">✂️</div>
                                <p style="font-size:var(--text-sm);">
                                    <!-- Replace with: Diagram showing how a photo is cropped to fit different magnet shapes/sizes -->
                                    Image Placeholder — Photo cropping guide for magnet sizes
                                </p>
                            </div>
                        </div>

                        <p><?php _e( 'A few composition tips:', 'memento-magnets' ); ?></p>

                        <ul style="list-style:disc;padding-left:1.5rem;margin-bottom:var(--space-5);color:var(--color-dark-grey);">
                            <li><?php _e( 'Portraits with the subject centred work best for square magnets', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Group photos with everyone clearly visible (not half cut off at the edges) work well for larger magnets', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Landscape or panorama photos suit our rectangular photo strip magnets', 'memento-magnets' ); ?></li>
                            <li><?php _e( 'Leave a little breathing room around the edges — don\'t crop too close to faces', 'memento-magnets' ); ?></li>
                        </ul>

                        <h2><?php _e( '5. Avoid Heavy Filters or Editing', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Heavy Instagram filters, extreme contrast, or black-and-white conversions can all affect how your magnet prints. What looks great on a backlit phone screen may print differently on physical material.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'Our recommendation: use the original photo with minimal editing. If you want to adjust brightness or contrast slightly, keep it subtle. Our printing process naturally produces rich, vibrant colours — so trust the photo!', 'memento-magnets' ); ?></p>

                        <!-- Callout -->
                        <div style="background:var(--gradient-brand);border-radius:var(--radius-xl);padding:var(--space-8);margin:var(--space-8) 0;text-align:center;">
                            <p style="font-family:var(--font-heading);font-size:var(--text-2xl);font-weight:800;color:white;margin-bottom:var(--space-3);">
                                <?php _e( 'Not sure if your photo is good enough?', 'memento-magnets' ); ?>
                            </p>
                            <p style="color:rgba(255,255,255,0.85);margin-bottom:var(--space-5);">
                                <?php _e( 'Upload it and our preview tool will show you exactly how it\'ll look before you order. No guesswork.', 'memento-magnets' ); ?>
                            </p>
                            <a href="<?php echo esc_url( home_url('/custom-magnets/') ); ?>" class="btn btn--outline-white btn--lg">
                                <?php _e( 'Try the Preview Tool', 'memento-magnets' ); ?>
                            </a>
                        </div>

                        <h2><?php _e( 'The Bottom Line', 'memento-magnets' ); ?></h2>

                        <p><?php _e( 'Choosing the right photo doesn\'t have to be complicated. Stick to high-resolution originals, shoot in good natural light, make sure your subject is in focus, and think about how it\'ll crop to your chosen magnet size. Do those four things and you\'ll end up with a magnet you\'ll love.', 'memento-magnets' ); ?></p>

                        <p><?php _e( 'If you ever have any doubts, our team is always happy to help. Send us an email at orders@mementomagnets.com and we can advise you before you place your order.', 'memento-magnets' ); ?></p>

                        <div style="display:flex;gap:var(--space-4);flex-wrap:wrap;margin-top:var(--space-6);">
                            <a href="<?php echo esc_url( home_url('/custom-magnets/') ); ?>" class="btn btn--primary btn--lg">
                                <?php _e( 'Start Your Order', 'memento-magnets' ); ?>
                            </a>
                            <a href="<?php echo esc_url( home_url('/contact/') ); ?>" class="btn btn--secondary btn--lg">
                                <?php _e( 'Ask Us a Question', 'memento-magnets' ); ?>
                            </a>
                        </div>

                    </div><!-- .post-content -->

                    <!-- Tags -->
                    <div style="margin-top:var(--space-8);padding-top:var(--space-6);border-top:2px solid var(--color-cream);display:flex;flex-wrap:wrap;gap:var(--space-2);align-items:center;">
                        <span style="font-size:var(--text-sm);font-weight:600;color:var(--color-mid-grey);"><?php _e( 'Tags:', 'memento-magnets' ); ?></span>
                        <?php
                        $tags = [
                            'Photo Tips',
                            'Custom Magnets NZ',
                            'Magnet Quality',
                            'Photo Magnet Guide',
                            'New Zealand',
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
                            <?php _e( 'Upload your photo and create a beautiful custom magnet. Delivered across New Zealand.', 'memento-magnets' ); ?>
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

    <?php get_template_part( 'template-parts/newsletter' ); ?>

</main>

<?php
$schema = [
    '@context'         => 'https://schema.org',
    '@type'            => 'BlogPosting',
    'headline'         => 'How to Choose the Best Photo for Your Custom Fridge Magnet',
    'description'      => 'Learn how to pick the perfect photo for your custom fridge magnet. Expert tips from Memento Magnets NZ on resolution, lighting, cropping and more.',
    'datePublished'    => '2026-03-04',
    'dateModified'     => '2026-03-04',
    'author'           => [ '@type' => 'Organization', 'name' => 'Memento Magnets', 'url' => get_site_url() ],
    'publisher'        => [ '@type' => 'Organization', 'name' => 'Memento Magnets', 'logo' => [ '@type' => 'ImageObject', 'url' => MEMENTO_URI . '/assets/img/logo.png' ] ],
    'url'              => get_permalink(),
    'mainEntityOfPage' => get_permalink(),
    'keywords'         => 'best photo custom magnet NZ, photo magnet tips New Zealand, how to choose photo fridge magnet',
    'inLanguage'       => 'en-NZ',
    'areaServed'       => 'New Zealand',
];
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
</script>

<?php get_footer(); ?>
