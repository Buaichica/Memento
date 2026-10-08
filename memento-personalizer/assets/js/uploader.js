/* Memento Personalizer — photo upload widget.
 *
 * Flow: init session (server) → upload/replace per slot → edit (Cropper) →
 * review → Add to Cart. The server is authoritative for every validation;
 * client checks here are only for fast feedback.
 */
(function ($) {
    'use strict';

    var PZ      = window.mementoPZ || {};
    var cfg     = PZ.config || {};
    var S       = PZ.strings || {};
    var MAX_CONCURRENT = 2;

    function fmt(str) {
        var args = Array.prototype.slice.call(arguments, 1), i = 0;
        return String(str || '')
            .replace(/%(\d)\$d/g, function (m, n) { return args[n - 1]; })
            .replace(/%d/g, function () { return args[i++]; });
    }

    function storageGet(key) { try { return window.localStorage.getItem(key); } catch (e) { return null; } }
    function storageSet(key, v) { try { window.localStorage.setItem(key, v); } catch (e) { /* private mode */ } }

    function init() {
        var widget = document.querySelector('.memento-pz.memento-upload-widget');
        if (!widget) return;

        var form        = document.querySelector('form.cart');
        var grid        = widget.querySelector('.memento-upload-grid');
        var statusEl    = widget.querySelector('.memento-pz-status');
        var reviewEl    = widget.querySelector('.memento-pz-review');
        var reviewSum   = widget.querySelector('.memento-pz-review__summary');
        var reviewCheck = widget.querySelector('.js-pz-review-check');
        var ctaNote     = widget.querySelector('.js-upload-cta-note');
        var multiInput  = widget.querySelector('.memento-pz-multi-input');
        var pickBtn     = widget.querySelector('.memento-pz-pick');
        var progressCnt = widget.querySelector('.upload-progress__count');
        var progressBar = widget.querySelector('.upload-progress__fill');
        var hidden      = document.querySelector('.memento-pz-session-input');

        if (!hidden && form) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'memento_pz_session';
            hidden.className = 'memento-pz-session-input';
            form.appendChild(hidden);
        }

        var isVariable  = !!(form && form.classList.contains('variations_form'));
        var productId   = parseInt(widget.getAttribute('data-product-id'), 10) || PZ.productId;
        var storageKey  = 'memento_pz_session_' + productId;
        var state = {
            sessionId: null,
            nonce: null,
            required: parseInt(widget.getAttribute('data-photo-count'), 10) || 0,
            variationId: 0,
            slots: [],
            queue: [],
            active: 0,
            ready: false
        };

        /* ── Server calls ─────────────────────────────────── */

        function request(action, data, onProgress) {
            return new Promise(function (resolve, reject) {
                var fd = new FormData();
                fd.append('action', 'memento_pz_' + action);
                Object.keys(data || {}).forEach(function (k) { fd.append(k, data[k]); });
                var xhr = new XMLHttpRequest();
                xhr.open('POST', PZ.ajaxUrl, true);
                xhr.withCredentials = true;
                xhr.responseType = 'json';
                if (onProgress && xhr.upload) {
                    xhr.upload.onprogress = function (e) { if (e.lengthComputable) onProgress(e.loaded / e.total); };
                }
                xhr.onload = function () {
                    var body = xhr.response;
                    if (body && body.success) { resolve(body.data); return; }
                    var err = (body && body.data) || {};
                    reject({ code: err.code || 'http_' + xhr.status, message: err.message || S.uploadFail });
                };
                xhr.onerror = function () { reject({ code: 'network', message: S.uploadFail }); };
                xhr.send(fd);
            });
        }

        // Mutating call with nonce; refreshes the nonce once if it has expired (e.g. cached page).
        function call(action, data, onProgress) {
            var payload = Object.assign({ nonce: state.nonce, session_id: state.sessionId }, data);
            return request(action, payload, onProgress).catch(function (err) {
                if (err.code !== 'nonce') throw err;
                return startSession().then(function () {
                    payload.nonce = state.nonce;
                    payload.session_id = state.sessionId;
                    return request(action, payload, onProgress);
                });
            });
        }

        function startSession() {
            return request('init', {
                product_id: productId,
                variation_id: state.variationId,
                session_id: state.sessionId || storageGet(storageKey) || ''
            }).then(function (data) {
                state.nonce = data.nonce;
                if (data.sessionId !== state.sessionId) {
                    // New session: forget any local-only state.
                    state.slots.forEach(function (s) { if (s) s.file = null; });
                }
                state.sessionId = data.sessionId;
                storageSet(storageKey, data.sessionId);
                if (hidden) hidden.value = data.sessionId;
                applyServerSlots(data.required, data.slots);
                state.ready = true;
                return data;
            });
        }

        /* ── State helpers ────────────────────────────────── */

        function emptySlot() {
            return { status: 'empty', uploadId: null, thumbUrl: '', fullUrl: '', quality: '', file: null, cropData: null, error: '', retry: null, progress: 0 };
        }

        function applyServerSlots(required, serverSlots) {
            var byUpload = {};
            state.slots.forEach(function (s) { if (s && s.uploadId) byUpload[s.uploadId] = s; });
            var next = [];
            for (var i = 0; i < required; i++) {
                var prev = state.slots[i];
                // Keep in-flight/error slots as they are.
                next.push(prev && (prev.status === 'uploading' || prev.status === 'error') ? prev : emptySlot());
            }
            serverSlots.forEach(function (s) {
                if (s.slot >= required) return;
                var old = byUpload[s.uploadId];
                next[s.slot] = {
                    status: 'ready', uploadId: s.uploadId, thumbUrl: s.thumbUrl, fullUrl: s.fullUrl,
                    quality: s.quality, file: old ? old.file : null, cropData: old ? old.cropData : null,
                    error: '', retry: null, progress: 1
                };
            });
            state.required = required;
            state.slots = next;
            render();
        }

        /* ── Rendering ────────────────────────────────────── */

        var ICON_IMG  = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
        var ICON_EDIT = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';

        function el(tag, cls, html) {
            var e = document.createElement(tag);
            if (cls) e.className = cls;
            if (html) e.innerHTML = html;
            return e;
        }

        function button(cls, label, html, onClick) {
            var b = el('button', cls, html);
            b.type = 'button';
            b.setAttribute('aria-label', label);
            b.title = label;
            b.addEventListener('click', function (e) { e.stopPropagation(); onClick(); });
            return b;
        }

        function render() {
            // Rebuilding the grid would drop keyboard focus; remember and restore it.
            var active   = document.activeElement;
            var focusLi  = active && grid.contains(active) ? active.closest('.upload-slot') : null;
            var focusIdx = focusLi ? focusLi.getAttribute('data-slot') : null;
            var focusCls = focusIdx !== null ? (active.className.split(' ')[0]) : null;

            grid.innerHTML = '';
            state.slots.forEach(function (slot, i) { grid.appendChild(renderSlot(slot, i)); });

            if (focusIdx !== null) {
                var li = grid.querySelector('[data-slot="' + focusIdx + '"]');
                var target = li && (li.querySelector('.' + focusCls) || li.querySelector('.upload-slot__hit'));
                if (target) target.focus();
            }
            widget.querySelectorAll('.js-pz-required').forEach(function (n) { n.textContent = state.required; });
            var title = widget.querySelector('.js-pz-title');
            if (title) {
                title.textContent = state.required === 1
                    ? title.getAttribute('data-singular')
                    : fmt(title.getAttribute('data-plural'), state.required);
            }
            updateSummary();
        }

        function renderSlot(slot, i) {
            var li    = el('li', 'upload-slot');
            var inner = el('div', 'upload-slot__inner');
            var label = (S.photo || 'Photo') + ' ' + (i + 1);
            li.setAttribute('data-slot', i);
            li.classList.toggle('is-uploaded', slot.status === 'ready');
            li.classList.toggle('is-uploading', slot.status === 'uploading');
            li.classList.toggle('is-error', slot.status === 'error');
            li.classList.toggle('is-low', slot.status === 'ready' && slot.quality === 'low');

            // Full-size hit area: pick a file when empty, open the editor when filled.
            var hit = el('button', 'upload-slot__hit');
            hit.type = 'button';
            if (slot.status === 'ready') {
                hit.setAttribute('aria-label', label + ' — ' + (S.edit || 'Edit'));
                hit.addEventListener('click', function () { openEditor(i); });
            } else {
                hit.setAttribute('aria-label', label + ' — ' + (S.addPhoto || 'Add photo'));
                hit.addEventListener('click', function () { pickFor(i); });
                hit.disabled = slot.status === 'uploading';
            }
            inner.appendChild(hit);

            if (slot.status === 'empty' || slot.status === 'error' && !slot.thumbUrl) {
                inner.appendChild(el('div', 'upload-slot__placeholder', ICON_IMG + '<span>' + label + '</span>'));
            }
            if (slot.thumbUrl) {
                var img = el('img', 'upload-slot__preview');
                img.src = slot.thumbUrl;
                img.alt = label;
                inner.appendChild(img);
            }
            if (slot.status === 'uploading') {
                var spin = el('div', 'upload-slot__spinner', '<div class="upload-slot__spinner-ring"></div>');
                var pct  = el('span', 'upload-slot__pct');
                pct.textContent = slot.progress >= 1 ? S.processing : Math.round(slot.progress * 100) + '%';
                spin.appendChild(pct);
                inner.appendChild(spin);
            }
            if (slot.status === 'ready' && slot.quality === 'low') {
                var badge = el('span', 'upload-slot__badge');
                badge.textContent = '!';
                badge.title = S.lowQuality;
                badge.setAttribute('aria-label', S.lowQuality);
                inner.appendChild(badge);
            }

            if (slot.status === 'ready') {
                var bar = el('div', 'upload-slot__toolbar');
                bar.appendChild(button('upload-slot__tool', S.edit, ICON_EDIT, function () { openEditor(i); }));
                if (i > 0) bar.appendChild(button('upload-slot__tool', S.moveEarlier, '&#8249;', function () { swap(i, i - 1); }));
                if (i < state.required - 1) bar.appendChild(button('upload-slot__tool', S.moveLater, '&#8250;', function () { swap(i, i + 1); }));
                bar.appendChild(button('upload-slot__tool upload-slot__tool--remove', S.remove, '&times;', function () { removeSlot(i); }));
                inner.appendChild(bar);
            }

            li.appendChild(inner);

            if (slot.status === 'error') {
                var err = el('div', 'upload-slot__error');
                err.setAttribute('role', 'alert');
                err.appendChild(document.createTextNode(slot.error + ' '));
                if (slot.retry) {
                    err.appendChild(button('upload-slot__retry', S.retry, S.retry, function () { slot.retry(); }));
                }
                li.appendChild(err);
            }
            li.addEventListener('dragover', function (e) { e.preventDefault(); li.classList.add('is-dragover'); });
            li.addEventListener('dragleave', function () { li.classList.remove('is-dragover'); });
            li.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                li.classList.remove('is-dragover');
                widget.classList.remove('is-dragover');
                var files = e.dataTransfer && e.dataTransfer.files;
                if (files && files.length) addFiles(files, i);
            });
            return li;
        }

        function updateSummary() {
            var ready     = state.slots.filter(function (s) { return s.status === 'ready'; }).length;
            var low       = state.slots.filter(function (s) { return s.status === 'ready' && s.quality === 'low'; }).length;
            var allReady  = ready === state.required && state.required > 0;

            if (progressCnt) progressCnt.textContent = ready;
            if (progressBar) progressBar.style.width = (state.required ? (ready / state.required) * 100 : 0) + '%';
            if (ctaNote) ctaNote.style.display = allReady ? 'none' : '';
            statusEl.textContent = fmt(S.progress, ready, state.required);

            if (reviewEl) {
                reviewEl.hidden = !allReady;
                if (reviewSum) reviewSum.textContent = low ? fmt(S.reviewLow, low) : '';
                var confirm = reviewEl.querySelector('.memento-pz-review__confirm');
                if (confirm) confirm.hidden = !cfg.requireReview;
                if (!allReady && reviewCheck) reviewCheck.checked = false;
            }

            var btn = form && form.querySelector('.single_add_to_cart_button');
            if (btn) {
                var blocked = !allReady || (cfg.requireReview && reviewCheck && !reviewCheck.checked);
                btn.classList.toggle('memento-pz-incomplete', blocked);
                btn.setAttribute('aria-disabled', blocked ? 'true' : 'false');
            }
        }

        function setStatus(msg) { statusEl.textContent = msg; }

        /* ── Actions ──────────────────────────────────────── */

        var pendingPickSlot = null;
        var slotInput = el('input');
        slotInput.type = 'file';
        slotInput.accept = (cfg.allowedMimes || ['image/jpeg', 'image/png', 'image/webp']).join(',');
        slotInput.hidden = true;
        widget.appendChild(slotInput);
        slotInput.addEventListener('change', function () {
            if (slotInput.files && slotInput.files[0] && pendingPickSlot !== null) {
                queueUpload(pendingPickSlot, slotInput.files[0], 'original');
            }
            slotInput.value = '';
            pendingPickSlot = null;
        });

        function hasSession() {
            if (state.sessionId) return true;
            setStatus(isVariable ? S.chooseOption : S.sessionFail);
            return false;
        }

        function pickFor(i) {
            if (!hasSession()) return;
            pendingPickSlot = i;
            slotInput.click();
        }

        function clientCheck(file) {
            var allowed = cfg.allowedMimes || ['image/jpeg', 'image/png', 'image/webp'];
            if (allowed.indexOf(file.type) === -1) return S.invalidType;
            if (file.size > (cfg.maxFileMb || 15) * 1024 * 1024) return fmt(S.tooLarge, cfg.maxFileMb || 15);
            return '';
        }

        // Fill empty slots (starting at startSlot when dropped onto a slot) with the given files.
        function addFiles(fileList, startSlot) {
            if (!hasSession()) return;
            var files = Array.prototype.slice.call(fileList);
            if (startSlot !== undefined && files.length === 1) {
                queueUpload(startSlot, files[0], 'original');
                return;
            }
            var targets = [];
            state.slots.forEach(function (s, i) { if (s.status === 'empty' || s.status === 'error') targets.push(i); });
            files.slice(0, targets.length).forEach(function (f, n) { queueUpload(targets[n], f, 'original'); });
            if (files.length > targets.length) setStatus(S.extraIgnored);
        }

        function queueUpload(i, file, source, cropData) {
            var slot = state.slots[i];
            var problem = source === 'original' ? clientCheck(file) : '';
            if (problem) {
                state.slots[i] = Object.assign(slot, { status: 'error', error: problem, retry: null });
                render();
                return;
            }
            var previous = slot.status === 'ready' ? Object.assign({}, slot) : null;
            Object.assign(slot, { status: 'uploading', progress: 0, error: '', retry: null });
            if (source === 'original') {
                slot.file = file;
                slot.cropData = null;
                slot.thumbUrl = URL.createObjectURL(file); // Instant local preview.
            } else if (cropData) {
                slot.pendingCrop = cropData;
            }
            render();
            state.queue.push({ slot: slot, file: file, source: source, previous: previous });
            pump();
        }

        function pump() {
            while (state.active < MAX_CONCURRENT && state.queue.length) {
                runUpload(state.queue.shift());
            }
        }

        function runUpload(job) {
            state.active++;
            var slot = job.slot;
            var lastRender = 0;
            call('upload', {
                slot: state.slots.indexOf(slot),
                source: job.source,
                photo: job.file
            }, function (p) {
                slot.progress = p;
                var now = Date.now();
                if (now - lastRender > 150 || p >= 1) { lastRender = now; render(); }
            }).then(function (data) {
                if (slot.thumbUrl && slot.thumbUrl.indexOf('blob:') === 0) URL.revokeObjectURL(slot.thumbUrl);
                Object.assign(slot, {
                    status: 'ready', uploadId: data.uploadId, thumbUrl: data.thumbUrl, fullUrl: data.fullUrl,
                    quality: data.quality, progress: 1, error: '', retry: null
                });
                if (slot.pendingCrop) { slot.cropData = slot.pendingCrop; slot.pendingCrop = null; }
            }).catch(function (err) {
                if (job.previous && job.source === 'edited') {
                    // Edit failed: keep the previously uploaded version.
                    Object.assign(slot, job.previous);
                    setStatus(err.message || S.uploadFail);
                } else {
                    if (slot.thumbUrl && slot.thumbUrl.indexOf('blob:') === 0) URL.revokeObjectURL(slot.thumbUrl);
                    Object.assign(slot, { status: 'error', thumbUrl: '', error: err.message || S.uploadFail });
                    var transient = err.code === 'network' || /^http_5/.test(err.code) || err.code === 'server' || err.code === 'rate_limited';
                    slot.retry = transient ? function () {
                        Object.assign(slot, { status: 'empty', error: '', retry: null });
                        queueUpload(state.slots.indexOf(slot), job.file, job.source);
                    } : null;
                }
                if (err.code === 'session_locked' || err.code === 'session') {
                    // Session moved to the cart or expired: start a fresh one.
                    state.sessionId = null;
                    storageSet(storageKey, '');
                    startSession();
                }
            }).then(function () {
                state.active--;
                render();
                pump();
            });
        }

        function openEditor(i) {
            var slot = state.slots[i];
            if (!slot || slot.status !== 'ready' || !window.MementoPZEditor) return;
            var useOriginal = !!slot.file;
            window.MementoPZEditor.open({
                src: useOriginal ? URL.createObjectURL(slot.file) : slot.fullUrl,
                revokeSrc: useOriginal,
                cropData: useOriginal ? slot.cropData : null,
                onApply: function (blob, data) {
                    var file = new File([blob], 'edited.jpg', { type: 'image/jpeg' });
                    queueUpload(i, file, 'edited', useOriginal ? data : null);
                },
                onReplace: function () { pickFor(i); },
                returnFocus: function () {
                    var hit = grid.querySelector('[data-slot="' + i + '"] .upload-slot__hit');
                    if (hit) hit.focus();
                }
            });
        }

        function removeSlot(i) {
            var slot = state.slots[i];
            if (!slot || slot.status !== 'ready') return;
            call('delete', { slot: i }).then(function (data) {
                state.slots[i] = emptySlot();
                applyServerSlots(data.required, data.slots);
            }).catch(function (err) { setStatus(err.message); });
        }

        function swap(a, b) {
            if (state.active || state.queue.length) { setStatus(S.busy); return; }
            call('swap', { from: a, to: b }).then(function (data) {
                var tmp = state.slots[a];
                state.slots[a] = state.slots[b];
                state.slots[b] = tmp;
                applyServerSlots(data.required, data.slots);
                var target = grid.querySelector('[data-slot="' + b + '"] .upload-slot__hit');
                if (target) target.focus();
            }).catch(function (err) { setStatus(err.message); });
        }

        /* ── Wiring ───────────────────────────────────────── */

        pickBtn.addEventListener('click', function () { if (hasSession()) multiInput.click(); });
        multiInput.addEventListener('change', function () {
            if (multiInput.files && multiInput.files.length) addFiles(multiInput.files);
            multiInput.value = '';
        });

        widget.addEventListener('dragover', function (e) { e.preventDefault(); widget.classList.add('is-dragover'); });
        widget.addEventListener('dragleave', function (e) { if (!widget.contains(e.relatedTarget)) widget.classList.remove('is-dragover'); });
        widget.addEventListener('drop', function (e) {
            e.preventDefault();
            widget.classList.remove('is-dragover');
            if (e.dataTransfer && e.dataTransfer.files.length) addFiles(e.dataTransfer.files);
        });

        if (reviewCheck) reviewCheck.addEventListener('change', updateSummary);

        // Guard Add to Cart (capture phase — runs before WooCommerce's handlers).
        document.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.single_add_to_cart_button');
            if (!btn || !form || !form.contains(btn)) return;
            if (btn.classList.contains('disabled')) return; // Let WooCommerce explain missing variation choices.

            e.preventDefault();
            e.stopImmediatePropagation();

            var ready = state.slots.filter(function (s) { return s.status === 'ready'; }).length;
            if (state.active || state.queue.length) { setStatus(S.busy); return; }
            if (!state.ready || ready !== state.required) {
                setStatus(fmt(S.needAll, state.required));
                widget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (cfg.requireReview && reviewCheck && !reviewCheck.checked) {
                setStatus(S.needReview);
                reviewCheck.focus();
                return;
            }

            // Submit the form natively so WooCommerce's form handler receives memento_pz_session.
            var productField = form.querySelector('input[name="add-to-cart"]');
            if (!productField) {
                productField = document.createElement('input');
                productField.type = 'hidden';
                productField.name = 'add-to-cart';
                form.appendChild(productField);
            }
            productField.value = btn.value || productId;
            hidden.value = state.sessionId;
            btn.classList.add('loading');
            form.submit();
        }, true);

        // Variable products: pack size may change the number of photos.
        if (isVariable && $) {
            $(form).on('found_variation', function (e, variation) {
                var count = parseInt(variation.memento_photo_count, 10) || 0;
                if (!count || variation.variation_id === state.variationId) return;
                state.variationId = variation.variation_id;
                startSession().catch(function (err) { setStatus(err.message || S.sessionFail); });
            });
        }

        render();
        startSession().catch(function (err) {
            // Variable products may only become personalised once an option is chosen.
            setStatus(isVariable && err.code === 'not_personalised' ? S.chooseOption : (err.message || S.sessionFail));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}(window.jQuery));
