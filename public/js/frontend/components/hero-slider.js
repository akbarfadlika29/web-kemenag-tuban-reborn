document.addEventListener(
    'DOMContentLoaded',
    function () {
        document
            .querySelectorAll(
                '[data-hero-slider]'
            )
            .forEach(
                initHeroSlider
            );
    }
);

function initHeroSlider(root) {
    const track =
        root.querySelector(
            '[data-hero-slider-track]'
        );

    const slides =
        Array.from(
            root.querySelectorAll(
                '[data-hero-slide]'
            )
        );

    const previous =
        root.querySelector(
            '[data-hero-slider-prev]'
        );

    const next =
        root.querySelector(
            '[data-hero-slider-next]'
        );

    const dots =
        Array.from(
            root.querySelectorAll(
                '[data-hero-slider-dot]'
            )
        );

    if (
        ! track
        || slides.length <= 1
    ) {
        return;
    }

    const reduceMotion =
        window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        );

    const mobilePrimaryOnly =
        window.matchMedia(
            '(max-width: 768px)'
        );

    const autoplayDelay = 6000;

    let currentIndex = 0;
    let physicalIndex = 0;

    let loopReady = false;
    let isAnimating = false;

    let autoplayTimer = null;
    let resumeTimer = null;
    let animationTimer = null;

    let paused = false;

    let touchStartX = null;
    let suppressClick = false;

    function normalizeIndex(index) {
        if (index < 0) {
            return slides.length - 1;
        }

        if (index >= slides.length) {
            return 0;
        }

        return index;
    }

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

    function createClone(slide) {
        const clone =
            slide.cloneNode(true);

        clone.removeAttribute(
            'data-hero-slide'
        );

        clone.setAttribute(
            'data-hero-clone',
            'true'
        );

        clone.classList.remove(
            'is-active'
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

    function trackTransform(index) {
        return (
            'translate3d('
            + (-index * 100)
            + '%, 0, 0)'
        );
    }

    function jumpToPhysical(index) {
        track.style.transition =
            'none';

        track.style.transform =
            trackTransform(index);

        void track.offsetWidth;

        track.style.transition =
            '';
    }

    function animateToPhysical(index) {
        track.style.transition = '';

        track.style.transform =
            trackTransform(index);
    }

    function updateState(index) {
        slides.forEach(
            function (
                slide,
                slideIndex
            ) {
                const active =
                    slideIndex === index;

                slide.classList.toggle(
                    'is-active',
                    active
                );

                slide.setAttribute(
                    'aria-hidden',
                    active
                        ? 'false'
                        : 'true'
                );

                if (active) {
                    slide.removeAttribute(
                        'inert'
                    );
                } else {
                    slide.setAttribute(
                        'inert',
                        ''
                    );
                }
            }
        );

        dots.forEach(
            function (
                dot,
                dotIndex
            ) {
                const active =
                    dotIndex === index;

                dot.classList.toggle(
                    'is-active',
                    active
                );

                if (active) {
                    dot.setAttribute(
                        'aria-current',
                        'true'
                    );
                } else {
                    dot.removeAttribute(
                        'aria-current'
                    );
                }
            }
        );
    }

    function clearAnimationTimer() {
        if (! animationTimer) {
            return;
        }

        window.clearTimeout(
            animationTimer
        );

        animationTimer = null;
    }

    function finishMovement() {
        clearAnimationTimer();

        if (! loopReady) {
            isAnimating = false;

            return;
        }

        /*
         * Setelah bergerak ke clone pertama
         * di sebelah kanan, lompat diam-diam
         * ke slide pertama asli.
         */
        if (
            physicalIndex
            === slides.length + 1
        ) {
            physicalIndex = 1;

            jumpToPhysical(
                physicalIndex
            );
        }

        /*
         * Setelah bergerak ke clone terakhir
         * di sebelah kiri, lompat diam-diam
         * ke slide terakhir asli.
         */
        if (physicalIndex === 0) {
            physicalIndex =
                slides.length;

            jumpToPhysical(
                physicalIndex
            );
        }

        isAnimating = false;
    }

    function startMovement() {
        isAnimating = true;

        clearAnimationTimer();

        animationTimer =
            window.setTimeout(
                finishMovement,
                900
            );

        animateToPhysical(
            physicalIndex
        );
    }

    function buildLoop() {
        if (
            loopReady
            || mobilePrimaryOnly.matches
        ) {
            return;
        }

        const firstClone =
            createClone(
                slides[0]
            );

        const lastClone =
            createClone(
                slides[
                    slides.length - 1
                ]
            );

        track.prepend(
            lastClone
        );

        track.append(
            firstClone
        );

        loopReady = true;

        physicalIndex =
            currentIndex + 1;

        jumpToPhysical(
            physicalIndex
        );
    }

    function destroyLoop() {
        clearAnimationTimer();

        track
            .querySelectorAll(
                '[data-hero-clone]'
            )
            .forEach(
                function (clone) {
                    clone.remove();
                }
            );

        loopReady = false;
        isAnimating = false;

        physicalIndex = 0;

        track.style.transition = '';
        track.style.transform = '';
    }

    function showSlide(
        index,
        animate = true
    ) {
        if (mobilePrimaryOnly.matches) {
            currentIndex = 0;

            updateState(0);

            return;
        }

        buildLoop();

        if (isAnimating) {
            return;
        }

        currentIndex =
            normalizeIndex(index);

        physicalIndex =
            currentIndex + 1;

        updateState(
            currentIndex
        );

        if (
            ! animate
            || reduceMotion.matches
        ) {
            jumpToPhysical(
                physicalIndex
            );

            return;
        }

        startMovement();
    }

    function goNext() {
        if (
            mobilePrimaryOnly.matches
            || isAnimating
        ) {
            return;
        }

        buildLoop();

        if (reduceMotion.matches) {
            showSlide(
                currentIndex + 1,
                false
            );

            return;
        }

        if (
            currentIndex
            === slides.length - 1
        ) {
            currentIndex = 0;

            physicalIndex =
                slides.length + 1;
        } else {
            currentIndex += 1;

            physicalIndex += 1;
        }

        updateState(
            currentIndex
        );

        startMovement();
    }

    function goPrevious() {
        if (
            mobilePrimaryOnly.matches
            || isAnimating
        ) {
            return;
        }

        buildLoop();

        if (reduceMotion.matches) {
            showSlide(
                currentIndex - 1,
                false
            );

            return;
        }

        if (currentIndex === 0) {
            currentIndex =
                slides.length - 1;

            physicalIndex = 0;
        } else {
            currentIndex -= 1;

            physicalIndex -= 1;
        }

        updateState(
            currentIndex
        );

        startMovement();
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
            || document.hidden
            || reduceMotion.matches
            || mobilePrimaryOnly.matches
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

    track.addEventListener(
        'transitionend',
        function (event) {
            if (
                event.target !== track
                || event.propertyName
                    !== 'transform'
            ) {
                return;
            }

            finishMovement();
        }
    );

    previous?.addEventListener(
        'click',
        function () {
            pause();

            goPrevious();

            resume(3000);
        }
    );

    next?.addEventListener(
        'click',
        function () {
            pause();

            goNext();

            resume(3000);
        }
    );

    dots.forEach(
        function (dot) {
            dot.addEventListener(
                'click',
                function () {
                    const index =
                        Number(
                            dot.dataset
                                .heroSliderDot
                        );

                    if (
                        ! Number.isInteger(
                            index
                        )
                    ) {
                        return;
                    }

                    pause();

                    showSlide(index);

                    resume(3000);
                }
            );
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

    root.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'ArrowLeft'
            ) {
                event.preventDefault();

                pause();
                goPrevious();
                resume(3000);
            }

            if (
                event.key === 'ArrowRight'
            ) {
                event.preventDefault();

                pause();
                goNext();
                resume(3000);
            }
        }
    );

    root.addEventListener(
        'touchstart',
        function (event) {
            if (
                mobilePrimaryOnly.matches
                || event.touches.length !== 1
            ) {
                return;
            }

            pause();

            touchStartX =
                event.touches[0]
                    .clientX;
        },
        {
            passive: true,
        }
    );

    root.addEventListener(
        'touchend',
        function (event) {
            if (
                mobilePrimaryOnly.matches
            ) {
                return;
            }

            if (
                touchStartX === null
                || ! event.changedTouches.length
            ) {
                resume(3000);

                return;
            }

            const touchEndX =
                event.changedTouches[0]
                    .clientX;

            const distance =
                touchEndX - touchStartX;

            touchStartX = null;

            if (
                Math.abs(distance) >= 50
            ) {
                suppressClick = true;

                if (distance > 0) {
                    goPrevious();
                } else {
                    goNext();
                }

                window.setTimeout(
                    function () {
                        suppressClick =
                            false;
                    },
                    350
                );
            }

            resume(3000);
        },
        {
            passive: true,
        }
    );

    root.addEventListener(
        'touchcancel',
        function () {
            touchStartX = null;

            if (
                ! mobilePrimaryOnly.matches
            ) {
                resume(3000);
            }
        },
        {
            passive: true,
        }
    );

    root.addEventListener(
        'click',
        function (event) {
            if (! suppressClick) {
                return;
            }

            const link =
                event.target.closest('a');

            if (link) {
                event.preventDefault();
            }
        },
        true
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

    function handleViewportChange() {
        clearAnimationTimer();

        if (mobilePrimaryOnly.matches) {
            stopAutoplay();

            destroyLoop();

            currentIndex = 0;

            updateState(0);

            return;
        }

        currentIndex = 0;

        buildLoop();

        physicalIndex = 1;

        jumpToPhysical(1);

        updateState(0);

        if (! paused) {
            startAutoplay();
        }
    }

    if (
        typeof mobilePrimaryOnly
            .addEventListener
        === 'function'
    ) {
        mobilePrimaryOnly.addEventListener(
            'change',
            handleViewportChange
        );
    } else if (
        typeof mobilePrimaryOnly
            .addListener
        === 'function'
    ) {
        mobilePrimaryOnly.addListener(
            handleViewportChange
        );
    }

    if (mobilePrimaryOnly.matches) {
        currentIndex = 0;

        updateState(0);
    } else {
        buildLoop();

        updateState(0);

        jumpToPhysical(1);

        startAutoplay();
    }
}
