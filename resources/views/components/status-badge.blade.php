@props([
    'type' => 'status',
    'value',
])

@php
    $statusMap = [
        'ouvert' => ['bg' => 'rgba(59,130,246,.12)', 'color' => '#2563eb', 'label' => 'Ouvert'],
        'en_cours' => ['bg' => 'rgba(217,119,6,.14)', 'color' => '#d97706', 'label' => 'En cours'],
        'resolu' => ['bg' => 'rgba(22,163,74,.14)', 'color' => '#16a34a', 'label' => 'Résolu'],
        'ferme' => ['bg' => 'var(--track)', 'color' => 'var(--muted)', 'label' => 'Fermé'],
    ];

    $priorityMap = [
        'haute' => ['bg' => 'rgba(220,38,38,.12)', 'color' => '#dc2626', 'label' => 'Haute', 'dot' => true],
        'moyenne' => ['bg' => 'rgba(217,119,6,.14)', 'color' => '#d97706', 'label' => 'Moyenne', 'dot' => true],
        'basse' => ['bg' => 'rgba(22,163,74,.14)', 'color' => '#16a34a', 'label' => 'Basse', 'dot' => true],
    ];

    $map = $type === 'priority' ? $priorityMap : $statusMap;
    $lower = strtolower($value);
    $item = $map[$lower] ?? ['bg' => 'var(--track)', 'color' => 'var(--muted)', 'label' => $value];
@endphp

<span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:{{ $item['bg'] }};color:{{ $item['color'] }};">
    @if(($item['dot'] ?? false))
        <span style="width:7px;height:7px;border-radius:50%;background:{{ $item['color'] }};"></span>
    @endif
    {{ $item['label'] }}
</span>
