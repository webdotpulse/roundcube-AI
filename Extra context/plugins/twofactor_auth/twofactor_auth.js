/**
 * Two-Factor Authentication Plugin Client Controller
 */

function twofactor_start_setup() {
    var container = document.getElementById('twofactor-modal-container');
    if (!container) return;

    container.innerHTML = '<div class="twofactor-loading">Generating secure TOTP secret and QR code...</div>';
    container.style.display = 'block';

    var fd = new FormData();
    fd.append('_token', rcmail.env.request_token);

    fetch('?_action=plugin.twofactor_auth-setup', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data.success) {
                container.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Setup error') + '</div>';
                return;
            }

            var html = '<div class="twofactor-setup-panel">';
            html += '<h4>' + rcmail.gettext('twofactor_setup', 'twofactor_auth') + '</h4>';
            html += '<p class="text-muted">' + rcmail.gettext('twofactor_step1_scan', 'twofactor_auth') + '</p>';
            html += '<div class="twofactor-qr-wrapper">' + data.qr_svg + '</div>';
            html += '<p class="text-muted mt-2">' + rcmail.gettext('twofactor_step1_manual', 'twofactor_auth') + '</p>';
            html += '<div class="twofactor-secret-badge"><code>' + data.formatted_secret + '</code></div>';

            html += '<div class="twofactor-step-2 mt-3">';
            html += '<label for="twofactor-setup-code"><strong>' + rcmail.gettext('twofactor_step2_verify', 'twofactor_auth') + '</strong></label>';
            html += '<div class="input-group" style="max-width: 320px; margin: 8px 0;">';
            html += '<input type="text" id="twofactor-setup-code" class="form-control form-control-lg text-center font-monospace" placeholder="000000" maxlength="6" autofocus>';
            html += '<div class="input-group-append">';
            html += '<button type="button" class="btn btn-primary" onclick="twofactor_confirm_setup()">' + rcmail.gettext('twofactor_activate_btn', 'twofactor_auth') + '</button>';
            html += '</div></div>';
            html += '<div id="twofactor-setup-alert" class="alert alert-danger" style="display:none; margin-top: 8px;"></div>';
            html += '</div></div>';

            container.innerHTML = html;
            var inp = document.getElementById('twofactor-setup-code');
            if (inp) inp.focus();
        })
        .catch(function() {
            container.innerHTML = '<div class="alert alert-danger">Network error initializing 2FA setup.</div>';
        });
}

function twofactor_confirm_setup() {
    var codeInput = document.getElementById('twofactor-setup-code');
    var alertBox = document.getElementById('twofactor-setup-alert');
    if (!codeInput) return;

    var code = codeInput.value.trim();
    if (code.length !== 6) {
        alertBox.textContent = 'Please enter a valid 6-digit code.';
        alertBox.style.display = 'block';
        return;
    }

    var fd = new FormData();
    fd.append('_token', rcmail.env.request_token);
    fd.append('code', code);
    fd.append('method', 'totp');

    fetch('?_action=plugin.twofactor_auth-activate', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                twofactor_show_recovery_modal(data.recovery_codes);
            } else {
                alertBox.textContent = data.message || 'Invalid verification code.';
                alertBox.style.display = 'block';
            }
        });
}

function twofactor_show_recovery_modal(codes) {
    var container = document.getElementById('twofactor-modal-container');
    if (!container) return;

    var html = '<div class="twofactor-recovery-panel alert alert-warning">';
    html += '<h4>' + rcmail.gettext('twofactor_recovery_title', 'twofactor_auth') + '</h4>';
    html += '<p>' + rcmail.gettext('twofactor_recovery_desc', 'twofactor_auth') + '</p>';
    html += '<div class="twofactor-recovery-grid">';

    codes.forEach(function(code) {
        html += '<div class="twofactor-code-badge">' + code + '</div>';
    });
    html += '</div>';

    var plainCodes = codes.join("\n");
    html += '<div class="btn-group mt-3">';
    html += '<button type="button" class="btn btn-secondary btn-sm" onclick="twofactor_copy_codes(' + JSON.stringify(plainCodes) + ')">' + rcmail.gettext('twofactor_recovery_copy', 'twofactor_auth') + '</button>';
    html += '<button type="button" class="btn btn-secondary btn-sm" onclick="twofactor_download_codes(' + JSON.stringify(plainCodes) + ')">' + rcmail.gettext('twofactor_recovery_download', 'twofactor_auth') + '</button>';
    html += '<button type="button" class="btn btn-primary btn-sm" onclick="window.location.reload()">' + rcmail.gettext('save') + '</button>';
    html += '</div></div>';

    container.innerHTML = html;
}

function twofactor_copy_codes(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            rcmail.display_message(rcmail.gettext('twofactor_recovery_copied', 'twofactor_auth'), 'confirmation');
        });
    }
}

function twofactor_download_codes(text) {
    var blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'roundcube-recovery-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function twofactor_generate_recovery() {
    var fd = new FormData();
    fd.append('_token', rcmail.env.request_token);

    fetch('?_action=plugin.twofactor_auth-recovery', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                var container = document.getElementById('twofactor-modal-container');
                if (container) {
                    container.style.display = 'block';
                    twofactor_show_recovery_modal(data.recovery_codes);
                }
            } else {
                rcmail.display_message(data.message || 'Error generating recovery codes.', 'error');
            }
        });
}

function twofactor_disable() {
    if (!confirm(rcmail.gettext('twofactor_confirm_disable', 'twofactor_auth'))) {
        return;
    }

    var fd = new FormData();
    fd.append('_token', rcmail.env.request_token);

    fetch('?_action=plugin.twofactor_auth-disable', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                rcmail.display_message(data.message, 'confirmation');
                setTimeout(function() { window.location.reload(); }, 800);
            } else {
                rcmail.display_message(data.message || 'Error disabling 2FA.', 'error');
            }
        });
}
