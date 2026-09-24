/**
 * Easy Unsubscribe - Client-Side Controller
 *
 * Implements Gmail-style Unsubscribe button in Roundcube's message header view.
 * Handles user interaction, confirmation modal, AJAX dispatch, and UI updates.
 *
 * @version 1.0.0
 * @author Webdotpulse <support@webdotpulse.com>
 * @license GNU GPLv3+
 */

(function(window, document, $) {
    'use strict';

    if (!window.rcmail) {
        return;
    }

    var rcmail = window.rcmail;

    /**
     * Main EasyUnsubscribe controller object.
     */
    var EasyUnsubscribe = {
        initialized: false,
        activeData: null,
        isProcessing: false,
        modalElement: null,

        /**
         * Initialize the plugin listeners.
         */
        init: function() {
            var self = this;

            // Listen for server response command
            rcmail.addEventListener('plugin.easy_unsubscribe_result', function(response) {
                self.handleServerResponse(response);
            });

            // Listen for message display hooks in Roundcube
            rcmail.addEventListener('init', function() {
                self.setup();
            });

            // Handle preview frame or AJAX message updates
            rcmail.addEventListener('message-load', function(e) {
                self.onMessageLoad(e);
            });

            rcmail.addEventListener('insert-message', function(e) {
                self.onMessageLoad(e);
            });

            rcmail.addEventListener('responseafterlist', function() {
                // If preview pane exists, monitor its loading
                self.monitorPreviewFrame();
            });

            // DOM ready fallback
            $(document).ready(function() {
                self.setup();
                self.monitorPreviewFrame();
            });
        },

        /**
         * Setup UI injection.
         */
        setup: function() {
            var self = this;
            var data = rcmail.env.easy_unsubscribe_data;

            if (data && typeof data === 'object') {
                self.activeData = data;
                self.renderButton(document, data);
            }

            // Also check preview frame if message is loaded inside an iframe
            self.monitorPreviewFrame();
        },

        /**
         * Monitor preview iframe for header changes.
         */
        monitorPreviewFrame: function() {
            var self = this;
            var frame = document.getElementById('messagecontframe');
            if (frame) {
                $(frame).off('load.easy_unsubscribe').on('load.easy_unsubscribe', function() {
                    try {
                        var frameDoc = frame.contentDocument || frame.contentWindow.document;
                        var frameRcmail = frame.contentWindow ? frame.contentWindow.rcmail : null;
                        var frameData = (frameRcmail && frameRcmail.env && frameRcmail.env.easy_unsubscribe_data)
                            ? frameRcmail.env.easy_unsubscribe_data
                            : rcmail.env.easy_unsubscribe_data;

                        if (frameDoc && frameData) {
                            self.activeData = frameData;
                            self.renderButton(frameDoc, frameData);
                        }
                    } catch (e) {
                        // Cross-origin or frame access exception
                    }
                });
            }
        },

        /**
         * Handle dynamic message load event.
         */
        onMessageLoad: function(e) {
            var self = this;
            setTimeout(function() {
                var data = rcmail.env.easy_unsubscribe_data;
                if (data && typeof data === 'object') {
                    self.activeData = data;
                    self.renderButton(document, data);
                }
                self.monitorPreviewFrame();
            }, 50);
        },

        /**
         * Get localized label with fallback.
         */
        getText: function(key, fallback) {
            try {
                if (rcmail.gettext) {
                    var val = rcmail.gettext(key, 'easy_unsubscribe');
                    if (val && val !== key) {
                        return val;
                    }
                    val = rcmail.gettext('easy_unsubscribe.' + key);
                    if (val && val !== 'easy_unsubscribe.' + key) {
                        return val;
                    }
                }
            } catch (e) {}

            return fallback || key;
        },

        /**
         * Render the Unsubscribe button into the message header DOM.
         */
        renderButton: function(contextDoc, data) {
            var self = this;
            if (!contextDoc || !data) {
                return;
            }

            var $doc = $(contextDoc);

            // Avoid duplicate rendering
            if ($doc.find('#easy-unsubscribe-wrapper').length > 0) {
                return;
            }

            // Locate target insertion point next to the sender information
            // Supports Elastic and Larry skin selectors
            var $target = null;

            // Elastic selectors
            var $fromAdr = $doc.find('.header-from .adr, span.header-from .adr');
            if ($fromAdr.length > 0) {
                $target = $fromAdr.first();
            } else {
                var $headerFrom = $doc.find('span.header-from, .header-from');
                if ($headerFrom.length > 0) {
                    $target = $headerFrom.first();
                }
            }

            // Larry & Classic selectors
            if (!$target || $target.length === 0) {
                var $larryFrom = $doc.find('#messageheader td.from span.adr, #messageheader td.from, .message-part-headers td.from');
                if ($larryFrom.length > 0) {
                    $target = $larryFrom.first();
                }
            }

            // Fallback: title/headers block
            if (!$target || $target.length === 0) {
                var $fallback = $doc.find('.header-title, .header-headers, #messageheader');
                if ($fallback.length > 0) {
                    $target = $fallback.first();
                }
            }

            if (!$target || $target.length === 0) {
                return;
            }

            var isUnsubscribed = (data.status === 'unsubscribed');
            var wrapper = document.createElement('span');
            wrapper.id = 'easy-unsubscribe-wrapper';
            wrapper.className = 'easy-unsubscribe-wrapper';

            var sep = document.createElement('span');
            sep.className = 'easy-unsubscribe-sep';
            sep.setAttribute('aria-hidden', 'true');
            sep.textContent = '•';
            wrapper.appendChild(sep);

            if (isUnsubscribed) {
                // Render disabled "Unsubscribed" badge
                var badge = self.createBadgeElement(contextDoc);
                wrapper.appendChild(badge);
            } else {
                // Render interactive "Unsubscribe" button
                var btn = self.createButtonElement(contextDoc, data);
                wrapper.appendChild(btn);
            }

            // Inject immediately after the target element
            $target.after(wrapper);
        },

        /**
         * Create the interactive button DOM element.
         */
        createButtonElement: function(contextDoc, data) {
            var self = this;
            var btn = contextDoc.createElement('button');
            btn.type = 'button';
            btn.id = 'easy-unsubscribe-btn';
            btn.className = 'easy-unsubscribe-btn';
            btn.setAttribute('title', self.getText('tooltip_button', 'Unsubscribe from this mailing list'));
            btn.setAttribute('aria-label', self.getText('tooltip_button', 'Unsubscribe from this mailing list'));

            var icon = contextDoc.createElement('span');
            icon.className = 'easy-unsubscribe-icon';
            icon.innerHTML = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>';
            btn.appendChild(icon);

            var label = contextDoc.createElement('span');
            label.className = 'easy-unsubscribe-label';
            label.textContent = self.getText('unsubscribe', 'Unsubscribe');
            btn.appendChild(label);

            $(btn).on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!self.isProcessing) {
                    self.openConfirmModal(data);
                }
            });

            return btn;
        },

        /**
         * Create the disabled "Unsubscribed" badge DOM element.
         */
        createBadgeElement: function(contextDoc) {
            var self = this;
            var badge = contextDoc.createElement('span');
            badge.className = 'easy-unsubscribe-badge unsubscribed';
            badge.setAttribute('title', self.getText('tooltip_unsubscribed', 'You have unsubscribed from this mailing list.'));

            var checkIcon = contextDoc.createElement('span');
            checkIcon.className = 'easy-unsubscribe-badge-icon';
            checkIcon.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            badge.appendChild(checkIcon);

            var label = contextDoc.createElement('span');
            label.className = 'easy-unsubscribe-badge-label';
            label.textContent = self.getText('unsubscribed', 'Unsubscribed');
            badge.appendChild(label);

            return badge;
        },

        /**
         * Open the confirmation modal dialog.
         */
        openConfirmModal: function(data) {
            var self = this;
            self.closeModal();

            var senderName = self.escapeHtml(data.sender_name || 'this sender');
            var title = self.getText('modal_title', 'Unsubscribe from %s?').replace('%s', senderName);
            var isHttp = (data.type === 'http');
            var isMailto = (data.type === 'mailto');
            var descHtml = '';

            if (isHttp) {
                descHtml = self.getText('modal_desc_http', 'This mailing list requires you to unsubscribe through their website. Would you like to open their unsubscribe page in a new window?');
                if (data.url) {
                    descHtml += '<div class="easy-unsub-url-preview"><code>' + self.escapeHtml(data.url) + '</code></div>';
                }
            } else if (isMailto) {
                var mailtoEmail = data.mailto && data.mailto.email ? self.escapeHtml(data.mailto.email) : senderName;
                var tmpl = self.getText('modal_desc_mailto', 'Are you sure you want to unsubscribe from <strong>%s</strong>? Roundcube will send an unsubscribe email on your behalf to <code>%s</code>.');
                descHtml = tmpl.replace('%s', senderName).replace('%s', mailtoEmail);
            } else {
                // One-click RFC 8058
                var tmplOneClick = self.getText('modal_desc_oneclick', 'Are you sure you want to unsubscribe from <strong>%s</strong>? Roundcube will automatically send an opt-out request on your behalf.');
                descHtml = tmplOneClick.replace('%s', senderName);
            }

            // Build Modal Overlay
            var overlay = document.createElement('div');
            overlay.id = 'easy-unsubscribe-modal-overlay';
            overlay.className = 'easy-unsubscribe-modal-overlay';

            var dialog = document.createElement('div');
            dialog.className = 'easy-unsubscribe-modal-dialog';
            dialog.setAttribute('role', 'dialog');
            dialog.setAttribute('aria-modal', 'true');

            // Header
            var header = document.createElement('div');
            header.className = 'easy-unsubscribe-modal-header';

            var titleEl = document.createElement('h3');
            titleEl.className = 'easy-unsubscribe-modal-title';
            titleEl.textContent = title;
            header.appendChild(titleEl);

            var closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'easy-unsubscribe-modal-close';
            closeBtn.setAttribute('aria-label', self.getText('btn_close', 'Close'));
            closeBtn.innerHTML = '&times;';
            $(closeBtn).on('click', function() {
                self.closeModal();
            });
            header.appendChild(closeBtn);
            dialog.appendChild(header);

            // Body
            var body = document.createElement('div');
            body.className = 'easy-unsubscribe-modal-body';
            body.innerHTML = descHtml;
            dialog.appendChild(body);

            // Footer
            var footer = document.createElement('div');
            footer.className = 'easy-unsubscribe-modal-footer';

            var cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'btn btn-secondary easy-unsub-btn-cancel';
            cancelBtn.textContent = self.getText('btn_cancel', 'Cancel');
            $(cancelBtn).on('click', function() {
                self.closeModal();
            });
            footer.appendChild(cancelBtn);

            if (isHttp) {
                var visitBtn = document.createElement('a');
                visitBtn.href = data.url;
                visitBtn.target = '_blank';
                visitBtn.rel = 'noopener noreferrer';
                visitBtn.className = 'btn btn-primary easy-unsub-btn-confirm';
                visitBtn.innerHTML = self.getText('btn_visit_website', 'Go to Website') + ' <span class="easy-unsub-external-icon">↗</span>';
                $(visitBtn).on('click', function() {
                    self.closeModal();
                    if (rcmail.display_message) {
                        rcmail.display_message(self.getText('notice_http_opened', 'External unsubscribe page opened in a new window.'), 'notice');
                    }
                });
                footer.appendChild(visitBtn);
            } else {
                var confirmBtn = document.createElement('button');
                confirmBtn.type = 'button';
                confirmBtn.id = 'easy-unsub-confirm-action-btn';
                confirmBtn.className = 'btn btn-danger easy-unsub-btn-confirm';
                confirmBtn.textContent = self.getText('btn_unsubscribe', 'Unsubscribe');

                $(confirmBtn).on('click', function() {
                    self.executeUnsubscribe(data, confirmBtn);
                });
                footer.appendChild(confirmBtn);
            }

            dialog.appendChild(footer);
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);
            self.modalElement = overlay;

            // Trigger animation
            setTimeout(function() {
                $(overlay).addClass('visible');
            }, 10);

            // Close on escape key or backdrop click
            $(overlay).on('click', function(e) {
                if (e.target === overlay) {
                    self.closeModal();
                }
            });

            $(document).off('keydown.easy_unsub_esc').on('keydown.easy_unsub_esc', function(e) {
                if (e.key === 'Escape' || e.keyCode === 27) {
                    self.closeModal();
                }
            });
        },

        /**
         * Close and remove the modal.
         */
        closeModal: function() {
            var self = this;
            if (self.modalElement) {
                $(self.modalElement).removeClass('visible');
                setTimeout(function() {
                    $(self.modalElement).remove();
                    self.modalElement = null;
                }, 200);
            }
            $(document).off('keydown.easy_unsub_esc');
        },

        /**
         * Dispatch AJAX request to plugin.easy_unsubscribe.
         */
        executeUnsubscribe: function(data, buttonEl) {
            var self = this;
            if (self.isProcessing) {
                return;
            }

            self.isProcessing = true;

            // Update button to busy state
            var $btn = $(buttonEl);
            var originalText = $btn.text();
            $btn.prop('disabled', true).addClass('busy');
            $btn.html('<span class="easy-unsub-spinner"></span> ' + self.getText('unsubscribing', 'Unsubscribing...'));

            var lock = rcmail.set_busy ? rcmail.set_busy(true, 'loading') : null;

            var postData = {
                _uid: data.uid,
                _mbox: data.mbox,
                _type: data.type
            };

            // Call Roundcube backend action
            rcmail.http_post('plugin.easy_unsubscribe', postData, lock);
        },

        /**
         * Handle backend response command from server.
         */
        handleServerResponse: function(response) {
            var self = this;
            self.isProcessing = false;

            if (rcmail.set_busy) {
                rcmail.set_busy(false);
            }

            if (!response) {
                self.closeModal();
                rcmail.display_message(self.getText('error_failed', 'Could not process unsubscribe request.'), 'error');
                return;
            }

            if (response.success && response.status === 'unsubscribed') {
                self.closeModal();

                // Display native confirmation toast
                var msg = response.message || self.getText('success_oneclick', 'Successfully unsubscribed.');
                rcmail.display_message(msg, 'confirmation');

                // Update active data state
                if (self.activeData) {
                    self.activeData.status = 'unsubscribed';
                }
                if (rcmail.env.easy_unsubscribe_data) {
                    rcmail.env.easy_unsubscribe_data.status = 'unsubscribed';
                }

                // Update all buttons in top document and preview iframe to disabled badge
                self.transformButtonToBadge(document);
                var frame = document.getElementById('messagecontframe');
                if (frame && frame.contentDocument) {
                    self.transformButtonToBadge(frame.contentDocument);
                }
            } else if (response.status === 'redirect_required' && response.url) {
                self.closeModal();
                window.open(response.url, '_blank', 'noopener,noreferrer');
                rcmail.display_message(self.getText('notice_http_opened', 'Opened unsubscribe page in a new window.'), 'notice');
            } else {
                // Error state
                var errMsg = response.message || self.getText('error_failed', 'Could not process unsubscribe request.');
                rcmail.display_message(errMsg, 'error');

                // Restore button in modal
                var $btn = $('#easy-unsub-confirm-action-btn');
                if ($btn.length > 0) {
                    $btn.prop('disabled', false).removeClass('busy');
                    $btn.text(self.getText('btn_unsubscribe', 'Unsubscribe'));
                }
            }
        },

        /**
         * Transform the active Unsubscribe button into a disabled "Unsubscribed" badge.
         */
        transformButtonToBadge: function(contextDoc) {
            var self = this;
            var $doc = $(contextDoc);
            var $wrapper = $doc.find('#easy-unsubscribe-wrapper');

            if ($wrapper.length > 0) {
                $wrapper.find('#easy-unsubscribe-btn').remove();
                $wrapper.find('.easy-unsubscribe-badge').remove();

                var badge = self.createBadgeElement(contextDoc);
                $wrapper.append(badge);

                // Add smooth subtle flash animation
                $(badge).hide().fadeIn(300);
            }
        },

        /**
         * Escape HTML entities to prevent XSS.
         */
        escapeHtml: function(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    };

    // Initialize EasyUnsubscribe
    EasyUnsubscribe.init();

    // Export to global scope for debugging or integration testing
    window.EasyUnsubscribe = EasyUnsubscribe;

})(window, document, window.jQuery);
