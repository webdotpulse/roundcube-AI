/**
 * Thread Drafts Plugin Client Script
 *
 * Handles UI badges, row classes, and seamless compose navigation for
 * active drafts and sent replies embedded into Roundcube conversation threads.
 * Also enforces that conversation threads standard start collapsed when opening the Inbox.
 */

(function () {
    if (!window.rcmail) {
        return;
    }

    // Intercept open_compose_step to guarantee draft UID is purely numeric
    var orig_open_compose_step = rcmail.open_compose_step;
    rcmail.open_compose_step = function (p) {
        if (p && p._draft_uid && String(p._draft_uid).indexOf('-') !== -1) {
            p._draft_uid = String(p._draft_uid).split('-')[0];
        }
        return orig_open_compose_step.apply(this, arguments);
    };

    /**
     * Determine if current or specified folder is the Inbox
     */
    function is_inbox_folder(mbox) {
        var cur_mbox = mbox || (rcmail.env ? rcmail.env.mailbox : '');
        if (!cur_mbox && window.location) {
            var match = window.location.search.match(/[?&]_mbox=([^&]+)/);
            if (match) {
                cur_mbox = decodeURIComponent(match[1]);
            }
        }
        return !cur_mbox || cur_mbox.toUpperCase() === 'INBOX';
    }

    /**
     * Check if auto-collapse inbox is enabled
     */
    function is_auto_collapse_enabled() {
        return !rcmail.env || rcmail.env.thread_drafts_auto_collapse_inbox !== false;
    }

    /**
     * Check if a message UID corresponds to an embedded draft
     */
    function is_draft_message(uid) {
        if (!uid) {
            return false;
        }

        var msg = rcmail.env.messages ? rcmail.env.messages[uid] : null;
        if (msg) {
            if (msg.flags && (msg.flags.is_draft || msg.flags.draft)) {
                return true;
            }
            if (msg.mbox && rcmail.env.drafts_mailbox && msg.mbox === rcmail.env.drafts_mailbox) {
                return true;
            }
        }

        if (rcmail.env.drafts_mailbox && /^[0-9]+-(.+)$/.test(uid)) {
            return RegExp.$1 === rcmail.env.drafts_mailbox;
        }

        return false;
    }

    /**
     * Check if a message UID corresponds to an embedded sent reply
     */
    function is_reply_message(uid) {
        if (!uid) {
            return false;
        }

        var msg = rcmail.env.messages ? rcmail.env.messages[uid] : null;
        if (msg) {
            if (msg.flags && (msg.flags.is_reply || msg.flags.is_sent)) {
                return true;
            }
            if (msg.mbox && rcmail.env.sent_mailbox && msg.mbox === rcmail.env.sent_mailbox) {
                return true;
            }
        }

        if (rcmail.env.sent_mailbox && /^[0-9]+-(.+)$/.test(uid)) {
            return RegExp.$1 === rcmail.env.sent_mailbox;
        }

        return false;
    }

    /**
     * Update thread root message row with a draft indicator badge
     */
    function update_thread_root_badge(uid) {
        if (!rcmail.env.thread_drafts_show_root_badge || !rcmail.message_list) {
            return;
        }

        var root_uid = rcmail.message_list.find_root(uid);
        if (!root_uid || root_uid === uid) {
            return;
        }

        var root_row = rcmail.message_list.rows[root_uid];
        if (!root_row || !root_row.obj) {
            return;
        }

        var $rootObj = $(root_row.obj);
        $rootObj.addClass('thread-has-draft');

        var $subjectSpan = $rootObj.find('td.subject a span');
        if ($subjectSpan.length && !$subjectSpan.find('.rcube-thread-root-draft-badge').length) {
            var label = rcmail.gettext('draft', 'thread_drafts') || 'Draft';
            var title = rcmail.gettext('draft_in_thread', 'thread_drafts') || 'This conversation contains an active draft';

            var badgeHtml = '<span class="rcube-thread-root-draft-badge" title="' + rcmail.quote_html(title) + '">'
                + '<span class="draft-badge-icon">&#9998;</span> '
                + rcmail.quote_html(label)
                + '</span> ';

            $subjectSpan.prepend(badgeHtml);
        }
    }

    /**
     * Update thread root message row with a sent replies indicator
     */
    function update_thread_root_reply_badge(uid) {
        if (!rcmail.message_list) {
            return;
        }

        var root_uid = rcmail.message_list.find_root(uid);
        if (!root_uid || root_uid === uid) {
            return;
        }

        var root_row = rcmail.message_list.rows[root_uid];
        if (!root_row || !root_row.obj) {
            return;
        }

        var $rootObj = $(root_row.obj);
        $rootObj.addClass('thread-has-replies');
    }

    /**
     * Enhance a draft row with classes and badges
     */
    function enhance_draft_row(uid, row) {
        var $row = $(row);
        $row.addClass('thread-draft-row');

        var $subjectSpan = $row.find('td.subject a span');
        if (!$subjectSpan.length) {
            $subjectSpan = $row.find('td.subject span');
        }

        if ($subjectSpan.length && !$subjectSpan.find('.rcube-thread-draft-badge').length) {
            var label = rcmail.gettext('draft', 'thread_drafts') || 'Draft';

            var badgeHtml = '<span class="rcube-thread-draft-badge">'
                + '<span class="draft-badge-icon">&#9998;</span> '
                + rcmail.quote_html(label)
                + '</span> ';

            $subjectSpan.prepend(badgeHtml);
        }

        // Attach click handler to ensure clean opening of draft compose
        $row.find('td.subject a').off('click.thread_drafts').on('click.thread_drafts', function (e) {
            if (rcube_event.get_modifier(e)) {
                return true;
            }

            var cleanUid = String(uid).split('-')[0];
            rcmail.open_compose_step({
                _draft_uid: cleanUid,
                _mbox: rcmail.env.drafts_mailbox
            });

            return false;
        });

        // Mark thread root
        update_thread_root_badge(uid);
    }

    /**
     * Enhance a sent reply row with classes and badges
     */
    function enhance_reply_row(uid, row) {
        var $row = $(row);
        $row.addClass('thread-reply-row thread-sent-row');

        var $subjectSpan = $row.find('td.subject a span');
        if (!$subjectSpan.length) {
            $subjectSpan = $row.find('td.subject span');
        }

        var showReplyBadge = rcmail.env.thread_drafts_show_reply_badge !== false;
        if (showReplyBadge && $subjectSpan.length && !$subjectSpan.find('.rcube-thread-reply-badge').length) {
            var label = rcmail.gettext('reply', 'thread_drafts') || 'Sent';

            var badgeHtml = '<span class="rcube-thread-reply-badge">'
                + '<span class="reply-badge-icon">&#8617;</span> '
                + rcmail.quote_html(label)
                + '</span> ';

            $subjectSpan.prepend(badgeHtml);
        }

        // Mark thread root
        update_thread_root_reply_badge(uid);
    }

    /**
     * Bulletproof collapse of all conversation threads in the Inbox across:
     * 1. Message list widget API (collapse_all)
     * 2. Message list rows internal state (expanded = false, update_expando)
     * 3. Direct DOM table rows (display: none on all child rows, chevron to collapsed)
     */
    function ensure_inbox_threads_collapsed(mbox) {
        if (!rcmail || !is_inbox_folder(mbox) || !is_auto_collapse_enabled()) {
            return;
        }

        rcmail.env.autoexpand_threads = 0;

        var list = rcmail.message_list;
        if (list) {
            if (typeof list.collapse_all === 'function') {
                try {
                    list.collapse_all();
                } catch (e) {}
            }

            // Layer 2: Synchronize internal rows state
            if (list.rows) {
                $.each(list.rows, function (uid, r) {
                    if (!r) return;
                    var msg = (rcmail.env.messages && rcmail.env.messages[uid]) ? rcmail.env.messages[uid] : null;
                    var depth = (r.depth !== undefined) ? r.depth : (msg && msg.depth !== undefined ? msg.depth : 0);
                    var has_children = r.has_children || (msg ? msg.has_children : false);
                    var parent_uid = r.parent_uid || (msg ? msg.parent_uid : null);

                    if (has_children && !depth) {
                        r.expanded = false;
                        if (msg) msg.expanded = false;
                        if (typeof list.update_expando === 'function') {
                            list.update_expando(r.id, false);
                        }
                        $('#rcmexpando' + r.id).removeClass('expanded').addClass('collapsed');
                        if (r.obj) {
                            $(r.obj).removeClass('expanded');
                            $(r.obj).find('.threadtoggle, [id^="rcmexpando"]').removeClass('expanded').addClass('collapsed');
                        }
                    } else if (depth > 0 || (parent_uid && parent_uid != 0)) {
                        r.expanded = false;
                        if (msg) msg.expanded = false;
                        if (r.obj) {
                            $(r.obj).removeClass('expanded').css('display', 'none');
                            if (r.obj.style) {
                                r.obj.style.display = 'none';
                            }
                        }
                    }
                });
            }
        }

        // Layer 3: Direct DOM traversal to ensure all child rows are hidden
        var $tbody = $('#messagelist tbody, table.messagelist > tbody');
        if ($tbody.length) {
            var active_thread_collapsed = false;
            $tbody.children('tr').each(function () {
                var $tr = $(this);
                var $expando = $tr.find('td.threads div, .threadtoggle, [id^="rcmexpando"]');
                var is_root = $expando.length > 0;
                var has_branch = $tr.find('span.branch, td.subject span.branch').length > 0;
                var has_padding = ($tr.find('td.subject').attr('style') || '').indexOf('padding-left') !== -1
                    || ($tr.attr('style') || '').indexOf('padding-left') !== -1;
                var has_child_class = $tr.hasClass('thread-child') || $tr.hasClass('subthread');
                var is_child = has_branch || has_padding || has_child_class;

                if (is_root) {
                    $expando.removeClass('expanded').addClass('collapsed');
                    $tr.removeClass('expanded');
                    active_thread_collapsed = true;
                } else if (is_child || active_thread_collapsed) {
                    var uid = $tr.data('uid') || this.uid;
                    var r = (list && list.rows && uid) ? list.rows[uid] : null;
                    var is_known_child = (r && (r.depth > 0 || r.parent_uid))
                        || (rcmail.env.messages && uid && rcmail.env.messages[uid] && (rcmail.env.messages[uid].depth > 0 || rcmail.env.messages[uid].parent_uid));

                    if (is_child || is_known_child) {
                        $tr.removeClass('expanded').css('display', 'none');
                        this.style.display = 'none';
                    } else {
                        active_thread_collapsed = false;
                    }
                }
            });
        }

        if (list && typeof list.resize === 'function') {
            try {
                list.resize();
            } catch (e) {}
        }
    }

    // Intercept rcmail.expand_threads to prevent auto-expanding Inbox threads
    if (typeof rcmail.expand_threads === 'function') {
        var orig_expand_threads = rcmail.expand_threads;
        rcmail.expand_threads = function () {
            if (is_inbox_folder() && is_auto_collapse_enabled()) {
                rcmail.env.autoexpand_threads = 0;
                ensure_inbox_threads_collapsed();
                return false;
            }
            return orig_expand_threads.apply(this, arguments);
        };
    }

    // Intercept init_threads so initial thread setup on Inbox stays collapsed
    if (typeof rcmail.init_threads === 'function') {
        var orig_init_threads = rcmail.init_threads;
        rcmail.init_threads = function (roots, mbox) {
            var is_inbox = is_inbox_folder(mbox);
            if (is_inbox && is_auto_collapse_enabled()) {
                rcmail.env.autoexpand_threads = 0;
            }

            var ret = orig_init_threads.apply(this, arguments);

            if (is_inbox && is_auto_collapse_enabled()) {
                ensure_inbox_threads_collapsed(mbox);
            }

            return ret;
        };
    }

    // Intercept add_message_row so incoming rows during Inbox render default to collapsed
    if (typeof rcmail.add_message_row === 'function') {
        var orig_add_message_row = rcmail.add_message_row;
        rcmail.add_message_row = function (uid, cols, flags, attop) {
            if (is_inbox_folder(flags ? flags.mbox : null) && is_auto_collapse_enabled()) {
                rcmail.env.autoexpand_threads = 0;
            }
            return orig_add_message_row.apply(this, arguments);
        };
    }

    // Intercept select_folder & list_mailbox so switching back to Inbox collapses threads
    if (typeof rcmail.select_folder === 'function') {
        var orig_select_folder = rcmail.select_folder;
        rcmail.select_folder = function (mbox) {
            if (is_inbox_folder(mbox) && is_auto_collapse_enabled()) {
                rcmail.env.autoexpand_threads = 0;
            }
            return orig_select_folder.apply(this, arguments);
        };
    }

    if (typeof rcmail.list_mailbox === 'function') {
        var orig_list_mailbox = rcmail.list_mailbox;
        rcmail.list_mailbox = function (mbox) {
            if (is_inbox_folder(mbox) && is_auto_collapse_enabled()) {
                rcmail.env.autoexpand_threads = 0;
            }
            return orig_list_mailbox.apply(this, arguments);
        };
    }

    // Listen for new rows inserted into message list
    rcmail.addEventListener('insertrow', function (props) {
        if (!props || !props.uid || !props.row) {
            return;
        }

        if (is_draft_message(props.uid)) {
            enhance_draft_row(props.uid, props.row);
        } else if (is_reply_message(props.uid)) {
            enhance_reply_row(props.uid, props.row);
        }

        if (is_inbox_folder() && is_auto_collapse_enabled()) {
            var rowObj = props.row.obj || props.row;
            if (props.row.depth > 0 || props.row.parent_uid) {
                $(rowObj).removeClass('expanded').css('display', 'none');
            }
        }
    });

    // Ensure threads are collapsed when response for list action is processed
    rcmail.addEventListener('responsebefore', function (props) {
        if (is_inbox_folder() && is_auto_collapse_enabled()) {
            rcmail.env.autoexpand_threads = 0;
        }
    });

    rcmail.addEventListener('responseafterlist', function () {
        if (is_inbox_folder()) {
            ensure_inbox_threads_collapsed();
        }
    });

    rcmail.addEventListener('responseafter', function (props) {
        if (is_inbox_folder() && props && props.response && (props.response.action === 'list' || props.response.action === 'check-recent')) {
            ensure_inbox_threads_collapsed();
        }
    });

    rcmail.addEventListener('init', function () {
        if (is_inbox_folder()) {
            ensure_inbox_threads_collapsed();
        }
    });

    // Check existing rows after list update in case roots need badge refreshes
    rcmail.addEventListener('listupdate', function () {
        if (!rcmail.message_list || !rcmail.message_list.rows) {
            return;
        }

        $.each(rcmail.message_list.rows, function (uid, row) {
            if (is_draft_message(uid)) {
                if (row.obj && !$(row.obj).hasClass('thread-draft-row')) {
                    enhance_draft_row(uid, row.obj);
                } else {
                    update_thread_root_badge(uid);
                }
            } else if (is_reply_message(uid)) {
                if (row.obj && !$(row.obj).hasClass('thread-reply-row')) {
                    enhance_reply_row(uid, row.obj);
                } else {
                    update_thread_root_reply_badge(uid);
                }
            }
        });
    });

    // Immediate and scheduled checks on DOM ready
    $(document).ready(function () {
        if (is_inbox_folder()) {
            ensure_inbox_threads_collapsed();
            setTimeout(function () { ensure_inbox_threads_collapsed(); }, 50);
            setTimeout(function () { ensure_inbox_threads_collapsed(); }, 150);
            setTimeout(function () { ensure_inbox_threads_collapsed(); }, 350);
        }
    });
})();
