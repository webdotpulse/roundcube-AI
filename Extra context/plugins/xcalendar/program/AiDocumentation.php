<?php
namespace XCalendar;

/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

class AiDocumentation
{
    public function get(\rcube $rcmail, bool $caldavServer, bool $caldavClient, bool $share, bool $publish,
                        bool $emailNotifications): string
    {
        $google = $rcmail->config->get('xcalendar_google_calendar_key');
        $birthday = ClientBirthday::enabled();
        $attachments = Event::areAttachmentsEnabled();
        $sunrise = $rcmail->config->get('xcalendar_show_sunrise') || $rcmail->config->get('xcalendar_show_sunset');

        $types = ['local'];
        $caldavClient && ($types[] = 'external CalDAV');
        $google && ($types[] = 'Google');
        $google && ($types[] = 'Holiday');
        $birthday && ($types[] = 'Birthday');
        $share && ($types[] = 'Shared by other Roundcube users');

        $d = [];
        $d[] = 'Plugin: Roundcube Plus Calendar';
        $d[] = 'Description: Calendar with multiple source types. AI can only perform actions provided by tools. For '.
                'other actions AI provides help and explanation.';
        $d[] = '';
        $d[] = 'Where: Main menu > Calendar';
        $d[] = 'Navigation tool: go_to_page("xcalendar")';
        $d[] = '';
        $d[] = 'Notes:';
        $d[] = '- Supported calendars: ' . implode(", ", $types);
        $caldavServer && ($d[] = '- Built-in CalDAV server for local calendars; allows external app sync (edit '.
            'calendar > Sync tab)');
        $d[] = '- Read-only calendars cannot be edited';
        $d[] = '';

        $d[] = 'Main calendar page:';
        $d[] = '- Left sidebar: mini-calendar + calendar list. Click name to show/hide events. Disabled = gray + ' .
            'strikethrough. Edit via small icon next to name. Colors match events';
        $d[] = "- Left sidebar: dropdown next to 'Calendars' title:";
        $d[] = '  [Create calendar] - new local calendar';
        $caldavClient && ($d[] = '  [Add CalDAV calendar] - enter URL, username, password > Find > select');
        $google &&       ($d[] = '  [Add Google calendar] - enter name, Google ID, bg/text colors; read-only; ' .
                                 'Google calendar must be public');
        $google &&       ($d[] = '  [Holiday calendars] - pick from country/religion; read-only');
        $share &&        ($d[] = '  [Shared calendars] - from other users; permissions vary');
        $birthday &&     ($d[] = '  [Add Birthday calendar] - birthdays from address books');

        $d[] = '- Top menu:';
        $d[] = '  [New event] - create event';
        $d[] = '  [Day] / [Week] / [Month] / [Agenda] - change view';
        $d[] = '  [Search] - find events';
        $d[] = '  [Settings] - go to calendar preferences page';
        $d[] = '';

        // Event actions
        $d[] = 'Event actions:';
        $d[] = "- Create: click empty slot in calendar grid or [New event]";
        $d[] = "- Edit: click event > popup with Edit, Delete, Options (dropdown with: Duplicate, Download, Send)";
        $d[] = '';

        // Event editor
        $d[] = 'Event editor:';
        $d[] = '- Summary tab: title, start/end, timezone, all-day, location, description, URL, calendar, category, ' .
            'color use, show-me-as, visibility, priority';
        $d[] = '- Recurrence tab: none/daily/weekly/monthly/yearly';
        $d[] = '- Notifications tab: popup' . ($emailNotifications ? ' or email' : '');
        $d[] = '- Attendees tab: emails, role, status, notify';
        $attachments && ($d[] = '- Attachments tab: upload file or link');
        $d[] = '';

        // Calendar editor
        $d[] = 'Calendar editor:';
        $d[] = '- Summary tab: name, description, background color, text color, default event visibility';
        $share && ($d[] = '- Share tab: by email; permissions = share, publish, edit, see details, notify');
        $publish && ($d[] = '- Publish tab: all info or free/busy; create/reset/remove link');
        $caldavServer && ($d[] = '- Sync tab: create CalDAV connection from built-in CalDAV server to external apps; '.
            'options: display name, password, is read-only; shows base URL for apps with autodiscovery and full URL '.
            'for no autodiscovery; username auto-generated, cannot be modified; recreate connection if password lost');
        $d[] = '- Import/Export tab: import .ics into calendar; export all events';
        $d[] = '';

        // Settings
        $d[] = 'Settings: Settings > Preferences > Calendar:';
        $d[] = '- Initial view (day/week/month/agenda)';
        $d[] = '- Week start day';
        $d[] = '- Agenda time span (weeks)';
        $d[] = '- Day time slot length';
        $d[] = '- Scroll to time';
        $d[] = '- Default calendar';
        $d[] = '- Refresh interval';
        $d[] = '- Week numbers in mini-calendar';
        $d[] = '- Category colors as borders';
        $sunrise && ($d[] = '- Sunrise/sunset in month view');
        $d[] = '- Categories list with colors';
        $d[] = '- Notification sound';
        $d[] = '- Default notifications (none/popup' . ($emailNotifications ? '/email' : '') . ')';
        $d[] = '';

        return implode("\n", $d);
    }
}