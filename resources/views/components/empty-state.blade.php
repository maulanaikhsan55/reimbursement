@props(['title', 'description' => null, 'link' => null, 'buttonText' => 'Tambah Baru', 'icon' => null])

<div class="empty-state">
    <div class="empty-state-icon">
        @if($icon)
            {!! $icon !!}
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        @endif
    </div>
    <div class="empty-state-title">{{ $title }}</div>
    @if($description)
        <p>{{ $description }}</p>
    @endif
    @if($link)
        <div style="margin-top: 20px;">
            <a href="{{ $link }}" class="btn-modern btn-modern-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                {{ $buttonText }}
            </a>
        </div>
    @endif
</div>
