@extends('layouts.client')

@section('title', $ticketData['titre'])
@section('breadcrumb', 'Mon espace · Mes tickets')
@section('pageTitle', 'Ticket #' . $ticketData['id'])

@section('content')
<div style="animation:fadeUp .45s both;max-width:860px;">
    @if(session('success'))
        <div style="background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#16a34a;border-radius:11px;padding:12px 16px;font-size:13px;font-weight:600;margin-bottom:18px;">{{ session('success') }}</div>
    @endif

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:22px;box-shadow:var(--shadow);margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div style="min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <x-status-badge type="status" :value="$ticketData['statut']" />
                    @if($ticketData['priorite'])
                        <x-status-badge type="priority" :value="$ticketData['priorite']" />
                    @endif
                    @if(isset($ticketData['categorie']['nom']))
                        <span style="display:inline-block;white-space:nowrap;padding:4px 10px;border-radius:100px;font-size:11px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $ticketData['categorie']['nom'] }}</span>
                    @endif
                </div>
                <h2 style="font-size:20px;font-weight:800;color:var(--text);margin:10px 0 4px;letter-spacing:-.4px;">{{ $ticketData['titre'] }}</h2>
                <div style="font-size:12px;color:var(--muted);">Créé {{ \Carbon\Carbon::parse($ticketData['created_at'])->diffForHumans() }}</div>
            </div>
            <div style="display:flex;gap:10px;flex-shrink:0;">
                <a href="{{ route('client.tickets.index') }}" style="padding:9px 14px;border:1px solid var(--border);border-radius:11px;background:var(--surface);color:var(--text2);font-weight:700;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    @if($ticketData['article_lie'])
        <div style="display:flex;align-items:center;gap:10px;padding:13px 16px;border-radius:12px;background:var(--primsoft);margin-bottom:20px;">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h11a2 2 0 012 2v14H7a2 2 0 01-2-2zM5 4v16"></path></svg>
            <span style="font-size:13px;font-weight:600;color:var(--accent);">{{ $ticketData['article_lie'] }} — une réponse de la base de connaissances a été proposée.</span>
        </div>
    @endif

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);font-weight:800;font-size:15px;color:var(--text);">Conversation</div>

        <div style="padding:22px;display:flex;flex-direction:column;gap:16px;">
            <div style="align-self:flex-start;max-width:76%;padding:13px 16px;border-radius:16px;font-size:13.5px;line-height:1.55;background:var(--surface2);border:1px solid var(--border);color:var(--text);border-top-left-radius:5px;">
                {{ $ticketData['description'] }}
                <div style="font-size:10.5px;color:var(--muted);margin-top:6px;">Votre demande · {{ \Carbon\Carbon::parse($ticketData['created_at'])->format('d/m/Y H:i') }}</div>
            </div>

            @foreach($ticketData['messages'] as $message)
                @php
                    $isClient = $message['auteur']['role'] === 'client';
                    $authorName = $message['auteur']['name'] ?? 'Utilisateur';
                @endphp
                <div style="display:flex;gap:11px;{{ $isClient ? 'flex-direction:row-reverse;' : '' }}">
                    @if(! $isClient)
                        <div style="width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;flex-shrink:0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"></path></svg>
                        </div>
                    @endif
                    <div style="max-width:76%;padding:13px 16px;border-radius:16px;font-size:13.5px;line-height:1.55;{{ $isClient ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border-top-right-radius:5px;' : 'background:var(--surface2);border:1px solid var(--border);color:var(--text);border-top-left-radius:5px;' }}">
                        {{ $message['contenu'] }}
                        <div style="font-size:10.5px;color:{{ $isClient ? 'rgba(255,255,255,.75)' : 'var(--muted)' }};margin-top:6px;">{{ $authorName }} · {{ \Carbon\Carbon::parse($message['created_at'])->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            @endforeach

            @if($ticketData['statut'] === 'ferme')
                <div style="text-align:center;padding:14px;border-radius:11px;background:var(--surface2);border:1px dashed var(--border);color:var(--muted);font-size:13px;font-weight:600;">Ce ticket est fermé. Vous ne pouvez plus y répondre.</div>
            @else
                <form method="POST" action="{{ route('client.tickets.message', $ticketData['id']) }}" style="display:flex;gap:10px;align-items:flex-end;margin-top:6px;">
                    @csrf
                    <textarea name="contenu" required minlength="2" maxlength="2000" placeholder="Répondre à la conversation…" style="flex:1;min-height:46px;max-height:120px;padding:13px 15px;border-radius:13px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:14px;font-family:inherit;outline:none;resize:none;line-height:1.4;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'"></textarea>
                    <button type="submit" title="Envoyer" style="width:46px;height:46px;flex-shrink:0;border:none;border-radius:13px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;cursor:pointer;display:grid;place-items:center;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"></path></svg>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection