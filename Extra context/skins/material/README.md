# Material Design Skin for Roundcube (`material`)

A modern, advanced UI Material Design 3 (Material You) skin for Roundcube Webmail extending `elastic`.

---

## Highlights & Features

- **Material 3 Design System**: Built on Google Material Design 3 guidelines featuring elevation levels, smooth radii, tonal surfaces, and responsive micro-interactions.
- **Perfect Light & Dark Themes**:
  - **Light Mode**: Clean `#ffffff` / `#f8f9fa` surface cards, soft `#f0f4f9` canvas, crisp high-contrast text.
  - **Dark Mode**: Deep `#121316` / `#1e1f23` OLED surfaces, elevated `#282a2f` containers, WCAG AA contrast compliance.
- **Outline Material Design Icons**: Full integration of stroke-based, unfilled **Material Symbols Outlined** across the entire UI (toolbar, sidebar, mailbox folders, message list indicators, and modal dialogs).
- **Customizable Button Colors**:
  - Full user settings control over primary button background (`--md-btn-primary-bg`), secondary button background (`--md-btn-secondary-bg`), and corner radius (`--md-btn-radius`).
  - Real-time live preview in Settings preferences.
- **Floating Action Button (FAB)**: Modern Material FAB compose button with elevation depth and ripple animation.
- **Fully Offline Ready**: Self-hosts `material-symbols-outlined.woff2` with automatic Google Fonts CDN fallback.

---

## Directory Structure

```
Extra context/skins/material/
├── assets/
│   ├── fonts/
│   │   └── material-symbols-outlined.woff2
│   ├── images/
│   │   └── logo_header.png
│   ├── scripts/
│   │   └── material.js
│   └── styles/
│       ├── _buttons.scss
│       ├── _dark.scss
│       ├── _icons.scss
│       ├── _variables.scss
│       └── styles.css
├── config.inc.php.sample
├── meta.json
├── README.md
└── thumbnail.png
```

---

## Installation & Activation

### Via the Extra Content Installer:

```bash
php bin/install-extra.php --activate --skin=material
```

Or deploy directly to your Roundcube `skins/` directory and configure in `config/config.inc.php`:

```php
$config['skin'] = 'material';
```

---

## License

MIT License. Designed by Webdotpulse & LifePrisma AI Contributors.
