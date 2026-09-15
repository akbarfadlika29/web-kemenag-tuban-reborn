(() => {
    function boot() {
        const root = document.querySelector('[data-page-interactions]');
        if (!root || root.dataset.ready) return;
        root.dataset.ready = '1';

        const main = root.closest('main') || document.querySelector('main');
        if (!main) return;

        const detail = main.querySelector('.public-detail');
        const host = detail?.querySelector('.container')
            || main.querySelector('.container');

        let heading = main.querySelector(
            '.public-detail-header h1, .public-page-header h1'
        );

        // Heading and breadcrumb are rendered by Blade.

        const meta = root.querySelector('[data-interaction-meta]');
        if (heading) heading.after(meta);

        if (host && !host.contains(root)) {
            root.classList.remove('container');
            host.append(root);
        }

        const status = root.querySelector('[data-interaction-status]');
        const likeButton = root.querySelector('[data-page-like]');
        const likeLabel = root.querySelector('[data-like-label]');
        const views = meta.querySelector('[data-view-count]');
        const likes = meta.querySelector('[data-like-count]');
        const formatter = new Intl.NumberFormat('id-ID');
        let liked = false;

        /* Experience preferences */
        const preferences = JSON.parse(root.dataset.preferences || '{}');
        const enabled = key => preferences[key] !== false;

        const viewItem = Array.from(meta.children).find(
            node => node.contains(views)
        );
        const likeItem = Array.from(meta.children).find(
            node => node.contains(likes)
        );

        if (viewItem) viewItem.hidden = !enabled('views');
        if (likeItem) likeItem.hidden = !enabled('likes');

        Array.from(meta.children).forEach(node => {
            if (node !== viewItem && node !== likeItem) {
                node.hidden = !enabled('metadata');
            }
        });

        meta.hidden = Array.from(meta.children).every(node => node.hidden);
        likeButton.hidden = !enabled('likes');

        const shareGroup = root.querySelector('.page-share-group');
        root.querySelectorAll('[data-share]').forEach(link => {
            link.hidden = !enabled(link.dataset.share);
        });

        const copyButton = root.querySelector('[data-copy-page]');
        copyButton.hidden = !enabled('copy');

        if (shareGroup) {
            shareGroup.hidden = !enabled('sharing') ||
                !Array.from(shareGroup.querySelectorAll('a,button'))
                    .some(node => !node.hidden);
        }

        async function request(payload) {
            const response = await fetch(root.dataset.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': root.dataset.csrf,
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Permintaan gagal');
            }

            const data = await response.json();

            if (!Number.isFinite(data.views) || !Number.isFinite(data.likes)) {
                throw new Error('Respons tidak dikenali');
            }

            views.textContent = formatter.format(data.views);
            likes.textContent = formatter.format(data.likes);
            liked = Boolean(data.liked);

            likeButton.setAttribute('aria-pressed', String(liked));
            likeLabel.textContent = liked ? 'Disukai' : 'Suka';
            likeButton.querySelector('[aria-hidden]').textContent = liked ? '♥' : '♡';
        }

        if (enabled('views') || enabled('likes')) request({ action: enabled('views') ? 'view' : 'status' })
            .then(() => {
                likeButton.disabled = false;
            })
            .catch(() => {
                status.textContent =
                    'Penghitung belum tersedia. Muat ulang halaman untuk mencoba lagi.';
            });

        likeButton.addEventListener('click', async () => {
            likeButton.disabled = true;
            status.textContent = '';

            try {
                await request({ action: 'like', liked: !liked });
                status.textContent = liked
                    ? 'Terima kasih atas apresiasinya.'
                    : 'Suka dibatalkan.';
            } catch {
                status.textContent =
                    'Suka belum berhasil disimpan. Muat ulang halaman lalu coba lagi.';
            } finally {
                likeButton.disabled = false;
            }
        });

        const pageUrl = new URL(window.location.href);
        pageUrl.search = '';
        pageUrl.hash = '';

        const url = encodeURIComponent(pageUrl.href);
        const title = encodeURIComponent(root.dataset.title);

        const shareUrls = {
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`,
            whatsapp: `https://wa.me/?text=${title}%20${url}`,
            telegram: `https://t.me/share/url?url=${url}&text=${title}`,
            x: `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
        };

        root.querySelectorAll('[data-share]').forEach(link => {
            link.href = shareUrls[link.dataset.share];
        });

        root.querySelector('[data-copy-page]').addEventListener('click', async () => {
            try {
                if (!navigator.clipboard) throw new Error('Clipboard unavailable');
                await navigator.clipboard.writeText(pageUrl.href);
                status.textContent = 'Tautan berhasil disalin.';
            } catch {
                let input = root.querySelector('[data-copy-fallback]');

                if (!input) {
                    input = document.createElement('input');
                    input.type = 'text';
                    input.readOnly = true;
                    input.dataset.copyFallback = '1';
                    input.className = 'page-copy-fallback';
                    input.setAttribute('aria-label', 'Tautan halaman untuk disalin');
                    root.append(input);
                }

                input.value = pageUrl.href;
                input.focus();
                input.select();
                status.textContent = 'Tekan Ctrl+C atau pilih Salin untuk menyalin tautan.';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
