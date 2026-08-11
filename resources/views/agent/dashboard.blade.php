@extends('layouts.agent')

@section('title', 'Mon tableau de bord')
@section('breadcrumb', 'Espace Agent')
@section('pageTitle', 'Mon tableau de bord')

@php
    $user = auth()->user();

    $statCards = [
        [
            'label' => 'Tickets ouverts',
            'value' => number_format($stats['tickets_ouverts'], 0, ',', ' '),
            'icon' => 'M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14',
            'trend' => '+2',
            'trendUp' => true,
            'subtitle' => 'à traiter aujourd\'hui',
        ],
        [
            'label' => 'Résolus (semaine)',
            'value' => number_format($stats['tickets_resolus'], 0, ',', ' '),
            'icon' => 'M22 11.08V12a10 10 0 11-5.93-9.14M22 4L12 14.01l-3-3',
            'trend' => '+14%',
            'trendUp' => true,
            'subtitle' => 'sur les 7 derniers jours',
        ],
        [
            'label' => 'En cours',
            'value' => number_format($stats['tickets_en_cours'], 0, ',', ' '),
            'icon' => 'M12 3a9 9 0 100 18 9 9 0 000-18zM12 8v4l3 2',
            'trend' => '-12%',
            'trendUp' => true,
            'subtitle' => 'temps moyen de réponse',
        ],
        [
            'label' => 'Assistance IA',
            'value' => number_format($stats['assigned_count'], 0, ',', ' '),
            'icon' => 'M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z',
            'trend' => '+31%',
            'trendUp' => true,
            'subtitle' => 'analyses lancées',
        ],
    ];

    $barData = [
        ['m' => 'Fév', 'v' => 62], ['m' => 'Mar', 'v' => 88], ['m' => 'Avr', 'v' => 54],
        ['m' => 'Mai', 'v' => 71], ['m' => 'Juin', 'v' => 96], ['m' => 'Juil', 'v' => 80],
    ];
    $barMax = 100;

    $catData = [
        ['name' => 'Connexion', 'pct' => 38, 'color' => '#7c3aed'],
        ['name' => 'Bug', 'pct' => 26, 'color' => '#f59e0b'],
        ['name' => 'Facturation', 'pct' => 21, 'color' => '#10b981'],
        ['name' => 'Question', 'pct' => 15, 'color' => '#3b82f6'],
    ];
@endphp

@section('content')
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
                <div><div style="font-weight:800;font-size:16px;color:var(--text);">Nouveaux tickets</div><div style="font-size:12px;color:var(--muted);">6 derniers mois</div></div>
                <div style="display:inline-flex;align-items:center;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:700;background:var(--primsoft);color:var(--accent);cursor:pointer;">6 mois</div>
            </div>
            <div style="display:flex;align-items:flex-end;gap:14px;height:210px;padding-top:10px;">
                @foreach($barData as $i => $b)
                    @php
                        $pct = ($b['v'] / $barMax) * 100;
                        $isMax = $b['v'] === $barMax;
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:9px;height:100%;justify-content:flex-end;">
                        <div style="width:100%;position:relative;display:flex;align-items:flex-end;height:100%;">
                            <div style="width:100%;border-radius:9px 9px 4px 4px;transform-origin:bottom;animation:grow .8s {{ $i * 0.08 }}s cubic-bezier(.34,1.2,.4,1) both;height:{{ $pct }}%;background:{{ $isMax ? 'linear-gradient(180deg,var(--accent),var(--accent2))' : 'var(--primsoft)' }};"></div>
                        </div>
                        <div style="font-size:12px;color:var(--text2);font-weight:600;">{{ $b['m'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <div><div style="font-weight:800;font-size:16px;color:var(--text);">Volume par catégorie</div><div style="font-size:12px;color:var(--muted);">14 derniers jours</div></div>
                <div style="display:flex;gap:14px;">
                    @foreach($catData as $c)
                        <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text2);font-weight:600;"><span style="width:9px;height:9px;border-radius:3px;display:inline-block;background:{{ $c['color'] }};"></span>{{ $c['name'] }}</div>
                    @endforeach
                </div>
            </div>
            <svg viewBox="0 0 480 210" style="width:100%;height:210px;overflow:visible;">
                @for($i = 0; $i < 5; $i++)
                    <line x1="24" x2="474" y1="{{ 20 + $i * 42.5 }}" y2="{{ 20 + $i * 42.5 }}" stroke="var(--border)" stroke-width="1"></line>
                @endfor
                <polyline points="24,110 99,96 174,104 249,76 324,88 399,62 474,50" fill="none" stroke="var(--accent)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray:1400;stroke-dashoffset:1400;animation:draw 1.5s 0s ease forwards;"></polyline>
                <polyline points="24,140 99,132 174,124 249,138 324,120 399,128 474,112" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray:1400;stroke-dashoffset:1400;animation:draw 1.5s 0.2s ease forwards;"></polyline>
                <polyline points="24,158 99,150 174,154 249,142 324,136 399,140 474,128" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="stroke-dasharray:1400;stroke-dashoffset:1400;animation:draw 1.5s 0.4s ease forwards;"></polyline>
            </svg>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div style="font-weight:800;font-size:16px;color:var(--text);">Derniers tickets</div>
                <a href="{{ route('agent.tickets.index') }}" style="font-size:13px;font-weight:700;color:var(--accent);">Tout voir</a>
            </div>
            @if(count($recentTickets) > 0)
                <div style="overflow-x:auto;">
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
                            @foreach($recentTickets as $ticket)
                                <tr style="border-top:1px solid var(--border);cursor:pointer;transition:.12s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'" onclick="window.location='{{ route('agent.tickets.show', $ticket['id']) }}'">
                                    <td style="padding:12px 8px;">
                                        <div style="font-weight:700;color:var(--text);">{{ $ticket['titre'] }}</div>
                                        <div style="font-size:11px;color:var(--muted);">{{ $ticket['client']['name'] ?? 'N/A' }} · {{ $ticket['categorie']['nom'] ?? 'N/A' }}</div>
                                    </td>
                                    <td style="padding:12px 8px;"><x-status-badge type="priority" :value="$ticket['priorite'] ?? 'basse'" /></td>
                                    <td style="padding:12px 8px;"><x-status-badge type="status" :value="$ticket['statut']" /></td>
                                    <td style="padding:12px 8px;text-align:right;"><a href="{{ route('agent.tickets.show', $ticket['id']) }}" style="color:var(--accent);font-weight:700;text-decoration:none;" onclick="event.stopPropagation();">Ouvrir</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:48px;color:var(--muted);font-size:14px;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:16px;opacity:.5;"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14"></path></svg>
                    <div style="font-weight:700;font-size:16px;color:var(--text);margin-bottom:6px;">Aucun ticket assigné</div>
                    <div style="font-size:13px;color:var(--text2);">Vos tickets assignés apparaîtront ici.</div>
                </div>
            @endif
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="font-weight:800;font-size:16px;color:var(--text);margin-bottom:4px;">Catégories fréquentes</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:18px;">Répartition des demandes</div>

            @foreach($catData as $i => $c)
                <div style="margin-bottom:15px;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                        <span style="color:var(--text);font-weight:600;">{{ $c['name'] }}</span>
                        <span style="color:var(--text2);font-weight:700;">{{ $c['pct'] }}%</span>
                    </div>
                    <div style="height:8px;border-radius:20px;background:var(--track);overflow:hidden;">
                        <div style="height:100%;border-radius:20px;transform-origin:left;animation:widen 1s {{ $i * 0.1 }}s cubic-bezier(.34,1.1,.4,1) both;width:{{ $c['pct'] }}%;background:{{ $c['color'] }};"></div>
                    </div>
                </div>
            @endforeach

            <div style="margin-top:22px;padding:16px;border-radius:14px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;display:flex;align-items:center;gap:16px;">
                <div style="position:relative;width:64px;height:64px;flex-shrink:0;">
                    <svg width="64" height="64" viewBox="0 0 64 64" style="transform:rotate(-90deg);">
                        <circle cx="32" cy="32" r="27" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="8"></circle>
                        <circle cx="32" cy="32" r="27" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-dasharray="170" stroke-dashoffset="47" style="animation:draw 1.4s ease forwards;stroke-dashoffset:170;"></circle>
                    </svg>
                    <div style="position:absolute;inset:0;display:grid;place-items:center;font-weight:800;font-size:15px;">72%</div>
                </div>
                <div>
                    <div style="font-weight:800;font-size:15px;">Résolution automatique</div>
                    <div style="font-size:12px;opacity:.9;line-height:1.4;">Tickets résolus par la base de connaissances ce mois-ci.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
