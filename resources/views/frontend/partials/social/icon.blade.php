@switch($platform)
    @case('instagram')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="5"></rect>
            <circle cx="12" cy="12" r="4"></circle>
            <circle cx="17.5" cy="6.5" r="1"></circle>
        </svg>
        @break

    @case('youtube')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M21 8.2a3 3 0 0 0-2.1-2.1C17 5.5 12 5.5 12 5.5s-5 0-6.9.6A3 3 0 0 0 3 8.2 31 31 0 0 0 2.5 12 31 31 0 0 0 3 15.8a3 3 0 0 0 2.1 2.1c1.9.6 6.9.6 6.9.6s5 0 6.9-.6a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-3.8 31 31 0 0 0-.5-3.8Z"></path>
            <path d="m10 15 5-3-5-3Z"></path>
        </svg>
        @break

    @case('tiktok')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M15 4v10.2a4.2 4.2 0 1 1-3.4-4.1"></path>
            <path d="M15 4c.8 2.2 2.2 3.5 4.5 4"></path>
        </svg>
        @break

    @case('facebook')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v6h4v-6h3l1-4h-4V9c0-.7.3-1 1-1Z"></path>
        </svg>
        @break

    @case('twitter')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5 4l14 16"></path>
            <path d="M19 4 5 20"></path>
        </svg>
        @break

    @case('threads')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M8.5 9.5c.7-1.3 2-2 3.7-2 2.8 0 4.8 1.8 4.8 4.6 0 3.1-1.8 5.1-4.5 5.1-2.1 0-3.6-1.2-3.6-3 0-1.7 1.4-2.8 3.5-2.8 1.7 0 3 .5 4 1.5"></path>
        </svg>
        @break

    @case('linkedin')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <rect x="4" y="9" width="4" height="11"></rect>
            <circle cx="6" cy="5.5" r="2"></circle>
            <path d="M12 20V9h4v1.8c.8-1.2 2-2.1 3.8-2.1 2.8 0 4.2 1.8 4.2 5V20h-4v-5.5c0-1.6-.6-2.5-1.9-2.5-1.4 0-2.1 1-2.1 2.8V20Z"></path>
        </svg>
        @break

    @case('whatsapp')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M20 11.5A8 8 0 0 1 8.2 18.6L4 20l1.4-4A8 8 0 1 1 20 11.5Z"></path>
            <path d="M9 8.5c.7 2 2.5 3.8 4.5 4.5"></path>
            <path d="M8.7 7.5 7.8 8.4"></path>
            <path d="m14.5 13.2.9-.9"></path>
        </svg>
        @break

    @case('telegram')
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="m21 4-4 16-5-5-3 2 1-4 8-6-10 5-5-2Z"></path>
        </svg>
        @break
@endswitch
