<?php
/**
 * Roundcube Plus Calendar plugin.
 *
 * Copyright 2016, Tecorama LLC.
 *
 * @license Commercial. See the file LICENSE for details.
 */

require_once __DIR__ . '/../xframework/common/Plugin.php';
require_once __DIR__ . '/program/AiController.php';
require_once __DIR__ . '/program/AiDocumentation.php';
require_once __DIR__ . '/program/Permission.php';
require_once __DIR__ . '/program/Calendar.php';
require_once __DIR__ . '/program/CalendarData.php';
require_once __DIR__ . '/program/Event.php';
require_once __DIR__ . '/program/EventData.php';
require_once __DIR__ . '/program/Holiday.php';
require_once __DIR__ . '/program/Color.php';
require_once __DIR__ . '/program/Timezone.php';
require_once __DIR__ . '/program/Itip.php';
require_once __DIR__ . '/program/ItipEventInfo.php';
require_once __DIR__ . '/program/CalDavSync.php';
require_once __DIR__ . '/program/CalDavIMip.php';
require_once __DIR__ . '/program/ClientBirthday.php';
require_once __DIR__ . '/program/ClientGoogle.php';
require_once __DIR__ . '/program/ClientCaldav.php';
require_once __DIR__ . '/vendor/autoload.php';

use XFramework\Utils;
use XCalendar\Calendar;
use XCalendar\Event;
use XCalendar\EventData;
use XCalendar\Timezone;
use XCalendar\ClientBirthday;
use XCalendar\ClientCaldav;
use XCalendar\ClientGoogle;
use XCalendar\Holiday;
use XCalendar\CalendarData;
use XCalendar\Permission;
use XCalendar\AiController;
use XCalendar\AiDocumentation;
use XFramework\Response;
use XFramework\Pref;

class xcalendar extends XFramework\Plugin
{
    public $allowed_prefs = ['xsidebar_order', 'xsidebar_collapsed'];
    protected string $databaseVersion = '20260413';
    protected string $appUrl = '?_task=settings&_action=preferences&_section=xcalendar';
    protected bool $hasSidebarBox = true;
    protected bool $debug = false;
    protected bool $loadAlpine = true;
    protected bool $messageProcessed = false;
    protected bool $caldavClientEnabled;
    protected bool $caldavServerEnabled;
    protected bool $shareEnabled;
    protected bool $publishEnabled;
    protected array $itipInfo = [];
    protected XCalendar\Calendar $calendar;
    protected XCalendar\Event $event;
    protected XCalendar\Itip $itip;
    protected array $configSchema = [
        'xcalendar_view' => [
            'type' => 'string',
            'options' => ['month', 'agendaWeek', 'agendaDay', 'list'],
            'default' => 'agendaWeek',
        ],
        'xcalendar_first_day' => ['type' => 'int', 'min' => 0, 'max' => 6, 'default' => 1],
        'xcalendar_slot_duration' => [
            'type' => 'string',
            'pattern' => '/^\d{2}:\d{2}:\d{2}$/',
            'default' => '00:30:00',
        ],
        'xcalendar_scroll_time' => [
            'type' => 'string',
            'pattern' => '/^(?:(0[1-9]|1\d|2[0-2]):[0-5]\d:[0-5]\d|23:00:00)$/',
            'default' => '06:00:00',
        ],
        'xcalendar_week_numbers' => ['type' => 'bool', 'default' => false],
        'xcalendar_event_border' => ['type' => 'bool', 'default' => false],
        'xcalendar_alarm_sound' => [
            'type' => 'string',
            'options' => ['', 'aurora', 'bamboo', 'bells', 'chord', 'circles', 'glass', 'maramba', 'metal', 'ripple',
                'startrek', 'whistle', 'yoohoo'],
            'default' => 'maramba',
        ],
        'xcalendar_bg_color' => [
            'type' => 'string',
            'pattern' => '/^#[0-9a-f]{3}([0-9a-f]{3})?$/i',
            'default' => '#a9d5fc',
        ],
        'xcalendar_tx_color' => [
            'type' => 'string',
            'pattern' => '/^#[0-9a-f]{3}([0-9a-f]{3})?$/i',
            'default' => '#333',
        ],
        'xcalendar_categories' => [
            'type' => 'array',
            'default' => [
                ['name' => 'Personal', 'color' => '#8B008B'],
                ['name' => 'Work', 'color' => '#483D8B'],
                ['name' => 'Family', 'color' => '#006400'],
            ],
        ],
        'xcalendar_enable_event_attachments' => ['type' => 'bool', 'default' => false],
        'xcalendar_attachment_dir' => ['type' => 'string', 'default' => ''],
        'xcalendar_max_attachment_size' => ['type' => 'int', 'min' => 1, 'default' => 1000000],
        'xcalendar_google_drive_integration' => ['type' => 'bool', 'default' => true],
        'xcalendar_dropbox_integration' => ['type' => 'bool', 'default' => true],
        'xcalendar_google_calendar_key' => ['type' => 'string', 'default' => ''],
        'xcalendar_show_sunrise' => ['type' => 'bool', 'default' => false],
        'xcalendar_show_sunset' => ['type' => 'bool', 'default' => false],
        'xcalendar_verify_shared_emails' => ['type' => 'bool', 'default' => false],
        'xcalendar_allowed_share_domains' => ['type' => 'array', 'default' => []],
        'xcalendar_calendar_share_enabled' => ['type' => 'bool', 'default' => true],
        'xcalendar_calendar_publish_enabled' => ['type' => 'bool', 'default' => true],
        'xcalendar_birthday_calendar_enabled' => ['type' => 'bool', 'default' => true],
        'xcalendar_caldav_client_enabled' => ['type' => 'bool', 'default' => true],
        'xcalendar_caldav_block_private_hosts' => ['type' => 'bool', 'default' => false],
        'xcalendar_caldav_server_enabled' => ['type' => 'bool', 'default' => false],
        'xcalendar_caldav_server_domain' => ['type' => 'string', 'default' => ''],
        'xcalendar_caldav_enable_browser_plugin' => ['type' => 'bool', 'default' => false],
        'xcalendar_smtp_user' => ['type' => 'string', 'default' => ''],
        'xcalendar_smtp_pass' => ['type' => 'string', 'default' => ''],
        'xcalendar_email_event_notifications' => ['type' => 'bool', 'default' => false],
        'xcalendar_email_event_notification_delivery_pause' => ['type' => 'int', 'min' => 0, 'default' => 0],
        'xcalendar_imip_sender_email' => ['type' => 'string', 'default' => ''],
        'xcalendar_cron_execution_delay' => ['type' => 'int', 'min' => 0, 'max' => 30, 'default' => 0],
        'xcalendar_agenda_week_span' => ['type' => 'int', 'min' => 1, 'max' => 4, 'default' => 1],
        'xcalendar_caldav_debug_log_enabled' => ['type' => 'bool', 'default' => false],
        'xcalendar_default_notification_type' => [
            'type' => 'string',
            'options' => ['none', 'popup', 'email'],
            'default' => 'none',
        ],
        'xcalendar_default_notification_position' => [
            'type' => 'string',
            'options' => ['before_start', 'after_start', 'before_end', 'after_end'],
            'default' => 'before_start',
        ],
        'xcalendar_default_notification_number' => ['type' => 'int', 'min' => 0, 'max' => 60, 'default' => 10],
        'xcalendar_default_notification_units' => [
            'type' => 'string',
            'options' => ['minutes', 'hours', 'days', 'weeks'],
            'default' => 'minutes',
        ],
        'xcalendar_search_range_year_limit' => ['type' => 'int', 'min' => 1, 'default' => 1],
        'xcalendar_refresh' => ['type' => 'int', 'options' => [0, 5, 15, 30, 60], 'default' => 30],
    ];


    public function init(): void
    {
        $this->default = Calendar::getDefaultSettings();
        parent::init();
    }

    /**
     * Initializes the plugin.
     * @throws Exception
     */
    public function initialize(): void
    {
        // fix plugin config according to the specified schema
        $this->config->validate($this->configSchema);

        $this->calendar = new XCalendar\Calendar();
        $this->event = new XCalendar\Event();
        $this->itip = new XCalendar\Itip($this->event);
        $this->caldavClientEnabled = ClientCaldav::enabled();
        $this->caldavServerEnabled = $this->rcmail->config->get('xcalendar_caldav_server_enabled');
        $this->shareEnabled = $this->rcmail->config->get('xcalendar_calendar_share_enabled');
        $this->publishEnabled = $this->rcmail->config->get('xcalendar_calendar_publish_enabled');

        // run cron
        if (rcube_utils::get_input_value('xcalendar-cron', rcube_utils::INPUT_GET) && !$this->isDemo()) {
            $this->runCron();
        }

        // make sure the user has a default calendar
        CalendarData::createDefaultCalendar();

        // if caldav domain or subdomain not specified, disable the server
        if (empty($this->rcmail->config->get('xcalendar_caldav_server_domain'))) {
            $this->caldavServerEnabled = false;
        }

        // on cpanel disable publishing and sharing if using sqlite because the users have individual databases
        if (Utils::isCpanel()) {
            $this->caldavServerEnabled = false;
            $this->publishEnabled = false;

            if (str_contains($this->rcmail->config->get('db_dsnw'), 'sqlite')) {
                $this->shareEnabled = false;
            }
        }

        // register the calendar task and add the calendar button to the taskbar
        if (!$this->unitTest) {
            $this->register_task('xcalendar');
        }

        $this->add_button(
            [
                'command' => 'xcalendar',
                'class' => 'button-calendar',
                'classsel' => 'button-calendar button-selected',
                'innerclass' => 'button-inner',
                'label' => 'xcalendar.calendar',
                'type' => 'link',
            ],
            'taskbar'
        );

        // URL action: process the encoded url action/data
        if ($request = rcube_utils::get_input_value('xcalendar', rcube_utils::INPUT_GET)) {
            // handle the encrypted url for downloading attachments included with exported ics files
            if (Utils::decodeUrlAction($request, $data) == 'download-attachment') {
                $this->event->dispatchAttachment($data['name'], $data['path']);
            }
            Utils::exit404();
        }

        // URL action: output the published calendar content
        if ($code = rcube_utils::get_input_value('xcalendar-publish', rcube_utils::INPUT_GET)) {
            $this->calendar->getPublishedContent($code);
        }

        // URL action: start download of an exported event file
        if ($calendarId = rcube_utils::get_input_value('xcalendar-export', rcube_utils::INPUT_GET)) {
            $this->exportEventsSendFile($calendarId);
        }

        // include the assets
        $min = $this->debug ? '' : '.min';

        if ($this->rcmail->task == 'xcalendar') {
            $this->register_action('index', [$this, 'getIndex']);
            $this->register_action('getCalendarData', [$this, 'getCalendarData']);
            $this->register_action('getHolidayList', [$this, 'getHolidayList']);
            $this->register_action('saveCalendarData', [$this, 'saveCalendarData']);
            $this->register_action('addHolidays', [$this, 'addHolidays']);
            $this->register_action('removeHolidays', [$this, 'removeHolidays']);
            $this->register_action('enableCalendar', [$this, 'enableCalendar']);
            $this->register_action('removeCalendar', [$this, 'removeCalendar']);
            $this->register_action('restoreCalendar', [$this, 'restoreCalendar']);
            $this->register_action('getLocalEventList', [$this, 'getLocalEventList']);
            $this->register_action('getRemoteEventList', [$this, 'getRemoteEventList']);
            $this->register_action('getEventPreviewData', [$this, 'getEventPreviewData']);
            $this->register_action('getEventData', [$this, 'getEventData']);
            $this->register_action('saveEventData', [$this, 'saveEventData']);
            $this->register_action('saveEventDrop', [$this, 'saveEventDrop']);
            $this->register_action('uploadEventAttachment', [$this, 'uploadEventAttachment']);
            $this->register_action('removeEventAttachment', [$this, 'removeEventAttachment']);
            $this->register_action('removeEvent', [$this, 'removeEvent']);
            $this->register_action('restoreEvent', [$this, 'restoreEvent']);
            $this->register_action('downloadEvent', [$this, 'downloadEvent']);
            $this->register_action('setCurrentUserAttendeeStatus', [$this, 'setCurrentUserAttendeeStatus']);
            $this->register_action('exportEvents', [$this, 'exportEvents']);
            $this->register_action('importEvents', [$this, 'importEvents']);
            $this->register_action('getSearchPageData', [$this, 'getSearchPageData']);
            $this->register_action('searchEvents', [$this, 'searchEvents']);

            if ($this->shareEnabled) {
                $this->register_action('ac', [$this, 'addSharedCalendarViaUrl']);
                $this->register_action('getSharedCalendarList', [$this, 'getSharedCalendarList']);
                $this->register_action('addSharedCalendar', [$this, 'addSharedCalendar']);
                $this->register_action('removeSharedCalendar', [$this, 'removeSharedCalendar']);
                $this->register_action('unshareCalendar', [$this, 'unshareCalendar']);
            }

            if ($this->publishEnabled) {
                $this->register_action('setPublishCode', [$this, 'setPublishCode']);
            }

            if ($this->caldavClientEnabled) {
                $this->register_action('findCaldavCalendars', [$this, 'findCaldavCalendars']);
                $this->register_action('addCaldavCalendars', [$this, 'addCaldavCalendars']);
            }

            if ($this->caldavServerEnabled) {
                $this->register_action('addSync', [$this, 'addSync']);
                $this->register_action('removeSync', [$this, 'removeSync']);
            }

            $this->includeAsset('xframework/assets/bower_components/moment/min/moment.min.js');
            $this->includeAsset("xframework/assets/bower_components/jquery-timepicker-jt/jquery.timepicker$min.js");
            $this->includeAsset('xframework/assets/bower_components/jquery-timepicker-jt/jquery.timepicker.css');
            $this->includeAsset('xframework/assets/bower_components/jquery-form/jquery.form.js');
            $this->includeAsset('assets/fullcalendar/index.global.min.js');
            $this->includeAsset('assets/fullcalendar/locales-all.global.min.js');
            $this->includeAsset('assets/fullcalendar/moment/index.global.min.js');
            $this->includeAsset("assets/scripts/app$min.js");
            $this->includeAsset("assets/scripts/calendar$min.js");

            if ($this->rcmail->output) {
                $this->rcmail->output->add_label('edit', 'xcalendar.unshare_warning', 'xcalendar.all_day', 'delete',
                    'xcalendar.remove_all', 'xcalendar.no_events', 'xcalendar.no_more_holidays_allowed',
                    'xcalendar.specify_google_calendar_id', 'xcalendar.calendar_id_format_error',
                    'xcalendar.confirm_remove_sync', 'xcalendar.new_share_not_saved_warning', 'xcalendar.calendar',
                    'xcalendar.attendee_notify_send', 'xcalendar.attendee_notify_dont',
                    'xcalendar.attendee_notify_cancel', 'xcalendar.attendee_status_yes', 'xcalendar.attendee_status_no',
                    'xcalendar.attendee_status_maybe', 'xcalendar.attendee_status_delegated',
                    'xcalendar.attendee_status_waiting', 'xcalendar.confirm_remove_calendar',
                    'xcalendar.unable_to_retrieve_calendar_data', 'xcalendar.attendees',
                    'xcalendar.new_attendee_not_saved_warning', 'xcalendar.confirm_remove_event', 'recipientedit',
                    'recipient', 'attachment'
                );
            }

            $this->setJsVar('xcalendar_birthday_calendar_enabled', ClientBirthday::enabled());
            $this->setJsVar('xcalendar_caldav_client_enabled', $this->caldavClientEnabled);
            $this->setJsVar('xcalendar_caldav_server_enabled', $this->caldavServerEnabled);
            $this->setJsVar('xcalendar_calendar_share_enabled', $this->shareEnabled);
            $this->setJsVar('xcalendar_calendar_publish_enabled', $this->publishEnabled);
            $this->setJsVar('xcalendar_allowed_holiday_count', Holiday::ALLOWED_HOLIDAY_COUNT);
            $this->setJsVar('xcalendar_google_drive_integration', $this->rcmail->config->get('xcalendar_google_drive_integration'));
            $this->setJsVar('xcalendar_dropbox_integration', $this->rcmail->config->get('xcalendar_dropbox_integration'));
            $this->setJsVar('xcalendar_google_calendar_key', $this->rcmail->config->get('xcalendar_google_calendar_key'));
            $this->setJsVar('xcalendar_time_now', date('c', time() + $this->getTimezoneDifference()));
            $this->setJsVar('xcalendar_user_email', $this->getIdentityEmail());
        } else if ($this->rcmail->task == 'settings') {
            $this->add_hook('preferences_sections_list', [$this, 'preferencesSectionsList']);
            $this->add_hook('preferences_list', [$this, 'preferencesList']);
            $this->add_hook('preferences_save', [$this, 'preferencesSave']);

            $this->includeAsset("assets/scripts/settings$min.js");

        } else if ($this->rcmail->task == 'mail') {
            $this->add_hook('message_compose', [$this, 'messageCompose']);

            if ($this->rcmail->action == 'show' || $this->rcmail->action == 'preview') {
                $this->add_hook('message_load', [$this, 'messageLoad']);
                $this->add_hook('template_object_messagebody', [$this, 'messageBody']);
            }

            switch ($this->rcmail->action) {
                case 'xcalendar.processItipResponse':
                    if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
                        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
                    }
                    $this->itip->processResponse();
                    break;
                case 'xcalendar.processItipUpdateReply':
                    if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
                        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
                    }
                    $this->itip->processUpdateReply();
                    break;
                case 'xcalendar.processItipUpdateEvent':
                    if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
                        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
                    }
                    $this->itip->processUpdateEvent();
                    break;
                case 'xcalendar.processItipDelete':
                    if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
                        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
                    }
                    $this->itip->processDelete();
                    break;
                case 'xcalendar.addMessageEventsToCalendar':
                    if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
                        $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
                    }
                    $this->addMessageEventsToCalendar();
                    break;
                case 'xcalendar.getTodaysAgenda':
                    $this->getTodaysAgenda();
                    break;
            }

            $this->rcmail->output->add_label('xcalendar.add_events', 'cancel', 'xcalendar.select_calendar_for_events',
                'xcalendar.select_calendar'
            );

            $this->includeAsset("assets/scripts/mail$min.js");
        }

        $this->add_hook('startup', [$this, 'startup']);
        $this->add_hook('xais_before_process_prompt', [$this, 'xaisBeforeProcessPrompt']);
        $this->add_hook('xais_get_plugin_documentation', [$this, 'xaisGetPluginDocumentation']);
        $this->add_hook('xais_generate_shortcuts', [$this, 'xaisGenerateShortcuts']);

        $this->includeAsset("xframework/assets/bower_components/howler.js/dist/howler$min.js");
        $this->skinBase && $this->includeAsset("assets/styles/$this->skinBase.css");

        switch ($this->rcmail->action) {
            case 'xcalendarGetAlarms':
                $this->getPopupAlarms();
                break;
            case 'xcalendarSnooze':
                if ($this->rcmail && method_exists($this->rcmail, 'request_security_check')) {
                    $this->rcmail->request_security_check(rcube_utils::INPUT_POST);
                }
                $this->snooze();
                break;
        }

        if ($this->rcmail->action != 'save-pref') {
            $this->includeAsset("assets/scripts/alarm$min.js");
            $this->rcmail->output->add_label('xcalendar.upcoming_events', 'xcalendar.dismiss_all', 'close');
        }
    }

    public function startup(): void
    {
        if ($this->rcmail->task == 'xcalendar') {
            $this->addBodyClass('xcalendar');
        }
    }

    /**
     * [Hook] Returns shortcuts that will be shown in the AI assistant dialog on the calendar page.
     *
     * @param array $arg
     * @return array
     */
    public function xaisGenerateShortcuts(array $arg): array
    {
        if ($arg['page'] == 'xcalendar') {
            $arg['shortcuts'] = array_merge($arg['shortcuts'], [
                [
                    'id' => 'list_meetings',
                    'icon' => 'xi-users',
                    'visible' => true,
                    'label' => 'List upcoming meetings',
                    'prompt' => 'List up to 3 upcoming calendar meetings',
                ],
                [
                    'id' => 'list_doctor_appointments',
                    'icon' => 'xi-clock',
                    'visible' => true,
                    'label' => 'List doctor appointments',
                    'prompt' => 'List up to 3 upcoming calendar doctor appointments',
                ]
            ]);
        }

        return $arg;
    }

    /**
     * [Hook] Adds tools and controllers available on the calendar page.
     *
     * @param array $arg
     * @return array
     */
    public function xaisBeforeProcessPrompt(array $arg): array
    {
        $arg['controllers'][] = new AiController();

        return $arg;
    }

    /**
     * [Hook] Returns documentation that teaches the AI model about the xcalendar plugin.
     *
     * @param array $arg
     * @return array
     */
    public function xaisGetPluginDocumentation(array $arg): array
    {
        if ($arg['plugin'] == $this->ID) {
            $arg['text'] = (new AiDocumentation())->get(
                $this->rcmail,
                $this->caldavServerEnabled,
                $this->caldavClientEnabled,
                $this->shareEnabled,
                $this->publishEnabled,
                $this->isEmailNotificationEnabled(),
            );
        }

        return $arg;
    }

    /**
     * Opens a specified message, searches for events and adds the events to the specified calendar.
     */
    public function addMessageEventsToCalendar(): void
    {
        try {
            $uid = rcube_utils::get_input_value("uid", rcube_utils::INPUT_POST);
            $mbox = rcube_utils::get_input_value("mbox", rcube_utils::INPUT_POST);
            $mimeId = rcube_utils::get_input_value("mimeId", rcube_utils::INPUT_POST);
            $calendarId = rcube_utils::get_input_value("calendarId", rcube_utils::INPUT_POST);

            if (!$uid || !$mbox || !$mimeId || !$calendarId || !($message = new rcube_message($uid, $mbox))) {
                throw new Exception();
            }

            $result = $this->event->importEvents($message->get_part_body($mimeId), $calendarId);

            if (!is_array($result) || !$result['success']) {
                throw new Exception();
            }

            $this->rcmail->output->command(
                "display_message",
                $this->gettext($result['error'] ? "events_imported__with_errors" : "events_imported_successfully"),
                "confirmation"
            );
        } catch (Exception $e) {
            $message = $e->getMessage() ?: $this->gettext("xcalendar.error_importing_events");
            $this->rcmail->output->command("display_message", $message, "error");
            Utils::logError($message . " (57884)");
        }
    }

    /**
     * Returns the sidebar box html. We show a loader and get the agenda via ajax so we don't delay the page load.
     *
     * @return array
     */
    public function getSidebarBox(): array
    {
        return [
            "title" => rcube::Q($this->gettext("xcalendar.todays_agenda")),
            "html" => html::div(["id" => "xcalendar-todays-agenda", "class" => "xspinner"], ""),
            "settingsUrl" => $this->appUrl,
        ];
    }

    /**
     * Adds the section links to the settings section list.
     *
     * @param array $arg
     * @return array
     */
    public function preferencesSectionsList(array $arg): array
    {
        $arg['list']['xcalendar'] = ['id' => 'xcalendar', 'section' => $this->gettext("xcalendar.calendar")];
		return $arg;
    }

    /**
     * Creates the calendar settings page.
     *
     * @param array $arg
     * @return array
     */
    public function preferencesList(array $arg): array
    {
        if ($arg['section'] != "xcalendar") {
            return $arg;
        }

        $scrollItems = [];
        $format = $this->format->getTimeFormat();
        for ($i = 1; $i < 24; $i++) {
            $time = sprintf('%02d:00:00', $i);
            $scrollItems[$time] = date($format, strtotime($time));
        }

        $soundItems = ['' => $this->gettext('none')];
        foreach ($this->configSchema['xcalendar_alarm_sound']['options'] as $sound) {
            $soundItems[$sound] = $this->gettext("xcalendar.alarm_sound_$sound");
        }

        $error = '';
        $defaultNotificationTypes = [
            'none' => $this->gettext("none"),
            'popup' => $this->gettext("xcalendar.popup")
        ];
        if ($this->isEmailNotificationEnabled($error)) {
            $defaultNotificationTypes['email'] = $this->gettext("email");
        }

        $categoryItems = [];
        foreach ($this->event->getCategories() as $key => $category) {
            $name = new html_inputfield([
                'name' => "xcalendar_categories[$key][name]",
                'value' => $category['name']
            ]);
            $color = new html_inputfield([
                'type' => 'color',
                'name' => "xcalendar_categories[$key][color]",
                'value' => $category['color']
            ]);
            $categoryItems[] = html::div(
                ['class' => 'category-item'],
                $name->show() . ' ' .
                $color->show() . ' ' .
                html::tag(
                    'button',
                    [
                        'type' => 'button',
                        'class' => 'button btn btn-sm btn-secondary',
                        'onclick' => '$(this).parent().remove()',
                    ],
                    'X'
                )
            );
        }

        $categoriesHtml = html::div(['id' => 'xcalendar-categories'],
            html::div(['class' => 'category-list'], implode(' ', $categoryItems)) .
            html::div(
                ["class" => "category-add"],
                (new html_inputfield(['name' => '', 'value' => '']))->show() . ' ' .
                html::tag(
                    'button',
                    [
                        'type' => 'button',
                        'class' => 'button btn btn-sm btn-secondary',
                        'onclick' => 'xcalendarCategories.add()',
                    ],
                    rcube::Q($this->gettext('xcalendar.add_category'))
                )
            )
        );

        // SECTION: MAIN
        $pref = new Pref($arg, $this->ID, 'mainoptions');
        $pref->select('xcalendar_view',
            [
                'agendaDay' => $this->gettext('xcalendar.day'),
                'agendaWeek' => $this->gettext('xcalendar.week'),
                'month' => $this->gettext('xcalendar.month'),
                'list' => $this->gettext('xcalendar.agenda'),
            ]
        );
        $pref->select('xcalendar_first_day',
            [
                1 => $this->gettext('monday'),
                2 => $this->gettext('tuesday'),
                3 => $this->gettext('wednesday'),
                4 => $this->gettext('thursday'),
                5 => $this->gettext('friday'),
                6 => $this->gettext('saturday'),
                0 => $this->gettext('sunday'),
            ]
        );
        $pref->select('xcalendar_agenda_week_span', '1,2,3,4');
        $pref->select('xcalendar_slot_duration',
            [
                '00:10:00' => '10',
                '00:15:00' => '15',
                '00:20:00' => '20',
                '00:30:00' => '30',
                '00:60:00' => '60',
            ]
        );
        $pref->select('xcalendar_scroll_time', $scrollItems);
        $pref->select('xcalendar_calendar',
            $this->calendar->getCalendarArray(
                [XCalendar\Calendar::LOCAL, XCalendar\Calendar::CALDAV], false, false, true, true
            )
        );
        $pref->select('xcalendar_refresh',
            [
                0 => $this->gettext('xcalendar.refresh_never'),
                5 => $this->gettext('xcalendar.refresh_every_5_minutes'),
                15 => $this->gettext('xcalendar.refresh_every_15_minutes'),
                30 => $this->gettext('xcalendar.refresh_every_30_minutes'),
                60 => $this->gettext('xcalendar.refresh_every_60_minutes'),
            ]
        );
        $pref->checkbox('xcalendar_week_numbers');
        $pref->checkbox('xcalendar_event_border');

        if ($this->rcmail->plugins->get_plugin("xweather")) {
            $pref->checkbox('xcalendar_show_sunrise');
            $pref->checkbox('xcalendar_show_sunset');
        }

        // SECTION: CATEGORIES
        $pref = new Pref($arg, $this->ID, 'categories');
        $pref->html($categoriesHtml);

        // SECTION: NOTIFICATIONS
        $pref = new Pref($arg, $this->ID, 'notifications');
        $pref->select(
            'xcalendar_alarm_sound',
            $soundItems,
            null,
            html::tag(
                'button',
                [
                    'type' => 'button',
                    'class' => 'button btn btn-sm btn-secondary',
                    'onclick' => 'xalarm.previewSound()',
                ],
                $this->gettext('xcalendar.play_sound')
            )
        );
        $pref->select('xcalendar_default_notification_type', $defaultNotificationTypes);
        $pref->select('xcalendar_default_notification_position',
            [
                'before_start' => $this->gettext('xcalendar.alarm_before_event_starts'),
                'after_start' => $this->gettext('xcalendar.alarm_after_event_starts'),
                'before_end' => $this->gettext('xcalendar.alarm_before_event_ends'),
                'after_end' => $this->gettext('xcalendar.alarm_after_event_ends'),
            ]
        );
        $pref->input('xcalendar_default_notification_number');
        $pref->select('xcalendar_default_notification_units',
            [
                'minutes' => $this->gettext('xcalendar.minutes'),
                'hours' => $this->gettext('xcalendar.hours'),
                'days' => $this->gettext('xcalendar.days'),
                'weeks' => $this->gettext('xcalendar.weeks'),
            ]
        );

		return $arg;
    }

    /**
     * Saves the settings from the calendar settings page.
     *
     * @param array $arg
     * @return array
     */
    public function preferencesSave(array $arg): array
    {
        if ($arg['section'] !== 'xcalendar') {
            return $arg;
        }

        $schema = $this->configSchema;
        $keys = ['xcalendar_view', 'xcalendar_first_day', 'xcalendar_agenda_week_span', 'xcalendar_slot_duration',
            'xcalendar_scroll_time', 'xcalendar_refresh', 'xcalendar_week_numbers', 'xcalendar_event_border',
            'xcalendar_show_sunrise', 'xcalendar_show_sunset', 'xcalendar_alarm_sound', 'xcalendar_categories',
            'xcalendar_default_notification_type', 'xcalendar_default_notification_position',
            'xcalendar_default_notification_number', 'xcalendar_default_notification_units'];

        // make sure the calendar id belongs to this user
        $calendarId = rcube_utils::get_input_value('xcalendar_calendar', rcube_utils::INPUT_POST);
        $calendars = $this->calendar->getCalendarArray(
            [XCalendar\Calendar::LOCAL, XCalendar\Calendar::CALDAV], false, false, true, true
        );

        if (array_key_exists($calendarId, $calendars)) {
            $schema['xcalendar_calendar'] = ['type' => 'int', 'default' => 0];
            $keys[] = 'xcalendar_calendar';
        }

        Pref::save($arg, $schema, $keys);

        return $arg;
    }

    /**
     * Sends the main calendar page layout with contents.
     */
    public function getIndex(): void
    {
        // send the calendar list to js via env, so we don't have to pull it via ajax - we need it right away
        $this->setJsVar("xcalendar_calendar_list", $this->calendar->getCalendarList());
        $this->setJsVar("xcalendar_new_shares_count", $this->calendar->getNewSharedCalendarCount());
        $this->rcmail->output->set_pagetitle($this->gettext('calendar'));
        $this->rcmail->output->send("xcalendar.layout");
    }

    public function findCaldavCalendars(): void
    {
        try {
            $calendars = ClientCaldav::findCalendars(
                trim((string)xget("url")),
                trim((string)xget("username")),
                trim((string)xget("password"))
            );

            if (empty($calendars)) {
                Response::error($this->rcmail->gettext("xcalendar.caldav_client_error_empty"));
            }

            Response::success(["calendars" => $calendars]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, "Unauthorized")) {
                $errorMessage = $this->rcmail->gettext("xcalendar.caldav_client_error_credentials");
            } else {
                $errorMessage = $this->rcmail->gettext("xcalendar.caldav_client_error_url");
            }

            Response::error($errorMessage);
        }
    }

    public function addCaldavCalendars(): void
    {
        try {
            if (!$this->caldavClientEnabled) {
                throw new Exception("CalDAV client functionality disabled. (281955)");
            }

            $added = $this->calendar->addCaldavCalendars([
                'url' => trim((string)xget('url')),
                'username' => trim((string)xget('username')),
                'password' => trim((string)xget('password')),
                'caldav_calendars' => xget('caldav_calendars'),
            ]);

            Response::success([
                "calendars" => $this->calendar->getCalendarList(),
                "added" => $added
            ]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Sends calendar data.
     */
    public function getCalendarData(): void
    {
        $id = xget("id");
        $calendarData = $id ? CalendarData::load($id) : CalendarData::loadEmpty(xget("type"));
        $result = $calendarData->getDataForEditing();
        $sharedDeletedId = false;

        if (empty($result)) {
            foreach ($this->calendar->getAddedSharedCalendars() as $val) {
                if ($val['id'] == $id) {
                    $sharedDeletedId = $id;
                    break;
                }
            }
        }

        Response::success([
            "calendar" => $result,
            "hasCalendarData" => !empty($result),
            "sharedDeletedId" => $sharedDeletedId,
        ]);
    }

    /**
     * Saves the calendar data sent to be saved via ajax.
     */
    public function saveCalendarData(): void
    {
        try {
            switch (xget("type")) {
                case Calendar::CALDAV:
                    if (!$this->caldavClientEnabled) {
                        throw new Exception("CalDAV client functionality disabled. (7288912)");
                    }
                    $calendarId = CalendarData::saveCaldavPost();
                    break;
                case Calendar::GOOGLE:
                    $calendarId = CalendarData::saveGooglePost();
                    break;
                case Calendar::HOLIDAY:
                    $calendarId = CalendarData::saveHolidayPost();
                    break;
                case Calendar::BIRTHDAY:
                    $calendarId = CalendarData::saveBirthdayPost();
                    break;
                case Calendar::LOCAL:
                    $calendarId = CalendarData::saveLocalPost();
                    break;
                default:
                    throw new Exception("Invalid calendar type (2711283)");
            }

            Response::success(["calendarId" => $calendarId, "calendars" => $this->calendar->getCalendarList()]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Sends the holiday list.
     */
    public function getHolidayList(): void
    {
        Response::success(["list" => Holiday::getList(), "added" => Holiday::getAdded()]);
    }

    /**
     * Saves the added holiday calendar and sends a new list of calendars back.
     */
    public function addHolidays(): void
    {
        if ($this->calendar->getCalendarCount(Calendar::HOLIDAY) >= Holiday::ALLOWED_HOLIDAY_COUNT) {
            Response::error($this->rcmail->gettext("xcalendar.no_more_holidays_allowed"));
        }

        Response::send(
            $id = Holiday::add(xget("url"), xget("name")),
            ["id" => $id, "calendars" => $this->calendar->getCalendarList(), "added" => Holiday::getAdded()]
        );
    }

    /**
     * Removes a holiday calendar and sends a new list of calendars back.
     */
    public function removeHolidays(): void
    {
        Response::send(
            Holiday::remove((int)xget("id")),
            [
                "calendars" => $this->calendar->getCalendarList(),
                "added" => Holiday::getAdded(),
            ]
        );
    }

    /**
     * Enables a calendar. (Ajax)
     */
    public function enableCalendar(): void
    {
        Response::send($this->calendar->enableCalendar((int)xget("id"), (bool)xget("enabled")));
    }

    /**
     * Removes a calendar. (Ajax)
     */
    public function removeCalendar(): void
    {
        Response::send(
            $this->calendar->removeCalendar((int)xget("id")),
            ["calendars" => $this->calendar->getCalendarList()]
        );
    }

    /**
     * Restores a calendar after the user clicked the "undo" button. (Ajax)
     */
    public function restoreCalendar(): void
    {
        Response::send(
            $this->calendar->restoreCalendar((int)xget("id")),
            ["calendars" => $this->calendar->getCalendarList()]
        );
    }

    /**
     * Sends the event list for the specified start/end dates.
     */
    public function getLocalEventList(): void
    {
        Response::success([
            "events" => $this->event->getLocalEventList(xget("start"), xget("end")),
            "sunData" => $this->calendar->getSunData(xget("start"), xget("end"), $this->getTimezoneOffset())
        ]);
    }

    public function getRemoteEventList(): void
    {
        try {
            $calendar = json_decode(xget("calendar"), true);
            if (!is_array($calendar) || empty($calendar['type']) || empty($calendar['id']) || empty($calendar['name'])) {
                throw new Exception("Cannot decode calendar data (2748119)");
            }

            switch ($calendar['type']) {
                case Calendar::CALDAV:
                    $events = ClientCaldav::getEvents($calendar['id'], xget("startTime"), xget("endTime"));
                    break;
                case Calendar::HOLIDAY:
                case Calendar::GOOGLE:
                    $events = ClientGoogle::getEvents($calendar['id'], xget("startTime"), xget("endTime"));
                    break;
                case Calendar::BIRTHDAY:
                    $events = ClientBirthday::getEvents(xget("startTime"), xget("endTime"));
                    break;
                default:
                    throw new Exception("Invalid calendar type (372881)");
            }

            Response::success(["events" => $events]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Sends the preview data for the specified event. The preview data will be used to construct a preview popup.
     * This function is only used for local events; caldav and google events get their previews directly from their
     * javascript data.
     */
    public function getEventPreviewData(): void
    {
        try {
            $eventData = new EventData();

            if (!$eventData->loadFromDb(xget("id"))) {
                throw new Exception("Unable to load event (478119)");
            }

            Response::success(["preview" => $eventData->getDataForPreview()]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Sends complete event data for a specific event.
     */
    public function getEventData(): void
    {
        $eventData = new EventData();
        $error = "";
        $attachmentsEnabled = Event::areAttachmentsEnabled();
        $calendarType = Calendar::LOCAL;

        try {
            if ($id = xget("id")) {
                if ($vcalendar = xget("vcalendar")) {
                    if (!$this->caldavClientEnabled) {
                        throw new Exception("CalDAV client functionality disabled. (289499)");
                    }

                    $calendarType = Calendar::CALDAV;

                    if (!$eventData->loadFromVCalendar($vcalendar, xget("calendarId"))) {
                        throw new Exception("Unable to load event (481994)");
                    }

                    // disable uploading local attachments with caldav calendars
                    $attachmentsEnabled = false;

                } else {
                    if (!$eventData->loadFromDb($id)) {
                        throw new Exception("Unable to load event (199382)");
                    }
                }
            } else {
                $allDay = xget("allDay");
                $startDate = strtotime(xget("startDate"));

                if ($endDate = xget("endDate")) {
                    $endDate = strtotime($endDate);
                } else {
                    // get the next full hour
                    $plusOne = strtotime("+1 hour", $startDate);
                    $endDate = strtotime(date("Y-m-d", $plusOne) . " " . date("H", $plusOne) . ":00:00", $startDate);
                }

                $eventData->setValue("start", $allDay ? date("Y-m-d 00:00:00", $startDate) : date("Y-m-d H:i:s", $startDate));
                $eventData->setValue("end", $allDay ? date("Y-m-d 00:00:00", $endDate) : date("Y-m-d H:i:s", $endDate));
                $eventData->setValue("all_day", (string)(int)$allDay);
            }

            if (!($data = $eventData->getDataForEditing(xget("duplicate"), $error))) {
                Response::error($error);
            }

            $data['href'] = xget("href");
            $data['etag'] = xget("etag");

            // check if this event has been added by accepting event from the itip message, in which case we need to display a notification
            // that editing will not be reflected in the original event created by the organizer
            $showItipOrganizerNote = false;
            $isOrganizer = false;

            if ($data['has_attendees'] && !empty($data['attendees'])) {
                // first check if the user is an organizer (the user can be in the list more than once if his identity emails are used)
                foreach ($data['attendees'] as $attendee) {
                    if ($attendee['user_id'] == $this->userId && $attendee['organizer']) {
                        $isOrganizer = true;
                    }
                }

                // now check if the user is in the list without being the organizer
                foreach ($data['attendees'] as $attendee) {
                    if ($attendee['user_id'] == $this->userId && !$isOrganizer) {
                        $showItipOrganizerNote = true;
                    }
                }
            }

            // check if email notification cron is running
            $emailNotificationsError = false;
            $emailNotificationsEnabled = $this->isEmailNotificationEnabled($emailNotificationsError);
            $tz = $this->rcmail->config->get("timezone");

            // get calendar list
            if ($calendarType == Calendar::LOCAL) {
                $calendars = $this->calendar->getCalendarList(
                    $id ? [Calendar::LOCAL] : [Calendar::LOCAL, Calendar::CALDAV],
                    true,
                    false,
                    true,
                    true
                );
            } else {
                $calendars = [];
            }

            Response::success([
                "calendarType" => $calendarType,
                "event" => $data,
                "calendars" => $calendars,
                "categories" => array_merge(
                    [["name" => $this->gettext("xcalendar.no_category"), "color" => "#000000"]],
                    $this->event->getCategories()
                ),
                "timezones" => Timezone::getTimezoneList(),
                "timezonesVisible" => $data['timezone_start'] != $tz || $data['timezone_end'] != $tz,
                "userTimezone" => $tz,
                "emailNotificationsEnabled" => $emailNotificationsEnabled,
                "emailNotificationsError" => $emailNotificationsError,
                "attachmentsEnabled" => $attachmentsEnabled,
                "maxAttachmentSizeNote" => $this->rcmail->gettext([
                    "name" => "maxuploadsize",
                    "vars" => ["size" => Utils::sizeToString(Event::getMaxAttachmentSize())]
                ]),
                "showItipOrganizerNote" => $showItipOrganizerNote,
            ]);
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Saves the new event properties after event drag-and-drop
     */
    public function saveEventDrop(): void
    {
        try {
            (new EventData())->saveDropData($this->input->fill(
                ["id", "type", "calendar_id", "href", "etag", "vcalendar", "start", "end", "all_day"]
            ));
            Response::success();
        } catch (Exception $e) {
            Response::error($e->getMessage() ?: "Cannot save event data (583772)");
        }
    }

    /**
     * Saves the event data after the user edited and submitted the event edit form via ajax.
     */
    public function saveEventData(): void
    {
        try {
            (new EventData())->savePostData($this->input->getAll());
            Response::success();
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }

    /**
     * Handles the event attachment upload.
     */
    public function uploadEventAttachment(): void
    {
        try {
            Response::success($this->event->uploadAttachment());
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Utils::logError($errorMessage . " (591744)");
            Response::error($errorMessage);
        }
    }

    /**
     * Removes an event attachment.
     */
    public function removeEventAttachment(): void
    {
        $this->event->removeAttachment(xget("path"));
        Response::success();
    }

    /**
     * Removes a caldav event.
     */
    public function removeEvent(): void
    {
        $type = xget("type");

        if ($type == Calendar::LOCAL) {
            Response::send($this->event->removeEvent(xget("id"), xget("day")));
        } else if ($type == Calendar::CALDAV) {
            try {
                ClientCaldav::removeEvent(xget("calendarId"), xget("href"));
                Response::success();
            } catch (Exception $e) {
                Response::error($e->getMessage());
            }
        }

        Response::error();
    }

    /**
     * Restores an event after the user clicked the undo button.
     */
    public function restoreEvent(): void
    {
        Response::send($this->event->restoreEvent(xget("id"), xget("day")));
    }

    /**
     * Dispatches the event data for download to the browser.
     */
    public function downloadEvent(): void
    {
        $this->event->dispatchEvent(
            rcube_utils::get_input_value("id", rcube_utils::INPUT_GET),
            urldecode(rcube_utils::get_input_value("name", rcube_utils::INPUT_GET))
        );
    }

    public function setCurrentUserAttendeeStatus(): void
    {
        $eventData = new EventData();

        if (xget("type") == Calendar::CALDAV) {
            if (!$this->caldavClientEnabled) {
                Response::error("CalDAV client functionality disabled. (278144)");
            }

            if (!$eventData->loadFromVCalendar(xget("vcalendar"), xget("calendar_id"), xget("href"), xget("etag"))) {
                Response::error("Unable to load event data (382991)");
            }
        } else {
            if (!$eventData->loadFromDb(xget("id"))) {
                Response::error("Unable to load event data (483982)");
            }
        }

        $vevent = $eventData->getValue("vevent");
        $response = xget("status");
        $emails = Utils::getUserEmails();
        $modified = false;

        foreach ($emails as $email) {
            if ($eventData->setAttendanceByEmail($email, $response)) {
                $modified = true;
            }
        }

        if (!$modified) {
            Response::success();
        }

        try {
            $eventData->save();
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }

        // set the response in all other events with this uid
        foreach ($emails as $email) {
            $this->event->setAttendeeStatus($eventData->getValue("uid"), $email, $response, $eventData->getValue("id"));
        }

        // send notification to organizer
        $this->event->sendAttendeeEmailNotifications($vevent, $eventData->getValue("vevent"));

        Response::success();
    }

    /**
     * Returns the language that should be used by the fullcalendar js component based on the currently selected
     * user language. The fullcalendar language codes are different from the RC codes, we try to find the correct one.
     *
     * @return string|boolean
     */
    public function getFullCalendarLanguage(): bool|string
    {
        $available = ["af", "ar-ma", "bs", "de-at", "en-nz", "eu", "fr", "hu", "ja", "lt", "ne", "pt", "sq", "th", "vi",
            "ar-dz", "ar-sa", "ca", "de", "eo", "fa", "gl", "hy-am", "ka", "lv", "nl", "ro", "sr-cyrl", "tr", "zh-cn",
            "ar", "ar-tn", "cs", "el", "es", "fi", "he", "id", "kk", "mk", "nn", "ru", "sr", "ug", "zh-tw",
            "ar-kw", "az", "cy", "en-au", "es-us", "fr-ca", "hi", "is", "ko", "ms", "pl", "sk", "sv", "uk",
            "ar-ly", "bg", "da", "en-gb", "et", "fr-ch", "hr", "it", "lb", "nb", "pt-br", "sl", "ta-in", "uz"];

        $lan = strtolower(str_replace("_", "-", $_SESSION['language']));

        if (in_array($lan, $available)) {
            return $lan;
        }

        $lan = substr($lan, 0, 2);
        return in_array($lan, $available) ? $lan : false;
    }

    /**
     * Sends the shared calendar list.
     */
    public function getSharedCalendarList(): void
    {
        if (!$this->shareEnabled) {
            Response::error();
        }

        Response::success(["list" => $this->calendar->getSharedCalendarList()]);
    }

    /**
     * Adds a shared calendar to the calendar list of the current user.
     */
    public function addSharedCalendar(): void
    {
        if (!$this->shareEnabled) {
            Response::error();
        }

        Response::send(
            $this->calendar->addSharedCalendar((int)xget("id")),
            [
                "calendars" => $this->calendar->getCalendarList(),
                "newSharesCount" => $this->calendar->getNewSharedCalendarCount(),
            ]
        );
    }

    /**
     * Handles the url that the user clicked on to add a shared calendar to his/her calendar list. When a calendar is
     * shared, an email is sent to the user with a link that can be clicked to add the calendar directly from the
     * message view page, without needing to go and find the shared calendar in the shared calendar list.
     *
     * @return void
     */
    public function addSharedCalendarViaUrl(): void
    {
        if (!$this->shareEnabled) {
            Response::error();
        }

        // add the calendar and update the calendar list that we'll be sending to the frontend
        $code = rcube_utils::get_input_value("c", rcube_utils::INPUT_GET);

        if (!empty($code) && is_string($code)) {
            if ($this->calendar->addSharedCalendarByCode($code)) {
                // redirect to the main calendar page after adding the calendar to remove the add code from the url
                // otherwise if the user removes the calendar and refreshes the page, the calendar will be added again
                header('Location: ?_task=xcalendar', true, 302);
                exit;
            } else {
                $this->rcmail->output->show_message(
                    $this->rcmail->gettext("xcalendar.unable_add_shared_calendar"),
                    "error"
                );
            }
        }

        $this->getIndex();
    }

    /**
     * Removes a shared calendar from the user calendar list.
     */
    public function removeSharedCalendar(): void
    {
        if (!$this->shareEnabled) {
            Response::error();
        }

        Response::send(
            $this->calendar->removeSharedCalendar((int)xget("id")),
            [
                "calendars" => $this->calendar->getCalendarList(), 
                "newSharesCount" => $this->calendar->getNewSharedCalendarCount(),
            ]
        );
    }

    /**
     * Unshares a calendar.
     */
    public function unshareCalendar(): void
    {
        if (!$this->shareEnabled) {
            Response::error();
        }

        Response::send(
            $this->calendar->unshareCalendar(xget("id")),
            [
                "calendars" => $this->calendar->getCalendarList(),
                "newSharesCount" => $this->calendar->getNewSharedCalendarCount(),
            ]
        );
    }

    public function setPublishCode(): void
    {
        if (!$this->publishEnabled) {
            Response::error();
        }

        $code = '';
        Response::send(
            $this->calendar->savePublishCode((int)xget("id"), (bool)xget("full"), (bool)xget("remove"), $code),
            ["code" => $code],
            $this->rcmail->gettext("xcalendar.unable_to_save_calendar_data")
        );
    }

    /**
     * Adds a caldav sync connection when editing a calendar.
     */
    public function addSync(): void
    {
        if (!$this->caldavServerEnabled) {
            Response::error();
        }

        $result = (new \XCalendar\CalDavSync())->add(
            $this->userId,
            xget("id"),
            xget("name"),
            xget("password"),
            xget("read_only"),
            $error
        );
        Response::send((bool)$result, ["sync" => $result], $error);
    }

    /**
     * Removes a caldav sync connection when editing a calendar.
     */
    public function removeSync(): void
    {
        if (!$this->caldavServerEnabled) {
            Response::error();
        }

        Response::send((new \XCalendar\CalDavSync())->remove(xget("id"), $this->userId, $error), [], $error);
    }

     /**
     * Intercepts message loading to check if there are any itip messages; if there are, it stores their ids in $this->>itipInfo.
     * At this time the contents of the message are not available, so we analyze the ics contents; we load the message in messageBody()
     * to analyze them.
     *
     * @param array $arg
     * @return array
     */
    public function messageLoad(array $arg): array
    {
        if (!$this->messageProcessed) {
            $this->messageProcessed = true;
            $this->itipInfo = ["uid" => $arg['object']->uid, "folder" => $arg['object']->folder, "mimeIds" => []];

            // check standard calendar types and attachments added on apple, which use application/x-any
            foreach ((array)$arg['object']->mime_parts as $part) {
                if (in_array($part->mimetype, ["application/ics", "text/calendar", "text/x-vcalendar"]) ||
                    ($part->mimetype == "application/x-any" && !empty($part->filename) && str_ends_with($part->filename, ".ics"))
                ) {
                    $this->itipInfo['mimeIds'][] = $part->mime_id;
                }
            }

            // if found some ics files, add "Add events to calendar" menu item (it'll be shown or hidden in javascript depending on the
            // mime ids classified for popup display in messageBody())
            if (!empty($this->itipInfo['mimeIds'])) {
                $this->add_button(
                    [
                        'id' => 'add-events-to-calendar',
                        'name' => 'add-events-to-calendar',
                        'type' => 'link',
                        'wrapper' => 'li',
                        'command' => 'add-events-to-calendar',
                        'class' => 'icon',
                        'classact' => 'icon active',
                        'innerclass' => 'icon',
                        'label' => 'xcalendar.add_events_to_calendar',
                    ],
                    'attachmentmenu'
                );
            }
        }

        return $arg;
    }

    public function messageBody($arg)
    {
        // if the message includes a calendar invitation in which the current user is in the attendee list and the method is set to REQUEST,
        // REPLY, or CANCEL, create the invitation html and prepend it to the message -- else the ics attachment mime id will be returned in
        // $popupMimeIds and the "Add events to calendar" menu will be shown for that attachment
        $popupMimeIds = [];
        if ($html = $this->itip->getItipHtmlForEmail($this->itipInfo, $popupMimeIds)) {
            $arg['content'] = $html . $arg['content'];
        }

        $this->setJsVar("xcalendar_mimeIds", $popupMimeIds);
        $this->setJsVar("xcalendar_calendars", $this->calendar->getCalendarList(XCalendar\Calendar::LOCAL, true, true, false, true));

        return $arg;
    }

    /**
     * Ajax: This function check if there are any events available for download. If there are, exportEventsSendFile() is called
     */
    public function exportEvents(): void
    {
        Response::success([
            "hasEvents" => $this->event->hasEventsToExport(xget("calendarId")),
            "message" => $this->gettext("xcalendar.export_no_events"),
        ]);
    }

    /**
     * This function creates the ics file with events and triggers download.
     *
     * @param $calendarId
     */
    public function exportEventsSendFile($calendarId): void
    {
        if ($data = $this->event->exportEvents($calendarId, $calendarName)) {
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . Utils::ensureFileName($calendarName) . ".ics\"");
        }

        exit($data ?: "Error exporting calendar events.");
    }

    /**
     * Imports events from file. It supports ical files and zip files.
     */
    public function importEvents(): void
    {
        $calendarId = rcube_utils::get_input_value("calendarId", rcube_utils::INPUT_POST);
        $result = ["success" => 0, "error" => 0];

        try {
            if (empty($_FILES['file']['tmp_name'])) {
                throw new Exception("xcalendar error: import file not uploaded (importEvents 79456)");
            }

            if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                throw new Exception("This file has not been properly uploaded.");
            }

            // if it's a zip file, unzip the contents and import all the files contained in it
            if ($_FILES['file']['type'] == "application/zip" ||
                strtolower(Utils::ext($_FILES['file']['name'])) === "zip"
            ) {
                if (!class_exists("ZipArchive", false)) {
                    throw new Exception("xcalendar error: ZipArchive class not available (importEvents 79457)");
                }

                $zip = new ZipArchive();
                if ($zip->open($_FILES['file']['tmp_name']) !== true) {
                    throw new Exception("xcalendar error: cannot open zip archive (importEvents 79458)");
                }

                $found = false;
                $totalUncompressed = 0;
                $maxTotal = 100 * 1024 * 1024; // 100 MB decompressed across the archive
                $maxEntries = 1000;

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    if ($i >= $maxEntries) {
                        throw new Exception("xcalendar error: too many entries in archive (importEvents 79464)");
                    }

                    $name = $zip->getNameIndex($i);
                    if (Utils::ext($name) !== "ics") {
                        continue;
                    }

                    $stat = $zip->statIndex($i);
                    if ($stat && $stat['size'] > 5 * 1024 * 1024) {
                        continue;
                    }

                    $totalUncompressed += $stat ? $stat['size'] : 0;
                    if ($totalUncompressed > $maxTotal) {
                        throw new Exception("xcalendar error: archive too large (importEvents 79465)");
                    }

                    $found = true;

                    // read entry contents directly from the archive — no extraction to disk, so a
                    // crafted entry name like ../../x.ics has no filesystem path to traverse
                    $text = $zip->getFromIndex($i);

                    if ($text === false || $text === "") {
                        continue;
                    }

                    if ($array = $this->event->importEvents($text, $calendarId)) {
                        $result['success'] += $array['success'];
                        $result['error'] += $array['error'];
                    }
                }

                $zip->close();

                if (!$found) {
                    throw new Exception("xcalendar error: no ics files in archive (importEvents 79460)");
                }
            } else {
                // it's not an archive file, import it
                if (!($text = file_get_contents($_FILES['file']['tmp_name']))) {
                    throw new Exception("xcalendar error: cannot read file contents (importEvents 79462)");
                }
                $result = $this->event->importEvents($text, $calendarId);
            }

            if (!is_array($result) || !$result['success']) {
                throw new Exception(); // errors logged inside importEvents()
            }

            $message = $this->gettext($result['error'] ? "events_imported_with_errors" : "events_imported_successfully");
            $success = true;

        } catch (Exception $e) {
            $success = false;
            $message = $this->gettext("error_importing_events");

            if (!($error = $e->getMessage())) {
                $error = "Cannot import events from file (79463)";
            }

            Utils::logError($error . " (28845)");
        }

        Response::send($success, [], $message);
    }

    public function getSearchPageData(): void
    {
        // we're storing unchecked calendars in the session; this allows for newly added calendars to be searched by default
        $calendars = [];
        if (!isset($_SESSION['xcalendar_search_calendars_unchecked']) || !is_array($_SESSION['xcalendar_search_calendars_unchecked'])) {
            $_SESSION['xcalendar_search_calendars_unchecked'] = [];
        }

        // get the calendars and return the ones of which the current user can see the details
        foreach ($this->calendar->getCalendarList(Calendar::LOCAL, true, true) as $val) {
            if (!empty($val['permissions']->see_details)) {
                $calendars[] = [
                    "id" => $val['id'],
                    "name" => $val['name'],
                    "checked" => !in_array($val['id'], $_SESSION['xcalendar_search_calendars_unchecked'])
                ];
            }
        }

        // if there's only one calendar, the calendar selection will be hidden; let's make sure the calendar is checked
        if (count($calendars) == 1) {
            $calendars[0]['checked'] = true;
        }

        // get the range restriction setting
        $rangeYearLimit = (int)$this->rcmail->config->get("xcalendar_search_range_year_limit", 1);
        if ($rangeYearLimit < 0) {
            $rangeYearLimit = 1;
        }

        // get search ranges
        try {
            $today = new DateTime();
            $startDate = new DateTime($_SESSION['xcalendar_search_start_date'] ?? "");
            $endDate = new DateTime($_SESSION['xcalendar_search_end_date'] ?? "+6 months");

            if ($rangeYearLimit &&
                ($today->diff($startDate)->days > $rangeYearLimit * 365 || $today->diff($endDate)->days > $rangeYearLimit * 365)
            ) {
                throw new Exception();
            }
        } catch (Exception) {
            $startDate = new DateTime();
            $endDate = new DateTime("+6 months");
        }

        Response::success([
            "calendars" => $calendars,
            "startDate" => date($this->format->getDateFormat(), $startDate->getTimestamp()),
            "endDate" => date($this->format->getDateFormat(), $endDate->getTimestamp()),
            "rangeYearLimit" => $rangeYearLimit,
        ]);
    }

    public function searchEvents(): void
    {
        if (!($text = trim(xget("text")))) {
            Response::error($this->gettext("xcalendar.specify_text_to_search"));
        }

        $calendars = xget("calendars");
        is_array($calendars) || ($calendars = []);
        $calendarIds = [];
        $_SESSION['xcalendar_search_calendars_unchecked'] = [];

        foreach ($calendars as $calendar) {
            if (!empty($calendar['id'])) {
                if (empty($calendar['checked'])) {
                    $_SESSION['xcalendar_search_calendars_unchecked'][] = $calendar['id'];
                } else {
                    $calendarIds[] = $calendar['id'];
                }
            }
        }

        if (empty($calendarIds)) {
            Response::error($this->gettext("xcalendar.specify_at_least_one_calendar"));
        }

        $_SESSION['xcalendar_search_start_date'] = xget("startDate");
        $_SESSION['xcalendar_search_end_date'] = xget("endDate");

        Response::success(
            $this->event->searchEvents(
                $text,
                $calendarIds,
                $_SESSION['xcalendar_search_start_date'],
                $_SESSION['xcalendar_search_end_date'],
                xget("page")
            )
        );
    }

    public function getTodaysAgenda(): void
    {
        $showMore = false;
        $now = time() + $this->getTimezoneDifference();
        $startTime = date("Y-m-d 00:00:00", $now);
        $endTime = date("Y-m-d 00:00:00", strtotime("+ 1 day", $now));
        $today = date("Y-m-d");

        // get events from all the calendars
        $events = array_merge(
            $this->event->getLocalEventList($startTime, $endTime),
            $this->event->getRemoteEventList($startTime, $endTime)
        );

        // remove the events from other days (caldav repeated events will have the original event included)
        foreach ($events as $key => $event) {
            if (substr($event['start'], 0, 10) != $today) {
                unset($events[$key]);
            }
        }

        // sort the events by time
        usort($events, function($a, $b) {
            try {
                return new DateTime($a['start']) <=> new DateTime($b['start']);
            } catch (Exception) {
                return 0;
            }
        });

        if (empty($events)) {
            $html = html::div(["class" => "content-container"], $this->gettext("xcalendar.no_events_today"));
        } else {
            $table = new html_table(["cols" => 3]);
            $count = 0;

            foreach ($events as $event) {
                $table->add([], html::span(
                    [
                        "class" => "event-color-box",
                        "style" => "background-color:" . $event['backgroundColor'],
                        "title" => $event['calendar'],
                    ],
                    ""
                ));
                $table->add([], html::span(
                    [
                        "class" => "event-date",
                        "title" => $event['calendar'],
                    ],
                    $event['allDay'] ? $this->gettext("all_day") : date($this->format->getTimeFormat(), strtotime($event['start']))
                ));
                $table->add([], html::a(
                    [
                        "class" => "event-title",
                        "href" => Utils::getUrl("?_task=xcalendar&view=agendaDay&scroll_time=" . substr($event['start'], 11)),
                        "title" => $event['calendar'],
                    ],
                    htmlentities($event['title'])
                ));

                $count++;

                if ($count >= 10) {
                    $showMore = true;
                    break;
                }
            }
            $html = html::div(["class" => "content-container"], $table->show());
        }

        if ($showMore) {
            $html .= html::div(
                ["class" => "bottom-panel bottom-links"],
                html::a(
                    ["href" => Utils::getUrl("?_task=xcalendar&view=agenda")],
                    $this->gettext("xcalendar.sidebar_more")
                )
            );
        }

        Response::success($html);
    }

    /**
     * Handles the regular ajax checks the calendar frontend sends to check if alarm notifications should be shown.
     */
    public function getPopupAlarms(): void
    {
        $list = [];
        $alarms = $this->event->getAlarms(ALARM_TYPE_POPUP);

        if (!empty($alarms)) {
            foreach ($alarms as $alarm) {
                $list[$alarm['id']] = $this->view("elastic", "xcalendar.event_alarm", [
                    'id'          => $alarm['id'],
                    'title'       => rcube::Q($alarm['title']),
                    'description' => rcube::Q($alarm['description']),
                    'snooze'      => $alarm['snooze'],
                ]);
            }
        }

        Response::success([
            "list" => $list,
            "count" => count($list),
            "sound" => $this->rcmail->config->get("xcalendar_alarm_sound")
        ]);
    }

    /**
     * Handles the alarm snooze ajax request.
     */
    public function snooze(): void
    {
        Response::success(
            $this->event->snooze($this->input->get("alarmId"), $this->input->get("snooze"))
        );
    }

    /**
     * Hook: intercepts smtp login when cron sends messages and sets the smtp username/password if specified in the
     * plugin's config.
     *
     * @param array $arg
     * @return array
     */
    public function smtpConnect(array $arg): array
    {
        if ($user = $this->rcmail->config->get("xcalendar_smtp_user")) {
            $arg['smtp_user'] = $user;
        }

        if ($password = $this->rcmail->config->get("xcalendar_smtp_pass")) {
            $arg['smtp_pass'] = $password;
        }

        return $arg;
    }

    public function runCron(): void
    {
        $sentCount = 0;
        $notSentCount = 0;
        echo "Roundcube Plus xcalendar cron (" . date("Y-m-d H:i:s") . ") | ";

        try {
            // verify the configuration
            if (!$this->rcmail->config->get("xcalendar_email_event_notifications")) {
                throw new Exception("The event notification functionality is disabled in the configuration.");
            }

            if ($this->rcmail->db->db_provider != "mysql" && $this->rcmail->db->db_provider != "postgres") {
                throw new Exception("Incompatible database type: " . $this->rcmail->db->db_provider);
            }

            // save the cron execution time and limit execution to once every 10 seconds to protect against DoS attacks
            // this still allows the admin to execute the url manually if need be
            $now = date("Y-m-d H:i:s");

            if ($this->db->value("value", "system", ["name" => "xcalendar_cron"]) === null) {
                $this->db->insert("system", ["name" => "xcalendar_cron", "value" => $now]);
            } else {
                $result = $this->db->query(
                    "UPDATE {system} SET value = ? WHERE name = 'xcalendar_cron' AND value < ?",
                    [$now, date("Y-m-d H:i:s", time() - 10)]
                );

                if ($this->db->affectedRows($result) < 1) {
                    exit("Cron skipped (executed less than 10 seconds ago).\n");
                }
            }

            // delay the cron execution for the number of seconds specified in the config
            $sleep = $this->rcmail->config->get("xcalendar_cron_execution_delay");
            if (is_numeric($sleep) && $sleep > 0 && $sleep < 30) {
                sleep($sleep);
            }

            // set the hook, so we can use the smtp username/password specified in the config when sending messages
            $this->add_hook("smtp_connect", [$this, "smtpConnect"]);

            $configDateFormat = $this->rcmail->config->get('date_format', 'Y-m-d');
            $configTimeFormat = $this->rcmail->config->get('time_format', 'H:i');
            $language = $this->rcmail->config->get("language");
            set_time_limit(0);

            // get and verify the delivery pause value
            $pause = $this->rcmail->config->get("xcalendar_email_event_notification_delivery_pause");
            if (!is_numeric($pause) || $pause < 0) {
                $pause = false;
            }

            try {
                $currentTime = new DateTime("now", new DateTimeZone("UTC"));
                // for dev purposes (use alarm_time in db) used in getAlarms() and below, after sending email:
                // $currentTime = new DateTime("2025-09-10 9:50:00", new \DateTimeZone("UTC"));
            } catch (Exception) {
                throw new Exception("Invalid current time");
            }

            // First get all the alarm records that should have emails sent during this minute, then check if this record
            // has not been processed yet by another copy of the cron job running concurrently. The transaction and
            // SELECT FOR UPDATE lock the record until we UPDATE it and set its processing_started flag, this way the
            // processing is thread-safe. It needs to be because several cron jobs from different containers could access
            // this function at the same time.
            foreach ($this->event->getAlarms(ALARM_TYPE_EMAIL, $currentTime) as $event) {
                $record = false;
                $this->db->beginTransaction();

                $st = $this->db->query(
                    "SELECT processing_started FROM {xcalendar_alarms} WHERE id = ? AND processing_started = 0 FOR UPDATE",
                    [$event['alarm_id']]
                );

                if ($st && ($record = $st->fetch(PDO::FETCH_ASSOC))) {
                    $this->db->query("UPDATE {xcalendar_alarms} SET processing_started = 1 WHERE id = ?", [$event['alarm_id']]);
                }

                $this->db->commit();

                if (empty($record)) {
                    continue;
                }

                // get the user's email
                $email = $this->getIdentityEmail($event['user_id']);

                if (!strpos($email, "@")) {
                    continue;
                }

                // get user's date/time format and language
                $format = $configDateFormat . ($event['all_day'] ? "" : " " . $configTimeFormat);
                $user = $this->db->row("users", ["user_id" => $event['user_id']]);

                if (is_array($user)) {
                    if (($pref = unserialize($user['preferences'])) && is_array($pref) && isset($pref['date_format']) &&
                        isset($pref['time_format'])
                    ) {
                        $format = $pref['date_format'] . ($event['all_day'] ? "" : " " . $pref['time_format']);
                    }

                    // if the currently loaded language is not the same as the user's language, load the strings from the user's language
                    // so the mail is sent in the user's language
                    if ($user['language'] != $language) {
                        $labels = $this->rcmail->read_localization(__DIR__ . "/localization", $user['language']);
                        if (!empty($labels)) {
                            $texts = [];
                            foreach ($labels as $key => $val) {
                                $texts["xcalendar.$key"] = $val;
                            }
                            $this->rcmail->load_language($user['language'], $texts);
                            $language = $user['language'];
                        }
                    }
                }

                $start = date($format, strtotime($event['start']));
                $end = date($format, strtotime($event['end']));

                $html = $this->view(
                    "elastic",
                    "xcalendar.email_notification",
                    [
                        "title" => rcube::Q($event['title']),
                        "start" => rcube::Q($start . " ({$event['timezone_start']})"),
                        "end" => rcube::Q($end . " ({$event['timezone_end']})"),
                        "location" => $event['location'] ? rcube::Q($event['location']) : "-",
                        "description" => $event['description'] ? nl2br(rcube::Q($event['description'])) : "-",
                        "url" => empty($event['url']) ? "-" : html::a(
                            ['href' => $event['url'], 'target' => '_blank', 'rel' => 'noopener'],
                            rcube::Q($event['url'])),
                    ],
                    false
                );

                $subject = $this->rcmail->gettext([
                    "name" => "xcalendar.email_notification_subject",
                    "vars" => ["t" => $event['title'], "d" => $start, "z" => $event['timezone_start']]
                ]);

                if (\XFramework\Plugin::sendHtmlEmail($email, $subject, $html, $error, $email)) {
                    $sentCount++;
                } else {
                    $notSentCount++;
                }

                // if it's a repeated event, we need to set processing_started to 0 to enable next week/month/year
                // processing, but to prevent other threads from executing it during this minute, we also set alarm_time
                // to midnight tomorrow -- this is accounted for in getAlarms() when retrieving repeated events
                if (!empty($event['repeat_rule'])) {
                    $this->db->query("UPDATE {xcalendar_alarms} SET processing_started = 0, alarm_time = ? ".
                        "WHERE id = ?",
                        [
                            (clone $currentTime)->modify('+1 day')->format('Y-m-d 00:00:00'),
                            $event['alarm_id'],
                        ]
                    );
                }

                if ($pause) {
                    usleep($pause * 1000);
                }
            }
        } catch (Exception $e) {
            exit($e->getMessage() . "\n");
        }

        exit("Event notifications sent: $sentCount | Not sent: $notSentCount\n");
    }

    /**
     * Intercepts message composing to check if it was invoked by the user trying to send an event. Get the event id,
     * export and attach the event.
     *
     * @param array $arg
     * @return array
     */
    public function messageCompose(array $arg): array
    {
        if (empty($arg['param']['xcalendar_event_type'])) {
            return $arg;
        }

        if ($arg['param']['xcalendar_event_type'] == Calendar::CALDAV) {
            $subject = $arg['param']['xcalendar_event_title'];
            $vcalendar = $arg['param']['xcalendar_event_vcalendar'];
        } else if ($arg['param']['xcalendar_event_type'] == Calendar::LOCAL && !empty($arg['param']['xcalendar_event_id'])) {
            $eventData = new EventData();
            if (!$eventData->loadFromDb($arg['param']['xcalendar_event_id'])) {
                return $arg;
            }

            // check calendar permissions
            $permissions = Permission::getCalendarPermissions(
                $eventData->getValue("calendar_id"),
                $this->rcmail->get_user_id(),
                $this->rcmail->get_user_email()
            );

            if (empty($permissions->see_details) || empty($permissions->edit_events)) {
                return $arg;
            }

            // get the timezones to add to vcalendar
            if ((int)$eventData->getValue("all_day")) {
                $vtimezone = false;
            } else {
                $timezoneStart = $eventData->getValue("timezone_start");
                $timezoneEnd = $eventData->getValue("timezone_end");
                $vtimezone = Timezone::getVTimezone($timezoneStart);

                if ($timezoneStart != $timezoneEnd) {
                    $vtimezone .= Timezone::getVTimezone($timezoneEnd);
                }
            }

            $subject = $eventData->getValue("title");
            $vcalendar = Event::wrapInVCalendar($eventData->getValue("vevent"), $vtimezone);
        } else {
            return $arg;
        }

        // save vevent to file and add subject and attachment to email
        $filepath = tempnam($this->rcmail->config->get("temp_dir"), "xcalendar");

        if (file_put_contents($filepath, $vcalendar)) {
            $arg['param']['subject'] = $subject;
            $arg['attachments'][] = [
                "path" => $filepath,
                "size" => filesize($filepath),
                "name" => Utils::ensureFilename($subject) . ".ics",
                "mimetype" => "text/calendar",
            ];
        }

        return $arg;
    }

    /**
     * Checks if cron is running: checks the date of last run, it should be within the last 2 minutes
     */
    private function checkCron(): bool
    {
        $date = $this->db->value("value", "system", ["name" => "xcalendar_cron"]);
        return $date && strtotime($date) > time() - 120;
    }

    private function isEmailNotificationEnabled(?string &$error = ""): bool
    {
        $result = (bool)$this->rcmail->config->get("xcalendar_email_event_notifications");
        $error = false;

        if ($result) {
            if ($this->db->getProvider() == "sqlite") {
                $result = false;
                $error = "Error: The SQLite database type is not compatible with the email notification functionality.";
            } else if (!$this->checkCron()) {
                $result = false;
                $error = $this->rcmail->gettext("xcalendar.email_notification_cron_error");
            }
        }

        return $result;
    }
}
