document.addEventListener(
    'DOMContentLoaded',
    function () {
        document
            .querySelectorAll(
                '[data-related-links-slider]'
            )
            .forEach(
                initRelatedLinksSlider
            );
    }
);

function initRelatedLinksSlider(root) {
    const viewport =
        root.querySelector(
            '[data-related-links-viewport]'
        );

    const track =
        root.querySelector(
            '[data-related-links-track]'
        );

    const previous =
        root.querySelector(
            '[data-related-links-prev]'
        );

    const next =
        root.querySelector(
            '[data-related-links-next]'
        );

    if (
        ! viewport
        || ! track
        || ! previous
        || ! next
    ) {
        return;
    }

    const items =
        Array.from(
            track.children
        );

    if (! items.length) {
        return;
    }

    const reduceMotion =
        window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        );

    const autoplayDelay = 4500;

    let autoplayTimer = null;
    let resumeTimer = null;
    let normalizeTimer = null;

    let paused = false;
    let loopEnabled = false;
    let sliderEnabled = false;
    let isJumping = false;

    let middleStart = 0;
    let endStart = 0;
    let segmentWidth = 0;

    function removeIds(element) {
        element.removeAttribute('id');

        element
            .querySelectorAll('[id]')
            .forEach(
                function (child) {
                    child.removeAttribute('id');
                }
            );
    }

    function createClone(item) {
        const clone =
            item.cloneNode(true);

        clone.setAttribute(
            'data-related-link-clone',
            'true'
        );

        clone.setAttribute(
            'aria-hidden',
            'true'
        );

        clone.setAttribute(
            'inert',
            ''
        );

        removeIds(clone);

        return clone;
    }

    function removeClones() {
        track
            .querySelectorAll(
                '[data-related-link-clone]'
            )
            .forEach(
                function (clone) {
                    clone.remove();
                }
            );

        loopEnabled = false;

        middleStart = 0;
        endStart = 0;
        segmentWidth = 0;
    }

    function originalHasOverflow() {
        return (
            track.scrollWidth
            > viewport.clientWidth + 2
        );
    }

    function jumpTo(left) {
        isJumping = true;

        const previousBehavior =
            viewport.style
                .scrollBehavior;

        const previousSnap =
            viewport.style
                .scrollSnapType;

        viewport.style
            .scrollBehavior =
                'auto';

        viewport.style
            .scrollSnapType =
                'none';

        viewport.scrollLeft =
            left;

        void viewport.offsetWidth;

        viewport.style
            .scrollBehavior =
                previousBehavior;

        viewport.style
            .scrollSnapType =
                previousSnap;

        window.requestAnimationFrame(
            function () {
                isJumping = false;
            }
        );
    }

    function buildLoop() {
        if (loopEnabled) {
            return;
        }

        const before =
            document.createDocumentFragment();

        const after =
            document.createDocumentFragment();

        const afterClones = [];

        items.forEach(
            function (item) {
                before.append(
                    createClone(item)
                );

                const clone =
                    createClone(item);

                afterClones.push(
                    clone
                );

                after.append(
                    clone
                );
            }
        );

        track.prepend(before);
        track.append(after);

        middleStart =
            items[0].offsetLeft;

        endStart =
            afterClones[0]
                .offsetLeft;

        segmentWidth =
            endStart
            - middleStart;

        loopEnabled =
            segmentWidth > 0;

        if (loopEnabled) {
            jumpTo(
                middleStart
            );
        }
    }

    function slideDistance() {
        const firstItem =
            items[0];

        if (! firstItem) {
            return viewport.clientWidth;
        }

        const style =
            window.getComputedStyle(
                track
            );

        const gap =
            parseFloat(
                style.columnGap
                || style.gap
                || '0'
            );

        return (
            firstItem
                .getBoundingClientRect()
                .width
            + gap
        );
    }

    function normalizeLoopPosition() {
        if (
            ! loopEnabled
            || isJumping
            || segmentWidth <= 0
        ) {
            return;
        }

        let left =
            viewport.scrollLeft;

        let target = left;

        while (
            target
            >= endStart - 1
        ) {
            target -=
                segmentWidth;
        }

        while (
            target
            < middleStart - 1
        ) {
            target +=
                segmentWidth;
        }

        if (
            Math.abs(
                target - left
            ) > 1
        ) {
            jumpTo(target);
        }
    }

    function scheduleNormalize(
        delay = 90
    ) {
        if (normalizeTimer) {
            window.clearTimeout(
                normalizeTimer
            );
        }

        normalizeTimer =
            window.setTimeout(
                normalizeLoopPosition,
                delay
            );
    }

    function scrollByDistance(
        distance
    ) {
        if (! sliderEnabled) {
            return;
        }

        viewport.scrollBy({
            left:
                distance,

            behavior:
                reduceMotion.matches
                    ? 'auto'
                    : 'smooth',
        });

        scheduleNormalize(
            reduceMotion.matches
                ? 0
                : 450
        );
    }

    function goNext() {
        scrollByDistance(
            slideDistance()
        );
    }

    function goPrevious() {
        scrollByDistance(
            -slideDistance()
        );
    }

    function stopAutoplay() {
        if (! autoplayTimer) {
            return;
        }

        window.clearInterval(
            autoplayTimer
        );

        autoplayTimer = null;
    }

    function startAutoplay() {
        stopAutoplay();

        if (
            paused
            || reduceMotion.matches
            || ! sliderEnabled
            || document.hidden
        ) {
            return;
        }

        autoplayTimer =
            window.setInterval(
                goNext,
                autoplayDelay
            );
    }

    function pause() {
        paused = true;

        stopAutoplay();

        if (resumeTimer) {
            window.clearTimeout(
                resumeTimer
            );

            resumeTimer = null;
        }
    }

    function resume(delay = 0) {
        if (resumeTimer) {
            window.clearTimeout(
                resumeTimer
            );
        }

        resumeTimer =
            window.setTimeout(
                function () {
                    paused = false;

                    startAutoplay();
                },
                delay
            );
    }

    function refresh() {
        stopAutoplay();

        if (normalizeTimer) {
            window.clearTimeout(
                normalizeTimer
            );

            normalizeTimer = null;
        }

        removeClones();

        jumpTo(0);

        sliderEnabled =
            originalHasOverflow();

        root.classList.toggle(
            'related-links-slider-is-static',
            ! sliderEnabled
        );

        previous.disabled =
            ! sliderEnabled;

        next.disabled =
            ! sliderEnabled;

        if (! sliderEnabled) {
            jumpTo(0);

            return;
        }

        buildLoop();

        if (! paused) {
            startAutoplay();
        }
    }

    previous.addEventListener(
        'click',
        function () {
            pause();

            goPrevious();

            resume(2500);
        }
    );

    next.addEventListener(
        'click',
        function () {
            pause();

            goNext();

            resume(2500);
        }
    );

    viewport.addEventListener(
        'scroll',
        function () {
            if (isJumping) {
                return;
            }

            scheduleNormalize();
        },
        {
            passive: true,
        }
    );

    root.addEventListener(
        'mouseenter',
        pause
    );

    root.addEventListener(
        'mouseleave',
        function () {
            resume();
        }
    );

    root.addEventListener(
        'focusin',
        pause
    );

    root.addEventListener(
        'focusout',
        function (event) {
            if (
                root.contains(
                    event.relatedTarget
                )
            ) {
                return;
            }

            resume();
        }
    );

    viewport.addEventListener(
        'touchstart',
        function () {
            pause();
        },
        {
            passive: true,
        }
    );

    viewport.addEventListener(
        'touchend',
        function () {
            scheduleNormalize(
                120
            );

            resume(3000);
        },
        {
            passive: true,
        }
    );

    viewport.addEventListener(
        'touchcancel',
        function () {
            scheduleNormalize(
                120
            );

            resume(3000);
        },
        {
            passive: true,
        }
    );

    document.addEventListener(
        'visibilitychange',
        function () {
            if (document.hidden) {
                stopAutoplay();

                return;
            }

            if (! paused) {
                startAutoplay();
            }
        }
    );

    window.addEventListener(
        'resize',
        debounce(
            refresh,
            180
        )
    );

    function handleMotionChange() {
        if (reduceMotion.matches) {
            stopAutoplay();

            return;
        }

        if (! paused) {
            startAutoplay();
        }
    }

    if (
        typeof reduceMotion
            .addEventListener
        === 'function'
    ) {
        reduceMotion.addEventListener(
            'change',
            handleMotionChange
        );
    } else if (
        typeof reduceMotion
            .addListener
        === 'function'
    ) {
        reduceMotion.addListener(
            handleMotionChange
        );
    }

    refresh();
}

function debounce(
    callback,
    delay
) {
    let timer = null;

    return function (...args) {
        window.clearTimeout(
            timer
        );

        timer =
            window.setTimeout(
                function () {
                    callback.apply(
                        this,
                        args
                    );
                },
                delay
            );
    };
}
