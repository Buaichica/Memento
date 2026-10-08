/* Memento Personalizer — square photo editor (Cropper.js 1.6.2).
 * Exposes window.MementoPZEditor.open({ src, cropData, onApply(blob, cropData), onReplace() }).
 */
(function () {
    'use strict';

    // Settings are localized onto the uploader script, which loads after this file,
    // so read them lazily rather than at load time.
    var cfg     = {};
    var strings = {};

    var modal, img, wrap, qualityEl, applyBtn, safeToggle;
    var cropper   = null;
    var current   = null;   // options passed to open()
    var lastFocus = null;

    function fmt(str, n) { return String(str || '').replace('%d', n); }

    function setup() {
        modal = document.getElementById('memento-editor-modal');
        if (!modal) return false;
        cfg     = (window.mementoPZ && window.mementoPZ.config) || {};
        strings = (window.mementoPZ && window.mementoPZ.strings) || {};
        img        = document.getElementById('memento-editor-img');
        wrap       = modal.querySelector('.memento-editor-canvas-wrap');
        qualityEl  = modal.querySelector('.memento-editor-quality');
        applyBtn   = document.getElementById('memento-editor-apply');
        safeToggle = modal.querySelector('.js-pz-safe-toggle');

        wrap.style.setProperty('--pz-safe', (cfg.safeAreaPercent || 8) + '%');

        modal.querySelectorAll('[data-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.getAttribute('data-action');
                if (action === 'replace') {
                    var cb = current && current.onReplace;
                    close();
                    if (cb) cb();
                    return;
                }
                if (!cropper) return;
                if (action === 'zoom-in')    cropper.zoom(0.1);
                if (action === 'zoom-out')   cropper.zoom(-0.1);
                if (action === 'rotate-cw')  cropper.rotate(90);
                if (action === 'rotate-ccw') cropper.rotate(-90);
                if (action === 'reset')      cropper.reset();
            });
        });

        if (safeToggle) {
            safeToggle.addEventListener('change', function () {
                wrap.classList.toggle('memento-pz-safe-area-on', safeToggle.checked);
            });
        }

        document.getElementById('memento-editor-cancel').addEventListener('click', close);
        modal.querySelector('.memento-editor-backdrop').addEventListener('click', close);

        modal.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { e.preventDefault(); close(); return; }
            if (e.key !== 'Tab') return;
            // Keep keyboard focus inside the dialog.
            var focusable = Array.prototype.filter.call(
                modal.querySelectorAll('button, input, [tabindex]:not([tabindex="-1"])'),
                function (el) { return !el.disabled && el.offsetParent !== null; }
            );
            if (!focusable.length) return;
            var first = focusable[0], last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        });

        applyBtn.addEventListener('click', function () {
            if (!cropper || !current) return;
            var data   = cropper.getData(true);
            var canvas = cropper.getCroppedCanvas({
                maxWidth: 4096,
                maxHeight: 4096,
                fillColor: '#fff',
                imageSmoothingQuality: 'high'
            });
            if (!canvas) return;
            var opts = current;
            applyBtn.disabled = true;
            canvas.toBlob(function (blob) {
                applyBtn.disabled = false;
                close();
                if (blob && opts.onApply) opts.onApply(blob, data);
            }, 'image/jpeg', 0.92);
        });
        return true;
    }

    function updateQuality() {
        if (!cropper || !qualityEl) return;
        var d  = cropper.getData(true);
        var px = Math.round(Math.min(d.width, d.height));
        var reject = cfg.minPxReject || 250;
        var good   = cfg.minPxRecommended || 600;
        qualityEl.className = 'memento-editor-quality';
        if (px < reject) {
            qualityEl.textContent = fmt(strings.cropTooSmall, px);
            qualityEl.classList.add('is-error');
            applyBtn.disabled = true;
        } else if (px < good) {
            qualityEl.textContent = fmt(strings.cropLow, px);
            qualityEl.classList.add('is-warning');
            applyBtn.disabled = false;
        } else {
            qualityEl.textContent = fmt(strings.cropGood, px);
            qualityEl.classList.add('is-good');
            applyBtn.disabled = false;
        }
    }

    function open(opts) {
        if (!modal && !setup()) return;
        current   = opts;
        lastFocus = document.activeElement;
        qualityEl.textContent = '';
        modal.removeAttribute('hidden');
        document.documentElement.classList.add('memento-pz-modal-open');

        if (cropper) { cropper.destroy(); cropper = null; }
        img.onload = function () {
            cropper = new window.Cropper(img, {
                viewMode: 1,
                aspectRatio: 1,
                autoCropArea: 1,
                dragMode: 'move',
                movable: true,
                zoomable: true,
                rotatable: true,
                scalable: false,
                checkOrientation: true,
                toggleDragModeOnDblclick: false,
                ready: function () {
                    if (opts.cropData) {
                        try { cropper.setData(opts.cropData); } catch (e) { /* ignore stale data */ }
                    }
                    updateQuality();
                },
                crop: updateQuality
            });
        };
        img.src = opts.src;
        applyBtn.focus();
    }

    function close() {
        if (!modal) return;
        if (cropper) { cropper.destroy(); cropper = null; }
        modal.setAttribute('hidden', '');
        document.documentElement.classList.remove('memento-pz-modal-open');
        if (current && current.revokeSrc && img.src.indexOf('blob:') === 0) {
            URL.revokeObjectURL(img.src);
        }
        img.removeAttribute('src');
        var returnFocus = current && current.returnFocus;
        current = null;
        // The slot may have been re-rendered while the editor was open.
        if (lastFocus && document.contains(lastFocus) && lastFocus.focus) lastFocus.focus();
        else if (returnFocus) returnFocus();
    }

    window.MementoPZEditor = { open: open, close: close };
}());
