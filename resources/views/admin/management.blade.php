@extends('layouts.admin')

@section('title', 'Agents & Catégories')
@section('breadcrumb', 'Administration')
@section('pageTitle', 'Agents & Catégories')

@section('content')
<div style="animation:fadeUp .45s both;">
    @if(session('success'))
        <div style="padding:12px 16px;border-radius:12px;background:rgba(22,163,74,.12);color:#16a34a;font-size:13px;font-weight:700;margin-bottom:16px;">{{ session('success') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div style="font-weight:800;font-size:16px;color:var(--text);">Agents support</div>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:16px;">{{ count($agents) }} agent(s) actif(s)</div>

            @if($agents->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                <th style="padding:10px 8px;font-weight:700;">Agent</th>
                                <th style="padding:10px 8px;font-weight:700;">Tickets assignés</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agents as $agent)
                                @php
                                    $nameParts = explode(' ', $agent->name);
                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                @endphp
                                <tr style="border-top:1px solid var(--border);">
                                    <td style="padding:12px 8px;">
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div style="width:34px;height:34px;border-radius:10px;background:var(--gradav);display:grid;place-items:center;color:#fff;font-weight:700;font-size:12px;">{{ $initials }}</div>
                                            <div>
                                                <div style="font-weight:700;color:var(--text);">{{ $agent->name }}</div>
                                                <div style="font-size:11px;color:var(--muted);">{{ $agent->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding:12px 8px;">
                                        <span style="display:inline-flex;padding:3px 10px;border-radius:100px;font-size:12px;font-weight:700;background:var(--primsoft);color:var(--accent);">{{ $agent->tickets_affectes_count }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:30px;color:var(--muted);font-size:13px;">Aucun agent enregistré.</div>
            @endif
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px;box-shadow:var(--shadow);overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div style="font-weight:800;font-size:16px;color:var(--text);">Catégories</div>
            </div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:16px;">{{ count($categories) }} catégorie(s)</div>

            <form method="POST" action="{{ route('admin.management.categories.store') }}" style="display:flex;gap:8px;margin-bottom:16px;">
                @csrf
                <input type="text" name="nom" required placeholder="Nom de la catégorie" style="flex:1;padding:10px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
                <button type="submit" style="padding:10px 16px;border:none;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13px;font-family:inherit;cursor:pointer;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Ajouter</button>
            </form>
            @error('nom')
                <div style="color:#dc2626;font-size:12px;font-weight:600;margin-bottom:10px;">{{ $message }}</div>
            @enderror
            @error('category')
                <div style="color:#dc2626;font-size:12px;font-weight:600;margin-bottom:10px;">{{ $message }}</div>
            @enderror

            @if($categories->isNotEmpty())
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="text-align:left;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">
                                <th style="padding:10px 8px;font-weight:700;">Nom</th>
                                <th style="padding:10px 8px;font-weight:700;">Tickets</th>
                                <th style="padding:10px 8px;font-weight:700;">Articles</th>
                                <th style="padding:10px 8px;font-weight:700;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr style="border-top:1px solid var(--border);">
                                    <td style="padding:12px 8px;">
                                        <div style="font-weight:700;color:var(--text);">{{ $category->nom }}</div>
                                        @if($category->description)
                                            <div style="font-size:11px;color:var(--muted);">{{ $category->description }}</div>
                                        @endif
                                    </td>
                                    <td style="padding:12px 8px;color:var(--text2);font-weight:600;">{{ $category->tickets_count }}</td>
                                    <td style="padding:12px 8px;color:var(--text2);font-weight:600;">{{ $category->articles_count }}</td>
                                    <td style="padding:12px 8px;text-align:right;">
                                        @if($category->tickets_count === 0 && $category->articles_count === 0)
                                            <form method="POST" action="{{ route('admin.management.categories.destroy', $category) }}" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="border:none;background:none;color:var(--muted);cursor:pointer;font-size:12px;font-weight:700;transition:.15s;" onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='var(--muted)'">Supprimer</button>
                                            </form>
                                        @else
                                            <span style="font-size:11px;color:var(--muted);" title="Catégorie utilisée">Utilisée</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center;padding:30px;color:var(--muted);font-size:13px;">Aucune catégorie. Ajoutez-en une ci-dessus.</div>
            @endif
        </div>
    </div>
</div>
@endsection
