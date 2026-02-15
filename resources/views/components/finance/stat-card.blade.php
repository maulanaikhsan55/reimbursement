@props(['title', 'value', 'type' => 'primary', 'icon'])

<div class="stat-card modern">
    <div class="stat-left">
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $title }}</div>
    </div>
    <div class="stat-icon {{ $type }}-icon">
        {!! $icon !!}
    </div>
</div>
