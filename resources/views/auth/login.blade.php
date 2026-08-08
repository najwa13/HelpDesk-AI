<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — HelpDesk AI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100%; }
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
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }
    </style>
</head>
<body>
    <div style="width:100%;min-height:100vh;display:grid;grid-template-columns:1.05fr .95fr;">

        <div style="position:relative;overflow:hidden;background:linear-gradient(150deg,#7c3aed,#5b21b6);display:flex;flex-direction:column;justify-content:space-between;padding:56px 52px;color:#fff;">
            <div style="position:absolute;width:420px;height:420px;border-radius:50%;background:rgba(255,255,255,.12);top:-120px;right:-120px;animation:float 7s ease-in-out infinite;"></div>
            <div style="position:absolute;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.09);bottom:-80px;left:-40px;animation:float 9s ease-in-out infinite;"></div>

            <div style="display:flex;align-items:center;gap:12px;position:relative;">
                <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.2);display:grid;place-items:center;font-weight:800;font-size:20px;">H</div>
                <div style="font-weight:800;font-size:20px;letter-spacing:-.3px;">HelpDesk<span style="opacity:.7;"> AI</span></div>
            </div>

            <div style="position:relative;max-width:440px;">
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);padding:7px 14px;border-radius:100px;font-size:13px;font-weight:600;margin-bottom:22px;">Support augmenté par l'IA</div>
                <h1 style="font-size:38px;line-height:1.12;font-weight:800;margin:0 0 16px;letter-spacing:-1px;text-wrap:balance;">Le support client, résolu avant même d'ouvrir un ticket.</h1>
                <p style="font-size:16px;line-height:1.6;opacity:.9;margin:0;">Base de connaissances consultée automatiquement, IA consultative pour vos agents, réponses toujours validées par un humain.</p>
            </div>

            <div style="position:relative;display:flex;gap:28px;font-size:13px;opacity:.9;">
                <div><div style="font-size:24px;font-weight:800;">72%</div>résolution auto</div>
                <div><div style="font-size:24px;font-weight:800;">-40%</div>temps de traitement</div>
                <div><div style="font-size:24px;font-weight:800;">100%</div>réponses validées</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:center;padding:40px;background:var(--bg);">
            <div style="width:100%;max-width:380px;animation:fadeUp .5s both;">
                <h2 style="font-size:26px;font-weight:800;margin:0 0 6px;color:var(--text);letter-spacing:-.5px;">Connexion</h2>
                <p style="color:var(--text2);margin:0 0 28px;font-size:14px;">Accédez à votre espace de support.</p>

                @if ($errors->any())
                    <div style="padding:12px 14px;border-radius:12px;background:rgba(220,38,38,.08);border:1px solid rgba(220,38,38,.2);margin-bottom:18px;font-size:13px;color:#dc2626;font-weight:600;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Adresse e-mail</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        autofocus
                        placeholder="agent@helpdesk.ai"
                        style="width:100%;padding:13px 15px;border-radius:12px;border:1px solid {{ $errors->has('email') ? '#dc2626' : 'var(--border)' }};background:var(--surface);color:var(--text);font-size:14px;font-family:inherit;margin-bottom:18px;outline:none;transition:.15s;"
                    >

                    <label style="display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:7px;">Mot de passe</label>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••••"
                        style="width:100%;padding:13px 15px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:14px;font-family:inherit;margin-bottom:14px;outline:none;transition:.15s;"
                    >

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;font-size:13px;">
                        <label style="display:flex;align-items:center;gap:7px;color:var(--text2);cursor:pointer;">
                            <input type="checkbox" name="remember" value="1" style="accent-color:var(--accent);">
                            Se souvenir de moi
                        </label>
                    </div>

                    <button type="submit" style="width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;font-weight:700;font-size:15px;font-family:inherit;cursor:pointer;box-shadow:0 8px 20px -6px var(--accent);transition:transform .15s,box-shadow .2s;">
                        Se connecter
                    </button>
                </form>

                <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text2);">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" style="color:var(--accent);font-weight:600;text-decoration:none;">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
