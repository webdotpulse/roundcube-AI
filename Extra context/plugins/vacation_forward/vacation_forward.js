/**
 * Client-Side JavaScript for Vacation & Forwarding Plugin
 *
 * @license MIT
 * @author Webdotpulse & LifePrisma Contributors
 */

(function($) {
    'use strict';

    if (window.rcmail) {
        rcmail.addEventListener('init', function() {
            initVacationForward();
        });

        // Register custom event listeners from server
        rcmail.addEventListener('plugin.vacation_forward_templates_updated', function(templates) {
            rcmail.env.vacation_forward_templates = templates;
            renderTemplatesList(templates);
            $('#vfTemplateModal').modal('hide');
        });

        rcmail.addEventListener('plugin.vacation_forward_sim_result', function(res) {
            $('#vf-sim-tpl-name').text(res.matched_template);
            $('#vf-sim-detected-lang').text(res.detected_lang);
            $('#vf-sim-reason').text(res.match_reason);
            $('#vf-sim-res-subject').text(res.rendered_subject);
            $('#vf-sim-res-body').text(res.rendered_body);
            $('#vf-sim-result').slideDown(200);
        });

        rcmail.addEventListener('plugin.vacation_forward_logs_cleared', function() {
            $('#vf-logs-table tbody').html('<tr id="vf-no-logs-row"><td colspan="5" class="text-center text-muted p-4">No vacation or forwarding activity logged yet.</td></tr>');
        });
    }

    function initVacationForward() {
        // Tab switching
        $('#vfTabs a').on('click', function(e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // Vacation status dropdown change
        $('#rcmfd_vacation_status').on('change', function() {
            if ($(this).val() === 'scheduled') {
                $('#vf-schedule-fields').slideDown(200);
            } else {
                $('#vf-schedule-fields').slideUp(200);
            }
        });

        // Forward condition dropdown change
        $('#rcmfd_forward_condition').on('change', function() {
            if ($(this).val() === 'matching') {
                $('#vf-forward-keywords-row').slideDown(200);
            } else {
                $('#vf-forward-keywords-row').slideUp(200);
            }
        });
    }

    // Helper: format Date to YYYY-MM-DDTHH:mm
    function formatLocalDateTime(d) {
        var pad = function(n) { return (n < 10 ? '0' : '') + n; };
        return d.getFullYear() + '-' +
            pad(d.getMonth() + 1) + '-' +
            pad(d.getDate()) + 'T' +
            pad(d.getHours()) + ':' +
            pad(d.getMinutes());
    }

    // Quick Presets
    window.vf_apply_preset = function(type) {
        var now = new Date();
        var start = new Date(now);
        var end = new Date(now);

        if (type === 'weekend') {
            // Next Friday 17:00 to Monday 08:00
            var day = start.getDay();
            var diff = (5 - day + 7) % 7;
            if (diff === 0 && start.getHours() >= 17) {
                diff = 7;
            }
            start.setDate(start.getDate() + diff);
            start.setHours(17, 0, 0, 0);

            end.setTime(start.getTime());
            end.setDate(end.getDate() + 3);
            end.setHours(8, 0, 0, 0);
        } else if (type === 'next_week') {
            // Next Monday 08:00 to Friday 18:00
            var day = start.getDay();
            var diff = (1 - day + 7) % 7;
            if (diff === 0) diff = 7;
            start.setDate(start.getDate() + diff);
            start.setHours(8, 0, 0, 0);

            end.setTime(start.getTime());
            end.setDate(end.getDate() + 4);
            end.setHours(18, 0, 0, 0);
        } else if (type === 'two_weeks') {
            // Next Monday 08:00 to Monday in 2 weeks 08:00
            var day = start.getDay();
            var diff = (1 - day + 7) % 7;
            if (diff === 0) diff = 7;
            start.setDate(start.getDate() + diff);
            start.setHours(8, 0, 0, 0);

            end.setTime(start.getTime());
            end.setDate(end.getDate() + 14);
            end.setHours(8, 0, 0, 0);
        }

        $('#rcmfd_vacation_start').val(formatLocalDateTime(start));
        $('#rcmfd_vacation_end').val(formatLocalDateTime(end));
    };

    // Open Template Modal for New Template
    window.vf_open_template_modal = function() {
        $('#vfTemplateModalTitle').text('New Language Template');
        $('#vf_modal_tpl_id').val('');
        $('#vf_modal_tpl_name').val('');
        $('#vf_modal_tpl_lang').val('en');
        $('#vf_modal_tpl_default').prop('checked', false);
        $('#vf_modal_tpl_domain').val('');
        $('#vf_modal_tpl_subject').val('Out of Office: {ORIGINAL_SUBJECT}');
        $('#vf_modal_tpl_body').val("Hello {SENDER_NAME},\n\nI am currently out of the office from {START_DATE} until {END_DATE}.\n\nKind regards,\n{USER_NAME}");
        $('#vfTemplateModal').modal('show');
    };

    // Edit Template
    window.vf_edit_template = function(tplId) {
        var templates = rcmail.env.vacation_forward_templates || [];
        var tpl = templates.find(function(t) { return t.id === tplId; });
        if (!tpl) return;

        $('#vfTemplateModalTitle').text('Edit Template: ' + tpl.name);
        $('#vf_modal_tpl_id').val(tpl.id);
        $('#vf_modal_tpl_name').val(tpl.name);
        $('#vf_modal_tpl_lang').val(tpl.lang || 'en');
        $('#vf_modal_tpl_default').prop('checked', !!tpl.is_default);
        $('#vf_modal_tpl_domain').val(tpl.domain_rule || '');
        $('#vf_modal_tpl_subject').val(tpl.subject || '');
        $('#vf_modal_tpl_body').val(tpl.body || '');
        $('#vfTemplateModal').modal('show');
    };

    // Save Template via AJAX
    window.vf_save_template_modal = function() {
        var name = $.trim($('#vf_modal_tpl_name').val());
        var subject = $.trim($('#vf_modal_tpl_subject').val());
        if (!name || !subject) {
            alert('Please specify a Template Name and Subject.');
            return;
        }

        rcmail.http_post('plugin.vacation_forward-template-save', {
            _tpl_id: $('#vf_modal_tpl_id').val(),
            _tpl_name: name,
            _tpl_lang: $('#vf_modal_tpl_lang').val(),
            _tpl_is_default: $('#vf_modal_tpl_default').is(':checked') ? 1 : 0,
            _tpl_domain_rule: $('#vf_modal_tpl_domain').val(),
            _tpl_subject: subject,
            _tpl_body: $('#vf_modal_tpl_body').val()
        });
    };

    // Delete Template
    window.vf_delete_template = function(tplId) {
        if (!confirm('Are you sure you want to delete this template?')) {
            return;
        }
        rcmail.http_post('plugin.vacation_forward-template-delete', {
            _tpl_id: tplId
        });
    };

    // Insert Tag into Textarea
    window.vf_insert_tag = function(tag) {
        var $txt = $('#vf_modal_tpl_body');
        var el = $txt[0];
        if (!el) return;

        var start = el.selectionStart;
        var end = el.selectionEnd;
        var val = $txt.val();

        $txt.val(val.substring(0, start) + tag + val.substring(end));
        el.selectionStart = el.selectionEnd = start + tag.length;
        $txt.focus();
    };

    // Simulator
    window.vf_open_simulator = function() {
        $('#vf-sim-result').hide();
        $('#vfSimulatorModal').modal('show');
    };

    window.vf_run_simulation = function() {
        var sender = $.trim($('#vf_sim_sender').val());
        if (!sender) {
            alert('Please provide a sender email to simulate.');
            return;
        }

        rcmail.http_post('plugin.vacation_forward-simulate', {
            _test_sender: sender,
            _test_subject: $('#vf_sim_subject').val(),
            _test_body: $('#vf_sim_body').val()
        });
    };

    // Clear Logs
    window.vf_clear_logs = function() {
        if (!confirm('Are you sure you want to clear all vacation and forward activity logs?')) {
            return;
        }
        rcmail.http_post('plugin.vacation_forward-clear-logs', {});
    };

    // Dynamic Template Card Re-rendering
    function renderTemplatesList(templates) {
        var $container = $('#vf-templates-list');
        $container.empty();
        $('#vf-tpl-count').text(templates.length);

        templates.forEach(function(t) {
            var isDef = !!t.is_default;
            var langCode = (t.lang || 'EN').toUpperCase();

            var cardHtml = '<div class="col-md-6 mb-3" id="vf-card-' + escapeHtml(t.id) + '">' +
                '<div class="card h-100 shadow-sm vf-template-card' + (isDef ? ' border-primary' : '') + '">' +
                '  <div class="card-header d-flex justify-content-between align-items-center">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <span class="badge badge-info">' + escapeHtml(langCode) + '</span>' +
                '      <strong class="card-title mb-0">' + escapeHtml(t.name) + '</strong>' +
                (isDef ? ' <span class="badge badge-primary ml-1">Default</span>' : '') +
                '    </div>' +
                '    <div class="btn-group btn-group-sm">' +
                '      <button type="button" class="btn btn-outline-secondary" onclick="vf_edit_template(\'' + escapeHtml(t.id) + '\')">✏️</button>' +
                '      <button type="button" class="btn btn-outline-danger" onclick="vf_delete_template(\'' + escapeHtml(t.id) + '\')">🗑️</button>' +
                '    </div>' +
                '  </div>' +
                '  <div class="card-body">' +
                '    <p class="small text-muted mb-1"><strong>Subject:</strong> ' + escapeHtml(t.subject) + '</p>' +
                (t.domain_rule ? '    <p class="small text-muted mb-1"><strong>Domain Filter:</strong> <code>' + escapeHtml(t.domain_rule) + '</code></p>' : '') +
                '    <div class="vf-template-preview-box p-2 bg-light rounded small text-monospace" style="max-height: 120px; overflow-y: auto;">' +
                escapeHtml(t.body).replace(/\n/g, '<br>') +
                '    </div>' +
                '  </div>' +
                '</div>' +
                '</div>';

            $container.append(cardHtml);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})(jQuery);
