/**
 * Roundcube Newsletter Plugin - Client JavaScript
 *
 * Manages campaign wizard, template switching, dynamic token insertion,
 * pre-flight anti-spam scoring, throttled batch dispatching, and suppressions.
 */

(function (window, document, $) {
    'use strict';

    var NewsletterApp = {
        recipients: {
            deliverable: [],
            suppressed: [],
            total: 0
        },
        templates: {
            blank: {
                subject: '',
                body: '<p>Hello {first_name},</p>\n<p>Write your newsletter content here.</p>\n<p>Best regards,<br>{name}</p>'
            },
            modern: {
                subject: 'Fresh updates & highlights for this week',
                body: '<div style="max-width: 600px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">\n' +
                      '  <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); padding: 32px 24px; text-align: center; color: #ffffff;">\n' +
                      '    <h1 style="margin: 0 0 8px; font-size: 26px; font-weight: 700; color: #ffffff;">Community Newsletter</h1>\n' +
                      '    <p style="margin: 0; font-size: 15px; opacity: 0.9;">Curated insights and essential updates</p>\n' +
                      '  </div>\n' +
                      '  <div style="padding: 32px 24px; color: #334155; line-height: 1.6;">\n' +
                      '    <p style="font-size: 16px; margin-top: 0;">Hi {first_name},</p>\n' +
                      '    <p>Welcome to this week\'s edition! We are excited to bring you the latest developments, featured articles, and community highlights.</p>\n' +
                      '    <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 16px; margin: 24px 0; border-radius: 0 6px 6px 0;">\n' +
                      '      <h3 style="margin: 0 0 6px; font-size: 17px; color: #1e293b;">Key Highlight</h3>\n' +
                      '      <p style="margin: 0; font-size: 14px; color: #64748b;">Discover how our latest platform optimizations improve speed, reliability, and security for your daily workflow.</p>\n' +
                      '    </div>\n' +
                      '    <p>Feel free to reply directly to this email if you have questions or feedback.</p>\n' +
                      '    <p style="margin-bottom: 0;">Warm regards,<br><strong>The Team</strong></p>\n' +
                      '  </div>\n' +
                      '  <div style="background: #f1f5f9; padding: 20px 24px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0;">\n' +
                      '    <p style="margin: 0 0 6px;">You received this email as a registered subscriber of our newsletter.</p>\n' +
                      '    <p style="margin: 0;"><a href="{unsubscribe_url}" style="color: #475569; text-decoration: underline;">Unsubscribe from this list</a></p>\n' +
                      '  </div>\n' +
                      '</div>'
            },
            announcement: {
                subject: 'Major Announcement: Introducing New Capabilities',
                body: '<div style="max-width: 600px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0;">\n' +
                      '  <div style="padding: 32px 24px 20px; text-align: center;">\n' +
                      '    <span style="background: #e0e7ff; color: #4338ca; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 12px; border-radius: 12px;">Product Launch</span>\n' +
                      '    <h1 style="margin: 16px 0 8px; font-size: 28px; color: #1e293b;">Something Big Just Landed</h1>\n' +
                      '    <p style="margin: 0; color: #64748b; font-size: 16px;">Built for speed, ease of use, and maximum deliverability.</p>\n' +
                      '  </div>\n' +
                      '  <div style="padding: 0 24px 32px; color: #334155; line-height: 1.6;">\n' +
                      '    <p>Dear {first_name},</p>\n' +
                      '    <p>Today we are thrilled to unveil our newest features tailored specifically to enhance your everyday communication experience.</p>\n' +
                      '    <div style="text-align: center; margin: 30px 0;">\n' +
                      '      <a href="https://example.com" style="background: #2563eb; color: #ffffff; padding: 12px 28px; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block;">Explore the Features</a>\n' +
                      '    </div>\n' +
                      '    <p>Thank you for being an essential part of our journey.</p>\n' +
                      '  </div>\n' +
                      '  <div style="background: #f8fafc; padding: 18px 24px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">\n' +
                      '    <a href="{unsubscribe_url}" style="color: #64748b; text-decoration: underline;">One-Click Unsubscribe</a>\n' +
                      '  </div>\n' +
                      '</div>'
            },
            digest: {
                subject: 'Weekly Digest: Top Articles & Community Highlights',
                body: '<div style="max-width: 600px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 24px;">\n' +
                      '  <h2 style="color: #0f172a; margin-top: 0;">Weekly Digest — {date}</h2>\n' +
                      '  <p style="color: #475569;">Hello {first_name}, here is your weekly summary of top news and helpful tips:</p>\n' +
                      '  <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">\n' +
                      '  <h4 style="margin: 0 0 4px; color: #1e293b;">1. Deliverability Best Practices</h4>\n' +
                      '  <p style="color: #64748b; font-size: 14px; margin: 0 0 16px;">How individual 1-to-1 envelope sending outperforms bulk BCC lists in modern mail systems.</p>\n' +
                      '  <h4 style="margin: 0 0 4px; color: #1e293b;">2. RFC 8058 One-Click Compliance</h4>\n' +
                      '  <p style="color: #64748b; font-size: 14px; margin: 0 0 16px;">Meeting Google and Yahoo sender guidelines with zero friction.</p>\n' +
                      '  <p style="font-size: 12px; color: #94a3b8; text-align: center; margin-top: 30px;"><a href="{unsubscribe_url}" style="color: #64748b;">Unsubscribe</a></p>\n' +
                      '</div>'
            }
        },
        dispatchState: {
            isRunning: false,
            isPaused: false,
            isCancelled: false,
            currentIndex: 0,
            batchSize: 25,
            batchDelaySeconds: 2,
            sentCount: 0,
            failedCount: 0,
            campaignId: 0,
            startTime: null
        },

        init: function () {
            var self = this;

            // Automatically append Roundcube CSRF token (_token) to all POST requests
            if (window.rcmail && rcmail.env && rcmail.env.request_token) {
                $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
                    if (options.type && options.type.toUpperCase() === 'POST') {
                        if (typeof options.data === 'string' && options.data.indexOf('_token=') === -1) {
                            options.data += (options.data ? '&' : '') + '_token=' + encodeURIComponent(rcmail.env.request_token);
                        } else if (typeof options.data === 'object' && options.data !== null && !(options.data instanceof FormData)) {
                            options.data._token = rcmail.env.request_token;
                        }
                    }
                });
            }

            // Check if we are on the newsletter studio page
            if ($('#newsletter-studio').length > 0) {
                self.setupStudioUI();
            }

            // Compose toolbar integration
            $(document).on('click', '#btn-newsletter-compose, a.send.newsletter', function (e) {
                e.preventDefault();
                window.location.href = '?_task=newsletter';
            });
        },

        setupStudioUI: function () {
            var self = this;

            // Load initial template
            self.loadTemplate('modern');

            // Setup Tab Switching
            $('.btn-tab').on('click', function () {
                var tab = $(this).data('tab');
                $('.btn-tab').removeClass('active');
                $(this).addClass('active');
                $('.newsletter-tab-pane').removeClass('active');
                $('#tab-' + tab).addClass('active');

                if (tab === 'history') {
                    self.loadHistory();
                } else if (tab === 'suppressions') {
                    self.loadSuppressions();
                }
            });

            // Recipient Source radio change
            $('input[name="recipient_source"]').on('change', function () {
                var val = $(this).val();
                if (val === 'groups') {
                    $('#group-selector-container').removeClass('d-none');
                    $('#custom-recipients-container').addClass('d-none');
                    self.loadContactGroups();
                } else if (val === 'custom') {
                    $('#custom-recipients-container').removeClass('d-none');
                    $('#group-selector-container').addClass('d-none');
                } else {
                    $('#group-selector-container').addClass('d-none');
                    $('#custom-recipients-container').addClass('d-none');
                }
                self.refreshRecipients();
            });

            // Refresh recipients button
            $('#btn-refresh-recipients').on('click', function () {
                self.refreshRecipients();
            });

            // Template buttons
            $('.template-buttons button').on('click', function () {
                var tmpl = $(this).data('template');
                $('.template-buttons button').removeClass('active');
                $(this).addClass('active');
                self.loadTemplate(tmpl);
            });

            // Token toolbar insertion
            $('.btn-token').on('click', function () {
                var token = $(this).data('token');
                self.insertTokenAtCursor(token);
            });

            // Editor tab toggling
            $('.btn-editor-tab').on('click', function () {
                var view = $(this).data('view');
                $('.btn-editor-tab').removeClass('active');
                $(this).addClass('active');

                if (view === 'preview') {
                    $('#newsletter-body').addClass('d-none');
                    $('#newsletter-preview-container').removeClass('d-none');
                    self.renderLivePreview();
                } else {
                    $('#newsletter-body').removeClass('d-none');
                    $('#newsletter-preview-container').addClass('d-none');
                }
            });

            // Debounced Spam Score re-calculation on input
            var spamDebounceTimer = null;
            $('#newsletter-subject, #newsletter-body').on('input change', function () {
                clearTimeout(spamDebounceTimer);
                spamDebounceTimer = setTimeout(function () {
                    self.runSpamCheck();
                }, 600);
            });

            $('#btn-recheck-spam').on('click', function () {
                self.runSpamCheck();
            });

            // Batch settings sliders
            $('#input-batch-size').on('input change', function () {
                var val = $(this).val();
                $('#batch-size-label').text(val);
                self.dispatchState.batchSize = parseInt(val, 10);
            });

            $('#input-batch-delay').on('input change', function () {
                var val = $(this).val();
                $('#batch-delay-label').text(val + 's');
                self.dispatchState.batchDelaySeconds = parseInt(val, 10);
            });

            // Dispatch Controls
            $('#btn-start-sending').on('click', function () {
                self.startBatchDispatch();
            });

            $('#btn-pause-sending').on('click', function () {
                self.pauseBatchDispatch();
            });

            $('#btn-resume-sending').on('click', function () {
                self.resumeBatchDispatch();
            });

            $('#btn-cancel-sending').on('click', function () {
                self.cancelBatchDispatch();
            });

            // Suppression list add button
            $('#btn-add-suppression').on('click', function () {
                var email = $('#input-new-suppression').val().trim();
                if (email) {
                    self.addSuppression(email);
                    $('#input-new-suppression').val('');
                }
            });

            $('#btn-refresh-history').on('click', function () {
                self.loadHistory();
            });

            // Initialize Gemini AI studio integrations
            self.setupGeminiAI();

            // Initial loads
            self.refreshRecipients();
            self.runSpamCheck();
        },

        setupGeminiAI: function () {
            var self = this;

            // Gemini Subject Suggestion Button
            $('#btn-newsletter-ai-subject').on('click', function (e) {
                e.preventDefault();
                var btn = this;
                if (window.lpai_suggest_subject) {
                    window.lpai_suggest_subject(btn);
                } else {
                    var content = $('#newsletter-body').val();
                    if (!content || !content.trim()) {
                        if (window.rcmail && rcmail.display_message) rcmail.display_message('Write or template some newsletter content first', 'notice');
                        return;
                    }
                    var orig = $(btn).html();
                    $(btn).prop('disabled', true).html('&#9203; Generating...');
                    $.post('?_task=mail&_action=plugin.lifeprisma_ai_request', {
                        ai_action: 'suggest_subject',
                        email_body: content.substring(0, 1500),
                        _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
                    }, function (data) {
                        $(btn).prop('disabled', false).html(orig);
                        if (data && data.status === 'success' && data.result) {
                            var lines = data.result.split('\n').filter(function(l) { return l.trim().length > 0; });
                            var first = lines[0].replace(/^\d+\.\s*/, '').replace(/^["']|["']$/g, '');
                            $('#newsletter-subject').val(first).trigger('input').trigger('change');
                            self.runSpamCheck();
                        }
                    }, 'json').fail(function() {
                        $(btn).prop('disabled', false).html(orig);
                    });
                }
            });

            // Open Full Gemini Assistant Panel
            $('#btn-newsletter-ai-open-panel').on('click', function (e) {
                e.preventDefault();
                if (window.lpai_open_panel) {
                    window.lpai_open_panel('newsletter');
                }
            });

            // Draft Newsletter with Gemini
            $('#btn-newsletter-ai-draft').on('click', function (e) {
                e.preventDefault();
                if (window.lpai_open_panel) {
                    window.lpai_open_panel('newsletter');
                    if (window.lpai_select_action) window.lpai_select_action('compose');
                }
            });

            // Polish & Rewrite
            $('#btn-newsletter-ai-rewrite').on('click', function (e) {
                e.preventDefault();
                if (window.lpai_open_panel) {
                    window.lpai_open_panel('newsletter');
                    if (window.lpai_select_action) window.lpai_select_action('rewrite');
                }
            });

            // Fix Grammar & Flow
            $('#btn-newsletter-ai-fix').on('click', function (e) {
                e.preventDefault();
                if (window.lpai_newsletter_quick) {
                    window.lpai_newsletter_quick('fix', this);
                } else if (window.lpai_open_panel) {
                    window.lpai_open_panel('newsletter');
                    if (window.lpai_select_action) window.lpai_select_action('fix');
                }
            });

            // Optimize Anti-Spam Deliverability
            $('#btn-newsletter-ai-optimize-spam').on('click', function (e) {
                e.preventDefault();
                if (window.lpai_newsletter_quick) {
                    window.lpai_newsletter_quick('newsletter_optimize_spam', this);
                } else if (window.lpai_open_panel) {
                    window.lpai_open_panel('newsletter');
                    if (window.lpai_select_action) window.lpai_select_action('rewrite');
                }
            });
        },

        loadTemplate: function (name) {
            var tmpl = this.templates[name] || this.templates.blank;
            if (tmpl.subject) {
                $('#newsletter-subject').val(tmpl.subject);
            }
            $('#newsletter-body').val(tmpl.body);
            this.runSpamCheck();
        },

        insertTokenAtCursor: function (token) {
            var textarea = document.getElementById('newsletter-body');
            if (!textarea) return;

            var startPos = textarea.selectionStart || 0;
            var endPos = textarea.selectionEnd || 0;
            var text = textarea.value;

            textarea.value = text.substring(0, startPos) + token + text.substring(endPos, text.length);
            textarea.selectionStart = textarea.selectionEnd = startPos + token.length;
            textarea.focus();
            this.runSpamCheck();
        },

        loadContactGroups: function () {
            var self = this;
            $.post('?_task=newsletter&_action=plugin.newsletter-groups', {
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function (res) {
                if (res) {
                    var html = '';
                    var groups = res.groups || [];
                    if (groups.length === 0) {
                        html = '<p class="text-muted mb-0">No specific contact groups found in address books. Switch to <strong>All Contacts</strong> to send to your full address book, or create groups in Contacts.</p>';
                    } else {
                        $.each(groups, function (i, g) {
                            html += '<label class="group-checkbox-pill">' +
                                    '<input type="checkbox" name="contact_group[]" value="' + g.id + '"> ' +
                                    '<span>' + (g.name || 'Group') + ' <small class="text-muted">(' + g.source + ')</small></span>' +
                                    '</label>';
                        });
                    }
                    $('#contact-groups-list').html(html);

                    $('#contact-groups-list input[type="checkbox"]').on('change', function () {
                        self.refreshRecipients();
                    });
                }
            }, 'json');
        },

        refreshRecipients: function () {
            var self = this;
            var sourceType = $('input[name="recipient_source"]:checked').val() || 'all';
            var selectedGroups = [];
            $('#contact-groups-list input[type="checkbox"]:checked').each(function () {
                selectedGroups.push($(this).val());
            });
            var customText = $('#custom-recipients-input').val();

            $('#recipient-count').text('...');
            $('#deliverable-count').text('...');
            $('#suppressed-count').text('...');

            $.post('?_task=newsletter&_action=plugin.newsletter-recipients', {
                source_type: sourceType,
                groups: selectedGroups,
                custom_text: customText,
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function (res) {
                if (res && res.success) {
                    self.recipients.deliverable = res.deliverable || [];
                    self.recipients.suppressed = res.suppressed || [];
                    self.recipients.total = res.total_count || 0;

                    $('#recipient-count').text(res.total_count);
                    $('#deliverable-count').text(res.deliverable_count);
                    $('#suppressed-count').text(res.suppressed_count);

                    if (res.deliverable_count === 0) {
                        $('#btn-start-sending').prop('disabled', true).addClass('disabled');
                    } else {
                        $('#btn-start-sending').prop('disabled', false).removeClass('disabled');
                    }
                } else {
                    $('#recipient-count').text('0');
                    $('#deliverable-count').text('0');
                    $('#suppressed-count').text('0');
                }
            }, 'json').fail(function (xhr, status, err) {
                console.error('Newsletter recipients check failed:', status, err);
                $('#recipient-count').text('0');
                $('#deliverable-count').text('0');
                $('#suppressed-count').text('0');
            });
        },

        runSpamCheck: function () {
            var subject = $('#newsletter-subject').val();
            var body = $('#newsletter-body').val();
            var from = $('#sender-identity-select').val();

            $.post('?_task=newsletter&_action=plugin.newsletter-spam-score', {
                subject: subject,
                body: body,
                from: from,
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function (res) {
                if (res && res.success) {
                    var score = res.score;
                    var severity = res.severity;

                    $('#spam-score-value').text(score);
                    var circle = $('#spam-score-circle');
                    circle.removeClass('score-low score-medium score-high');

                    var title = $('#spam-risk-title');
                    var desc = $('#spam-risk-desc');

                    if (severity === 'low') {
                        circle.addClass('score-low');
                        title.removeClass().addClass('score-title text-success').text('Low Risk (' + score + '/100)');
                        desc.text('Excellent deliverability. Content aligns with ISP best practices.');
                    } else if (severity === 'medium') {
                        circle.addClass('score-medium');
                        title.removeClass().addClass('score-title text-warning').text('Moderate Risk (' + score + '/100)');
                        desc.text('Review suggestions below to boost inbox delivery.');
                    } else {
                        circle.addClass('score-high');
                        title.removeClass().addClass('score-title text-danger').text('High Risk (' + score + '/100)');
                        desc.text('Likely to trigger spam filters. Fix flagged items before sending.');
                    }

                    // Check unsubscribe presence
                    if (body.indexOf('{unsubscribe_url}') !== -1 || body.toLowerCase().indexOf('unsubscribe') !== -1) {
                        $('#badge-unsub').removeClass('fail').addClass('pass').html('<span class="badge-icon">✓</span> Unsubscribe Link Present');
                    } else {
                        $('#badge-unsub').removeClass('pass').addClass('fail').html('<span class="badge-icon">✗</span> Missing Unsubscribe Link');
                    }

                    // Render recommendations
                    var html = '';
                    if (res.flags && res.flags.length > 0) {
                        $.each(res.flags, function (i, f) {
                            var icon = f.type === 'danger' ? '⚠️' : 'ℹ️';
                            var cls = f.type === 'danger' ? 'flag-danger' : 'flag-warning';
                            html += '<div class="spam-flag ' + cls + '">' +
                                    '<strong>' + icon + ' ' + f.title + '</strong>' +
                                    '<p>' + f.detail + '</p>' +
                                    '</div>';
                        });
                    } else {
                        html = '<div class="text-success p-2">🎉 No deliverability red flags detected!</div>';
                    }
                    $('#spam-suggestions').html(html);
                }
            }, 'json').fail(function (xhr, status, err) {
                console.error('Newsletter spam check failed:', status, err);
                $('#spam-score-value').text('0');
            });
        },

        renderLivePreview: function () {
            var subject = $('#newsletter-subject').val();
            var body = $('#newsletter-body').val();

            $.post('?_task=newsletter&_action=plugin.newsletter-preview', {
                subject: subject,
                body: body,
                sample_email: 'sarah.connor@example.com',
                sample_name: 'Sarah Connor',
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function (res) {
                if (res && res.success) {
                    var html = '<div class="preview-header mb-3 p-3 bg-light border rounded">' +
                               '<strong>Subject:</strong> ' + $('<div>').text(res.subject).html() + '<br>' +
                               '<small class="text-muted">Previewing with sample recipient: Sarah Connor &lt;sarah.connor@example.com&gt;</small>' +
                               '</div>' +
                               '<div class="preview-content-rendered">' + res.body_html + '</div>';
                    $('#newsletter-preview-container').html(html);
                }
            }, 'json');
        },

        startBatchDispatch: function () {
            var self = this;
            if (self.recipients.deliverable.length === 0) {
                alert('Please select or specify at least one deliverable recipient before sending.');
                return;
            }

            var subject = $('#newsletter-subject').val().trim();
            if (!subject) {
                alert('Please enter a subject line for your newsletter.');
                $('#newsletter-subject').focus();
                return;
            }

            if (!confirm('Are you ready to begin batch transmission to ' + self.recipients.deliverable.length + ' recipients?')) {
                return;
            }

            self.dispatchState.isRunning = true;
            self.dispatchState.isPaused = false;
            self.dispatchState.isCancelled = false;
            self.dispatchState.currentIndex = 0;
            self.dispatchState.sentCount = 0;
            self.dispatchState.failedCount = 0;
            self.dispatchState.campaignId = Date.now();
            self.dispatchState.startTime = Date.now();

            $('#btn-start-sending').addClass('d-none');
            $('#dispatch-running-controls').removeClass('d-none');
            $('#dispatch-progress-section').removeClass('d-none');
            $('#btn-pause-sending').removeClass('d-none');
            $('#btn-resume-sending').addClass('d-none');

            self.executeNextBatch();
        },

        executeNextBatch: function () {
            var self = this;
            if (!self.dispatchState.isRunning || self.dispatchState.isPaused || self.dispatchState.isCancelled) {
                return;
            }

            var total = self.recipients.deliverable.length;
            var start = self.dispatchState.currentIndex;
            var end = Math.min(start + self.dispatchState.batchSize, total);

            if (start >= total) {
                self.finishBatchDispatch();
                return;
            }

            var batchSlice = self.recipients.deliverable.slice(start, end);

            var subject = $('#newsletter-subject').val();
            var bodyHtml = $('#newsletter-body').val();
            var fromEmail = $('#sender-identity-select').val();
            var fromName = $('#sender-identity-select option:selected').data('name') || '';

            // Update Progress Bar
            self.updateProgressUI(start, total);

            $.ajax({
                url: '?_task=newsletter&_action=plugin.newsletter-send-batch',
                type: 'POST',
                data: {
                    campaign_id: self.dispatchState.campaignId,
                    subject: subject,
                    from_email: fromEmail,
                    from_name: fromName,
                    body_html: bodyHtml,
                    recipients: batchSlice,
                    _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
                },
                dataType: 'json',
                success: function (res) {
                    if (res && res.success) {
                        self.dispatchState.sentCount += (res.sent || 0);
                        self.dispatchState.failedCount += (res.failed || 0);
                    } else {
                        self.dispatchState.failedCount += batchSlice.length;
                    }

                    self.dispatchState.currentIndex = end;
                    self.updateProgressUI(end, total);

                    if (end < total && self.dispatchState.isRunning && !self.dispatchState.isPaused && !self.dispatchState.isCancelled) {
                        // Throttled wait between batches
                        var delayMs = self.dispatchState.batchDelaySeconds * 1000;
                        setTimeout(function () {
                            self.executeNextBatch();
                        }, delayMs);
                    } else if (end >= total) {
                        self.finishBatchDispatch();
                    }
                },
                error: function () {
                    self.dispatchState.failedCount += batchSlice.length;
                    self.dispatchState.currentIndex = end;
                    self.updateProgressUI(end, total);

                    if (end < total && self.dispatchState.isRunning && !self.dispatchState.isPaused && !self.dispatchState.isCancelled) {
                        setTimeout(function () {
                            self.executeNextBatch();
                        }, 2000);
                    } else {
                        self.finishBatchDispatch();
                    }
                }
            });
        },

        updateProgressUI: function (current, total) {
            var percent = total > 0 ? Math.round((current / total) * 100) : 0;
            $('#progress-percent').text(percent + '%');
            $('#progress-stats').text(current + ' / ' + total);
            $('#batch-progress-bar').css('width', percent + '%');
            $('#metric-sent-count').text(this.dispatchState.sentCount);
            $('#metric-failed-count').text(this.dispatchState.failedCount);

            if (this.dispatchState.startTime && current > 0) {
                var elapsed = (Date.now() - this.dispatchState.startTime) / 1000;
                var rate = current / elapsed; // items per second
                var remaining = total - current;
                var etaSec = rate > 0 ? Math.round(remaining / rate) : 0;
                $('#metric-time-left').text('~' + etaSec + 's left');
            }
        },

        pauseBatchDispatch: function () {
            this.dispatchState.isPaused = true;
            $('#btn-pause-sending').addClass('d-none');
            $('#btn-resume-sending').removeClass('d-none');
        },

        resumeBatchDispatch: function () {
            this.dispatchState.isPaused = false;
            $('#btn-resume-sending').addClass('d-none');
            $('#btn-pause-sending').removeClass('d-none');
            this.executeNextBatch();
        },

        cancelBatchDispatch: function () {
            if (confirm('Are you sure you want to stop this campaign? Remaining batches will not be sent.')) {
                this.dispatchState.isCancelled = true;
                this.dispatchState.isRunning = false;
                $('#btn-start-sending').removeClass('d-none');
                $('#dispatch-running-controls').addClass('d-none');
                alert('Campaign cancelled. Sent: ' + this.dispatchState.sentCount + ', Failed: ' + this.dispatchState.failedCount);
            }
        },

        finishBatchDispatch: function () {
            this.dispatchState.isRunning = false;
            $('#batch-progress-bar').removeClass('progress-bar-animated bg-primary').addClass('bg-success');
            $('#metric-time-left').text('Completed');
            $('#dispatch-running-controls').addClass('d-none');
            $('#btn-start-sending').removeClass('d-none');

            alert('🎉 Newsletter campaign successfully dispatched!\n\nSent: ' + this.dispatchState.sentCount + '\nFailed: ' + this.dispatchState.failedCount);
        },

        loadHistory: function () {
            $.post('?_task=newsletter&_action=plugin.newsletter-campaigns', {
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function (res) {
                if (res && res.campaigns) {
                    var html = '';
                    if (res.campaigns.length === 0) {
                        html = '<tr><td colspan="6" class="text-center p-4 text-muted">No campaigns sent yet.</td></tr>';
                    } else {
                        $.each(res.campaigns, function (i, c) {
                            var spamBadge = '<span class="badge badge-success">' + (c.spam_score || 0) + '</span>';
                            if (c.spam_score > 50) {
                                spamBadge = '<span class="badge badge-danger">' + c.spam_score + '</span>';
                            } else if (c.spam_score > 25) {
                                spamBadge = '<span class="badge badge-warning">' + c.spam_score + '</span>';
                            }

                            html += '<tr>' +
                                    '<td>' + (c.created_at || '-') + '</td>' +
                                    '<td><strong>' + $('<div>').text(c.subject || '').html() + '</strong></td>' +
                                    '<td>' + (c.recipients_sent + c.recipients_failed) + '</td>' +
                                    '<td><span class="text-success">' + c.recipients_sent + '</span> / <span class="text-danger">' + c.recipients_failed + '</span></td>' +
                                    '<td>' + spamBadge + '</td>' +
                                    '<td><span class="badge badge-primary">' + (c.status || 'completed') + '</span></td>' +
                                    '</tr>';
                        });
                    }
                    $('#campaigns-history-body').html(html);
                }
            }, 'json');
        },

        loadSuppressions: function () {
            var self = this;
            $.post('?_task=newsletter&_action=plugin.newsletter-suppressions', {
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function (res) {
                if (res && res.suppressions) {
                    var html = '';
                    var entries = Object.values(res.suppressions);
                    if (entries.length === 0) {
                        html = '<tr><td colspan="4" class="text-center p-4 text-muted">No suppressed addresses found.</td></tr>';
                    } else {
                        $.each(entries, function (i, s) {
                            html += '<tr>' +
                                    '<td><code>' + $('<div>').text(s.email).html() + '</code></td>' +
                                    '<td><span class="badge badge-secondary">' + $('<div>').text(s.reason).html() + '</span></td>' +
                                    '<td>' + (s.created_at || '-') + '</td>' +
                                    '<td class="text-right">' +
                                    '<button type="button" class="btn btn-xs btn-outline-danger btn-remove-suppression" data-email="' + s.email + '">Remove</button>' +
                                    '</td>' +
                                    '</tr>';
                        });
                    }
                    $('#suppressions-body').html(html);

                    $('.btn-remove-suppression').on('click', function () {
                        var email = $(this).data('email');
                        if (confirm('Remove ' + email + ' from suppression list?')) {
                            self.removeSuppression(email);
                        }
                    });
                }
            }, 'json');
        },

        addSuppression: function (email) {
            var self = this;
            $.post('?_task=newsletter&_action=plugin.newsletter-suppressions', {
                sub_action: 'add',
                email: email,
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function () {
                self.loadSuppressions();
                self.refreshRecipients();
            }, 'json');
        },

        removeSuppression: function (email) {
            var self = this;
            $.post('?_task=newsletter&_action=plugin.newsletter-suppressions', {
                sub_action: 'remove',
                email: email,
                _token: (window.rcmail && rcmail.env && rcmail.env.request_token) ? rcmail.env.request_token : ''
            }, function () {
                self.loadSuppressions();
                self.refreshRecipients();
            }, 'json');
        }
    };

    $(document).ready(function () {
        NewsletterApp.init();
    });

})(window, document, window.jQuery || window.$);
