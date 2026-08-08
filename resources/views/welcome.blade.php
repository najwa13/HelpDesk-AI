<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HelpDesk AI — Support client intelligent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background: var(--bg);
            color: var(--text);
        }

        :root {
            --bg: #f4f4fb;
            --surface: #ffffff;
            --surface2: #faf9ff;
            --border: #ecebf5;
            --text: #191430;
            --text2: #6c6885;
            --muted: #a29fb5;
            --primsoft: #f4effe;
            --accent: #7c3aed;
            --accent2: #5b21b6;
            --track: #eeecf7;
            --shadow: 0 1px 2px rgba(20,10,50,.05), 0 10px 30px rgba(20,10,50,.05);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: none; }
        }
        @keyframes grow {
            from { transform: scaleY(0); }
            to { transform: scaleY(1); }
        }

        .section-title { font-size: 32px; font-weight: 800; letter-spacing: -.6px; color: var(--text); text-align: center; margin: 0 0 10px; }
        .section-sub { font-size: 15px; color: var(--text2); text-align: center; margin: 0 auto 48px; max-width: 560px; line-height: 1.6; }

        @media (max-width: 768px) {
            .hero-grid { grid-template-columns: 1fr !important; gap: 40px !important; }
            .hero-illustration { display: none !important; }
            .feat-grid, .role-grid { grid-template-columns: 1fr !important; }
            .steps-grid { grid-template-columns: 1fr !important; gap: 32px !important; }
            .nav-links { display: none !important; }
            .hero-actions, .cta-actions { flex-direction: column; }
            .hero-actions a, .hero-actions button,
            .cta-actions a, .cta-actions button,
            .hero-actions span, .cta-actions span { width: 100%; text-align: center; justify-content: center; }
            .section-title { font-size: 26px; }
            .footer-inner { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

{{-- NAVBAR --}}
<nav style="position:sticky;top:0;z-index:50;background:rgba(244,244,251,.85);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);border-bottom:1px solid var(--border);">
    <div style="max-width:1200px;margin:0 auto;padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;">
        <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;font-weight:800;font-size:17px;box-shadow:0 4px 12px -3px var(--accent);">H</div>
            <div style="font-weight:800;font-size:17px;color:var(--text);letter-spacing:-.3px;">HelpDesk<span style="opacity:.5;"> AI</span></div>
        </a>

        <div class="nav-links" style="display:flex;align-items:center;gap:32px;font-size:13.5px;font-weight:600;">
            <a href="#features" style="color:var(--text2);text-decoration:none;transition:.15s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text2)'">Fonctionnalités</a>
            <a href="#how" style="color:var(--text2);text-decoration:none;transition:.15s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text2)'">Comment ça marche</a>
            <a href="#roles" style="color:var(--text2);text-decoration:none;transition:.15s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text2)'">Rôles</a>
        </div>

        <div style="display:flex;align-items:center;gap:10px;">
            @guest
                <a href="{{ route('login') }}" style="padding:9px 18px;border-radius:10px;font-size:13.5px;font-weight:700;color:var(--text);text-decoration:none;border:1px solid var(--border);background:var(--surface);transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">Se connecter</a>
                <a href="{{ route('register') }}" style="padding:9px 18px;border-radius:10px;font-size:13.5px;font-weight:700;color:#fff;text-decoration:none;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 4px 14px -4px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Créer un compte</a>
            @else
                @php $isAdmin = auth()->user()->role->value === 'admin'; @endphp
                @if($isAdmin)
                    <a href="/admin/dashboard" style="padding:9px 18px;border-radius:10px;font-size:13.5px;font-weight:700;color:#fff;text-decoration:none;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 4px 14px -4px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'">Accéder à mon espace</a>
                @else
                    <span style="padding:9px 18px;border-radius:10px;font-size:13.5px;font-weight:600;color:var(--muted);background:var(--track);cursor:default;opacity:.7;">Espace bientôt disponible</span>
                @endif
            @endguest
        </div>
    </div>
</nav>

{{-- HERO --}}
<section style="max-width:1200px;margin:0 auto;padding:72px 32px 80px;">
    <div class="hero-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
        <div style="animation:fadeUp .5s both;">
            <div style="display:inline-flex;align-items:center;gap:8px;background:var(--primsoft);padding:7px 16px;border-radius:100px;font-size:12.5px;font-weight:700;color:var(--accent);margin-bottom:22px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"/></svg>
                Support augmenté par l'IA
            </div>
            <h1 style="font-size:44px;line-height:1.1;letter-spacing:-1.2px;font-weight:800;margin:0 0 18px;color:var(--text);">Un support plus rapide, plus clair, assisté par l'IA.</h1>
            <p style="font-size:16px;line-height:1.7;color:var(--text2);margin:0 0 32px;max-width:480px;">HelpDesk AI centralise les tickets, la base de connaissances et l'assistance IA pour aider clients et agents à résoudre les demandes plus efficacement.</p>

            <div class="hero-actions" style="display:flex;gap:12px;flex-wrap:wrap;">
                @guest
                    <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;color:#fff;text-decoration:none;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 8px 24px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                        Créer un compte
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;color:var(--text);text-decoration:none;background:var(--surface);border:1px solid var(--border);transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">Se connecter</a>
                @else
                    @php $isAdmin = auth()->user()->role->value === 'admin'; @endphp
                    @if($isAdmin)
                        <a href="/admin/dashboard" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;color:#fff;text-decoration:none;background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 8px 24px -6px var(--accent);transition:.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                            Accéder à mon espace
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;color:var(--muted);background:var(--track);cursor:default;opacity:.7;">Espace bientôt disponible</span>
                    @endif
                @endguest
            </div>
        </div>

        <div class="hero-illustration" style="animation:fadeUp .6s .15s both;">
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;">
                <div style="display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--border);">
                    <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;font-weight:800;font-size:12px;">H</div>
                    <div style="flex:1;height:8px;border-radius:20px;background:var(--track);max-width:120px;"></div>
                    <div style="display:flex;gap:6px;">
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--border);"></div>
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--border);"></div>
                    </div>
                </div>
                <div style="display:flex;min-height:240px;">
                    <div style="width:56px;border-right:1px solid var(--border);padding:12px 8px;display:flex;flex-direction:column;gap:8px;align-items:center;">
                        <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent2));opacity:.9;"></div>
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--track);"></div>
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--track);"></div>
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--track);"></div>
                    </div>
                    <div style="flex:1;padding:14px;display:flex;flex-direction:column;gap:10px;">
                        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;">
                            @foreach([1,2,3,4] as $i)
                                <div style="background:{{ $i === 1 ? 'linear-gradient(135deg,var(--accent),var(--accent2))' : 'var(--surface2)' }};border:1px solid var(--border);border-radius:8px;padding:8px 6px;">
                                    <div style="height:5px;border-radius:4px;background:{{ $i === 1 ? 'rgba(255,255,255,.3)' : 'var(--track)' }};width:60%;margin-bottom:5px;"></div>
                                    <div style="height:8px;border-radius:4px;background:{{ $i === 1 ? 'rgba(255,255,255,.5)' : 'var(--border)' }};width:40%;"></div>
                                </div>
                            @endforeach
                        </div>
                        <div style="flex:1;background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:10px;display:flex;align-items:flex-end;gap:5px;">
                            @foreach([35,55,40,65,50,75,60] as $i => $h)
                                <div style="flex:1;height:{{ $h }}%;border-radius:4px 4px 2px 2px;background:{{ $i === 5 ? 'linear-gradient(180deg,var(--accent),var(--accent2))' : 'var(--primsoft)' }};transform-origin:bottom;animation:grow .8s {{ $i * 0.07 }}s cubic-bezier(.34,1.2,.4,1) both;"></div>
                            @endforeach
                        </div>
                        <div style="background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:10px;padding:10px 12px;display:flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.9)" stroke-width="2" stroke-linecap="round"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"/></svg>
                            <div style="flex:1;">
                                <div style="height:5px;border-radius:4px;background:rgba(255,255,255,.3);width:70%;margin-bottom:4px;"></div>
                                <div style="height:5px;border-radius:4px;background:rgba(255,255,255,.2);width:50%;"></div>
                            </div>
                            <div style="width:24px;height:24px;border-radius:6px;background:rgba(255,255,255,.2);display:grid;place-items:center;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FONCTIONNALITÉS --}}
<section id="features" style="max-width:1200px;margin:0 auto;padding:0 32px 96px;">
    <h2 class="section-title">Tout ce qu'il faut pour un support efficace</h2>
    <p class="section-sub">Une plateforme complète qui combine gestion de tickets, base de connaissances et assistance par intelligence artificielle.</p>

    <div class="feat-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow);transition:.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(20,10,50,.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
            <div style="width:48px;height:48px;border-radius:13px;background:var(--primsoft);display:grid;place-items:center;margin-bottom:18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4z"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 8px;">Tickets centralisés</h3>
            <p style="font-size:14px;color:var(--text2);line-height:1.6;margin:0;">Créez, suivez et gérez tous vos tickets de support en un seul endroit. Statuts, priorités et historique des échanges visibles en temps réel.</p>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow);transition:.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(20,10,50,.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
            <div style="width:48px;height:48px;border-radius:13px;background:var(--primsoft);display:grid;place-items:center;margin-bottom:18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h11a2 2 0 012 2v14H7a2 2 0 01-2-2zM5 4v16M9 9h6M9 13h4"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 8px;">Base de connaissances</h3>
            <p style="font-size:14px;color:var(--text2);line-height:1.6;margin:0;">Recherche automatique de solutions. Les clients trouvent des réponses avant même de créer un ticket, réduisant le volume de demandes.</p>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow);transition:.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(20,10,50,.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
            <div style="width:48px;height:48px;border-radius:13px;background:var(--primsoft);display:grid;place-items:center;margin-bottom:18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 8px;">Assistance IA</h3>
            <p style="font-size:14px;color:var(--text2);line-height:1.6;margin:0;">L'IA analyse chaque ticket et propose des brouillons de réponse aux agents. Assistant conversationnel pour guider clients et agents.</p>
        </div>
    </div>
</section>

{{-- COMMENT ÇA MARCHE --}}
<section id="how" style="background:var(--surface);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:96px 0;">
    <div style="max-width:1200px;margin:0 auto;padding:0 32px;">
        <h2 class="section-title">Comment ça marche</h2>
        <p class="section-sub">Un flux simple et efficace, de la demande du client à la résolution validée par un agent.</p>

        <div class="steps-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:40px;">
            <div style="text-align:center;">
                <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:inline-grid;place-items:center;color:#fff;font-weight:800;font-size:20px;margin-bottom:20px;box-shadow:0 6px 18px -4px var(--accent);">1</div>
                <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 8px;">Le client cherche une solution</h3>
                <p style="font-size:14px;color:var(--text2);line-height:1.6;margin:0;max-width:280px;margin-left:auto;margin-right:auto;">Le client décrit son problème. La base de connaissances est consultée automatiquement pour trouver une réponse existante.</p>
            </div>

            <div style="text-align:center;">
                <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:inline-grid;place-items:center;color:#fff;font-weight:800;font-size:20px;margin-bottom:20px;box-shadow:0 6px 18px -4px var(--accent);">2</div>
                <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 8px;">La KB propose une réponse</h3>
                <p style="font-size:14px;color:var(--text2);line-height:1.6;margin:0;max-width:280px;margin-left:auto;margin-right:auto;">Si une solution est trouvée dans la base de connaissances, elle est présentée au client instantanément, sans intervention humaine.</p>
            </div>

            <div style="text-align:center;">
                <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:inline-grid;place-items:center;color:#fff;font-weight:800;font-size:20px;margin-bottom:20px;box-shadow:0 6px 18px -4px var(--accent);">3</div>
                <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 8px;">Ticket traité avec assistance IA</h3>
                <p style="font-size:14px;color:var(--text2);line-height:1.6;margin:0;max-width:280px;margin-left:auto;margin-right:auto;">Sinon, un ticket est créé. L'IA analyse le problème et propose un brouillon de réponse que l'agent valide et envoie.</p>
            </div>
        </div>
    </div>
</section>

{{-- RÔLES --}}
<section id="roles" style="max-width:1200px;margin:0 auto;padding:96px 32px;">
    <h2 class="section-title">Un espace adapté à chaque rôle</h2>
    <p class="section-sub">HelpDesk AI s'adapte aux besoins de chaque utilisateur : client, agent ou administrateur.</p>

    <div class="role-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow);transition:.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(20,10,50,.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
            <div style="width:48px;height:48px;border-radius:13px;background:linear-gradient(135deg,#7c3aed,#a78bfa);display:grid;place-items:center;margin-bottom:18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 4px;">Client</h3>
            <div style="font-size:12px;font-weight:700;color:var(--accent);margin-bottom:12px;">Accès self-service</div>
            <ul style="list-style:none;padding:0;margin:0;font-size:14px;color:var(--text2);line-height:1.8;">
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Recherche dans la base de connaissances</li>
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Création et suivi de tickets</li>
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Assistant IA conversationnel</li>
            </ul>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow);transition:.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(20,10,50,.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
            <div style="width:48px;height:48px;border-radius:13px;background:linear-gradient(135deg,#f59e0b,#f97316);display:grid;place-items:center;margin-bottom:18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 4px;">Agent</h3>
            <div style="font-size:12px;font-weight:700;color:#f59e0b;margin-bottom:12px;">Traitement des tickets</div>
            <ul style="list-style:none;padding:0;margin:0;font-size:14px;color:var(--text2);line-height:1.8;">
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Traitement et réponse aux tickets</li>
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Analyse IA des demandes</li>
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Brouillons de réponse IA</li>
            </ul>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:28px;box-shadow:var(--shadow);transition:.2s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(20,10,50,.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='var(--shadow)'">
            <div style="width:48px;height:48px;border-radius:13px;background:linear-gradient(135deg,#10b981,#059669);display:grid;place-items:center;margin-bottom:18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 4px;">Admin</h3>
            <div style="font-size:12px;font-weight:700;color:#10b981;margin-bottom:12px;">Supervision totale</div>
            <ul style="list-style:none;padding:0;margin:0;font-size:14px;color:var(--text2);line-height:1.8;">
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Vue globale du support</li>
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Gestion des catégories et de la KB</li>
                <li style="display:flex;align-items:start;gap:8px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:3px;"><path d="M20 6L9 17l-5-5"/></svg>Statistiques et supervision IA</li>
            </ul>
        </div>
    </div>
</section>

{{-- CTA FINAL --}}
<section style="max-width:1200px;margin:0 auto;padding:0 32px 96px;">
    <div style="background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:20px;padding:56px 40px;text-align:center;color:#fff;box-shadow:0 12px 40px -10px var(--accent);">
        <h2 style="font-size:30px;font-weight:800;letter-spacing:-.6px;margin:0 0 10px;">Prêt à simplifier votre support ?</h2>
        <p style="font-size:15px;opacity:.9;margin:0 0 32px;max-width:440px;margin-left:auto;margin-right:auto;">Rejoignez HelpDesk AI et offrez à vos clients un support plus rapide, plus intelligent et plus efficace.</p>

        <div class="cta-actions" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            @guest
                <a href="{{ route('register') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;color:var(--accent);text-decoration:none;background:#fff;transition:.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    Créer un compte
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('login') }}" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;color:#fff;text-decoration:none;border:1px solid rgba(255,255,255,.3);transition:.15s;" onmouseover="this.style.borderColor='rgba(255,255,255,.6)'" onmouseout="this.style.borderColor='rgba(255,255,255,.3)'">Se connecter</a>
            @else
                @php $isAdmin = auth()->user()->role->value === 'admin'; @endphp
                @if($isAdmin)
                    <a href="/admin/dashboard" style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:700;color:var(--accent);text-decoration:none;background:#fff;transition:.15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                        Accéder à mon espace
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span style="display:inline-flex;align-items:center;gap:8px;padding:14px 28px;border-radius:12px;font-size:15px;font-weight:600;color:rgba(255,255,255,.5);border:1px solid rgba(255,255,255,.15);cursor:default;">Espace bientôt disponible</span>
                @endif
            @endguest
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer style="border-top:1px solid var(--border);padding:0;">
    <div class="footer-inner" style="max-width:1200px;margin:0 auto;padding:28px 32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;font-weight:800;font-size:13px;">H</div>
            <span style="font-weight:700;font-size:14px;color:var(--text);">HelpDesk AI</span>
            <span style="font-size:12px;color:var(--muted);">&copy; {{ date('Y') }}</span>
        </div>
        <div style="display:flex;gap:20px;font-size:13px;">
            <a href="{{ route('login') }}" style="color:var(--text2);text-decoration:none;font-weight:600;transition:.15s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text2)'">Connexion</a>
            <a href="{{ route('register') }}" style="color:var(--text2);text-decoration:none;font-weight:600;transition:.15s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--text2)'">Inscription</a>
        </div>
    </div>
</footer>

</body>
</html>
