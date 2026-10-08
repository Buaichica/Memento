# Memento Personalizer

WordPress/WooCommerce plugin that owns the personalised-photo workflow for Memento Magnets:
secure uploads, the square photo editor, cart/checkout enforcement, protected order files,
the admin production panel and ZIP export, cleanup, and Dropbox sync.

The theme (`memento-magnets`) is presentation only. If you redesign the theme later, the plugin keeps working.

---

## 1. Install

1. **Back up first.** On SiteGround: Site Tools → Security → Backups → Create, or use a staging copy (recommended).
2. Upload the `memento-personalizer` folder to `wp-content/plugins/` and the updated `memento-magnets` theme.
3. Plugins → activate **Memento Personalizer** (WooCommerce must be active).
   On activation it:
   - creates two tables (`{prefix}memento_pz_sessions`, `{prefix}memento_pz_uploads`)
   - creates the private storage folder (see §3)
   - sets **Required Photos** on existing products from their titles (3/6/9/12) where it was not already set
   - schedules the hourly cleanup job
4. Products → edit each magnet product → Product data → General → check **Required Photos**.
   `0` means the product is not personalised (normal add-to-cart).

## 2. Configuration (`wp-config.php`)

Everything is optional except the Dropbox credentials. Add the lines above `/* That's all, stop editing! */`.

```php
// Dropbox (required for sync). Use an "App folder" Dropbox app — see §4.
define( 'MEMENTO_DROPBOX_APP_KEY',       '...' );
define( 'MEMENTO_DROPBOX_APP_SECRET',    '...' );
define( 'MEMENTO_DROPBOX_REFRESH_TOKEN', '...' );

// Recommended: store customer photos OUTSIDE public_html.
define( 'MEMENTO_PZ_STORAGE_DIR', '/home/customer/www/example.co.nz/memento-private' );

// Optional overrides (defaults shown).
define( 'MEMENTO_PZ_MAX_FILE_MB', 15 );
define( 'MEMENTO_PZ_MIN_PX_RECOMMENDED', 600 );   // Below this: "low resolution" warning.
define( 'MEMENTO_PZ_MIN_PX_REJECT', 250 );        // Below this: upload refused.
define( 'MEMENTO_PZ_OUTPUT_MAX_PX', 2000 );       // Production JPEG size (never upscaled).
define( 'MEMENTO_PZ_JPEG_QUALITY', 92 );
define( 'MEMENTO_PZ_SAFE_AREA_PERCENT', 8 );      // Print-safe inset in the editor.
define( 'MEMENTO_PZ_TEMP_TTL_HOURS', 48 );        // Abandoned uploads are deleted after this.
define( 'MEMENTO_PZ_CART_TTL_HOURS', 168 );       // Uploads already in a cart.
define( 'MEMENTO_PZ_RETENTION_DAYS', 0 );         // Delete photos N days after completion (0 = keep).
define( 'MEMENTO_PZ_UNPAID_RETENTION_DAYS', 30 ); // Delete photos of cancelled/failed orders.
define( 'MEMENTO_PZ_REQUIRE_REVIEW_CHECK', true );
define( 'MEMENTO_PZ_DROPBOX_ROOT', '/Orders' );
define( 'MEMENTO_PZ_DEBUG', false );              // Verbose logs — staging only.
```

All settings can also be changed with the `memento_pz_settings` filter. The full list is in `includes/class-settings.php`.

Logs: WooCommerce → Status → Logs → source `memento-personalizer`. Logs never contain image data or credentials.

## 3. Storage and security

| State | Location | What happens |
|---|---|---|
| Temporary | `{storage}/temp/{session}/{random}.jpg` | Deleted after 48h if no order claims it |
| Order | `{storage}/orders/{order_id}/{item_id}/{NN}-{random}.jpg` | Used by the admin panel, ZIP and Dropbox |
| Dropbox | `/Apps/{app}/Orders/MM-{order number}/item-01/01.jpg` | Operational copy, kept separately |
| Retention | — | Optional purge after `RETENTION_DAYS` (Dropbox copy is **not** deleted) |

- **Storage location.** If `MEMENTO_PZ_STORAGE_DIR` is set, that folder is used. Otherwise the plugin uses a `memento-private` folder next to the WordPress folder (outside the web root) when it can write there. If it can't, it falls back to `wp-content/uploads/memento-private`, protected by `.htaccess`/`web.config` deny rules. In the fallback case, an admin notice recommends setting the constant.
- **File access.** Files are only served through `admin-ajax.php?action=memento_pz_file`, which checks access:
  - temporary photos: the browser that uploaded them (a random HttpOnly cookie)
  - order photos: shop managers, or the logged-in customer who owns the order
- **Upload checks.** Uploads need a nonce and session ownership. The content is checked server-side (finfo + getimagesize must agree): JPEG/PNG/WebP only, with limits on size and megapixels. Images are re-encoded, which strips EXIF/GPS metadata. Filenames are always random server IDs.
- **Abuse limits.** Uploads and session starts are rate-limited per IP.

## 4. Dropbox setup (least privilege)

1. https://www.dropbox.com/developers/apps → Create app → **Scoped access** → **App folder** → name it, e.g. "Memento Magnets Orders".
2. Permissions tab: enable `files.content.write` (and `files.content.read`) → Submit.
3. Settings tab: copy the App key and App secret.
4. Get a refresh token (once):
   - Open `https://www.dropbox.com/oauth2/authorize?client_id=APP_KEY&response_type=code&token_access_type=offline` and approve.
   - Run `curl -X POST https://api.dropboxapi.com/oauth2/token -d "code=CODE&grant_type=authorization_code&client_id=APP_KEY&client_secret=APP_SECRET"`.
   - Copy `refresh_token` from the response into `wp-config.php` (§2).
5. Files then appear in `Dropbox/Apps/Memento Magnets Orders/Orders/MM-1234/`.

> The old theme code used a "Full Dropbox" app and `/Memento/Orders/{date}-{id}-{customer name}`. Those orders show as **Complete (legacy)**. New folders use `MM-{order number}`, with no personal data in the folder name.

How sync works:

- Runs in the background (Action Scheduler) once the order is **Processing** or **Completed**. Checkout never waits for Dropbox.
- Files are uploaded with `overwrite` to fixed paths, so retries never create duplicates.
- The status only becomes **Complete** when every expected file and `manifest.json` has uploaded.
- Failures retry automatically (5 min, 20 min, 80 min, up to `DROPBOX_MAX_RETRIES`). After that, use **Retry Dropbox Sync** on the order screen.
- For an immediate run from the command line: `wp memento-pz dropbox-sync <order_id>`.

## 5. Admin workflow

Each order screen has a **Production — Customer Photos** panel with:

- thumbnails grouped by line item, slot numbers, low-resolution flags and "Missing" tiles
- expected vs actual photo counts per item and for the whole order
- a **Production status** field: New → Photo Review → Ready to Print → Printed → Packed → Shipped (saved with the order and logged as an order note)
- Dropbox status, last attempt, attempt count, error summary, and a Retry/Re-sync button
- a **Download Production ZIP** button, which produces `MM-{order}.zip` containing `manifest.json`, `order.txt` and `item-01/01.jpg …`

The orders list also has a **Production** column (with the tracking number once set).

**Shipping an order (NZ Post).** On the order screen, paste the NZ Post tracking number into **NZ Post tracking number**, set **Production status** to **Shipped** and click **Update**. A *Processing* order then becomes *Completed*, and WooCommerce's "Completed order" email goes to the customer with the subject "Your Memento Magnets order is on its way", the tracking number and a **Track your parcel** link (`https://www.nzpost.co.nz/tools/tracking/item/{number}`). The same tracking box shows on the customer's My Account order page. The number is stored in order meta `_memento_tracking_number`; changes are logged as order notes.

## 6. Data model and migration

**New tables:** `memento_pz_sessions` and `memento_pz_uploads`. See `includes/class-installer.php` for the columns.

**Product meta:** `_memento_photo_count` (same key as before) is now set explicitly in admin, and variations can override it. Title parsing still works as a deprecated fallback. Activation fills in the meta for existing products, and a notice appears on any product that still relies on its title.

**Cart item data:** `memento_pz` = `{ session_id, uploads[], required }`. It replaces `memento_photos` (public URLs). Carts created before deployment that still use `memento_photos` are accepted at checkout.

**Order line item meta:**

| Key | Visible? | Content |
|---|---|---|
| `_memento_pz_session` | hidden | upload session ID |
| `_memento_pz_uploads` | hidden | upload IDs in slot order |
| `_memento_pz_required` | hidden | required photo count at purchase |
| `_memento_pz_finalized` | hidden | number of files moved into order storage |
| `Custom Photos` | visible | e.g. "6 photos" |
| `Customer Photos` | visible | legacy only: newline-separated public URLs |

**Order meta:** `_memento_pz_has_photos`, `_memento_pz_production_status`, `_memento_pz_dropbox_*` (status, attempts, last_attempt, error, folder, done). Legacy `_memento_dropbox_folder` is still read.

**Migrating legacy orders.** Older orders store their photos as public URLs. They still display and sync without migration. To move them into protected storage:

```bash
wp memento-pz migrate-legacy --dry-run          # report only
wp memento-pz migrate-legacy                    # copy into protected storage
wp memento-pz migrate-legacy --delete-public    # …and delete the public originals
```

The original URLs are kept in hidden item meta `_memento_pz_legacy_urls`. Unclaimed files left in `wp-content/uploads/memento-orders/` by the old uploader (abandoned before checkout) are not referenced by any order. Once migration is done, they can be deleted manually.

## 7. SiteGround checklist

- [ ] Do the work on a **staging** copy first (Site Tools → WordPress → Staging), with a backup of the database and `wp-content`.
- [ ] PHP 8.1+ (7.4 minimum), WordPress 6.2+, WooCommerce 7.0+ with HPOS on or off.
- [ ] PHP settings (Site Tools → Devs → PHP Manager): `upload_max_filesize ≥ 16M`, `post_max_size ≥ 20M`, `memory_limit ≥ 256M`, `max_execution_time ≥ 60`. Extensions: `imagick` or `gd`, `zip`, `fileinfo`.
- [ ] Speed Optimizer / Dynamic Cache: `admin-ajax.php` is not cached by default. Make sure the product, cart and checkout pages aren't served with cached personalised content (WooCommerce cart/checkout are excluded by default).
- [ ] HTTPS enforced. The ownership cookie is `Secure` on HTTPS.
- [ ] Replace WP-Cron with a real cron job (Site Tools → Devs → Cron Jobs: `wget -q -O - https://example.co.nz/wp-cron.php?doing_wp_cron >/dev/null 2>&1` every 15 min, plus `define('DISABLE_WP_CRON', true);`), so cleanup and Dropbox retries run reliably.
- [ ] Set `MEMENTO_PZ_STORAGE_DIR` to a folder outside `public_html` and check that it was created.
- [ ] Keep Dropbox secrets only in `wp-config.php`, never in the theme or plugin zip.
- [ ] After deploy: place a real low-value order end-to-end and confirm the thumbnails, ZIP and Dropbox folder.

## 8. Acceptance test checklist (staging)

**Customer flow**
- [ ] A product needing N photos shows N slots. Add to Cart is blocked until all N are ready and the review box is ticked.
- [ ] Multi-select and drag-and-drop fill empty slots in order. Single-slot replace works. Remove works.
- [ ] Edit lets the customer crop, zoom, rotate, reset and replace, and shows the safe area. Saving replaces the slot (only one file per slot on the server).
- [ ] Refreshing the page restores uploaded photos for that product.
- [ ] Rejected with a clear message: a `.txt` renamed to `.jpg`, an image under 250px, a file over 15MB, a corrupt JPEG.
- [ ] Retry appears after a network failure (e.g. go offline in DevTools mid-upload).
- [ ] Checkout succeeds with Dropbox credentials removed. The order panel then shows "Not configured" or "Failed".

**Security**
- [ ] Guessing `wp-content/uploads/...` URLs gives no access to new photos. Direct requests to the storage folder return 403/404.
- [ ] A `memento_pz_file` URL opened in a private window (different cookie) returns 404.
- [ ] An upload without a nonce returns 403. An upload with another browser's `session_id` returns 403.
- [ ] Dropbox secrets do not appear in page source, JS or plugin files.

**Admin/production**
- [ ] Thumbnails are correct per line item. Individual downloads and the ZIP work, in slot order.
- [ ] Partial Dropbox failure followed by a retry gives a complete folder with no duplicates.
- [ ] `wp memento-pz cleanup` after setting `MEMENTO_PZ_TEMP_TTL_HOURS` to 0 removes abandoned temp uploads and leaves order files alone.

**Regression**
- [ ] A product with Required Photos = 0 adds to cart normally.
- [ ] Pricing, tax, shipping, coupons and gateways are unchanged.
- [ ] Old orders still show their photos (Legacy badge).

## 9. Files

| File | Responsibility |
|---|---|
| `memento-personalizer.php` | Bootstrap, HPOS compatibility, `memento_get_photo_count()` back-compat helper |
| `includes/class-settings.php` | All thresholds (constants / filter) |
| `includes/class-logger.php` | WooCommerce logger wrapper with secret redaction |
| `includes/class-installer.php` | Tables, activation, product-count migration |
| `includes/class-storage.php` | Private storage root, key validation, path-traversal protection |
| `includes/class-repository.php` | DB access for sessions and uploads |
| `includes/class-product-config.php` | "Required Photos" field (product and variation) |
| `includes/class-image-processor.php` | Content validation, EXIF rotation, square crop, resize, metadata strip, thumbnails |
| `includes/class-upload-session.php` | Owner cookie, session ownership, JSON payloads |
| `includes/class-upload-handler.php` | `init` / `upload` / `delete` / `swap` AJAX endpoints, rate limiting |
| `includes/class-file-server.php` | Access-checked file delivery |
| `includes/class-frontend.php` | Widget, editor modal, assets |
| `includes/class-cart.php` | Add-to-cart and checkout validation, cart display |
| `includes/class-order-files.php` | Line item meta, idempotent finalisation, production view, manifest |
| `includes/class-cleanup.php` | Hourly cleanup and retention |
| `includes/class-dropbox-client.php` / `class-dropbox-sync.php` | Dropbox API and the queued, stateful sync |
| `includes/class-zip-export.php` | Production ZIP |
| `includes/class-admin-order-panel.php` | Order panel, status, orders-list column |
| `includes/class-cli.php` | WP-CLI commands |
| `assets/js/uploader.js`, `assets/js/editor.js`, `assets/css/personalizer.css` | Front-end |
| `assets/vendor/cropperjs/` | Cropper.js 1.6.2 (MIT), bundled so there's no CDN dependency |
