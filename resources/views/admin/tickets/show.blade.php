@extends('layouts.admin')

@section('title', $ticketData['titre'])
@section('breadcrumb', 'Administration · Tickets')
@section('pageTitle', $ticketData['titre'])

@php
    $ref = '#TK-' . str_pad($ticketData['id'], 4, '0', STR_PAD_LEFT);
    $createdDate = $ticketData['created_at'] ? \Carbon\Carbon::parse($ticketData['created_at'])->format('d/m/Y') : 'N/A';
    $suggestion = $ticketData['suggestion_ia'] ?? null;
@endphp

@section('content')
<div data-ticket-id="{{ $ticketData['id'] }}" style="animation:fadeUp .45s both;">
    <a href="{{ route('admin.tickets.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;margin-bottom:16px;color:var(--accent);text-decoration:none;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
        Retour aux tickets
    </a>

    <div style="display:grid;grid-template-columns:1fr 380px;gap:16px;align-items:start;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;padding-bottom:18px;border-bottom:1px solid var(--border);">
                <div>
                    <div style="font-size:11px;color:var(--muted);font-weight:700;">{{ $ref }}</div>
                    <h2 style="font-size:20px;font-weight:800;color:var(--text);margin:3px 0 8px;letter-spacing:-.4px;">{{ $ticketData['titre'] }}</h2>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <x-status-badge type="status" :value="$ticketData['statut']" />
                        @if($ticketData['priorite'])
                            <x-status-badge type="priority" :value="$ticketData['priorite']" />
                        @endif
                        @if($ticketData['categorie'])
                            <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $ticketData['categorie']['nom'] }}</span>
                        @endif
                    </div>
                </div>
                <div style="text-align:right;font-size:12px;color:var(--text2);">
                    <div>Ouvert le {{ $createdDate }}</div>
                    <div style="margin-top:3px;">par <b style="color:var(--text);">{{ $ticketData['client']['name'] ?? 'N/A' }}</b></div>
                    <div style="margin-top:3px;">
                        Assigné à <b style="color:var(--text);">{{ $ticketData['agent']['name'] ?? 'Personne' }}</b>
                    </div>
                </div>
            </div>

            <div style="padding:20px 2px;border-bottom:1px solid var(--border);">
                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">Description</div>
                <div style="font-size:13.5px;line-height:1.6;color:var(--text2);white-space:pre-wrap;">{{ $ticketData['description'] }}</div>
            </div>

            <div style="padding:20px 2px;display:flex;flex-direction:column;gap:16px;">
                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;">Échange</div>
                @forelse($ticketData['messages'] as $message)
                    @php
                        $isAgent = in_array($message['auteur']['role'] ?? '', ['agent', 'admin'], true);
                        $initials = strtoupper(substr($message['auteur']['name'] ?? '?', 0, 1));
                        $authorName = $message['auteur']['name'] ?? 'Inconnu';
                        $time = \Carbon\Carbon::parse($message['created_at'])->format('d/m/Y · H:i');
                    @endphp
                    <div style="display:flex;gap:12px;{{ $isAgent ? 'flex-direction:row-reverse;' : '' }}">
                        <div style="width:36px;height:36px;border-radius:11px;display:grid;place-items:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;background:{{ $isAgent ? 'linear-gradient(135deg,var(--accent),var(--accent2))' : 'var(--gradav)' }};">{{ $initials }}</div>
                        <div style="max-width:78%;padding:12px 15px;border-radius:15px;{{ $isAgent ? 'background:var(--primsoft);border-top-right-radius:4px;' : 'background:var(--surface2);border:1px solid var(--border);border-top-left-radius:4px;' }}">
                            <div style="display:flex;justify-content:space-between;gap:12px;margin-bottom:5px;">
                                <span style="font-weight:700;font-size:12px;color:var(--text);">{{ $authorName }}</span>
                                <span style="font-size:11px;color:var(--muted);">{{ $time }}</span>
                            </div>
                            <div style="font-size:13.5px;line-height:1.6;color:var(--text2);">{{ $message['contenu'] }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;padding:30px;color:var(--muted);font-size:13px;">Aucun message pour ce ticket.</div>
                @endforelse
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:0;">
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px;box-shadow:var(--shadow);">
                <div style="font-weight:800;font-size:15px;color:var(--text);margin-bottom:4px;">Assignation</div>
                <div style="font-size:12px;color:var(--muted);margin-bottom:14px;">Confier ce ticket à un agent support.</div>

                @if(session('success'))
                    <div style="padding:10px 12px;border-radius:10px;background:rgba(22,163,74,.12);color:#16a34a;font-size:12.5px;font-weight:700;margin-bottom:12px;">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.tickets.assign', $ticketData['id']) }}">
                    @csrf
                    <select name="agent_id" required style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;font-family:inherit;font-weight:600;cursor:pointer;outline:none;margin-bottom:10px;">
                        <option value="" @selected($ticketData['agent'] === null)>Aucun agent</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" @selected($ticketData['agent']['id'] ?? null === $agent->id)>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    @error('agent_id')
                        <div style="color:#dc2626;font-size:12px;font-weight:600;margin-bottom:10px;">{{ $message }}</div>
                    @enderror
                    <button type="submit" style="width:100%;padding:11px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13.5px;font-family:inherit;cursor:pointer;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Assigner l'agent</button>
                </form>
            </div>

            <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px;box-shadow:var(--shadow);">
                <div style="font-weight:800;font-size:15px;color:var(--text);margin-bottom:4px;">Analyse IA</div>
                <div style="font-size:12px;color:var(--muted);margin-bottom:14px;">Dernière suggestion générée pour ce ticket.</div>

                @if($ticketData['article_lie'])
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:100px;background:var(--primsoft);color:var(--accent);font-size:11px;font-weight:700;margin-bottom:12px;">{{ $ticketData['article_lie'] }}</span>
                @endif

                @if($suggestion && $suggestion['resume'])
                    <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Résumé</div>
                    <div style="font-size:13px;line-height:1.55;color:var(--text2);background:var(--primsoft);padding:12px 13px;border-radius:11px;border:1px solid var(--border);margin-bottom:14px;">{{ $suggestion['resume'] }}</div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                        <div>
                            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Catégorie</div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);background:var(--surface2);padding:10px 12px;border-radius:11px;border:1px solid var(--border);">{{ $suggestion['categorie_proposee'] }}</div>
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Priorité</div>
                            <div style="font-size:13px;font-weight:700;color:var(--text);background:var(--surface2);padding:10px 12px;border-radius:11px;border:1px solid var(--border);">{{ ucfirst($suggestion['priorite_proposee']) }}</div>
                        </div>
                    </div>

                    <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Brouillon de réponse</div>
                    <div style="font-size:12.5px;line-height:1.55;color:var(--text2);background:var(--surface2);padding:12px 13px;border-radius:11px;border:1px solid var(--border);">{{ $suggestion['brouillon_reponse'] }}</div>
                @else
                    <div style="text-align:center;padding:22px 6px;color:var(--muted);">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;opacity:.5;"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>
                        <div style="font-size:12.5px;font-weight:700;color:var(--text2);">Aucune analyse pour ce ticket.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
