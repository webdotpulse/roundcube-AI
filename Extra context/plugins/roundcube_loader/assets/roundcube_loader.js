/**
 * Roundcube Loader Plugin — Gmail-style Loading Screen Controller
 *
 * @license GNU GPLv3+
 * @author Webdotpulse & LifePrisma
 */

(function (window, document) {
  'use strict';

  var timerProgress = null;
  var timerFallback = null;
  var currentProgress = 0;
  var isFinished = false;

  function getConfig() {
    return (
      (window.rcmail && window.rcmail.env && window.rcmail.env.roundcube_loader_config) ||
      window.__rc_loader_config || {
        enabled: true,
        show_username: true,
        show_subtext: true,
        splash_on_login: true,
        splash_on_startup: false,
        timeout: 15,
      }
    );
  }

  function getLabel(key, fallback) {
    if (window.rcmail && typeof window.rcmail.gettext === 'function') {
      var translated = window.rcmail.gettext(key, 'roundcube_loader');
      if (translated && translated !== key) {
        return translated;
      }
    }
    return fallback;
  }

  function getLoaderElements() {
    var loader = document.getElementById('rc-page-loader');
    if (!loader) {
      return null;
    }
    return {
      loader: loader,
      bar: loader.querySelector('.rc-loader-bar'),
      text: loader.querySelector('.rc-loader-text'),
      fallback: loader.querySelector('.rc-loader-fallback'),
      reloadLink: loader.querySelector('.rc-loader-reload-action'),
    };
  }

  function setProgress(percentage) {
    var els = getLoaderElements();
    if (!els || !els.bar) return;
    currentProgress = Math.min(100, Math.max(0, percentage));
    els.bar.style.width = currentProgress + '%';
  }

  function showLoader(username, customMessage) {
    var config = getConfig();
    if (config.enabled === false) return;

    var els = getLoaderElements();
    if (!els || !els.loader) return;

    isFinished = false;
    currentProgress = 0;
    setProgress(0);

    // Update status text
    if (els.text) {
      if (customMessage) {
        els.text.textContent = customMessage;
      } else if (username && config.show_username) {
        var pattern = getLabel('loading_user', 'Loading %s...');
        els.text.innerHTML = pattern.replace(
          '%s',
          '<span class="rc-loader-username">' + escapeHtml(username) + '</span>'
        );
      } else {
        els.text.textContent = getLabel('signing_in', 'Signing in...');
      }
    }

    // Reset fallback prompt
    if (els.fallback) {
      els.fallback.style.display = 'none';
    }

    // Reveal overlay
    els.loader.classList.remove('rc-loader-hidden');
    els.loader.style.display = 'flex';

    // Begin progressive bar animation
    if (timerProgress) clearInterval(timerProgress);

    setTimeout(function () {
      if (!isFinished) setProgress(18);
    }, 40);

    setTimeout(function () {
      if (!isFinished) setProgress(42);
    }, 400);

    setTimeout(function () {
      if (!isFinished) setProgress(68);
    }, 1100);

    setTimeout(function () {
      if (!isFinished) setProgress(82);
    }, 2200);

    setTimeout(function () {
      if (!isFinished) setProgress(90);
    }, 3800);

    // Setup fallback prompt timeout
    if (timerFallback) clearTimeout(timerFallback);
    var timeoutSec = (config.timeout || 15) * 1000;
    timerFallback = setTimeout(function () {
      if (!isFinished && els.fallback) {
        els.fallback.style.display = 'block';
      }
    }, timeoutSec);
  }

  function finishLoader() {
    if (isFinished) return;
    isFinished = true;

    var els = getLoaderElements();
    if (!els || !els.loader) return;

    if (timerProgress) clearInterval(timerProgress);
    if (timerFallback) clearTimeout(timerFallback);

    // Jump to 100%
    setProgress(100);

    // Fade out after a moment for smooth visual closure
    setTimeout(function () {
      els.loader.classList.add('rc-loader-hidden');
      setTimeout(function () {
        els.loader.style.display = 'none';
        try {
          sessionStorage.removeItem('rc_loader_active');
          sessionStorage.removeItem('rc_loader_user');
        } catch (e) {}
      }, 420);
    }, 250);
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function bindLoginForm() {
    var config = getConfig();
    if (config.enabled === false || config.splash_on_login === false) return;

    var loginForm =
      document.getElementById('login-form') ||
      document.querySelector('form[name="form"]') ||
      document.querySelector('form[action*="_task=login"]');

    if (!loginForm) return;

    loginForm.addEventListener('submit', function (e) {
      var userInp =
        loginForm.querySelector('input[name="_user"]') ||
        document.getElementById('rcmloginuser');
      var passInp =
        loginForm.querySelector('input[name="_pass"]') ||
        document.getElementById('rcmloginpwd');

      var userVal = userInp ? userInp.value.trim() : '';
      var passVal = passInp ? passInp.value : '';

      // If required fields are left blank, allow default form validation
      if (!userVal || !passVal) {
        return;
      }

      try {
        sessionStorage.setItem('rc_loader_active', '1');
        if (userVal) {
          sessionStorage.setItem('rc_loader_user', userVal);
        }
      } catch (err) {}

      showLoader(userVal);
    });
  }

  function bindAppStartup() {
    var config = getConfig();
    if (config.enabled === false) return;

    var els = getLoaderElements();
    if (!els || !els.loader) return;

    var isLoginTask =
      (window.rcmail && window.rcmail.env && window.rcmail.env.task === 'login') ||
      Boolean(document.getElementById('rcmloginuser'));

    if (isLoginTask) {
      // If on login page, hide loader initially so user can see credentials form
      els.loader.classList.add('rc-loader-hidden');
      els.loader.style.display = 'none';
      return;
    }

    // On webmail app load: only run loader if coming from active login submit
    var justLoggedIn = false;
    var cachedUser = '';
    try {
      justLoggedIn = sessionStorage.getItem('rc_loader_active') === '1';
      cachedUser = sessionStorage.getItem('rc_loader_user') || '';
    } catch (e) {}

    // Must be active login flow; never run on normal page/task transitions (e.g. mail -> settings)
    if (justLoggedIn) {
      els.loader.classList.remove('rc-loader-hidden');
      els.loader.style.display = 'flex';

      // Progress through final loading stage
      setProgress(65);
      if (els.text && cachedUser && config.show_username) {
        var pattern = getLabel('loading_user', 'Loading %s...');
        els.text.innerHTML = pattern.replace(
          '%s',
          '<span class="rc-loader-username">' + escapeHtml(cachedUser) + '</span>'
        );
      }

      setTimeout(function () {
        if (!isFinished) setProgress(88);
      }, 150);

      // Listen for window load
      if (document.readyState === 'complete') {
        setTimeout(finishLoader, 200);
      } else {
        window.addEventListener('load', function () {
          setTimeout(finishLoader, 200);
        });
      }

      // Listen for Roundcube init event
      if (window.rcmail && typeof window.rcmail.addEventListener === 'function') {
        window.rcmail.addEventListener('init', function () {
          setTimeout(finishLoader, 150);
        });
      }
    } else {
      // Hide immediately and remove from DOM if not an active login redirect
      els.loader.classList.add('rc-loader-hidden');
      els.loader.style.display = 'none';
      if (els.loader.parentNode) {
        els.loader.parentNode.removeChild(els.loader);
      }
    }
  }

  // Fallback reload link handler
  function bindReloadAction() {
    var els = getLoaderElements();
    if (!els || !els.reloadLink) return;
    els.reloadLink.addEventListener('click', function (e) {
      e.preventDefault();
      try {
        sessionStorage.removeItem('rc_loader_active');
      } catch (err) {}
      window.location.reload();
    });
  }

  // Initialization
  function init() {
    bindLoginForm();
    bindAppStartup();
    bindReloadAction();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Public API
  window.RoundcubeLoader = {
    show: showLoader,
    finish: finishLoader,
    setProgress: setProgress,
  };
})(window, document);
