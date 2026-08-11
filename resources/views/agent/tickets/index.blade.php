@extends('layouts.agent')

@section('title', 'Mes tickets')
@section('breadcrumb', 'Espace Agent')
@section('pageTitle', 'Mes tickets')

@section('content')
<div style="animation:fadeUp .45s both;">
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;">
        @foreach($filterMap as $label => $val)
            @php
                $isActive = $activeFilter === $label;
                $style = $isActive
                    ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border-color:transparent;'
                    : 'background:var(--surface);color:var(--text2);border-color:var(--border);';
                $countBg = $isActive ? 'background:rgba(255,255,255,.25);color:#fff;' : 'background:var(--primsoft);color:var(--accent);';
            @endphp
            <a href="{{ $val ? route('agent.tickets.index', array_merge(request()->query(), ['statut' => $val])) : route('agent.tickets.index') }}"
               style="display:flex;align-items:center;gap:8px;padding:9px 15px;border:1px solid;border-radius:11px;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;transition:.15s;text-decoration:none;{{ $style }}">
                {{ $label }}
                <span style="font-size:11px;padding:1px 7px;border-radius:20px;{{ $countBg }}">{{ $counts[$label] }}</span>
            </a>
        @endforeach
    </div>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);overflow:hidden;">
        @if(count($tickets) > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="text-align:left;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                            <th style="padding:12px 10px;font-weight:700;">Ticket</th>
                            <th style="padding:12px 10px;font-weight:700;">Client</th>
                            <th style="padding:12px 10px;font-weight:700;">Catégorie</th>
                            <th style="padding:12px 10px;font-weight:700;">Priorité</th>
                            <th style="padding:12px 10px;font-weight:700;">Statut</th>
                            <th style="padding:12px 10px;font-weight:700;">Date</th>
                            <th style="padding:12px 10px;font-weight:700;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            <tr style="border-top:1px solid var(--border);cursor:pointer;transition:.12s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'" onclick="window.location='{{ route('agent.tickets.show', $ticket['id']) }}'">
                                <td style="padding:14px 10px;">
                                    <div style="font-weight:700;color:var(--text);">{{ $ticket['titre'] }}</div>
                                    <div style="font-size:11px;color:var(--muted);max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $ticket['description'] }}</div>
                                </td>
                                <td style="padding:14px 10px;color:var(--text2);font-weight:600;">{{ $ticket['client']['name'] ?? 'N/A' }}</td>
                                <td style="padding:14px 10px;"><span style="display:inline-block;white-space:nowrap;padding:4px 10px;border-radius:100px;font-size:11px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $ticket['categorie']['nom'] ?? 'N/A' }}</span></td>
                                <td style="padding:14px 10px;"><x-status-badge type="priority" :value="$ticket['priorite'] ?? 'basse'" /></td>
                                <td style="padding:14px 10px;"><x-status-badge type="status" :value="$ticket['statut']" /></td>
                                <td style="padding:14px 10px;color:var(--text2);font-size:12px;white-space:nowrap;">{{ $ticket['created_at'] ? \Carbon\Carbon::parse($ticket['created_at'])->format('d/m/Y') : 'N/A' }}</td>
                                <td style="padding:14px 10px;text-align:right;color:var(--accent);font-weight:700;">Ouvrir →</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding:16px;border-top:1px solid var(--border);display:flex;justify-content:center;">
                {{ $pagination->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div style="text-align:center;padding:64px 32px;color:var(--muted);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:16px;opacity:.5;"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14"></path></svg>
                <div style="font-weight:700;font-size:16px;color:var(--text);margin-bottom:6px;">Aucun ticket trouvé</div>
                <div style="font-size:13px;color:var(--text2);">Aucun ticket ne correspond à vos critères.</div>
            </div>
        @endif
    </div>
</div>
@endsection