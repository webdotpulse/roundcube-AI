/**
 * Gemini Executive Assistant for Roundcube
 *
 * Exclusively powered by Google Gemini.
 * Features automated email triage, executive briefings, action items,
 * and zero-click pre-crafted draft replies.
 */

if (window.rcmail) {
    rcmail.addEventListener('init', function() {
        lpai_detect_skin();
        var task = rcmail.env.task;
        var action = rcmail.env.action;

        // Ensure purple Gemini icon is installed in sidebar #taskmenu
        lpai_setup_sidebar_button();

        if (task === 'mail' && action === 'compose') {
            lpai_add_compose_button();
            lpai_check_pending_reply();
            lpai_init_smart_compose();
        }

        if (task === 'mail' && (action === 'show' || action === 'preview' || action === '' || action === 'mail')) {
            lpai_add_message_button();
            // Trigger Autonomous Executive Triage
            setTimeout(function() { lpai_init_executive_triage(); }, 350);
        }

        if (task === 'settings') {
            setTimeout(function() { if (window.lpai_init_admin) lpai_init_admin(); }, 200);
        }

        lpai_apply_server_prefs();
        lpai_restore_prefs();
        lpai_bind_events();
    });

    // In widescreen 3-pane mode (e.g. gmail_plus), listen for dynamic message preview loads
    rcmail.addEventListener('message_load', function() {
        lpai_detect_skin();
        setTimeout(function() {
            lpai_setup_sidebar_button();
            lpai_add_message_button();
            lpai_init_executive_triage();
        }, 200);
    });

    rcmail.addEventListener('responseafterpreview', function() {
        lpai_detect_skin();
        setTimeout(function() {
            lpai_setup_sidebar_button();
            lpai_add_message_button();
            lpai_init_executive_triage();
        }, 200);
    });
}

var lpai_current_action = null;
var lpai_last_result = null;
var lpai_undo_text = null;
var lpai_panel_context = 'compose';
var lpai_history = [];
var lpai_stream_controller = null;

var lpai_options = {
    model: 'gemini-3.8-flash',
    language: 'English',
    tone: 'professional'
};

// ========================================
// Security & HTML Sanitization
// ========================================
function lpai_escape_html(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ========================================
// SVG Icons
// ========================================
function lpai_icon(name) {
    var icons = {
        'sparkles': '<svg class="lpai-icon" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"/></svg>',
        'translate': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>',
        'fix': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
        'rewrite': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
        'subject': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M8 7h6"/><path d="M8 11h8"/></svg>',
        'summarize': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" x2="3" y1="6" y2="6"/><line x1="15" x2="3" y1="12" y2="12"/><line x1="17" x2="3" y1="18" y2="18"/></svg>',
        'scam': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>',
        'reply': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>',
        'chevron': '<svg class="lpai-icon" viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>',
        'copy': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>',
        'check': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        'refresh': '<svg class="lpai-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>',
    };
    return icons[name] || '';
}

// ========================================
// LocalStorage & Server Prefs
// ========================================
function lpai_save_prefs() {
    try {
        localStorage.setItem('lpai_prefs', JSON.stringify({
            model: lpai_options.model,
            language: lpai_options.language,
            tone: lpai_options.tone
        }));
    } catch (e) {}
}

function lpai_restore_prefs() {
    try {
        var saved = JSON.parse(localStorage.getItem('lpai_prefs'));
        if (saved) {
            if (saved.language) lpai_options.language = saved.language;
            if (saved.tone) lpai_options.tone = saved.tone;
            if (saved.model) lpai_options.model = saved.model;
        }
    } catch (e) {}
}

function lpai_apply_server_prefs() {
    var sp = rcmail.env.lpai_user_prefs || {};
    if (sp.language && !localStorage.getItem('lpai_prefs')) lpai_options.language = sp.language;
    if (sp.tone && !localStorage.getItem('lpai_prefs')) lpai_options.tone = sp.tone;
    var gemini = rcmail.env.lpai_gemini || {};
    if (gemini.model) lpai_options.model = gemini.model;
}

function lpai_check_pending_reply() {
    try {
        // Prefilled reply from draft "Review & Send in Composer"
        var prefilled = localStorage.getItem('lpai_prefilled_reply');
        if (prefilled) {
            localStorage.removeItem('lpai_prefilled_reply');
            setTimeout(function() {
                lpai_apply_with_preserve(prefilled);
                if (rcmail.display_message) {
                    rcmail.display_message('Gemini draft loaded into editor. Review and send!', 'confirmation');
                }
            }, 600);
            return;
        }

        var pending = localStorage.getItem('lpai_pending_reply');
        if (pending) {
            localStorage.removeItem('lpai_pending_reply');
            setTimeout(function() {
                lpai_open_panel('compose');
                lpai_select_action('reply');
            }, 800);
        }
    } catch (e) {}
}

// ========================================
// Markdown to HTML Formatter
// ========================================
function lpai_md_to_html(text) {
    if (!text) return '';
    var html = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    html = html.replace(/```[\s\S]*?```/g, function(m) {
        var code = m.replace(/^```\w*\n?/, '').replace(/\n?```$/, '');
        return '<pre style="background:#f1f5f9;padding:8px 12px;border-radius:6px;font-size:13px;overflow-x:auto">' + code + '</pre>';
    });
    html = html.replace(/`([^`]+)`/g, '<code style="background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:13px">$1</code>');
    html = html.replace(/^### (.+)$/gm, '<strong style="font-size:14px;color:#1e293b">$1</strong>');
    html = html.replace(/^## (.+)$/gm, '<strong style="font-size:15px;color:#1e293b">$1</strong>');
    html = html.replace(/^# (.+)$/gm, '<strong style="font-size:16px;color:#1e293b">$1</strong>');
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
    html = html.replace(/^[-*+] (.+)$/gm, '<li>$1</li>');
    html = html.replace(/((?:<li>.*<\/li>\n?)+)/g, '<ul style="margin:6px 0;padding-left:20px">$1</ul>');
    html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
    html = html.replace(/\n/g, '<br>');
    html = html.replace(/<\/(ul|pre)><br>/g, '</$1>');
    return html;
}

// ========================================
// Skin Adaptation & Container Helpers
// ========================================
function lpai_detect_skin() {
    if (typeof rcmail === 'undefined') return false;
    var skin = (rcmail.env.lpai_skin || rcmail.env.skin || rcmail.env.rcp_skin || '').toLowerCase();
    var isGmailPlus = (skin.indexOf('gmail') !== -1) ||
                      document.body.classList.contains('skin-gmail_plus') ||
                      document.body.classList.contains('xelastic') ||
                      document.body.classList.contains('xskin') ||
                      !!document.getElementById('compose-plus') ||
                      (document.getElementById('layout-menu') && window.getComputedStyle(document.getElementById('layout-menu')).order === '4');

    if (isGmailPlus) {
        document.body.classList.add('lpai-skin-gmail-plus');
    }
    return isGmailPlus;
}

function lpai_get_message_container() {
    return document.getElementById('messagebody') ||
           document.getElementById('messagepreview') ||
           document.getElementById('messagecontent');
}

function lpai_get_message_text() {
    var msgPart = document.querySelector('#messagebody .message-part, #messagebody .message-htmlpart, #messagebody, #messagepreview, #messagecontent');
    if (msgPart) {
        var text = msgPart.innerText || msgPart.textContent || '';
        if (text && text.trim()) return text;
    }
    var iframe = document.getElementById('messagecontframe');
    if (iframe && iframe.contentDocument) {
        try {
            var iframePart = iframe.contentDocument.querySelector('#messagebody, .message-part, .message-htmlpart, body');
            if (iframePart) {
                var iText = iframePart.innerText || iframePart.textContent || '';
                if (iText && iText.trim()) return iText;
            }
        } catch (e) {}
    }
    return '';
}

// ========================================
// Label Integration (roundcube-labels / Thunderbird Standard)
// ========================================
var LPAI_LABEL_DEF = {
    '$Label1': { name: 'Belangrijk', color: '#d93025', bg: '#fce8e6', class: 'label-1' },
    '$Label2': { name: 'Werk', color: '#e37400', bg: '#fef7e0', class: 'label-2' },
    '$Label3': { name: 'Persoonlijk', color: '#188038', bg: '#e6f4ea', class: 'label-3' },
    '$Label4': { name: 'Te doen', color: '#1a73e8', bg: '#e8f0fe', class: 'label-4' },
    '$Label5': { name: 'Later', color: '#9333ea', bg: '#f3e8fd', class: 'label-5' }
};

function lpai_format_size(bytes) {
    if (!bytes || isNaN(bytes)) return '0 B';
    var k = 1024;
    var sizes = ['B', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function lpai_sync_message_row_label(uid, labelFlag) {
    if (!uid || !labelFlag) return;
    var info = LPAI_LABEL_DEF[labelFlag];
    if (!info) return;

    var docs = [document];
    try {
        if (window.parent && window.parent.document && window.parent.document !== document) {
            docs.push(window.parent.document);
        }
        if (window.top && window.top.document && docs.indexOf(window.top.document) === -1) {
            docs.push(window.top.document);
        }
    } catch (e) {}

    docs.forEach(function(doc) {
        var row = doc.getElementById('rcmrow' + uid);
        if (!row) return;

        row.classList.add(info.class);

        var subjectCell = row.querySelector('td.subject') || row.querySelector('.subject') || row;
        if (subjectCell && !row.querySelector('.lpai-row-label-badge')) {
            var badge = doc.createElement('span');
            badge.className = 'lpai-row-label-badge ' + info.class;
            badge.style.cssText = 'display:inline-block;padding:1px 6px;margin-right:6px;border-radius:4px;font-size:11px;font-weight:600;color:' + info.color + ';background:' + info.bg + ';line-height:14px;vertical-align:middle;';
            badge.innerText = info.name;
            var insertTarget = subjectCell.querySelector('a') || subjectCell.firstChild;
            if (insertTarget) {
                subjectCell.insertBefore(badge, insertTarget);
            } else {
                subjectCell.appendChild(badge);
            }
        }
    });
}

// ========================================
// Sidebar Gemini Icon Button (#taskmenu)
// ========================================
function lpai_setup_sidebar_button() {
    var svgIcon = '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M11.04 19.32Q12 21.51 12 24q0-2.49.93-4.68.96-2.19 2.58-3.81t3.81-2.55Q21.51 12 24 12q-2.49 0-4.68-.93a12.3 12.3 0 0 1-3.81-2.58 12.3 12.3 0 0 1-2.58-3.81Q12 2.49 12 0q0 2.49-.96 4.68-.93 2.19-2.55 3.81a12.3 12.3 0 0 1-3.81 2.58Q2.49 12 0 12q2.49 0 4.68.96 2.19.93 3.81 2.55t2.55 3.81"></path></svg>';

    var docs = [document];
    try {
        if (window.parent && window.parent.document && window.parent.document !== document) {
            docs.push(window.parent.document);
        }
        if (window.top && window.top.document && docs.indexOf(window.top.document) === -1) {
            docs.push(window.top.document);
        }
    } catch (e) {}

    docs.forEach(function(doc) {
        // Remove any old floating button if present
        var oldBtns = doc.querySelectorAll('.lpai-floating-btn');
        oldBtns.forEach(function(b) { b.remove(); });

        // Replace any lingering [Gemini] or Gemini text spans anywhere in sidebar buttons
        var innerSpans = doc.querySelectorAll('a.button-gemini-ai .inner, #taskmenu-gemini-btn .inner, a[href="#gemini"] .inner');
        innerSpans.forEach(function(span) {
            span.outerHTML = svgIcon;
        });

        var existingBtn = doc.getElementById('taskmenu-gemini-btn') || doc.querySelector('a.button-gemini-ai') || doc.querySelector('a[href="#gemini"]');
        if (existingBtn) {
            var innerSpan = existingBtn.querySelector('.inner');
            if (innerSpan) {
                innerSpan.outerHTML = svgIcon;
            } else if (!existingBtn.querySelector('svg')) {
                existingBtn.innerHTML = svgIcon;
            }
            existingBtn.onclick = function(e) {
                e.preventDefault();
                lpai_open_panel();
                return false;
            };
            return;
        }

        var taskmenu = doc.getElementById('taskmenu');
        if (!taskmenu) {
            var layoutMenu = doc.getElementById('layout-menu');
            if (layoutMenu) taskmenu = layoutMenu.querySelector('.menu') || layoutMenu;
        }

        if (taskmenu) {
            var a = doc.createElement('a');
            a.id = 'taskmenu-gemini-btn';
            a.className = 'button-gemini-ai';
            a.href = '#gemini';
            a.setAttribute('role', 'button');
            a.setAttribute('tabindex', '0');
            a.setAttribute('aria-label', 'Gemini Assistant');
            a.title = 'Gemini Assistant (Alt+A)';
            a.innerHTML = svgIcon;
            a.onclick = function(e) {
                e.preventDefault();
                lpai_open_panel();
                return false;
            };

            var specialBtns = taskmenu.querySelector('.special-buttons');
            if (specialBtns) {
                taskmenu.insertBefore(a, specialBtns);
            } else {
                taskmenu.appendChild(a);
            }
        }
    });
}

// ========================================
// AUTONOMOUS EXECUTIVE ASSISTANT
// ========================================
function lpai_init_executive_triage(force) {
    var target = lpai_get_message_container();
    if (!target) return;

    var prefs = rcmail.env.lpai_user_prefs || {};
    var mode = prefs.auto_draft_mode || 'open';
    if (mode === 'disabled') return;

    var gemini = rcmail.env.lpai_gemini || {};
    if (!gemini.has_key) return;

    var uid = rcmail.env.uid;
    var mbox = rcmail.env.mailbox || 'INBOX';
    if (!uid) return;

    var cacheKey = 'lpai_triage_' + mbox + '_' + uid;

    // Check browser session cache
    if (!force) {
        try {
            var cached = sessionStorage.getItem(cacheKey);
            if (cached) {
                var c = JSON.parse(cached);
                lpai_render_executive_hub(c.analysis, c.model, c.tokens, true);
                return;
            }
        } catch (e) {}
    }

    // Render loading state
    lpai_render_executive_hub_loading();

    var postData = 'msg_uid=' + encodeURIComponent(uid) +
        '&mbox=' + encodeURIComponent(mbox) +
        (force ? '&force=1' : '') +
        '&_token=' + encodeURIComponent(rcmail.env.request_token);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', rcmail.url('plugin.lifeprisma_ai_triage'));
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) return;
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.status === 'success' && data.analysis) {
                try {
                    sessionStorage.setItem(cacheKey, JSON.stringify({
                        analysis: data.analysis,
                        model: data.model,
                        tokens: data.tokens
                    }));
                } catch (e) {}
                lpai_render_executive_hub(data.analysis, data.model, data.tokens, data.cached);
            } else {
                var hub = document.getElementById('lpai-executive-hub');
                if (hub) hub.remove();
            }
        } catch (e) {
            var hub = document.getElementById('lpai-executive-hub');
            if (hub) hub.remove();
        }
    };
    xhr.send(postData);
}

function lpai_render_executive_hub_loading() {
    var existing = document.getElementById('lpai-executive-hub');
    if (existing) existing.remove();

    var hub = document.createElement('div');
    hub.id = 'lpai-executive-hub';
    hub.className = 'lpai-executive-hub lpai-hub-loading';

    hub.innerHTML =
        '<div class="lpai-hub-header">' +
            '<div class="lpai-hub-brand">' +
                lpai_icon('sparkles') +
                '<strong>Gemini Executive Assistant</strong>' +
            '</div>' +
            '<div class="lpai-hub-status-loading">' +
                '<span class="lpai-spinner-sm"></span>' +
                '<span>Analyzing message & preparing briefing...</span>' +
            '</div>' +
        '</div>';

    var target = lpai_get_message_container();
    if (target && target.parentNode) {
        target.parentNode.insertBefore(hub, target);
    }
}

function lpai_render_executive_hub(analysis, model, tokens, fromCache) {
    var existing = document.getElementById('lpai-executive-hub');
    if (existing) existing.remove();

    if (!analysis) return;

    var category = analysis.category || 'fyi';
    var urgency = analysis.urgency || 'low';
    var categoryLabel = analysis.category_label || 'Executive Briefing';
    var summary = analysis.summary || '';
    var actionItems = analysis.action_items || [];
    var meetingDetails = analysis.meeting_details || null;
    var needsReply = analysis.needs_reply;
    var draftReply = analysis.draft_reply || '';
    var isScam = analysis.is_scam;
    var scamReason = analysis.scam_reason || '';
    var assignedLabel = analysis.assigned_label;

    var hub = document.createElement('div');
    hub.id = 'lpai-executive-hub';
    hub.className = 'lpai-executive-hub lpai-cat-' + category + ' lpai-urgency-' + urgency;

    var badgeClass = 'lpai-badge-' + category;
    var urgencyIcon = urgency === 'high' ? '&#9888;' : '&#9889;';

    // Label badge (roundcube-labels sync)
    var labelHtml = '';
    if (assignedLabel && LPAI_LABEL_DEF[assignedLabel]) {
        var lInfo = LPAI_LABEL_DEF[assignedLabel];
        labelHtml = '<span class="lpai-category-badge lpai-label-badge" style="background:' + lInfo.bg + ';color:' + lInfo.color + ';border:1px solid ' + lInfo.color + '40;" title="Synced via roundcube-labels (' + assignedLabel + ')">&#127991; ' + lpai_escape_html(lInfo.name) + '</span>';
        lpai_sync_message_row_label(rcmail.env.uid, assignedLabel);
    }

    var html = '<div class="lpai-hub-header">';
    html += '<div class="lpai-hub-brand">';
    html += lpai_icon('sparkles');
    html += '<span class="lpai-hub-title">Gemini Assistant</span>';
    html += '<span class="lpai-category-badge ' + badgeClass + '">' + urgencyIcon + ' ' + lpai_escape_html(categoryLabel) + '</span>';
    if (labelHtml) html += ' ' + labelHtml;
    html += '</div>';

    html += '<div class="lpai-hub-actions">';
    html += '<button type="button" class="lpai-hub-btn-icon" title="Refresh Analysis" onclick="lpai_init_executive_triage(true)">' + lpai_icon('refresh') + '</button>';
    html += '<button type="button" class="lpai-hub-btn-icon lpai-hub-toggle" title="Toggle Briefing" onclick="lpai_toggle_hub_body()"><span id="lpai-hub-toggle-arrow">&#9650;</span></button>';
    html += '</div>';
    html += '</div>'; // header

    html += '<div id="lpai-hub-body" class="lpai-hub-body">';

    // Security warning if scam
    if (isScam) {
        html += '<div class="lpai-scam-alert">';
        html += '<span class="lpai-alert-icon">&#9888;</span>';
        html += '<div><strong>Security Warning:</strong> ' + lpai_escape_html(scamReason || 'Suspicious content or sender impersonation detected.') + '</div>';
        html += '<button type="button" class="lpai-btn-junk" onclick="rcmail.command(\'move\',\'Junk\')">Move to Spam</button>';
        html += '</div>';
    }

    // Executive Summary
    if (summary) {
        html += '<div class="lpai-hub-summary">';
        html += '<div class="lpai-section-title">&#128203; Executive Briefing</div>';
        html += '<p class="lpai-summary-text">' + lpai_escape_html(summary) + '</p>';
        html += '</div>';
    }

    // Action items & Meeting details
    if (actionItems.length > 0 || meetingDetails) {
        html += '<div class="lpai-hub-actions-section">';
        if (actionItems.length > 0) {
            html += '<div class="lpai-section-title">&#10003; Action Items & Next Steps</div>';
            html += '<ul class="lpai-action-list">';
            for (var i = 0; i < actionItems.length; i++) {
                var safeItem = lpai_escape_html(actionItems[i]);
                html += '<li><label class="lpai-checkbox-label"><input type="checkbox" onchange="this.parentElement.classList.toggle(\'checked\', this.checked)"> <span>' + safeItem + '</span></label></li>';
            }
            html += '</ul>';
        }

        if (meetingDetails) {
            html += '<div class="lpai-meeting-chip">&#128197; <strong>Meeting Detected:</strong> ' + lpai_escape_html(meetingDetails) + '</div>';
        }
        html += '</div>';
    }

    // Pre-crafted Draft Reply
    if (needsReply && draftReply) {
        html += '<div class="lpai-hub-draft-section">';
        html += '<div class="lpai-draft-header">';
        html += '<div class="lpai-section-title">&#9997; Gemini Prepared Draft Reply</div>';
        html += '<div class="lpai-tone-chips">';
        html += '<span class="lpai-tone-label">Tune:</span>';
        html += '<button type="button" class="lpai-tone-pill" onclick="lpai_retune_draft(\'concise\')">Concise</button>';
        html += '<button type="button" class="lpai-tone-pill" onclick="lpai_retune_draft(\'professional\')">Professional</button>';
        html += '<button type="button" class="lpai-tone-pill" onclick="lpai_retune_draft(\'friendly\')">Friendly</button>';
        html += '</div>';
        html += '</div>';

        html += '<div class="lpai-draft-box">';
        html += '<textarea id="lpai-hub-draft-text" class="lpai-draft-textarea" rows="4">' + lpai_escape_html(draftReply) + '</textarea>';
        html += '</div>';

        // Attachments Picker for Reply
        var attachments = rcmail.env.lpai_attachments || [];
        if (attachments && attachments.length > 0) {
            html += '<div class="lpai-draft-attachments-section">';
            html += '<div class="lpai-section-title">&#128206; Attachments for Reply</div>';
            html += '<div class="lpai-draft-attachments-list">';
            for (var aIdx = 0; aIdx < attachments.length; aIdx++) {
                var att = attachments[aIdx];
                var attJson = lpai_escape_html(JSON.stringify(att));
                html += '<label class="lpai-checkbox-label lpai-attachment-chip"><input type="checkbox" class="lpai-attachment-checkbox" data-attachment="' + attJson + '"> <span>' + lpai_escape_html(att.name) + ' (' + lpai_format_size(att.size) + ')</span></label>';
            }
            html += '</div>';
            html += '</div>';
        }

        html += '<div class="lpai-draft-footer">';
        html += '<button type="button" class="lpai-btn-composer" onclick="lpai_send_to_composer()">';
        html += '<span>&#9998; Review & Send in Composer</span>';
        html += '</button>';
        html += '<button type="button" class="lpai-btn-copy" onclick="lpai_copy_draft(this)">' + lpai_icon('copy') + ' Copy</button>';
        html += '<button type="button" class="lpai-btn-copy lpai-btn-memory" onclick="lpai_remember_answer(this)" title="Save this Question & Answer into AI Memory so similar questions from other clients receive this verified answer">&#129504; Remember this Answer</button>';
        html += '</div>';
        html += '</div>';
    }

    // Footer with model info
    var cost = lpai_estimate_cost(model, tokens ? tokens.input : 0, tokens ? tokens.output : 0);
    html += '<div class="lpai-hub-footer">';
    html += '<span>Powered by ' + lpai_escape_html(model || 'Gemini 3.8 Flash') + (cost ? ' \u00B7 ' + cost : '') + (fromCache ? ' \u00B7 Cached' : '') + '</span>';
    html += '</div>';

    html += '</div>'; // hub body

    hub.innerHTML = html;

    var target = lpai_get_message_container();
    if (target && target.parentNode) {
        target.parentNode.insertBefore(hub, target);
    }
}

function lpai_toggle_hub_body() {
    var body = document.getElementById('lpai-hub-body');
    var arrow = document.getElementById('lpai-hub-toggle-arrow');
    if (!body) return;
    if (body.style.display === 'none') {
        body.style.display = 'block';
        if (arrow) arrow.innerHTML = '&#9650;';
    } else {
        body.style.display = 'none';
        if (arrow) arrow.innerHTML = '&#9660;';
    }
}

function lpai_send_to_composer() {
    var draftText = document.getElementById('lpai-hub-draft-text');
    if (!draftText) return;

    var text = draftText.value;
    try {
        localStorage.setItem('lpai_prefilled_reply', text);
    } catch (e) {}

    var selectedAtts = [];
    var checkedBoxes = document.querySelectorAll('.lpai-attachment-checkbox:checked');
    checkedBoxes.forEach(function(cb) {
        try {
            selectedAtts.push(JSON.parse(cb.getAttribute('data-attachment')));
        } catch (e) {}
    });

    if (selectedAtts.length > 0) {
        var postData = 'reply=' + encodeURIComponent(text) +
            '&subject=' + encodeURIComponent(rcmail.env.subject ? ('Re: ' + rcmail.env.subject.replace(/^(Re:\s*)+/i, '')) : '') +
            '&attachments=' + encodeURIComponent(JSON.stringify(selectedAtts)) +
            '&_token=' + encodeURIComponent(rcmail.env.request_token);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', rcmail.url('plugin.lifeprisma_ai_prepare_compose'));
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                rcmail.command('reply');
            }
        };
        xhr.send(postData);
    } else {
        rcmail.command('reply');
    }
}

function lpai_copy_draft(btn) {
    var draftText = document.getElementById('lpai-hub-draft-text');
    if (!draftText) return;
    var text = draftText.value;
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = lpai_icon('check') + ' Copied!';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}

function lpai_remember_answer(btn) {
    var draftText = document.getElementById('lpai-hub-draft-text');
    var summaryEl = document.querySelector('.lpai-summary-text');
    var defaultQ = summaryEl ? summaryEl.innerText.trim() : (rcmail.env.subject || 'Client Inquiry');
    var answer = draftText ? draftText.value.trim() : '';

    if (!answer) {
        if (rcmail.display_message) rcmail.display_message('No draft answer found to learn', 'warning');
        return;
    }

    var question = prompt('Verify question or topic to remember for future clients:', defaultQ);
    if (!question) return;

    if (btn) {
        btn.disabled = true;
        btn.innerText = 'Remembering...';
    }

    var postData = 'op=add' +
        '&question=' + encodeURIComponent(question) +
        '&answer=' + encodeURIComponent(answer) +
        '&subject=' + encodeURIComponent(rcmail.env.subject || '') +
        '&client=' + encodeURIComponent(rcmail.env.from || '') +
        '&_token=' + encodeURIComponent(rcmail.env.request_token);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', rcmail.url('plugin.lifeprisma_ai_memory'));
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) return;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '&#129504; Remember this Answer';
        }
        try {
            var res = JSON.parse(xhr.responseText);
            if (res.status === 'success') {
                if (rcmail.display_message) {
                    rcmail.display_message('AI learned this answer! Similar client questions will now replicate this answer.', 'confirmation');
                }
            } else {
                if (rcmail.display_message) rcmail.display_message(res.message || 'Failed to remember answer', 'error');
            }
        } catch (e) {
            if (rcmail.display_message) rcmail.display_message('Failed to save to memory', 'error');
        }
    };
    xhr.send(postData);
}

function lpai_retune_draft(tone) {
    var draftBox = document.getElementById('lpai-hub-draft-text');
    if (!draftBox) return;

    var origText = draftBox.value;
    draftBox.disabled = true;
    draftBox.style.opacity = '0.6';

    var postData = {
        _action: 'plugin.lifeprisma_ai_request',
        ai_action: 'rewrite',
        instruction: 'Rewrite this email draft to be distinctly ' + tone + ' while retaining key facts.',
        email_body: origText,
        reply_text: '',
        subject: '',
        language: lpai_options.language,
        tone: tone,
        sender_name: '',
        model: lpai_options.model,
        _token: rcmail.env.request_token
    };

    var encoded = [];
    for (var k in postData) encoded.push(encodeURIComponent(k) + '=' + encodeURIComponent(postData[k]));

    fetch(rcmail.url('plugin.lifeprisma_ai_request'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: encoded.join('&')
    }).then(function(r) { return r.json(); }).then(function(data) {
        draftBox.disabled = false;
        draftBox.style.opacity = '1';
        if (data.status === 'success' && data.result) {
            draftBox.value = data.result;
        }
    }).catch(function() {
        draftBox.disabled = false;
        draftBox.style.opacity = '1';
    });
}

// ========================================
// Quick Actions Toolbar (Read View)
// ========================================
function lpai_add_message_button() {
    lpai_setup_sidebar_button();
    lpai_add_quick_actions();
}

function lpai_add_quick_actions() {
    var target = lpai_get_message_container();
    if (!target) return;

    var existingBar = document.getElementById('lpai-qa-bar');
    if (existingBar) existingBar.remove();

    var bar = document.createElement('div');
    bar.className = 'lpai-qa-bar';
    bar.id = 'lpai-qa-bar';

    var label = document.createElement('span');
    label.className = 'lpai-qa-label';
    label.innerHTML = lpai_icon('sparkles') + ' <span>Gemini Actions</span>';
    bar.appendChild(label);

    // Translate Dropdown
    var trWrap = document.createElement('div');
    trWrap.className = 'lpai-qa-dropdown';

    var trBtn = document.createElement('button');
    trBtn.type = 'button';
    trBtn.className = 'lpai-qa-btn';
    trBtn.innerHTML = lpai_icon('translate') + ' <span>Translate</span> ' + lpai_icon('chevron');
    trBtn.onclick = function(e) {
        e.stopPropagation();
        var menu = document.getElementById('lpai-tr-menu');
        if (menu) menu.classList.toggle('open');
    };
    trWrap.appendChild(trBtn);

    var trMenu = document.createElement('div');
    trMenu.id = 'lpai-tr-menu';
    trMenu.className = 'lpai-qa-menu';
    var langs = ['English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese', 'Dutch'];
    for (var i = 0; i < langs.length; i++) {
        (function(lang) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'lpai-qa-menu-item';
            item.textContent = lang;
            item.onclick = function() {
                trMenu.classList.remove('open');
                lpai_translate_to(lang, trBtn);
            };
            trMenu.appendChild(item);
        })(langs[i]);
    }
    trWrap.appendChild(trMenu);
    bar.appendChild(trWrap);

    // Summarize Button
    var sumBtn = document.createElement('button');
    sumBtn.type = 'button';
    sumBtn.className = 'lpai-qa-btn';
    sumBtn.innerHTML = lpai_icon('summarize') + ' <span>Summarize</span>';
    sumBtn.onclick = function() { lpai_quick_action('summarize', sumBtn); };
    bar.appendChild(sumBtn);

    // Reply with Gemini
    var replyBtn = document.createElement('button');
    replyBtn.type = 'button';
    replyBtn.className = 'lpai-qa-btn lpai-qa-reply';
    replyBtn.innerHTML = lpai_icon('reply') + ' <span>Reply with Gemini</span>';
    replyBtn.onclick = function() { lpai_open_panel('read'); lpai_select_action('reply'); };
    bar.appendChild(replyBtn);

    target.parentNode.insertBefore(bar, target);

    document.addEventListener('click', function() {
        var menu = document.getElementById('lpai-tr-menu');
        if (menu) menu.classList.remove('open');
    });
}

function lpai_quick_action(action, clickedBtn) {
    var text = lpai_get_message_text();
    if (!text.trim()) return;

    var panel = document.getElementById('lpai-qa-result-panel');
    if (!panel) {
        panel = document.createElement('div');
        panel.id = 'lpai-qa-result-panel';
        panel.className = 'lpai-qa-result-panel';
        var bar = document.getElementById('lpai-qa-bar');
        if (bar) bar.parentNode.insertBefore(panel, bar.nextSibling);
    }
    panel.style.display = 'block';
    panel.innerHTML = '<div class="lpai-qa-result-header"><strong>Gemini Summary</strong><button type="button" class="lpai-qa-close" onclick="this.parentElement.parentElement.style.display=\'none\'">&times;</button></div><div id="lpai-qa-result-content" class="lpai-qa-result-content"><div class="lpai-spinner-sm"></div> Streaming...</div>';

    var targetEl = document.getElementById('lpai-qa-result-content');
    var postData = {
        _action: 'plugin.lifeprisma_ai_stream',
        ai_action: action,
        instruction: '',
        email_body: '',
        reply_text: text.substring(0, 3500),
        subject: lpai_get_subject(),
        language: lpai_options.language,
        tone: lpai_options.tone,
        model: lpai_options.model,
        view_context: 'read',
        _token: rcmail.env.request_token
    };

    var controller = new AbortController();
    lpai_stream_to_element(postData, targetEl, controller);
}

function lpai_translate_to(lang, btn) {
    var text = lpai_get_message_text();
    if (!text.trim()) return;

    var panel = document.getElementById('lpai-qa-result-panel');
    if (!panel) {
        panel = document.createElement('div');
        panel.id = 'lpai-qa-result-panel';
        panel.className = 'lpai-qa-result-panel';
        var bar = document.getElementById('lpai-qa-bar');
        if (bar) bar.parentNode.insertBefore(panel, bar.nextSibling);
    }
    panel.style.display = 'block';
    panel.innerHTML = '<div class="lpai-qa-result-header"><strong>Translation (' + lpai_escape_html(lang) + ')</strong><button type="button" class="lpai-qa-close" onclick="this.parentElement.parentElement.style.display=\'none\'">&times;</button></div><div id="lpai-qa-result-content" class="lpai-qa-result-content"><div class="lpai-spinner-sm"></div> Translating...</div>';

    var targetEl = document.getElementById('lpai-qa-result-content');
    var postData = {
        _action: 'plugin.lifeprisma_ai_stream',
        ai_action: 'translate',
        instruction: '',
        email_body: '',
        reply_text: text.substring(0, 3500),
        subject: '',
        language: lang,
        tone: 'professional',
        model: lpai_options.model,
        view_context: 'read',
        _token: rcmail.env.request_token
    };

    var controller = new AbortController();
    lpai_stream_to_element(postData, targetEl, controller);
}

// ========================================
// Compose View Enhancements
// ========================================
function lpai_add_compose_button() {
    lpai_setup_sidebar_button();
    lpai_add_compose_quick_actions();
}

function lpai_add_compose_quick_actions() {
    var container = document.getElementById('composebodycontainer') || document.getElementById('compose-content');
    if (!container) return;

    var existingBar = document.getElementById('lpai-qa-bar-compose');
    if (existingBar) existingBar.remove();

    var bar = document.createElement('div');
    bar.className = 'lpai-qa-bar lpai-qa-bar-compose';
    bar.id = 'lpai-qa-bar-compose';

    var label = document.createElement('span');
    label.className = 'lpai-qa-label';
    label.innerHTML = lpai_icon('sparkles') + ' <span>Gemini</span>';
    bar.appendChild(label);

    // Fix Grammar
    var fixBtn = document.createElement('button');
    fixBtn.type = 'button';
    fixBtn.className = 'lpai-qa-btn';
    fixBtn.innerHTML = lpai_icon('fix') + ' <span>Fix Grammar</span>';
    fixBtn.onclick = function() { lpai_compose_quick('fix', fixBtn); };
    bar.appendChild(fixBtn);

    // Rewrite
    var rwBtn = document.createElement('button');
    rwBtn.type = 'button';
    rwBtn.className = 'lpai-qa-btn';
    rwBtn.innerHTML = lpai_icon('rewrite') + ' <span>Rewrite</span>';
    rwBtn.onclick = function() { lpai_open_panel('compose'); lpai_select_action('rewrite'); };
    bar.appendChild(rwBtn);

    // Suggest Subject
    var subBtn = document.createElement('button');
    subBtn.type = 'button';
    subBtn.className = 'lpai-qa-btn';
    subBtn.innerHTML = lpai_icon('subject') + ' <span>Subject</span>';
    subBtn.onclick = function() { lpai_suggest_subject(subBtn); };
    bar.appendChild(subBtn);

    // Compose with Gemini
    var cmpBtn = document.createElement('button');
    cmpBtn.type = 'button';
    cmpBtn.className = 'lpai-qa-btn lpai-qa-reply';
    cmpBtn.innerHTML = lpai_icon('sparkles') + ' <span>Compose with Gemini</span>';
    cmpBtn.onclick = function() { lpai_open_panel('compose'); };
    bar.appendChild(cmpBtn);

    container.parentNode.insertBefore(bar, container);
}

function lpai_compose_quick(action, btn) {
    var content = lpai_get_editor_content();
    if (!content.trim()) {
        if (rcmail.display_message) rcmail.display_message('Write something first, then use Gemini', 'notice');
        return;
    }

    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '&#9203; Thinking...';

    var postData = {
        _action: 'plugin.lifeprisma_ai_stream',
        ai_action: action,
        instruction: '',
        email_body: content,
        reply_text: '',
        subject: lpai_get_subject(),
        language: lpai_options.language,
        tone: lpai_options.tone,
        model: lpai_options.model,
        view_context: 'compose',
        _token: rcmail.env.request_token
    };

    var temp = document.createElement('div');
    var controller = new AbortController();

    lpai_stream_to_element(postData, temp, controller, function(fullText) {
        btn.disabled = false;
        btn.innerHTML = orig;
        if (fullText) {
            lpai_apply_with_preserve(fullText);
            if (rcmail.display_message) rcmail.display_message('Gemini text applied', 'confirmation');
        }
    });
}

function lpai_suggest_subject(btn) {
    var content = lpai_get_editor_content();
    if (!content.trim()) {
        if (rcmail.display_message) rcmail.display_message('Write some email body text first', 'notice');
        return;
    }

    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '&#9203; Generating...';

    var postData = {
        _action: 'plugin.lifeprisma_ai_request',
        ai_action: 'suggest_subject',
        instruction: '',
        email_body: content.substring(0, 1500),
        reply_text: '',
        subject: '',
        language: lpai_options.language,
        tone: lpai_options.tone,
        model: lpai_options.model,
        _token: rcmail.env.request_token
    };

    var encoded = [];
    for (var k in postData) encoded.push(encodeURIComponent(k) + '=' + encodeURIComponent(postData[k]));

    fetch(rcmail.url('plugin.lifeprisma_ai_request'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: encoded.join('&')
    }).then(function(r) { return r.json(); }).then(function(data) {
        btn.disabled = false;
        btn.innerHTML = orig;
        if (data.status === 'success' && data.result) {
            var lines = data.result.split('\n').filter(function(l) { return l.trim().length > 0; });
            var first = lines[0].replace(/^\d+\.\s*/, '').replace(/^["']|["']$/g, '');
            var subInput = document.getElementById('_subject') || document.querySelector('input[name="_subject"]');
            if (subInput) subInput.value = first;
            if (rcmail.display_message) rcmail.display_message('Subject set: ' + first, 'confirmation');
        }
    }).catch(function() {
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

// ========================================
// Smart Compose Autocomplete
// ========================================
var lpai_sc_timer = null;
function lpai_init_smart_compose() {
    var enabled = rcmail.env.lpai_smart_compose;
    if (!enabled) return;

    var textarea = document.getElementById('_message');
    if (textarea) {
        textarea.addEventListener('keyup', function(e) {
            if (e.key === 'Tab' || e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt') return;
            clearTimeout(lpai_sc_timer);
            lpai_sc_timer = setTimeout(function() {
                lpai_trigger_autocomplete(textarea.value);
            }, 800);
        });
    }
}

function lpai_trigger_autocomplete(text) {
    if (!text || text.trim().length < 15) return;
    var gemini = rcmail.env.lpai_gemini || {};
    if (!gemini.has_key) return;

    var postData = {
        _action: 'plugin.lifeprisma_ai_request',
        ai_action: 'autocomplete',
        instruction: '',
        email_body: text.substring(Math.max(0, text.length - 400)),
        reply_text: '',
        subject: lpai_get_subject(),
        language: lpai_options.language,
        tone: lpai_options.tone,
        model: lpai_options.model || 'gemini-3.8-flash',
        _token: rcmail.env.request_token
    };

    var encoded = [];
    for (var k in postData) encoded.push(encodeURIComponent(k) + '=' + encodeURIComponent(postData[k]));

    fetch(rcmail.url('plugin.lifeprisma_ai_request'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: encoded.join('&')
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.status === 'success' && data.result) {
            // Can display inline completion ghost or hint
            var hint = data.result.trim();
            if (hint.length > 0 && rcmail.display_message) {
                // Subtle notice
            }
        }
    }).catch(function() {});
}

// ========================================
// Editor Helper Utilities
// ========================================
function lpai_get_editor_content() {
    if (window.tinymce && tinymce.activeEditor) {
        return tinymce.activeEditor.getContent({ format: 'text' }) || '';
    }
    var ta = document.getElementById('_message');
    return ta ? ta.value : '';
}

function lpai_apply_with_preserve(newContent) {
    if (window.tinymce && tinymce.activeEditor) {
        var ed = tinymce.activeEditor;
        var existingHtml = ed.getContent();
        var htmlContent = lpai_md_to_html(newContent);

        // Check if there is quoted content or signature
        var quoteIdx = existingHtml.indexOf('<blockquote');
        if (quoteIdx < 0) quoteIdx = existingHtml.indexOf('class="gmail_quote"');

        if (quoteIdx > 0) {
            var quotePart = existingHtml.substring(quoteIdx);
            ed.setContent(htmlContent + '<br><br>' + quotePart);
        } else {
            ed.setContent(htmlContent);
        }
    } else {
        var ta = document.getElementById('_message');
        if (ta) {
            var val = ta.value;
            var qIdx = val.indexOf('\n> ');
            if (qIdx > 0) {
                ta.value = newContent + '\n\n' + val.substring(qIdx);
            } else {
                ta.value = newContent;
            }
        }
    }
}

function lpai_get_subject() {
    var sub = document.getElementById('_subject') || document.querySelector('input[name="_subject"]');
    return sub ? sub.value : (rcmail.env.subject || '');
}

// ========================================
// Streaming Engine
// ========================================
function lpai_stream_to_element(postData, targetEl, controller, onDone) {
    var encoded = [];
    for (var k in postData) encoded.push(encodeURIComponent(k) + '=' + encodeURIComponent(postData[k]));

    var fullText = '';
    var tokens = { input: 0, output: 0 };
    var model = postData.model;

    fetch(rcmail.url(postData._action || 'plugin.lifeprisma_ai_stream'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: encoded.join('&'),
        signal: controller ? controller.signal : undefined
    }).then(function(res) {
        var reader = res.body.getReader();
        var decoder = new TextDecoder();
        targetEl.innerHTML = '';

        function readChunk() {
            return reader.read().then(function(result) {
                if (result.done) {
                    if (onDone) onDone(fullText, tokens, model);
                    return;
                }
                var chunk = decoder.decode(result.value, { stream: true });
                var lines = chunk.split('\n');

                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i].trim();
                    if (!line || line.indexOf('data: ') !== 0) continue;
                    var jsonStr = line.substring(6);
                    if (jsonStr === '[DONE]') continue;
                    try {
                        var evt = JSON.parse(jsonStr);
                        if (evt.type === 'delta' && evt.text) {
                            fullText += evt.text;
                            targetEl.innerHTML = lpai_md_to_html(fullText);
                        } else if (evt.type === 'error') {
                            targetEl.innerHTML = '<span style="color:#ef4444">Error: ' + lpai_escape_html(evt.message) + '</span>';
                        }
                    } catch (e) {}
                }
                return readChunk();
            });
        }
        return readChunk();
    }).catch(function(err) {
        if (err.name !== 'AbortError') {
            targetEl.innerHTML = '<span style="color:#ef4444">Connection interrupted</span>';
        }
    });
}

// ========================================
// Modal Assistant Panel
// ========================================
function lpai_get_modal_elements() {
    var panel = document.getElementById('lpai-panel');
    var overlay = document.getElementById('lpai-overlay');
    try {
        if (!panel && window.parent && window.parent.document) {
            panel = window.parent.document.getElementById('lpai-panel');
            overlay = window.parent.document.getElementById('lpai-overlay');
        }
        if (!panel && window.top && window.top.document) {
            panel = window.top.document.getElementById('lpai-panel');
            overlay = window.top.document.getElementById('lpai-overlay');
        }
    } catch (e) {}
    return { panel: panel, overlay: overlay };
}

function lpai_open_panel(context) {
    var els = lpai_get_modal_elements();
    var panel = els.panel;
    var overlay = els.overlay;
    if (!panel || !overlay) return;

    lpai_panel_context = context || 'compose';
    panel.style.display = 'flex';
    overlay.style.display = 'block';

    var doc = panel.ownerDocument || document;
    var input = doc.getElementById('lpai-input');
    if (input) input.focus();
}

function lpai_close_panel() {
    var els = lpai_get_modal_elements();
    var panel = els.panel;
    var overlay = els.overlay;
    if (panel) panel.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
    if (lpai_stream_controller) {
        lpai_stream_controller.abort();
        lpai_stream_controller = null;
    }
}

function lpai_select_action(action) {
    lpai_current_action = action;
    var btns = document.querySelectorAll('.lpai-action-btn');
    btns.forEach(function(b) {
        b.classList.toggle('active', b.dataset.action === action);
    });

    var input = document.getElementById('lpai-input');
    if (input) {
        var placeholders = {
            'compose': 'What should Gemini write?',
            'rewrite': 'How should Gemini rephrase this?',
            'fix': 'Correcting grammar & clarity...',
            'translate': 'Translating into selected language...',
            'summarize': 'Extracting key takeaways...',
            'suggest_subject': 'Generating high-impact subject lines...'
        };
        input.placeholder = placeholders[action] || 'Instruction for Gemini...';
    }
}

function lpai_submit() {
    var input = document.getElementById('lpai-input');
    var instruction = input ? input.value.trim() : '';
    var action = lpai_current_action || 'compose';

    var preview = document.getElementById('lpai-preview');
    var previewContent = document.getElementById('lpai-preview-content');
    var applyBtn = document.getElementById('lpai-apply');
    var copyBtn = document.getElementById('lpai-copy');
    var cancelBtn = document.getElementById('lpai-cancel');
    var generateBtn = document.getElementById('lpai-generate');

    if (preview) preview.style.display = 'block';
    if (applyBtn) applyBtn.style.display = 'none';
    if (copyBtn) copyBtn.style.display = 'none';
    if (cancelBtn) cancelBtn.style.display = 'inline-flex';
    if (generateBtn) generateBtn.style.display = 'none';

    var contextText = '';
    if (lpai_panel_context === 'compose') {
        contextText = lpai_get_editor_content();
    } else {
        contextText = lpai_get_message_text();
    }

    var modelSelect = document.getElementById('lpai-model-select');
    var toneSelect = document.getElementById('lpai-tone-select');
    var langSelect = document.getElementById('lpai-lang-select');

    var model = modelSelect ? modelSelect.value : lpai_options.model;
    var tone = toneSelect ? toneSelect.value : lpai_options.tone;
    var language = langSelect ? langSelect.value : lpai_options.language;

    var postData = {
        _action: 'plugin.lifeprisma_ai_stream',
        ai_action: action,
        instruction: instruction,
        email_body: (lpai_panel_context === 'compose') ? contextText : '',
        reply_text: (lpai_panel_context === 'read') ? contextText.substring(0, 3500) : '',
        subject: lpai_get_subject(),
        language: language,
        tone: tone,
        model: model,
        view_context: lpai_panel_context,
        _token: rcmail.env.request_token
    };

    if (lpai_stream_controller) lpai_stream_controller.abort();
    lpai_stream_controller = new AbortController();

    lpai_stream_to_element(postData, previewContent, lpai_stream_controller, function(fullText, tokens) {
        lpai_last_result = fullText;
        if (cancelBtn) cancelBtn.style.display = 'none';
        if (generateBtn) generateBtn.style.display = 'inline-flex';
        if (applyBtn) applyBtn.style.display = 'inline-flex';
        if (copyBtn) copyBtn.style.display = 'inline-flex';

        var costSpan = document.getElementById('lpai-token-cost');
        if (costSpan) {
            var cost = lpai_estimate_cost(model, tokens.input, tokens.output);
            costSpan.textContent = cost ? ('Cost: ' + cost) : '';
        }
    });
}

function lpai_apply_result() {
    if (!lpai_last_result) return;
    lpai_apply_with_preserve(lpai_last_result);
    lpai_close_panel();
    if (rcmail.display_message) rcmail.display_message('Gemini text inserted', 'confirmation');
}

function lpai_copy_result() {
    if (!lpai_last_result) return;
    navigator.clipboard.writeText(lpai_last_result).then(function() {
        if (rcmail.display_message) rcmail.display_message('Copied to clipboard', 'confirmation');
    });
}

// ========================================
// Cost Estimation
// ========================================
function lpai_estimate_cost(model, inpTokens, outTokens) {
    var inp = Number(inpTokens) || 0;
    var out = Number(outTokens) || 0;
    if (inp === 0 && out === 0) return null;

    var rates = [0.30, 2.50]; // Gemini 3.8 / 3.7 / 3.6 / 3.5 Flash default ($ per 1M tokens)
    if (model && model.indexOf('lite') >= 0) {
        rates = [0.075, 0.30];
    } else if (model && model.indexOf('pro') >= 0) {
        rates = [1.25, 10.00];
    }

    var cost = (inp * rates[0] + out * rates[1]) / 1000000;
    if (cost < 0.0001) return '$' + cost.toFixed(6);
    return '$' + cost.toFixed(4);
}

// ========================================
// Event Listeners
// ========================================
function lpai_bind_events() {
    document.addEventListener('click', function(e) {
        if (e.target.id === 'lpai-close' || e.target.id === 'lpai-overlay' || (e.target.closest && e.target.closest('#lpai-close'))) {
            lpai_close_panel();
        }
        var actionBtn = e.target.closest ? e.target.closest('.lpai-action-btn') : null;
        if (actionBtn) {
            lpai_select_action(actionBtn.dataset.action);
        }
        var genBtn = e.target.closest ? e.target.closest('#lpai-generate') : null;
        if (genBtn || e.target.id === 'lpai-generate') {
            lpai_submit();
        }
        var applyBtn = e.target.closest ? e.target.closest('#lpai-apply') : null;
        if (applyBtn || e.target.id === 'lpai-apply') {
            lpai_apply_result();
        }
        var copyBtn = e.target.closest ? e.target.closest('#lpai-copy') : null;
        if (copyBtn || e.target.id === 'lpai-copy') {
            lpai_copy_result();
        }
        var cancelBtn = e.target.closest ? e.target.closest('#lpai-cancel') : null;
        if (cancelBtn || e.target.id === 'lpai-cancel') {
            if (lpai_stream_controller) {
                lpai_stream_controller.abort();
                lpai_stream_controller = null;
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.target.id === 'lpai-input' && e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            lpai_submit();
        }
        if (e.key === 'Escape') {
            lpai_close_panel();
        }
        if (e.altKey && (e.key === 'a' || e.key === 'A')) {
            e.preventDefault();
            var panel = document.getElementById('lpai-panel');
            if (panel && panel.style.display !== 'none') {
                lpai_close_panel();
            } else {
                var action = rcmail.env.action;
                var ctx = (action === 'show' || action === 'preview') ? 'read' : 'compose';
                lpai_open_panel(ctx);
            }
        }
    });
}

// ========================================
// Admin Panel (Google Gemini Exclusivity)
// ========================================
function lpai_init_admin() {
    var root = document.getElementById('lpai-admin-root');
    if (!root) return;

    var urlConfig = root.dataset.urlConfig;
    var urlSave = root.dataset.urlSave;
    var token = root.dataset.token;

    root.innerHTML = '<div class="lpai-admin-loading"><div class="lpai-spinner-sm"></div> Loading Gemini settings...</div>';

    fetch(urlConfig + '&op=get_config&_token=' + encodeURIComponent(token))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status !== 'success') {
                root.innerHTML = '<div style="color:#ef4444;padding:16px">Failed to load Gemini config</div>';
                return;
            }
            lpai_render_admin(root, data, urlSave, token, urlConfig);
        })
        .catch(function(err) {
            root.innerHTML = '<div style="color:#ef4444;padding:16px">Error: ' + lpai_escape_html(err.message) + '</div>';
        });
}

function lpai_render_admin(root, data, urlSave, token, urlConfig) {
    var gemini = data.gemini || {};
    var settings = data.settings || {};
    var usage = data.usage || {};

    var safeApiKeyMasked = lpai_escape_html(gemini.api_key_masked || 'Paste Google Gemini API key...');
    var currentModel = gemini.model || 'gemini-3.8-flash';

    var html = '<div class="lpai-admin-container">';

    // Header Card
    html += '<div class="lpai-admin-card">';
    html += '<div class="lpai-admin-card-header">';
    html += lpai_icon('sparkles') + ' <h3>Google Gemini Assistant Configuration</h3>';
    html += '</div>';
    html += '<p class="lpai-admin-subtitle">GenIA is powered exclusively by Google Gemini for lightning-fast executive triage, briefings, and contextual draft generation.</p>';

    // API Key Row
    html += '<div class="lpai-form-group">';
    html += '<label class="lpai-label">Gemini API Key</label>';
    html += '<div class="lpai-input-with-action">';
    html += '<input type="password" id="lpai-admin-key" class="lpai-admin-input" placeholder="' + safeApiKeyMasked + '">';
    html += '<button type="button" id="lpai-admin-test-btn" class="lpai-btn-secondary">Test Connection</button>';
    html += '</div>';
    html += '<div id="lpai-test-status" class="lpai-test-status"></div>';
    html += '<small class="lpai-help">Get your API key at <a href="https://aistudio.google.com/apikey" target="_blank" style="color:#2563eb;text-decoration:underline">aistudio.google.com/apikey</a></small>';
    html += '</div>';

    // Model Dropdown
    html += '<div class="lpai-form-group">';
    html += '<label class="lpai-label">Default Gemini Model</label>';
    html += '<select id="lpai-admin-model" class="lpai-admin-input">';
    var models = gemini.models || ['gemini-3.8-flash', 'gemini-3.8-flash-cyber', 'gemini-3.7-flash', 'gemini-3.6-flash', 'gemini-3.5-flash', 'gemini-3.5-flash-lite'];
    for (var m = 0; m < models.length; m++) {
        var sel = (models[m] === currentModel) ? ' selected' : '';
        html += '<option value="' + lpai_escape_html(models[m]) + '"' + sel + '>' + lpai_escape_html(models[m]) + '</option>';
    }
    html += '</select>';
    html += '<small class="lpai-help"><strong>gemini-3.8-flash</strong> is recommended for instant 1M token triage, executive briefings, and draft generation.</small>';
    html += '</div>';

    // Autonomous Mode
    html += '<div class="lpai-form-group">';
    html += '<label class="lpai-label">Autonomous Assistant Mode</label>';
    html += '<select id="lpai-admin-mode" class="lpai-admin-input">';
    var currentMode = settings.auto_draft_mode || 'open';
    html += '<option value="open"' + (currentMode === 'open' ? ' selected' : '') + '>Active on Email Read (Auto-triage, briefing & draft reply)</option>';
    html += '<option value="receive"' + (currentMode === 'receive' ? ' selected' : '') + '>Background on Incoming Mail</option>';
    html += '<option value="disabled"' + (currentMode === 'disabled' ? ' selected' : '') + '>Disabled (Manual trigger only)</option>';
    html += '</select>';
    html += '</div>';

    // Language and Tone
    html += '<div class="lpai-form-row">';
    html += '<div class="lpai-form-group lpai-half">';
    html += '<label class="lpai-label">Default Language</label>';
    html += '<select id="lpai-admin-lang" class="lpai-admin-input">';
    var langs = ['English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese', 'Dutch'];
    for (var l = 0; l < langs.length; l++) {
        var s = (langs[l] === settings.default_language) ? ' selected' : '';
        html += '<option value="' + langs[l] + '"' + s + '>' + langs[l] + '</option>';
    }
    html += '</select>';
    html += '</div>';

    html += '<div class="lpai-form-group lpai-half">';
    html += '<label class="lpai-label">Default Tone</label>';
    html += '<select id="lpai-admin-tone" class="lpai-admin-input">';
    var tones = ['professional', 'concise', 'friendly', 'formal', 'direct'];
    for (var t = 0; t < tones.length; t++) {
        var st = (tones[t] === settings.default_tone) ? ' selected' : '';
        html += '<option value="' + tones[t] + '"' + st + '>' + tones[t].charAt(0).toUpperCase() + tones[t].slice(1) + '</option>';
    }
    html += '</select>';
    html += '</div>';
    html += '</div>';

    // Stats
    html += '<div class="lpai-admin-stats-card">';
    html += '<div class="lpai-stat-box"><span class="lpai-stat-num">' + (usage.total_users || 0) + '</span><span class="lpai-stat-label">Total Users</span></div>';
    html += '<div class="lpai-stat-box"><span class="lpai-stat-num">' + (usage.active_users || 0) + '</span><span class="lpai-stat-label">Active Users</span></div>';
    html += '</div>';

    // Save Button
    html += '<div class="lpai-admin-footer">';
    html += '<button type="button" id="lpai-admin-save-btn" class="lpai-btn-primary">Save Gemini Settings</button>';
    html += '<span id="lpai-save-status" class="lpai-save-status"></span>';
    html += '</div>';

    html += '</div>'; // admin card
    html += '</div>'; // admin container

    root.innerHTML = html;

    // Bind Test Connection Button
    var testBtn = document.getElementById('lpai-admin-test-btn');
    if (testBtn) {
        testBtn.onclick = function() {
            var keyInput = document.getElementById('lpai-admin-key');
            var keyVal = keyInput ? keyInput.value.trim() : '';
            var statusDiv = document.getElementById('lpai-test-status');
            if (statusDiv) statusDiv.innerHTML = '<span class="lpai-spinner-sm"></span> Testing connection...';

            var fd = 'op=test_connection&api_key=' + encodeURIComponent(keyVal) + '&_token=' + encodeURIComponent(token);
            fetch(urlConfig, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: fd
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (statusDiv) {
                    if (res.status === 'success') {
                        statusDiv.innerHTML = '<span style="color:#16a34a">&#10003; ' + lpai_escape_html(res.message) + '</span>';
                    } else {
                        statusDiv.innerHTML = '<span style="color:#ef4444">&#10007; ' + lpai_escape_html(res.message) + '</span>';
                    }
                }
            }).catch(function(err) {
                if (statusDiv) statusDiv.innerHTML = '<span style="color:#ef4444">&#10007; Error: ' + lpai_escape_html(err.message) + '</span>';
            });
        };
    }

    // Bind Save Button
    var saveBtn = document.getElementById('lpai-admin-save-btn');
    if (saveBtn) {
        saveBtn.onclick = function() {
            var keyInput = document.getElementById('lpai-admin-key');
            var modelSelect = document.getElementById('lpai-admin-model');
            var modeSelect = document.getElementById('lpai-admin-mode');
            var langSelect = document.getElementById('lpai-admin-lang');
            var toneSelect = document.getElementById('lpai-admin-tone');
            var saveStatus = document.getElementById('lpai-save-status');

            saveBtn.disabled = true;
            if (saveStatus) saveStatus.innerHTML = '<span class="lpai-spinner-sm"></span> Saving...';

            var payload = {
                _token: token,
                gemini: {
                    api_key: keyInput ? keyInput.value.trim() : '',
                    model: modelSelect ? modelSelect.value : 'gemini-3.8-flash'
                },
                settings: {
                    auto_draft_mode: modeSelect ? modeSelect.value : 'open',
                    default_language: langSelect ? langSelect.value : 'English',
                    default_tone: toneSelect ? toneSelect.value : 'professional'
                }
            };

            fetch(urlSave, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(function(r) { return r.json(); }).then(function(res) {
                saveBtn.disabled = false;
                if (saveStatus) {
                    if (res.status === 'success') {
                        saveStatus.innerHTML = '<span style="color:#16a34a">&#10003; ' + lpai_escape_html(res.message) + '</span>';
                        setTimeout(function() { saveStatus.innerHTML = ''; }, 4000);
                    } else {
                        saveStatus.innerHTML = '<span style="color:#ef4444">&#10007; ' + lpai_escape_html(res.message) + '</span>';
                    }
                }
            }).catch(function(err) {
                saveBtn.disabled = false;
                if (saveStatus) saveStatus.innerHTML = '<span style="color:#ef4444">&#10007; ' + lpai_escape_html(err.message) + '</span>';
            });
        };
    }
}
