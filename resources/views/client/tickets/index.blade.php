@extends('layouts.client')

@section('title', 'Mes tickets')
@section('breadcrumb', 'Mon espace')
@section('pageTitle', 'Mes tickets')

@section('content')
<div style="animation:fadeUp .45s both;">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px;">
        @foreach($stats as $stat)
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);display:flex;align-items:center;gap:14px;">
                <div style="width:44px;height:44px;border-radius:12px;background:var(--primsoft);color:var(--accent);display:grid;place-items:center;">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14"></path></svg>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:800;color:var(--text);letter-spacing:-.5px;">{{ $stat['value'] }}</div>
                    <div style="font-size:12px;color:var(--text2);font-weight:600;">{{ $stat['label'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            @foreach($filterMap as $label => $val)
                @php
                    $isActive = $activeFilter === $label;
                    $style = $isActive
                        ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border-color:transparent;'
                        : 'background:var(--surface);color:var(--text2);border-color:var(--border);';
                    $countBg = $isActive ? 'background:rgba(255,255,255,.25);color:#fff;' : 'background:var(--primsoft);color:var(--accent);';
                @endphp
                <a href="{{ $val ? route('client.tickets.index', array_merge(request()->query(), ['statut' => $val])) : route('client.tickets.index') }}"
                   style="display:flex;align-items:center;gap:8px;padding:9px 15px;border:1px solid;border-radius:11px;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;transition:.15s;text-decoration:none;{{ $style }}">
                    {{ $label }}
                    <span style="font-size:11px;padding:1px 7px;border-radius:20px;{{ $countBg }}">{{ $counts[$label] }}</span>
                </a>
            @endforeach
        </div>
        <a href="{{ route('client.tickets.create') }}" style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13px;text-decoration:none;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
            Nouvelle demande
        </a>
    </div>

    <form method="GET" action="{{ route('client.tickets.index') }}" style="margin-bottom:18px;display:flex;gap:10px;">
        <div style="position:relative;flex:1;max-width:400px;">
            <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
            <input name="search" value="{{ $currentSearch }}" placeholder="Rechercher dans mes demandes…" style="width:100%;padding:10px 14px 10px 36px;border-radius:11px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
        </div>
        <button type="submit" style="padding:10px 18px;border:1px solid var(--border);border-radius:11px;background:var(--surface);color:var(--text2);font-weight:700;font-size:13px;font-family:inherit;cursor:pointer;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">Rechercher</button>
        @if($currentSearch || $activeFilter !== 'Tous')
            <a href="{{ route('client.tickets.index') }}" style="padding:10px 14px;border:1px solid var(--border);border-radius:11px;background:var(--surface);color:var(--muted);font-weight:700;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;transition:.15s;" onmouseover="this.style.color='var(--accent)';this.style.borderColor='var(--accent)'" onmouseout="this.style.color='var(--muted)';this.style.borderColor='var(--border)'">Réinitialiser</a>
        @endif
    </form>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
        @if(count($tickets) > 0)
            <div style="font-weight:800;font-size:16px;color:var(--text);margin-bottom:12px;">Mes demandes</div>
            @foreach($tickets as $ticket)
                <a href="{{ route('client.tickets.show', $ticket['id']) }}" style="display:flex;align-items:center;gap:14px;padding:15px 12px;border-radius:13px;border:1px solid var(--border);margin-bottom:10px;text-decoration:none;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.background='var(--surface2)'" onmouseout="this.style.borderColor='var(--border)';this.style.background='transparent'">
                    <div style="width:40px;height:40px;border-radius:11px;flex-shrink:0;display:grid;place-items:center;background:var(--primsoft);color:var(--accent);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14"></path></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;color:var(--text);font-size:14px;">{{ $ticket['titre'] }}</div>
                        <div style="font-size:12px;color:var(--muted);">Ticket #{{ $ticket['id'] }} · {{ isset($ticket['categorie']['nom']) ? $ticket['categorie']['nom'] : 'Sans catégorie' }} · Mis à jour {{ $ticket['updated_at'] ? \Carbon\Carbon::parse($ticket['updated_at'])->diffForHumans() : 'récemment' }}</div>
                    </div>
                    <x-status-badge type="status" :value="$ticket['statut']" />
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>
                </a>
            @endforeach

            <div style="padding:16px 0 0;border-top:1px solid var(--border);display:flex;justify-content:center;">
                {{ $pagination->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div style="text-align:center;padding:64px 32px;color:var(--muted);">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:16px;opacity:.5;"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14"></path></svg>
                <div style="font-weight:700;font-size:16px;color:var(--text);margin-bottom:6px;">Aucune demande trouvée</div>
                <div style="font-size:13px;color:var(--text2);margin-bottom:18px;">Aucune demande ne correspond à vos critères.</div>
                <a href="{{ route('client.tickets.create') }}" style="display:inline-flex;align-items:center;gap:7px;padding:10px 18px;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13px;text-decoration:none;box-shadow:0 7px 16px -6px var(--accent);">Créer ma première demande</a>
            </div>
        @endif
    </div>
</div>
@endsection