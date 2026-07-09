{{--
    Global media viewer modal — view image(s) and PDFs from anywhere.

    Usage (JS API):
        GlobalMedia.show('https://.../file.jpg');
        GlobalMedia.show([{ url: '...', name: 'NID Front', type: 'image' }, { url: '...pdf', name: 'Deed' }], { index: 0 });

    Usage (declarative — no JS needed):
        <a href="#" data-media-view data-media-url="{{ $url }}" data-media-name="Photo">View</a>
        <a href="#" data-media-view data-media-index="1" data-media-items='@json($items)'>View</a>
        ($items = array of { url, name?, type? })
--}}
<div class="modal fade" id="globalMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title text-truncate" id="globalMediaTitle">Preview</h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" id="globalMediaOpen" title="Open in new tab">
                        <i class="mdi mdi-open-in-new"></i>
                    </a>
                    <a href="#" download class="btn btn-sm btn-outline-secondary" id="globalMediaDownload" title="Download">
                        <i class="mdi mdi-download-outline"></i>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-dark position-relative" style="min-height: 60vh;">
                {{-- Prev / Next --}}
                <button type="button" class="btn btn-dark bg-opacity-50 position-absolute top-50 start-0 translate-middle-y ms-2 d-none" id="globalMediaPrev" style="z-index:5;">
                    <i class="mdi mdi-chevron-left mdi-24px"></i>
                </button>
                <button type="button" class="btn btn-dark bg-opacity-50 position-absolute top-50 end-0 translate-middle-y me-2 d-none" id="globalMediaNext" style="z-index:5;">
                    <i class="mdi mdi-chevron-right mdi-24px"></i>
                </button>

                {{-- Stage --}}
                <div class="d-flex align-items-center justify-content-center w-100 h-100" style="min-height: 60vh;">
                    <img src="" alt="" id="globalMediaImage" class="d-none mw-100" style="max-height: 78vh; object-fit: contain;">
                    <iframe src="" id="globalMediaFrame" class="d-none w-100" style="height: 78vh; border: 0;" title="Document preview"></iframe>
                    <div id="globalMediaFallback" class="d-none text-center text-light p-5">
                        <i class="mdi mdi-file-alert-outline mdi-48px"></i>
                        <p class="mt-2 mb-0">Preview not available.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 d-none justify-content-center" id="globalMediaThumbsWrap">
                <div class="d-flex gap-2 flex-wrap justify-content-center" id="globalMediaThumbs"></div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.GlobalMedia) { return; }

    var els = {};
    var state = { items: [], index: 0, modal: null };

    function q(id) { return document.getElementById(id); }

    function ready() {
        els = {
            modal: q('globalMediaModal'),
            title: q('globalMediaTitle'),
            image: q('globalMediaImage'),
            frame: q('globalMediaFrame'),
            fallback: q('globalMediaFallback'),
            open: q('globalMediaOpen'),
            download: q('globalMediaDownload'),
            prev: q('globalMediaPrev'),
            next: q('globalMediaNext'),
            thumbsWrap: q('globalMediaThumbsWrap'),
            thumbs: q('globalMediaThumbs'),
        };
    }

    function inferType(item) {
        if (item.type) { return item.type.toLowerCase().indexOf('pdf') !== -1 ? 'pdf' : 'image'; }
        var url = (item.url || '').split('?')[0].toLowerCase();
        return url.slice(-4) === '.pdf' ? 'pdf' : 'image';
    }

    function normalize(input) {
        if (!input) { return []; }
        var arr = Array.isArray(input) ? input : [input];
        return arr.map(function (it) {
            var obj = typeof it === 'string' ? { url: it } : Object.assign({}, it);
            obj.name = obj.name || 'Preview';
            obj.mediaType = inferType(obj);
            return obj;
        }).filter(function (it) { return it.url; });
    }

    function render() {
        var item = state.items[state.index];
        if (!item) { return; }

        els.title.textContent = item.name + (state.items.length > 1 ? ' (' + (state.index + 1) + '/' + state.items.length + ')' : '');
        els.open.href = item.url;
        els.download.href = item.url;

        els.image.classList.add('d-none');
        els.frame.classList.add('d-none');
        els.fallback.classList.add('d-none');

        if (item.mediaType === 'pdf') {
            els.frame.src = item.url;
            els.frame.classList.remove('d-none');
        } else {
            els.image.src = item.url;
            els.image.alt = item.name;
            els.image.classList.remove('d-none');
            els.image.onerror = function () {
                els.image.classList.add('d-none');
                els.fallback.classList.remove('d-none');
            };
        }

        var multi = state.items.length > 1;
        els.prev.classList.toggle('d-none', !multi);
        els.next.classList.toggle('d-none', !multi);
        els.thumbsWrap.classList.toggle('d-none', !multi);
        renderThumbs();
    }

    function renderThumbs() {
        els.thumbs.innerHTML = '';
        if (state.items.length <= 1) { return; }
        state.items.forEach(function (item, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'btn btn-sm p-0 border ' + (i === state.index ? 'border-primary border-2' : 'border-secondary');
            b.style.width = '44px';
            b.style.height = '44px';
            b.style.overflow = 'hidden';
            if (item.mediaType === 'pdf') {
                b.innerHTML = '<i class="mdi mdi-file-pdf-box mdi-24px text-danger"></i>';
            } else {
                b.innerHTML = '<img src="' + item.url + '" alt="" style="width:100%;height:100%;object-fit:cover;">';
            }
            b.addEventListener('click', function () { state.index = i; render(); });
            els.thumbs.appendChild(b);
        });
    }

    function move(delta) {
        var n = state.items.length;
        if (n <= 1) { return; }
        state.index = (state.index + delta + n) % n;
        render();
    }

    function bind() {
        els.prev.addEventListener('click', function () { move(-1); });
        els.next.addEventListener('click', function () { move(1); });
        document.addEventListener('keydown', function (e) {
            if (!state.modal || !els.modal.classList.contains('show')) { return; }
            if (e.key === 'ArrowLeft') { move(-1); }
            if (e.key === 'ArrowRight') { move(1); }
        });
        els.modal.addEventListener('hidden.bs.modal', function () {
            els.image.src = '';
            els.frame.src = '';
        });
    }

    window.GlobalMedia = {
        show: function (input, opts) {
            opts = opts || {};
            if (!els.modal) { ready(); bind(); }
            state.items = normalize(input);
            if (!state.items.length) { return; }
            state.index = Math.min(Math.max(parseInt(opts.index, 10) || 0, 0), state.items.length - 1);
            if (!state.modal) { state.modal = new bootstrap.Modal(els.modal); }
            render();
            state.modal.show();
        },
    };

    // Declarative trigger: any element with [data-media-view].
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('[data-media-view]');
        if (!trigger) { return; }
        e.preventDefault();

        var items = null;
        var raw = trigger.getAttribute('data-media-items');
        if (raw) {
            try { items = JSON.parse(raw); } catch (err) { items = null; }
        }
        if (!items) {
            items = [{
                url: trigger.getAttribute('data-media-url') || trigger.getAttribute('href'),
                name: trigger.getAttribute('data-media-name') || 'Preview',
                type: trigger.getAttribute('data-media-type') || undefined,
            }];
        }
        window.GlobalMedia.show(items, { index: trigger.getAttribute('data-media-index') || 0 });
    });
})();
</script>
