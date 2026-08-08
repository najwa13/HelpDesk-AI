@extends('layouts.admin')

@section('title', 'Vue globale du support')
@section('breadcrumb', 'Administration')
@section('pageTitle', 'Vue globale du support')

@php
    $user = auth()->user();

    $statCards = [
        [
            'label' => 'Tickets ce mois',
            'value' => number_format($stats['tickets_this_month']['value'], 0, ',', ' '),
            'icon' => 'M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14',
            'trend' => $stats['tickets_this_month']['trend_percentage'] !== null
                ? ($stats['tickets_this_month']['trend_percentage'] >= 0 ? '+' : '') . $stats['tickets_this_month']['trend_percentage'] . '%'
                : 'Nouveau',
            'trendUp' => $stats['tickets_this_month']['trend_percentage'] >= 0,
            'subtitle' => 'vs mois dernier',
        ],
        [
            'label' => 'Résolution auto',
            'value' => $stats['kb_resolution_rate']['value'] . '%',
            'icon' => 'M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z',
            'trend' => null,
            'trendUp' => true,
            'subtitle' => 'par la base de connaissances',
        ],
        [
            'label' => 'Tickets résolus',
            'value' => number_format($stats['resolved_tickets']['value'], 0, ',', ' '),
            'icon' => 'M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3',
            'trend' => null,
            'trendUp' => true,
            'subtitle' => 'total résolus',
        ],
        [
            'label' => 'Analyses IA',
            'value' => number_format($stats['ai_analyses']['value'], 0, ',', ' '),
            'icon' => 'M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z',
            'trend' => null,
            'trendUp' => true,
            'subtitle' => 'analyses effectuées',
        ],
    ];

    $barMax = max(array_column($ticketsByMonth, 'total'));
    $barMax = max($barMax, 1);

    $categories = collect($ticketsByCategoryOverTime)->groupBy('category');
    $categoryColors = ['#7c3aed', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6'];
    $allMonths = collect($ticketsByMonth)->pluck('month')->toArray();

    $lineSeries = $categories->map(function ($items, $name) use ($allMonths, $categoryColors, $categories) {
        $colorIndex = $categories->keys()->indexOf($name);
        $color = $categoryColors[$colorIndex % count($categoryColors)];
        $monthTotals = collect($allMonths)->map(function ($month) use ($items) {
            $found = $items->firstWhere('month', $month);
            return $found ? $found['total'] : 0;
        })->toArray();
        return ['name' => $name, 'color' => $color, 'data' => $monthTotals];
    })->values()->toArray();

    $maxLineVal = 1;
    foreach ($lineSeries as $series) {
        foreach ($series['data'] as $v) {
            if ($v > $maxLineVal) $maxLineVal = $v;
        }
    }

    $svgW = 480;
    $svgH = 210;
    $padL = 24;
    $padR = 6;
    $padT = 10;
    $padB = 20;
    $chartW = $svgW - $padL - $padR;
    $chartH = $svgH - $padT - $padB;

    function buildPolyline(array $data, int $maxVal, int $svgW, int $svgH, int $padL, int $padT, int $chartW, int $chartH): string {
        $count = count($data);
        if ($count < 2) return '';
        $step = $chartW / ($count - 1);
        $points = [];
        foreach ($data as $i => $val) {
            $x = $padL + ($i * $step);
            $y = $padT + $chartH - ($maxVal > 0 ? ($val / $maxVal) * $chartH : 0);
            $points[] = sprintf('%.1f,%.1f', $x, $y);
        }
        return implode(' ', $points);
    }
@endsection

<div style="animation:fadeUp .45s both;">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px;">
        @foreach($statCards as $i => $card)
            <x-stat-card
                :label="$card['label']"
                :value="$card['value']"
                :icon="$card['icon']"
                :trend="$card['trend']"
                :trendUp="$card['trendUp']"
                :subtitle="$card['subtitle']"
                :delay="$i * 0.07"
            />
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1.25fr;gap:16px;margin-bottom:16px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div>
                    <div style="font-weight:800;font-size:16px;color:var(--text);">Nouveaux tickets</div>
                    <div style="font-size:12px;color:var(--muted);">6 derniers mois</div>
                </div>
                <div style="display:inline-flex;align-items:center;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;background:var(--primsoft);color:var(--accent);">6 mois</div>
            </div>
            <div style="display:flex;align-items:flex-end;gap:14px;height:210px;padding-top:10px;">
                @foreach($ticketsByMonth as $i => $bar)
                    @php
                        $pct = $barMax > 0 ? ($bar['total'] / $barMax) * 100 : 0;
                        $isMax = $bar['total'] === $barMax;
                        $barBg = $isMax
                            ? 'background:linear-gradient(180deg,var(--accent),var(--accent2));'
                            : 'background:var(--primsoft);';
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:9px;height:100%;justify-content:flex-end;">
                        <div style="width:100%;position:relative;display:flex;align-items:flex-end;height:100%;">
                            <div style="width:100%;border-radius:9px 9px 4px 4px;transform-origin:bottom;animation:grow .8s {{ $i * 0.08 }}s cubic-bezier(.34,1.2,.4,1) both;height:{{ $pct }}%;{{ $barBg }}"></div>
                        </div>
                        <div style="font-size:12px;color:var(--text2);font-weight:600;">{{ $bar['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <div>
                    <div style="font-weight:800;font-size:16px;color:var(--text);">Volume par catégorie</div>
                    <div style="font-size:12px;color:var(--muted);">6 derniers mois</div>
                </div>
                <div style="display:flex;gap:14px;">
                    @foreach($lineSeries as $s)
                        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text2);font-weight:600;">
                            <span style="width:9px;height:9px;border-radius:3px;display:inline-block;background:{{ $s['color'] }};"></span>
                            {{ $s['name'] }}
                        </div>
                    @endforeach
                </div>
            </div>
            <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" style="width:100%;height:210px;overflow:visible;">
                @for($i = 0; $i < 5; $i++)
                    @php $y = $padT + $i * ($chartH / 4); @endphp
                    <line x1="{{ $padL }}" x2="{{ $svgW - $padR }}" y1="{{ $y }}" y2="{{ $y }}" stroke="var(--border)" stroke-width="1"></line>
                @endfor
                @foreach($lineSeries as $si => $s)
                    <polyline
                        points="{{ buildPolyline($s['data'], $maxLineVal, $svgW, $svgH, $padL, $padT, $chartW, $chartH) }}"
                        fill="none"
                        stroke="{{ $s['color'] }}"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        style="stroke-dasharray:1400;stroke-dashoffset:1400;animation:draw 1.5s {{ $si * 0.2 }}s ease forwards;"
                    ></polyline>
                @endforeach
            </svg>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div style="font-weight:800;font-size:16px;color:var(--text);">Derniers tickets</div>
                <a href="#" style="font-size:13px;font-weight:700;color:var(--accent);text-decoration:none;">Tout voir</a>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="text-align:left;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                        <th style="padding:11px 8px;font-weight:700;">Sujet</th>
                        <th style="padding:11px 8px;font-weight:700;">Priorité</th>
                        <th style="padding:11px 8px;font-weight:700;">Statut</th>
                        <th style="padding:11px 8px;font-weight:700;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTickets as $ticket)
                        <tr style="border-top:1px solid var(--border);cursor:pointer;transition:.12s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 8px;">
                                <div style="font-weight:700;color:var(--text);">{{ $ticket['titre'] }}</div>
                                <div style="font-size:11px;color:var(--muted);">{{ $ticket['client']['name'] ?? 'N/A' }} · {{ $ticket['categorie']['nom'] ?? 'N/A' }}</div>
                            </td>
                            <td style="padding:12px 8px;">
                                <x-status-badge type="priority" :value="$ticket['priorite'] ?? 'basse'" />
                            </td>
                            <td style="padding:12px 8px;">
                                <x-status-badge type="status" :value="$ticket['statut']" />
                            </td>
                            <td style="padding:12px 8px;text-align:right;">
                                <span style="color:var(--accent);font-weight:700;">Ouvrir</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:24px 8px;text-align:center;color:var(--muted);font-size:13px;">Aucun ticket pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="font-weight:800;font-size:16px;color:var(--text);margin-bottom:4px;">Catégories fréquentes</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:18px;">Répartition des demandes</div>
            @forelse($categoryDistribution as $i => $cat)
                @php
                    $catColor = $categoryColors[$i % count($categoryColors)];
                @endphp
                <div style="margin-bottom:15px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                        <span style="color:var(--text);font-weight:600;">{{ $cat['category'] }}</span>
                        <span style="color:var(--text2);font-weight:700;">{{ $cat['percentage'] }}%</span>
                    </div>
                    <div style="height:8px;border-radius:20px;background:var(--track);overflow:hidden;">
                        <div style="height:100%;border-radius:20px;transform-origin:left;animation:widen 1s {{ $i * 0.1 }}s cubic-bezier(.34,1.1,.4,1) both;width:{{ $cat['percentage'] }}%;background:{{ $catColor }};"></div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">Aucune catégorie.</div>
            @endforelse

            @php
                $kbRate = $stats['kb_resolution_rate']['value'];
                $donutCircumference = 2 * 3.14159 * 27;
                $donutOffset = $donutCircumference * (1 - $kbRate / 100);
            @endphp
            <div style="margin-top:22px;padding:16px;border-radius:14px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;display:flex;align-items:center;gap:16px;">
                <div style="position:relative;width:64px;height:64px;flex-shrink:0;">
                    <svg width="64" height="64" viewBox="0 0 64 64" style="transform:rotate(-90deg);">
                        <circle cx="32" cy="32" r="27" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="8"></circle>
                        <circle cx="32" cy="32" r="27" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-dasharray="{{ $donutCircumference }}" stroke-dashoffset="{{ $donutCircumference }}" style="animation:draw 1.4s ease forwards;stroke-dashoffset:{{ $donutOffset }};"></circle>
                    </svg>
                    <div style="position:absolute;inset:0;display:grid;place-items:center;font-weight:800;font-size:15px;">{{ $kbRate }}%</div>
                </div>
                <div>
                    <div style="font-weight:800;font-size:15px;">Résolution automatique</div>
                    <div style="font-size:12px;opacity:.9;line-height:1.4;">Demandes résolues par la base de connaissances.</div>
                </div>
            </div>
        </div>
    </div>
</div>
