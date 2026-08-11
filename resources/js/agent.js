document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    };

    // ========== TICKET DETAIL PAGE ==========
    const ticketEl = document.querySelector('[data-ticket-id]');
    if (ticketEl) {
        initTicketDetail(ticketEl.dataset.ticketId);
    }

    // ========== ASSISTANT PAGE ==========
    const assistantRoot = document.querySelector('[data-assistant-root]');
    if (assistantRoot) {
        initAssistant();
    }

    // ========== KNOWLEDGE BASE PAGE ==========
    const kbRoot = document.querySelector('[data-kb-articles-grid]');
    if (kbRoot) {
        initKnowledgeBase();
    }

    // ============================================================
    // TICKET DETAIL
    // ============================================================
    function initTicketDetail(ticketId) {
        // MESSAGE
        const messageInput = document.querySelector('[data-message-input]');
        const sendMessageBtn = document.querySelector('[data-send-message-btn]');
        const messagesList = document.querySelector('[data-messages-list]');
        const messageError = document.querySelector('[data-message-error]');

        if (sendMessageBtn && messageInput) {
            sendMessageBtn.addEventListener('click', sendMessage);
            messageInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        async function sendMessage() {
            const contenu = messageInput.value.trim();
            if (!contenu) return;

            if (messageError) messageError.style.display = 'none';
            sendMessageBtn.disabled = true;

            try {
                const res = await fetch('/api/v1/tickets/' + ticketId + '/messages', {
                    method: 'POST',
                    credentials: 'include',
                    headers: headers,
                    body: JSON.stringify({ contenu }),
                });

                const data = await res.json();

                if (res.ok) {
                    location.reload();
                } else {
                    if (messageError) showError(messageError, data.message || 'Erreur lors de l\'envoi.');
                }
            } catch (err) {
                if (messageError) showError(messageError, 'Erreur réseau.');
            } finally {
                sendMessageBtn.disabled = false;
            }
        }

        // CHANGE STATUS
        const statutSelect = document.querySelector('[data-statut-select]');
        const changeStatusBtn = document.querySelector('[data-change-status-btn]');

        if (changeStatusBtn && statutSelect) {
            changeStatusBtn.addEventListener('click', async function () {
                const statut = statutSelect.value;
                changeStatusBtn.disabled = true;

                try {
                    const res = await fetch('/api/v1/tickets/' + ticketId + '/statut', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: headers,
                        body: JSON.stringify({ statut }),
                    });

                    if (res.ok) {
                        location.reload();
                    } else {
                        const data = await res.json();
                        alert(data.message || 'Erreur lors du changement de statut.');
                    }
                } catch (err) {
                    alert('Erreur réseau.');
                } finally {
                    changeStatusBtn.disabled = false;
                }
            });
        }

        // AI ANALYSIS
        const analyzeBtn = document.querySelector('[data-analyze-btn]');
        const relaunchBtn = document.querySelector('[data-relaunch-analyze-btn]');
        const aiDone = document.querySelector('[data-ai-done]');
        const aiIdle = document.querySelector('[data-ai-idle]');
        const aiLoading = document.querySelector('[data-ai-loading]');
        const aiError = document.querySelector('[data-ai-error]');

        if (analyzeBtn) {
            analyzeBtn.addEventListener('click', launchAnalysis);
        }
        if (relaunchBtn) {
            relaunchBtn.addEventListener('click', launchAnalysis);
        }

        function showAiLoading() {
            if (aiLoading) aiLoading.style.display = 'block';
            if (aiDone) aiDone.style.display = 'none';
            if (aiIdle) aiIdle.style.display = 'none';
        }

        function hideAiLoading() {
            if (aiLoading) aiLoading.style.display = 'none';
            if (aiDone) aiDone.style.display = '';
            if (aiIdle) aiIdle.style.display = '';
        }

        async function launchAnalysis() {
            if (aiError) aiError.style.display = 'none';
            showAiLoading();
            if (analyzeBtn) analyzeBtn.disabled = true;
            if (relaunchBtn) relaunchBtn.disabled = true;

            try {
                const res = await fetch('/api/v1/tickets/' + ticketId + '/ai/analyze', {
                    method: 'POST',
                    credentials: 'include',
                    headers: headers,
                });

                if (res.status === 202) {
                    pollAnalysis();
                } else {
                    const data = await res.json();
                    if (aiError) showError(aiError, data.message || 'Erreur lors du lancement.');
                    hideAiLoading();
                    if (analyzeBtn) analyzeBtn.disabled = false;
                    if (relaunchBtn) relaunchBtn.disabled = false;
                }
            } catch (err) {
                if (aiError) showError(aiError, 'Erreur réseau.');
                hideAiLoading();
                if (analyzeBtn) analyzeBtn.disabled = false;
                if (relaunchBtn) relaunchBtn.disabled = false;
            }
        }

        function pollAnalysis(attempts) {
            attempts = attempts || 0;
            if (attempts >= 15) {
                if (aiError) showError(aiError, 'Analyse trop longue. Rechargez la page plus tard.');
                hideAiLoading();
                if (analyzeBtn) analyzeBtn.disabled = false;
                if (relaunchBtn) relaunchBtn.disabled = false;
                return;
            }

            setTimeout(async function () {
                try {
                    const res = await fetch('/api/v1/tickets/' + ticketId + '/ai/analysis', {
                        credentials: 'include',
                        headers: { 'Accept': 'application/json' },
                    });

                    if (res.ok) {
                        location.reload();
                    } else if (res.status === 404) {
                        pollAnalysis(attempts + 1);
                    } else {
                        if (aiError) showError(aiError, 'Erreur lors du polling.');
                        hideAiLoading();
                        if (analyzeBtn) analyzeBtn.disabled = false;
                        if (relaunchBtn) relaunchBtn.disabled = false;
                    }
                } catch (err) {
                    pollAnalysis(attempts + 1);
                }
            }, 2000);
        }

        // AI DRAFT - Insert into reply
        const insertDraftBtn = document.querySelector('[data-insert-draft-btn]');
        const aiDraft = document.querySelector('[data-ai-draft]');

        if (insertDraftBtn && aiDraft && messageInput) {
            insertDraftBtn.addEventListener('click', function () {
                const draft = aiDraft.textContent.trim();
                const hasExisting = messageInput.value.trim().length > 0;

                if (hasExisting && !confirm('Le champ de réponse contient déjà du texte. Remplacer par le brouillon IA ?')) {
                    return;
                }

                messageInput.value = draft;
                messageInput.focus();
                const banner = document.querySelector('[data-ai-draft-banner]');
                if (banner) banner.style.display = 'flex';
            });
        }
    }

    // ============================================================
    // ASSISTANT PAGE
    // ============================================================
    function initAssistant() {
        const msgContainer = document.querySelector('[data-assistant-messages]');
        const emptyState = document.querySelector('[data-assistant-empty]');
        const input = document.querySelector('[data-assistant-input]');
        const sendBtn = document.querySelector('[data-assistant-send]');
        const errorEl = document.querySelector('[data-assistant-error]');
        const loadingEl = document.querySelector('[data-assistant-loading]');
        const ticketSelect = document.querySelector('[data-assistant-ticket-select]');
        const suggestionsEl = document.querySelector('[data-assistant-suggestions]');

        let currentTicketId = ticketSelect ? ticketSelect.value : null;
        let conversationId = null;

        if (ticketSelect) {
            ticketSelect.addEventListener('change', function () {
                currentTicketId = this.value || null;
                conversationId = null;
                clearMessages();
                if (currentTicketId) {
                    loadLatestConversation(currentTicketId);
                }
            });
        }

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
                    var question = btn.dataset.question;
                    if (currentTicketId) {
                        input.value = question;
                        sendMessage();
                    } else if (question) {
                        input.value = question;
                        input.focus();
                    }
                }
            });
        }

        async function sendMessage() {
            var message = input.value.trim();
            if (!message) return;
            if (!currentTicketId) {
                showError(errorEl, 'Sélectionnez d\'abord un ticket.');
                return;
            }

            errorEl.style.display = 'none';
            loadingEl.style.display = 'block';
            sendBtn.disabled = true;

            appendAssistantMessage('user', message);
            input.value = '';

            var body = { message: message };
            if (conversationId) body.conversation_id = conversationId;

            try {
                var res = await fetch('/api/v1/tickets/' + currentTicketId + '/ai/chat', {
                    method: 'POST',
                    credentials: 'include',
                    headers: headers,
                    body: JSON.stringify(body),
                });

                if (res.status === 202) {
                    pollAssistantResponse();
                } else {
                    var data = await res.json();
                    showError(errorEl, errorMessageFrom(data, 'L\'assistant IA n\'a pas pu répondre. Réessayez.'));
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
                        var latestRes = await fetch('/api/v1/tickets/' + currentTicketId + '/ai/chat/latest', {
                            credentials: 'include',
                            headers: { 'Accept': 'application/json' },
                        });
                        if (latestRes.ok) {
                            var latestData = await latestRes.json();
                            if (latestData.available && latestData.conversation) {
                                conversationId = latestData.conversation.id;
                                var histRes = await fetch('/api/v1/tickets/' + currentTicketId + '/ai/chat/' + conversationId, {
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

                    var res = await fetch('/api/v1/tickets/' + currentTicketId + '/ai/chat/' + conversationId, {
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

            bubble.innerHTML = renderMessageMarkup(content) + timeHtml;
            div.appendChild(bubble);
            msgContainer.appendChild(div);
            msgContainer.scrollTop = msgContainer.scrollHeight;
        }

        function clearMessages() {
            var msgs = msgContainer.querySelectorAll('[data-assistant-msg]');
            msgs.forEach(function (el) { el.remove(); });
            if (emptyState) emptyState.style.display = 'block';
        }

        async function loadLatestConversation(ticketId) {
            try {
                var res = await fetch('/api/v1/tickets/' + ticketId + '/ai/chat/latest', {
                    credentials: 'include',
                    headers: { 'Accept': 'application/json' },
                });
                if (res.ok) {
                    var data = await res.json();
                    if (data.available && data.conversation) {
                        conversationId = data.conversation.id;
                        var histRes = await fetch('/api/v1/tickets/' + ticketId + '/ai/chat/' + conversationId, {
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

        if (currentTicketId) {
            loadLatestConversation(currentTicketId);
        }
    }

    // ========== KNOWLEDGE BASE PAGE ==========
    function initKnowledgeBase() {
        const grid = document.querySelector('[data-kb-articles-grid]');
        const categoryFilter = document.querySelector('[data-kb-category-filter]');
        const searchInput = document.querySelector('[data-kb-search]');

        if (!grid) return;

        let debounceTimer = null;

        function applyFilters() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                var params = new URLSearchParams(window.location.search);
                var category = categoryFilter ? categoryFilter.value : '';
                var search = searchInput ? searchInput.value.trim() : '';

                if (category) {
                    params.set('category', category);
                } else {
                    params.delete('category');
                }

                if (search) {
                    params.set('search', search);
                } else {
                    params.delete('search');
                }

                // Reset page to 1 when filters change
                params.delete('page');

                window.location.search = params.toString();
            }, 300);
        }

        if (categoryFilter) {
            categoryFilter.addEventListener('change', applyFilters);
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
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
        var escaped = escapeHtml(content || '');
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
});
