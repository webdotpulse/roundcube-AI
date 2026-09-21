/**
 * Email Scheduler & Undo Send Plugin Client Script
 *
 * Implements:
 * - "Send Later" and "Save" (Save Draft) button injections in Compose toolbar and formbuttons
 * - "Schedule Delivery" modal with presets (Tomorrow morning/afternoon, Monday, custom date/time)
 * - Undo Send live animated countdown toast with one-click cancellation
 * - Seamless integration with Elastic, Gmail Plus, Larry, and Classic skins
 */

(function(window, document, rcmail) {
    if (!window.rcmail) return;

    var undoTimer = null;
    var undoSecondsRemaining = 0;
    var observerAttached = false;
    var retryTimer = null;

    // Clock Icon SVG for Send Later
    var CLOCK_SVG = '<svg style="width:15px;height:15px;vertical-align:-2px;margin-right:5px;fill:currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/></svg>';

    // Save/Disk Icon SVG for Save Draft
    var SAVE_SVG = '<svg style="width:15px;height:15px;vertical-align:-2px;margin-right:5px;fill:currentColor;" viewBox="0 0 24 24"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm2 16H5V5h11.17L19 7.83V19zm-7-7c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zM6 6h9v4H6z"/></svg>';

    /**
     * Checks if current active view is a message compose / reply / forward / draft view.
     */
    function isComposeView() {
        if (!window.rcmail) return false;

        // Check rcmail env and action states
        var envAction = (rcmail.env && rcmail.env.action) ? rcmail.env.action : '';
        var curAction = rcmail.action || '';
        var curTask = rcmail.task || '';

        if (curTask !== 'mail') {
            return false;
        }

        if (curAction === 'compose' || curAction === 'reply' || curAction === 'forward' || curAction === 'draft' ||
            envAction === 'compose' || envAction === 'reply' || envAction === 'forward' || envAction === 'draft') {
            return true;
        }

        // Check DOM elements indicative of the compose screen
        return (
            document.getElementById('compose-content') !== null ||
            document.getElementById('composebody') !== null ||
            document.getElementById('compose-form') !== null ||
            document.querySelector('.formbuttons .send, #messagetoolbar a.save, form.formcontent, form[name="form"] input[name="_subject"]') !== null
        );
    }

    /**
     * Register command handlers with Roundcube
     */
    rcmail.register_command('plugin.email_scheduler-schedule', function() {
        showScheduleModal();
    }, true);

    rcmail.enable_command('plugin.email_scheduler-schedule', true);

    /**
     * Entry point: Attempt to attach buttons and observers.
     */
    function setupComposeEnhancements() {
        if (!isComposeView()) return;

        initComposeButtons();

        // Attach MutationObserver to catch asynchronous DOM updates (e.g. skin AJAX replacements)
        if (!observerAttached && window.MutationObserver) {
            var targetNode = document.getElementById('layout-content') || document.body;
            var observer = new MutationObserver(function(mutations) {
                if (isComposeView()) {
                    // Quick check if Send Later button is missing
                    if (!document.getElementById('btn-send-later')) {
                        initComposeButtons();
                    }
                }
            });

            observer.observe(targetNode, { childList: true, subtree: true });
            observerAttached = true;
        }
    }

    /**
     * Injects "Send Later" and "Save" buttons into both .formbuttons and #messagetoolbar.
     */
    function initComposeButtons() {
        // 1. Ensure hidden inputs for scheduler parameters exist in the compose form
        var composeForm = document.getElementById('compose-form') || document.forms['form'] || document.querySelector('form.formcontent, form[name="form"]');
        if (composeForm) {
            if (!document.getElementById('_email_scheduler_action')) {
                var actionInp = document.createElement('input');
                actionInp.type = 'hidden';
                actionInp.name = '_email_scheduler_action';
                actionInp.id = '_email_scheduler_action';
                composeForm.appendChild(actionInp);
            }
            if (!document.getElementById('_email_scheduler_send_at')) {
                var timeInp = document.createElement('input');
                timeInp.type = 'hidden';
                timeInp.name = '_email_scheduler_send_at';
                timeInp.id = '_email_scheduler_send_at';
                composeForm.appendChild(timeInp);
            }
        }

        var sendLaterLabel = rcmail.gettext('send_later_btn', 'email_scheduler') || 'Send Later';
        var saveLabel = rcmail.gettext('save_draft_btn', 'email_scheduler') || rcmail.gettext('save') || 'Save';
        var saveTooltip = rcmail.gettext('save_draft_tooltip', 'email_scheduler') || rcmail.gettext('savemessage') || 'Save Draft';

        // 2. Inject into .formbuttons (Beside primary Send button)
        var formButtonsContainers = document.querySelectorAll('.formbuttons, #composeview-bottom .formbuttons, .formcontainer .formbuttons');
        formButtonsContainers.forEach(function(container) {
            var sendBtn = container.querySelector('button.send, .btn.send, button[command="send"], button[name="_send"], a.button.send');
            if (sendBtn) {
                // A. Insert "Send Later" button if missing
                if (!container.querySelector('.send-later-btn') && !document.getElementById('btn-send-later')) {
                    var sendLaterBtn = document.createElement('button');
                    sendLaterBtn.type = 'button';
                    sendLaterBtn.id = 'btn-send-later';
                    sendLaterBtn.className = 'btn btn-secondary send-later-btn';
                    sendLaterBtn.title = sendLaterLabel;
                    sendLaterBtn.innerHTML = '<span class="inner">' + CLOCK_SVG + '<span>' + sendLaterLabel + '</span></span>';
                    sendLaterBtn.onclick = function(e) {
                        e.preventDefault();
                        showScheduleModal();
                    };

                    sendBtn.parentNode.insertBefore(sendLaterBtn, sendBtn.nextSibling);
                }

                    // B. Remove "Save" button from the composer window itself (.formbuttons)
                var composerSaveBtns = container.querySelectorAll('.save-draft-btn, #btn-save-draft, button.save, button.savedraft, button[command="savedraft"], a.button.save');
                for (var sIdx = 0; sIdx < composerSaveBtns.length; sIdx++) {
                    composerSaveBtns[sIdx].remove();
                }

                // Setup Undo Send Interception on standard Send button
                setupUndoSend(sendBtn);
            }
        });

        // 3. Remove "Send Later" from the right sidebar and compose toolbar
        var sideSendLater = document.querySelectorAll('#btn-send-later-toolbar, .button.send.schedule, #messagetoolbar a.schedule, #layout-sidebar .send-later, #layout-menu .send-later, .toolbar a.send.schedule');
        for (var slIdx = 0; slIdx < sideSendLater.length; slIdx++) {
            sideSendLater[slIdx].remove();
        }

        var toolbar = document.getElementById('messagetoolbar') || document.querySelector('.toolbar.menu, #compose-toolbar');
        if (toolbar) {

            // B. Ensure Toolbar Save Draft is prominent, visible, and uses xskin outline font icon (xi-save)
            var existingSave = toolbar.querySelector('a.save, a.draft, a.savedraft');
            if (existingSave) {
                existingSave.style.display = 'inline-flex';
                existingSave.style.visibility = 'visible';
                existingSave.classList.remove('disabled');
                existingSave.classList.add('xi-save');
                var oldSvg = existingSave.querySelector('svg');
                if (oldSvg) oldSvg.remove();
                if (!existingSave.classList.contains('icon-ready')) {
                    existingSave.classList.add('icon-ready');
                    var innerSpan = existingSave.querySelector('span.inner') || existingSave;
                    if (!innerSpan.querySelector('.btn-text')) {
                        var labelText = innerSpan.textContent.trim() || saveLabel;
                        innerSpan.innerHTML = '<span class="btn-text">' + labelText + '</span>';
                    }
                }
            } else if (!toolbar.querySelector('#btn-save-draft-toolbar')) {
                var tbSave = document.createElement('a');
                tbSave.href = '#savedraft';
                tbSave.id = 'btn-save-draft-toolbar';
                tbSave.className = 'button save draft xi-save';
                tbSave.title = saveTooltip;
                tbSave.tabIndex = 2;
                tbSave.innerHTML = '<span class="inner"><span class="btn-text">' + saveLabel + '</span></span>';
                tbSave.onclick = function(e) {
                    e.preventDefault();
                    rcmail.command('savedraft');
                    return false;
                };
                toolbar.appendChild(tbSave);
            }
        }
    }

    /**
     * Intercepts standard Send if Undo Send is enabled.
     */
    function setupUndoSend(sendBtn) {
        if (!sendBtn || sendBtn.getAttribute('data-undo-bound')) return;
        sendBtn.setAttribute('data-undo-bound', '1');

        var undoDelay = parseInt(rcmail.env ? (rcmail.env.email_scheduler_undo_delay || 0) : 0, 10);
        if (undoDelay <= 0) return;

        sendBtn.addEventListener('click', function(e) {
            var actionField = document.getElementById('_email_scheduler_action');
            if (actionField && actionField.value === 'schedule') {
                return; // Scheduled send passes through directly to database queue
            }

            if (!sendBtn.getAttribute('data-undo-bypass')) {
                e.preventDefault();
                e.stopPropagation();
                startUndoSendCountdown(undoDelay, function() {
                    sendBtn.setAttribute('data-undo-bypass', '1');
                    sendBtn.click();
                    sendBtn.removeAttribute('data-undo-bypass');
                });
            }
        }, true);
    }

    /**
     * Renders floating Undo Send Toast with live animated countdown bar.
     */
    function startUndoSendCountdown(seconds, sendCallback) {
        clearTimeout(undoTimer);
        undoSecondsRemaining = seconds;

        var existing = document.getElementById('undo-send-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.id = 'undo-send-toast';
        toast.className = 'undo-send-toast';
        toast.innerHTML = '<div class="undo-content">' +
            '<span>' + (rcmail.gettext('sending_delayed_toast', 'email_scheduler') || 'Sending message...') + ' (<strong id="undo-countdown">' + undoSecondsRemaining + 's</strong>)</span>' +
            '<button type="button" class="btn btn-warning btn-sm ml-3" id="undo-send-action-btn">' + (rcmail.gettext('undo_button', 'email_scheduler') || 'Undo') + '</button>' +
            '</div><div class="undo-progress-bar"><div class="undo-progress-fill" id="undo-progress-fill" style="width: 100%;"></div></div>';

        document.body.appendChild(toast);

        var cancelBtn = document.getElementById('undo-send-action-btn');
        cancelBtn.onclick = function() {
            clearTimeout(undoTimer);
            toast.remove();
            rcmail.display_message(rcmail.gettext('sending_undone', 'email_scheduler') || 'Sending cancelled.', 'confirmation');
        };

        var startTime = Date.now();
        var totalMs = seconds * 1000;

        function tick() {
            var elapsed = Date.now() - startTime;
            var remaining = Math.max(0, totalMs - elapsed);
            var pct = (remaining / totalMs) * 100;

            var countdownEl = document.getElementById('undo-countdown');
            var fillEl = document.getElementById('undo-progress-fill');
            if (countdownEl) countdownEl.textContent = Math.ceil(remaining / 1000) + 's';
            if (fillEl) fillEl.style.width = pct + '%';

            if (remaining <= 0) {
                toast.remove();
                sendCallback();
            } else {
                undoTimer = setTimeout(tick, 100);
            }
        }

        tick();
    }

    /**
     * Renders modal for choosing future scheduled delivery time.
     */
    function showScheduleModal() {
        var existing = document.getElementById('schedule-send-modal');
        if (existing) existing.remove();

        var presets = (rcmail.env && rcmail.env.email_scheduler_presets) ? rcmail.env.email_scheduler_presets : {};
        var tomorrowM = presets.tomorrow_morning || '';
        var tomorrowMLabel = presets.tomorrow_morning_label || 'Tomorrow morning 8:00 AM';
        var tomorrowA = presets.tomorrow_afternoon || '';
        var tomorrowALabel = presets.tomorrow_afternoon_label || 'Tomorrow afternoon 1:00 PM';
        var mondayM = presets.next_monday || '';
        var mondayMLabel = presets.next_monday_label || 'Monday morning 8:00 AM';

        var modal = document.createElement('div');
        modal.id = 'schedule-send-modal';
        modal.className = 'email-scheduler-modal-overlay';
        modal.innerHTML = '<div class="email-scheduler-modal">' +
            '<div class="modal-header"><h3>' + (rcmail.gettext('schedule_modal_title', 'email_scheduler') || 'Schedule Message Delivery') + '</h3><button type="button" class="close-btn" onclick="document.getElementById(\'schedule-send-modal\').remove()">&times;</button></div>' +
            '<div class="modal-body">' +
            '<div class="preset-group">' +
            '<button type="button" class="btn btn-preset" onclick="email_scheduler_choose(\'' + tomorrowM + '\')"><span class="icon">☀️</span> ' + tomorrowMLabel + '</button>' +
            '<button type="button" class="btn btn-preset" onclick="email_scheduler_choose(\'' + tomorrowA + '\')"><span class="icon">☕</span> ' + tomorrowALabel + '</button>' +
            '<button type="button" class="btn btn-preset" onclick="email_scheduler_choose(\'' + mondayM + '\')"><span class="icon">📅</span> ' + mondayMLabel + '</button>' +
            '</div>' +
            '<div class="custom-schedule-section">' +
            '<h4>' + (rcmail.gettext('schedule_custom_time', 'email_scheduler') || 'Custom date & time') + '</h4>' +
            '<div class="row" style="display:flex;gap:10px;margin-bottom:12px;">' +
            '<div style="flex:1;"><label>' + (rcmail.gettext('schedule_date_label', 'email_scheduler') || 'Date:') + '</label><input type="date" id="sched-custom-date" class="form-control" style="width:100%;"></div>' +
            '<div style="flex:1;"><label>' + (rcmail.gettext('schedule_time_label', 'email_scheduler') || 'Time:') + '</label><input type="time" id="sched-custom-time" class="form-control" value="08:00" style="width:100%;"></div>' +
            '</div>' +
            '<button type="button" class="btn btn-primary btn-block" style="width:100%;" onclick="email_scheduler_choose_custom()">' + (rcmail.gettext('schedule_confirm_btn', 'email_scheduler') || 'Schedule Send') + '</button>' +
            '</div></div></div>';

        document.body.appendChild(modal);

        // Pre-fill date picker with tomorrow
        var tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        var yyyy = tomorrow.getFullYear();
        var mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
        var dd = String(tomorrow.getDate()).padStart(2, '0');
        var dateInp = document.getElementById('sched-custom-date');
        if (dateInp) {
            dateInp.value = yyyy + '-' + mm + '-' + dd;
            dateInp.min = yyyy + '-' + mm + '-' + dd;
        }
    }

    window.email_scheduler_choose = function(dateTimeStr) {
        var act = document.getElementById('_email_scheduler_action');
        var time = document.getElementById('_email_scheduler_send_at');
        if (act) act.value = 'schedule';
        if (time) time.value = dateTimeStr;

        var modal = document.getElementById('schedule-send-modal');
        if (modal) modal.remove();

        // Trigger compose send
        rcmail.command('send');
    };

    window.email_scheduler_choose_custom = function() {
        var date = document.getElementById('sched-custom-date').value;
        var time = document.getElementById('sched-custom-time').value;
        if (!date || !time) {
            alert('Please select a date and time.');
            return;
        }
        var dt = date + ' ' + time + ':00';
        window.email_scheduler_choose(dt);
    };

    // Settings actions
    window.email_scheduler_send_now = function(id) {
        var fd = new FormData();
        fd.append('_token', rcmail.env.request_token);
        fd.append('id', id);

        fetch('?_action=plugin.email_scheduler-send-now', { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                rcmail.display_message(data.message, data.success ? 'confirmation' : 'error');
                if (data.success) {
                    var row = document.getElementById('sched-row-' + id);
                    if (row) row.remove();
                }
            });
    };

    window.email_scheduler_cancel = function(id) {
        var fd = new FormData();
        fd.append('_token', rcmail.env.request_token);
        fd.append('id', id);

        fetch('?_action=plugin.email_scheduler-cancel', { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                rcmail.display_message(data.message, data.success ? 'confirmation' : 'error');
                if (data.success) {
                    var row = document.getElementById('sched-row-' + id);
                    if (row) row.remove();
                }
            });
    };

    // Attach listeners across all lifecycle events
    rcmail.addEventListener('init', setupComposeEnhancements);
    rcmail.addEventListener('actionafter', setupComposeEnhancements);
    rcmail.addEventListener('responseafter', setupComposeEnhancements);

    // Document ready & load listeners
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupComposeEnhancements);
    } else {
        setupComposeEnhancements();
    }
    window.addEventListener('load', setupComposeEnhancements);

    // Polling retry for asynchronous single-page interface transitions
    var retries = 0;
    retryTimer = setInterval(function() {
        retries++;
        if (isComposeView()) {
            setupComposeEnhancements();
        }
        if (retries > 15) {
            clearInterval(retryTimer);
        }
    }, 250);

})(window, document, window.rcmail);
