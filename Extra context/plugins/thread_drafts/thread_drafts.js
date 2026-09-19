/**
 * Thread Drafts Plugin Client Script
 *
 * Handles UI badges, row classes, and seamless compose navigation for
 * active drafts embedded into Roundcube conversation threads.
 */

$(document).ready(function () {
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

    // Listen for new rows inserted into message list
    rcmail.addEventListener('insertrow', function (props) {
        if (!props || !props.uid || !props.row) {
            return;
        }

        if (is_draft_message(props.uid)) {
            enhance_draft_row(props.uid, props.row);
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
            }
        });
    });
});
