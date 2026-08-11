@extends('layouts.agent')

@section('title', 'Base de connaissances')
@section('breadcrumb', 'Documentation')
@section('pageTitle', 'Base de connaissances')

@section('content')
<div style="animation:fadeUp .45s both;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="font-weight:800;font-size:18px;color:var(--text);">Articles publiés</div>
            <div style="font-size:13px;color:var(--muted);margin-top:2px;">{{ $articles->total() }} article(s) disponible(s)</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:280px 1fr;gap:24px;align-items:start;">
        <aside style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);height:fit-content;position:sticky;top:100px;">
            <div style="font-weight:800;font-size:14px;color:var(--text);margin-bottom:12px;">Filtres</div>
            
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Catégorie</label>
                <select data-kb-category-filter style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;font-family:inherit;cursor:pointer;outline:none;">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Recherche</label>
                <input type="text" data-kb-search placeholder="Titre, contenu…" style="width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
            </div>
        </aside>

        <main style="flex:1;min-width:0;">
            @if($articles->isNotEmpty())
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;" data-kb-articles-grid>
                    @foreach($articles as $article)
                        <article style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);transition:transform .15s,box-shadow .2s;cursor:pointer;" onclick="window.location='{{ route('agent.knowledge.show', $article) }}'" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 16px 40px -12px rgba(90,40,180,.25)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                                @if($article->categorie)
                                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $article->categorie->nom }}</span>
                                @endif
                                @if($article->published_at)
                                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(22,163,74,.14);color:#16a34a;">Publié</span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(217,119,6,.14);color:#d97706;">Brouillon</span>
                                @endif
                            </div>
                            <div style="font-weight:800;font-size:15px;color:var(--text);line-height:1.35;margin-bottom:8px;">{{ $article->titre }}</div>
                            <p style="font-size:13px;color:var(--text2);line-height:1.55;margin:0 0 16px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">{{ $article->contenu }}</p>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid var(--border);font-size:12px;color:var(--muted);">
                                <span style="display:flex;align-items:center;gap:6px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>Article</span>
                                <span style="display:flex;align-items:center;gap:6px;color:#16a34a;font-weight:700"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>Valide</span>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div style="margin-top:24px;display:flex;justify-content:center;">
                    {{ $articles->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div style="text-align:center;padding:64px 32px;color:var(--muted);">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:16px;opacity:.5;"><path d="M5 4h11a2 2 0 012 2v14H7a2 2 0 01-2-2zM5 4v16"></path></svg>
                    <div style="font-weight:700;font-size:16px;color:var(--text);margin-bottom:6px;">Aucun article publié</div>
                    <div style="font-size:13px;color:var(--text2);">Les articles publiés apparaîtront ici.</div>
                </div>
            @endif
        </main>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/agent.js'])
@endpush
@endsection