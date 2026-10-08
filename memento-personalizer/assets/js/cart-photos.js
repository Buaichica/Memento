/* Memento Personalizer — photo tiles under each personalised item in the
 * block Cart and Checkout. Photo data comes from the Store API extension
 * `extensions['memento-personalizer'].photos` (see Memento_PZ_Cart).
 * Fails silently if WooCommerce's markup changes: the "N photos" label and the
 * photo-1 thumbnail still show.
 */
(function () {
    'use strict';

    var S = window.mementoPZCart || {};

    function cartItems() {
        try {
            var store = window.wp && window.wp.data && window.wp.data.select('wc/store/cart');
            return (store && store.getCartData().items) || [];
        } catch (e) {
            return [];
        }
    }

    function build(photos) {
        var strip = document.createElement('div');
        strip.className = 'mm-cart-photos';
        photos.forEach(function (p) {
            var tile = document.createElement('span');
            tile.className = 'mm-cart-photos__tile' + (p.low ? ' is-low' : '');
            if (p.low) tile.title = S.lowQuality || '';
            var img = document.createElement('img');
            img.src = p.thumb;
            img.alt = String(S.photo || 'Photo %d').replace('%d', p.slot);
            img.loading = 'lazy';
            img.width = 44;
            img.height = 44;
            tile.appendChild(img);
            strip.appendChild(tile);
        });
        return strip;
    }

    function render() {
        var items = cartItems();
        if (!items.length) return;

        // Cart table and checkout summary each list items in cart order.
        [document.querySelectorAll('.wc-block-cart-items__row'),
         document.querySelectorAll('.wc-block-components-order-summary-item')].forEach(function (rows) {
            Array.prototype.forEach.call(rows, function (row, i) {
                var item   = items[i];
                var ext    = item && item.extensions && item.extensions['memento-personalizer'];
                var photos = (ext && ext.photos) || [];
                var key    = item ? item.key + ':' + photos.map(function (p) { return p.thumb; }).join('|') : '';

                // The marker lives on the strip itself: React re-renders can reset row attributes.
                var old = row.querySelector('.mm-cart-photos');
                if (old && old.getAttribute('data-mm-key') === key) return; // Already up to date.
                if (old) old.remove();
                if (!photos.length) return;

                var anchor = row.querySelector('.wc-block-components-product-details') ||
                             row.querySelector('.wc-block-components-product-metadata') ||
                             row.querySelector('.wc-block-components-order-summary-item__description');
                if (!anchor) return;
                var strip = build(photos);
                strip.setAttribute('data-mm-key', key);
                anchor.parentNode.insertBefore(strip, anchor.nextSibling);
            });
        });
    }

    var queued = false;
    function schedule() {
        if (queued) return;
        queued = true;
        window.requestAnimationFrame(function () { queued = false; render(); });
    }

    function start() {
        if (window.wp && window.wp.data && window.wp.data.subscribe) {
            window.wp.data.subscribe(schedule);
        }
        // The blocks re-render rows (quantity changes, removals), so watch the DOM too.
        new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var t = mutations[i].target;
                if (t.nodeType === 1 && !(t.closest && t.closest('.mm-cart-photos'))) { schedule(); return; }
            }
        }).observe(document.body, { childList: true, subtree: true });
        schedule();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}());
