@extends('layouts.client')

@section('title', "Centre d'aide")
@section('breadcrumb', 'Mon espace')
@section('pageTitle', "Centre d'aide")

@section('content')
@push('styles')
<style>
    .help-search { background: #ffffff; color: #191430; border: 1px solid #ecebf5; box-shadow: 0 10px 30px -8px rgba(0,0,0,.3); }
    .help-search::placeholder { color: #8f8ba3; opacity: 1; }
    .help-search:focus { border-color: #7c3aed; box-shadow: 0 10px 30px -8px rgba(0,0,0,.3), 0 0 0 3px rgba(124,58,237,.12); }
</style>
@endpush
<div style="animation:fadeUp .45s both;">
    <div style="background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:20px;padding:38px;text-align:center;color:#fff;margin-bottom:22px;position:relative;overflow:hidden;">
        <div style="position:absolute;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.1);top:-70px;right:-40px;"></div>
        <div style="position:absolute;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.08);bottom:-40px;left:-30px;"></div>
        <h2 style="font-size:26px;font-weight:800;margin:0 0 10px;letter-spacing:-.5px;position:relative;color:#fff;">Comment pouvons-nous vous aider ?</h2>
        <p style="opacity:.9;margin:0 0 22px;position:relative;">Trouvez une réponse instantanée dans notre centre d'aide.</p>
        <form method="GET" action="{{ route('client.help') }}" style="max-width:460px;margin:0 auto;position:relative;">
            <input class="help-search" name="search" value="{{ $currentSearch }}" placeholder="Décrivez votre problème…" style="width:100%;padding:15px 20px;border-radius:13px;font-size:15px;font-family:inherit;outline:none;transition:border-color .15s, box-shadow .15s;">
        </form>
    </div>

    @if(count($articles) > 0)
        <div style="font-weight:800;font-size:16px;color:var(--text);margin-bottom:14px;">Articles populaires</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
            @foreach($articles as $article)
                <a href="{{ route('client.help.show', $article) }}" style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);cursor:pointer;text-decoration:none;transition:.15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 16px 40px -12px rgba(90,40,180,.25)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
                    @if($article->categorie)
                        <span style="display:inline-block;white-space:nowrap;padding:4px 10px;border-radius:100px;font-size:11px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $article->categorie->nom }}</span>
                    @endif
                    <div style="font-weight:800;font-size:15px;color:var(--text);line-height:1.35;margin:12px 0 8px;">{{ $article->titre }}</div>
                    <p style="font-size:13px;color:var(--text2);line-height:1.55;margin:0;">{{ \Illuminate\Support\Str::limit(strip_tags($article->contenu), 130) }}</p>
                </a>
            @endforeach
        </div>
    @else
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:48px 32px;text-align:center;color:var(--muted);box-shadow:var(--shadow);">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:14px;opacity:.5;"><path d="M5 4h11a2 2 0 012 2v14H7a2 2 0 01-2-2zM5 4v16"></path></svg>
            <div style="font-weight:700;font-size:16px;color:var(--text);margin-bottom:6px;">Aucun article trouvé</div>
            <div style="font-size:13px;color:var(--text2);">Essayez d'autres mots-clés ou ouvrez une nouvelle demande.</div>
            <a href="{{ route('client.tickets.create') }}" style="display:inline-flex;align-items:center;gap:7px;margin-top:18px;padding:10px 18px;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13px;text-decoration:none;box-shadow:0 7px 16px -6px var(--accent);">Créer une demande</a>
        </div>
    @endif
</div>
@endsection