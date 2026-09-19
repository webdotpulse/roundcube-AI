# Roundcube Customizr

Plugin to customize logo, watermark image, favicon, stylesheets, and inline CSS in Roundcube Webmail — either globally via configuration files or individually per-user directly within the **Settings** interface.

---

## Features

- **Custom Watermark Image**: Replace the default empty preview watermark with your own branding image.
- **Custom Watermark Page**: Display a custom HTML page instead of the empty preview frame.
- **Custom Favicon**: Replace the browser tab favicon or remove it.
- **Custom Logo**: Set a custom application logo image.
- **Custom External Stylesheet**: Add an external CSS stylesheet to every page.
- **Custom Inline CSS**: Inject custom CSS rules directly without needing to host an external file.
- **Settings UI**: Users can configure their visual appearance under **Settings > Preferences > Custom Appearance**.
- **Admin Lockout Support (`dont_override`)**: System administrators can lock specific options so end users cannot change them in the Settings UI.
- **Multilingual**: Bundled with English, Dutch, German, and French translations.

---

## Configuration Options

Options can be set in `plugins/customizr/config.inc.php` or directly in Roundcube's main `config/config.inc.php` file:

```php
// Section under Settings > Preferences where options appear ('customizr' or 'general')
$config['customizr_settings_section'] = 'customizr';

// Define a custom watermark image (relative or absolute URL)
$config['custom_watermark_image'] = './skins/custom_watermark.png';

// Define a custom URI to be displayed instead of the empty watermark page
$config['custom_watermark_uri'] = '';

// Define a custom favicon image (empty string to remove it)
$config['custom_favicon'] = './skins/custom_favicon.ico';

// Define a custom logo image
$config['custom_logo'] = './skins/custom_logo.svg';

// Defines a custom CSS file which is added to every page
$config['custom_stylesheet'] = './skins/custom_stylez.css';

// Defines custom CSS rules injected into every page
$config['custom_css'] = '';
```

### Locking Down Settings

To prevent users from modifying specific visual settings in their Preferences, add the desired configuration keys to `$config['dont_override']`:

```php
$config['dont_override'] = [
    'custom_logo',
    'custom_watermark_image',
    'custom_stylesheet',
];
```

If all customization keys are added to `dont_override`, the "Custom Appearance" section will automatically be hidden from the Settings UI.

---

## Installation

1. Place the plugin folder into the `plugins/` directory of your Roundcube installation (as `customizr`).
2. Copy `config.inc.php.dist` to `config.inc.php` and adjust any desired default values.
3. Enable the plugin in `config/config.inc.php`:
   ```php
   $config['plugins'] = [
       // other plugins...
       'customizr',
   ];
   ```
4. Navigate to **Settings > Preferences > Custom Appearance** to configure your look and feel.
