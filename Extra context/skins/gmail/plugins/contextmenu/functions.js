/**
 * ContextMenu plugin — roundcube-gmail skin glue (replaces plugins/contextmenu/skins/elastic/functions.js).
 * Registers which toolbar buttons / menus feed each right-click menu. Vanilla JS; the plugin
 * itself is jQuery-based and reads the settings object we extend here.
 */
(function () {
  function merge(target, src) {
    for (const k of Object.keys(src)) {
      const v = src[k];
      if (v && typeof v === 'object' && !Array.isArray(v) && !(v instanceof RegExp)) {
        target[k] = merge(target[k] && typeof target[k] === 'object' ? target[k] : {}, v);
      } else target[k] = v;
    }
    return target;
  }

  function init() {
    if (!window.rcmail || !rcmail.contextmenu) return;
    merge(rcmail.contextmenu.settings, {
      popup_attrib: 'data-popup',
      popup_func: "rcm_popup_wrapper('$2');",
      popup_pattern: /rcm_popup_wrapper\('([^']+)'|^([a-z0-9-]+)$/i,
      classes: {
        container: 'contextmenu gm-contextmenu',
        button_remove: '',
        modal_overlay: 'popover-overlay',
      },
      menu_defaults: {
        modal: true,
        classes: {
          ul: 'menu listing',
          a: 'button rcmbutton',
          sub_button_a: 'rcmsubbutton',
          sub_button_span: null,
        },
      },
      global_events: {
        init: function (p) {
          if (p.ref.is_submenu) p.ref.skinable = true;
        },
        insertitem: function (p) {
          const elem = p.originalElement;
          if (elem.attr('data-popup') || elem.attr('aria-haspopup')) {
            const a = p.item.children('a');
            a.data('level', p.ref.parents + 2);
            a.attr('aria-haspopup', true);
          }
        },
        beforeactivate: function (p) {
          p.ref.mouseover_timeout = document.documentElement.matches('.layout-small, .layout-phone')
            ? -1
            : rcmail.env.contextmenu_mouseover_timeout;
        },
      },
    });

    rcmail.addEventListener('dialog-open', function () {
      rcmail.contextmenu.hide_all(window.event, false, true);
    });

    const task = rcmail.env.task;
    const action = rcmail.env.action;
    // message toolbar buttons (main actions of split buttons too) + the "more" and "mark" menus
    const mailSources = [
      '#mailtoolbar > a',
      '#mailtoolbar > .dropbutton > a:not(.dropdown)',
      '#markmessage-menu > ul > li',
      '#message-menu > ul > li',
    ];
    if (task === 'mail' && action === '') {
      rcmail.addEventListener('insertrow', function (props) {
        rcmail.contextmenu.init_list(props.row.id, {
          menu_name: 'messagelist',
          menu_source: mailSources,
        });
      });
      rcmail.add_onload(
        "rcmail.contextmenu.init_folder('#mailboxlist li', {'menu_source': ['#rcmfolder-menu > ul', '#mailboxoptions-menu > ul > li']})"
      );
    } else if (task === 'addressbook' && action === '') {
      rcmail.addEventListener('insertrow', function (props) {
        rcmail.contextmenu.init_list(props.row.id, {
          menu_name: 'contactlist',
          menu_source: [
            '#addressbooktoolbar > a',
            '#addressbooktoolbar > .dropbutton > a:not(.dropdown)',
            '#contact-menu > ul > li',
          ],
          list_object: 'contact_list',
        });
      });
      rcmail.add_onload(
        "rcmail.contextmenu.init_addressbook('#directorylist li, #savedsearchlist li', {'menu_source': '#groupoptions-menu > ul > li'})"
      );
      rcmail.addEventListener('group_insert', function (props) {
        rcmail.contextmenu.init_addressbook(props.li, {
          menu_source: '#groupoptions-menu > ul > li',
        });
      });
    } else if (task === 'settings') {
      rcmail.contextmenu.settings_menus([
        {
          obj: 'settings-menu li',
          props: {
            menu_name: 'settingslist',
            menu_source: '#rcmsettings-menu > ul',
            init_func: 'init_settings',
          },
        },
        {
          obj: 'sections-table tr',
          props: {
            menu_name: 'preferenceslist',
            menu_source: '#rcmsettings-menu > ul',
            list_object: 'sections_list',
          },
        },
        {
          obj: 'subscription-table li',
          props: {
            menu_name: 'folderlist',
            menu_source: [
              '#rcmfolder-menu > ul',
              '#rcmsettings-menu > ul',
              '#layout-content .toolbar > a',
            ],
            list_object: 'subscription_list',
            init_func: 'init_settings',
          },
        },
        {
          obj: 'identities-table tr',
          props: {
            menu_name: 'identiteslist',
            menu_source: ['#rcmsettings-menu > ul', '#layout-content .toolbar > a'],
            list_object: 'identity_list',
          },
        },
        {
          obj: 'responses-table tr',
          props: {
            menu_name: 'responseslist',
            menu_source: ['#rcmsettings-menu > ul', '#layout-content .toolbar > a'],
            list_object: 'responses_list',
          },
        },
        {
          obj: 'filtersetslist tr',
          props: {
            menu_name: 'managesievesetlist',
            menu_source: ['#rcmsettings-menu > ul', '#filterset-menu > ul > li'],
            list_object: 'filtersets_list',
          },
        },
        {
          obj: 'filterslist tr',
          props: {
            menu_name: 'managesieverulelist',
            menu_source: ['#rcmsettings-menu > ul', '#layout-content .toolbar > a'],
            list_object: 'filters_list',
          },
        },
      ]);
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
