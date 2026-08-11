@extends('layouts.agent')

@section('title', $article->titre)
@section('breadcrumb', 'Documentation · Base de connaissances')
@section('pageTitle', $article->titre)

@section('content')
<div style="animation:fadeUp .45s both;max-width:860px;margin:0 auto;">
    <a href="{{ route('agent.knowledge') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;margin-bottom:16px;color:var(--accent);text-decoration:none;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
        Retour à la base de connaissances
    </a>

    <article style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px;box-shadow:var(--shadow);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
            <div>
                @if($article->categorie)
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:var(--primsoft);color:var(--accent);margin-bottom:8px;">{{ $article->categorie->nom }}</span>
                @endif
                <h1 style="font-size:24px;font-weight:800;color:var(--text);margin:0;letter-spacing:-.5px;line-height:1.2;">{{ $article->titre }}</h1>
                <div style="font-size:12px;color:var(--muted);margin-top:8px;">
                    Publié le {{ $article->published_at ? \Carbon\Carbon::parse($article->published_at)->format('d/m/Y') : 'N/A' }}
                </div>
            </div>
        </div>

        <div style="font-size:15px;line-height:1.8;color:var(--text);white-space:pre-wrap;">{{ $article->contenu }}</div>
    </article>
</div>
@endsection