(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
    };

    document.addEventListener('DOMContentLoaded', function () {
        initClientAssistant();
    });

    // ========== ASSISTANT PAGE ==========
    function initClientAssistant() {
        const root = document.querySelector('[data-client-assistant-root]');
        if (!root) return;

        const msgContainer = root.querySelector('[data-assistant-messages]');
        const emptyState = root.querySelector('[data-assistant-empty]');
        const input = root.querySelector('[data-assistant-input]');
        const sendBtn = root.querySelector('[data-assistant-send]');
        const errorEl = root.querySelector('[data-assistant-error]');
        const loadingEl = root.querySelector('[data-assistant-loading]');
        const suggestionsEl = root.querySelector('[data-assistant-suggestions]');

        let conversationId = null;

        if (sendBtn && input) {
            sendBtn.addEventListener('click', sendMessage);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        if (suggestionsEl) {
            suggestionsEl.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-suggestion-btn]');
                if (btn) {
                    input.value = btn.dataset.question;
                    sendMessage();
                }
            });
        }

        async function sendMessage() {
            var message = input.value.trim();
            if (!message) return;

            errorEl.style.display = 'none';
            loadingEl.style.display = 'block';
            sendBtn.disabled = true;

            appendAssistantMessage('user', message);
            input.value = '';

            var body = { message: message };
            if (conversationId) body.conversation_id = conversationId;

            try {
                var res = await fetch('/api/v1/client/ai/chat', {
                    method: 'POST',
                    credentials: 'include',
                    headers: headers,
                    body: JSON.stringify(body),
                });

                if (res.status === 202) {
                    pollAssistantResponse();
                } else {
                    var data = await res.json();
                    showError(errorEl, errorMessageFrom(data, "L'assistant IA n'a pas pu répondre. Réessayez."));
                    loadingEl.style.display = 'none';
                    sendBtn.disabled = false;
                }
            } catch (err) {
                showError(errorEl, 'Erreur réseau.');
                loadingEl.style.display = 'none';
                sendBtn.disabled = false;
            }
        }

        function pollAssistantResponse(attempts) {
            attempts = attempts || 0;
            if (attempts >= 20) {
                showError(errorEl, 'La réponse prend plus de temps que prévu. Réessayez.');
                loadingEl.style.display = 'none';
                sendBtn.disabled = false;
                return;
            }

            setTimeout(async function () {
                try {
                    if (!conversationId) {
                        var latestRes = await fetch('/api/v1/client/ai/chat/latest', {
                            credentials: 'include',
                            headers: { 'Accept': 'application/json' },
                        });
                        if (latestRes.ok) {
                            var latestData = await latestRes.json();
                            if (latestData.available && latestData.conversation) {
                                conversationId = latestData.conversation.id;
                                var histRes = await fetch('/api/v1/client/ai/chat/' + conversationId, {
                                    credentials: 'include',
                                    headers: { 'Accept': 'application/json' },
                                });
                                if (histRes.ok) {
                                    var histData = await histRes.json();
                                    var msgs = (histData.data && histData.data.messages) || [];
                                    if (msgs.length > 0) {
                                        clearMessages();
                                        msgs.forEach(function (msg) {
                                            appendAssistantMessage(msg.role, msg.content, msg.created_at);
                                        });
                                        loadingEl.style.display = 'none';
                                        sendBtn.disabled = false;
                                        return;
                                    }
                                }
                            }
                        }
                        pollAssistantResponse(attempts + 1);
                        return;
                    }

                    var res = await fetch('/api/v1/client/ai/chat/' + conversationId, {
                        credentials: 'include',
                        headers: { 'Accept': 'application/json' },
                    });

                    if (res.ok) {
                        var data = await res.json();
                        var msgs = (data.data && data.data.messages) || [];
                        var existingMsgs = msgContainer.querySelectorAll('[data-assistant-msg]');
                        if (msgs.length > existingMsgs.length) {
                            clearMessages();
                            msgs.forEach(function (msg) {
                                appendAssistantMessage(msg.role, msg.content, msg.created_at);
                            });
                            loadingEl.style.display = 'none';
                            sendBtn.disabled = false;
                        } else {
                            pollAssistantResponse(attempts + 1);
                        }
                    } else {
                        pollAssistantResponse(attempts + 1);
                    }
                } catch (err) {
                    pollAssistantResponse(attempts + 1);
                }
            }, 2000);
        }

        function appendAssistantMessage(role, content, createdAt) {
            if (emptyState) emptyState.style.display = 'none';
            var isAI = role === 'assistant' || role === 'ai';
            var div = document.createElement('div');
            div.setAttribute('data-assistant-msg', '');
            div.style.cssText = 'display:flex;gap:11px;' + (isAI ? '' : 'flex-direction:row-reverse;');

            if (isAI) {
                var avatar = document.createElement('div');
                avatar.style.cssText = 'width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;color:#fff;flex-shrink:0;';
                avatar.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4z"></path></svg>';
                div.appendChild(avatar);
            }

            var bubble = document.createElement('div');
            bubble.style.cssText = 'max-width:76%;padding:13px 16px;border-radius:16px;font-size:13.5px;line-height:1.55;'
                + (isAI
                    ? 'background:var(--surface2);border:1px solid var(--border);color:var(--text);border-top-left-radius:5px;'
                    : 'background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border-top-right-radius:5px;');

            var timeHtml = createdAt
                ? '<div style="font-size:10px;margin-top:6px;opacity:.6;">' + new Date(createdAt).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) + '</div>'
                : '';

            var sourceHtml = isAI && content && typeof content === 'object' ? '' : '';

            bubble.innerHTML = renderMessageMarkup(content) + sourceHtml + timeHtml;
            div.appendChild(bubble);
            msgContainer.appendChild(div);
            msgContainer.scrollTop = msgContainer.scrollHeight;
        }

        function clearMessages() {
            var msgs = msgContainer.querySelectorAll('[data-assistant-msg]');
            msgs.forEach(function (el) { el.remove(); });
            if (emptyState) emptyState.style.display = 'block';
        }

        // Load the most recent conversation if one already exists.
        loadLatestConversation();
        async function loadLatestConversation() {
            try {
                var res = await fetch('/api/v1/client/ai/chat/latest', {
                    credentials: 'include',
                    headers: { 'Accept': 'application/json' },
                });
                if (res.ok) {
                    var data = await res.json();
                    if (data.available && data.conversation) {
                        conversationId = data.conversation.id;
                        var histRes = await fetch('/api/v1/client/ai/chat/' + conversationId, {
                            credentials: 'include',
                            headers: { 'Accept': 'application/json' },
                        });
                        if (histRes.ok) {
                            var histData = await histRes.json();
                            var msgs = (histData.data && histData.data.messages) || [];
                            if (msgs.length > 0) {
                                clearMessages();
                                msgs.forEach(function (msg) {
                                    appendAssistantMessage(msg.role, msg.content, msg.created_at);
                                });
                            }
                        }
                    }
                }
            } catch (err) { /* no conversation yet */ }
        }
    }

    // ========== HELPERS ==========
    function showError(el, msg) {
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function errorMessageFrom(data, fallback) {
        if (!data) return fallback;
        if (data.errors && data.errors.message) {
            var first = Array.isArray(data.errors.message) ? data.errors.message[0] : data.errors.message;
            if (first) return first;
        }
        return data.message || fallback;
    }

    function renderInline(text) {
        return text
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/\*([^*]+)\*/g, '<em>$1</em>')
            .replace(/`([^`]+)`/g, '<code style="background:rgba(0,0,0,.06);padding:1px 5px;border-radius:5px;font-size:12px;">$1</code>');
    }

    function renderMessageMarkup(content) {
        var value = content;
        if (value && typeof value === 'object') {
            value = value.text || value.content || JSON.stringify(value);
        }
        var escaped = escapeHtml(value || '');
        var lines = escaped.split(/\n/);
        var html = '';
        var inUl = false;
        var inOl = false;

        function closeLists() {
            if (inUl) { html += '</ul>'; inUl = false; }
            if (inOl) { html += '</ol>'; inOl = false; }
        }

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i].trim();
            if (line === '') { closeLists(); html += '<br>'; continue; }

            var heading = line.match(/^#{1,6}\s+(.*)$/);
            if (heading) {
                closeLists();
                html += '<div style="font-weight:800;margin:6px 0 2px;">' + renderInline(heading[1]) + '</div>';
                continue;
            }

            var ulMatch = line.match(/^[-*]\s+/);
            var olMatch = line.match(/^\d+[.)]\s+/);
            if (ulMatch) {
                if (!inUl) { closeLists(); html += '<ul style="margin:4px 0;padding-left:20px;">'; inUl = true; }
                html += '<li>' + renderInline(line.replace(/^[-*]\s+/, '')) + '</li>';
                continue;
            }
            if (olMatch) {
                if (!inOl) { closeLists(); html += '<ol style="margin:4px 0;padding-left:20px;">'; inOl = true; }
                html += '<li>' + renderInline(line.replace(/^\d+[.)]\s+/, '')) + '</li>';
                continue;
            }

            closeLists();
            html += '<div>' + renderInline(line) + '</div>';
        }
        closeLists();
        return html;
    }
})();