{{-- Reusable clipboard + toast helpers for customer profile links. --}}
<div class="bs-toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="hkToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body d-flex align-items-center justify-content-between">
            <span id="hkToastMsg"></span>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        window.showToast = function (message, type) {
            var el = document.getElementById('hkToast');
            var body = el.querySelector('.toast-body');
            document.getElementById('hkToastMsg').textContent = message;
            body.parentElement.className = 'toast text-white bg-' + (type === 'error' ? 'danger' : 'success');
            try {
                bootstrap.Toast.getOrCreateInstance(el, { delay: 4000 }).show();
            } catch (e) {
                alert(message);
            }
        };

        // Clipboard with graceful fallback for non-secure (http) contexts.
        window.copyToClipboard = function (text, okMessage) {
            var done = function () { window.showToast(okMessage || 'Link copied to clipboard.', 'success'); };
            var fail = function () { window.prompt('Copy this link:', text); };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done).catch(function () {
                    legacyCopy(text) ? done() : fail();
                });
            } else {
                legacyCopy(text) ? done() : fail();
            }
        };

        function legacyCopy(text) {
            try {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.focus();
                ta.select();
                var ok = document.execCommand('copy');
                document.body.removeChild(ta);
                return ok;
            } catch (e) {
                return false;
            }
        }

        // Delegate clicks on any element carrying data-copy-link.
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-copy-link]');
            if (!trigger) return;
            e.preventDefault();
            window.copyToClipboard(trigger.getAttribute('data-copy-link'), 'Profile link copied to clipboard.');
        });

        // Auto-copy a freshly generated link flashed from the server.
        @if (session('copy_link'))
            document.addEventListener('DOMContentLoaded', function () {
                window.copyToClipboard(@json(session('copy_link')), 'Profile link copied to clipboard.');
                @if (session('success'))
                    window.showToast(@json(session('success')), 'success');
                @endif
            });
        @elseif (session('success'))
            document.addEventListener('DOMContentLoaded', function () {
                window.showToast(@json(session('success')), 'success');
            });
        @endif
    })();
</script>
@endpush
