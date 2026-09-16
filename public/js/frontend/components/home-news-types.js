/* Filter berita beranda berdasarkan jenis unit. */
(() => {
    function initialize() {
        document.querySelectorAll('[data-home-news-types]').forEach((tabs) => {
            if (tabs.dataset.initialized === 'true') return;

            const section = tabs.closest('.portal-block-card');
            const grid = section?.querySelector('.kemenag-news-grid');

            if (!grid) return;

            const cards = [...grid.querySelectorAll('.news-filter-card')];
            const buttons = [...tabs.querySelectorAll('[data-news-type]')];

            tabs.dataset.initialized = 'true';

            const status = document.createElement('p');
            status.setAttribute('role', 'status');
            status.style.cssText =
                'position:absolute;width:1px;height:1px;padding:0;' +
                'overflow:hidden;clip-path:inset(50%);white-space:nowrap;';
            tabs.after(status);

            tabs.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-news-type]');

                if (!button || !tabs.contains(button)) return;

                const target = button.dataset.newsType;
                let visibleCount = 0;

                buttons.forEach((item) => {
                    const selected = item === button;
                    item.classList.toggle('active', selected);
                    item.setAttribute('aria-pressed', String(selected));
                });

                cards.forEach((card) => {
                    const visible =
                        target === 'all' || card.classList.contains(target);

                    card.hidden = !visible;

                    if (visible) {
                        // Biarkan CSS kartu menentukan layout aslinya.
                        card.style.removeProperty('display');
                        visibleCount++;
                    } else {
                        card.style.setProperty('display', 'none', 'important');
                    }
                });

                status.textContent =
                    visibleCount + ' berita ditampilkan untuk ' +
                    button.textContent.trim() + '.';
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();