@props([
    'href' => null,
    'title' => '',
    'variant' => 'view',
    'type' => 'button',
    'onclick' => null,
    'class' => '',
    'style' => null,
])

@php
    $variantClass = match ($variant) {
        'duplicate' => 'btn-action-icon-duplicate',
        'delete' => 'btn-action-delete',
        default => '',
    };

    $resolvedClass = trim('btn-action-icon '.$variantClass.' '.$class);
    $slotContent = trim((string) $slot);
    $icon = $slotContent !== '' ? $slotContent : match ($variant) {
        'approve' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>',
        'duplicate' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>',
        'delete' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>',
        default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
    };
@endphp

@if($href)
    <a href="{{ $href }}"
       title="{{ $title }}"
       @if($style) style="{{ $style }}" @endif
       {{ $attributes->merge(['class' => $resolvedClass]) }}>
        {!! $icon !!}
    </a>
@else
    <button type="{{ $type }}"
            title="{{ $title }}"
            @if($onclick) onclick="{{ $onclick }}" @endif
            @if($style) style="{{ $style }}" @endif
            {{ $attributes->merge(['class' => $resolvedClass]) }}>
        {!! $icon !!}
    </button>
@endif
