(() => {
  // src/js/layout.js
  var BREAKPOINTS = [
    ["phone", 0, 480],
    ["small", 481, 1024],
    ["normal", 1025, 1200],
    ["large", 1201, Infinity]
  ];
  var html = document.documentElement;
  function screenMode() {
    const w = window.innerWidth;
    const mode = BREAKPOINTS.find(([, min, max]) => w >= min && w <= max)[0];
    for (const [name] of BREAKPOINTS) html.classList.toggle(`layout-${name}`, name === mode);
    return mode;
  }
  function detectTouch() {
    const touch = "ontouchstart" in window || navigator.maxTouchPoints > 0;
    html.classList.toggle("touch", touch);
    return touch;
  }
  function placeSidebar(mode) {
    const sidebar = document.getElementById("layout-sidebar");
    const menu = document.getElementById("layout-menu");
    const layoutEl = document.getElementById("layout");
    if (!sidebar || !menu || !layoutEl || document.body.classList.contains("task-settings")) return;
    const small = mode === "phone" || mode === "small";
    if (small && sidebar.parentElement !== menu) {
      menu.append(sidebar);
      sidebar.classList.add("gm-in-drawer");
    } else if (!small && sidebar.parentElement === menu) {
      const list = document.getElementById("layout-list");
      if (list) layoutEl.insertBefore(sidebar, list);
      else layoutEl.append(sidebar);
      sidebar.classList.remove("gm-in-drawer");
    }
  }
  var layout = {
    mode: null,
    touch: false,
    placeSidebar,
    init() {
      document.addEventListener("keydown", (e) => {
        if (["Tab", "ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight", "Enter", " "].includes(e.key)) {
          html.classList.add("kbd");
        }
      });
      document.addEventListener("pointerdown", () => html.classList.remove("kbd"), true);
      this.touch = detectTouch();
      this.mode = screenMode();
      placeSidebar(this.mode);
      window.addEventListener("resize", () => {
        const m = screenMode();
        if (m !== this.mode) {
          this.mode = m;
          placeSidebar(m);
          document.dispatchEvent(new CustomEvent("gm:layout", { detail: { mode: m } }));
        }
      });
      try {
        if (localStorage.getItem("gm.nav") === "collapsed") html.classList.add("nav-collapsed");
      } catch {
      }
    },
    toggleNav(force) {
      if (this.mode === "phone" || this.mode === "small") {
        html.classList.toggle("nav-open", force === void 0 ? void 0 : !force);
        return false;
      }
      const collapsed = html.classList.toggle("nav-collapsed", force);
      try {
        localStorage.setItem("gm.nav", collapsed ? "collapsed" : "expanded");
      } catch {
      }
      return collapsed;
    }
  };

  // src/js/theme.js
  var html2 = document.documentElement;
  var MODES = ["auto", "light", "dark"];
  var media = window.matchMedia("(prefers-color-scheme: dark)");
  function readMode() {
    return (document.cookie.match(/(?:^|;\s*)colorMode=(dark|light|auto)/) || [])[1] || "auto";
  }
  function isDark(mode) {
    return mode === "dark" || mode === "auto" && media.matches;
  }
  function paint(mode) {
    const dark = isDark(mode);
    html2.classList.toggle("dark-mode", dark);
    html2.setAttribute("data-theme", mode);
    for (const f of document.querySelectorAll("iframe")) {
      try {
        f.contentDocument?.documentElement?.classList.toggle("dark-mode", dark);
      } catch {
      }
    }
    const btn = document.getElementById("theme-toggle");
    if (btn) {
      const i = btn.querySelector(".ico");
      if (i) {
        i.className = `ico ico-lg ${mode === "auto" ? "ico-theme-auto" : dark ? "ico-light-mode" : "ico-dark-mode"}`;
      }
      btn.dataset.mode = mode;
    }
    document.dispatchEvent(new CustomEvent("gm:theme", { detail: { mode, dark } }));
  }
  var theme = {
    get mode() {
      return readMode();
    },
    get dark() {
      return isDark(readMode());
    },
    set(mode) {
      if (!MODES.includes(mode)) mode = "auto";
      if (window.rcmail?.set_cookie) rcmail.set_cookie("colorMode", mode, false);
      else document.cookie = `colorMode=${mode}; path=/; max-age=31536000; SameSite=Lax`;
      paint(mode);
      return false;
    },
    // header button: auto → light → dark → auto …
    cycle() {
      const i = MODES.indexOf(readMode());
      return this.set(MODES[(i + 1) % MODES.length]);
    },
    init() {
      paint(readMode());
      media.addEventListener("change", () => {
        if (readMode() === "auto") paint("auto");
      });
      document.addEventListener(
        "load",
        (e) => {
          if (e.target.tagName === "IFRAME") paint(readMode());
        },
        true
      );
    }
  };

  // src/js/popup.js
  var openMenus = [];
  function place(menu, anchor) {
    const r = anchor.getBoundingClientRect();
    const m = menu.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const parent2 = anchor.closest(".popupmenu.gm-open, .gm-menu.gm-open");
    if (parent2 && parent2 !== menu) {
      const pr = parent2.getBoundingClientRect();
      let left2 = pr.right + 8;
      if (left2 + m.width > vw - 8) left2 = Math.max(8, pr.left - m.width - 8);
      let top2 = r.top - 6;
      if (top2 + m.height > vh - 8) top2 = Math.max(8, vh - m.height - 8);
      menu.style.left = `${Math.round(left2)}px`;
      menu.style.top = `${Math.round(top2)}px`;
      return;
    }
    const alignRight = menu.dataset.align === "right" || r.left + m.width > vw - 8;
    let left = alignRight ? r.right - m.width : r.left;
    let top = r.bottom + 4;
    if (top + m.height > vh - 8) top = Math.max(8, r.top - m.height - 4);
    left = Math.max(8, Math.min(left, vw - m.width - 8));
    menu.style.left = `${Math.round(left)}px`;
    menu.style.top = `${Math.round(top)}px`;
  }
  function focusables(menu) {
    return [
      ...menu.querySelectorAll('a[href]:not(.disabled), button:not([disabled]), [tabindex="0"]')
    ].filter((el2) => el2.offsetParent !== null);
  }
  function isOpen(id) {
    return openMenus.some((m) => m.menu.id === id);
  }
  function open(id, anchor, event) {
    const menu = typeof id === "string" ? document.getElementById(id) : id;
    if (!menu) return false;
    if (isOpen(menu.id)) return close(menu.id);
    if (!(anchor && openMenus.some((m) => m.menu.contains(anchor)))) closeAll();
    document.body.append(menu);
    menu.querySelectorAll("ul.toolbarmenu").forEach((list) => list.classList.add("menu"));
    if (menu.id === "folder-selector") {
      for (const a of menu.querySelectorAll("a")) {
        if (a.dataset.gmFolderInset) continue;
        const depth = Math.max(0, parseFloat(a.style.paddingLeft) || 0) / 16;
        a.style.paddingLeft = "";
        a.style.setProperty("--gm-folder-depth", depth);
        a.dataset.gmFolderInset = "1";
      }
    }
    menu.classList.add("gm-open");
    menu.setAttribute("role", menu.getAttribute("role") || "menu");
    if (anchor) place(menu, anchor);
    else if (event) {
      menu.style.left = `${event.clientX}px`;
      menu.style.top = `${event.clientY}px`;
    }
    openMenus.push({ menu, anchor, returnFocus: document.activeElement });
    anchor?.setAttribute("aria-expanded", "true");
    anchor?.classList.add("gm-active");
    const first = focusables(menu)[0];
    if (first && !(event && event.pointerType === "mouse")) first.focus({ preventScroll: true });
    window.rcmail?.triggerEvent("menu-open", {
      name: menu.id,
      obj: menu,
      props: { menu: menu.id },
      originalEvent: event
    });
    return false;
  }
  function close(id) {
    const idx = openMenus.findIndex((m) => m.menu.id === id);
    if (idx < 0) return false;
    for (const m of openMenus.splice(idx)) {
      m.menu.classList.remove("gm-open");
      if (m.menu.style.display === "block") m.menu.style.display = "";
      m.anchor?.setAttribute("aria-expanded", "false");
      m.anchor?.classList.remove("gm-active");
      window.rcmail?.triggerEvent("menu-close", {
        name: m.menu.id,
        obj: m.menu,
        props: { menu: m.menu.id }
      });
      if (m.returnFocus && document.body.contains(m.returnFocus)) {
        m.returnFocus.focus({ preventScroll: true });
      }
    }
    return false;
  }
  function closeAll() {
    while (openMenus.length) close(openMenus[openMenus.length - 1].menu.id);
  }
  function onDocumentPointer(e) {
    if (!openMenus.length) return;
    if (e.target.closest(".gm-select-list")) return;
    if (openMenus.some((m) => m.menu.contains(e.target) || m.anchor?.contains(e.target))) return;
    closeAll();
  }
  function onKey(e) {
    if (!openMenus.length) return;
    const { menu } = openMenus[openMenus.length - 1];
    if (e.key === "Escape") {
      e.preventDefault();
      close(menu.id);
      return;
    }
    if (e.key === "ArrowDown" || e.key === "ArrowUp" || e.key === "Home" || e.key === "End") {
      const items = focusables(menu);
      if (!items.length) return;
      e.preventDefault();
      const i = items.indexOf(document.activeElement);
      const next = e.key === "Home" ? 0 : e.key === "End" ? items.length - 1 : (i + (e.key === "ArrowDown" ? 1 : -1) + items.length) % items.length;
      items[next].focus();
    }
  }
  var popup = {
    open,
    close,
    closeAll,
    isOpen,
    init() {
      document.addEventListener("click", (e) => {
        const trigger = e.target.closest("[data-popup]");
        if (trigger && !trigger.classList.contains("disabled")) {
          e.preventDefault();
          open(trigger.dataset.popup, trigger, e);
          return;
        }
        const item = e.target.closest('.gm-open .menu a, .gm-open [role="menuitem"]');
        if (item && !item.dataset.popup && !item.classList.contains("disabled") && item.getAttribute("aria-haspopup") !== "true" && !item.querySelector(".folder-selector-link")) {
          closeAll();
        }
      });
      document.addEventListener("pointerdown", onDocumentPointer, true);
      document.addEventListener("keydown", onKey);
      const hookFrame = (f) => {
        const attach = () => {
          try {
            f.contentDocument?.addEventListener("pointerdown", () => closeAll(), true);
          } catch {
          }
        };
        f.addEventListener("load", attach);
        attach();
      };
      document.querySelectorAll("iframe").forEach(hookFrame);
      new MutationObserver((muts) => {
        for (const m of muts) {
          for (const n of m.addedNodes) {
            if (n.nodeType === 1) {
              (n.tagName === "IFRAME" ? [n] : [...n.querySelectorAll?.("iframe") || []]).forEach(
                hookFrame
              );
            }
          }
        }
      }).observe(document.documentElement, { childList: true, subtree: true });
      window.addEventListener("blur", () => closeAll());
      window.addEventListener("resize", () => {
        for (const m of openMenus) if (m.anchor) place(m.menu, m.anchor);
      });
    }
  };

  // src/js/toast.js
  var stackId = "messagestack";
  var timers = /* @__PURE__ */ new WeakMap();
  function container() {
    let c = document.getElementById(stackId);
    if (!c) {
      c = document.createElement("div");
      c.id = stackId;
      document.body.append(c);
    }
    c.classList.add("gm-toasts");
    c.setAttribute("role", "status");
    c.setAttribute("aria-live", "polite");
    return c;
  }
  function show(text, o = {}) {
    const c = container();
    const t2 = document.createElement("div");
    t2.className = `gm-toast ${o.type || "notice"}`;
    const msg = document.createElement("span");
    msg.className = "gm-toast-text";
    if (o.html) msg.innerHTML = text;
    else msg.textContent = text;
    t2.append(msg);
    if (o.type === "loading") {
      const sp = document.createElement("span");
      sp.className = "gm-spinner";
      t2.prepend(sp);
    }
    if (o.action) {
      const a = document.createElement("button");
      a.type = "button";
      a.className = "gm-toast-action";
      a.textContent = o.action.label;
      a.addEventListener("click", () => {
        o.action.onClick?.();
        dismiss(t2);
      });
      t2.append(a);
    }
    if (o.dismissible !== false && o.type !== "loading") {
      const x = document.createElement("button");
      x.type = "button";
      x.className = "gm-toast-close";
      x.innerHTML = '<i class="ico ico-close" aria-hidden="true"></i>';
      x.setAttribute("aria-label", window.rcmail?.get_label?.("close") || "Close");
      x.addEventListener("click", () => dismiss(t2));
      t2.append(x);
    }
    const live = [...c.children].filter((k) => !k.classList.contains("gm-leaving"));
    for (const old of live.slice(0, Math.max(0, live.length - 2))) dismiss(old);
    c.append(t2);
    requestAnimationFrame(() => t2.classList.add("gm-visible"));
    const timeout = o.timeout ?? (o.type === "error" || o.type === "warning" ? 8e3 : o.type === "loading" ? 0 : 4e3);
    if (timeout > 0) {
      timers.set(
        t2,
        setTimeout(() => dismiss(t2), timeout)
      );
    }
    return t2;
  }
  function dismiss(t2) {
    if (!t2 || !t2.parentNode) return;
    clearTimeout(timers.get(t2));
    t2.classList.remove("gm-visible");
    t2.classList.add("gm-leaving");
    setTimeout(() => t2.remove(), 180);
  }
  var toast = {
    show,
    dismiss,
    init() {
      container();
      const html9 = document.documentElement;
      const inPopup = html9.classList.contains("gm-in-popup");
      window.rcmail?.addEventListener("message", (e) => {
        const node = e.object && (e.object.nodeType ? e.object : e.object[0]);
        if (!node) return;
        const text = node.textContent.trim();
        if (window.rcmail.env.action === "compose" && e.type === "confirmation" && text === window.rcmail.get_label("messagesaved")) {
          const when = (/* @__PURE__ */ new Date()).toLocaleTimeString(void 0, {
            hour: "2-digit",
            minute: "2-digit"
          });
          const status = `${text.replace(/\.$/, "")} \xB7 ${when}`;
          document.querySelectorAll(".gm-compose-status").forEach((s) => s.textContent = status);
          try {
            window.frameElement?.closest(".gm-cwin")?.querySelector(".gm-cwin-title")?.setAttribute("data-status", status);
          } catch {
          }
          window.rcmail.hide_message(node);
          return;
        }
        if (inPopup && e.type !== "loading") {
          try {
            window.parent.UI.toast.show(text, { type: e.type });
            window.rcmail.hide_message(node);
            return;
          } catch {
          }
        }
        node.classList.add("gm-toast", "gm-visible");
        if (e.type === "loading") {
          const sp = document.createElement("span");
          sp.className = "gm-spinner";
          node.prepend(sp);
        }
        if (e.type !== "loading" && !node.querySelector(".gm-toast-close")) {
          const x = document.createElement("button");
          x.type = "button";
          x.className = "gm-toast-close";
          x.innerHTML = '<i class="ico ico-close" aria-hidden="true"></i>';
          x.addEventListener("click", () => rcmail.hide_message(node));
          node.append(x);
        }
        const kids = [...container().children].filter((k) => !k.classList.contains("gm-leaving"));
        for (const old of kids.slice(0, Math.max(0, kids.length - 3))) {
          old.classList.add("gm-leaving");
          rcmail.hide_message(old);
        }
      });
    }
  };

  // src/js/modal.js
  var label = (name) => window.rcmail?.get_label ? rcmail.get_label(name) : name;
  var stack = [];
  function el(tag, cls, html9) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html9 != null) n.innerHTML = html9;
    return n;
  }
  function trapFocus(dialog, e) {
    const f = [
      ...dialog.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])'
      )
    ].filter((x) => x.offsetParent !== null);
    if (!f.length) return;
    const first = f[0];
    const last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  }
  function openDialog(o = {}) {
    if (window !== window.top && !o.local) {
      try {
        const topUI = window.top.UI;
        if (topUI?.modal?.open && topUI.modal.open !== openDialog) {
          const opts = { ...o };
          if (o.content && typeof o.content === "object" && typeof o.content.nodeType === "number") {
            opts.content = window.top.document.adoptNode(o.content);
          }
          return topUI.modal.open(opts);
        }
      } catch {
      }
    }
    const scrim = el("div", "gm-scrim");
    const dialog = el("div", `gm-dialog ${o.dialogClass || ""}`.trim());
    dialog.setAttribute("role", o.role || "dialog");
    dialog.setAttribute("aria-modal", "true");
    if (o.width) dialog.style.width = typeof o.width === "number" ? `${o.width}px` : o.width;
    if (o.height) dialog.style.height = typeof o.height === "number" ? `${o.height}px` : o.height;
    const id = `gm-dialog-${Date.now().toString(36)}${stack.length}`;
    if (o.title) {
      const h = el("div", "gm-dialog-head");
      const t2 = el("h2", "gm-dialog-title");
      t2.id = `${id}-title`;
      t2.textContent = o.title;
      h.append(t2);
      if (o.closeButton !== false) {
        const x = el(
          "button",
          "gm-icon-btn gm-dialog-close",
          '<i class="ico ico-close" aria-hidden="true"></i>'
        );
        x.type = "button";
        x.title = label("close");
        x.addEventListener("click", () => handle.close());
        h.append(x);
      }
      dialog.append(h);
      dialog.setAttribute("aria-labelledby", t2.id);
    }
    const body = el("div", "gm-dialog-body");
    if (o.content && typeof o.content === "object" && typeof o.content.nodeType === "number") {
      body.append(o.content);
    } else if (o.content != null) body.innerHTML = o.content;
    dialog.append(body);
    const buttons = (o.buttons || []).filter(Boolean);
    if (buttons.length) {
      const foot = el("div", "gm-dialog-actions");
      for (const b of buttons) {
        const btn = el(
          "button",
          `gm-btn ${b.mainaction ? "gm-btn-filled" : "gm-btn-text"} ${b.class || ""}`.trim()
        );
        btn.type = "button";
        btn.textContent = b.text;
        if (b.mainaction) btn.dataset.mainaction = "1";
        btn.addEventListener("click", (e) => {
          if (b.click) b.click.call(dialog, e, handle);
          else handle.close();
        });
        foot.append(btn);
      }
      dialog.append(foot);
    }
    scrim.append(dialog);
    document.body.append(scrim);
    document.documentElement.classList.add("gm-modal-open");
    const prevFocus = document.activeElement;
    const onKey2 = (e) => {
      if (stack[stack.length - 1] !== handle) return;
      if (e.key === "Escape" && o.closeOnEscape !== false) {
        e.preventDefault();
        handle.close();
      } else if (e.key === "Tab") trapFocus(dialog, e);
      else if (e.key === "Enter" && !["TEXTAREA", "SELECT"].includes(e.target.tagName) && !e.target.closest("[contenteditable]")) {
        const main = dialog.querySelector("[data-mainaction]");
        if (main && (e.target.tagName !== "BUTTON" || e.target === main)) {
          e.preventDefault();
          main.click();
        }
      }
    };
    document.addEventListener("keydown", onKey2);
    if (o.closeOnScrim !== false) {
      scrim.addEventListener("pointerdown", (e) => e.target === scrim && handle.close());
    }
    const handle = {
      el: dialog,
      scrim,
      closed: false,
      close() {
        if (handle.closed) return;
        handle.closed = true;
        document.removeEventListener("keydown", onKey2);
        stack = stack.filter((h) => h !== handle);
        scrim.remove();
        if (!stack.length) document.documentElement.classList.remove("gm-modal-open");
        o.onClose?.();
        if (prevFocus && document.body.contains(prevFocus)) prevFocus.focus({ preventScroll: true });
      },
      setOption(opts) {
        if (opts.width) {
          dialog.style.width = typeof opts.width === "number" ? `${opts.width}px` : opts.width;
        }
        if (opts.height) {
          dialog.style.height = typeof opts.height === "number" ? `${opts.height}px` : opts.height;
        }
        if (opts.title) {
          const t2 = dialog.querySelector(".gm-dialog-title");
          if (t2) t2.textContent = opts.title;
        }
      }
    };
    stack.push(handle);
    requestAnimationFrame(() => {
      scrim.classList.add("gm-visible");
      const focusTarget = dialog.querySelector(
        "[autofocus], input:not([type=hidden]):not([disabled]), textarea, select"
      ) || dialog.querySelector("[data-mainaction]") || dialog.querySelector("button");
      focusTarget?.focus({ preventScroll: true });
    });
    return handle;
  }
  var modal = {
    open: openDialog,
    alert(message, title) {
      return new Promise((resolve) => {
        openDialog({
          title: title || label("errortitle") || "Notice",
          content: el("p", "gm-dialog-text", escapeHtml(message)),
          dialogClass: "gm-dialog-sm",
          buttons: [
            { text: label("ok"), mainaction: true, click: (e, h) => (h.close(), resolve(true)) }
          ],
          onClose: () => resolve(true)
        });
      });
    },
    confirm(message, { title, okLabel, cancelLabel, danger } = {}) {
      return new Promise((resolve) => {
        let answered = false;
        openDialog({
          title: title || "",
          content: el("p", "gm-dialog-text", escapeHtml(message)),
          dialogClass: "gm-dialog-sm",
          buttons: [
            {
              text: cancelLabel || label("cancel"),
              click: (e, h) => (answered = true, resolve(false), h.close())
            },
            {
              text: okLabel || label("ok"),
              mainaction: true,
              class: danger ? "gm-btn-danger" : "",
              click: (e, h) => (answered = true, resolve(true), h.close())
            }
          ],
          onClose: () => !answered && resolve(false)
        });
      });
    },
    prompt(message, { title, value = "", okLabel, cancelLabel, placeholder } = {}) {
      return new Promise((resolve) => {
        let answered = false;
        const wrap = el("div", "gm-field");
        const p = el("label", "gm-dialog-text", escapeHtml(message));
        const input = el("input", "gm-input");
        input.type = "text";
        input.value = value;
        if (placeholder) input.placeholder = placeholder;
        p.append(input);
        wrap.append(p);
        openDialog({
          title: title || "",
          content: wrap,
          dialogClass: "gm-dialog-sm",
          buttons: [
            {
              text: cancelLabel || label("cancel"),
              click: (e, h) => (answered = true, resolve(null), h.close())
            },
            {
              text: okLabel || label("ok"),
              mainaction: true,
              click: (e, h) => (answered = true, resolve(input.value), h.close())
            }
          ],
          onClose: () => !answered && resolve(null)
        });
      });
    },
    closeAll() {
      for (const h of [...stack]) h.close();
    },
    get isOpen() {
      return stack.length > 0;
    }
  };
  function escapeHtml(s) {
    return String(s).replace(
      /[&<>"']/g,
      (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c]
    );
  }

  // src/js/controls.js
  var selectSeq = 0;
  function enhanceListbox(sel) {
    sel.dataset.gm = "1";
    const wrap = document.createElement("div");
    wrap.className = "gm-select gm-select-multiple";
    const list = document.createElement("div");
    list.className = "gm-choice-list";
    list.setAttribute("role", "listbox");
    list.setAttribute("aria-multiselectable", String(sel.multiple));
    const label10 = sel.labels?.[0];
    if (label10) {
      label10.id || (label10.id = `gm-choice-label-${++selectSeq}`);
      list.setAttribute("aria-labelledby", label10.id);
    }
    const render = () => {
      wrap.hidden = sel.hidden || sel.classList.contains("hidden") || sel.style.display === "none";
      wrap.style.display = wrap.hidden ? "none" : "";
      const activeIndex = list.contains(document.activeElement) ? document.activeElement.dataset.index : null;
      list.replaceChildren();
      for (const option of sel.options) {
        const item = document.createElement("button");
        item.type = "button";
        item.className = "gm-choice-option";
        item.dataset.index = option.index;
        item.setAttribute("role", "option");
        item.setAttribute("aria-selected", String(option.selected));
        item.disabled = sel.disabled || option.disabled || !!option.parentElement.disabled;
        item.textContent = option.textContent;
        item.addEventListener("click", () => {
          if (sel.multiple) option.selected = !option.selected;
          else sel.selectedIndex = option.index;
          sel.dispatchEvent(new Event("change", { bubbles: true }));
        });
        list.append(item);
      }
      if (activeIndex !== null) list.querySelector(`[data-index="${activeIndex}"]`)?.focus();
    };
    list.addEventListener("keydown", (event) => {
      if (!["ArrowDown", "ArrowUp", "Home", "End"].includes(event.key)) return;
      event.preventDefault();
      const items = [...list.querySelectorAll("button:not(:disabled)")];
      const current2 = items.indexOf(document.activeElement);
      const index = event.key === "Home" ? 0 : event.key === "End" ? items.length - 1 : current2 + (event.key === "ArrowDown" ? 1 : -1);
      items[Math.max(0, Math.min(items.length - 1, index))]?.focus();
    });
    sel.classList.add("gm-select-native");
    sel.tabIndex = -1;
    sel.setAttribute("aria-hidden", "true");
    sel.parentNode.insertBefore(wrap, sel);
    wrap.append(sel, list);
    sel.addEventListener("change", render);
    new MutationObserver(render).observe(sel, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["disabled", "selected", "style", "class", "hidden"]
    });
    render();
  }
  function enhanceSelect(sel) {
    if (sel.dataset.gm === "1" || sel.closest(".gm-native")) {
      return;
    }
    if (sel.multiple || sel.size > 1) return enhanceListbox(sel);
    sel.dataset.gm = "1";
    const id = `gm-select-${++selectSeq}`;
    const wrap = document.createElement("div");
    wrap.className = "gm-select";
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "gm-select-btn";
    btn.id = `${id}-btn`;
    btn.setAttribute("aria-haspopup", "listbox");
    btn.setAttribute("aria-expanded", "false");
    if (sel.disabled) btn.disabled = true;
    const list = document.createElement("div");
    list.className = "gm-select-list gm-menu";
    list.id = `${id}-list`;
    list.setAttribute("role", "listbox");
    const render = () => {
      wrap.hidden = sel.hidden || sel.classList.contains("hidden") || sel.style.display === "none";
      wrap.style.display = wrap.hidden ? "none" : "";
      btn.disabled = sel.disabled;
      const opt = sel.options[sel.selectedIndex];
      btn.innerHTML = `<span class="gm-select-value">${escapeHtml(opt ? opt.textContent : "")}</span><i class="ico ico-arrow-drop-down" aria-hidden="true"></i>`;
      list.innerHTML = "";
      for (const o of sel.querySelectorAll("option, optgroup")) {
        if (o.tagName === "OPTGROUP") {
          const group = document.createElement("div");
          group.className = "gm-select-group";
          group.textContent = o.label;
          list.append(group);
          continue;
        }
        const item = document.createElement("div");
        item.className = "gm-select-option" + (o.selected ? " selected" : "") + (o.disabled || o.parentElement.disabled ? " disabled" : "");
        item.setAttribute("role", "option");
        item.setAttribute("aria-selected", o.selected ? "true" : "false");
        item.tabIndex = -1;
        item.dataset.index = o.index;
        item.textContent = o.textContent;
        list.append(item);
      }
    };
    render();
    const openList = () => {
      if (btn.disabled) return;
      list.classList.add("gm-open");
      btn.setAttribute("aria-expanded", "true");
      document.body.append(list);
      const r = btn.getBoundingClientRect();
      const availableWidth = document.documentElement.clientWidth - 16;
      list.style.minWidth = `${Math.min(r.width, availableWidth)}px`;
      list.style.maxWidth = `${availableWidth}px`;
      const listWidth = list.getBoundingClientRect().width;
      list.style.left = `${Math.max(8, Math.min(r.left, document.documentElement.clientWidth - listWidth - 8))}px`;
      const below = window.innerHeight - r.bottom;
      const h = Math.min(list.scrollHeight, 320, Math.max(below - 8, r.top - 8));
      list.style.maxHeight = `${h}px`;
      list.style.top = below >= h + 8 ? `${r.bottom + 4}px` : `${r.top - h - 4}px`;
      (list.querySelector(".selected") || list.querySelector(".gm-select-option"))?.focus();
      setTimeout(() => document.addEventListener("pointerdown", onOutside, true));
    };
    const closeList = (focusBtn = true) => {
      if (!list.classList.contains("gm-open")) return;
      list.classList.remove("gm-open");
      btn.setAttribute("aria-expanded", "false");
      list.remove();
      document.removeEventListener("pointerdown", onOutside, true);
      if (focusBtn) btn.focus();
    };
    const onOutside = (e) => {
      if (!list.contains(e.target) && e.target !== btn) closeList(false);
    };
    const choose = (idx) => {
      if (!sel.options[idx] || sel.options[idx].disabled || sel.options[idx].parentElement.disabled) {
        return;
      }
      if (sel.selectedIndex !== idx) {
        sel.selectedIndex = idx;
        sel.dispatchEvent(new Event("change", { bubbles: true }));
      }
      render();
      closeList();
    };
    btn.addEventListener(
      "click",
      () => list.classList.contains("gm-open") ? closeList() : openList()
    );
    btn.addEventListener("keydown", (e) => {
      if (["ArrowDown", "ArrowUp"].includes(e.key)) {
        e.preventDefault();
        if (!list.classList.contains("gm-open")) {
          const n = sel.selectedIndex + (e.key === "ArrowDown" ? 1 : -1);
          if (n >= 0 && n < sel.options.length) choose(n);
        }
      }
    });
    list.addEventListener("click", (e) => {
      const it = e.target.closest(".gm-select-option");
      if (it) choose(+it.dataset.index);
    });
    list.addEventListener("keydown", (e) => {
      const items = [...list.querySelectorAll(".gm-select-option:not(.disabled)")];
      const i = items.indexOf(document.activeElement);
      if (e.key === "Escape") e.preventDefault(), e.stopPropagation(), closeList();
      else if (e.key === "Enter" || e.key === " ") {
        e.preventDefault(), e.stopPropagation(), i >= 0 && choose(+items[i].dataset.index);
      } else if (e.key === "ArrowDown") {
        e.preventDefault(), e.stopPropagation(), items[Math.min(i + 1, items.length - 1)]?.focus();
      } else if (e.key === "ArrowUp") {
        e.preventDefault(), e.stopPropagation(), items[Math.max(i - 1, 0)]?.focus();
      } else if (e.key === "Tab") closeList(false);
    });
    sel.addEventListener("change", render);
    new MutationObserver(render).observe(sel, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["disabled", "selected", "style", "class", "hidden"]
    });
    sel.classList.add("gm-select-native");
    sel.tabIndex = -1;
    sel.setAttribute("aria-hidden", "true");
    sel.parentNode.insertBefore(wrap, sel);
    wrap.append(sel, btn);
  }
  function enhanceFile(input) {
    if (input.dataset.gm === "1" || input.closest(".gm-native, form.hidden, [hidden], #uploadform, #upload-form") || input.classList.contains("hidden")) {
      return;
    }
    input.dataset.gm = "1";
    const wrap = document.createElement("div");
    wrap.className = "gm-file";
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "gm-btn gm-btn-outlined";
    btn.innerHTML = `<i class="ico ico-upload" aria-hidden="true"></i><span>${escapeHtml(window.rcmail?.get_label?.(input.multiple ? "choosefiles" : "choosefile") || "Choose file")}</span>`;
    const name = document.createElement("span");
    name.className = "gm-file-name";
    btn.addEventListener("click", () => input.click());
    input.addEventListener("change", () => {
      name.textContent = [...input.files].map((f) => f.name).join(", ");
    });
    input.parentNode.insertBefore(wrap, input);
    wrap.append(input, btn, name);
    input.classList.add("gm-file-native");
    input.tabIndex = -1;
  }
  function enhance(root = document) {
    for (const s of root.querySelectorAll("select")) enhanceSelect(s);
    for (const f of root.querySelectorAll('input[type="file"]')) enhanceFile(f);
  }
  var controls = {
    enhance,
    init() {
      enhance(document);
      new MutationObserver((muts) => {
        for (const m of muts) for (const n of m.addedNodes) if (n.nodeType === 1) enhance(n);
      }).observe(document.body, { childList: true, subtree: true });
    }
  };

  // src/js/unload.js
  var label2 = (n) => window.rcmail?.get_label ? rcmail.get_label(n) : n;
  function dirty() {
    const rc = window.rcmail;
    return !!(rc && rc.env.action === "compose" && !rc.compose_skip_unsavedcheck && typeof rc.cmp_hash === "string" && rc.cmp_hash !== rc.compose_field_hash());
  }
  var unload = {
    init() {
      if (!window.rcmail) return;
      const rc = rcmail;
      const origHref = rc.location_href;
      rc.location_href = function(url, target, frame, replace) {
        if (!frame && dirty()) {
          modal.confirm(label2("notsentwarning"), {
            okLabel: label2("discard") || label2("ok"),
            danger: true
          }).then((ok) => {
            if (ok) {
              rc.compose_skip_unsavedcheck = true;
              origHref.call(rc, url, target, frame, replace);
            }
          });
          return;
        }
        return origHref.call(rc, url, target, frame, replace);
      };
    }
  };

  // src/js/compose-window.js
  var html3 = document.documentElement;
  var label3 = (n) => window.rcmail?.get_label ? rcmail.get_label(n) : n;
  var windows = [];
  var W = 600;
  var GAP = 16;
  function relayout() {
    const available = window.innerWidth - GAP * 2;
    const width = (w) => w.state === "min" ? 260 : Math.min(W, available);
    const total = () => windows.filter((w) => w.state !== "full").reduce((n, w) => n + width(w) + 12, -12);
    for (const w of windows) {
      if (total() <= available) break;
      if (w.state === "normal") {
        w.state = "min";
        w.el.dataset.state = "min";
      }
    }
    let right = GAP;
    for (const w of windows) {
      if (w.state === "full") continue;
      w.el.style.right = `${right}px`;
      right += width(w) + 12;
    }
    html3.classList.toggle(
      "gm-cwin-fullscreen",
      windows.some((w) => w.state === "full")
    );
  }
  window.addEventListener("resize", relayout);
  function isDirty(win) {
    try {
      const rc = win.frame.contentWindow.rcmail;
      return !!(rc && typeof rc.cmp_hash === "string" && rc.cmp_hash !== rc.compose_field_hash());
    } catch {
      return false;
    }
  }
  function frameRc(win) {
    try {
      return win.frame.contentWindow.rcmail;
    } catch {
      return null;
    }
  }
  var composeWindow = {
    get count() {
      return windows.length;
    },
    open(url) {
      if (windows.length >= 3) {
        toast.show(label3("windowopenerror") || "Too many compose windows", { type: "warning" });
        return null;
      }
      const el2 = document.createElement("div");
      el2.className = "gm-cwin";
      el2.dataset.state = "normal";
      el2.setAttribute("role", "dialog");
      el2.innerHTML = `
      <div class="gm-cwin-head">
        <span class="gm-cwin-title">${label3("compose")}</span>
        <span class="gm-cwin-actions">
          <button type="button" class="gm-cwin-btn min" title="${label3("minimize") || "Minimize"}"><i class="ico ico-minimize" aria-hidden="true"></i></button>
          <button type="button" class="gm-cwin-btn full" title="${label3("fullscreen") || "Full screen"}"><i class="ico ico-fullscreen" aria-hidden="true"></i></button>
          <button type="button" class="gm-cwin-btn close" title="${label3("close")}"><i class="ico ico-close" aria-hidden="true"></i></button>
        </span>
      </div>
      <div class="gm-cwin-loading" aria-hidden="true"><div class="gm-skel gm-skel-row"></div><div class="gm-skel gm-skel-row"></div><div class="gm-skel gm-skel-row short"></div><div class="gm-skel gm-skel-block"></div></div>
      <iframe class="gm-cwin-frame" title="${label3("compose")}"></iframe>`;
      const frame = el2.querySelector("iframe");
      const win = { el: el2, frame, state: "normal", url };
      windows.push(win);
      document.body.append(el2);
      relayout();
      requestAnimationFrame(() => el2.classList.add("gm-visible"));
      frame.src = url + (url.includes("?") ? "&" : "?") + "_extwin=1";
      frame.addEventListener("load", () => {
        el2.classList.add("gm-loaded");
        try {
          const w = frame.contentWindow;
          w.document.documentElement.classList.add("gm-in-popup");
          w.close = () => this.close(win, true);
          const subj = w.document.getElementById("compose-subject");
          const setTitle = () => {
            el2.querySelector(".gm-cwin-title").textContent = subj?.value?.trim() || label3("compose");
          };
          subj?.addEventListener("input", setTitle);
          setTitle();
          if (w.rcmail) w.rcmail.env.gm_popup = true;
          w.document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && !modal.isOpen && !w.document.querySelector(".popupmenu.gm-open")) {
              this.close(win);
            }
          });
        } catch {
        }
      });
      el2.querySelector(".min").addEventListener("click", () => this.toggleMin(win));
      el2.querySelector(".full").addEventListener("click", () => this.toggleFull(win));
      el2.querySelector(".close").addEventListener("click", () => this.close(win));
      el2.querySelector(".gm-cwin-head").addEventListener("dblclick", (e) => {
        if (!e.target.closest("button")) this.toggleMin(win);
      });
      return win;
    },
    toggleMin(win) {
      win.state = win.state === "min" ? "normal" : "min";
      win.el.dataset.state = win.state;
      html3.classList.remove("gm-cwin-fullscreen");
      relayout();
    },
    toggleFull(win) {
      win.state = win.state === "full" ? "normal" : "full";
      win.el.dataset.state = win.state;
      html3.classList.toggle("gm-cwin-fullscreen", win.state === "full");
      relayout();
    },
    async close(win, sent = false) {
      if (!windows.includes(win) || win.closing) return;
      win.closing = true;
      const rc = frameRc(win);
      if (!sent && rc && isDirty(win)) {
        const choice = await new Promise((resolve) => {
          const content = document.createElement("p");
          content.className = "gm-dialog-text";
          content.textContent = label3("savemessage");
          const answer = (value) => (e, h) => {
            resolve(value);
            h.close();
          };
          modal.open({
            title: label3("compose"),
            content,
            buttons: [
              { text: label3("cancel"), click: answer("cancel") },
              { text: label3("discard"), click: answer("discard") },
              { text: label3("save"), mainaction: true, click: answer("save") }
            ],
            onClose: () => resolve("cancel")
          });
        });
        if (choice === "cancel") {
          win.closing = false;
          return;
        }
        if (choice === "save") {
          rc.command("savedraft");
          const saved = await new Promise((resolve) => {
            const t0 = Date.now();
            const poll = () => {
              if (!rc.busy && rc.env.draft_id && !isDirty(win)) resolve(true);
              else if (Date.now() - t0 > 15e3) resolve(false);
              else setTimeout(poll, 100);
            };
            setTimeout(poll, 300);
          });
          if (!saved) {
            win.closing = false;
            toast.show(label3("errorsaving"), { type: "error" });
            return;
          }
        } else {
          rc.compose_skip_unsavedcheck = true;
        }
      }
      windows.splice(windows.indexOf(win), 1);
      win.el.classList.remove("gm-visible");
      html3.classList.remove("gm-cwin-fullscreen");
      setTimeout(() => win.el.remove(), 180);
      relayout();
      if (sent && !rc?.env?.gm_discarded) toast.show(label3("messagesent"), { type: "confirmation" });
    },
    closeAll() {
      for (const w of [...windows]) this.close(w);
    }
  };

  // src/js/core-overrides.js
  var label4 = (n) => window.rcmail?.get_label ? rcmail.get_label(n) : n;
  function dialogShim(handle) {
    const shim = [handle.el];
    shim.dialog = (cmd, opts) => {
      if (cmd === "close" || cmd === "destroy") handle.close();
      else if (cmd === "option" && opts) handle.setOption(opts);
      else if (cmd === "isOpen") return !handle.closed;
      return shim;
    };
    shim.remove = () => handle.close();
    shim[0].jqref = () => shim;
    return shim;
  }
  function installCoreOverrides() {
    if (!window.rcmail) return;
    rcmail.show_popup_dialog = function(content, title, buttons, options = {}) {
      if (this.is_framed()) return parent.rcmail.show_popup_dialog(content, title, buttons, options);
      const btns = (buttons || []).map((b, i) => ({
        text: b.text === "*" ? "" : b.text,
        class: `${b.class || options.button_classes?.[i] || ""}${b.text === "*" ? " gm-btn-spacer" : ""}`,
        mainaction: /mainaction/.test(`${b.class || ""} ${options.button_classes?.[i] || ""}`),
        click: (e, h) => {
          const r = b.click?.call(shim[0], e);
          if (r !== false && !options.keep_open) h.close();
        }
      }));
      btns.sort((a, b) => Number(a.mainaction) - Number(b.mainaction));
      let node = content;
      if (typeof content === "string") node = content;
      else if (content && content.jquery) node = content[0];
      const handle = openDialog({
        title,
        content: node,
        buttons: btns,
        width: options.width,
        height: options.height,
        closeOnEscape: options.closeOnEscape !== false,
        dialogClass: options.dialogClass || options.classes?.["ui-dialog"] || "",
        onClose: () => options.close?.()
      });
      const shim = dialogShim(handle);
      if (options.open) setTimeout(() => options.open.call(shim[0]), 0);
      this.triggerEvent("dialog-open", { obj: shim });
      return shim;
    };
    const coreOpenWindow = rcmail.open_window;
    rcmail.open_window = function(url, small, toolbar) {
      const isCompose = /[?&]_action=compose(&|$)/.test(url || "");
      const smallScreen = layout.mode === "phone" || layout.mode === "small";
      if (isCompose && !this.env.extwin && !this.env.gm_popup && !smallScreen && !this.env.standard_windows_force) {
        return composeWindow.open(url);
      }
      return coreOpenWindow.call(this, url, small, toolbar);
    };
    if (!rcmail.env.extwin && !rcmail.env.framed && rcmail.env.task === "mail" && rcmail.env.action !== "compose") {
      rcmail.env.compose_extwin = true;
    }
    rcmail.show_menu = function(prop, show3, event) {
      const name = typeof prop === "object" ? prop.menu : prop;
      const menu = document.getElementById(name);
      if (!menu) {
        this.triggerEvent("menu-open", { name, obj: null, props: prop, originalEvent: event });
        return false;
      }
      const anchor = event?.target?.closest?.("a, button") || typeof prop === "object" && prop.obj || null;
      if (show3 === false || show3 === void 0 && popup.isOpen(name)) popup.close(name);
      else popup.open(name, anchor, event);
      return false;
    };
    rcmail.hide_menu = function(name) {
      popup.close(name);
      return false;
    };
    const origCheck = rcmail.check_compose_input;
    rcmail.check_compose_input = function(cmd) {
      if (!this.mailvelope_editor && this.editor && !this.editor.get_content() && !this.env.gm_nobody_ok) {
        modal.confirm(label4("nobodywarning")).then((ok) => {
          if (ok) {
            this.env.gm_nobody_ok = true;
            this.command(cmd);
            this.env.gm_nobody_ok = false;
          }
        });
        return false;
      }
      return origCheck.call(this, cmd);
    };
    const origToggle = rcmail.toggle_editor;
    rcmail.toggle_editor = function(props, obj, e) {
      if (props?.mode === "plain" && !this.env.editor_warned && this.editor?.get_content?.()) {
        modal.confirm(label4("editorwarning")).then((ok) => {
          if (ok) {
            this.env.editor_warned = true;
            origToggle.call(this, props, obj, e);
          } else if (obj?.tagName === "SELECT") obj.value = "html";
        });
        return false;
      }
      return origToggle.call(this, props, obj, e);
    };
    window.onbeforeunload = function() {
      return void 0;
    };
    window.alert = (m) => void modal.alert(String(m));
    window.confirm = () => {
      console.warn("window.confirm() is disabled in roundcube-gmail \u2014 use UI.modal.confirm()");
      return false;
    };
  }

  // src/js/recipients.js
  var ADDRESS = '(\\S+|("[^"]+"))@\\S+';
  var RX_ANGLE = new RegExp("(<" + ADDRESS + ">)");
  var RX_PLAIN = new RegExp("(" + ADDRESS + ")");
  var RX_TOKENS = /(?=\S)[^",;]*(?:"[^\\"]*(?:\\[,;\S][^\\"]*)*"[^",;]*)*/g;
  function parseRecipients(text) {
    text = text.replace(/[,;\s]*[\r\n]+/g, ",").trim();
    const recipients2 = [];
    for (const raw of text.match(RX_TOKENS) || []) {
      let str = raw;
      if (!str.length) continue;
      let m = RX_ANGLE.exec(str) || RX_PLAIN.exec(str);
      if (!m) continue;
      text = text.replace(raw, "");
      let email = m[1];
      while (str.length && str.indexOf(email) === 0) {
        recipients2.push({
          name: "",
          email: email.replace(/(^<|>$)/g, "").replace(/[^\p{L}\p{N}]$/u, "")
        });
        str = str.replace(email, "").trim();
        m = RX_ANGLE.exec(str) || RX_PLAIN.exec(str);
        if (!m) break;
        email = m[1];
      }
      if (m && str.length) {
        const name = str.replace(m[1], "").trim().replace(/^"|"$/g, "");
        recipients2.push({ name, email: m[1].replace(/(^<|>$)/g, "") });
      }
    }
    text = text.replace(/[,;]+/, ",").replace(/^[,;\s]+/, "");
    return { recipients: recipients2, text };
  }
  function formatRecipient(name, email) {
    if (!name) return email;
    const quoted = /[",;<>@]/.test(name) ? `"${name.replace(/\\/g, "\\\\").replace(/"/g, '\\"')}"` : name;
    return `${quoted} <${email}>`;
  }
  function recipientInput(orig) {
    if (orig.dataset.gmChips) return;
    orig.dataset.gmChips = "1";
    const rc = window.rcmail;
    const list = document.createElement("ul");
    list.className = "form-control recipient-input ac-input";
    const inputLi = document.createElement("li");
    inputLi.className = "input";
    const input = document.createElement("input");
    input.type = "text";
    input.spellcheck = false;
    input.autocomplete = "off";
    input.tabIndex = orig.tabIndex || 1;
    input.setAttribute("aria-label", orig.getAttribute("aria-label") || orig.id.replace(/^_/, ""));
    inputLi.append(input);
    list.append(inputLi);
    const apply2 = () => {
      const chips = [...list.querySelectorAll("li.recipient")].map((li) => li.dataset.recipient);
      const rest = input.value.trim();
      orig.value = chips.concat(rest ? [rest] : []).join(", ");
    };
    const insert = (name, email, replace) => {
      const li = document.createElement("li");
      li.className = "recipient";
      li.dataset.recipient = formatRecipient(name, email);
      li.title = name ? `${name} <${email}>` : email;
      li.innerHTML = `<span class="name">${escapeHtml(name || email)}</span><span class="email">${escapeHtml(name ? ` <${email}>` : "")},</span><a class="button icon remove" href="#remove" title="${escapeHtml(rc?.get_label?.("delete") || "Remove")}"></a>`;
      li.querySelector("a.remove").addEventListener("click", (e) => {
        e.preventDefault();
        li.remove();
        apply2();
        input.focus();
        rc && rc.compose_type_activity++;
      });
      li.querySelector(".name").addEventListener("dblclick", () => {
        li.remove();
        input.value = li.dataset.recipient + (input.value ? ", " + input.value : "");
        apply2();
        input.focus();
      });
      if (replace) replace.replaceWith(li);
      else inputLi.before(li);
      apply2();
    };
    const update = (text) => {
      text = (text ?? input.value).replace(/[,;\s]+$/, "");
      const result = parseRecipients(text);
      for (const r of result.recipients) insert(r.name, r.email);
      input.value = result.text;
      apply2();
      return result.recipients.length > 0;
    };
    const parse = (e, ac, trigger) => {
      if (trigger === false) return;
      let value = input.value;
      if (e?.type === "paste") {
        const paste = (e.clipboardData || window.clipboardData)?.getData("text") || "";
        value = value.substring(0, input.selectionStart) + paste + value.substring(input.selectionEnd);
        e.preventDefault();
      } else if (ac) {
        const last = list.querySelector("li.recipient:last-of-type");
        if (last && input.value.indexOf(last.dataset.recipient.replace(/[ ,]+$/, "")) > -1) {
          last.remove();
        }
      }
      update(value);
    };
    input.addEventListener("paste", parse);
    input.addEventListener("change", (e) => parse(e));
    input.addEventListener("keydown", (e) => {
      if (e.key === "Backspace" && !input.value.length) {
        list.querySelector("li.recipient:last-of-type")?.remove();
        apply2();
        e.preventDefault();
        return;
      }
      if (e.key === "," || e.key === ";" || e.key === "Enter" && !(rc && rc.ksearch_visible())) {
        if (update()) e.preventDefault();
      }
      if (e.key === "Tab") update();
    });
    input.addEventListener("blur", () => {
      list.classList.remove("focus");
      setTimeout(() => {
        if (!(rc && rc.ksearch_visible())) update();
      }, 150);
    });
    input.addEventListener("focus", () => list.classList.add("focus"));
    list.addEventListener("click", (e) => {
      if (!window.getSelection()?.toString().length && !e.target.closest("a")) input.focus();
    });
    Object.assign(orig.style, {
      position: "absolute",
      opacity: "0",
      left: "-5000px",
      width: "10px",
      height: "10px"
    });
    orig.tabIndex = -1;
    orig.setAttribute("aria-hidden", "true");
    orig.after(list);
    orig.addEventListener("focus", (e) => {
      e.preventDefault();
      input.focus();
    });
    orig.addEventListener("change", () => {
      list.querySelectorAll("li.recipient").forEach((li) => li.remove());
      input.value = orig.value;
      update();
    });
    if (orig.value) update(orig.value);
    if (rc) {
      const props = rc.env.autocomplete_threads > 0 ? { threads: rc.env.autocomplete_threads, sources: rc.env.autocomplete_sources } : void 0;
      input.addEventListener("keydown", (e) => {
        if (rc.ksearch_keydown(e, input, props) === false) e.preventDefault();
      });
      input.setAttribute("aria-autocomplete", "list");
      input.setAttribute("aria-expanded", "false");
      input.setAttribute("role", "combobox");
      const hide2 = (e) => {
        if (rc.ksearch_pane && e.target === rc.ksearch_pane.get?.(0)) return;
        rc.ksearch_hide();
      };
      document.addEventListener("click", hide2);
      document.addEventListener("scroll", hide2, true);
      rc.addEventListener("autocomplete_insert", (e) => {
        if (e.field === input) parse(null, true);
      });
    }
    return { list, input, insert, update, apply: apply2 };
  }
  var recipients = {
    parse: parseRecipients,
    attach: recipientInput,
    init(root = document) {
      for (const el2 of root.querySelectorAll("[data-recipient-input]")) recipientInput(el2);
    }
  };

  // src/js/compose.js
  var html4 = document.documentElement;
  var label5 = (n) => window.rcmail?.get_label ? rcmail.get_label(n) : n;
  var compose = {
    show_header(name) {
      const row2 = document.getElementById(`compose_${name}`);
      if (!row2) return false;
      row2.classList.remove("hidden");
      document.querySelector(`.gm-hlink[data-header="${name}"]`)?.classList.add("hidden");
      const input = row2.querySelector("ul.recipient-input input, input, textarea");
      input?.focus();
      return false;
    },
    header_reset(id) {
      const input = document.getElementById(id);
      const row2 = input?.closest(".gm-hrow");
      if (!input || !row2) return false;
      input.value = "";
      row2.querySelectorAll("ul.recipient-input > li.recipient").forEach((li) => li.remove());
      row2.querySelectorAll("ul.recipient-input input").forEach((i) => i.value = "");
      row2.classList.add("hidden");
      document.querySelector(`.gm-hlink[data-header="${id.replace(/^_/, "")}"]`)?.classList.remove("hidden");
      if (window.rcmail) rcmail.compose_type_activity++;
      return false;
    },
    discard() {
      const rc = window.rcmail;
      if (!rc) return false;
      const dirty2 = typeof rc.cmp_hash === "string" && rc.cmp_hash !== rc.compose_field_hash();
      const go = () => {
        rc.compose_skip_unsavedcheck = true;
        rc.env.gm_discarded = true;
        if (rc.env.extwin) window.close();
        rc.command("list");
      };
      if (dirty2) {
        modal.confirm(label5("notsentwarning"), { okLabel: label5("discard"), danger: true }).then((ok) => ok && go());
      } else go();
      return false;
    },
    toggle_toolbar() {
      html4.classList.toggle("gm-editor-toolbar-hidden");
      const ed = window.rcmail?.editor?.editor;
      if (ed && !window.rcmail.editor.is_html()) {
        window.rcmail.command("toggle-editor", { id: "composebody", html: true });
        html4.classList.remove("gm-editor-toolbar-hidden");
      }
      return false;
    },
    init() {
      const ssLabel = document.querySelector("#ss-inline-schedule .ss-label");
      if (ssLabel) ssLabel.textContent = ssLabel.textContent.replace(/^[^\p{L}\p{N}]+/u, "").trim();
      const rc = window.rcmail;
      if (!rc || rc.env.action !== "compose") return;
      const spellMenu = document.getElementById("spell-menu");
      if (spellMenu) {
        const list = document.createElement("ul");
        list.className = "menu listing";
        for (const [code, name] of Object.entries(rc.env.spell_langs || {})) {
          const item = document.createElement("li");
          const link = document.createElement("a");
          link.href = "#";
          link.className = "active";
          link.textContent = name;
          link.dataset.lang = code;
          link.addEventListener("click", (event) => {
            event.preventDefault();
            rc.spellcheck_lang_set(code);
            rc.hide_menu("spell-menu");
          });
          item.append(link);
          list.append(item);
        }
        spellMenu.replaceChildren(list);
        const trigger = document.querySelector('[data-popup="spell-menu"]');
        if (trigger && list.children.length) {
          trigger.classList.remove("disabled");
          trigger.parentElement.classList.remove("disabled");
        }
        rc.addEventListener("menu-open", ({ name }) => {
          if (name !== "spell-menu") return;
          const current2 = rc.spellcheck_lang();
          for (const link of list.querySelectorAll("a")) {
            const selected = link.dataset.lang === current2;
            link.classList.toggle("selected", selected);
            link.setAttribute("aria-selected", String(selected));
          }
        });
      }
      recipients.init();
      let dragDepth = 0;
      document.addEventListener("dragenter", (e) => {
        if (!e.dataTransfer?.types?.includes("Files")) return;
        dragDepth++;
        html4.classList.add("gm-dragging");
      });
      document.addEventListener("dragleave", () => {
        if (--dragDepth <= 0) dragDepth = 0, html4.classList.remove("gm-dragging");
      });
      document.addEventListener(
        "drop",
        () => (dragDepth = 0, html4.classList.remove("gm-dragging"))
      );
      for (const name of ["cc", "bcc", "replyto", "followupto"]) {
        const input = document.getElementById(`_${name}`);
        if (input && input.value.trim()) this.show_header(name);
      }
      rc.addEventListener("editor-init", (e) => {
        const c = e.config;
        c.menubar = false;
        c.statusbar = false;
        c.toolbar_location = "bottom";
        c.toolbar_mode = "sliding";
        c.toolbar = "undo redo | fontselect fontsizeselect | bold italic underline forecolor | alignleft aligncenter alignright | numlist bullist outdent indent | blockquote link image | removeformat";
        c.content_style = (c.content_style || "") + " body{font-family:Roboto Flex,Roboto,system-ui,sans-serif;font-size:14px;line-height:1.5;margin:8px 0}";
        if (html4.classList.contains("dark-mode")) c.skin = "oxide-dark";
      });
      const obs = new MutationObserver(() => {
        for (const name of ["cc", "bcc"]) {
          const row2 = document.getElementById(`compose_${name}`);
          document.querySelector(`.gm-hlink[data-header="${name}"]`)?.classList.toggle("hidden", row2 && !row2.classList.contains("hidden"));
        }
      });
      for (const name of ["cc", "bcc"]) {
        const row2 = document.getElementById(`compose_${name}`);
        if (row2) obs.observe(row2, { attributes: true, attributeFilter: ["class"] });
      }
    }
  };

  // src/js/smart-field.js
  function row(area, value, field, after) {
    const wrap = document.createElement("div");
    wrap.className = "input-group";
    const input = document.createElement("input");
    input.type = "text";
    input.className = "form-control";
    input.value = value;
    input.name = `${field.name}[]`;
    if (field.dataset.size) input.size = field.dataset.size;
    if (field.title) input.title = field.title;
    if (field.placeholder) input.placeholder = field.placeholder;
    const reset = document.createElement("a");
    reset.href = "#";
    reset.className = "icon reset input-group-text";
    reset.title = window.rcmail?.get_label?.("delete") || "Delete";
    reset.innerHTML = '<i class="ico ico-close" aria-hidden="true"></i>';
    wrap.append(input, reset);
    input.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        row(area, "", field, wrap).querySelector("input").focus();
      } else if ((e.key === "Backspace" || e.key === "Delete") && input.value === "" && area.children.length > 1) {
        e.preventDefault();
        const sib = wrap.previousElementSibling || wrap.nextElementSibling;
        wrap.remove();
        sib?.querySelector("input")?.focus();
      }
    });
    reset.addEventListener("click", (e) => {
      e.preventDefault();
      if (area.children.length > 1) {
        const sib = wrap.nextElementSibling || wrap.previousElementSibling;
        wrap.remove();
        sib?.querySelector("input")?.focus();
      } else {
        input.value = "";
        input.focus();
      }
    });
    for (const el2 of [input, reset]) {
      el2.addEventListener("focus", () => area.parentElement.classList.add("focused"));
      el2.addEventListener("blur", () => area.parentElement.classList.remove("focused"));
    }
    if (after) after.after(wrap);
    else area.append(wrap);
    return wrap;
  }
  function smartFieldInit(field) {
    if (!field || document.getElementById(`${field.id}_list`)) return;
    const area = document.createElement("div");
    area.className = "multi-input";
    area.id = `${field.id}_list`;
    const content = document.createElement("div");
    content.className = "content";
    const feedback = document.createElement("div");
    feedback.className = "invalid-feedback";
    area.append(content, feedback);
    const values = field.value ? field.value.split("\n") : [""];
    for (const v of values) row(content, v, field);
    if (field.disabled || field.dataset.hidden) area.hidden = true;
    field.disabled = true;
    field.classList.add("gm-smart-source");
    if (field.classList.contains("is-invalid")) {
      area.classList.add("is-invalid");
      feedback.textContent = field.dataset.errorMsg || "";
    }
    field.after(area);
  }
  function smartFieldReset(field, data = []) {
    const area = document.getElementById(`${field.id}_list`)?.querySelector(".content");
    if (!area) return;
    area.innerHTML = "";
    for (const v of data.length ? data : [""]) row(area, v, field);
  }

  // src/js/shim.js
  var label6 = (n) => window.rcmail?.get_label ? rcmail.get_label(n) : n;
  function buildUI() {
    const UI = {
      modal,
      popup,
      toast,
      theme,
      layout,
      controls,
      compose,
      composeWindow,
      // ---- template helpers ----
      toggle_nav() {
        layout.toggleNav();
        return false;
      },
      about_dialog(elem) {
        const rc = window.rcmail;
        rc.http_request("settings/about", {}, rc.set_busy(true, "loading"), "GET");
        fetch(`${rc.url("settings/about", { _framed: 1 })}`, { credentials: "same-origin" }).then((r) => r.text()).then((html9) => {
          const doc = new DOMParser().parseFromString(html9, "text/html");
          const body = doc.querySelector("#layout-content, body");
          const div = document.createElement("div");
          div.className = "gm-about";
          div.innerHTML = body ? body.innerHTML : html9;
          div.querySelectorAll("script, link, style").forEach((n) => n.remove());
          openDialog({
            title: label6("about"),
            content: div,
            buttons: [{ text: label6("close"), mainaction: true }]
          });
        }).finally(() => rc.set_busy(false));
        elem?.blur?.();
        return false;
      },
      // list selection mode (checkbox column) — same contract as Elastic
      toggle_list_selection(obj, list_id) {
        const list = document.getElementById(list_id);
        if (!list) return;
        const on = list.classList.toggle("withselection");
        obj?.classList.toggle("selected", on);
        const w = window.rcmail?.[list.dataset.list];
        if (w) w.checkbox_selection = on;
        return false;
      },
      // ---- plugin ABI (Elastic names) ----
      switch_nav_list(elem) {
        const target = elem?.getAttribute("data-target") || elem?.dataset.target;
        document.querySelectorAll("#layout-sidebar .listbox").forEach((l) => l.classList.toggle("hidden", target && l.id !== target));
        return false;
      },
      smart_field_init(field) {
        smartFieldInit(field);
      },
      smart_field_reset(field, data) {
        smartFieldReset(field, data);
      },
      form_errors(tips) {
        for (const t2 of tips || []) {
          const el2 = document.getElementById(t2[0]) || document.querySelector(`[name="${t2[0]}"]`);
          el2?.classList.add("is-invalid");
          el2?.setAttribute("title", t2[2] || t2[1] || "");
          el2?.addEventListener("input", () => el2.classList.remove("is-invalid"), { once: true });
        }
        if (tips?.length) {
          toast.show(tips[0][2] || tips[0][1] || label6("formincomplete"), { type: "error" });
        }
      },
      compose_status(id, status) {
        const el2 = document.getElementById(`compose-${id}`) || document.querySelector(`.gm-compose-status[data-status="${id}"]`);
        if (el2) el2.classList.toggle("active", !!status);
      },
      recipient_selector(field, opts = {}) {
        const rc = window.rcmail;
        const picker = document.getElementById("recipient-dialog");
        if (!rc || !picker || !rc.contact_list) return false;
        const parent2 = picker.parentNode;
        if (field) rc.env.focused_field = `#_${field}`;
        rc.contact_list.clear_selection();
        rc.contact_list.multiselect = opts.multiselect ?? true;
        picker.classList.remove("popupmenu");
        picker.style.display = "block";
        const close2 = (event) => {
          document.getElementById(`_${event.field}`)?.dispatchEvent(new Event("change"));
          handle.close();
        };
        const handle = openDialog({
          title: label6(opts.title || "insertcontact"),
          content: picker,
          local: true,
          dialogClass: "gm-dialog-lg gm-recipient-dialog",
          buttons: [
            { text: label6("cancel") },
            {
              text: label6(opts.button || "insert"),
              mainaction: true,
              click: () => {
                if (opts.action) {
                  opts.action();
                  handle.close();
                } else rc.command("add-recipient", field);
              }
            }
          ],
          onClose: () => {
            rc.removeEventListener("add-recipient", close2);
            parent2.append(picker);
            picker.classList.add("popupmenu");
            picker.style.display = "none";
            document.querySelector(opts.focus || rc.env.focused_field)?.focus();
          }
        });
        rc.addEventListener("add-recipient", close2);
        picker.querySelector("#directorylist a")?.click();
        return false;
      },
      header_reset(id) {
        return compose.header_reset(id);
      },
      // message view: "▾" toggles the detailed header table; "Headers" opens the raw headers
      headers_show(toggle) {
        const details = document.querySelector("#message-header .header-headers");
        const link = document.querySelector("#message-header a.headers-summary");
        if (!details) return false;
        const show3 = toggle === true ? !details.classList.contains("details") : !!toggle;
        details.classList.toggle("details", show3);
        link?.classList.toggle("expanded", show3);
        link?.setAttribute("aria-expanded", show3 ? "true" : "false");
        return false;
      },
      headers_dialog() {
        const rc = window.rcmail;
        if (!rc) return false;
        const box = document.createElement("div");
        box.className = "gm-headers-raw";
        box.textContent = rc.get_label("loading");
        const handle = openDialog({
          title: rc.get_label("allheaders") || "Headers",
          content: box,
          dialogClass: "gm-dialog-lg",
          buttons: [{ text: rc.get_label("close") }]
        });
        fetch(rc.url("headers", { _uid: rc.env.uid, _mbox: rc.env.mailbox, _framed: 1 }), {
          credentials: "same-origin"
        }).then((r) => r.text()).then((txt) => {
          const doc = new DOMParser().parseFromString(txt, "text/html");
          const src = doc.querySelector("#dialog-content, .dialog-content, .headers-raw") || doc.body;
          box.innerHTML = src.innerHTML.replace(/^\s+|\s+$/g, "");
          box.querySelectorAll("script, style").forEach((n) => n.remove());
        }).catch(() => {
          box.textContent = rc.get_label("errorloadingheaders") || "Could not load headers";
        });
        return handle ? false : false;
      },
      show_sidebar() {
        return false;
      },
      show_popup(name, show3, ev) {
        return window.rcmail?.show_menu(name, show3, ev);
      },
      escapeHtml,
      version: "0.1.0"
    };
    return UI;
  }

  // src/js/avatars.js
  var PALETTE = [
    "#0b57d0",
    "#146c2e",
    "#b3261e",
    "#7d4ea3",
    "#00639b",
    "#8a5a00",
    "#3c6e56",
    "#a8437c",
    "#5b5f97",
    "#0f766e"
  ];
  function colorFor(str) {
    let h = 0;
    for (const ch of String(str)) h = h * 31 + ch.charCodeAt(0) >>> 0;
    return PALETTE[h % PALETTE.length];
  }
  function initial(name) {
    const s = String(name || "").trim();
    const m = s.match(/[\p{L}\p{N}]/u);
    return m ? m[0].toUpperCase() : "?";
  }
  function apply(el2, name) {
    el2.textContent = initial(name);
    el2.style.background = colorFor(name);
  }
  var avatars = {
    colorFor,
    initial,
    apply,
    init() {
      for (const el2 of document.querySelectorAll(".gm-avatar-initial[data-username]")) {
        apply(el2, el2.dataset.username);
      }
    }
  };

  // src/js/sender.js
  var ANGLE = /^\s*(.*?)\s*<\s*([^<>\s]+@[^<>\s]+)\s*>\s*$/;
  function parseSender(el2) {
    if (!el2) return { name: "", email: "" };
    const text = (el2.textContent || "").trim();
    let email = (el2.getAttribute?.("title") || "").trim();
    let name = text;
    const m = text.match(ANGLE);
    if (m) {
      name = m[1] || m[2];
      email = email || m[2];
    } else if (!email && /^[^\s<>]+@[^\s<>]+$/.test(text)) {
      email = text;
    }
    if (!email) {
      const href = el2.getAttribute?.("href") || "";
      if (href.startsWith("mailto:")) email = decodeURIComponent(href.slice(7).split("?")[0]);
    }
    return { name: name || email, email: email.toLowerCase() };
  }

  // src/js/mail.js
  var html5 = document.documentElement;
  var label7 = (n) => window.rcmail?.get_label ? rcmail.get_label(n) : n;
  function onSmallScreen() {
    return layout.mode === "phone" || layout.mode === "small";
  }
  function showPanel(name) {
    if (name === "sidebar" && document.querySelector("#layout-menu > #layout-sidebar")) {
      html5.classList.add("nav-open");
      return;
    }
    if (!document.getElementById(`layout-${name}`)) name = "content";
    for (const id of ["layout-sidebar", "layout-list", "layout-content"]) {
      document.getElementById(id)?.classList.toggle("selected", id === `layout-${name}`);
    }
    html5.dataset.panel = name;
  }
  function rowAction(uid, action) {
    const rc = window.rcmail;
    const list = rc.message_list;
    if (!list) return;
    if (action === "read" || action === "unread") {
      rc.mark_message(action, uid);
      return;
    }
    list.select_row(uid, 0, false);
    if (action === "archive") {
      rc.command(rc.commands["plugin.archive"] !== void 0 ? "plugin.archive" : "delete");
    } else if (action === "snooze") rc.command("plugin.snoozed_messages.snooze");
    else rc.command(action);
  }
  function decorateRow(row2) {
    const el2 = row2?.obj;
    if (!el2 || el2.dataset.gm) return;
    el2.dataset.gm = "1";
    const subjectCell = el2.querySelector("td.subject");
    const flagsCell = el2.querySelector("td.flags");
    if (!subjectCell) return;
    const star = flagsCell?.querySelector("span.flag");
    if (star) subjectCell.prepend(star);
    const att = flagsCell?.querySelector("span.attachment");
    if (att) subjectCell.append(att);
    const from = subjectCell.querySelector(":scope > .fromto");
    if (from && !subjectCell.querySelector(".gm-avatar-initial")) {
      const { name, email } = parseSender(from.querySelector(".rcmContactAddress") || from);
      const a = document.createElement("span");
      a.className = "gm-avatar gm-avatar-sm gm-avatar-initial";
      apply(a, name);
      if (email) a.dataset.email = email;
      a.setAttribute("aria-hidden", "true");
      subjectCell.prepend(a);
    }
    const rc = window.rcmail;
    if (!html5.classList.contains("touch")) {
      const actions = document.createElement("span");
      actions.className = "gm-row-actions";
      const items = [
        rc.env.archive_folder || rc.commands?.["plugin.archive"] !== void 0 ? ["archive", "archive"] : null,
        ["delete", "delete"],
        [
          el2.classList.contains("unread") ? "read" : "unread",
          el2.classList.contains("unread") ? "markread" : "markunread"
        ],
        rc.commands?.["plugin.snoozed_messages.snooze"] !== void 0 ? ["snooze", "snooze"] : null
      ].filter(Boolean);
      for (const [cls, lab] of items) {
        const a = document.createElement("a");
        a.href = `#${cls}`;
        a.className = cls;
        a.title = label7(lab);
        a.setAttribute("aria-label", label7(lab));
        a.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          rowAction(row2.uid, a.className);
        });
        a.addEventListener("mousedown", (e) => e.stopPropagation());
        actions.append(a);
      }
      subjectCell.append(actions);
    }
  }
  function syncReadAction(uid) {
    const rc = window.rcmail;
    const row2 = rc.message_list?.rows?.[uid]?.obj;
    const a = row2?.querySelector(".gm-row-actions > a.read, .gm-row-actions > a.unread");
    if (!a) return;
    const unread = row2.classList.contains("unread");
    a.className = unread ? "read" : "unread";
    a.title = label7(unread ? "markread" : "markunread");
  }
  function openListOptions() {
    const rc = window.rcmail;
    const tpl = document.getElementById("listoptions-menu");
    if (!rc || !tpl) return false;
    const form = tpl.cloneNode(true);
    form.removeAttribute("id");
    form.className = "gm-listoptions propform";
    form.querySelector("h3.voice")?.remove();
    for (const wrap of form.querySelectorAll(".gm-select")) {
      const sel = wrap.querySelector("select");
      if (sel) {
        delete sel.dataset.gm;
        sel.classList.remove("gm-select-native");
        sel.removeAttribute("tabindex");
        sel.removeAttribute("aria-hidden");
        wrap.replaceWith(sel);
      }
    }
    for (const sel of form.querySelectorAll("select")) sel.id = `${sel.id}-dialog`;
    for (const l of form.querySelectorAll("label[for]")) l.htmlFor = `${l.htmlFor}-dialog`;
    const val = (name, v) => {
      const sel = form.querySelector(`select[name="${name}"]`);
      if (sel) sel.value = v;
    };
    val("sort_col", rc.env.sort_col || "");
    val("sort_ord", rc.env.sort_order || "ASC");
    val("mode", rc.env.threading ? "threads" : "list");
    enhance(form);
    openDialog({
      title: label7("listoptionstitle"),
      content: form,
      dialogClass: "gm-dialog-sm",
      buttons: [
        { text: label7("cancel") },
        {
          text: label7("save"),
          mainaction: true,
          click(e, handle) {
            const get = (name) => form.querySelector(`select[name="${name}"]`)?.value;
            rc.set_list_options(
              [],
              get("sort_col"),
              get("sort_ord"),
              get("mode") === "threads" ? 1 : 0
            );
            handle.close();
            document.getElementById("listmenulink")?.focus();
          }
        }
      ]
    });
    return false;
  }
  var AUTHRES = {
    status_pass: ["ico-verified-user", "ok"],
    status_partial_pass: ["ico-verified-user", "partial"],
    status_fail: ["ico-block", "fail"],
    status_warn: ["ico-warning", "warn"],
    status_nores: ["ico-help", "none"],
    status_nosig: ["ico-shield", "none"],
    status_third: ["ico-shield", "third"]
  };
  function replaceAuthresIcons(root = document) {
    for (const img of root.querySelectorAll("img.authres-status-img")) {
      const key = (img.getAttribute("src") || "").match(/(status_[a-z_]+)\.png/)?.[1];
      const [cls, state] = AUTHRES[key] || ["ico-help", "none"];
      const i = document.createElement("i");
      i.className = `ico gm-authres ${cls}`;
      i.dataset.state = state;
      i.title = img.title || img.alt || "";
      i.setAttribute("aria-label", img.alt || img.title || "");
      img.replaceWith(i);
    }
  }
  function buildSummary() {
    const header = document.querySelector("#message-header");
    const details = header?.querySelector(".header-headers");
    const summary = header?.querySelector(".header-summary");
    if (!header || !details || !summary || header.querySelector(".gm-msg-summary")) return;
    const rows = {};
    for (const tr of details.querySelectorAll("tr")) {
      const title = tr.querySelector(".header-title")?.textContent.trim().toLowerCase();
      const cell = tr.querySelector("td:not(.header-title)");
      if (title && cell) rows[title] = cell;
    }
    const fromCell = rows.from || rows.sender;
    const from = fromCell?.querySelector(".adr");
    if (!from) return;
    const { name, email } = parseSender(from.querySelector(".rcmContactAddress"));
    const date = rows.date?.textContent.trim() || summary.querySelector(".date")?.textContent.trim() || "";
    const who = (cell) => [...cell?.querySelectorAll(".rcmContactAddress") || []].map((a) => a.textContent.trim());
    const me = window.rcmail?.env?.identities ? Object.values(window.rcmail.env.identities).map((i) => i.email) : [];
    const toList = [...who(rows.to), ...who(rows.cc)];
    const toLabel = toList.length ? toList.map((t2) => me.includes(t2) ? "me" : t2.split("@")[0]).slice(0, 3).join(", ") + (toList.length > 3 ? ` +${toList.length - 3}` : "") : "";
    const el2 = document.createElement("div");
    el2.className = "gm-msg-summary";
    el2.innerHTML = `
    <div class="gm-msg-line1">
      <span class="gm-msg-name"></span>
      <span class="gm-msg-email"></span>
      <span class="gm-msg-flags"></span>
      <span class="gm-msg-date"></span>
    </div>
    <div class="gm-msg-line2">
      <button type="button" class="gm-msg-to" aria-expanded="false"></button>
    </div>`;
    el2.querySelector(".gm-msg-name").textContent = name;
    if (email && email !== name) el2.querySelector(".gm-msg-email").textContent = `<${email}>`;
    el2.querySelector(".gm-msg-date").textContent = date;
    for (const i of summary.querySelectorAll(".gm-authres, img.authres-status-img, img")) {
      el2.querySelector(".gm-msg-flags").append(i);
    }
    const toBtn = el2.querySelector(".gm-msg-to");
    toBtn.innerHTML = `<span>${toLabel ? `${label7("to")} ${toLabel}` : label7("details") || "Details"}</span><i class="ico ico-arrow-drop-down" aria-hidden="true"></i>`;
    toBtn.addEventListener("click", () => {
      const open3 = details.classList.toggle("details");
      toBtn.setAttribute("aria-expanded", open3 ? "true" : "false");
      header.querySelector("a.headers-summary")?.classList.toggle("expanded", open3);
    });
    summary.after(el2);
    summary.classList.add("gm-hidden");
    const add = fromCell.querySelector("a.rcmaddcontact");
    if (add) el2.querySelector(".gm-msg-email").after(add.cloneNode(true));
  }
  function decorateMessageHeader() {
    replaceAuthresIcons();
    buildSummary();
    const img = document.querySelector("#message-header img.contactphoto");
    if (!img) return;
    const { name, email: addr } = parseSender(
      document.querySelector("#message-header .header-summary .adr .rcmContactAddress") || document.querySelector("#message-header .header-summary .adr")
    );
    const fallback = document.createElement("span");
    fallback.className = "contactphoto gm-avatar-initial gm-msg-avatar";
    apply(fallback, name || "?");
    if (addr) fallback.dataset.email = addr;
    const isPlaceholder = () => /contactpic|contactgroup/.test(img.getAttribute("src") || "") || img.complete && img.naturalWidth === 0;
    const settle = () => {
      if (isPlaceholder()) {
        img.replaceWith(fallback);
      } else {
        img.classList.add("gm-loaded");
      }
    };
    if (img.complete) settle();
    else {
      img.addEventListener("load", settle, { once: true });
      img.addEventListener("error", () => img.replaceWith(fallback), { once: true });
    }
  }
  var mailUI = {
    showPanel,
    init() {
      const rc = window.rcmail;
      if (!rc || rc.env.task !== "mail") return;
      rc.addEventListener("insertrow", (e) => decorateRow(e.row));
      rc.addEventListener("menu-open", (p) => {
        if (p?.name === "messagelistmenu") return openListOptions();
        return void 0;
      });
      if (["show", "preview", "print"].includes(rc.env.action)) decorateMessageHeader();
      rc.addEventListener("set_unread_count", () => {
        for (const uid of Object.keys(rc.message_list?.rows || {})) syncReadAction(uid);
      });
      document.addEventListener("click", (e) => {
        const a = e.target.closest("a.back-list-button, a.back-sidebar-button, a.task-menu-button");
        if (!a) return;
        e.preventDefault();
        if (a.classList.contains("back-sidebar-button")) showPanel("sidebar");
        else if (a.classList.contains("task-menu-button")) html5.classList.toggle("nav-open");
        else showPanel("list");
      });
      rc.addEventListener("init", () => {
        const list = rc.message_list;
        if (list) {
          list.enable_checkbox_selection();
          document.getElementById("messagelist")?.classList.add("withselection");
          const selBtn = document.querySelector("#messagelist-header a.select");
          const syncSel = () => {
            const total = Object.keys(list.rows || {}).length;
            const n = list.get_selection().length;
            selBtn?.classList.toggle("gm-all", total > 0 && n === total);
            selBtn?.classList.toggle("gm-some", n > 0 && n < total);
            selBtn?.classList.toggle("disabled", total === 0);
            document.getElementById("layout-list")?.classList.toggle("gm-has-selection", n > 0);
            const count = document.querySelector(".gm-selection-count");
            if (count) count.textContent = String(n);
          };
          if (selBtn) {
            selBtn.removeAttribute("data-popup");
            selBtn.addEventListener("click", (e) => {
              e.preventDefault();
              e.stopPropagation();
              rc.command(list.get_selection().length ? "select-none" : "select-all", "page");
              syncSel();
            });
            const caret = document.createElement("a");
            caret.href = "#select-menu";
            caret.className = "select-caret";
            caret.dataset.popup = "listselect-menu";
            caret.title = selBtn.title;
            caret.innerHTML = '<span class="inner">\u25BE</span>';
            selBtn.after(caret);
          }
          list.addEventListener("select", (l) => {
            syncSel();
            if (onSmallScreen() && l.get_single_selection() && !l.multi_selecting && !rc.dummy_select && rc.env.layout !== "list") {
              showPanel("content");
            }
          });
          rc.addEventListener("listupdate", syncSel);
          rc.addEventListener("afterlist", syncSel);
        }
        if (rc.env.action === "show" && onSmallScreen()) showPanel("content");
        else if (onSmallScreen()) showPanel("list");
      });
      document.getElementById("mailboxlist")?.addEventListener("click", (e) => {
        if (onSmallScreen() && e.target.closest("a")) {
          html5.classList.remove("nav-open");
          showPanel("list");
        }
      });
      document.addEventListener("pointerdown", (e) => {
        if (html5.classList.contains("nav-open") && !e.target.closest("#layout-menu, #nav-toggle, .task-menu-button")) {
          html5.classList.remove("nav-open");
        }
      });
      document.addEventListener("gm:layout", () => {
        if (!onSmallScreen()) {
          for (const id of ["layout-sidebar", "layout-list", "layout-content"]) {
            document.getElementById(id)?.classList.add("selected");
          }
        } else {
          showPanel(rc.env.action === "show" || rc.env.action === "compose" ? "content" : "list");
        }
      });
    }
  };

  // src/js/search.js
  var search = {
    init() {
      const btn = document.querySelector(".gm-search .button.options");
      const panel = document.getElementById("searchmenu");
      if (!btn || !panel) return;
      const isOpen2 = () => !panel.classList.contains("hidden");
      const set = (open3) => {
        panel.classList.toggle("hidden", !open3);
        btn.classList.toggle("gm-active", open3);
        btn.setAttribute("aria-expanded", open3 ? "true" : "false");
        if (open3) panel.querySelector("input, select, button")?.focus({ preventScroll: true });
      };
      btn.setAttribute("aria-controls", "searchmenu");
      btn.setAttribute("aria-expanded", "false");
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        set(!isOpen2());
      });
      panel.querySelector(".formbuttons button.search")?.addEventListener("click", () => set(false));
      document.addEventListener("pointerdown", (e) => {
        if (isOpen2() && !e.target.closest("#searchmenu, .gm-search, .gm-select-list")) set(false);
      });
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && isOpen2()) {
          set(false);
          btn.focus();
        }
      });
      window.rcmail?.addEventListener("menu-close", (p) => {
        if (p?.name === "searchmenu") set(false);
      });
    }
  };

  // src/js/contacts.js
  var html6 = document.documentElement;
  var onSmallScreen2 = () => layout.mode === "phone" || layout.mode === "small";
  function showPanel2(name) {
    if (name === "sidebar" && document.querySelector("#layout-menu > #layout-sidebar")) {
      html6.classList.add("nav-open");
      return;
    }
    if (!document.getElementById(`layout-${name}`)) name = "content";
    for (const id of ["layout-sidebar", "layout-list", "layout-content"]) {
      document.getElementById(id)?.classList.toggle("selected", id === `layout-${name}`);
    }
    html6.dataset.panel = name;
  }
  function decorate(row2) {
    const td = row2.querySelector("td.name");
    if (!td || td.dataset.initial) return;
    const name = td.textContent.trim();
    td.dataset.initial = initial(name);
    td.style.setProperty("--gm-avatar-bg", colorFor(name));
  }
  var contactsUI = {
    init() {
      const rc = window.rcmail;
      if (!rc || rc.env.task !== "addressbook") return;
      const photo = document.querySelector("#contactpic.image-upload");
      const upload = document.querySelector("#upload-form");
      if (photo && upload) {
        const input = upload.querySelector('input[type="file"]');
        if (input) {
          upload.hidden = true;
          const choose = document.createElement("button");
          choose.type = "button";
          choose.className = "gm-photo-choose";
          choose.textContent = rc.gettext("addphoto");
          choose.addEventListener("click", () => input.click());
          photo.append(choose);
          const remove = document.createElement("button");
          remove.type = "button";
          remove.className = "btn gm-photo-remove";
          remove.textContent = rc.gettext("delete");
          remove.addEventListener("click", () => rc.command("delete-photo"));
          photo.closest(".contact-header").after(remove);
          const update = () => {
            const absent = document.getElementById("ff_photo")?.value === "-del-";
            choose.textContent = rc.gettext(absent ? "addphoto" : "replacephoto");
            remove.hidden = absent;
          };
          photo.querySelector("img")?.addEventListener("load", update);
          update();
        }
      }
      rc.addEventListener("insertrow", (e) => decorate(e.row.obj || e.row));
      document.querySelectorAll("#contacts-table tbody tr").forEach(decorate);
      document.addEventListener("click", (e) => {
        const a = e.target.closest("a.back-list-button, a.back-sidebar-button, a.task-menu-button");
        if (!a) return;
        e.preventDefault();
        if (a.classList.contains("back-sidebar-button")) showPanel2("sidebar");
        else if (a.classList.contains("task-menu-button")) html6.classList.toggle("nav-open");
        else showPanel2("list");
      });
      rc.addEventListener("init", () => {
        rc.contact_list?.addEventListener("select", (l) => {
          if (onSmallScreen2() && l.get_single_selection()) showPanel2("content");
        });
        if (onSmallScreen2()) showPanel2("list");
      });
      document.getElementById("directorylist")?.addEventListener("click", (e) => {
        if (onSmallScreen2() && e.target.closest("a")) {
          html6.classList.remove("nav-open");
          showPanel2("list");
        }
      });
      document.addEventListener("gm:layout", () => {
        if (!onSmallScreen2()) {
          for (const id of ["layout-sidebar", "layout-list", "layout-content"]) {
            document.getElementById(id)?.classList.add("selected");
          }
        }
      });
    }
  };

  // src/js/lists.js
  var ICONS = { messagelist: "ico-inbox", contactlist: "ico-contacts", folderlist: "ico-folder" };
  function emptyStateFor(list) {
    const info = document.createElement("div");
    info.className = "gm-list-empty hidden";
    const icon = Object.keys(ICONS).find((k) => list.classList.contains(k));
    info.innerHTML = `<i class="ico ${icon ? ICONS[icon] : "ico-description"}" aria-hidden="true"></i><span class="gm-list-empty-text"></span>`;
    list.after(info);
    let timer;
    const update = () => {
      info.classList.add("hidden");
      clearTimeout(timer);
      if (window.rcmail?.busy || !list.offsetParent) {
        timer = setTimeout(update, 250);
        return;
      }
      timer = setTimeout(() => {
        const body = list.tagName === "UL" ? list : list.tBodies[0] || list;
        const visible = [...body.children].some(
          (r) => r.offsetParent !== null || r.tagName === "TR" && r.style.display !== "none" && !r.hidden
        );
        let msg = list.dataset.labelMsg;
        if (!msg || visible) return;
        const ext = list.dataset.labelExt;
        const cmd = list.dataset.createCommand;
        if (ext && (!cmd || window.rcmail?.commands?.[cmd])) msg += ` ${ext}`;
        info.querySelector(".gm-list-empty-text").textContent = msg;
        info.classList.remove("hidden");
      }, 50);
    };
    new MutationObserver(update).observe(list, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ["style", "class"]
    });
    update();
  }
  var lists = {
    init() {
      document.querySelectorAll("ul[data-label-msg], table[data-label-msg]").forEach(emptyStateFor);
      const rc = window.rcmail;
      if (!rc) return;
      const html9 = document.documentElement;
      rc.addEventListener("requestlist", () => html9.classList.add("gm-list-loading"));
      rc.addEventListener("requestsearch", () => html9.classList.add("gm-list-loading"));
      for (const ev of ["responseafterlist", "responseaftersearch", "listupdate"]) {
        rc.addEventListener(ev, () => html9.classList.remove("gm-list-loading"));
      }
    }
  };

  // src/js/loading.js
  function watch(frame) {
    if (frame.dataset.gmLoad) return;
    frame.dataset.gmLoad = "1";
    const wrap = frame.parentElement;
    if (!wrap) return;
    const overlay = document.createElement("div");
    overlay.className = "gm-frame-loading";
    overlay.setAttribute("aria-hidden", "true");
    overlay.innerHTML = '<div class="gm-skel gm-skel-title"></div><div class="gm-skel gm-skel-row"></div><div class="gm-skel gm-skel-row short"></div><div class="gm-skel gm-skel-block"></div>';
    wrap.append(overlay);
    const isBlank = () => !frame.getAttribute("src") || /blank|_blank\.html$/.test(frame.getAttribute("src"));
    const show3 = () => {
      if (!isBlank()) wrap.classList.add("gm-loading");
    };
    const hide2 = () => wrap.classList.remove("gm-loading");
    new MutationObserver(show3).observe(frame, { attributes: true, attributeFilter: ["src"] });
    frame.addEventListener("load", hide2);
    frame.gmShowLoading = show3;
  }
  function hookLocationHref() {
    const rc = window.rcmail;
    if (!rc || rc.gm_location_hooked) return;
    rc.gm_location_hooked = true;
    const orig = rc.location_href;
    rc.location_href = function(url, target, frame) {
      if (frame && target && target !== window) {
        for (const f of document.querySelectorAll(".iframe-wrapper > iframe")) {
          if (f.contentWindow === target && !/_blank\.html|about:blank/.test(String(url))) {
            f.gmShowLoading?.();
          }
        }
      }
      return orig.call(this, url, target, frame);
    };
  }
  var loading = {
    watch,
    init() {
      document.querySelectorAll(".iframe-wrapper > iframe").forEach(watch);
      hookLocationHref();
    }
  };

  // src/js/datetime.js
  var html7 = document.documentElement;
  var open2 = null;
  var pad = (n) => String(n).padStart(2, "0");
  var lang = () => document.documentElement.lang || navigator.language || "en";
  var monthName = (d) => new Intl.DateTimeFormat(lang(), { month: "long", year: "numeric" }).format(d);
  var weekdays = () => {
    const first = firstDay();
    const fmt = new Intl.DateTimeFormat(lang(), { weekday: "narrow" });
    return Array.from({ length: 7 }, (_, i) => ({
      s: fmt.format(new Date(2024, 0, 7 + (first + i) % 7)),
      dow: (first + i) % 7
    }));
  };
  var firstDay = () => {
    const v = window.rcmail?.env?.first_day_of_week;
    return typeof v === "number" ? v : 1;
  };
  function formatJq(d, fmt) {
    return fmt.replace(/yy|y|mm|m|dd|d|MM|M|DD|D/g, (t2) => {
      switch (t2) {
        case "yy":
          return String(d.getFullYear());
        case "y":
          return String(d.getFullYear()).slice(-2);
        case "mm":
          return pad(d.getMonth() + 1);
        case "m":
          return String(d.getMonth() + 1);
        case "dd":
          return pad(d.getDate());
        case "d":
          return String(d.getDate());
        case "MM":
          return new Intl.DateTimeFormat(lang(), { month: "long" }).format(d);
        case "M":
          return new Intl.DateTimeFormat(lang(), { month: "short" }).format(d);
        case "DD":
          return new Intl.DateTimeFormat(lang(), { weekday: "long" }).format(d);
        case "D":
          return new Intl.DateTimeFormat(lang(), { weekday: "short" }).format(d);
        default:
          return t2;
      }
    });
  }
  function parseValue(v, kind) {
    if (!v) return null;
    if (kind === "datetime") {
      const m = v.match(/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})/);
      if (m) return new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]);
    }
    const iso = v.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
    if (iso) return new Date(+iso[1], +iso[2] - 1, +iso[3]);
    const dmy = v.match(/^(\d{1,2})[./-](\d{1,2})[./-](\d{4})/);
    if (dmy) return new Date(+dmy[3], +dmy[2] - 1, +dmy[1]);
    const t2 = Date.parse(v);
    return Number.isNaN(t2) ? null : new Date(t2);
  }
  function serialize(d, kind, jqFormat) {
    if (kind === "datetime") {
      return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }
    if (kind === "jq") return formatJq(d, jqFormat || "yy-mm-dd");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  }
  function display(d, kind) {
    if (!d) return "";
    const opts = { year: "numeric", month: "short", day: "numeric" };
    if (kind === "datetime") Object.assign(opts, { hour: "2-digit", minute: "2-digit" });
    return new Intl.DateTimeFormat(lang(), opts).format(d);
  }
  function label8(n, fallback) {
    const s = window.rcmail?.get_label?.(n);
    return s && s !== n ? s : fallback;
  }
  function closePicker() {
    if (!open2) return;
    open2.el.remove();
    open2.btn.setAttribute("aria-expanded", "false");
    open2 = null;
  }
  function openPicker(input, btn, kind, jqFormat) {
    closePicker();
    const now = /* @__PURE__ */ new Date();
    let value = parseValue(input.value, kind) || (kind === "datetime" ? new Date(now.getTime() + 60 * 60 * 1e3) : null);
    let view = new Date((value || now).getFullYear(), (value || now).getMonth(), 1);
    let hours = value ? value.getHours() : now.getHours();
    let minutes = value ? Math.round(value.getMinutes() / 5) * 5 : 0;
    const el2 = document.createElement("div");
    el2.className = "gm-dt gm-menu gm-open";
    el2.setAttribute("role", "dialog");
    el2.tabIndex = -1;
    document.body.append(el2);
    open2 = { el: el2, btn, input };
    const commit = (d) => {
      const out = new Date(
        d.getFullYear(),
        d.getMonth(),
        d.getDate(),
        kind === "datetime" ? hours : 0,
        kind === "datetime" ? minutes % 60 : 0
      );
      input.value = serialize(out, kind, jqFormat);
      input.dispatchEvent(new Event("input", { bubbles: true }));
      input.dispatchEvent(new Event("change", { bubbles: true }));
      btn.querySelector(".gm-dt-value").textContent = display(out, kind);
      btn.classList.remove("gm-dt-empty");
      closePicker();
      btn.focus();
    };
    const render = () => {
      const days = [];
      const first = new Date(view.getFullYear(), view.getMonth(), 1);
      const start = (first.getDay() - firstDay() + 7) % 7;
      const dim = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
      for (let i = 0; i < start; i++) days.push(null);
      for (let d = 1; d <= dim; d++) days.push(d);
      while (days.length % 7) days.push(null);
      const isSame = (d, ref) => ref && d === ref.getDate() && view.getMonth() === ref.getMonth() && view.getFullYear() === ref.getFullYear();
      el2.innerHTML = `
      <div class="gm-dt-head">
        <button type="button" class="gm-icon-btn gm-dt-prev" title="${label8("previous", "Previous")}"><i class="ico ico-chevron-left" aria-hidden="true"></i></button>
        <span class="gm-dt-month" aria-live="polite">${monthName(view)}</span>
        <button type="button" class="gm-icon-btn gm-dt-next" title="${label8("next", "Next")}"><i class="ico ico-chevron-right" aria-hidden="true"></i></button>
      </div>
      <div class="gm-dt-grid" role="grid">
        ${weekdays().map((w2) => `<span class="gm-dt-dow">${w2.s}</span>`).join("")}
        ${days.map((d) => d ? `<button type="button" class="gm-dt-day${isSame(d, now) ? " today" : ""}${isSame(d, value) ? " selected" : ""}" data-day="${d}">${d}</button>` : "<span></span>").join("")}
      </div>
      ${kind === "datetime" ? `
      <div class="gm-dt-time">
        <label class="gm-dt-field"><span>${label8("hours", "Hour")}</span><input type="text" inputmode="numeric" class="gm-dt-h" value="${pad(hours)}" maxlength="2" aria-label="${label8("hours", "Hour")}"></label>
        <span class="gm-dt-colon">:</span>
        <label class="gm-dt-field"><span>${label8("minutes", "Minute")}</span><input type="text" inputmode="numeric" class="gm-dt-m" value="${pad(minutes % 60)}" maxlength="2" aria-label="${label8("minutes", "Minute")}"></label>
      </div>` : ""}
      <div class="gm-dt-actions">
        <button type="button" class="gm-btn gm-btn-text gm-dt-clear">${label8("clear", "Clear")}</button>
        <span class="gm-dt-spacer"></span>
        <button type="button" class="gm-btn gm-btn-text gm-dt-today">${label8("today", "Today")}</button>
        ${kind === "datetime" ? `<button type="button" class="gm-btn gm-btn-filled gm-dt-ok">${label8("ok", "OK")}</button>` : ""}
      </div>`;
      el2.querySelector(".gm-dt-prev").onclick = () => {
        view = new Date(view.getFullYear(), view.getMonth() - 1, 1);
        render();
      };
      el2.querySelector(".gm-dt-next").onclick = () => {
        view = new Date(view.getFullYear(), view.getMonth() + 1, 1);
        render();
      };
      el2.querySelectorAll(".gm-dt-day").forEach((b) => {
        b.onclick = () => {
          const d = new Date(view.getFullYear(), view.getMonth(), +b.dataset.day);
          if (kind === "datetime") {
            value = d;
            render();
          } else commit(d);
        };
      });
      el2.querySelector(".gm-dt-clear").onclick = () => {
        input.value = "";
        input.dispatchEvent(new Event("change", { bubbles: true }));
        btn.querySelector(".gm-dt-value").textContent = btn.dataset.placeholder || "";
        btn.classList.add("gm-dt-empty");
        closePicker();
      };
      el2.querySelector(".gm-dt-today").onclick = () => {
        view = new Date(now.getFullYear(), now.getMonth(), 1);
        if (kind === "datetime") {
          value = new Date(now);
          render();
        } else commit(now);
      };
      el2.querySelector(".gm-dt-ok")?.addEventListener("click", () => commit(value || now));
      el2.querySelector(".gm-dt-h")?.addEventListener("input", (e) => {
        hours = Math.min(23, Math.max(0, parseInt(e.target.value, 10) || 0));
      });
      el2.querySelector(".gm-dt-m")?.addEventListener("input", (e) => {
        minutes = Math.min(59, Math.max(0, parseInt(e.target.value, 10) || 0));
      });
    };
    render();
    const r = btn.getBoundingClientRect();
    const w = el2.offsetWidth;
    const h = el2.offsetHeight;
    const left = Math.min(r.left, document.documentElement.clientWidth - w - 8);
    let top = r.bottom + 4;
    if (top + h > window.innerHeight - 8) top = Math.max(8, r.top - h - 4);
    el2.style.left = `${Math.max(8, left)}px`;
    el2.style.top = `${top}px`;
    btn.setAttribute("aria-expanded", "true");
    (el2.querySelector(".gm-dt-day.selected") || el2.querySelector(".gm-dt-day")).focus();
    el2.addEventListener("keydown", (e) => {
      const cur = e.target.closest(".gm-dt-day");
      const move = (delta) => {
        const all = [...el2.querySelectorAll(".gm-dt-day")];
        const i = all.indexOf(cur);
        const n = all[i + delta];
        if (n) n.focus();
        else {
          view = new Date(view.getFullYear(), view.getMonth() + (delta > 0 ? 1 : -1), 1);
          render();
          const list = el2.querySelectorAll(".gm-dt-day");
          (delta > 0 ? list[0] : list[list.length - 1]).focus();
        }
      };
      if (e.key === "Escape") {
        e.preventDefault();
        closePicker();
        btn.focus();
      } else if (cur && e.key === "ArrowRight") {
        e.preventDefault();
        move(1);
      } else if (cur && e.key === "ArrowLeft") {
        e.preventDefault();
        move(-1);
      } else if (cur && e.key === "ArrowDown") {
        e.preventDefault();
        move(7);
      } else if (cur && e.key === "ArrowUp") {
        e.preventDefault();
        move(-7);
      }
    });
  }
  function enhance2(input) {
    if (input.dataset.gmDt) return;
    input.dataset.gmDt = "1";
    const kind = input.type === "datetime-local" ? "datetime" : input.type === "date" ? "date" : "jq";
    const jqFormat = window.rcmail?.env?.date_format || "yy-mm-dd";
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "gm-dt-btn";
    btn.setAttribute("aria-haspopup", "dialog");
    btn.setAttribute("aria-expanded", "false");
    btn.dataset.placeholder = input.placeholder || input.title || label8(
      kind === "datetime" ? "date" : "date",
      kind === "datetime" ? "Pick date & time" : "Pick a date"
    );
    const v = parseValue(input.value, kind);
    btn.innerHTML = `<i class="ico ${kind === "datetime" ? "ico-schedule" : "ico-calendar"}" aria-hidden="true"></i><span class="gm-dt-value">${v ? display(v, kind) : btn.dataset.placeholder}</span>`;
    btn.classList.toggle("gm-dt-empty", !v);
    if (input.disabled) btn.disabled = true;
    input.classList.add("gm-dt-native");
    input.tabIndex = -1;
    input.setAttribute("aria-hidden", "true");
    input.after(btn);
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      if (open2?.btn === btn) closePicker();
      else openPicker(input, btn, kind, jqFormat);
    });
    input.addEventListener("change", () => {
      const d = parseValue(input.value, kind);
      btn.querySelector(".gm-dt-value").textContent = d ? display(d, kind) : btn.dataset.placeholder;
      btn.classList.toggle("gm-dt-empty", !d);
    });
  }
  var datetime = {
    enhance: enhance2,
    scan(root = document) {
      root.querySelectorAll('input[type="datetime-local"], input[type="date"], input.datepicker').forEach(enhance2);
    },
    init() {
      this.scan();
      const mo = new MutationObserver((muts) => {
        for (const m of muts) {
          for (const n of m.addedNodes) {
            if (n.nodeType === 1) this.scan(n.querySelectorAll ? n : document);
          }
        }
      });
      mo.observe(document.body, { childList: true, subtree: true });
      document.addEventListener("pointerdown", (e) => {
        if (open2 && !e.target.closest(".gm-dt, .gm-dt-btn")) closePicker();
      });
      window.addEventListener("resize", closePicker);
      window.addEventListener("blur", closePicker);
      html7.addEventListener("gm:popup-close", closePicker);
    }
  };

  // src/js/tooltip.js
  var tip;
  var showTimer;
  var current;
  function ensure() {
    if (tip) return tip;
    tip = document.createElement("div");
    tip.className = "gm-tooltip";
    tip.setAttribute("role", "tooltip");
    document.body.append(tip);
    return tip;
  }
  function textFor(el2) {
    if (el2.hasAttribute("title")) {
      const t2 = el2.getAttribute("title");
      el2.dataset.tip = t2;
      el2.removeAttribute("title");
      if (!el2.hasAttribute("aria-label") && !el2.textContent.trim()) el2.setAttribute("aria-label", t2);
    }
    return el2.dataset.tip || "";
  }
  function restore(el2) {
    if (el2 && el2.dataset.tip !== void 0 && !el2.hasAttribute("title")) {
      el2.setAttribute("title", el2.dataset.tip);
    }
  }
  function place2(el2) {
    const r = el2.getBoundingClientRect();
    const t2 = ensure();
    const w = t2.offsetWidth;
    const h = t2.offsetHeight;
    let left = r.left + r.width / 2 - w / 2;
    left = Math.max(8, Math.min(left, window.innerWidth - w - 8));
    let top = r.bottom + 8;
    if (top + h > window.innerHeight - 8) top = r.top - h - 8;
    t2.style.left = `${left}px`;
    t2.style.top = `${top}px`;
  }
  function show2(el2) {
    const text = textFor(el2);
    if (!text) return;
    current = el2;
    clearTimeout(showTimer);
    showTimer = setTimeout(() => {
      if (current !== el2 || !el2.isConnected) return;
      const t2 = ensure();
      t2.textContent = text;
      t2.classList.add("gm-visible");
      place2(el2);
    }, 320);
  }
  function hide() {
    clearTimeout(showTimer);
    restore(current);
    current = null;
    tip?.classList.remove("gm-visible");
  }
  var SELECTOR = "[title]:not(iframe):not(body):not(html), [data-tip]";
  var tooltip = {
    show: show2,
    hide,
    init() {
      const noHover = window.matchMedia?.("(hover: none)");
      document.addEventListener("pointerover", (e) => {
        if (e.pointerType === "touch" || noHover?.matches) return;
        const el2 = e.target.closest(SELECTOR);
        if (!el2 || el2 === current) return;
        if (el2.closest(".tox, .gm-tooltip")) return;
        if (/^(IFRAME|HTML|BODY|FORM|TABLE|TBODY|THEAD|SECTION|MAIN)$/.test(el2.tagName)) return;
        show2(el2);
      });
      document.addEventListener("pointerout", (e) => {
        if (current && !current.contains(e.relatedTarget)) hide();
      });
      document.addEventListener("pointerdown", hide, true);
      document.addEventListener("keydown", (e) => e.key === "Escape" && hide());
      document.addEventListener("focusin", (e) => {
        const el2 = e.target.closest(SELECTOR);
        if (el2 && document.documentElement.classList.contains("kbd")) show2(el2);
      });
      document.addEventListener("focusout", hide);
      window.addEventListener("scroll", hide, true);
    }
  };

  // src/js/contact-dialog.js
  var label9 = (n, fb) => {
    const s = window.rcmail?.get_label?.(n);
    return s && s !== n ? s : fb;
  };
  function parseAddress(value) {
    const m = String(value || "").match(/^\s*"?([^"<]*?)"?\s*<([^>]+)>\s*$/);
    if (m) return { name: m[1].trim(), email: m[2].trim() };
    return { name: "", email: String(value || "").trim() };
  }
  function splitName(name) {
    const parts = name.split(/\s+/).filter(Boolean);
    if (parts.length < 2) return { first: name, last: "" };
    return { first: parts.slice(0, -1).join(" "), last: parts[parts.length - 1] };
  }
  function openAddContact(value, source) {
    const rc = window.rcmail;
    const { name, email } = parseAddress(value);
    const frame = document.createElement("iframe");
    frame.className = "gm-picker-frame";
    frame.title = label9("addcontact", "Add contact");
    const params = { _framed: 1, _gm_dialog: 1 };
    if (source) params._source = source;
    frame.src = rc.url("addressbook/add", params);
    let saved = false;
    const handle = openDialog({
      title: label9("addcontact", "Add contact"),
      content: frame,
      dialogClass: "gm-dialog-lg gm-contact-dialog",
      buttons: [
        { text: label9("cancel", "Cancel") },
        {
          text: label9("save", "Save"),
          mainaction: true,
          click() {
            frame.contentWindow?.rcmail?.command("save");
          }
        }
      ]
    });
    frame.addEventListener("load", () => {
      let w;
      try {
        w = frame.contentWindow;
      } catch {
        return;
      }
      const url = String(w.location.href);
      if (/_action=show/.test(url) || saved && /_action=add/.test(url) === false) {
        handle.close();
        toast.show(label9("addedsuccessfully", "Contact added"), { type: "confirmation" });
        rc.triggerEvent("gm:contact-added", { email });
        return;
      }
      w.document.documentElement.classList.add("gm-in-dialog");
      const set = (id, v) => {
        const el2 = w.document.getElementById(id);
        if (el2 && v && !el2.value) {
          el2.value = v;
          el2.dispatchEvent(new Event("input", { bubbles: true }));
        }
      };
      const { first, last } = splitName(name);
      set("ff_firstname", first);
      set("ff_surname", last);
      set("ff_email0", email);
      (w.document.getElementById("ff_firstname") || w.document.getElementById("ff_email0"))?.focus();
      w.rcmail?.addEventListener("beforesave", () => {
        saved = true;
      });
    });
    return handle;
  }
  var contactDialog = {
    init() {
      const rc = window.rcmail;
      if (!rc || rc.env.framed || rc.env.extwin) return;
      rc.add_contact = function(value, reload, source) {
        if (!value) return;
        openAddContact(value, source);
      };
    }
  };

  // src/js/fab.js
  var fab = {
    init() {
      const src = document.querySelector(
        "#layout-content .toolbar a[data-fab], #layout-content .toolbar a.create"
      );
      const list = document.getElementById("layout-list");
      if (!src || !list || document.querySelector(".gm-fab")) return;
      const btn = document.createElement("a");
      btn.href = "#";
      btn.className = "gm-fab";
      btn.setAttribute("role", "button");
      btn.setAttribute("aria-label", src.title || src.textContent.trim());
      btn.dataset.tip = src.title || src.textContent.trim();
      btn.innerHTML = '<i class="ico ico-add" aria-hidden="true"></i>';
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        src.click();
        if (layout.mode === "phone" || layout.mode === "small") {
          document.getElementById("layout-content")?.classList.add("selected");
          list.classList.remove("selected");
        }
      });
      const sync = () => btn.classList.toggle("disabled", src.classList.contains("disabled"));
      new MutationObserver(sync).observe(src, { attributes: true, attributeFilter: ["class"] });
      sync();
      list.append(btn);
    }
  };

  // src/js/ai.js
  var html8 = document.documentElement;
  var seq = 0;
  var pending = /* @__PURE__ */ new Map();
  var t = (n) => window.rcmail?.get_label?.(n, "gm_core") || n;
  function request(op, data = {}) {
    const rc = window.rcmail;
    const reqid = `ai${++seq}`;
    return new Promise((resolve, reject) => {
      pending.set(reqid, { resolve, reject });
      rc.http_post(
        "plugin.gm_core.ai",
        { _op: op, _reqid: reqid, _lang: rc.env.locale || "", ...data },
        rc.set_busy(true, "gm_core.aiworking")
      );
      setTimeout(() => {
        if (pending.has(reqid)) {
          pending.delete(reqid);
          reject(new Error("timeout"));
        }
      }, 9e4);
    });
  }
  function onResult(p) {
    const w = pending.get(p.reqid);
    if (!w) return;
    pending.delete(p.reqid);
    if (p.error) w.reject(new Error(p.error));
    else w.resolve(p.text || "");
  }
  function editorText(selectionOnly) {
    const rc = window.rcmail;
    const ed = rc?.editor;
    if (!ed) return "";
    if (ed.is_html?.()) {
      const tm = window.tinymce?.get(ed.id);
      if (!tm) return "";
      if (selectionOnly) {
        const sel = tm.selection.getContent({ format: "text" });
        if (sel.trim()) return sel;
      }
      return tm.getContent({ format: "text" });
    }
    const ta = document.getElementById(ed.id);
    if (!ta) return "";
    if (selectionOnly && ta.selectionStart !== ta.selectionEnd) {
      return ta.value.slice(ta.selectionStart, ta.selectionEnd);
    }
    return ta.value;
  }
  function editorInsert(text, mode) {
    const rc = window.rcmail;
    const ed = rc?.editor;
    if (!ed) return;
    if (ed.is_html?.()) {
      const tm = window.tinymce?.get(ed.id);
      if (!tm) return;
      const htmlText = escapeHtml(text).split(/\n{2,}/).map((para) => `<p>${para.replace(/\n/g, "<br>")}</p>`).join("");
      if (mode === "replace") tm.setContent(htmlText);
      else tm.execCommand("mceInsertContent", false, htmlText);
      tm.focus();
    } else {
      const ta = document.getElementById(ed.id);
      if (!ta) return;
      if (mode === "replace") ta.value = text;
      else {
        const s = ta.selectionStart ?? ta.value.length;
        const e = ta.selectionEnd ?? s;
        ta.value = ta.value.slice(0, s) + text + ta.value.slice(e);
      }
      ta.focus();
    }
    rc.compose_type_activity++;
  }
  function resultDialog(op, text, rerun) {
    const box = document.createElement("div");
    box.className = "gm-ai-result";
    box.innerHTML = `<div class="gm-ai-text"></div><div class="gm-ai-note"><i class="ico ico-ai" aria-hidden="true"></i>${escapeHtml(t("aidisclaimer"))}</div>`;
    box.querySelector(".gm-ai-text").textContent = text;
    const buttons = [
      { text: t("regenerate"), click: () => rerun() },
      {
        text: t("copy"),
        click: () => navigator.clipboard?.writeText(text).then(() => toast.show(t("copy") + " \u2713", { type: "confirmation" }))
      }
    ];
    if (op !== "summarize") {
      buttons.push({
        text: t("replace"),
        click: (e, h) => {
          editorInsert(text, "replace");
          h.close();
        }
      });
      buttons.push({
        text: t("insert"),
        mainaction: true,
        click: (e, h) => {
          editorInsert(text, "insert");
          h.close();
        }
      });
    } else buttons.push({ text: window.rcmail.get_label("close"), mainaction: true });
    openDialog({ title: t(op), content: box, dialogClass: "gm-dialog-lg gm-ai-dialog", buttons });
  }
  async function runComposeOp(op) {
    const rc = window.rcmail;
    let prompt = "";
    if (op === "write" || op === "translate" || op === "reply") {
      prompt = await modal.prompt(op === "translate" ? t("translate") : t("aiprompt"), {
        title: t(op),
        placeholder: op === "translate" ? "English" : ""
      });
      if (prompt === null) return;
    }
    const selectionOnly = [
      "improve",
      "shorten",
      "expand",
      "fix",
      "translate",
      "formal",
      "friendly"
    ].includes(op);
    const text = editorText(selectionOnly);
    if (!text.trim() && op !== "write") {
      toast.show(t("aiempty"), { type: "warning" });
      return;
    }
    const subject = document.getElementById("compose-subject")?.value || "";
    const go = () => request(op, { _text: text, _subject: subject, _prompt: prompt }).then((out) => resultDialog(op, out, go)).catch(
      (e) => toast.show(e.message === "timeout" ? t("aierror") : e.message, { type: "error" })
    );
    go();
    void rc;
  }
  function initCompose() {
    const tools = document.querySelector(".gm-compose-tools");
    if (!tools) return;
    const ops = (window.rcmail.env.gm_ai?.ops || []).filter((o) => o !== "summarize");
    if (!ops.length) return;
    const menu = document.createElement("div");
    menu.id = "ai-menu";
    menu.className = "popupmenu gm-menu";
    menu.innerHTML = `<h3 class="voice">${escapeHtml(t("aiassistant"))}</h3><ul class="menu listing" role="menu">${ops.map(
      (o) => `<li role="menuitem"><a href="#${o}" class="ai-op ai-${o}" data-op="${o}">${escapeHtml(t(o))}</a></li>`
    ).join("")}</ul>`;
    document.body.append(menu);
    menu.addEventListener("click", (e) => {
      const a = e.target.closest("a.ai-op");
      if (!a) return;
      e.preventDefault();
      popup.closeAll();
      runComposeOp(a.dataset.op);
    });
    const rc = window.rcmail;
    const btn = document.createElement("a");
    btn.href = "#ai";
    btn.id = "gm-ai-button";
    btn.className = "ai";
    btn.title = t("aiassistant");
    btn.setAttribute("role", "button");
    btn.innerHTML = `<span class="inner">${escapeHtml(t("ai"))}</span>`;
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!btn.classList.contains("disabled")) popup.open("ai-menu", btn, e);
    });
    tools.insertBefore(btn, tools.querySelector("a.more") || null);
    rc.register_command("plugin.gm-ai", (props, obj, e) => popup.open("ai-menu", btn, e), true);
    if (Array.isArray(rc.env.compose_commands) && !rc.env.compose_commands.includes("plugin.gm-ai")) {
      rc.env.compose_commands.push("plugin.gm-ai");
    }
    rc.register_button(
      "plugin.gm-ai",
      "gm-ai-button",
      "link",
      "ai active",
      "ai active selected",
      "ai active"
    );
    rc.addEventListener("init", () => rc.enable_command("plugin.gm-ai", true));
  }
  function initMessage() {
    const links = document.querySelector("#message-header .header-links");
    const body = document.getElementById("messagebody");
    if (!links || !body || !(window.rcmail.env.gm_ai?.ops || []).includes("summarize")) return;
    const a = document.createElement("a");
    a.href = "#summarize";
    a.className = "ai-summarize";
    a.textContent = t("summarize");
    links.prepend(a);
    a.addEventListener("click", (e) => {
      e.preventDefault();
      const existing = document.querySelector(".gm-ai-card");
      if (existing) {
        existing.remove();
        return;
      }
      const text = body.innerText.slice(0, 24e3);
      const subjectNode = document.querySelector("#message-header h2.subject")?.cloneNode(true);
      subjectNode?.querySelectorAll("script, style, .voice, a, button").forEach((n) => n.remove());
      const subject = subjectNode?.textContent.replace(/\s+/g, " ").trim() || "";
      request("summarize", { _text: text, _subject: subject }).then((out) => {
        const card = document.createElement("div");
        card.className = "gm-ai-card";
        card.innerHTML = `<div class="gm-ai-card-head"><i class="ico ico-ai" aria-hidden="true"></i><span>${escapeHtml(t("summary"))}</span><button type="button" class="gm-icon-btn gm-ai-card-close" title="${escapeHtml(window.rcmail.get_label("close"))}"><i class="ico ico-close" aria-hidden="true"></i></button></div><div class="gm-ai-card-text"></div><div class="gm-ai-note">${escapeHtml(t("aidisclaimer"))}</div>`;
        card.querySelector(".gm-ai-card-text").textContent = out;
        card.querySelector(".gm-ai-card-close").addEventListener("click", () => card.remove());
        body.parentElement.insertBefore(card, body);
      }).catch(
        (err) => toast.show(err.message === "timeout" ? t("aierror") : err.message, { type: "error" })
      );
    });
  }
  var ai = {
    init() {
      const rc = window.rcmail;
      if (!rc?.env?.gm_ai?.enabled) return;
      html8.classList.add("gm-ai");
      rc.addEventListener("plugin.gm_core.ai_result", onResult);
      if (rc.env.task === "mail" && rc.env.action === "compose") initCompose();
      if (rc.env.task === "mail" && ["show", "preview"].includes(rc.env.action)) initMessage();
    }
  };

  // src/js/apps.js
  var apps = {
    init() {
      const list = window.rcmail?.env?.gm_apps;
      const ul = document.querySelector("#apps-menu .gm-apps-card .menu.listing");
      if (!Array.isArray(list) || !list.length || !ul) return;
      for (const app of list) {
        const li = document.createElement("li");
        li.setAttribute("role", "menuitem");
        const a = document.createElement("a");
        a.href = app.url;
        a.target = app.target || "_blank";
        if (a.target === "_blank") a.rel = "noopener";
        a.className = "gm-app-ext";
        a.innerHTML = `<i class="ico ico-${escapeHtml(app.icon || "language")}" aria-hidden="true"></i>${escapeHtml(app.label)}`;
        if (app.color) a.style.setProperty("--gm-app-color", app.color);
        li.append(a);
        ul.append(li);
      }
    }
  };

  // src/js/avatar-images.js
  var seen = /* @__PURE__ */ new Map();
  function endpoint() {
    return window.rcmail?.env?.gm_avatars?.url || null;
  }
  function probe(email) {
    const url = endpoint();
    if (!url) return Promise.resolve(null);
    if (seen.has(email)) {
      return Promise.resolve(
        seen.get(email) === "ok" ? `${url}&_email=${encodeURIComponent(email)}` : null
      );
    }
    const src = `${url}&_email=${encodeURIComponent(email)}`;
    const p = new Promise((resolve) => {
      const img = new Image();
      img.onload = () => {
        seen.set(email, "ok");
        resolve(src);
      };
      img.onerror = () => {
        seen.set(email, "miss");
        resolve(null);
      };
      img.src = src;
    });
    seen.set(email, p);
    return p;
  }
  function upgrade(el2, email) {
    if (!el2 || !email || el2.dataset.gmImg) return;
    el2.dataset.gmImg = "1";
    probe(email.toLowerCase()).then((src) => {
      if (!src || !el2.isConnected) return;
      const img = document.createElement("img");
      img.className = "gm-avatar-img";
      img.alt = "";
      img.src = src;
      img.loading = "lazy";
      el2.textContent = "";
      el2.style.background = "transparent";
      el2.append(img);
    });
  }
  var avatarImages = {
    upgrade,
    // observe letter avatars that carry data-email (mail.js / contacts.js set it)
    init() {
      if (!endpoint()) return;
      const scan = (root) => root.querySelectorAll?.(".gm-avatar-initial[data-email]").forEach((el2) => upgrade(el2, el2.dataset.email));
      scan(document);
      new MutationObserver((muts) => {
        for (const m of muts) {
          if (m.type === "attributes") {
            delete m.target.dataset.gmImg;
            upgrade(m.target, m.target.dataset.email);
            continue;
          }
          for (const n of m.addedNodes) if (n.nodeType === 1) scan(n);
        }
      }).observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ["data-email"]
      });
    }
  };

  // src/js/index.js
  window.UI = buildUI();
  layout.init();
  theme.init();
  popup.init();
  installCoreOverrides();
  unload.init();
  var boot = () => {
    toast.init();
    controls.init();
    avatars.init();
    mailUI.init();
    compose.init();
    search.init();
    contactsUI.init();
    lists.init();
    loading.init();
    datetime.init();
    tooltip.init();
    contactDialog.init();
    fab.init();
    ai.init();
    apps.init();
    avatarImages.init();
  };
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", boot);
  else boot();
})();
//# sourceMappingURL=ui.js.map
