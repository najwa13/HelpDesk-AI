@php
    $clientUser = auth()->user();
    $clientNotifications = $clientUser->notifications()->latest()->limit(15)->get();
    $clientUnreadCount = $clientUser->unreadNotifications()->count();
@endphp

<div style="position:relative;">
    <button id="notif-toggle" style="width:40px;height:40px;border-radius:11px;border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;display:grid;place-items:center;position:relative;transition:.15s;" onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'" onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"></path></svg>
        <span id="notif-badge" style="display:{{ $clientUnreadCount > 0 ? 'grid' : 'none' }};position:absolute;top:5px;right:6px;min-width:17px;height:17px;border-radius:20px;background:#f43f5e;border:2px solid var(--surface);color:#fff;font-size:9px;font-weight:800;place-items:center;padding:0 3px;">{{ $clientUnreadCount > 9 ? '9+' : $clientUnreadCount }}</span>
    </button>
    <div id="notif-dropdown" style="display:none;position:absolute;top:48px;right:0;width:340px;background:var(--surface);border:1px solid var(--border);border-radius:15px;box-shadow:0 20px 50px -12px rgba(30,10,70,.3);z-index:60;overflow:hidden;animation:pop .2s both;">
        <div style="padding:13px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:800;font-size:13.5px;color:var(--text);">Notifications</span>
            @if($clientUnreadCount > 0)
                <form method="POST" action="{{ route('client.notifications.read-all') }}">
                    @csrf
                    <button type="submit" style="border:none;background:none;color:var(--accent);font-size:12px;font-weight:700;cursor:pointer;padding:2px;transition:.15s;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Tout marquer comme lu</button>
                </form>
            @endif
        </div>
        <div id="notif-list" style="max-height:340px;overflow-y:auto;">
            @forelse($clientNotifications as $notification)
                @php
                    $notifData = $notification->data;
                    $isUnread = $notification->read_at === null;
                    $notifTitle = $notifData['title'] ?? 'Nouvelle réponse';
                    $notifMessage = $notifData['message'] ?? '';
                    $notifTickettId = $notifData['ticket_id'] ?? null;
                    $notifTime = $notification->created_at
                        ? \Carbon\Carbon::parse($notification->created_at)->diffForHumans()
                        : '';
                @endphp
                <a href="{{ route('client.notifications.open', $notification) }}" style="display:flex;gap:12px;padding:13px 16px;border-bottom:1px solid var(--border);text-decoration:none;transition:.12s;{{ $isUnread ? 'background:var(--primsoft);' : '' }}" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='{{ $isUnread ? 'var(--primsoft)' : 'transparent' }}'">
                    <div style="width:34px;height:34px;border-radius:10px;background:{{ $isUnread ? 'linear-gradient(135deg,var(--accent),var(--accent2))' : 'var(--surface2)' }};display:grid;place-items:center;color:{{ $isUnread ? '#fff' : 'var(--muted)' }};flex-shrink:0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 012-2h12a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H6a2 2 0 01-2-2v-3a2 2 0 000-4zM12 5v14"></path></svg>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;gap:8px;">
                            <span style="font-weight:700;font-size:12.5px;color:var(--text);">{{ $notifTitle }}</span>
                            @if($isUnread)
                                <span style="width:7px;height:7px;border-radius:50%;background:var(--accent);flex-shrink:0;margin-top:4px;"></span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:var(--text2);line-height:1.45;margin-top:2px;">{{ $notifMessage }}</div>
                        @if($notifTickettId)
                            <div style="font-size:11px;color:var(--accent);font-weight:700;margin-top:3px;">Ticket #{{ $notifTickettId }}</div>
                        @endif
                        <div style="font-size:10.5px;color:var(--muted);margin-top:3px;">{{ $notifTime }}</div>
                    </div>
                </a>
            @empty
                <div style="text-align:center;padding:32px 16px;color:var(--muted);font-size:13px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:12px;opacity:.5;"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"></path></svg>
                    <div>Aucune notification pour le moment.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>