@extends('layouts.client')

@section('title', 'Nouvelle demande')
@section('breadcrumb', 'Mon espace · Mes tickets')
@section('pageTitle', 'Nouvelle demande')

@section('content')
<div style="animation:fadeUp .45s both;max-width:720px;">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:22px 24px;box-shadow:var(--shadow);">
        <h3 style="margin:0 0 4px;font-size:18px;font-weight:800;color:var(--text);letter-spacing:-.3px;">Décrivez votre problème</h3>
        <p style="margin:0 0 20px;font-size:12.5px;color:var(--text2);">L'IA cherchera d'abord une réponse dans la base de connaissances avant d'affecter un agent.</p>

        @if($errors->any())
            <div style="background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.25);color:#dc2626;border-radius:11px;padding:12px 14px;font-size:13px;margin-bottom:18px;">
                <ul style="margin:0;padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('client.tickets.store') }}">
            @csrf

            <div style="display:flex;flex-direction:column;gap:16px;">
                <div>
                    <label style="display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:7px;">Sujet</label>
                    <input name="titre" value="{{ old('titre') }}" required placeholder="Ex : Impossible de télécharger ma facture" style="width:100%;padding:12px 14px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13.5px;font-family:inherit;outline:none;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                </div>

                <div>
                    <label style="display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:7px;">Catégorie</label>
                    <select name="categorie_id" required style="width:100%;padding:12px 14px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13.5px;font-family:inherit;cursor:pointer;outline:none;">
                        <option value="">Choisir une catégorie…</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('categorie_id') == $category->id)>{{ $category->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:12.5px;font-weight:700;color:var(--text);margin-bottom:7px;">Description</label>
                    <textarea name="description" required minlength="20" placeholder="Détaillez le contexte, les étapes et le message d'erreur éventuel…" style="width:100%;min-height:130px;padding:12px 14px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13.5px;font-family:inherit;outline:none;resize:vertical;line-height:1.5;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">{{ old('description') }}</textarea>
                    <div style="font-size:11px;color:var(--muted);margin-top:5px;">Minimum 20 caractères.</div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:12px;background:var(--primsoft);">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="var(--accent)" style="flex-shrink:0;"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>
                    <span style="font-size:12px;color:var(--accent);font-weight:600;line-height:1.4;">L'IA cherchera d'abord une réponse dans la base de connaissances avant d'affecter un agent.</span>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid var(--border);">
                <a href="{{ route('client.tickets.index') }}" style="padding:11px 18px;border:1px solid var(--border);border-radius:11px;background:var(--surface);color:var(--text2);font-weight:700;font-size:13px;text-decoration:none;font-family:inherit;display:inline-flex;align-items:center;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">Annuler</a>
                <button type="submit" style="padding:11px 20px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13px;font-family:inherit;cursor:pointer;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Créer la demande</button>
            </div>
        </form>
    </div>
</div>
@endsection