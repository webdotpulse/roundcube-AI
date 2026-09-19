# Roundcube Loader Plugin

A premium, Gmail-style full-page loading screen and animated progress bar for Roundcube Webmail during login and application startup.

![Gmail Loader Style](assets/gmail_logo.svg)

## Features

- **Iconic Gmail Aesthetics**: Authentic Google/Gmail look with high-resolution vector branding, crisp progress bar, and "Standard View" footer typography.
- **Immediate Login Feedback**: Intercepts login form submissions to display the loader instantly with the active username ("Loading user@domain..."), preventing duplicate submissions and blank screens during IMAP authentication.
- **Zero Layout Shift (FOUC-Free)**: Injected directly at the opening `<body>` tag with critical CSS for instantaneous rendering before external assets finish downloading.
- **Automatic Dark Mode**: Seamlessly adapts to dark mode (`html.dark-mode`, Roundcube `gmail_plus` skin dark styles, or system `prefers-color-scheme`).
- **Brand Theming**: Supports classic Gmail Red (`#d93025`), modern Google 4-color gradient, Roundcube Blue, or custom branding.
- **Stall Protection**: Built-in fallback prompt that appears if backend authentication or network connection stalls, allowing one-click retry.
- **Composer Auto-Installed**: Fully integrated with Roundcube plugin installer and the companion installer script (`bin/install-extra.php`).

## Requirements

- Roundcube: 1.4, 1.5, 1.6, 1.7+
- PHP: 7.4 or higher (fully compatible with PHP 8.0 - 8.4+)
- Skins: `gmail_plus`, `elastic`, `larry`, or any custom responsive skin

## Installation

### Automatic via Composer (Recommended)

When installing `roundcube-ai` or running composer in your Roundcube root:
```bash
composer install
```
The plugin is automatically copied to `plugins/roundcube_loader`, default configuration is initialized, and `'roundcube_loader'` is enabled in `config/config.inc.php`.

### Manual Installation

1. Copy the `roundcube_loader` folder into your Roundcube `plugins/` directory:
   ```bash
   cp -r "Extra context/plugins/roundcube_loader" /path/to/roundcube/plugins/
   ```

2. Enable the plugin in your Roundcube configuration (`config/config.inc.php`):
   ```php
   $config['plugins'] = [
       // ... other plugins ...
       'roundcube_loader',
   ];
   ```

3. (Optional) Initialize and customize settings:
   ```bash
   cp plugins/roundcube_loader/config.inc.php.dist plugins/roundcube_loader/config.inc.php
   ```

## Configuration Options

Edit `config.inc.php` within the plugin directory or set overrides in Roundcube's main `config/config.inc.php`:

| Option | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `roundcube_loader_enabled` | bool | `true` | Enable or disable the plugin globally |
| `roundcube_loader_theme` | string | `'gmail'` | Visual theme: `'gmail'`, `'google_gradient'`, or `'roundcube'` |
| `roundcube_loader_logo_type` | string | `'gmail'` | Logo type: `'gmail'`, `'roundcube'`, or `'custom'` |
| `roundcube_loader_custom_logo` | string | `''` | URL or relative path to a custom logo image/SVG |
| `roundcube_loader_bar_color` | string\|null | `null` | Custom hex color for the loading bar (e.g. `'#ea4335'`) |
| `roundcube_loader_show_username` | bool | `true` | Show username on login loading screen ("Loading user@domain...") |
| `roundcube_loader_show_subtext` | bool | `true` | Show "Standard View" footer subtext below loading bar |
| `roundcube_loader_splash_on_login` | bool | `true` | Show full loader immediately when submitting login form |
| `roundcube_loader_splash_on_startup`| bool | `true` | Show progress bar during initial application mailbox load |
| `roundcube_loader_timeout` | int | `15` | Seconds before offering "Taking longer than usual? Click to reload" |

## User Preferences

Users can also customize or toggle the loader via **Settings** > **Preferences** > **User Interface** > **Appearance**.

## License

GNU General Public License v3 or later (GPL-3.0-or-later).
