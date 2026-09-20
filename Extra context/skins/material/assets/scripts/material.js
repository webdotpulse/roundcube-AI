/**
 * Material Design Skin for Roundcube - Interactive Scripts
 * Handles Material ripple animations, FAB compose interactions,
 * and live preview of customizable button colors.
 *
 * @license MIT
 * @author LifePrisma AI & Webdotpulse
 */

(function (window, document) {
    'use strict';

    var material = window.material || {};
    window.material = material;

    material.customConfig = material.customConfig || { primary: '', secondary: '', radius: '' };

    /**
     * Apply customizable button colors directly to the DOM and parent frames.
     *
     * @param {string} type - 'primary', 'secondary', or 'radius'
     * @param {string} value - CSS color hex (e.g. '#1a73e8') or radius (e.g. '20px')
     */
    material.applyButtonColor = function (type, value) {
        var val = (value || '').trim();
        var styleId = 'material-custom-button-colors';

        if (type in material.customConfig) {
            material.customConfig[type] = val;
        }

        function updateDoc(doc) {
            if (!doc) return;

            if (type === 'primary' && val) {
                doc.documentElement.style.setProperty('--md-btn-primary-bg', val);
            } else if (type === 'secondary' && val) {
                doc.documentElement.style.setProperty('--md-btn-secondary-bg', val);
            } else if (type === 'radius' && val) {
                doc.documentElement.style.setProperty('--md-btn-radius', val);
            }

            if (!doc.head) return;
            var el = doc.getElementById(styleId);
            if (!el) {
                el = doc.createElement('style');
                el.id = styleId;
                el.type = 'text/css';
                doc.head.appendChild(el);
            }

            var cssVars = [];
            var directRules = [];
            if (material.customConfig.primary) {
                var p = material.customConfig.primary;
                cssVars.push('--md-btn-primary-bg: ' + p + ' !important;');
                directRules.push('.btn-primary, button.mainaction, input[type="submit"].mainaction, .formbuttons .btn-primary, .btn-material-primary { background-color: ' + p + ' !important; transition: none !important; }');
            }
            if (material.customConfig.secondary) {
                var s = material.customConfig.secondary;
                cssVars.push('--md-btn-secondary-bg: ' + s + ' !important;');
                directRules.push('.btn-secondary, .btn-outline-secondary, button.cancel, .formbuttons .btn-secondary, .btn-material-secondary { background-color: ' + s + ' !important; transition: none !important; }');
            }
            if (material.customConfig.radius) {
                var r = material.customConfig.radius;
                cssVars.push('--md-btn-radius: ' + r + ' !important;');
                directRules.push('.btn-primary, .btn-secondary, button.mainaction, .btn, .btn-material-primary, .btn-material-secondary { border-radius: ' + r + ' !important; transition: none !important; }');
            }

            el.textContent = ':root, html, body { ' + cssVars.join(' ') + ' }\n' + directRules.join('\n');
        }

        try {
            updateDoc(document);
            if (window.parent && window.parent.document && window.parent.document !== document) {
                updateDoc(window.parent.document);
            }
        } catch (e) {
            // Ignore cross-origin frame access if any
        }
    };

    /**
     * Initialize Material Ripple micro-interactions on buttons and interactive cards.
     */
    material.initRipple = function () {
        var rippleSelectors = [
            '.btn',
            '.btn-primary',
            '.btn-secondary',
            'button.mainaction',
            'a.button.compose',
            '#compose-plus',
            '.toolbar a.button',
            '#mailboxlist li.mailbox > a'
        ].join(',');

        document.addEventListener('click', function (e) {
            var target = e.target.closest(rippleSelectors);
            if (!target) return;

            var rect = target.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var x = e.clientX - rect.left - size / 2;
            var y = e.clientY - rect.top - size / 2;

            var ripple = document.createElement('span');
            ripple.className = 'wave';
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';

            target.classList.add('wave-container');
            target.appendChild(ripple);

            setTimeout(function () {
                ripple.remove();
            }, 600);
        }, false);
    };

    /**
     * Enhance Floating Action Button (FAB) compose button.
     */
    material.initFab = function () {
        var fab = document.querySelector('#compose-plus, a.button.compose');
        if (fab) {
            fab.setAttribute('role', 'button');
            fab.setAttribute('aria-label', 'Compose');
        }
    };

    /**
     * Initialize on DOM ready.
     */
    function onReady() {
        material.initRipple();
        material.initFab();

        // Check for skin preferences in Roundcube environment if available
        if (window.rcmail && window.rcmail.env) {
            if (window.rcmail.env.material_btn_primary) {
                material.applyButtonColor('primary', window.rcmail.env.material_btn_primary);
            }
            if (window.rcmail.env.material_btn_secondary) {
                material.applyButtonColor('secondary', window.rcmail.env.material_btn_secondary);
            }
            if (window.rcmail.env.material_btn_radius) {
                material.applyButtonColor('radius', window.rcmail.env.material_btn_radius);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }

})(window, document);
