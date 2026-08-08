<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HelpDesk AI — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
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
            --side: #ffffff;
            --accent: #7c3aed;
            --accent2: #5b21b6;
            --gradav: linear-gradient(135deg, #a78bfa, #7c3aed);
            --track: #eeecf7;
            --shadow: 0 1px 2px rgba(20,10,50,.05), 0 10px 30px rgba(20,10,50,.05);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: none; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes grow {
            from { transform: scaleY(0); }
            to { transform: scaleY(1); }
        }
        @keyframes draw {
            to { stroke-dashoffset: 0; }
        }
        @keyframes widen {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }
        @keyframes pop {
            0% { opacity: 0; transform: scale(.9); }
            60% { transform: scale(1.02); }
            100% { opacity: 1; transform: scale(1); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }
        @keyframes ring {
            0% { box-shadow: 0 0 0 0 rgba(124,58,237,.35); }
            100% { box-shadow: 0 0 0 14px rgba(124,58,237,0); }
        }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 20px;
            border: 3px solid transparent;
            background-clip: padding-box;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div style="display:flex;min-height:100vh;">
        @include('components.admin-sidebar')

        <main style="flex:1;min-width:0;display:flex;flex-direction:column;height:100vh;overflow:hidden;">
            <header style="display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 30px;border-bottom:1px solid var(--border);flex-shrink:0;background:var(--surface);">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;color:var(--muted);font-weight:600;margin-bottom:2px;">@yield('breadcrumb', 'Administration')</div>
                    <h1 style="font-size:21px;font-weight:800;color:var(--text);margin:0;letter-spacing:-.4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">@yield('pageTitle', 'Dashboard')</h1>
                </div>
                <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;min-width:0;">
                    <div style="position:relative;display:flex;align-items:center;flex:1;min-width:0;max-width:250px;">
                        <svg style="position:absolute;left:12px;color:var(--muted);" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4-4"></path></svg>
                        <input placeholder="Rechercher un ticket, un article…" style="width:100%;min-width:0;padding:10px 14px 10px 36px;border-radius:11px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:.15s;">
                    </div>
                    <div style="position:relative;">
                        <button style="width:40px;height:40px;border-radius:11px;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;display:grid;place-items:center;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"></path></svg>
                            <span style="position:absolute;top:8px;right:9px;width:7px;height:7px;border-radius:50%;background:#f43f5e;border:2px solid var(--surface);"></span>
                        </button>
                    </div>
                </div>
            </header>

            <div style="flex:1;overflow-y:auto;padding:26px 30px;">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
