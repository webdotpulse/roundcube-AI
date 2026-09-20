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
            lpai_init_responses();
        }

        if (task === 'newsletter') {
            lpai_init_newsletter();
        }

        lpai_apply_server_prefs();
        lpai_restore_prefs();
        lpai_bind_events();

        // Render labels on initial page load if present in environment
        if (rcmail.env.lpai_row_labels) {
            Object.keys(rcmail.env.lpai_row_labels).forEach(function(uid) {
                lpai_sync_message_row_label(uid, rcmail.env.lpai_row_labels[uid]);
            });
        }
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

    // Render inbox message row label chips whenever Roundcube renders or updates the message list
    rcmail.addEventListener('plugin.lifeprisma_ai_sync_labels', function(rowLabels) {
        if (rowLabels && typeof rowLabels === 'object') {
            Object.keys(rowLabels).forEach(function(uid) {
                lpai_sync_message_row_label(uid, rowLabels[uid]);
            });
        }
    });

    rcmail.addEventListener('messagelist_update', function() {
        var labels = rcmail.env.lpai_row_labels || {};
        Object.keys(labels).forEach(function(uid) {
            lpai_sync_message_row_label(uid, labels[uid]);
        });
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
    '$Label1': { name: 'To Respond', color: '#1a73e8', bg: '#e8f0fe', class: 'label-1' },
    '$Label2': { name: 'FYI', color: '#5f6368', bg: '#f1f3f4', class: 'label-2' },
    '$Label3': { name: 'Important', color: '#d93025', bg: '#fce8e6', class: 'label-3' },
    '$Label4': { name: 'Marketing & Newsletters', color: '#188038', bg: '#e6f4ea', class: 'label-4' },
    '$Label5': { name: 'ToDo', color: '#e37400', bg: '#fef7e0', class: 'label-5' }
};

function lpai_format_size(bytes) {
    if (!bytes || isNaN(bytes)) return '0 B';
    var k = 1024;
    var sizes = ['B', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function lpai_resolve_label_info(flag) {
    if (!flag) return null;
    var numMatch = String(flag).match(/\$?Label([0-9]+)/i);
    var key = numMatch ? ('LABEL' + numMatch[1]) : String(flag).toUpperCase();
    var idx = numMatch ? numMatch[1] : '1';

    var customLabels = (window.rcmail && rcmail.env && rcmail.env.tb_label_custom_labels) || {};
    var customColors = (window.rcmail && rcmail.env && rcmail.env.tb_label_colors) || {};

    if (customLabels[key]) {
        var rawName = customLabels[key];
        var name = (rawName && rawName !== key && !/^LABEL[0-9]+$/i.test(rawName)) ? rawName : (LPAI_LABEL_DEF[flag] ? LPAI_LABEL_DEF[flag].name : key);
        var color = customColors[key] || (LPAI_LABEL_DEF[flag] ? LPAI_LABEL_DEF[flag].color : '#1a73e8');
        return {
            name: name,
            color: color,
            bg: color + '22',
            class: 'label-' + idx
        };
    }

    if (LPAI_LABEL_DEF[flag]) {
        return LPAI_LABEL_DEF[flag];
    }

    return {
        name: key,
        color: '#1a73e8',
        bg: '#e8f0fe',
        class: 'label-' + idx
    };
}

function lpai_sync_message_row_label(uid, labelFlag) {
    if (!uid || !labelFlag) return;
    var flags = Array.isArray(labelFlag) ? labelFlag : [labelFlag];
    flags = flags.filter(Boolean);
    if (!flags.length) return;

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

        // Clear any previous badges to allow fresh multi-label rendering
        var existingBadges = row.querySelectorAll('.lpai-row-label-badge');
        existingBadges.forEach(function(b) { b.remove(); });

        var subjectCell = row.querySelector('td.subject') || row.querySelector('.subject') || row;
        if (!subjectCell) return;

        var insertTarget = subjectCell.querySelector('a') || subjectCell.firstChild;

        flags.forEach(function(flag) {
            var info = lpai_resolve_label_info(flag);
            if (!info) return;

            row.classList.add(info.class);

            var badge = doc.createElement('span');
            badge.className = 'lpai-row-label-badge ' + info.class;
            badge.style.cssText = 'display:inline-block;padding:1px 6px;margin-right:4px;border-radius:4px;font-size:11px;font-weight:600;color:' + info.color + ';background:' + info.bg + ';border:1px solid ' + info.color + '44;line-height:14px;vertical-align:middle;';
            badge.innerText = info.name;

            if (insertTarget) {
                subjectCell.insertBefore(badge, insertTarget);
            } else {
                subjectCell.appendChild(badge);
            }
        });
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

        // Remove About button from sidebar
        var aboutBtns = doc.querySelectorAll('#layout-menu a.about, #layout-menu a.button-about, #taskmenu a.about, #taskmenu a.button-about, .special-buttons a.about, .special-buttons a.button-about, a.button-about, a.about[onclick*="about"], [data-target="about"]');
        aboutBtns.forEach(function(b) { b.remove(); });

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
                if (force && data && data.message) {
                    rcmail.display_message(data.message, 'error');
                }
            }
        } catch (e) {
            var hub = document.getElementById('lpai-executive-hub');
            if (hub) hub.remove();
            if (force) {
                rcmail.display_message('Failed to load executive triage analysis', 'error');
            }
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
    var lInfo = lpai_resolve_label_info(assignedLabel);
    if (lInfo) {
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
            var subInput = document.getElementById('newsletter-subject') || document.getElementById('_subject') || document.querySelector('input[name="_subject"]');
            if (subInput) {
                subInput.value = first;
                subInput.dispatchEvent(new Event('input', { bubbles: true }));
                subInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (rcmail.display_message) rcmail.display_message('Subject set: ' + first, 'confirmation');
        }
    }).catch(function() {
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

// ========================================
// Settings -> Responses (Canned Responses)
// ========================================
function lpai_init_responses() {
    // If inside responseedit (either iframe or standalone)
    if (document.getElementById('fftext')) {
        lpai_add_response_quick_actions(document);
    }

    // If on settings/responses list page with preferences-frame iframe
    var frame = document.getElementById('preferences-frame');
    if (frame) {
        frame.addEventListener('load', function() {
            setTimeout(function() {
                try {
                    if (frame.contentDocument && frame.contentDocument.getElementById('fftext')) {
                        lpai_add_response_quick_actions(frame.contentDocument);
                    }
                } catch (e) {}
            }, 120);
        });

        // Check if iframe content is already available
        try {
            if (frame.contentDocument && frame.contentDocument.getElementById('fftext')) {
                lpai_add_response_quick_actions(frame.contentDocument);
            }
        } catch (e) {}
    }

    // Mutation observer for dynamically rendered response forms
    var content = document.getElementById('layout-content') || document.body;
    if (content && window.MutationObserver) {
        var obs = new MutationObserver(function() {
            if (document.getElementById('fftext') && !document.getElementById('lpai-qa-bar-response')) {
                lpai_add_response_quick_actions(document);
            }
        });
        obs.observe(content, { childList: true, subtree: true });
    }
}

function lpai_add_response_quick_actions(doc) {
    var targetDoc = doc || document;
    var ta = targetDoc.getElementById('fftext');
    if (!ta) return;

    var existingBar = targetDoc.getElementById('lpai-qa-bar-response');
    if (existingBar) existingBar.remove();

    var bar = targetDoc.createElement('div');
    bar.className = 'lpai-qa-bar lpai-qa-bar-response';
    bar.id = 'lpai-qa-bar-response';

    var label = targetDoc.createElement('span');
    label.className = 'lpai-qa-label';
    label.innerHTML = lpai_icon('sparkles') + ' <span>Gemini</span>';
    bar.appendChild(label);

    // Draft with Gemini
    var cmpBtn = targetDoc.createElement('button');
    cmpBtn.type = 'button';
    cmpBtn.className = 'lpai-qa-btn lpai-qa-reply';
    cmpBtn.innerHTML = lpai_icon('sparkles') + ' <span>Draft with Gemini</span>';
    cmpBtn.onclick = function() {
        lpai_open_panel('response');
        lpai_select_action('compose');
    };
    bar.appendChild(cmpBtn);

    // Fix Grammar
    var fixBtn = targetDoc.createElement('button');
    fixBtn.type = 'button';
    fixBtn.className = 'lpai-qa-btn';
    fixBtn.innerHTML = lpai_icon('fix') + ' <span>Fix Grammar</span>';
    fixBtn.onclick = function() {
        lpai_response_quick('fix', fixBtn, targetDoc);
    };
    bar.appendChild(fixBtn);

    // Rewrite
    var rwBtn = targetDoc.createElement('button');
    rwBtn.type = 'button';
    rwBtn.className = 'lpai-qa-btn';
    rwBtn.innerHTML = lpai_icon('rewrite') + ' <span>Rewrite</span>';
    rwBtn.onclick = function() {
        lpai_open_panel('response');
        lpai_select_action('rewrite');
    };
    bar.appendChild(rwBtn);

    // Suggest Name
    var nameBtn = targetDoc.createElement('button');
    nameBtn.type = 'button';
    nameBtn.className = 'lpai-qa-btn';
    nameBtn.innerHTML = lpai_icon('subject') + ' <span>Suggest Name</span>';
    nameBtn.onclick = function() {
        lpai_suggest_response_name(nameBtn, targetDoc);
    };
    bar.appendChild(nameBtn);

    // Insert at top of td container containing fftext
    var container = ta.closest('td') || ta.parentNode;
    container.insertBefore(bar, container.firstElementChild);

    // Guarantee the editor container and row span full available width across columns
    if (container && container.tagName === 'TD') {
        container.setAttribute('colspan', '2');
        container.classList.add('col-sm-12', 'html-editor');
        var editorTr = container.closest('tr');
        if (editorTr) {
            editorTr.classList.add('form-group', 'row', 'rc-response-editor-row');
        }
    }
}

function lpai_response_quick(action, btn, doc) {
    var targetDoc = doc || document;
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
        subject: lpai_get_response_name(),
        language: lpai_options.language,
        tone: lpai_options.tone,
        model: lpai_options.model,
        view_context: 'response',
        _token: rcmail.env.request_token
    };

    var targetEl = targetDoc.createElement('div');
    if (lpai_stream_controller) lpai_stream_controller.abort();
    lpai_stream_controller = new AbortController();

    lpai_stream_to_element(postData, targetEl, lpai_stream_controller, function(fullText) {
        btn.disabled = false;
        btn.innerHTML = orig;
        if (fullText) {
            lpai_undo_text = content;
            lpai_apply_with_preserve(fullText);
            lpai_show_undo_bar();
            if (rcmail.display_message) rcmail.display_message('Response updated with Gemini', 'confirmation');
        }
    });
}

function lpai_suggest_response_name(btn, doc) {
    var targetDoc = doc || document;
    var content = lpai_get_editor_content();
    if (!content.trim()) {
        if (rcmail.display_message) rcmail.display_message('Write or generate response text first', 'notice');
        return;
    }

    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '&#9203; Generating...';

    var postData = {
        _action: 'plugin.lifeprisma_ai_request',
        ai_action: 'suggest_response_name',
        instruction: '',
        email_body: content.substring(0, 1500),
        reply_text: '',
        subject: '',
        language: lpai_options.language,
        tone: lpai_options.tone,
        model: lpai_options.model,
        view_context: 'response',
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
            var cleanName = data.result.trim().replace(/^["']|["']$/g, '').replace(/[\r\n]+/g, ' ');
            var nameInput = targetDoc.getElementById('ffname');
            if (!nameInput && window.parent && window.parent.document) {
                nameInput = window.parent.document.getElementById('ffname');
            }
            if (!nameInput) {
                var f = document.getElementById('preferences-frame');
                if (f && f.contentDocument) nameInput = f.contentDocument.getElementById('ffname');
            }
            if (nameInput) {
                nameInput.value = cleanName;
                nameInput.dispatchEvent(new Event('change', { bubbles: true }));
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (rcmail.display_message) rcmail.display_message('Response name set: ' + cleanName, 'confirmation');
        }
    }).catch(function() {
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

function lpai_get_response_name() {
    var nameInput = document.getElementById('ffname');
    if (!nameInput && window.parent && window.parent.document) {
        var pf = window.parent.document.getElementById('preferences-frame');
        if (pf && pf.contentDocument) nameInput = pf.contentDocument.getElementById('ffname');
    }
    if (!nameInput) {
        var f = document.getElementById('preferences-frame');
        if (f && f.contentDocument) nameInput = f.contentDocument.getElementById('ffname');
    }
    return nameInput ? nameInput.value.trim() : '';
}

function lpai_auto_fill_response_name(content, doc) {
    var targetDoc = doc || document;
    var nameInput = targetDoc.getElementById('ffname');
    if (!nameInput && window.parent && window.parent.document) {
        var pf = window.parent.document.getElementById('preferences-frame');
        if (pf && pf.contentDocument) nameInput = pf.contentDocument.getElementById('ffname');
    }
    if (!nameInput) {
        var f = document.getElementById('preferences-frame');
        if (f && f.contentDocument) nameInput = f.contentDocument.getElementById('ffname');
    }
    if (nameInput && !nameInput.value.trim() && content) {
        var lines = content.split('\n').map(function(l) { return l.trim(); }).filter(function(l) { return l.length > 0; });
        if (lines.length > 0) {
            var candidate = lines[0].replace(/^[#*>\-\d\.\s]+/, '').replace(/^["']|["']$/g, '');
            if (candidate.length > 40) candidate = candidate.substring(0, 37) + '...';
            if (candidate.length > 2) {
                nameInput.value = candidate;
                nameInput.dispatchEvent(new Event('change', { bubbles: true }));
                nameInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    }
}

// ========================================
// Newsletter Plugin Integration
// ========================================
function lpai_init_newsletter() {
    lpai_panel_context = 'newsletter';

    setTimeout(function() {
        lpai_setup_newsletter_quick_actions();
    }, 150);

    var content = document.getElementById('newsletter-studio') || document.getElementById('layout-content') || document.body;
    if (content && window.MutationObserver) {
        var obs = new MutationObserver(function() {
            if (document.getElementById('newsletter-body') && !document.getElementById('btn-newsletter-ai-open-panel')) {
                lpai_setup_newsletter_quick_actions();
            }
        });
        obs.observe(content, { childList: true, subtree: true });
    }
}

function lpai_setup_newsletter_quick_actions() {
    var ta = document.getElementById('newsletter-body');
    if (!ta) return;

    // Attach subject suggest button
    var aiSubjBtn = document.getElementById('btn-newsletter-ai-subject');
    if (aiSubjBtn && !aiSubjBtn.dataset.bound) {
        aiSubjBtn.dataset.bound = '1';
        aiSubjBtn.onclick = function(e) {
            e.preventDefault();
            lpai_suggest_subject(aiSubjBtn);
        };
    }

    // Attach open panel button
    var panelBtn = document.getElementById('btn-newsletter-ai-open-panel');
    if (panelBtn && !panelBtn.dataset.bound) {
        panelBtn.dataset.bound = '1';
        panelBtn.onclick = function(e) {
            e.preventDefault();
            lpai_open_panel('newsletter');
        };
    }

    // Attach draft button
    var draftBtn = document.getElementById('btn-newsletter-ai-draft');
    if (draftBtn && !draftBtn.dataset.bound) {
        draftBtn.dataset.bound = '1';
        draftBtn.onclick = function(e) {
            e.preventDefault();
            lpai_open_panel('newsletter');
            lpai_select_action('compose');
        };
    }

    // Attach rewrite/polish button
    var rwBtn = document.getElementById('btn-newsletter-ai-rewrite');
    if (rwBtn && !rwBtn.dataset.bound) {
        rwBtn.dataset.bound = '1';
        rwBtn.onclick = function(e) {
            e.preventDefault();
            lpai_open_panel('newsletter');
            lpai_select_action('rewrite');
        };
    }

    // Attach fix grammar button
    var fixBtn = document.getElementById('btn-newsletter-ai-fix');
    if (fixBtn && !fixBtn.dataset.bound) {
        fixBtn.dataset.bound = '1';
        fixBtn.onclick = function(e) {
            e.preventDefault();
            lpai_newsletter_quick('fix', fixBtn);
        };
    }

    // Attach optimize spam button
    var spamBtn = document.getElementById('btn-newsletter-ai-optimize-spam');
    if (spamBtn && !spamBtn.dataset.bound) {
        spamBtn.dataset.bound = '1';
        spamBtn.onclick = function(e) {
            e.preventDefault();
            lpai_newsletter_quick('newsletter_optimize_spam', spamBtn);
        };
    }
}

function lpai_newsletter_quick(action, btn) {
    var content = lpai_get_editor_content();
    if (!content.trim()) {
        if (rcmail.display_message) rcmail.display_message('Write or template some newsletter content first', 'notice');
        return;
    }

    var orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '&#9203; Optimizing...';

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
        view_context: 'newsletter',
        _token: rcmail.env.request_token
    };

    var targetEl = document.createElement('div');
    if (lpai_stream_controller) lpai_stream_controller.abort();
    lpai_stream_controller = new AbortController();

    lpai_stream_to_element(postData, targetEl, lpai_stream_controller, function(fullText) {
        btn.disabled = false;
        btn.innerHTML = orig;
        if (fullText) {
            lpai_undo_text = content;
            lpai_apply_with_preserve(fullText);
            if (rcmail.display_message) rcmail.display_message('Newsletter optimized with Gemini AI', 'confirmation');
        }
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
    // 1. TinyMCE in current window
    if (window.tinymce) {
        var ed = tinymce.get('fftext') || tinymce.get('_message') || tinymce.activeEditor;
        if (ed && typeof ed.getContent === 'function') {
            var c = ed.getContent({ format: 'text' });
            if (c) return c;
        }
    }
    // 2. Textarea in current window
    var ta = document.getElementById('fftext') || document.getElementById('newsletter-body') || document.getElementById('_message');
    if (ta && typeof ta.value === 'string' && ta.value.length > 0) {
        return ta.value;
    }
    // 3. Child iframe (e.g. preferences-frame)
    try {
        var frame = document.getElementById('preferences-frame');
        if (frame && frame.contentWindow) {
            if (frame.contentWindow.tinymce) {
                var edF = frame.contentWindow.tinymce.get('fftext') || frame.contentWindow.tinymce.activeEditor;
                if (edF && typeof edF.getContent === 'function') {
                    var cF = edF.getContent({ format: 'text' });
                    if (cF) return cF;
                }
            }
            var taF = frame.contentDocument ? frame.contentDocument.getElementById('fftext') : null;
            if (taF && typeof taF.value === 'string' && taF.value.length > 0) {
                return taF.value;
            }
        }
    } catch (e) {}
    // 4. Parent window if looking up
    try {
        if (window.parent && window.parent !== window) {
            var pTa = window.parent.document.getElementById('_message');
            if (pTa && typeof pTa.value === 'string') return pTa.value;
        }
    } catch (e) {}

    return ta ? ta.value : '';
}

function lpai_apply_with_preserve(newContent) {
    // 1. Target is a response editor (fftext) in current document
    var respTa = document.getElementById('fftext');
    var respEd = window.tinymce ? (tinymce.get('fftext') || (tinymce.activeEditor && tinymce.activeEditor.id === 'fftext' ? tinymce.activeEditor : null)) : null;

    if (respEd && typeof respEd.setContent === 'function') {
        respEd.setContent(lpai_md_to_html(newContent));
        if (respEd.fire) respEd.fire('change');
        lpai_auto_fill_response_name(newContent);
        return;
    }
    if (respTa) {
        respTa.value = newContent;
        respTa.dispatchEvent(new Event('change', { bubbles: true }));
        respTa.dispatchEvent(new Event('input', { bubbles: true }));
        lpai_auto_fill_response_name(newContent);
        return;
    }

    // Target is Newsletter Studio body (#newsletter-body)
    var newsTa = document.getElementById('newsletter-body');
    if (newsTa) {
        var formattedHtml = (newContent.indexOf('<') >= 0 && newContent.indexOf('>') >= 0) ? newContent : lpai_md_to_html(newContent);
        newsTa.value = formattedHtml;
        newsTa.dispatchEvent(new Event('input', { bubbles: true }));
        newsTa.dispatchEvent(new Event('change', { bubbles: true }));
        if (window.newsletter_app && typeof newsletter_app.runSpamCheck === 'function') {
            newsletter_app.runSpamCheck();
        }
        return;
    }

    // 2. Target is inside child iframe (#preferences-frame)
    try {
        var frame = document.getElementById('preferences-frame');
        if (frame && frame.contentWindow) {
            var fWin = frame.contentWindow;
            var fDoc = frame.contentDocument;
            var fEd = fWin.tinymce ? (fWin.tinymce.get('fftext') || fWin.tinymce.activeEditor) : null;
            if (fEd && typeof fEd.setContent === 'function') {
                fEd.setContent(lpai_md_to_html(newContent));
                if (fEd.fire) fEd.fire('change');
                lpai_auto_fill_response_name(newContent, fDoc);
                return;
            }
            var fTa = fDoc ? fDoc.getElementById('fftext') : null;
            if (fTa) {
                fTa.value = newContent;
                fTa.dispatchEvent(new Event('change', { bubbles: true }));
                fTa.dispatchEvent(new Event('input', { bubbles: true }));
                lpai_auto_fill_response_name(newContent, fDoc);
                return;
            }
        }
    } catch (e) {}

    // 3. Compose email body editor (_message)
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
    var sub = document.getElementById('newsletter-subject') || document.getElementById('_subject') || document.querySelector('input[name="_subject"]');
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
    // If in an iframe, prioritize the parent window's modal if available
    try {
        if (window.parent && window.parent !== window && window.parent.document) {
            var pPanel = window.parent.document.getElementById('lpai-panel');
            var pOverlay = window.parent.document.getElementById('lpai-overlay');
            if (pPanel && pOverlay) {
                return { panel: pPanel, overlay: pOverlay };
            }
        }
    } catch (e) {}

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

function lpai_get_modal_doc() {
    var els = lpai_get_modal_elements();
    if (els.panel && els.panel.ownerDocument) {
        return els.panel.ownerDocument;
    }
    return document;
}

function lpai_close_all_custom_dropdowns() {
    var doc = lpai_get_modal_doc();
    doc.querySelectorAll('.lpai-custom-select.open').forEach(function(cs) {
        cs.classList.remove('open');
        var dd = cs.querySelector('.lpai-custom-dropdown');
        if (dd) dd.style.display = 'none';
        var tr = cs.querySelector('.lpai-custom-select-trigger');
        if (tr) tr.setAttribute('aria-expanded', 'false');
    });
}

function lpai_init_custom_selects() {
    var doc = lpai_get_modal_doc();
    var selects = doc.querySelectorAll('.lpai-select');
    selects.forEach(function(sel) {
        if (!sel.id) return;
        var parent = sel.closest('.lpai-custom-select');
        if (!parent) {
            if (sel.parentElement && sel.parentElement.classList.contains('lpai-custom-select')) {
                parent = sel.parentElement;
            } else {
                var wrapper = doc.createElement('div');
                wrapper.className = 'lpai-custom-select';
                wrapper.dataset.selectId = sel.id;
                sel.parentNode.insertBefore(wrapper, sel);
                wrapper.appendChild(sel);
                parent = wrapper;
            }
        }

        var trigger = parent.querySelector('.lpai-custom-select-trigger');
        var dropdown = parent.querySelector('.lpai-custom-dropdown');

        if (!trigger) {
            trigger = doc.createElement('button');
            trigger.type = 'button';
            trigger.className = 'lpai-custom-select-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');

            var labelSpan = doc.createElement('span');
            labelSpan.className = 'lpai-custom-select-label';
            var selectedOpt = sel.options[sel.selectedIndex] || sel.options[0];
            labelSpan.textContent = selectedOpt ? selectedOpt.text : '';

            var arrowSvg = '<svg class="lpai-custom-select-arrow" viewBox="0 0 20 20" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8l4 4 4-4"/></svg>';
            trigger.appendChild(labelSpan);
            trigger.insertAdjacentHTML('beforeend', arrowSvg);
            parent.insertBefore(trigger, sel);
        }

        if (!dropdown) {
            dropdown = doc.createElement('div');
            dropdown.className = 'lpai-custom-dropdown';
            dropdown.setAttribute('role', 'listbox');
            dropdown.style.display = 'none';
            parent.insertBefore(dropdown, sel);
        }

        dropdown.innerHTML = '';
        Array.from(sel.options).forEach(function(opt) {
            var item = doc.createElement('div');
            item.className = 'lpai-dropdown-option' + (opt.value === sel.value ? ' selected' : '');
            item.dataset.value = opt.value;
            item.setAttribute('role', 'option');
            item.setAttribute('tabindex', '0');

            var textSpan = doc.createElement('span');
            textSpan.textContent = opt.text;
            item.appendChild(textSpan);

            var checkSvg = '<svg class="lpai-option-check" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
            item.insertAdjacentHTML('beforeend', checkSvg);

            item.addEventListener('click', function(e) {
                e.stopPropagation();
                sel.value = opt.value;
                if (sel.id === 'lpai-model-select') {
                    lpai_options.model = sel.value;
                    var modalDoc = lpai_get_modal_doc();
                    var tag = modalDoc ? modalDoc.querySelector('.lpai-model-tag') : null;
                    if (tag) tag.textContent = sel.value;
                } else if (sel.id === 'lpai-tone-select') {
                    lpai_options.tone = sel.value;
                } else if (sel.id === 'lpai-lang-select') {
                    lpai_options.language = sel.value;
                }
                lpai_save_prefs();
                lpai_sync_custom_ui_for_select(sel);
                sel.dispatchEvent(new Event('change', { bubbles: true }));
                lpai_close_all_custom_dropdowns();
                trigger.focus();
            });

            item.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    e.stopPropagation();
                    item.click();
                }
            });

            dropdown.appendChild(item);
        });

        if (!trigger._lpai_bound) {
            trigger._lpai_bound = true;
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = parent.classList.contains('open');
                lpai_close_all_custom_dropdowns();
                if (!isOpen) {
                    parent.classList.add('open');
                    dropdown.style.display = 'block';
                    trigger.setAttribute('aria-expanded', 'true');
                    var selItem = dropdown.querySelector('.lpai-dropdown-option.selected');
                    if (selItem) selItem.scrollIntoView({ block: 'nearest' });
                }
            });

            trigger.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    if (!parent.classList.contains('open')) {
                        trigger.click();
                    } else {
                        var items = Array.from(dropdown.querySelectorAll('.lpai-dropdown-option'));
                        var idx = items.findIndex(function(it) { return it.dataset.value === sel.value; });
                        if (e.key === 'ArrowDown') {
                            var nextIdx = (idx + 1) % items.length;
                            items[nextIdx].click();
                        } else if (e.key === 'ArrowUp') {
                            var prevIdx = (idx - 1 + items.length) % items.length;
                            items[prevIdx].click();
                        }
                    }
                }
            });
        }

        if (!sel._lpai_bound) {
            sel._lpai_bound = true;
            sel.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                trigger.click();
            });
            sel.addEventListener('change', function() {
                if (sel.id === 'lpai-model-select') {
                    lpai_options.model = sel.value;
                    var modalDoc = lpai_get_modal_doc();
                    var tag = modalDoc ? modalDoc.querySelector('.lpai-model-tag') : null;
                    if (tag) tag.textContent = sel.value;
                } else if (sel.id === 'lpai-tone-select') {
                    lpai_options.tone = sel.value;
                } else if (sel.id === 'lpai-lang-select') {
                    lpai_options.language = sel.value;
                }
                lpai_save_prefs();
                lpai_sync_custom_ui_for_select(sel);
            });
        }
    });
}

function lpai_sync_custom_ui_for_select(sel) {
    if (!sel) return;
    var parent = sel.closest ? sel.closest('.lpai-custom-select') : null;
    if (!parent) return;
    var labelEl = parent.querySelector('.lpai-custom-select-label');
    var opt = sel.options && sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex] : null;
    if (labelEl && opt) {
        labelEl.textContent = opt.text;
    }
    var items = parent.querySelectorAll('.lpai-dropdown-option');
    items.forEach(function(it) {
        var isSel = (it.dataset.value === sel.value);
        it.classList.toggle('selected', isSel);
        it.setAttribute('aria-selected', isSel ? 'true' : 'false');
    });
}

function lpai_sync_select_elements() {
    var doc = lpai_get_modal_doc();
    var mSel = doc.getElementById('lpai-model-select');
    var tSel = doc.getElementById('lpai-tone-select');
    var lSel = doc.getElementById('lpai-lang-select');
    var tag = doc.querySelector('.lpai-model-tag');

    if (mSel && lpai_options.model) {
        mSel.value = lpai_options.model;
    }
    if (tSel && lpai_options.tone) {
        tSel.value = lpai_options.tone;
    }
    if (lSel && lpai_options.language) {
        lSel.value = lpai_options.language;
    }
    if (tag) {
        tag.textContent = (mSel && mSel.value) ? mSel.value : lpai_options.model;
    }

    [mSel, tSel, lSel].forEach(function(sel) {
        lpai_sync_custom_ui_for_select(sel);
    });
}

function lpai_open_panel(context) {
    var els = lpai_get_modal_elements();
    var panel = els.panel;
    var overlay = els.overlay;
    if (!panel || !overlay) return;

    if (!context) {
        if (document.getElementById('fftext') || (document.getElementById('preferences-frame') && document.getElementById('preferences-frame').contentDocument && document.getElementById('preferences-frame').contentDocument.getElementById('fftext'))) {
            context = 'response';
        } else if (document.getElementById('newsletter-studio') || rcmail.env.task === 'newsletter') {
            context = 'newsletter';
        } else if (rcmail.env.task === 'mail' && (rcmail.env.action === 'show' || rcmail.env.action === 'preview')) {
            context = 'read';
        } else {
            context = 'compose';
        }
    }

    lpai_panel_context = context || 'compose';
    panel.style.display = 'flex';
    overlay.style.display = 'block';

    lpai_init_custom_selects();
    lpai_sync_select_elements();

    var doc = panel.ownerDocument || document;
    var titleEl = doc.getElementById('lpai-title');
    var applyBtn = doc.getElementById('lpai-apply');
    var subjBtn = doc.querySelector('.lpai-action-btn[data-action="suggest_subject"] span');

    if (lpai_panel_context === 'response') {
        if (titleEl) titleEl.textContent = 'Gemini Response Assistant';
        if (applyBtn) applyBtn.textContent = 'Insert into Response';
        if (subjBtn) subjBtn.textContent = 'Suggest Name';
    } else if (lpai_panel_context === 'newsletter') {
        if (titleEl) titleEl.textContent = 'Gemini Newsletter Assistant';
        if (applyBtn) applyBtn.textContent = 'Insert into Newsletter';
        if (subjBtn) subjBtn.textContent = 'Subject Lines';
    } else {
        if (titleEl) titleEl.textContent = 'Gemini Assistant';
        if (applyBtn) applyBtn.textContent = 'Insert into Email';
        if (subjBtn) subjBtn.textContent = 'Subject Lines';
    }

    var input = doc.getElementById('lpai-input');
    if (input) {
        lpai_select_action(lpai_current_action || 'compose');
        input.focus();
    }
}

function lpai_close_panel() {
    lpai_close_all_custom_dropdowns();
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
    var doc = lpai_get_modal_doc();
    var btns = doc.querySelectorAll('.lpai-action-btn');
    btns.forEach(function(b) {
        b.classList.toggle('active', b.dataset.action === action);
    });

    var input = doc.getElementById('lpai-input');
    if (input) {
        var placeholders = {
            'compose': (lpai_panel_context === 'response') ? 'What canned response should Gemini write? (e.g. Out of office, Billing confirmation...)' :
                       (lpai_panel_context === 'newsletter') ? 'What newsletter should Gemini write? (e.g. Monthly roundup, special launch, exclusive member promo...)' :
                       'What should Gemini write?',
            'rewrite': (lpai_panel_context === 'response') ? 'How should Gemini rephrase this response?' :
                       (lpai_panel_context === 'newsletter') ? 'How should Gemini enhance this newsletter? (e.g. More engaging, add CTA, punchier headings...)' :
                       'How should Gemini rephrase this?',
            'fix': 'Correcting grammar & clarity...',
            'translate': 'Translating into selected language...',
            'summarize': 'Extracting key takeaways...',
            'suggest_subject': (lpai_panel_context === 'response') ? 'Suggesting a clear name for this response...' :
                               (lpai_panel_context === 'newsletter') ? 'Generating 5 high-converting, spam-compliant newsletter subject lines...' :
                               'Generating high-impact subject lines...'
        };
        input.placeholder = placeholders[action] || 'Instruction for Gemini...';
    }
}

function lpai_submit() {
    var doc = lpai_get_modal_doc();
    var input = doc.getElementById('lpai-input');
    var instruction = input ? input.value.trim() : '';
    var action = lpai_current_action || 'compose';

    var preview = doc.getElementById('lpai-preview');
    var previewContent = doc.getElementById('lpai-preview-content');
    var applyBtn = doc.getElementById('lpai-apply');
    var copyBtn = doc.getElementById('lpai-copy');
    var cancelBtn = doc.getElementById('lpai-cancel');
    var generateBtn = doc.getElementById('lpai-generate');

    if (preview) preview.style.display = 'block';
    if (applyBtn) applyBtn.style.display = 'none';
    if (copyBtn) copyBtn.style.display = 'none';
    if (cancelBtn) cancelBtn.style.display = 'inline-flex';
    if (generateBtn) generateBtn.style.display = 'none';

    var contextText = '';
    if (lpai_panel_context === 'compose' || lpai_panel_context === 'response' || lpai_panel_context === 'newsletter') {
        contextText = lpai_get_editor_content();
    } else {
        contextText = lpai_get_message_text();
    }

    var modelSelect = doc.getElementById('lpai-model-select');
    var toneSelect = doc.getElementById('lpai-tone-select');
    var langSelect = doc.getElementById('lpai-lang-select');

    if (modelSelect && modelSelect.value) lpai_options.model = modelSelect.value;
    if (toneSelect && toneSelect.value) lpai_options.tone = toneSelect.value;
    if (langSelect && langSelect.value) lpai_options.language = langSelect.value;
    lpai_save_prefs();

    var model = modelSelect ? modelSelect.value : lpai_options.model;
    var tone = toneSelect ? toneSelect.value : lpai_options.tone;
    var language = langSelect ? langSelect.value : lpai_options.language;

    var postData = {
        _action: 'plugin.lifeprisma_ai_stream',
        ai_action: (action === 'suggest_subject' && lpai_panel_context === 'response') ? 'suggest_response_name' :
                   (action === 'compose' && lpai_panel_context === 'newsletter') ? 'newsletter_draft' : action,
        instruction: instruction,
        email_body: (lpai_panel_context === 'compose' || lpai_panel_context === 'response' || lpai_panel_context === 'newsletter') ? contextText : '',
        reply_text: (lpai_panel_context === 'read') ? contextText.substring(0, 3500) : '',
        subject: (lpai_panel_context === 'response') ? lpai_get_response_name() : lpai_get_subject(),
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

        var costSpan = doc.getElementById('lpai-token-cost');
        if (costSpan) {
            var cost = lpai_estimate_cost(model, tokens.input, tokens.output);
            costSpan.textContent = cost ? ('Cost: ' + cost) : '';
        }
    });
}

function lpai_apply_result() {
    if (!lpai_last_result) return;
    if (lpai_panel_context === 'response' && lpai_current_action === 'suggest_subject') {
        var cleanName = lpai_last_result.trim().replace(/^["']|["']$/g, '').replace(/[\r\n]+/g, ' ');
        var nameInput = document.getElementById('ffname') || (window.parent && window.parent.document.getElementById('ffname'));
        if (!nameInput) {
            var f = document.getElementById('preferences-frame');
            if (f && f.contentDocument) nameInput = f.contentDocument.getElementById('ffname');
        }
        if (nameInput) {
            nameInput.value = cleanName;
            nameInput.dispatchEvent(new Event('change', { bubbles: true }));
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        lpai_close_panel();
        if (rcmail.display_message) rcmail.display_message('Response name set', 'confirmation');
        return;
    }
    if (lpai_panel_context === 'newsletter' && lpai_current_action === 'suggest_subject') {
        var subInput = document.getElementById('newsletter-subject') || document.getElementById('_subject');
        if (subInput) {
            var cleanSub = lpai_last_result.trim().replace(/^["']|["']$/g, '').replace(/[\r\n]+/g, ' ');
            var lines = cleanSub.split('\n').filter(function(l) { return l.trim().length > 0; });
            if (lines.length > 0) cleanSub = lines[0].replace(/^\d+\.\s*/, '').replace(/^["']|["']$/g, '');
            subInput.value = cleanSub;
            subInput.dispatchEvent(new Event('input', { bubbles: true }));
            subInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        lpai_close_panel();
        if (rcmail.display_message) rcmail.display_message('Newsletter subject set', 'confirmation');
        return;
    }
    lpai_apply_with_preserve(lpai_last_result);
    lpai_close_panel();
    var msg = (lpai_panel_context === 'response') ? 'Gemini response inserted' :
              (lpai_panel_context === 'newsletter') ? 'Gemini newsletter content inserted' :
              'Gemini text inserted';
    if (rcmail.display_message) rcmail.display_message(msg, 'confirmation');
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

    var rates = null;
    var geminiEnv = (window.rcmail && rcmail.env && rcmail.env.lpai_gemini) || {};
    var pricingTable = geminiEnv.pricing || {};
    if (model && pricingTable[model]) {
        var mRates = pricingTable[model];
        rates = [Number(mRates.input) || 0, Number(mRates.output) || 0];
    }

    if (!rates && model && model.indexOf('lite') >= 0) {
        rates = [0.075, 0.30];
    } else if (!rates && model && model.indexOf('pro') >= 0) {
        rates = [1.25, 10.00];
    } else if (!rates) {
        rates = [0.30, 2.50]; // Gemini 3.8 / 3.7 / 3.6 / 3.5 Flash default ($ per 1M tokens)
    }

    var cost = (inp * rates[0] + out * rates[1]) / 1000000;
    if (cost === 0) return '$0.00';
    if (cost < 0.0001) return '$' + cost.toFixed(6);
    return '$' + cost.toFixed(4);
}

// ========================================
// Event Listeners
// ========================================
function lpai_bind_events() {
    var docs = [document];
    try {
        if (window.parent && window.parent.document && docs.indexOf(window.parent.document) === -1) {
            docs.push(window.parent.document);
        }
        if (window.top && window.top.document && docs.indexOf(window.top.document) === -1) {
            docs.push(window.top.document);
        }
    } catch (e) {}

    docs.forEach(function(d) {
        d.addEventListener('change', function(e) {
            var t = e.target;
            if (!t) return;
            if (t.id === 'lpai-model-select') {
                lpai_options.model = t.value;
                lpai_save_prefs();
                var modalDoc = lpai_get_modal_doc();
                var tag = modalDoc.querySelector('.lpai-model-tag');
                if (tag) tag.textContent = t.value;
                lpai_sync_custom_ui_for_select(t);
            } else if (t.id === 'lpai-tone-select') {
                lpai_options.tone = t.value;
                lpai_save_prefs();
                lpai_sync_custom_ui_for_select(t);
            } else if (t.id === 'lpai-lang-select') {
                lpai_options.language = t.value;
                lpai_save_prefs();
                lpai_sync_custom_ui_for_select(t);
            }
        });

        d.addEventListener('click', function(e) {
            if (!e.target.closest || !e.target.closest('.lpai-custom-select')) {
                lpai_close_all_custom_dropdowns();
            }
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

        d.addEventListener('keydown', function(e) {
            if (e.target.id === 'lpai-input' && e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                lpai_submit();
            }
            if (e.key === 'Escape') {
                var doc = lpai_get_modal_doc();
                var anyOpen = doc.querySelector('.lpai-custom-select.open');
                if (anyOpen) {
                    lpai_close_all_custom_dropdowns();
                    return;
                }
                lpai_close_panel();
            }
            if (e.altKey && (e.key === 'a' || e.key === 'A')) {
                e.preventDefault();
                var els = lpai_get_modal_elements();
                var panel = els.panel;
                if (panel && panel.style.display !== 'none') {
                    lpai_close_panel();
                } else {
                    var action = rcmail.env.action;
                    var ctx = (action === 'show' || action === 'preview') ? 'read' : 'compose';
                    lpai_open_panel(ctx);
                }
            }
        });
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
