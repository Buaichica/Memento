# Memento Magnets — Custom WordPress Theme

A bespoke WordPress theme for **Memento Magnets**, a personalised fridge magnet business serving New Zealand and Australia. Built with WooCommerce, full SEO, and a bold, playful brand identity.

---

## Features

- **WooCommerce ready** — product pages, cart, and checkout inherit full Memento brand styles
- **Full SEO suite** — `hreflang` (en-NZ / en-AU), Open Graph, Twitter Cards, canonical URLs, JSON-LD structured data (LocalBusiness, FAQPage, BlogPosting, Product, BreadcrumbList)
- **Responsive** — mobile hamburger nav, fluid layouts at 375px, 768px, 1280px breakpoints
- **Accessibility** — semantic HTML5, skip-to-content link, ARIA labels on interactive elements
- **Contact Form 7** integration with native `wp_mail` fallback
- **FAQ accordion** with FAQPage schema auto-generated from PHP array
- **Custom 404 page**, search form, and blog archive
- Lazy-loaded images, preconnect hints for Google Fonts, single enqueued CSS/JS files

---

## Tech Stack

| Layer | Technology |
|---|---|
| CMS | WordPress (PHP) |
| E-commerce | WooCommerce |
| Styles | Vanilla CSS (CSS custom properties) |
| Scripts | Vanilla JavaScript (no jQuery dependency) |
| Fonts | Google Fonts — Big Shoulders Display, Outfit, Nothing You Could Do |
| Contact | Contact Form 7 plugin |

---

## Brand

### Color Palette

| Name | Hex | Usage |
|---|---|---|
| Hot Pink | `#FF5FA0` | Primary accent, CTAs, gradient start |
| Warm Gold | `#FFD54F` | Secondary accent, gradient end |
| Coral | `#FF8C6E` | Hover states, highlights |
| Lilac | `#C97EC4` | Decorative accents |
| Charcoal | `#1A1A1A` | Body text, headings |
| Cream | `#FFF0E6` | Page backgrounds, card fills |

**Brand gradient:** `linear-gradient(135deg, #FF5FA0 0%, #FFD54F 100%)`

### Typography

| Font | Role |
|---|---|
| Big Shoulders Display | Headings, hero text, nav logo |
| Outfit | Body text, UI labels, captions |
| Nothing You Could Do | Taglines, accent overlays |

### Visual Style

- Border radius: `14px` on cards and buttons
- Sparkle decorators (`✦` / `✧`) as CSS pseudo-elements on hero and section headings
- Soft drop shadows on product cards
- Polaroid-style photo frames (CSS border + slight rotation) in blog and reviews

---

## File Structure

```
memento-magnets/
├── style.css                     ← Theme declaration, CSS variables, global styles
├── functions.php                 ← Theme setup, menus, widget areas, Google Fonts, WooCommerce support
├── index.php                     ← Fallback template
├── header.php                    ← Sticky header: SEO meta, OG tags, hreflang, logo, nav
├── footer.php                    ← Footer columns, social icons, LocalBusiness JSON-LD, copyright
├── front-page.php                ← Homepage: Hero → Products → How To Order → Reviews → Newsletter
├── page.php                      ← Generic page template
├── single.php                    ← Single blog post (author, reading time, related posts)
├── archive.php                   ← Blog listing grid
├── page-faq.php                  ← FAQ accordion + FAQPage schema (assign to FAQ page)
├── page-contact.php              ← Contact form + business info sidebar (assign to Contact page)
├── 404.php                       ← Custom 404 page
├── searchform.php                ← Search form partial
├── template-parts/
│   ├── hero.php                  ← Gradient hero, tagline, CTA buttons
│   ├── products.php              ← WooCommerce product grid with fallback cards
│   ├── how-to-order.php          ← 3-step process: Upload → Choose → Deliver
│   ├── reviews.php               ← Scrolling testimonial cards with star ratings
│   └── newsletter.php            ← Email signup section
├── assets/
│   ├── css/
│   │   └── theme.css             ← Extended styles: animations, responsive, all components
│   └── js/
│       └── theme.js              ← Sticky nav, mobile menu, FAQ accordion, scroll animations
└── screenshot.png                ← 1200×900 theme screenshot
```

---

## Navigation

**Primary (header):** Home | Custom Magnets | Blogs | FAQ | Contact Us

Plus search icon and WooCommerce mini-cart icon.

**Footer columns:** Quick Links · Policies · Contact info · Newsletter signup

---

## Installation

1. Download or clone this repository.
2. Zip the `memento-magnets/` folder (or use the pre-built `memento-magnets.zip`).
3. In WordPress Admin, go to **Appearance → Themes → Add New → Upload Theme**.
4. Upload the zip file and click **Activate**.
5. Install and activate the **WooCommerce** and **Contact Form 7** plugins.
6. Complete the pre-launch checklist below before going live.

---

## Pre-launch Checklist

- [ ] **Menus** — Create _Primary_, _Footer_, and _Policies_ menus in **WP Admin → Appearance → Menus** and assign the correct locations
- [ ] **Page templates** — Assign `page-faq.php` to the FAQ page and `page-contact.php` to the Contact page via the Page Attributes panel
- [ ] **Social links** — Replace `#` placeholder URLs in `footer.php` and `page-contact.php` with real Facebook, Instagram, and TikTok URLs
- [ ] **Contact Form 7** — Update the shortcode ID in `page-contact.php` (around line 24) to match your published CF7 form ID
- [ ] **Logo** — Add your logo image to `assets/img/` and set it via **Appearance → Customize → Site Identity**
- [ ] **OG image** — Add a real `og-default.jpg` to `assets/img/` for social share previews
- [ ] **hreflang** — Verify that `$_SERVER['HTTP_HOST']` resolves correctly on your production server (NZ + AU hreflang tags are set dynamically in `header.php`)

---

## SEO Implementation

- Custom `<title>`, meta description (via `_memento_meta_description` custom field), Open Graph, and Twitter Card tags on every page
- `hreflang="en-NZ"` and `hreflang="en-AU"` alternate links in `<head>`
- **LocalBusiness** JSON-LD in `footer.php` with `areaServed: ["New Zealand", "Australia"]`
- **FAQPage** JSON-LD auto-generated from the PHP accordion array in `page-faq.php`
- **BlogPosting** schema on single posts, **Product** schema on product pages
- **BreadcrumbList** schema sitewide
- Lazy-loading on all `<img>` elements, Google Fonts preconnect hints

---

## License

© 2025 Memento Magnets. All rights reserved.

This theme is proprietary and intended for use on the Memento Magnets website only. Redistribution or resale is not permitted.
