@php
    $experience = app(\App\Services\Frontend\ExperienceSettings::class)->all();

    [$accent, $hover, $soft] = match ($experience['palette']) {
        'teal' => ['#286b70', '#20565a', '#eef5f5'],
        'slate' => ['#486581', '#364e66', '#f0f3f7'],
        default => ['#247052', '#1d5b43', '#eff5f2'],
    };
@endphp

<style>
body.ppid-soft.ppid-consistent {
    --experience-accent: {{ $accent }};
    --experience-hover: {{ $hover }};
    --experience-soft: {{ $soft }};
    --experience-font: {{ $experience['font_size'] }}px;

    --container: {{ $experience['width'] }}px;
    --color-green-600: var(--experience-accent);
    --color-green-700: var(--experience-hover);
    --color-green-100: var(--experience-soft);
    --front-green: var(--experience-accent);

    font-size: var(--experience-font);
}

body.ppid-soft.ppid-consistent .public-prose,
body.ppid-soft.ppid-consistent .reg-text {
    font-size: var(--experience-font);
}

.page-interaction-meta [hidden],
.page-interactions [hidden],
.page-interaction-meta[hidden],
.page-interactions[hidden] {
    display: none !important;
}
</style>