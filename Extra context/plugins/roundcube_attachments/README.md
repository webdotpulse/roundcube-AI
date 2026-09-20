# Roundcube Attachments & Reaction Enhancements Plugin

A Roundcube Webmail plugin that:
1. **Stores attachments on the server**: Save frequently used attachments (documents, brochures, pricing sheets, terms & conditions) on the server repository. Easily attach them to new compose emails with a single click or multiple selection.
2. **Assigns attachments to canned responses (reactions)**: In `Settings` => `Responses` ("Reacties"), attach saved server files or upload new files directly to a response template. When inserting the reaction in compose, the attachments are automatically added to the outgoing email.
3. **Assigns subjects to canned responses (reactions)**: In `Settings` => `Responses` ("Reacties"), set an optional default email subject for the reaction. When inserting the reaction in compose, the subject is automatically set.

## Features
- **Centralized Server Repository**: Store attachments per user in a secured server directory protected from direct HTTP access and script execution.
- **Compose Integration**: Add server attachments modal with search/filter, multi-select, and direct upload capabilities.
- **Full Dutch & English Localization**: Supported in both Dutch (`nl_NL`) and English (`en_US`).
- **Elastic, Larry, & Gmail Plus Skin Compatibility**: Beautifully styled modal and form elements supporting light and dark modes.
- **Zero Database Migration**: Uses Roundcube's native preferences and storage hooks for metadata persistence.

## Installation
1. Move or link the plugin directory to `plugins/roundcube_attachments`.
2. Copy `config.inc.php.dist` to `config.inc.php` if custom configuration is needed.
3. Add `'roundcube_attachments'` to `$config['plugins']` in your Roundcube `config/config.inc.php`.

## License
GNU General Public License v3 or later (GPL-3.0-or-later).
