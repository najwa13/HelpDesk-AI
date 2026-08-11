@extends('layouts.admin')

@section('title', $article->titre)
@section('breadcrumb', 'Administration · Base de connaissances')
@section('pageTitle', $article->titre)

@section('content')
<div style="animation:fadeUp .45s both;">
    <a href="{{ route('admin.knowledge.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;margin-bottom:16px;color:var(--accent);text-decoration:none;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
        Retour aux articles
    </a>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:var(--shadow);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <div>
                <div style="font-size:11px;color:var(--muted);font-weight:700;">#KB-{{ str_pad((string) $article->id, 2, '0', STR_PAD_LEFT) }}</div>
                <h2 style="font-size:20px;font-weight:800;color:var(--text);margin:3px 0 8px;letter-spacing:-.4px;">{{ $article->titre }}</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @if($article->categorie)
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $article->categorie->nom }}</span>
                    @endif
                    @if($article->published_at)
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:rgba(22,163,74,.14);color:#16a34a;">Publié</span>
                    @else
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:700;background:rgba(217,119,6,.14);color:#d97706;">Brouillon</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('admin.knowledge.edit', $article) }}" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;background:var(--primsoft);color:var(--accent);font-weight:700;font-size:13px;text-decoration:none;transition:.15s;" onmouseover="this.style.background='var(--accent)';this.style.color='#fff'" onmouseout="this.style.background='var(--primsoft)';this.style.color='var(--accent)'">Modifier</a>
                <form method="POST" action="{{ route('admin.knowledge.destroy', $article) }}" onsubmit="return confirm('Supprimer cet article ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:10px;background:var(--surface);color:#dc2626;border:1px solid rgba(220,38,38,.25);font-weight:700;font-size:13px;font-family:inherit;cursor:pointer;transition:.15s;" onmouseover="this.style.background='rgba(220,38,38,.08)'" onmouseout="this.style.background='var(--surface)'">Supprimer</button>
                </form>
            </div>
        </div>

        <div style="font-size:14px;line-height:1.7;color:var(--text2);white-space:pre-wrap;">{{ $article->contenu }}</div>
    </div>
</div>
@endsection
