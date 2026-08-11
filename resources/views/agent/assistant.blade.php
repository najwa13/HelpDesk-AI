@extends('layouts.agent')

@section('title', 'Assistant IA')
@section('breadcrumb', 'IA')
@section('pageTitle', 'Assistant IA')

@php
    $selectedId = $selectedTicketId;
    $selectedTicket = null;
    if ($selectedId) {
        $selectedTicket = $tickets->firstWhere('id', $selectedId);
    }
@endphp

@section('content')
<div style="animation:fadeUp .45s both;max-width:860px;margin:0 auto;height:100%;display:flex;flex-direction:column;" data-assistant-root>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:0;box-shadow:var(--shadow);overflow:hidden;flex:1;display:flex;flex-direction:column;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>
            </div>
            <div style="flex:1;">
                <div style="font-weight:800;font-size:16px;color:var(--text);">Assistant agent</div>
                <div style="font-size:12px;color:#16a34a;display:flex;align-items:center;gap:5px;">
                    <span style="width:7px;height:7px;border-radius:50%;background:#16a34a;"></span>
                    <span id="assistant-status">Analyse consultative en direct</span>
                </div>
            </div>
            @if(count($tickets) > 0)
                <div style="position:relative;">
                    <select data-assistant-ticket-select style="padding:8px 12px;border-radius:10px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;font-weight:600;cursor:pointer;outline:none;">
                        <option value="">Sélectionner un ticket…</option>
                        @foreach($tickets as $t)
                            <option value="{{ $t['id'] }}" {{ $selectedId == $t['id'] ? 'selected' : '' }}>#TK-{{ str_pad($t['id'], 4, '0', STR_PAD_LEFT) }} — {{ $t['titre'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div style="flex:1;overflow-y:auto;padding:22px;display:flex;flex-direction:column;gap:16px;" data-assistant-messages>
            <div data-assistant-empty style="text-align:center;padding:60px 20px;color:var(--muted);">
                <div style="width:64px;height:64px;border-radius:16px;background:var(--primsoft);color:var(--accent);display:grid;place-items:center;margin:0 auto 16px;animation:float 4s ease-in-out infinite;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>
                </div>
                <div style="font-weight:700;color:var(--text);font-size:16px;margin-bottom:8px;">Bienvenue dans l'assistant IA</div>
                <div style="font-size:13px;color:var(--text2);line-height:1.5;max-width:400px;margin:0 auto;">Sélectionnez un ticket pour démarrer une conversation, ou posez directement une question.</div>
            </div>
        </div>

        <div style="padding:16px 20px;border-top:1px solid var(--border);">
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;" data-assistant-suggestions>
                <button data-suggestion-btn data-question="Résumer ce ticket" style="padding:7px 13px;border:1px solid var(--border);border-radius:100px;background:var(--surface2);color:var(--text2);font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">Résumer ce ticket</button>
                <button data-suggestion-btn data-question="Proposer une priorité" style="padding:7px 13px;border:1px solid var(--border);border-radius:100px;background:var(--surface2);color:var(--text2);font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">Proposer une priorité</button>
                <button data-suggestion-btn data-question="Rédiger 3 réponses" style="padding:7px 13px;border:1px solid var(--border);border-radius:100px;background:var(--surface2);color:var(--text2);font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text2)'">Rédiger 3 réponses</button>
            </div>
            <div style="display:flex;gap:10px;align-items:flex-end;">
                <textarea data-assistant-input rows="1" placeholder="Écrivez votre message…" style="flex:1;min-height:46px;max-height:120px;padding:13px 15px;border-radius:13px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:14px;font-family:inherit;outline:none;resize:none;line-height:1.4;transition:.15s;" onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--primsoft)'" onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'"></textarea>
                <button type="button" data-assistant-send style="width:46px;height:46px;flex-shrink:0;border:none;border-radius:13px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;cursor:pointer;display:grid;place-items:center;box-shadow:0 7px 16px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"></path></svg>
                </button>
            </div>
            <div data-assistant-error style="color:#dc2626;font-size:12px;font-weight:600;margin-top:8px;display:none;"></div>
            <div data-assistant-loading style="display:none;text-align:center;padding:8px;color:var(--muted);font-size:12px;font-weight:600;">L'assistant réfléchit…</div>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/agent.js'])
@endpush
@endsection
