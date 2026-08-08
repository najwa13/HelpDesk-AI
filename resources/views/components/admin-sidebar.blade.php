@php
    $user = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 1));
    $currentRoute = request()->route()->getName();
@endphp

<aside style="width:262px;flex-shrink:0;background:var(--side);border-right:1px solid var(--border);display:flex;flex-direction:column;height:100vh;position:sticky;top:0;">
    <div style="padding:22px 22px 14px;display:flex;align-items:center;gap:11px;">
        <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;font-weight:800;font-size:18px;box-shadow:0 6px 14px -4px var(--accent);">H</div>
        <div>
            <div style="font-weight:800;font-size:17px;color:var(--text);letter-spacing:-.3px;line-height:1;">HelpDesk</div>
            <div style="font-size:11px;color:var(--accent);font-weight:700;letter-spacing:.5px;">AI STUDIO</div>
        </div>
    </div>

    <nav style="flex:1;overflow-y:auto;padding:6px 14px;">
        <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;padding:10px 12px 8px;">Navigation</div>

        @php
            $navItems = [
                [
                    'label' => 'Vue globale',
                    'route' => 'admin.dashboard',
                    'icon' => 'M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z',
                ],
                [
                    'label' => 'Tickets',
                    'route' => null,
                    'icon' => 'M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14',
                ],
                [
                    'label' => 'Agents & Catégories',
                    'route' => null,
                    'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75',
                ],
                [
                    'label' => 'Base de connaissances',
                    'route' => null,
                    'icon' => 'M5 4h11a2 2 0 012 2v14H7a2 2 0 01-2-2zM5 4v16M9 9h6M9 13h4',
                ],
                [
                    'label' => 'Assistant IA',
                    'route' => null,
                    'icon' => 'M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z',
                ],
            ];
        @endphp

        @foreach($navItems as $item)
            @php
                $isActive = $item['route'] && $currentRoute === $item['route'];
                $activeStyle = $isActive
                    ? 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border-color:transparent;box-shadow:0 4px 12px -3px var(--accent);'
                    : 'background:transparent;color:var(--text2);border-color:transparent;';
            @endphp
            <a href="{{ $item['route'] ? route($item['route']) : '#', }}"
               style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:11px;font-size:13.5px;font-weight:600;text-decoration:none;transition:.15s;{{ $activeStyle }}"
               onmouseover="if(!this.classList.contains('active')){this.style.background='var(--surface2)';this.style.color='var(--text)'}"
               onmouseout="if(!this.classList.contains('active')){this.style.background='transparent';this.style.color='var(--text2)'}"
               @if($isActive) class="active" @endif>
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                    <path d="{{ $item['icon'] }}"></path>
                </svg>
                <span style="flex:1;">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div style="padding:14px;border-top:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:11px;padding:9px;border-radius:12px;cursor:pointer;transition:.15s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
            <div style="width:38px;height:38px;border-radius:11px;background:var(--gradav);display:grid;place-items:center;color:#fff;font-weight:700;font-size:14px;">{{ $initials }}</div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:13px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $user->name }}</div>
                <div style="font-size:11px;color:var(--text2);">{{ $user->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" title="Déconnexion" style="border:none;background:none;color:var(--muted);cursor:pointer;padding:4px;display:grid;place-items:center;transition:.15s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"></path></svg>
                </button>
            </form>
        </div>
    </div>
</aside>
