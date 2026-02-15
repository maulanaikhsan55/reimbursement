@props(['title', 'description' => null, 'icon' => null])

<div class="report-empty-state">
    <div class="report-empty-state-icon">
        @if($icon)
            {!! $icon !!}
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 4rem; height: 4rem; opacity: 0.2; color: #425d87;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        @endif
    </div>
    <div class="report-empty-state-title">{{ $title }}</div>
    @if($description)
        <p class="report-empty-state-text">{{ $description }}</p>
    @endif
</div>