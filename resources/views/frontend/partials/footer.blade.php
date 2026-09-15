@php
    $institutionName = filled($siteSettings['institution_name'] ?? null)
        ? $siteSettings['institution_name']
        : 'Kementerian Agama Kabupaten Tuban';

    $institutionShortName = filled($siteSettings['institution_short_name'] ?? null)
        ? $siteSettings['institution_short_name']
        : $institutionName;

    $siteName = filled($siteSettings['site_name'] ?? null)
        ? $siteSettings['site_name']
        : 'WEB PPID';

    $siteTagline = filled($siteSettings['site_tagline'] ?? null)
        ? $siteSettings['site_tagline']
        : 'Pelayanan Informasi Publik';

    $logoUrl = isset($siteLogo) && $siteLogo
        ? Storage::disk($siteLogo->disk)->url($siteLogo->path)
        : null;

$socialLinks = array_values(
    array_filter([
        [
            'platform' => 'instagram',
            'label' => 'Instagram',
            'url' => $siteSettings['instagram_url'] ?? null,
        ],
        [
            'platform' => 'youtube',
            'label' => 'YouTube',
            'url' => $siteSettings['youtube_url'] ?? null,
        ],
        [
            'platform' => 'tiktok',
            'label' => 'TikTok',
            'url' => $siteSettings['tiktok_url'] ?? null,
        ],
        [
            'platform' => 'facebook',
            'label' => 'Facebook',
            'url' => $siteSettings['facebook_url'] ?? null,
        ],
        [
            'platform' => 'twitter',
            'label' => 'X / Twitter',
            'url' => $siteSettings['twitter_url'] ?? null,
        ],
        [
            'platform' => 'threads',
            'label' => 'Threads',
            'url' => $siteSettings['threads_url'] ?? null,
        ],
        [
            'platform' => 'linkedin',
            'label' => 'LinkedIn',
            'url' => $siteSettings['linkedin_url'] ?? null,
        ],
        [
            'platform' => 'whatsapp',
            'label' => 'WhatsApp Channel',
            'url' => $siteSettings['whatsapp_channel_url'] ?? null,
        ],
        [
            'platform' => 'telegram',
            'label' => 'Telegram',
            'url' => $siteSettings['telegram_url'] ?? null,
        ],
    ], fn (array $social): bool => filled($social['url']))
);
@endphp

<footer class="site-footer">
    <div class="container">
        <div class="site-footer-grid">

            {{-- =====================================================
                 IDENTITAS
                 ===================================================== --}}
            <div class="site-footer-identity">
                <a
                    href="{{ route('home') }}"
                    class="footer-brand"
                    aria-label="{{ $siteName }}"
                >
                    <div class="footer-logo">
                        @if ($logoUrl)
                            <img
                                src="{{ $logoUrl }}"
                                alt="{{ $siteName }}"
                            >
                        @else
                            <span>
                                W
                            </span>
                        @endif
                    </div>

                    <div class="footer-brand-copy">
                        <strong>
                            {{ $siteName }}
                        </strong>

                        <span>
                            {{ $institutionShortName }}
                        </span>
                    </div>
                </a>

                <p class="footer-description">
                    {{ $siteTagline }} untuk
                    {{ $institutionName }}.
                </p>

                @if (! empty($socialLinks))
                    <div class="footer-social">
                        <span class="footer-social-label">
                            Ikuti Kami
                        </span>

<div class="footer-social-list">
    @foreach ($socialLinks as $social)
        <a
            href="{{ $social['url'] }}"
            target="_blank"
            rel="noopener noreferrer"
            class="footer-social-link"
            aria-label="{{ $social['label'] }}"
            title="{{ $social['label'] }}"
        >
            @include(
                'frontend.partials.social.icon',
                [
                    'platform' => $social['platform'],
                ]
            )
        </a>
    @endforeach
</div>
                    </div>
                @endif
            </div>

            {{-- =====================================================
                 NAVIGASI FOOTER
                 ===================================================== --}}
            @foreach ($footerMenus as $footerMenu)
                <div class="site-footer-column">
                    <h3>
                        {{ $footerMenu['label'] }}
                    </h3>

                    @if ($footerMenu['children']->isNotEmpty())
                        <ul class="footer-links">
                            @foreach ($footerMenu['children'] as $child)
                                @if ($child['url'])
                                    <li>
                                        <a
                                            href="{{ $child['url'] }}"
                                            @if ($child['target'])
                                                target="{{ $child['target'] }}"
                                            @endif
                                            @if ($child['rel'])
                                                rel="{{ $child['rel'] }}"
                                            @endif
                                        >
                                            {{ $child['label'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach

            {{-- =====================================================
                 KONTAK
                 ===================================================== --}}
            <div class="site-footer-column">
                <h3>
                    Kontak
                </h3>

                <div class="footer-contact">
                    @if (filled($siteSettings['address'] ?? null))
                        <div class="footer-contact-item">
                            <span>
                                Alamat
                            </span>

                            <p>
                                {!! nl2br(e($siteSettings['address'])) !!}
                            </p>
                        </div>
                    @endif

                    @if (filled($siteSettings['phone'] ?? null))
                        <div class="footer-contact-item">
                            <span>
                                Telepon
                            </span>

                            <a href="tel:{{ $siteSettings['phone'] }}">
                                {{ $siteSettings['phone'] }}
                            </a>
                        </div>
                    @endif

                    @if (filled($siteSettings['whatsapp'] ?? null))
                        <div class="footer-contact-item">
                            <span>
                                WhatsApp
                            </span>

                            <p>
                                {{ $siteSettings['whatsapp'] }}
                            </p>
                        </div>
                    @endif

                    @if (filled($siteSettings['email'] ?? null))
                        <div class="footer-contact-item">
                            <span>
                                Email
                            </span>

                            <a href="mailto:{{ $siteSettings['email'] }}">
                                {{ $siteSettings['email'] }}
                            </a>
                        </div>
                    @endif

                    @if (filled($siteSettings['service_hours'] ?? null))
                        <div class="footer-contact-item">
                            <span>
                                Jam Pelayanan
                            </span>

                            <p>
                                {!! nl2br(e($siteSettings['service_hours'])) !!}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div class="site-footer-bottom">
            <span>
                © {{ date('Y') }} {{ $institutionName }}
            </span>

            <span>
                {{ $siteName }} · {{ $siteTagline }}
            </span>
        </div>
    </div>
</footer>
