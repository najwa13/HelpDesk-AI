@extends('layouts.agent')

@section('title', $ticketData['titre'])
@section('breadcrumb', 'Support · Tickets')
@section('pageTitle', $ticketData['titre'])

@php
    $transitions = $ticketData['transitions'] ?? [];
    $statutsDisponibles = $ticketData['statuts_disponibles'] ?? [];
    $ref = '#TK-' . str_pad($ticketData['id'], 4, '0', STR_PAD_LEFT);
    $createdDate = $ticketData['created_at'] ? \Carbon\Carbon::parse($ticketData['created_at'])->format('d/m/Y') : 'N/A';
    $suggestion = $ticketData['suggestion_ia'] ?? null;
@endphp

@section('content')
<div data-ticket-id="{{ $ticketData['id'] }}" style="animation:fadeUp .45s both;">
    <a href="{{ route('agent.tickets.index') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:700;margin-bottom:16px;color:var(--accent);text-decoration:none;">
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
                </div>
            </div>

            <div style="padding:22px 2px;display:flex;flex-direction:column;gap:16px;" data-messages-list>
                @forelse($ticketData['messages'] as $message)
                    @php
                        $isAgent = ($message['auteur']['role'] ?? '') === 'agent';
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
                    <div style="text-align:center;padding:40px;color:var(--muted);font-size:13px;">Aucun message pour ce ticket.</div>
                @endforelse
            </div>

            <div style="border-top:1px solid var(--border);padding-top:16px;">
                <div data-ai-draft-banner style="display:none;font-size:12px;color:var(--accent);font-weight:700;margin-bottom:8px;align-items:center;gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>
                    Brouillon inséré par l'IA — à relire avant envoi
                </div>
                <textarea data-message-input rows="3" placeholder="Rédigez votre réponse au client…" style="width:100%;min-height:90px;padding:13px 15px;border-radius:12px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13.5px;font-family:inherit;outline:none;resize:vertical;line-height:1.5;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
                    <div style="display:flex;gap:8px;">
                        @if(count($transitions) > 0)
                            <select data-statut-select style="padding:9px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:13px;font-family:inherit;font-weight:600;cursor:pointer;">
                                @foreach($statutsDisponibles as $s)
                                    @php
                                        $isCurrent = ($s['value'] === $ticketData['statut']);
                                        $isAllowed = in_array($s['value'], $transitions, true);
                                    @endphp
                                    <option value="{{ $s['value'] }}" @selected($isCurrent) @disabled(!$isAllowed && !$isCurrent)>{{ $s['label'] }}</option>
                                @endforeach
                            </select>
                            <button type="button" data-change-status-btn style="padding:9px 14px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:var(--text2);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">Appliquer</button>
                        @endif
                    </div>
                    <button type="button" data-send-message-btn style="display:flex;align-items:center;gap:7px;padding:11px 18px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13px;font-family:inherit;cursor:pointer;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"></path></svg>
                        Envoyer la réponse
                    </button>
                </div>
                <div data-message-error style="color:#dc2626;font-size:12px;font-weight:600;margin-top:8px;display:none;"></div>
            </div>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:0;box-shadow:var(--shadow);overflow:hidden;position:sticky;top:0;">
            <div style="padding:16px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:11px;">
                <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;animation:ring 2s ease-out infinite;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:800;font-size:15px;color:var(--text);">Assistance IA</div>
                    <div style="font-size:11px;color:var(--muted);">Analyse consultative — validée par l'agent</div>
                </div>
            </div>

            <div style="padding:18px;" data-ai-analysis>
                @if($suggestion && $suggestion['resume'])
                    <div data-ai-done>
                        <div style="animation:pop .4s both;">
                            <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Résumé</div>
                            <div style="font-size:13px;line-height:1.55;color:var(--text2);background:var(--primsoft);padding:12px 13px;border-radius:11px;border:1px solid var(--border);">{{ $suggestion['resume'] }}</div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;animation:pop .4s .1s both;">
                            <div>
                                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Catégorie</div>
                                <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:var(--text);background:var(--surface2);padding:10px 12px;border-radius:11px;border:1px solid var(--border);">{{ $suggestion['categorie_proposee'] }}</div>
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Priorité</div>
                                @php
                                    $prio = strtolower($suggestion['priorite_proposee'] ?? '');
                                    $prioMap = [
                                        'haute' => ['bg' => 'rgba(220,38,38,.1)', 'color' => '#dc2626'],
                                        'moyenne' => ['bg' => 'rgba(217,119,6,.12)', 'color' => '#d97706'],
                                        'basse' => ['bg' => 'rgba(22,163,74,.1)', 'color' => '#16a34a'],
                                    ];
                                    $prioStyle = $prioMap[$prio] ?? ['bg' => 'var(--surface2)', 'color' => 'var(--text)'];
                                @endphp
                                <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:700;color:{{ $prioStyle['color'] }};background:{{ $prioStyle['bg'] }};padding:10px 12px;border-radius:11px;border:1px solid var(--border);">
                                    <span style="width:7px;height:7px;border-radius:50%;background:{{ $prioStyle['color'] }};"></span>
                                    {{ ucfirst($suggestion['priorite_proposee']) }}
                                </div>
                            </div>
                        </div>
                        <div style="margin-top:14px;animation:pop .4s .2s both;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:6px;">
                                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;">Brouillon de réponse</div>
                                @if($ticketData['article_lie'])
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:100px;background:var(--primsoft);color:var(--accent);font-size:11px;font-weight:700;">{{ $ticketData['article_lie'] }}</span>
                                @endif
                            </div>
                            <div data-ai-draft style="font-size:12.5px;line-height:1.55;color:var(--text2);background:var(--surface2);padding:12px 13px;border-radius:11px;border:1px solid var(--border);">{{ $suggestion['brouillon_reponse'] }}</div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:8px;margin-top:14px;animation:pop .4s .3s both;">
                            <button type="button" data-insert-draft-btn style="width:100%;padding:12px;border:1.5px solid var(--accent);border-radius:11px;background:var(--primsoft);color:var(--accent);font-weight:700;font-size:13.5px;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:.15s;" onmouseover="this.style.background='var(--accent)';this.style.color='#fff'" onmouseout="this.style.background='var(--primsoft)';this.style.color='var(--accent)'">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                                Insérer dans la réponse
                            </button>
                            <button type="button" data-relaunch-analyze-btn style="width:100%;padding:11px;border-radius:11px;border:1px solid var(--border);background:var(--surface);color:var(--text2);font-weight:700;font-size:12.5px;font-family:inherit;cursor:pointer;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">Relancer l'analyse</button>
                        </div>
                    </div>
                @else
                    <div data-ai-idle style="text-align:center;padding:24px 6px;">
                        <div style="width:56px;height:56px;border-radius:16px;background:var(--primsoft);color:var(--accent);display:grid;place-items:center;margin:0 auto 14px;animation:float 4s ease-in-out infinite;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>
                        </div>
                        <div style="font-weight:800;color:var(--text);font-size:15px;margin-bottom:8px;">Assistance IA</div>
                        <p style="font-size:12.5px;color:var(--text2);line-height:1.5;margin:0 0 18px;">Analysez ce ticket pour obtenir un résumé, une priorité et un brouillon de réponse.</p>
                        <button type="button" data-analyze-btn style="width:100%;padding:12px;border:none;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:13.5px;font-family:inherit;cursor:pointer;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Lancer l'analyse</button>
                    </div>
                @endif

                <div data-ai-loading style="display:none;text-align:center;padding:34px 6px;">
                    <div style="width:44px;height:44px;border-radius:50%;border:3px solid var(--primsoft);border-top-color:var(--accent);margin:0 auto 16px;animation:spin .8s linear infinite;"></div>
                    <div style="font-weight:700;color:var(--text);font-size:13.5px;">Analyse en cours…</div>
                    <div style="font-size:12px;color:var(--muted);margin-top:4px;">Traitement en tâche de fond</div>
                </div>
                <div data-ai-error style="display:none;color:#dc2626;font-size:12px;font-weight:600;margin-top:10px;text-align:center;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/agent.js'])
@endpush
@endsection
