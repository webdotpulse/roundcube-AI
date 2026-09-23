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
  rcm_tb_label_show_filter_modal,
  rcm_tb_label_render_filter_rules_table,
  rcm_tb_label_init_filters,
  rcm_tb_label_fetch_counts,
  rcm_tb_label_update_count,
  rcm_tb_label_count_local_messages,
  rcm_tb_label_update_popup_menu,
  TB_LABEL_TAG_SVG,
  TB_FILTER_ICON_SVG,
  slice = [].slice;

TB_LABEL_TAG_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M17.63 5.84C17.27 5.33 16.67 5 16 5L5 5.01C3.9 5.01 3 5.9 3 7v10c0 1.1.9 1.99 2 1.99L16 19c.67 0 1.27-.33 1.63-.84L22 12l-4.37-6.16z"/></svg>';
TB_FILTER_ICON_SVG = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>';

rcm_tb_label_count_local_messages = function (labelKey) {
  var count = 0;
  if (window.rcmail && rcmail.env && rcmail.env.messages) {
    $.each(rcmail.env.messages, function (uid, msg) {
      if (msg && msg.flags && msg.flags.tb_labels && jQuery.inArray(labelKey, msg.flags.tb_labels) > -1) {
        count++;
      }
    });
  }
  return count;
};

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
  if (!label_name) return "";
  var custom_str = (window.rcmail && rcmail.env && rcmail.env.tb_label_custom_labels)
    ? rcmail.env.tb_label_custom_labels[label_name] : null;
  if (custom_str && custom_str !== label_name && !/^LABEL[0-9]+$/i.test(custom_str)) {
    return custom_str;
  }
  var loc_key = "thunderbird_labels." + String(label_name).toLowerCase();
  if (window.rcmail && rcmail.labels && rcmail.labels[loc_key]) {
    return rcmail.labels[loc_key];
  }
  var default_names = {
    LABEL1: "Important",
    LABEL2: "Work",
    LABEL3: "Personal",
    LABEL4: "To do",
    LABEL5: "Later"
  };
  return default_names[String(label_name).toUpperCase()] || custom_str || label_name;
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
  var filter_rules_title = (rcmail.labels && rcmail.labels["thunderbird_labels.filter_rules"]) || "Filter Rules";

  var html = '<div id="tb-labels-sidebar" class="tb-labels-sidebar">' +
             '  <div class="tb-labels-header">' +
             '    <span class="tb-labels-title">' + rcm_tb_label_escape_html(labels_title) + '</span>' +
             '    <div class="tb-labels-header-actions">' +
             '      <button type="button" class="tb-labels-filter-btn" id="tb-labels-filter-btn" title="' + rcm_tb_label_escape_html(filter_rules_title) + '" aria-label="' + rcm_tb_label_escape_html(filter_rules_title) + '">' + TB_FILTER_ICON_SVG + '</button>' +
             '      <button type="button" class="tb-labels-add-btn" id="tb-labels-add-btn" title="' + rcm_tb_label_escape_html(add_label_title) + '" aria-label="' + rcm_tb_label_escape_html(add_label_title) + '">+</button>' +
             '    </div>' +
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

  // Add click listener to filter rules button
  $("#tb-labels-filter-btn").off("click").on("click", function (e) {
    e.preventDefault();
    e.stopPropagation();
    rcm_tb_label_show_filter_modal();
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
  var counts = (window.rcmail && rcmail.env && rcmail.env.tb_label_counts) || null;
  var del_title = (rcmail.labels && rcmail.labels["thunderbird_labels.delete_label"]) || "Delete label";

  $.each(labels, function (key, name) {
    if (key === "LABEL0") return;
    var color = colors[key] || "#757575";

    // Resolve human-friendly name instead of raw DB key (e.g. LABEL1 -> Important)
    var display_name = (name && name !== key && !/^LABEL[0-9]+$/i.test(name)) ? name : i18n_label(key);

    // Determine count: use server count if present; otherwise fallback to local loaded messages count
    var count = 0;
    if (counts && typeof counts[key] !== "undefined") {
      var parsed = parseInt(counts[key], 10);
      count = (!isNaN(parsed) && parsed > 0) ? parsed : 0;
    } else {
      count = rcm_tb_label_count_local_messages(key);
    }
    var is_active = rcmail.env.tb_label_active_filter === key;

    // Show "0" explicitly when there is nothing to show
    var display_count = (typeof count === "number" && !isNaN(count) && count > 0) ? count : 0;
    var count_html = '<span class="tb-label-count">' + display_count + '</span>';

    var item = $(
      '<li class="tb-label-item ' + (is_active ? "selected active " : "") + key.toLowerCase() + '" data-label="' + key + '" role="treeitem">' +
        '<a href="#label-' + key + '" class="tb-label-link ' + (is_active ? "active" : "") + '" title="' + rcm_tb_label_escape_html(display_name) + '">' +
          '<span class="tb-label-icon ' + key.toLowerCase() + '" style="color: ' + color + ';">' + TB_LABEL_TAG_SVG + '</span>' +
          '<span class="name tb-label-name">' + rcm_tb_label_escape_html(display_name) + '</span>' +
          count_html +
        "</a>" +
        '<button type="button" class="tb-label-delete-btn" title="' + rcm_tb_label_escape_html(del_title) + '" aria-label="' + rcm_tb_label_escape_html(del_title) + '">&times;</button>' +
      "</li>"
    );

    item.find("a.tb-label-link").on("click", function (e) {
      e.preventDefault();
      rcm_tb_label_filter_click(key);
    });

    item.find(".tb-label-delete-btn").on("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var confirm_msg = (rcmail.labels && rcmail.labels["thunderbird_labels.confirm_delete_label"]) || "Are you sure you want to delete this label?";
      var do_delete = function () {
        var lock = rcmail.set_busy(true, "loading");
        rcmail.http_post("plugin.thunderbird_labels.delete_label", { key: key }, lock);
      };
      if (rcmail.confirm) {
        rcmail.confirm(confirm_msg, function () {
          do_delete();
        });
      } else if (window.confirm(confirm_msg)) {
        do_delete();
      }
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
  if (window.rcmail && rcmail.env) {
    rcmail.env.tb_label_active_filter = null;
  }
  $("#tb-labels-list li").removeClass("selected active");
  $("#tb-labels-list li a").removeClass("active");
  $("#tb-label-filter-bar").remove();
  $("#messagelist tbody tr").show();

  // Restore active mailbox folder highlighting safely without CSS selector errors
  if (window.rcmail && rcmail.env && rcmail.env.mailbox) {
    var curMbox = rcmail.env.mailbox;
    var folder_li = null;

    if (typeof rcmail.get_folder_li === "function") {
      var el = rcmail.get_folder_li(curMbox, "", true) || rcmail.get_folder_li(curMbox, "", false);
      if (el) {
        folder_li = $(el);
      }
    }
    if (!folder_li || !folder_li.length) {
      folder_li = $("#mailboxlist a, .mailboxlist a, [role='navigation'] .treelist a").filter(function () {
        return $(this).attr("rel") === curMbox || $(this).data("mailbox") === curMbox;
      }).closest("li");
    }
    var mboxLower = (curMbox || "").toLowerCase();
    var standardFolders = ["inbox", "drafts", "sent", "trash", "junk", "archive"];
    if ((!folder_li || !folder_li.length) && standardFolders.indexOf(mboxLower) !== -1) {
      folder_li = $("#mailboxlist li.mailbox." + mboxLower);
    }
    if (folder_li && folder_li.length) {
      folder_li.addClass("selected");
      folder_li.find("> a").addClass("active");
    }
    if (rcmail.treelist && typeof rcmail.treelist.select === "function") {
      rcmail.treelist.select(curMbox);
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
// Resolve human-readable name for mail folders
function rcm_tb_label_folder_display_name(folderId) {
  if (!folderId || typeof folderId !== "string") return "";
  var lower = folderId.toLowerCase();
  if (lower === "inbox") {
    return (window.rcmail && rcmail.labels && rcmail.labels["inbox"]) || "Inbox";
  }
  if (lower === "drafts" || lower.endsWith(".drafts") || lower.endsWith("/drafts")) {
    return (window.rcmail && rcmail.labels && rcmail.labels["drafts"]) || "Drafts";
  }
  if (lower === "sent" || lower.endsWith(".sent") || lower.endsWith("/sent")) {
    return (window.rcmail && rcmail.labels && rcmail.labels["sent"]) || "Sent";
  }
  if (lower === "junk" || lower === "spam" || lower.endsWith(".junk") || lower.endsWith("/junk")) {
    return (window.rcmail && rcmail.labels && rcmail.labels["junk"]) || "Junk / Spam";
  }
  if (lower === "trash" || lower.endsWith(".trash") || lower.endsWith("/trash")) {
    return (window.rcmail && rcmail.labels && rcmail.labels["trash"]) || "Trash";
  }
  if (lower === "archive" || lower === "archives" || lower.endsWith(".archive") || lower.endsWith("/archive")) {
    return (window.rcmail && rcmail.labels && rcmail.labels["archive"]) || "Archive";
  }

  var clean = folderId.replace(/^INBOX[./]/i, "");
  clean = clean.replace(/[./]/g, " / ");
  return clean || folderId;
}

// Comprehensive folder resolution helper
rcm_tb_label_get_mail_folders = function () {
  var folders = [];
  var seen = {};

  function addFolder(id, name) {
    if (!id || typeof id !== "string") return;
    id = id.trim();
    if (!id || seen[id]) return;
    seen[id] = true;
    var disp = name && typeof name === "string" && name.trim() ? name.trim() : rcm_tb_label_folder_display_name(id);
    folders.push({ id: id, name: disp });
  }

  // 1. Check server-exported tb_label_mail_folders
  if (window.rcmail && rcmail.env && rcmail.env.tb_label_mail_folders) {
    var srv = rcmail.env.tb_label_mail_folders;
    if (Array.isArray(srv)) {
      srv.forEach(function (f) {
        if (typeof f === "string") addFolder(f, null);
        else if (f && typeof f === "object") addFolder(f.id || f.name, f.name || f.id);
      });
    } else if (typeof srv === "object") {
      $.each(srv, function (k, v) {
        if (typeof v === "string") addFolder(k, v);
        else if (v && typeof v === "object") addFolder(v.id || k, v.name || k);
        else addFolder(k, null);
      });
    }
  }

  // 2. Check Roundcube core environment mailboxes (standard in task === 'mail')
  if (window.rcmail && rcmail.env && rcmail.env.mailboxes) {
    var mboxes = rcmail.env.mailboxes;
    if (Array.isArray(mboxes)) {
      mboxes.forEach(function (mb) {
        if (typeof mb === "string") addFolder(mb, null);
        else if (mb && typeof mb === "object") addFolder(mb.id || mb.mailbox || mb.name, mb.name || mb.id);
      });
    } else if (typeof mboxes === "object") {
      $.each(mboxes, function (k, mb) {
        if (mb && typeof mb === "object") {
          addFolder(mb.id || k, mb.name || k);
        } else {
          addFolder(k, null);
        }
      });
    }
  }

  // 3. Check mailboxlist / unread_counts
  if (window.rcmail && rcmail.env) {
    if (Array.isArray(rcmail.env.mailboxlist)) {
      rcmail.env.mailboxlist.forEach(function (f) { addFolder(f, null); });
    }
    if (rcmail.env.unread_counts && typeof rcmail.env.unread_counts === "object") {
      $.each(rcmail.env.unread_counts, function (k) { addFolder(k, null); });
    }
  }

  // 4. Extract from DOM (#mailboxlist in Elastic, Larry, Classic, Gmail+)
  var $dom = $("#mailboxlist, .mailboxlist, [role='navigation'] .treelist");
  if ($dom.length) {
    $dom.find("li a, li[data-mailbox], li[data-id]").each(function () {
      var $el = $(this);
      var id = $el.attr("rel") || $el.attr("data-mailbox") || $el.attr("data-id") || $el.data("mailbox") || $el.data("id");
      if (!id && $el.is("a")) {
        var $p = $el.closest("li");
        id = $p.attr("rel") || $p.attr("data-mailbox") || $p.attr("data-id") || $p.data("mailbox") || $p.data("id");
      }
      if (id && typeof id === "string") {
        var name = $el.find(".name, .mailboxname, span:not(.unreadcount)").first().text() || $el.text();
        name = name.replace(/\(\d+\)$/, "").replace(/\d+$/, "").trim();
        addFolder(id, name);
      }
    });
  }

  // 5. Fallback standard mailboxes
  if (!folders.length) {
    var defaults = ["INBOX", "Drafts", "Sent", "Junk", "Trash", "Archive"];
    defaults.forEach(function (f) { addFolder(f, null); });
  }

  return folders;
};

// Helper to generate options HTML for folder select
rcm_tb_label_build_folder_options = function (selectedFolder) {
  var mailFolders = rcm_tb_label_get_mail_folders();
  var noneText = (window.rcmail && rcmail.labels && rcmail.labels["thunderbird_labels.action_move_none"]) || "-- Do not move --";
  var folderOpts = '<option value="">' + rcm_tb_label_escape_html(noneText) + '</option>';
  var foundSelected = false;

  mailFolders.forEach(function (f) {
    var isSel = (selectedFolder && (f.id === selectedFolder || f.id.toLowerCase() === selectedFolder.toLowerCase()));
    if (isSel) foundSelected = true;
    var selAttr = isSel ? ' selected="selected"' : "";
    folderOpts += '<option value="' + rcm_tb_label_escape_html(f.id) + '"' + selAttr + '>' + rcm_tb_label_escape_html(f.name) + '</option>';
  });

  if (selectedFolder && !foundSelected) {
    var disp = rcm_tb_label_folder_display_name(selectedFolder);
    folderOpts += '<option value="' + rcm_tb_label_escape_html(selectedFolder) + '" selected="selected">' + rcm_tb_label_escape_html(disp) + '</option>';
  }

  return folderOpts;
};

// ==========================================
// Incoming Mail Filter Rules Modal & Table
// ==========================================

rcm_tb_label_show_filter_modal = function (ruleToEdit) {
  $("#tb-filter-modal").remove();

  var isEdit = !!(ruleToEdit && ruleToEdit.id);
  var modal_title = isEdit
    ? ((rcmail.labels && rcmail.labels["thunderbird_labels.edit_filter_rule"]) || "Edit Filter Rule")
    : ((rcmail.labels && rcmail.labels["thunderbird_labels.add_filter_rule"]) || "New Filter Rule");

  var ruleName = ruleToEdit ? (ruleToEdit.name || "") : "";
  var ruleScope = ruleToEdit ? (ruleToEdit.scope || "all") : "all";
  var ruleEnabled = ruleToEdit ? (ruleToEdit.enabled !== false) : true;
  var ruleConditions = (ruleToEdit && Array.isArray(ruleToEdit.conditions) && ruleToEdit.conditions.length)
    ? ruleToEdit.conditions
    : [{ field: "subject", operator: "contains", value: "" }];

  var ruleActions = (ruleToEdit && ruleToEdit.actions) || {};
  var selectedLabels = Array.isArray(ruleActions.labels) ? ruleActions.labels : (ruleActions.label ? [ruleActions.label] : []);
  var targetFolder = ruleActions.folder || "";
  var markRead = !!ruleActions.mark_read;

  var customLabels = (rcmail.env && rcmail.env.tb_label_custom_labels) || {};
  var labelColors = (rcmail.env && rcmail.env.tb_label_colors) || {};

  // Build labels checklist HTML (supports MULTIPLE labels!)
  var labelsHtml = "";
  $.each(customLabels, function (k, val) {
    if (k === "LABEL0") return;
    var dName = (val && val !== k && !/^LABEL[0-9]+$/i.test(val)) ? val : i18n_label(k);
    var color = labelColors[k] || "#757575";
    var checked = selectedLabels.indexOf(k) > -1 ? ' checked="checked"' : "";
    labelsHtml += '<label class="tb-filter-label-chip" style="border-left: 3px solid ' + color + ';">' +
      '<input type="checkbox" name="tb_filter_labels[]" value="' + k + '"' + checked + '> ' +
      '<span class="tb-filter-chip-name">' + rcm_tb_label_escape_html(dName) + '</span>' +
      '</label>';
  });

  // Build folders dropdown options
  var folderOpts = rcm_tb_label_build_folder_options(targetFolder);

  // Request latest server folders asynchronously if not loaded yet
  if (window.rcmail && rcmail.http_post && (!rcmail.env.tb_label_mail_folders || !rcmail.env.tb_label_mail_folders.length)) {
    rcmail.http_post("plugin.thunderbird_labels.get_folders", {});
  }

  var cancel_text = (rcmail.labels && rcmail.labels["thunderbird_labels.cancel"]) || "Cancel";
  var save_text = (rcmail.labels && rcmail.labels["save"]) || "Save";

  var modal = $(
    '<div id="tb-filter-modal" class="tb-label-modal tb-filter-modal" role="dialog" aria-modal="true">' +
      '<div class="tb-label-modal-backdrop"></div>' +
      '<div class="tb-label-modal-dialog">' +
        '<div class="tb-label-modal-header">' +
          '<h4>' + rcm_tb_label_escape_html(modal_title) + '</h4>' +
          '<button type="button" class="tb-modal-close" aria-label="Close">&times;</button>' +
        '</div>' +
        '<div class="tb-label-modal-body">' +
          '<div class="form-group">' +
            '<label for="tb-filter-rule-name"><b>' + rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.rule_name"]) || "Rule Name") + ':</b></label>' +
            '<input type="text" id="tb-filter-rule-name" class="form-control tb-input" autocomplete="off" value="' + rcm_tb_label_escape_html(ruleName) + '" placeholder="e.g. Work Invoices, VIP Client..." />' +
          '</div>' +

          '<div class="form-group">' +
            '<label for="tb-filter-rule-scope"><b>' + rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.rule_scope"]) || "Conditions") + ':</b></label>' +
            '<select id="tb-filter-rule-scope" class="form-control tb-select" style="margin-bottom: 10px;">' +
              '<option value="all"' + (ruleScope === "all" ? ' selected="selected"' : "") + '>' + rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.rule_scope_all"]) || "Match ALL conditions (AND)") + '</option>' +
              '<option value="any"' + (ruleScope === "any" ? ' selected="selected"' : "") + '>' + rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.rule_scope_any"]) || "Match ANY condition (OR)") + '</option>' +
            '</select>' +
            '<div id="tb-filter-conditions-list"></div>' +
            '<button type="button" class="btn btn-sm btn-secondary" id="tb-filter-add-cond-btn" style="margin-top: 6px;">+ Add Condition</button>' +
          '</div>' +

          '<div class="form-group tb-filter-actions-group">' +
            '<label><b>Actions:</b></label>' +
            '<div style="margin-top: 6px;">' +
              '<div style="font-size: 12px; font-weight: 600; margin-bottom: 4px; color: #5f6368;">' + rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.action_assign_labels"]) || "Assign label(s)") + ':</div>' +
              '<div class="tb-filter-labels-checklist">' + labelsHtml + '</div>' +
            '</div>' +

            '<div style="margin-top: 12px;">' +
              '<label for="tb-filter-target-folder" style="font-size: 12px; font-weight: 600; color: #5f6368;">' + rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.action_move_folder"]) || "Move to folder") + ':</label>' +
              '<select id="tb-filter-target-folder" class="form-control tb-select">' + folderOpts + '</select>' +
            '</div>' +

            '<div style="margin-top: 10px;">' +
              '<label class="tb-filter-checkbox-label">' +
                '<input type="checkbox" id="tb-filter-mark-read"' + (markRead ? ' checked="checked"' : "") + '> ' +
                rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.action_mark_read"]) || "Mark as read") +
              '</label>' +
            '</div>' +

            '<div style="margin-top: 6px;">' +
              '<label class="tb-filter-checkbox-label">' +
                '<input type="checkbox" id="tb-filter-enabled"' + (ruleEnabled ? ' checked="checked"' : "") + '> Enable this rule' +
              '</label>' +
            '</div>' +
          '</div>' +
        '</div>' +
        '<div class="tb-label-modal-footer">' +
          '<button type="button" class="btn btn-secondary tb-modal-cancel">' + rcm_tb_label_escape_html(cancel_text) + '</button>' +
          '<button type="button" class="btn btn-primary tb-modal-submit">' + rcm_tb_label_escape_html(save_text) + '</button>' +
        '</div>' +
      '</div>' +
    '</div>'
  );

  function createConditionRow(cond) {
    cond = cond || { field: "subject", operator: "contains", value: "" };
    var row = $(
      '<div class="tb-cond-row">' +
        '<select class="form-control tb-cond-field">' +
          '<option value="subject"' + (cond.field === "subject" ? ' selected="selected"' : "") + '>Subject</option>' +
          '<option value="from"' + (cond.field === "from" ? ' selected="selected"' : "") + '>From</option>' +
          '<option value="to"' + (cond.field === "to" ? ' selected="selected"' : "") + '>To</option>' +
          '<option value="cc"' + (cond.field === "cc" ? ' selected="selected"' : "") + '>Cc</option>' +
          '<option value="body"' + (cond.field === "body" ? ' selected="selected"' : "") + '>Body</option>' +
        '</select>' +
        '<select class="form-control tb-cond-op">' +
          '<option value="contains"' + (cond.operator === "contains" ? ' selected="selected"' : "") + '>contains</option>' +
          '<option value="not_contains"' + (cond.operator === "not_contains" || cond.operator === "does_not_contain" ? ' selected="selected"' : "") + '>does not contain</option>' +
          '<option value="equals"' + (cond.operator === "equals" || cond.operator === "is" ? ' selected="selected"' : "") + '>is exactly</option>' +
          '<option value="not_equals"' + (cond.operator === "not_equals" || cond.operator === "is_not" ? ' selected="selected"' : "") + '>is not</option>' +
          '<option value="starts_with"' + (cond.operator === "starts_with" ? ' selected="selected"' : "") + '>starts with</option>' +
          '<option value="ends_with"' + (cond.operator === "ends_with" ? ' selected="selected"' : "") + '>ends with</option>' +
          '<option value="regex"' + (cond.operator === "regex" ? ' selected="selected"' : "") + '>regex</option>' +
        '</select>' +
        '<input type="text" class="form-control tb-cond-val" placeholder="Value..." value="' + rcm_tb_label_escape_html(cond.value || "") + '" />' +
        '<button type="button" class="tb-cond-del-btn" title="Remove condition">&times;</button>' +
      '</div>'
    );
    row.find(".tb-cond-del-btn").on("click", function () {
      if ($("#tb-filter-conditions-list .tb-cond-row").length > 1) {
        row.remove();
      } else {
        row.find(".tb-cond-val").val("");
      }
    });
    return row;
  }

  var condList = modal.find("#tb-filter-conditions-list");
  ruleConditions.forEach(function (c) {
    condList.append(createConditionRow(c));
  });

  modal.find("#tb-filter-add-cond-btn").on("click", function () {
    condList.append(createConditionRow());
  });

  function close_filter_modal() {
    modal.fadeOut(150, function () { modal.remove(); });
  }

  modal.find(".tb-modal-close, .tb-modal-cancel, .tb-label-modal-backdrop").on("click", function () {
    close_filter_modal();
  });

  function submit_filter() {
    var name = $.trim(modal.find("#tb-filter-rule-name").val());
    if (!name) {
      modal.find("#tb-filter-rule-name").focus();
      return;
    }

    var conditions = [];
    modal.find("#tb-filter-conditions-list .tb-cond-row").each(function () {
      var f = $(this).find(".tb-cond-field").val();
      var op = $(this).find(".tb-cond-op").val();
      var v = $.trim($(this).find(".tb-cond-val").val());
      if (v) {
        conditions.push({ field: f, operator: op, value: v });
      }
    });

    if (!conditions.length) {
      alert("Please specify at least one condition value.");
      return;
    }

    var chosenLabels = [];
    modal.find("input[name='tb_filter_labels[]']:checked").each(function () {
      chosenLabels.push($(this).val());
    });

    var targetF = modal.find("#tb-filter-target-folder").val();
    var isMarkRead = modal.find("#tb-filter-mark-read").is(":checked");
    var isEnabled = modal.find("#tb-filter-enabled").is(":checked");
    var scopeVal = modal.find("#tb-filter-rule-scope").val();

    var ruleObj = {
      id: isEdit ? ruleToEdit.id : undefined,
      name: name,
      enabled: isEnabled,
      scope: scopeVal,
      conditions: conditions,
      actions: {
        labels: chosenLabels,
        folder: targetF,
        mark_read: isMarkRead
      }
    };

    var lock = rcmail.set_busy(true, "loading");
    rcmail.http_post(
      "plugin.thunderbird_labels.save_filter",
      { rule: JSON.stringify(ruleObj) },
      lock
    );
    close_filter_modal();
  }

  modal.find(".tb-modal-submit").on("click", submit_filter);

  $("body").append(modal);
  modal.fadeIn(150);
  setTimeout(function () {
    modal.find("#tb-filter-rule-name").focus();
  }, 100);
};

rcm_tb_label_render_filter_rules_table = function () {
  var container = $("#tb-label-filter-rules-list");
  if (!container.length) return;
  container.empty();

  var rules = (rcmail.env && rcmail.env.tb_label_filters) || [];
  if (!rules.length) {
    container.html('<div class="tb-filter-empty" style="padding: 16px; color: #5f6368; font-style: italic;">' +
      rcm_tb_label_escape_html((rcmail.labels && rcmail.labels["thunderbird_labels.no_filter_rules"]) || "No filter rules defined yet.") +
      '</div>');
    return;
  }

  var customLabels = (rcmail.env && rcmail.env.tb_label_custom_labels) || {};
  var labelColors = (rcmail.env && rcmail.env.tb_label_colors) || {};

  var table = $('<table class="tb-filter-table"><thead><tr>' +
    '<th>Rule Name</th>' +
    '<th>Conditions</th>' +
    '<th>Actions</th>' +
    '<th style="text-align:center;">Active</th>' +
    '<th style="text-align:right;">Actions</th>' +
    '</tr></thead><tbody></tbody></table>');

  var tbody = table.find("tbody");

  rules.forEach(function (r) {
    // Conditions summary
    var condDesc = (r.scope === "any" ? "ANY: " : "ALL: ");
    var condParts = [];
    (r.conditions || []).forEach(function (c) {
      condParts.push(c.field + " " + c.operator + ' "' + c.value + '"');
    });
    condDesc += condParts.join(", ") || "(none)";

    // Actions summary
    var actParts = [];
    var rLabels = r.actions && (r.actions.labels || (r.actions.label ? [r.actions.label] : []));
    if (rLabels && rLabels.length) {
      var badges = rLabels.map(function (k) {
        var n = customLabels[k] || i18n_label(k);
        var col = labelColors[k] || "#757575";
        return '<span style="display:inline-block;padding:1px 6px;margin-right:4px;border-radius:4px;background:' + col + ';color:#fff;font-size:11px;font-weight:600;">' + rcm_tb_label_escape_html(n) + '</span>';
      }).join("");
      actParts.push(badges);
    }
    if (r.actions && r.actions.folder) {
      actParts.push('Move: <b>' + rcm_tb_label_escape_html(r.actions.folder) + '</b>');
    }
    if (r.actions && r.actions.mark_read) {
      actParts.push('Mark read');
    }
    var actDesc = actParts.join(" | ") || "(none)";

    var tr = $('<tr>' +
      '<td><b>' + rcm_tb_label_escape_html(r.name) + '</b></td>' +
      '<td style="font-size: 12px; color: #5f6368;">' + rcm_tb_label_escape_html(condDesc) + '</td>' +
      '<td>' + actDesc + '</td>' +
      '<td style="text-align:center;"><input type="checkbox" class="tb-rule-toggle"' + (r.enabled !== false ? ' checked="checked"' : "") + ' /></td>' +
      '<td style="text-align:right;">' +
        '<button type="button" class="btn btn-sm btn-secondary tb-rule-edit" style="margin-right: 6px;">Edit</button>' +
        '<button type="button" class="btn btn-sm btn-danger tb-rule-del">&times;</button>' +
      '</td>' +
      '</tr>');

    tr.find(".tb-rule-toggle").on("change", function () {
      var en = $(this).is(":checked");
      var lock = rcmail.set_busy(true, "loading");
      rcmail.http_post("plugin.thunderbird_labels.toggle_filter", { id: r.id, enabled: en ? 1 : 0 }, lock);
    });

    tr.find(".tb-rule-edit").on("click", function () {
      rcm_tb_label_show_filter_modal(r);
    });

    tr.find(".tb-rule-del").on("click", function () {
      var confirmMsg = (rcmail.labels && rcmail.labels["thunderbird_labels.confirm_delete_rule"]) || "Are you sure you want to delete this filter rule?";
      var doDel = function () {
        var lock = rcmail.set_busy(true, "loading");
        rcmail.http_post("plugin.thunderbird_labels.delete_filter", { id: r.id }, lock);
      };
      if (rcmail.confirm) {
        rcmail.confirm(confirmMsg, doDel);
      } else if (window.confirm(confirmMsg)) {
        doDel();
      }
    });

    tbody.append(tr);
  });

  container.append(table);
};

rcm_tb_label_init_filters = function () {
  if ($("#tb-label-filter-rules-list").length) {
    rcm_tb_label_render_filter_rules_table();
  }
  $("#tb-label-add-rule-btn").off("click").on("click", function (e) {
    e.preventDefault();
    rcm_tb_label_show_filter_modal();
  });
  $("#tb-label-apply-rules-btn").off("click").on("click", function (e) {
    e.preventDefault();
    var lock = rcmail.set_busy(true, "loading");
    rcmail.http_post("plugin.thunderbird_labels.apply_filters_now", { mbox: rcmail.env.mailbox || "INBOX" }, lock);
  });
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
  if (isNaN(current) || current < 0) current = 0;
  var next = Math.max(0, current + delta);
  rcmail.env.tb_label_counts[labelKey] = next;

  var badge = $("#tb-labels-list li[data-label=\"" + labelKey + "\"] .tb-label-count");
  if (badge.length) {
    var final_cnt = (typeof next === "number" && !isNaN(next) && next > 0) ? next : 0;
    badge.text(final_cnt).show().css("display", "inline-block");
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
  var labels_for_message = typeof tb_labels_for_message !== "undefined" ? tb_labels_for_message : null;
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
    // Update counts from loaded messages immediately
    var allLabels = rcmail.env.tb_label_custom_labels || {};
    $.each(allLabels, function (key) {
      if (key === "LABEL0") return;
      var badge = $("#tb-labels-list li[data-label=\"" + key + "\"] .tb-label-count");
      if (badge.length) {
        var server_c = (rcmail.env.tb_label_counts && typeof rcmail.env.tb_label_counts[key] !== "undefined")
          ? parseInt(rcmail.env.tb_label_counts[key], 10) : null;
        var final_c = (server_c !== null && !isNaN(server_c) && server_c > 0) ? server_c : rcm_tb_label_count_local_messages(key);
        var display_c = (final_c !== null && !isNaN(final_c) && final_c > 0) ? final_c : 0;
        badge.text(display_c).show().css("display", "inline-block");
      }
    });
    rcm_tb_label_fetch_counts();
  });

  // Commands received from PHP backend
  rcmail.addEventListener("plugin.thunderbird_labels.update_counts", function (data) {
    if (data && data.counts) {
      rcmail.env.tb_label_counts = data.counts;
      var allLabels = rcmail.env.tb_label_custom_labels || {};
      $.each(allLabels, function (key) {
        if (key === "LABEL0") return;
        var raw = (data.counts && typeof data.counts[key] !== "undefined") ? data.counts[key] : null;
        var count = (raw !== null) ? parseInt(raw, 10) : 0;
        var badge = $("#tb-labels-list li[data-label=\"" + key + "\"] .tb-label-count");
        if (badge.length) {
          var final_cnt = (!isNaN(count) && count > 0) ? count : 0;
          badge.text(final_cnt).show().css("display", "inline-block");
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
      if (data.key && rcmail.env.tb_label_counts) {
        delete rcmail.env.tb_label_counts[data.key];
      }
      if (data.key && rcmail.env.tb_label_active_filter === data.key) {
        rcm_tb_label_clear_filter();
      }
      new rcm_tb_label_css().inject();
      rcm_tb_label_render_sidebar_items();
      rcm_tb_label_update_popup_menu();
    }
  });

  // Filter rules event listeners
  rcmail.addEventListener("plugin.thunderbird_labels.filter_saved", function (data) {
    if (data && data.rules) {
      rcmail.env.tb_label_filters = data.rules;
      rcm_tb_label_render_filter_rules_table();
      var msg = (rcmail.labels && rcmail.labels["thunderbird_labels.rule_saved"]) || "Filter rule saved successfully.";
      rcmail.display_message(msg, "confirmation");
    }
  });

  rcmail.addEventListener("plugin.thunderbird_labels.filter_deleted", function (data) {
    if (data && data.rules) {
      rcmail.env.tb_label_filters = data.rules;
      rcm_tb_label_render_filter_rules_table();
      var msg = (rcmail.labels && rcmail.labels["thunderbird_labels.rule_deleted"]) || "Filter rule deleted.";
      rcmail.display_message(msg, "confirmation");
    }
  });

  rcmail.addEventListener("plugin.thunderbird_labels.filter_toggled", function (data) {
    if (data && data.rules) {
      rcmail.env.tb_label_filters = data.rules;
      rcm_tb_label_render_filter_rules_table();
    }
  });

  rcmail.addEventListener("plugin.thunderbird_labels.filters_applied", function (data) {
    var matched = (data && data.matched) || 0;
    var processed = (data && data.processed) || 0;
    var msg = (rcmail.labels && rcmail.labels["thunderbird_labels.filters_applied"]) || "Filters evaluated successfully.";
    rcmail.display_message(msg + " (" + matched + " matched / " + processed + " evaluated)", "confirmation");
    rcm_tb_label_fetch_counts();
    if (rcmail.task === "mail" && rcmail.command) {
      rcmail.command("checkmail");
    }
  });

  function rcm_tb_label_refresh_modal_folders(folders) {
    if (folders && Array.isArray(folders)) {
      rcmail.env.tb_label_mail_folders = folders;
    }
    var $select = $("#tb-filter-target-folder");
    if ($select.length && typeof rcm_tb_label_build_folder_options === "function") {
      var curVal = $select.val();
      $select.html(rcm_tb_label_build_folder_options(curVal));
    }
  }

  rcmail.addEventListener("plugin.thunderbird_labels.folders_list", function (data) {
    if (data && data.folders) {
      rcm_tb_label_refresh_modal_folders(data.folders);
    }
  });

  rcmail.addEventListener("plugin.thunderbird_labels.filters_list", function (data) {
    if (data && data.rules) {
      rcmail.env.tb_label_filters = data.rules;
    }
    if (data && data.folders) {
      rcm_tb_label_refresh_modal_folders(data.folders);
    }
    rcm_tb_label_render_filter_rules_table();
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

  // Ensure sidebar and filters are initialized
  setTimeout(rcm_tb_label_init_sidebar, 150);
  setTimeout(rcm_tb_label_init_filters, 200);
});
