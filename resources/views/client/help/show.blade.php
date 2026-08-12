@extends('layouts.client')

@section('title', $article->titre)
@section('breadcrumb', 'Mon espace · Centre d\'aide')
@section('pageTitle', $article->titre)

@section('content')
<div style="animation:fadeUp .45s both;max-width:860px;">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:26px;box-shadow:var(--shadow);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                @if($article->categorie)
                    <span style="display:inline-block;white-space:nowrap;padding:4px 10px;border-radius:100px;font-size:11px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $article->categorie->nom }}</span>
                @endif
                <span style="font-size:11px;color:var(--muted);font-weight:600;">Mis à jour {{ $article->published_at ? $article->published_at->diffForHumans() : '' }}</span>
            </div>
            <a href="{{ route('client.help') }}" style="padding:9px 14px;border:1px solid var(--border);border-radius:11px;background:var(--surface);color:var(--text2);font-weight:700;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                Retour
            </a>
        </div>

        <h1 style="font-size:24px;font-weight:800;color:var(--text);margin:0 0 16px;letter-spacing:-.5px;line-height:1.3;">{{ $article->titre }}</h1>

        <div style="font-size:14px;line-height:1.75;color:var(--text2);white-space:pre-line;">
            {{ $article->contenu }}
        </div>

        <div style="display:flex;gap:22px;margin-top:26px;padding-top:18px;border-top:1px solid var(--border);font-size:12px;color:var(--muted);flex-wrap:wrap;">
            <span>Publié le {{ $article->published_at?->format('d/m/Y') }}</span>
            <span>Dernière mise à jour le {{ $article->updated_at?->format('d/m/Y') }}</span>
        </div>
    </div>

    <div style="background:var(--primsoft);border:1px solid var(--border);border-radius:16px;padding:22px;margin-top:20px;display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;">
        <div>
            <div style="font-weight:800;font-size:15px;color:var(--text);margin-bottom:4px;">Vous n'avez pas trouvé votre réponse ?</div>
            <div style="font-size:13px;color:var(--text2);">Notre équipe ou l'assistant IA peut vous aider.</div>
        </div>
        <div style="display:flex;gap:10px;flex-shrink:0;">
            <a href="{{ route('client.assistant') }}" style="padding:10px 16px;border:1px solid var(--accent);border-radius:11px;background:var(--surface);color:var(--accent);font-weight:700;font-size:13px;text-decoration:none;transition:.15s;" onmouseover="this.style.background='var(--accent)';this.style.color='#fff'" onmouseout="this.style.background='var(--surface)';this.style.color='var(--accent)'">Demander à l'IA</a>
            <a href="{{ route('client.tickets.create') }}" style="padding:10px 16px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13px;text-decoration:none;box-shadow:0 7px 16px -6px var(--accent);">Ouvrir une demande</a>
        </div>
    </div>
</div>
@endsection