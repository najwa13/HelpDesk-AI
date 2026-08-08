@props([
    'label',
    'value',
    'icon',
    'trend' => null,
    'trendUp' => true,
    'subtitle' => '',
    'delay' => 0,
])

@php
    $trendBg = $trendUp ? 'rgba(22,163,74,.1)' : 'rgba(220,38,38,.1)';
    $trendColor = $trendUp ? '#16a34a' : '#dc2626';
    $trendIcon = $trendUp
        ? 'M7 17l5-5 5 5M7 7l5 5 5-5'
        : 'M17 7l-5 5-5-5M17 17l-5-5-5 5';
@endphp

<div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px;box-shadow:var(--shadow);animation:fadeUp .5s {{ $delay }}s both;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div style="width:42px;height:42px;border-radius:12px;background:var(--primsoft);color:var(--accent);display:grid;place-items:center;">
            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path d="{{ $icon }}"></path>
            </svg>
        </div>
        @if($trend !== null)
            <span style="display:inline-flex;align-items:center;gap:3px;font-size:12px;font-weight:700;padding:4px 9px;border-radius:20px;background:{{ $trendBg }};color:{{ $trendColor }};">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="{{ $trendIcon }}"></path>
                </svg>
                {{ $trend }}
            </span>
        @endif
    </div>
    <div style="font-size:13px;color:var(--text2);font-weight:600;margin-top:16px;">{{ $label }}</div>
    <div style="font-size:28px;font-weight:800;color:var(--text);letter-spacing:-.6px;margin-top:2px;">{{ $value }}</div>
    @if($subtitle)
        <div style="font-size:11px;color:var(--muted);margin-top:3px;">{{ $subtitle }}</div>
    @endif
</div>
