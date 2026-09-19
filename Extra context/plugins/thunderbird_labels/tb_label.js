/**
 * Thunderbird Labels Plugin for Roundcube Webmail - Gmail-Style Labels UI & Engine
 *
 * Version: 1.5.0
 * Author: Michael Kefeder & Roundcube AI Team
 */

var escape_jquery_selector,
  i18n_label,
  rcm_tb_label_css,
  rcm_tb_label_find_main_window,
  rcm_tb_label_flag_msgs,
  rcm_tb_label_flag_toggle,
  rcm_tb_label_get_selection,
  rcm_tb_label_global,
  rcm_tb_label_global_set,
  rcm_tb_label_insert,
  rcm_tb_label_menuclick,
  rcm_tb_label_submenu,
  rcm_tb_label_toggle,
  rcm_tb_label_unflag_msgs,
  rcmail_ctxm_label,
  rcmail_ctxm_label_set,
  rcm_tb_label_escape_html,
  rcm_tb_label_init_sidebar,
  rcm_tb_label_render_sidebar_items,
  rcm_tb_label_filter_click,
  rcm_tb_label_render_filter_bar,
  rcm_tb_label_clear_filter_ui,
  rcm_tb_label_clear_filter,
  rcm_tb_label_show_add_modal,
  rcm_tb_label_fetch_counts,
  rcm_tb_label_update_count,
  rcm_tb_label_update_popup_menu,
  TB_LABEL_TAG_SVG,
  slice = [].slice;

TB_LABEL_TAG_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.63 5.84C17.27 5.33 16.67 5 16 5L5 5.01C3.9 5.01 3 5.9 3 7v10c0 1.1.9 1.99 2 1.99L16 19c.67 0 1.27-.33 1.63-.84L22 12l-4.37-6.16z"/></svg>';

rcm_tb_label_escape_html = function (str) {
  return String(str || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
};

// String formatting prototype helper
String.prototype.format = function () {
  var args = 1 <= arguments.length ? slice.call(arguments, 0) : [];
  return this.replace(/{(\d+)}/g, function (match, number) {
    return number < args.length ? args[number] : match;
  });
};

rcm_tb_label_css = (function () {
  function rcm_tb_label_css() {
    this.palette = [
      "#d93025", "#e37400", "#f29900", "#188038", "#129eaf",
      "#039be5", "#1a73e8", "#8430ce", "#d01884", "#5f6368",
      "#0d652d", "#795548", "#1a237e", "#455a64"
    ];
    this.label_colors = {};
    this.init_colors();
  }

  rcm_tb_label_css.prototype.hexToRgb = function (hex) {
    if (!hex || typeof hex !== "string") {
      return { r: 100, g: 100, b: 100 };
    }
    hex = hex.replace(/^#/, "");
    if (hex.length === 3) {
      hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    var num = parseInt(hex, 16);
    if (isNaN(num)) {
      return { r: 100, g: 100, b: 100 };
    }
    return {
      r: (num >> 16) & 255,
      g: (num >> 8) & 255,
      b: num & 255,
    };
  };

  rcm_tb_label_css.prototype.init_colors = function () {
    var self = this;
    var env_colors = (window.rcmail && rcmail.env && rcmail.env.tb_label_colors) || {};
    var labels = (window.rcmail && rcmail.env && rcmail.env.tb_label_custom_labels) || {};
    var idx = 0;

    $.each(labels, function (key, name) {
      if (key === "LABEL0") return;
      var raw_color = env_colors[key] || self.palette[idx % self.palette.length];
      var rgb = self.hexToRgb(raw_color);
      self.label_colors[key] = {
        color: raw_color,
        bg: "rgba(" + rgb.r + "," + rgb.g + "," + rgb.b + ",0.14)",
        border: "rgba(" + rgb.r + "," + rgb.g + "," + rgb.b + ",0.35)",
        fg: raw_color,
        box: raw_color,
      };
      idx++;
    });
  };

  rcm_tb_label_css.prototype.generate = function () {
    var css = "";
    $.each(this.label_colors, function (label_name, c) {
      var escaped = "tb_label_" + label_name;
      var escaped_lower = label_name.toLowerCase();

      // Gmail badge styles in message list
      css += ".tb_label_badges." + escaped_lower + ", .tb_label_badges[data-label=\"" + label_name + "\"] {\n" +
             "  background-color: " + c.bg + " !important;\n" +
             "  color: " + c.fg + " !important;\n" +
             "  border: 1px solid " + c.border + " !important;\n" +
             "}\n";

      // Bullet dots
      css += "span.tb_label_dots span." + escaped + " {\n" +
             "  color: " + c.fg + " !important;\n" +
             "}\n";

      // Sidebar tag icon
      css += ".tb-label-icon." + escaped_lower + " {\n" +
             "  color: " + c.color + " !important;\n" +
             "}\n";

      // Unselected row text color
      css += "#messagelist tr." + escaped + " td,\n" +
             "#messagelist tr." + escaped + " td a,\n" +
             ".toolbarmenu li." + escaped_lower + " a {\n" +
             "  color: " + c.fg + " !important;\n" +
             "}\n";

      // Selected row highlight
      css += "#messagelist tr.selected." + escaped + " td,\n" +
             "#messagelist tr.selected." + escaped + " td a {\n" +
             "  color: #FFFFFF !important;\n" +
             "  background-color: " + c.color + " !important;\n" +
             "}\n";

      // Detail preview label box
      css += "div#labelbox span.box_" + escaped + " {\n" +
             "  background-color: " + c.bg + " !important;\n" +
             "  color: " + c.fg + " !important;\n" +
             "  border: 1px solid " + c.border + " !important;\n" +
             "}\n";
    });
    return css;
  };

  rcm_tb_label_css.prototype.inject = function () {
    $("#tb-label-dynamic-styles").remove();
    return $("<style id=\"tb-label-dynamic-styles\">")
      .prop("type", "text/css")
      .html(this.generate())
      .appendTo("head");
  };

  return rcm_tb_label_css;
})();

i18n_label = function (label_name) {
  if (!window.rcmail || !rcmail.env || !rcmail.env.tb_label_custom_labels) {
    return label_name;
  }
  var custom_str = rcmail.env.tb_label_custom_labels[label_name];
  return custom_str ? custom_str : label_name;
};

// Shows the colors based on flag info like in Thunderbird / Gmail
rcm_tb_label_insert = function (uid, row) {
  var i, label_name, len, message, ref, rowobj, spanobj;
  if (
    typeof rcmail === "undefined" ||
    typeof rcmail.env === "undefined" ||
    typeof rcmail.env.messages === "undefined"
  ) {
    return;
  }
  message = rcmail.env.messages[uid];
  if (!message || !row || !row.obj) {
    return;
  }
  rowobj = $(row.obj);

  // If label filter is currently active in sidebar, ensure row matches filter
  if (rcmail.env.tb_label_active_filter) {
    var active_filter = rcmail.env.tb_label_active_filter;
    if (message.flags && message.flags.tb_labels && jQuery.inArray(active_filter, message.flags.tb_labels) > -1) {
      rowobj.show();
    } else {
      rowobj.hide();
    }
  }

  // Prepend badge container before the subject text inside td.subject
  var subject_container = rowobj.find("td.subject span.subject");
  rowobj.find("td.subject span.tb_label_dots").remove();

  var dots_container = $(
    '<span class="tb_label_dots ' + (rcmail.env.tb_label_style || "badges") + '"></span>'
  );
  if (subject_container.length) {
    subject_container.prepend(dots_container);
  } else {
    rowobj.find("td.subject").prepend(dots_container);
  }

  if (message.flags && message.flags.tb_labels && message.flags.tb_labels.length) {
    spanobj = dots_container;
    message.flags.tb_labels.sort(function (a, b) {
      return a.localeCompare(b, undefined, { numeric: true });
    });
    ref = message.flags.tb_labels;
    var style = rcmail.env.tb_label_style || "badges";

    if (style === "bullets") {
      for (i = 0, len = ref.length; i < len; i++) {
        label_name = ref[i];
        spanobj.append(
          '<span class="tb_label_' +
            label_name +
            '" title="' +
            rcm_tb_label_escape_html(i18n_label(label_name)) +
            '">&#8226;</span>'
        );
      }
    } else if (style === "badges") {
      for (i = 0, len = ref.length; i < len; i++) {
        label_name = ref[i];
        if (rcmail.env.tb_label_custom_labels && rcmail.env.tb_label_custom_labels[label_name]) {
          spanobj.append(
            '<span class="tb_label_badges badge ' +
              label_name.toLowerCase() +
              '" data-label="' + label_name + '" title="' +
              rcm_tb_label_escape_html(i18n_label(label_name)) +
              '">' +
              rcm_tb_label_escape_html(i18n_label(label_name)) +
              "</span>"
          );
        }
      }
    } else {
      // thunderbird UI style
      for (i = 0, len = ref.length; i < len; i++) {
        label_name = ref[i];
        rowobj.addClass("tb_label_" + label_name);
      }
    }
  }
};

// Frame coordination
rcm_tb_label_find_main_window = function () {
  var elastic_popup_window, login_form, ms, popup_window, preview_frame, w;
  ms = $("#mainscreen");
  login_form = $("#login-form");
  preview_frame = $("#messagecontframe");
  popup_window = $("body.extwin");
  elastic_popup_window = $("body.action-show");

  if (login_form.length) {
    return window;
  }
  w = window;
  if (ms.length && preview_frame.length) {
    w = window;
  }
  if (!ms.length && !preview_frame.length) {
    w = window.parent;
  }
  if (popup_window.length || elastic_popup_window.length) {
    w = window;
  }
  ms = w.document.getElementById("mainscreen");
  if (!ms) {
    ms = w.document.getElementById("messagelist-content");
    if (!ms) {
      if (elastic_popup_window.length) {
        return w;
      }
      return window;
    }
  }
  return w;
};

rcm_tb_label_global = function (var_name) {
  var w = rcm_tb_label_find_main_window();
  return w ? w[var_name] : null;
};

rcm_tb_label_global_set = function (var_name, value) {
  var w = rcm_tb_label_find_main_window();
  if (w) {
    w[var_name] = value;
  }
  return value;
};

escape_jquery_selector = function (str) {
  return str.replace(/([ #;&,.+*~':"!^$[\]()=>|/@])/g, "\\$1");
};

// ==========================================
// Gmail-Style Sidebar Labels Section
// ==========================================

rcm_tb_label_init_sidebar = function () {
  if (window.rcmail && rcmail.task !== "mail") {
    return;
  }
  if ($("#tb-labels-sidebar").length) {
    return;
  }

  var container = $("#folderlist-content");
  if (!container.length) {
    container = $("#mailboxlist-container");
  }
  if (!container.length && $("#mailboxlist").length) {
    container = $("#mailboxlist").parent();
  }
  if (!container.length) {
    return;
  }

  var labels_title = (rcmail.labels && rcmail.labels["thunderbird_labels.labels_title"]) || "Labels";
  var add_label_title = (rcmail.labels && rcmail.labels["thunderbird_labels.add_label"]) || "Create new label";

  var html = '<div id="tb-labels-sidebar" class="tb-labels-sidebar">' +
             '  <div class="tb-labels-header">' +
             '    <span class="tb-labels-title">' + rcm_tb_label_escape_html(labels_title) + '</span>' +
             '    <button type="button" class="tb-labels-add-btn" id="tb-labels-add-btn" title="' + rcm_tb_label_escape_html(add_label_title) + '" aria-label="' + rcm_tb_label_escape_html(add_label_title) + '">+</button>' +
             '  </div>' +
             '  <ul id="tb-labels-list" class="tb-labels-list" role="tree"></ul>' +
             '</div>';

  if ($("#mailboxlist").length) {
    $("#mailboxlist").after(html);
  } else {
    container.append(html);
  }

  rcm_tb_label_render_sidebar_items();

  // Add click listener to '+' button
  $("#tb-labels-add-btn").off("click").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    rcm_tb_label_show_add_modal();
  });

  // When clicking on mailboxlist folder, clear active label filter UI
  $("#mailboxlist").off("click.tb_label").on("click.tb_label", "li a", function () {
    rcm_tb_label_clear_filter_ui();
  });

  // Fetch server counts
  rcm_tb_label_fetch_counts();
};

rcm_tb_label_render_sidebar_items = function () {
  var list = $("#tb-labels-list");
  if (!list.length) return;
  list.empty();

  var labels = (window.rcmail && rcmail.env && rcmail.env.tb_label_custom_labels) || {};
  var colors = (window.rcmail && rcmail.env && rcmail.env.tb_label_colors) || {};
  var counts = (window.rcmail && rcmail.env && rcmail.env.tb_label_counts) || {};

  $.each(labels, function (key, name) {
    if (key === "LABEL0") return;
    var color = colors[key] || "#757575";
    var count = parseInt(counts[key] || 0, 10);
    var is_active = rcmail.env.tb_label_active_filter === key;

    var count_html = count > 0
      ? '<span class="tb-label-count">' + count + "</span>"
      : '<span class="tb-label-count" style="display:none;"></span>';

    var item = $(
      '<li class="tb-label-item ' + (is_active ? "selected active " : "") + key.toLowerCase() + '" data-label="' + key + '" role="treeitem">' +
        '<a href="#label-' + key + '" class="tb-label-link ' + (is_active ? "active" : "") + '" title="' + rcm_tb_label_escape_html(name) + '">' +
          '<span class="tb-label-icon ' + key.toLowerCase() + '" style="color: ' + color + ';">' + TB_LABEL_TAG_SVG + '</span>' +
          '<span class="name tb-label-name">' + rcm_tb_label_escape_html(name) + '</span>' +
          count_html +
        "</a>" +
      "</li>"
    );

    item.find("a.tb-label-link").on("click", function (e) {
      e.preventDefault();
      rcm_tb_label_filter_click(key);
    });

    list.append(item);
  });
};

// ==========================================
// Filtering by Label
// ==========================================

rcm_tb_label_filter_click = function (labelKey) {
  if (rcmail.env.tb_label_active_filter === labelKey) {
    rcm_tb_label_clear_filter();
    return;
  }

  rcmail.env.tb_label_active_filter = labelKey;

  // Update active state in sidebar
  $("#tb-labels-list li").removeClass("selected active");
  $("#tb-labels-list li a").removeClass("active");
  var active_li = $("#tb-labels-list li[data-label=\"" + labelKey + "\"]");
  active_li.addClass("selected active");
  active_li.find("a.tb-label-link").addClass("active");

  // Remove active highlight from folders
  $("#mailboxlist li").removeClass("selected active");
  $("#mailboxlist li a").removeClass("active");

  // Show active filter bar above message list
  rcm_tb_label_render_filter_bar(labelKey);

  var filter_val = "KEYWORD $" + labelKey;
  var target_mbox = "INBOX";

  // If currently viewing another folder (e.g. Trash, Sent, Junk), switch to INBOX where labeled mail lives
  if (rcmail.env.mailbox && rcmail.env.mailbox !== target_mbox) {
    if (rcmail.list_mailbox) {
      rcmail.list_mailbox(target_mbox, 1, { filter: filter_val });
      return;
    }
  }

  // Instant client-side filtering on loaded rows if already in INBOX
  var match_count = 0;
  $("#messagelist tbody tr").each(function () {
    var uid = this.id.replace(/^rcmrow/, "");
    var msg = rcmail.env.messages && rcmail.env.messages[uid];
    if (msg && msg.flags && msg.flags.tb_labels && jQuery.inArray(labelKey, msg.flags.tb_labels) > -1) {
      $(this).show();
      match_count++;
    } else {
      $(this).hide();
    }
  });

  // Server-side filter
  if (rcmail.filter_mailbox) {
    rcmail.filter_mailbox(filter_val);
  } else if (rcmail.list_mailbox) {
    rcmail.list_mailbox(rcmail.env.mailbox || target_mbox, 1, { filter: filter_val });
  }
};

rcm_tb_label_render_filter_bar = function (labelKey) {
  $("#tb-label-filter-bar").remove();

  var labels = (window.rcmail && rcmail.env && rcmail.env.tb_label_custom_labels) || {};
  var colors = (window.rcmail && rcmail.env && rcmail.env.tb_label_colors) || {};
  var name = labels[labelKey] || labelKey;
  var color = colors[labelKey] || "#757575";
  var filter_text = (rcmail.labels && rcmail.labels["thunderbird_labels.filter_by_label"]) || "Filtered by label";
  var clear_text = (rcmail.labels && rcmail.labels["thunderbird_labels.clear_filter"]) || "Clear filter";

  var bar = $(
    '<div id="tb-label-filter-bar" class="tb-label-filter-bar">' +
      '<div class="tb-filter-chip" style="background-color: ' + color + '1f; border-color: ' + color + '55; color: ' + color + ';">' +
        TB_LABEL_TAG_SVG +
        '<span class="tb-filter-chip-name">' + rcm_tb_label_escape_html(name) + "</span>" +
        '<button type="button" class="tb-filter-clear-btn" title="' + rcm_tb_label_escape_html(clear_text) + '" aria-label="' + rcm_tb_label_escape_html(clear_text) + '">&times;</button>' +
      "</div>" +
      '<span class="tb-filter-text">' + rcm_tb_label_escape_html(filter_text) + "</span>" +
    "</div>"
  );

  bar.find(".tb-filter-clear-btn").on("click", function (e) {
    e.preventDefault();
    rcm_tb_label_clear_filter();
  });

  var target = $("#messagelist-content");
  if (!target.length) target = $("#messagelist").parent();
  if (target.length) {
    target.prepend(bar);
  }
};

rcm_tb_label_clear_filter_ui = function () {
  rcmail.env.tb_label_active_filter = null;
  $("#tb-labels-list li").removeClass("selected active");
  $("#tb-labels-list li a").removeClass("active");
  $("#tb-label-filter-bar").remove();
  $("#messagelist tbody tr").show();

  // Restore active mailbox folder highlighting
  if (rcmail.env.mailbox) {
    var folder_li = $("#mailboxlist li.mailbox." + rcmail.env.mailbox.toLowerCase());
    if (!folder_li.length) {
      folder_li = $("#mailboxlist a[rel='" + rcmail.env.mailbox + "']").closest("li");
    }
    if (folder_li.length) {
      folder_li.addClass("selected");
      folder_li.find("> a").addClass("active");
    }
  }
};

rcm_tb_label_clear_filter = function () {
  rcm_tb_label_clear_filter_ui();
  if (rcmail.filter_mailbox) {
    rcmail.filter_mailbox("ALL");
  } else if (rcmail.list_mailbox) {
    rcmail.list_mailbox(rcmail.env.mailbox || "INBOX", 1);
  }
};

// ==========================================
// Label Creation Modal ("+" Button)
// ==========================================

rcm_tb_label_show_add_modal = function () {
  $("#tb-label-modal").remove();

  var modal_title = (rcmail.labels && rcmail.labels["thunderbird_labels.add_label"]) || "Create new label";
  var label_name_text = (rcmail.labels && rcmail.labels["thunderbird_labels.label_name"]) || "Label name";
  var placeholder_text = (rcmail.labels && rcmail.labels["thunderbird_labels.label_name_placeholder"]) || "Please enter a new label name...";
  var color_text = (rcmail.labels && rcmail.labels["thunderbird_labels.label_color"]) || "Label color";
  var create_text = (rcmail.labels && rcmail.labels["thunderbird_labels.create_label"]) || "Create";
  var cancel_text = (rcmail.labels && rcmail.labels["thunderbird_labels.cancel"]) || "Cancel";

  var swatches = [
    "#d93025", "#e37400", "#f29900", "#188038", "#129eaf",
    "#039be5", "#1a73e8", "#8430ce", "#d01884", "#5f6368",
    "#0d652d", "#795548", "#1a237e", "#455a64"
  ];

  var selected_color = swatches[0];

  var swatches_html = "";
  $.each(swatches, function (idx, hex) {
    swatches_html += '<button type="button" class="tb-color-swatch' + (idx === 0 ? " active" : "") + '" data-color="' + hex + '" style="background-color: ' + hex + ';" aria-label="' + hex + '"></button>';
  });

  var modal = $(
    '<div id="tb-label-modal" class="tb-label-modal" role="dialog" aria-modal="true">' +
      '<div class="tb-label-modal-backdrop"></div>' +
      '<div class="tb-label-modal-dialog">' +
        '<div class="tb-label-modal-header">' +
          "<h4>" + rcm_tb_label_escape_html(modal_title) + "</h4>" +
          '<button type="button" class="tb-modal-close" aria-label="Close">&times;</button>' +
        "</div>" +
        '<div class="tb-label-modal-body">' +
          '<div class="form-group">' +
            "<label for=\"tb-label-input-name\">" + rcm_tb_label_escape_html(label_name_text) + ":</label>" +
            '<input type="text" id="tb-label-input-name" class="form-control tb-input" autocomplete="off" placeholder="' + rcm_tb_label_escape_html(placeholder_text) + '" />' +
          "</div>" +
          '<div class="form-group tb-color-group">' +
            "<label>" + rcm_tb_label_escape_html(color_text) + ":</label>" +
            '<div class="tb-color-swatches-grid">' + swatches_html + "</div>" +
          "</div>" +
        "</div>" +
        '<div class="tb-label-modal-footer">' +
          '<button type="button" class="btn btn-secondary tb-modal-cancel">' + rcm_tb_label_escape_html(cancel_text) + "</button>" +
          '<button type="button" class="btn btn-primary tb-modal-submit">' + rcm_tb_label_escape_html(create_text) + "</button>" +
        "</div>" +
      "</div>" +
    "</div>"
  );

  modal.find(".tb-color-swatch").on("click", function () {
    modal.find(".tb-color-swatch").removeClass("active");
    $(this).addClass("active");
    selected_color = $(this).attr("data-color");
  });

  function close_modal() {
    modal.fadeOut(150, function () { modal.remove(); });
  }

  modal.find(".tb-modal-close, .tb-modal-cancel, .tb-label-modal-backdrop").on("click", function () {
    close_modal();
  });

  function submit_form() {
    var name = $.trim(modal.find("#tb-label-input-name").val());
    if (!name) {
      modal.find("#tb-label-input-name").focus();
      return;
    }

    var lock = rcmail.set_busy(true, "loading");
    rcmail.http_post(
      "plugin.thunderbird_labels.add_label",
      {
        name: name,
        color: selected_color,
      },
      lock
    );
    close_modal();
  }

  modal.find(".tb-modal-submit").on("click", submit_form);
  modal.find("#tb-label-input-name").on("keydown", function (e) {
    if (e.which === 13) {
      e.preventDefault();
      submit_form();
    } else if (e.which === 27) {
      close_modal();
    }
  });

  $("body").append(modal);
  modal.fadeIn(150);
  setTimeout(function () {
    modal.find("#tb-label-input-name").focus();
  }, 100);
};

// ==========================================
// Counts Management
// ==========================================

rcm_tb_label_fetch_counts = function () {
  if (!window.rcmail || rcmail.task !== "mail") return;
  var mbox = "INBOX";
  rcmail.http_request("plugin.thunderbird_labels.get_counts", "_mbox=" + urlencode(mbox));
};

rcm_tb_label_update_count = function (labelKey, delta) {
  if (!rcmail.env.tb_label_counts) {
    rcmail.env.tb_label_counts = {};
  }
  var current = parseInt(rcmail.env.tb_label_counts[labelKey] || 0, 10);
  var next = Math.max(0, current + delta);
  rcmail.env.tb_label_counts[labelKey] = next;

  var badge = $("#tb-labels-list li[data-label=\"" + labelKey + "\"] .tb-label-count");
  if (badge.length) {
    if (next > 0) {
      badge.text(next).show();
    } else {
      badge.text("").hide();
    }
  }
};

// Update popup menu in toolbar when labels change
rcm_tb_label_update_popup_menu = function () {
  var menu = $("#tb-label-menu ul");
  if (!menu.length) return;
  menu.empty();

  var labels = (rcmail.env && rcmail.env.tb_label_custom_labels) || {};
  var idx = 0;
  $.each(labels, function (key, name) {
    var num_match = key.match(/^LABEL([0-9]+)$/);
    var num = num_match ? num_match[1] : idx;
    var display = num > 0 ? num + " " + name : name;
    var li = $(
      '<li role="menuitem">' +
        '<a href="#" class="tb-label label' + num + ' active" data-labelname="' + key + '">' +
          rcm_tb_label_escape_html(display) +
        "</a>" +
      "</li>"
    );
    li.find("a").on("click", function (e) {
      e.preventDefault();
      rcm_tb_label_menuclick(key);
    });
    menu.append(li);
    idx++;
  });
};

// ==========================================
// Flag Toggling & State Handling
// ==========================================

rcm_tb_label_flag_toggle = function (flag_uids, toggle_label_no, onoff) {
  var headers_table, label_box, labels_for_message, pos, preview_frame;
  if (!flag_uids.length) {
    return;
  }
  preview_frame = $("#messagecontframe");
  labels_for_message = rcm_tb_label_global("tb_labels_for_message");

  if (preview_frame.length) {
    headers_table = preview_frame
      .contents()
      .find("table.headers-table,#message-header");
    label_box = preview_frame.contents().find("#labelbox");
  } else {
    headers_table = $("table.headers-table,#message-header");
    label_box = $("#labelbox");
  }
  if (!rcmail.message_list && !headers_table.length) {
    return;
  }

  // Single message / preview pane
  if (headers_table.length) {
    if (onoff === true) {
      label_box
        .find("span.box_tb_label_" + escape_jquery_selector(toggle_label_no))
        .remove();
      label_box.append(
        '<span class="box_tb_label_' +
          toggle_label_no +
          '">' +
          rcm_tb_label_escape_html(i18n_label(toggle_label_no)) +
          "</span>"
      );
      if (labels_for_message) {
        labels_for_message.push(toggle_label_no);
      }
    } else {
      label_box
        .find("span.box_tb_label_" + escape_jquery_selector(toggle_label_no))
        .remove();
      headers_table.removeClass("tb_label_" + toggle_label_no);
      if (labels_for_message) {
        pos = jQuery.inArray(toggle_label_no, labels_for_message);
        if (pos > -1) {
          labels_for_message.splice(pos, 1);
        }
      }
    }
    if (labels_for_message) {
      labels_for_message = jQuery.grep(labels_for_message, function (v, k) {
        return jQuery.inArray(v, labels_for_message) === k;
      });
      rcm_tb_label_global_set("tb_labels_for_message", labels_for_message);
    }
  }

  if (!rcmail.env.messages) {
    return;
  }

  // Message list update
  jQuery.each(flag_uids, function (idx, uid) {
    var message = rcmail.env.messages[uid];
    var row = rcmail.message_list ? rcmail.message_list.rows[uid] : null;
    if (!message) return;
    if (!message.flags) message.flags = {};
    if (!message.flags.tb_labels) message.flags.tb_labels = [];

    if (onoff === true) {
      if (jQuery.inArray(toggle_label_no, message.flags.tb_labels) > -1) {
        return;
      }
      if (row && row.obj) {
        var rowobj = $(row.obj);
        var spanobj = rowobj.find("td.subject span.tb_label_dots");
        var style = rcmail.env.tb_label_style || "badges";

        if (style === "bullets") {
          spanobj.append(
            '<span class="tb_label_' +
              toggle_label_no +
              '" title="' +
              rcm_tb_label_escape_html(i18n_label(toggle_label_no)) +
              '">&#8226;</span>'
          );
        } else if (style === "badges") {
          spanobj.append(
            '<span class="tb_label_badges badge ' +
              toggle_label_no.toLowerCase() +
              '" data-label="' + toggle_label_no + '" title="' +
              rcm_tb_label_escape_html(i18n_label(toggle_label_no)) +
              '">' +
              rcm_tb_label_escape_html(i18n_label(toggle_label_no)) +
              "</span>"
          );
        } else {
          rowobj.addClass("tb_label_" + toggle_label_no);
        }
      }
      message.flags.tb_labels.push(toggle_label_no);
      rcm_tb_label_update_count(toggle_label_no, 1);
    } else {
      if (row && row.obj) {
        var rowobj = $(row.obj);
        var style = rcmail.env.tb_label_style || "badges";
        if (style === "bullets") {
          rowobj
            .find("td.subject span.tb_label_dots span.tb_label_" + toggle_label_no)
            .remove();
        } else if (style === "badges") {
          rowobj
            .find(
              "td.subject span.tb_label_dots span.tb_label_badges." +
                toggle_label_no.toLowerCase()
            )
            .remove();
        } else {
          rowobj.removeClass("tb_label_" + toggle_label_no);
        }
      }
      pos = jQuery.inArray(toggle_label_no, message.flags.tb_labels);
      if (pos > -1) {
        message.flags.tb_labels.splice(pos, 1);
        rcm_tb_label_update_count(toggle_label_no, -1);
      }
    }
  });
};

rcm_tb_label_flag_msgs = function (flag_uids, toggle_label_no) {
  rcm_tb_label_flag_toggle(flag_uids, toggle_label_no, true);
};

rcm_tb_label_unflag_msgs = function (unflag_uids, toggle_label_no) {
  rcm_tb_label_flag_toggle(unflag_uids, toggle_label_no, false);
};

rcm_tb_label_get_selection = function () {
  var selection;
  selection = rcmail.message_list ? rcmail.message_list.get_selection() : [];
  if (selection.length === 0 && rcmail.env.uid) {
    selection = [rcmail.env.uid];
  }
  return selection;
};

rcm_tb_label_menuclick = function (labelname, obj, ev) {
  return rcm_tb_label_toggle(labelname);
};

rcm_tb_label_toggle = function (toggle_label) {
  var selection, toggle_labels, unset_all;
  selection = rcm_tb_label_get_selection();
  if (!selection.length) {
    return;
  }
  // LABEL0 means remove all labels
  if (toggle_label === "LABEL0") {
    toggle_labels = Object.keys((rcmail.env && rcmail.env.tb_label_custom_labels) || {}).filter(function (k) {
      return k !== "LABEL0";
    });
    if (!toggle_labels.length) {
      toggle_labels = ["LABEL1", "LABEL2", "LABEL3", "LABEL4", "LABEL5"];
    }
    unset_all = true;
  } else {
    toggle_labels = [toggle_label];
    unset_all = false;
  }

  toggle_labels.forEach(function (v) {
    var first_message,
      first_toggle_mode,
      flag_uids,
      lock,
      str_flag_uids,
      str_unflag_uids,
      toggle_label_no,
      unflag_uids;

    toggle_label_no = v;
    first_toggle_mode = "on";

    if (rcmail.env.messages) {
      first_message = rcmail.env.messages[selection[0]];
      if (
        first_message &&
        first_message.flags &&
        jQuery.inArray(toggle_label_no, first_message.flags.tb_labels) >= 0
      ) {
        first_toggle_mode = "off";
      } else {
        first_toggle_mode = "on";
      }
    } else {
      if (
        jQuery.inArray(
          toggle_label_no,
          rcm_tb_label_global("tb_labels_for_message") || []
        ) >= 0
      ) {
        first_toggle_mode = "off";
      }
    }

    flag_uids = [];
    unflag_uids = [];

    jQuery.each(selection, function (idx, uid) {
      if (!rcmail.env.messages) {
        if (first_toggle_mode === "on") {
          flag_uids.push(uid);
        } else {
          unflag_uids.push(uid);
        }
        if (unset_all && unflag_uids.length === 0) {
          unflag_uids.push(uid);
        }
        return;
      }
      var message = rcmail.env.messages[uid];
      if (
        message &&
        message.flags &&
        jQuery.inArray(toggle_label_no, message.flags.tb_labels) >= 0
      ) {
        if (first_toggle_mode === "off") {
          unflag_uids.push(uid);
        }
      } else {
        if (first_toggle_mode === "on") {
          flag_uids.push(uid);
        }
      }
    });

    if (unset_all) {
      flag_uids = [];
    }

    if (flag_uids.length === 0 && unflag_uids.length === 0) {
      return;
    }

    str_flag_uids = flag_uids.join(",");
    str_unflag_uids = unflag_uids.join(",");
    lock = rcmail.set_busy(true, "loading");

    rcmail.http_request(
      "plugin.thunderbird_labels.set_flags",
      "_flag_uids=" +
        str_flag_uids +
        "&_unflag_uids=" +
        str_unflag_uids +
        "&_mbox=" +
        urlencode(rcmail.env.mailbox) +
        "&_toggle_label=" +
        toggle_label_no,
      lock
    );

    rcm_tb_label_flag_msgs(flag_uids, toggle_label_no);
    rcm_tb_label_unflag_msgs(unflag_uids, toggle_label_no);
  });
};

rcmail_ctxm_label = function (command, el, pos) {
  var cur_a, selection;
  selection = rcmail.message_list ? rcmail.message_list.get_selection() : [];
  if (!selection.length && !rcmail.env.uid) {
    return;
  }
  if (!selection.length && rcmail.env.uid) {
    rcmail.message_list.select_row(rcmail.env.uid);
  }
  cur_a = $("#tb-label-menu a.label" + rcmail.tb_label_no);
  if (cur_a && cur_a.length) {
    cur_a.click();
  }
};

rcmail_ctxm_label_set = function (which) {
  rcmail.tb_label_no = which;
};

rcm_tb_label_submenu = function (p, obj, ev) {
  if (typeof rcmail_ui === "undefined") {
    window.rcmail_ui = window.UI;
  }
  if (!rcmail_ui || !rcmail_ui.show_popup) {
    return;
  }
  if (!rcmail_ui.check_tb_popup()) {
    rcmail_ui.tb_label_popup_add();
  }
  if (typeof rcmail_ui.show_popupmenu === "undefined") {
    return;
  } else {
    rcmail_ui.show_popupmenu("tb-label-menu", ev);
  }
  return false;
};

// ==========================================
// Initialization & Document Lifecycle
// ==========================================

$(function () {
  var css = new rcm_tb_label_css();
  css.inject();

  if (rcm_tb_label_global("tb_labels_for_message") == null) {
    rcm_tb_label_global_set("tb_labels_for_message", []);
  }

  // Keyboard shortcuts (1-5)
  if (rcmail.env.tb_label_enable_shortcuts) {
    $(document).keyup(function (e) {
      if (e.isComposing || e.keyCode === 229) return;
      if (e.shiftKey || e.altKey || e.ctrlKey || e.metaKey) return;
      if (e.target.nodeName === "INPUT" || e.target.nodeName === "TEXTAREA") return;

      var k = e.which;
      if ((k > 47 && k < 58) || (k > 95 && k < 106)) {
        var label_no = k % 48;
        var cur_a = $("#tb-label-menu a.label" + label_no);
        if (cur_a && cur_a.length) {
          cur_a.click();
        }
      }
    });
  }

  // Contextmenu integration
  if (window.rcm_contextmenu_register_command) {
    rcm_contextmenu_register_command(
      "ctxm_tb_label",
      rcmail_ctxm_label,
      $("#tb_label_ctxm_mainmenu"),
      "moreacts",
      "after",
      true
    );
  }

  // Single message view
  var labels_for_message = tb_labels_for_message;
  if (labels_for_message && labels_for_message.length) {
    var labelbox_parent = $("div.message-headers, #message-header");
    if (!labelbox_parent.length) {
      labelbox_parent = $("table.headers-table");
    }
    labelbox_parent.append(
      '<div id="labelbox" class="' + (rcmail.env.tb_label_style || "badges") + '"></div>'
    );
    labels_for_message.sort(function (a, b) {
      return a.localeCompare(b, undefined, { numeric: true });
    });
    jQuery.each(labels_for_message, function (idx, val) {
      rcm_tb_label_flag_msgs([-1], val);
    });
    rcm_tb_label_global_set("tb_labels_for_message", labels_for_message);
  }

  // Event listener: row inserted into message list
  rcmail.addEventListener("insertrow", function (event) {
    rcm_tb_label_insert(event.uid, event.row);
  });

  // Event listener: plugin init
  rcmail.addEventListener("init", function (evt) {
    rcmail.register_command(
      "plugin.thunderbird_labels.rcm_tb_label_submenu",
      rcm_tb_label_submenu,
      rcmail.env.uid
    );
    rcmail.register_command(
      "plugin.thunderbird_labels.rcm_tb_label_menuclick",
      rcm_tb_label_menuclick,
      rcmail.env.uid
    );

    if (rcmail.message_list) {
      rcmail.message_list.addEventListener("select", function (list) {
        rcmail.enable_command(
          "plugin.thunderbird_labels.rcm_tb_label_submenu",
          list.get_selection().length > 0
        );
        rcmail.enable_command(
          "plugin.thunderbird_labels.rcm_tb_label_menuclick",
          list.get_selection().length > 0
        );
      });
    }

    // Initialize sidebar
    rcm_tb_label_init_sidebar();
  });

  // Event listener: folder loaded / list refreshed
  rcmail.addEventListener("responseafterlist", function (p) {
    if (rcmail.env.tb_label_active_filter) {
      $("#mailboxlist li").removeClass("selected active");
      $("#mailboxlist li a").removeClass("active");
      $("#tb-labels-list li[data-label=\"" + rcmail.env.tb_label_active_filter + "\"]").addClass("selected active");
    } else {
      rcm_tb_label_clear_filter_ui();
    }
    rcm_tb_label_fetch_counts();
  });

  // Commands received from PHP backend
  rcmail.addEventListener("plugin.thunderbird_labels.update_counts", function (data) {
    if (data && data.counts) {
      rcmail.env.tb_label_counts = data.counts;
      var allLabels = rcmail.env.tb_label_custom_labels || {};
      $.each(allLabels, function (key) {
        if (key === "LABEL0") return;
        var count = parseInt((data.counts && data.counts[key]) || 0, 10);
        var badge = $("#tb-labels-list li[data-label=\"" + key + "\"] .tb-label-count");
        if (badge.length) {
          if (count > 0) {
            badge.text(count).show();
          } else {
            badge.text("").hide();
          }
        }
      });
    }
  });

  rcmail.addEventListener("plugin.thunderbird_labels.label_added", function (data) {
    if (data && data.labels) {
      rcmail.env.tb_label_custom_labels = data.labels;
      rcmail.env.tb_label_colors = data.colors || {};
      new rcm_tb_label_css().inject();
      rcm_tb_label_render_sidebar_items();
      rcm_tb_label_update_popup_menu();
      rcm_tb_label_fetch_counts();
    }
  });

  rcmail.addEventListener("plugin.thunderbird_labels.label_updated", function (data) {
    if (data && data.labels) {
      rcmail.env.tb_label_custom_labels = data.labels;
      rcmail.env.tb_label_colors = data.colors || {};
      new rcm_tb_label_css().inject();
      rcm_tb_label_render_sidebar_items();
      rcm_tb_label_update_popup_menu();
    }
  });

  rcmail.addEventListener("plugin.thunderbird_labels.label_deleted", function (data) {
    if (data && data.labels) {
      rcmail.env.tb_label_custom_labels = data.labels;
      rcmail.env.tb_label_colors = data.colors || {};
      new rcm_tb_label_css().inject();
      rcm_tb_label_render_sidebar_items();
      rcm_tb_label_update_popup_menu();
    }
  });

  // Response before refresh
  rcmail.addEventListener("responsebeforerefresh", function (p) {
    var default_flags;
    if (p.response && p.response.env && p.response.env.recent_flags != null) {
      default_flags = [
        "SEEN", "UNSEEN", "ANSWERED", "FLAGGED",
        "DELETED", "DRAFT", "RECENT", "NONJUNK", "JUNK"
      ];
      $.each(p.response.env.recent_flags, function (uid, flags) {
        var message = rcmail.env.messages && rcmail.env.messages[uid];
        if (!message) return;
        var unset_labels;
        if (typeof message.flags.tb_labels === "object") {
          unset_labels = Array.from(message.flags.tb_labels);
        } else {
          unset_labels = Object.keys(rcmail.env.tb_label_custom_labels || {}).filter(function (k) {
            return k !== "LABEL0";
          });
        }
        $.each(flags, function (flagname, flagvalue) {
          flagname = flagname.toUpperCase();
          if (flagvalue && jQuery.inArray(flagname, default_flags) === -1) {
            rcm_tb_label_flag_msgs([uid], flagname);
            var pos = jQuery.inArray(flagname, unset_labels);
            if (pos > -1) {
              unset_labels.splice(pos, 1);
            }
          }
        });
        $.each(unset_labels, function (idx, label_name) {
          rcm_tb_label_unflag_msgs([uid], label_name);
        });
      });
    }
  });

  // Classic UI helpers
  if (window.rcube_mail_ui) {
    rcube_mail_ui.prototype.tb_label_popup_add = function () {
      var add = {
        "tb-label-menu": {
          id: "tb-label-menu",
        },
      };
      this.popups = $.extend(this.popups, add);
      var obj = $("#" + this.popups["tb-label-menu"].id);
      if (obj.length) {
        this.popups["tb-label-menu"].obj = obj;
      } else {
        delete this.popups["tb-label-menu"];
      }
    };

    rcube_mail_ui.prototype.check_tb_popup = function () {
      if (typeof this.popups === "undefined") {
        return true;
      }
      return !!this.popups["tb-label-menu"];
    };
  }

  // Ensure sidebar is initialized
  setTimeout(rcm_tb_label_init_sidebar, 150);
});
