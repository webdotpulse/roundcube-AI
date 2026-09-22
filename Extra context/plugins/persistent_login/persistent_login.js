/**
 * Persistent Login Plugin Frontend Script
 * Handles login form integration and asynchronous session revocation in settings.
 */

(function () {
    'use strict';

    /**
     * Injects the "Keep me logged in" checkbox into Roundcube login forms.
     */
    function initLoginCheckbox() {
        // Prevent duplicate injections
        if (document.querySelector('input[name="_persistent_login"]') || document.querySelector('input[name="_remember_me"]')) {
            return;
        }

        // Locate standard Roundcube login form
        var loginForm = document.getElementById('login-form') ||
                        document.querySelector('form[name="form"]') ||
                        document.querySelector('form[name="_login"]') ||
                        document.querySelector('form[action*="_task=login"]');

        if (!loginForm) {
            return;
        }

        // Ensure this is truly a login form with username and password
        var userInput = loginForm.querySelector('input[name="_user"]');
        var passInput = loginForm.querySelector('input[name="_pass"]');
        if (!userInput || !passInput) {
            return;
        }

        // Get localized label from rcmail or fallback
        var labelText = (window.rcmail && rcmail.gettext('remember_me', 'persistent_login')) || 'Keep me logged in';
        var defaultChecked = window.rcmail_persistent_login_default || false;

        // Container element
        var group = document.createElement('div');
        group.className = 'persistent-login-group form-group';

        // Checkbox element
        var checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = '_persistent_login';
        checkbox.id = 'rcmfd_persistent_login';
        checkbox.value = '1';
        checkbox.className = 'persistent-login-checkbox';
        if (defaultChecked) {
            checkbox.checked = true;
        }

        // Label element
        var label = document.createElement('label');
        label.htmlFor = 'rcmfd_persistent_login';
        label.className = 'persistent-login-label';

        var labelSpan = document.createElement('span');
        labelSpan.textContent = labelText;

        label.appendChild(checkbox);
        label.appendChild(labelSpan);
        group.appendChild(label);

        // Find appropriate insertion point (immediately before submit button or button container)
        var submitBtn = loginForm.querySelector('button[type="submit"]') ||
                        loginForm.querySelector('input[type="submit"]') ||
                        loginForm.querySelector('.formbuttons') ||
                        loginForm.querySelector('.form-group:last-of-type');

        if (submitBtn) {
            // Check if submit button is wrapped in a container
            var insertTarget = submitBtn;
            if (submitBtn.closest && submitBtn.closest('.form-group') && submitBtn.closest('.form-group') !== loginForm) {
                insertTarget = submitBtn.closest('.form-group');
            } else if (submitBtn.closest && submitBtn.closest('p.formbuttons')) {
                insertTarget = submitBtn.closest('p.formbuttons');
            }
            insertTarget.parentNode.insertBefore(group, insertTarget);
        } else {
            loginForm.appendChild(group);
        }
    }

    /**
     * Initializes interactive revocation handlers on Settings -> Trusted Devices.
     */
    function initSettingsHandlers() {
        var container = document.getElementById('persistent-sessions-wrapper');
        if (!container) {
            return;
        }

        // Individual session revoke handler
        container.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-revoke-session');
            if (!btn) return;

            e.preventDefault();
            var series = btn.getAttribute('data-series');
            if (!series) return;

            var confirmMsg = (window.rcmail && rcmail.gettext('revoke_confirm', 'persistent_login')) ||
                             'Are you sure you want to revoke this remembered device session?';
            if (!window.confirm(confirmMsg)) {
                return;
            }

            btn.disabled = true;
            btn.textContent = '...';

            var postData = {
                _series: series,
                _token: (window.rcmail && rcmail.env.request_token) || ''
            };

            var requestUrl = '?_task=settings&_action=plugin.persistent_login-revoke';
            fetch(requestUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(postData).toString()
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.success) {
                    var row = document.getElementById('session-row-' + series);
                    if (row) {
                        row.classList.add('session-row-fade-out');
                        setTimeout(function () {
                            row.parentNode && row.parentNode.removeChild(row);
                            checkEmptySessions();
                        }, 300);
                    }
                    if (window.rcmail && rcmail.display_message) {
                        rcmail.display_message(data.message || 'Session revoked.', 'confirmation');
                    }
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Revoke';
                    if (window.rcmail && rcmail.display_message) {
                        rcmail.display_message((data && data.message) || 'Failed to revoke session.', 'error');
                    }
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.textContent = 'Revoke';
                if (window.rcmail && rcmail.display_message) {
                    rcmail.display_message('Network error while revoking session.', 'error');
                }
            });
        });

        // Revoke all other sessions button handler
        var revokeAllBtn = document.getElementById('btn-revoke-all-other');
        if (revokeAllBtn) {
            revokeAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                var confirmAllMsg = (window.rcmail && rcmail.gettext('revoke_all_confirm', 'persistent_login')) ||
                                    'Are you sure you want to sign out all other remembered devices?';
                if (!window.confirm(confirmAllMsg)) {
                    return;
                }

                revokeAllBtn.disabled = true;
                var origText = revokeAllBtn.textContent;
                revokeAllBtn.textContent = '...';

                var postData = {
                    _token: (window.rcmail && rcmail.env.request_token) || ''
                };

                var requestUrl = '?_task=settings&_action=plugin.persistent_login-revoke-all';
                fetch(requestUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams(postData).toString()
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && data.success) {
                        // Remove all rows that are not the current device
                        var rows = container.querySelectorAll('.persistent-sessions-table tbody tr:not(.session-row-current)');
                        rows.forEach(function (row) {
                            row.classList.add('session-row-fade-out');
                            setTimeout(function () {
                                row.parentNode && row.parentNode.removeChild(row);
                            }, 300);
                        });
                        revokeAllBtn.style.display = 'none';

                        if (window.rcmail && rcmail.display_message) {
                            rcmail.display_message(data.message || 'All other sessions revoked.', 'confirmation');
                        }
                    } else {
                        revokeAllBtn.disabled = false;
                        revokeAllBtn.textContent = origText;
                        if (window.rcmail && rcmail.display_message) {
                            rcmail.display_message((data && data.message) || 'Error revoking sessions.', 'error');
                        }
                    }
                })
                .catch(function () {
                    revokeAllBtn.disabled = false;
                    revokeAllBtn.textContent = origText;
                    if (window.rcmail && rcmail.display_message) {
                        rcmail.display_message('Network error revoking sessions.', 'error');
                    }
                });
            });
        }
    }

    function checkEmptySessions() {
        var tbody = document.querySelector('.persistent-sessions-table tbody');
        if (tbody && tbody.querySelectorAll('tr').length === 0) {
            var emptyRow = document.createElement('tr');
            var emptyCell = document.createElement('td');
            emptyCell.colSpan = 5;
            emptyCell.className = 'text-center text-muted p-4';
            emptyCell.textContent = (window.rcmail && rcmail.gettext('no_active_sessions', 'persistent_login')) || 'No active persistent sessions found.';
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);

            var revokeAllBtn = document.getElementById('btn-revoke-all-other');
            if (revokeAllBtn) revokeAllBtn.style.display = 'none';
        }
    }

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initLoginCheckbox();
            initSettingsHandlers();
        });
    } else {
        initLoginCheckbox();
        initSettingsHandlers();
    }
})();
