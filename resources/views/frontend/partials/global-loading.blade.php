{{-- Loading global frontend. Tersembunyi jika JavaScript tidak berjalan. --}}
<style>
#public-glass-loader[hidden] {
    display: none !important;
}

#public-glass-loader {
    position: fixed;
    inset: 0;
    z-index: 9998;
    display: grid;
    place-items: center;
    padding: 24px;
    background: rgb(242 247 244 / 55%);


    pointer-events: none;
    opacity: 0;
    transition: opacity 180ms ease;
}

#public-glass-loader.is-visible {
    opacity: 1;
}

#public-glass-loader .public-loader-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    box-sizing: border-box;
    width: min(260px, 100%);
    padding: 28px 24px;
    border: 1px solid rgb(255 255 255 / 90%);
    border-radius: 22px;
    background: rgb(255 255 255 / 88%);
    box-shadow:
        0 16px 48px rgb(29 75 53 / 10%),
        inset 0 1px 0 #fff;
    color: #254d3b;
    text-align: center;
}

#public-glass-loader .public-loader-spinner {
    box-sizing: border-box;
    width: 42px;
    height: 42px;
    border: 3px solid #dfece5;
    border-top-color: var(--experience-accent, #247052);
    border-right-color: var(--experience-accent, #247052);
    border-radius: 50%;
    animation: public-loader-spin 800ms linear infinite;
}

#public-glass-loader .public-loader-title {
    margin: 0;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.5;
    letter-spacing: normal;
}

@keyframes public-loader-spin {
    to { transform: rotate(360deg); }
}

@media (prefers-reduced-motion: reduce) {
    #public-glass-loader {
        transition: none;
    }

    #public-glass-loader .public-loader-spinner {
        animation: none;
    }
}
/* PUBLIC LOADER CARD GLASS */
#public-glass-loader .public-loader-card {
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
}
</style>

<div id="public-glass-loader" hidden role="status" aria-live="polite">
    <div class="public-loader-card">
        <span class="public-loader-spinner" aria-hidden="true"></span>
        <p class="public-loader-title">Memuat halaman…</p>
    </div>
</div>

<script>
(() => {
    const loader = document.getElementById('public-glass-loader');

    if (!loader || loader.dataset.initialized === 'true') return;

    loader.dataset.initialized = 'true';

    let finished = false;
    let showTimer;
    let maximumTimer;
    let hideTimer;

    function finish(immediate = false) {
        finished = true;

        clearTimeout(showTimer);
        clearTimeout(maximumTimer);
        clearTimeout(hideTimer);

        loader.classList.remove('is-visible');

        if (immediate || loader.hidden) {
            loader.hidden = true;
            return;
        }

        hideTimer = setTimeout(() => {
            loader.hidden = true;
        }, 200);
    }

    if (document.readyState !== 'loading') return;

    // Hindari kilatan loading pada halaman yang terbuka cepat.
    showTimer = setTimeout(() => {
        if (finished) return;

        loader.hidden = false;

        requestAnimationFrame(() => {
            if (!finished) loader.classList.add('is-visible');
        });
    }, 180);

    // Loading tidak menunggu selamanya jika gambar/aset lambat.
    maximumTimer = setTimeout(() => finish(), 5000);

    document.addEventListener('DOMContentLoaded', () => finish(), { once: true });

    // Pastikan overlay hilang ketika kembali melalui tombol browser.
    window.addEventListener('pageshow', () => finish(true));
    window.addEventListener('pagehide', () => finish(true));

    // Pengguna bisa langsung berinteraksi tanpa menunggu overlay.
    window.addEventListener('pointerdown', () => finish(true), { once: true });
    window.addEventListener('keydown', () => finish(true), { once: true });
})();
</script>