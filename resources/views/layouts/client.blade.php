<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HelpDesk AI — Client — @yield('title', 'Mon espace')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        [data-theme="light"] {
            --bg: #f4f4fb; --surface: #ffffff; --surface2: #faf9ff; --border: #ecebf5;
            --text: #191430; --text2: #6c6885; --muted: #a29fb5;
            --primsoft: #f4effe; --side: #ffffff; --track: #eeecf7;
            --shadow: 0 1px 2px rgba(20,10,50,.05), 0 10px 30px rgba(20,10,50,.05);
        }
        [data-theme="dark"] {
            --bg: #0c0a17; --surface: #151127; --surface2: #1b1734; --border: #282343;
            --text: #f3f1fb; --text2: #a5a2bd; --muted: #726e90;
            --primsoft: #221c40; --side: #100c20; --track: #221d3d;
            --shadow: 0 1px 2px rgba(0,0,0,.5), 0 12px 34px rgba(0,0,0,.4);
        }
        :root {
            --accent: #7c3aed;
            --accent2: #5b21b6;
            --gradav: linear-gradient(135deg, #a78bfa, #7c3aed);
        }

        @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        @keyframes pop { 0% { opacity:0; transform:scale(.9); } 60% { transform:scale(1.02); } 100% { opacity:1; transform:scale(1); } }
        @keyframes spin { to { transform:rotate(360deg); } }
        @keyframes float { 0%, 100% { transform:translateY(0); } 50% { transform:translateY(-7px); } }
        @keyframes ring { 0% { box-shadow:0 0 0 0 rgba(124,58,237,.35); } 100% { box-shadow:0 0 0 14px rgba(124,58,237,0); } }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-thumb {
            background: var(--border); border-radius: 20px;
            border: 3px solid transparent; background-clip: padding-box;
        }

        a { color: var(--accent); text-decoration: none; }
        a:hover { color: var(--accent2); }
    </style>
    @stack('styles')
</head>
<body style="background:var(--bg);color:var(--text);display:flex;min-height:100vh;">
    @include('components.client-sidebar')

    <main style="flex:1;min-width:0;display:flex;flex-direction:column;height:100vh;overflow:hidden;">
        <header style="display:flex;justify-content:space-between;align-items:center;gap:16px;padding:16px 30px;border-bottom:1px solid var(--border);flex-shrink:0;background:var(--surface);">
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:var(--muted);font-weight:600;margin-bottom:2px;">@yield('breadcrumb', 'Mon espace')</div>
                <h1 style="font-size:21px;font-weight:800;color:var(--text);margin:0;letter-spacing:-.4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">@yield('pageTitle', 'Mon espace')</h1>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;min-width:0;">
                <button id="theme-toggle" title="Basculer le thème" style="width:40px;height:40px;border-radius:11px;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;display:grid;place-items:center;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                    <svg id="theme-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"></path></svg>
                </button>
                <x-client-notifications />
            </div>
        </header>

        <div style="flex:1;overflow-y:auto;padding:26px 30px;">
            @yield('content')
        </div>
    </main>

    @stack('modals')

    <script>
        // Theme toggle with localStorage persistence
        document.addEventListener('DOMContentLoaded', function() {
            var html = document.documentElement;
            var savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                html.setAttribute('data-theme', savedTheme);
                updateThemeIcon(savedTheme);
            }

            var themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    var current = html.getAttribute('data-theme');
                    var next = current === 'light' ? 'dark' : 'light';
                    html.setAttribute('data-theme', next);
                    localStorage.setItem('theme', next);
                    updateThemeIcon(next);
                });
            }

            // Notification dropdown
            var notifToggle = document.getElementById('notif-toggle');
            var notifDropdown = document.getElementById('notif-dropdown');
            if (notifToggle && notifDropdown) {
                notifToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notifDropdown.style.display = notifDropdown.style.display === 'none' ? 'block' : 'none';
                });
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('#notif-toggle') && !e.target.closest('#notif-dropdown')) {
                        notifDropdown.style.display = 'none';
                    }
                });
            }
        });

        function updateThemeIcon(theme) {
            var icon = document.getElementById('theme-icon');
            if (!icon) return;
            if (theme === 'dark') {
                icon.innerHTML = '<circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M5 5l1.4 1.4M17.6 17.6L19 19M3 12h2M19 12h2M5 19l1.4-1.4M17.6 6.4L19 5"></path>';
            } else {
                icon.innerHTML = '<path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"></path>';
            }
        }
    </script>

    @stack('scripts')
</body>
</html>