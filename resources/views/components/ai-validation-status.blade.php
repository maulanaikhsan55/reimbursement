@props(['status', 'showLabel' => true])

@php
    $statusValue = $status instanceof \UnitEnum ? $status->value : $status;
    $normalizedStatus = str_replace('_', '-', $statusValue);
    
    $config = [
        'valid' => ['class' => 'status-approved', 'label' => 'Lolos'],
        'invalid' => ['class' => 'status-draft', 'label' => 'Review'],
        'pending' => ['class' => 'status-pending', 'label' => 'Proses'],
    ];
    
    $item = $config[$statusValue] ?? null;
@endphp

@if($item)
    <span class="status-badge {{ $item['class'] }}">
        {{ $item['label'] }}
    </span>
@else
    <span class="text-secondary" style="opacity: 0.5;">-</span>
@endif