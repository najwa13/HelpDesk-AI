@extends('layouts.admin')

@php
    $isEdit = ! is_null($article);
    $action = $isEdit
        ? route('admin.knowledge.update', $article)
        : route('admin.knowledge.store');
    $method = $isEdit ? 'PUT' : 'POST';
    $title = $isEdit ? $article->titre : 'Nouvel article';
@endphp

@section('title', $title)
@section('breadcrumb', 'Administration · Base de connaissances')
@section('pageTitle', $isEdit ? 'Modifier l\'article' : 'Nouvel article')

@section('content')
<div style="animation:fadeUp .45s both;">
    <a href="{{ $isEdit ? route('admin.knowledge.show', $article) : route('admin.knowledge.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;margin-bottom:16px;color:var(--accent);text-decoration:none;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
        Retour
    </a>

    <div style="max-width:760px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:24px;box-shadow:var(--shadow);">
            <form method="POST" action="{{ $action }}">
                @csrf
                @method($method)

                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Titre</label>
                    <input type="text" name="titre" required value="{{ old('titre', $isEdit ? $article->titre : '') }}" placeholder="Titre de l'article" style="width:100%;padding:11px 13px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13.5px;font-family:inherit;outline:none;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                    @error('titre')
                        <div style="color:#dc2626;font-size:12px;font-weight:600;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Catégorie</label>
                    <select name="categorie_id" required style="width:100%;padding:11px 13px;border-radius:11px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13.5px;font-family:inherit;font-weight:600;cursor:pointer;outline:none;">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categorie_id', $isEdit ? $article->categorie_id : null) == $cat->id)>{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                    @error('categorie_id')
                        <div style="color:#dc2626;font-size:12px;font-weight:600;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Contenu</label>
                    <textarea name="contenu" required rows="10" placeholder="Rédigez la solution…" style="width:100%;padding:13px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13.5px;font-family:inherit;outline:none;resize:vertical;line-height:1.6;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">{{ old('contenu', $isEdit ? $article->contenu : '') }}</textarea>
                    @error('contenu')
                        <div style="color:#dc2626;font-size:12px;font-weight:600;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;font-weight:700;color:var(--text);">
                        <input type="checkbox" name="publier" value="1" @checked($isEdit && $article->published_at) style="width:16px;height:16px;accent-color:var(--accent);">
                        Publier immédiatement
                    </label>
                    <div style="font-size:11.5px;color:var(--muted);margin-top:4px;">Sinon, l'article sera enregistré comme brouillon.</div>
                </div>

                <button type="submit" style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:14px;font-family:inherit;cursor:pointer;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">{{ $isEdit ? 'Enregistrer les modifications' : 'Créer l\'article' }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
