/**
 * Email Scheduler & Undo Send Plugin Client Script
 */

(function(window, document, rcmail) {
    if (!window.rcmail) return;

    var undoTimer = null;
    var undoSecondsRemaining = 0;
    var sendPendingPayload = null;

    rcmail.addEventListener('init', function() {
        // 1. Compose Window Enhancements
        if (rcmail.task === 'mail' && rcmail.action === 'compose') {
            initComposeScheduler();
        }
    });

    /**
     * Initializes the Send Later button and modal in the compose toolbar.
     */
    function initComposeScheduler() {
        if (!rcmail.env.email_scheduler_schedule_enabled) return;

        // Find standard compose Send button
        var sendBtn = document.getElementById('rcmbtn107') || document.querySelector('.button.send, .btn.btn-primary.send, button[name="_send"]');
        if (!sendBtn || document.getElementById('btn-send-later')) return;

        // Create Send Later button
        var sendLaterBtn = document.createElement('button');
        sendLaterBtn.type = 'button';
        sendLaterBtn.id = 'btn-send-later';
        sendLaterBtn.className = 'btn btn-outline-secondary send-later-btn';
        sendLaterBtn.style.marginLeft = '6px';
        sendLaterBtn.title = rcmail.gettext('send_later_btn', 'email_scheduler');
        sendLaterBtn.innerHTML = '<span class="inner"><svg style="width:14px;height:14px;vertical-align:-2px;margin-right:4px;fill:currentColor;" viewBox="0 0 24 24"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7-.8 1.3z"/></svg>' + rcmail.gettext('send_later_btn', 'email_scheduler') + '</span>';
        sendLaterBtn.onclick = showScheduleModal;

        sendBtn.parentNode.insertBefore(sendLaterBtn, sendBtn.nextSibling);

        // Inject hidden inputs for scheduler parameters into compose form
        var composeForm = document.getElementById('compose-form') || document.forms['form'];
        if (composeForm) {
            var actionInp = document.createElement('input');
            actionInp.type = 'hidden';
            actionInp.name = '_email_scheduler_action';
            actionInp.id = '_email_scheduler_action';
            composeForm.appendChild(actionInp);

            var timeInp = document.createElement('input');
            timeInp.type = 'hidden';
            timeInp.name = '_email_scheduler_send_at';
            timeInp.id = '_email_scheduler_send_at';
            composeForm.appendChild(timeInp);
        }

        // Intercept standard Send if Undo Send is enabled
        var undoDelay = parseInt(rcmail.env.email_scheduler_undo_delay || 0, 10);
        if (undoDelay > 0) {
            sendBtn.addEventListener('click', function(e) {
                var actionField = document.getElementById('_email_scheduler_action');
                if (actionField && actionField.value === 'schedule') {
                    return; // Let scheduled send pass through
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
    }

    /**
     * Renders floating Undo Send Toast with live countdown.
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
            '<span>' + rcmail.gettext('sending_delayed_toast', 'email_scheduler') + ' (<strong id="undo-countdown">' + undoSecondsRemaining + 's</strong>)</span>' +
            '<button type="button" class="btn btn-warning btn-sm ml-3" id="undo-send-action-btn">' + rcmail.gettext('undo_button', 'email_scheduler') + '</button>' +
            '</div><div class="undo-progress-bar"><div class="undo-progress-fill" id="undo-progress-fill" style="width: 100%;"></div></div>';

        document.body.appendChild(toast);

        var cancelBtn = document.getElementById('undo-send-action-btn');
        cancelBtn.onclick = function() {
            clearTimeout(undoTimer);
            toast.remove();
            rcmail.display_message(rcmail.gettext('sending_undone', 'email_scheduler'), 'confirmation');
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

        var presets = rcmail.env.email_scheduler_presets || {};
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
            '<div class="modal-header"><h3>' + rcmail.gettext('schedule_modal_title', 'email_scheduler') + '</h3><button type="button" class="close-btn" onclick="document.getElementById(\'schedule-send-modal\').remove()">&times;</button></div>' +
            '<div class="modal-body">' +
            '<div class="preset-group">' +
            '<button type="button" class="btn btn-preset" onclick="email_scheduler_choose(\'' + tomorrowM + '\')"><span class="icon">☀️</span> ' + tomorrowMLabel + '</button>' +
            '<button type="button" class="btn btn-preset" onclick="email_scheduler_choose(\'' + tomorrowA + '\')"><span class="icon">☕</span> ' + tomorrowALabel + '</button>' +
            '<button type="button" class="btn btn-preset" onclick="email_scheduler_choose(\'' + mondayM + '\')"><span class="icon">📅</span> ' + mondayMLabel + '</button>' +
            '</div>' +
            '<div class="custom-schedule-section">' +
            '<h4>' + rcmail.gettext('schedule_custom_time', 'email_scheduler') + '</h4>' +
            '<div class="row" style="display:flex;gap:10px;margin-bottom:12px;">' +
            '<div style="flex:1;"><label>' + rcmail.gettext('schedule_date_label', 'email_scheduler') + '</label><input type="date" id="sched-custom-date" class="form-control" style="width:100%;"></div>' +
            '<div style="flex:1;"><label>' + rcmail.gettext('schedule_time_label', 'email_scheduler') + '</label><input type="time" id="sched-custom-time" class="form-control" value="08:00" style="width:100%;"></div>' +
            '</div>' +
            '<button type="button" class="btn btn-primary btn-block" style="width:100%;" onclick="email_scheduler_choose_custom()">' + rcmail.gettext('schedule_confirm_btn', 'email_scheduler') + '</button>' +
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
        document.getElementById('_email_scheduler_action').value = 'schedule';
        document.getElementById('_email_scheduler_send_at').value = dateTimeStr;
        document.getElementById('schedule-send-modal').remove();

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

})(window, document, window.rcmail);
