<?php
namespace XCalendar;

require_once (defined('RCUBE_INSTALL_PATH') && file_exists(RCUBE_INSTALL_PATH . 'plugins/xframework/common/Utils.php'))
    ? RCUBE_INSTALL_PATH . 'plugins/xframework/common/Utils.php'
    : __DIR__ . '/../../xframework/common/Utils.php';
require_once (defined('RCUBE_INSTALL_PATH') && file_exists(RCUBE_INSTALL_PATH . 'plugins/xframework/common/Format.php'))
    ? RCUBE_INSTALL_PATH . 'plugins/xframework/common/Format.php'
    : __DIR__ . '/../../xframework/common/Format.php';
require_once (defined('RCUBE_INSTALL_PATH') && file_exists(RCUBE_INSTALL_PATH . 'plugins/xcalendar/program/CalDavLog.php'))
    ? RCUBE_INSTALL_PATH . 'plugins/xcalendar/program/CalDavLog.php'
    : __DIR__ . '/CalDavLog.php';
require_once (defined('RCUBE_INSTALL_PATH') && file_exists(RCUBE_INSTALL_PATH . 'plugins/xcalendar/vendor/autoload.php'))
    ? RCUBE_INSTALL_PATH . 'plugins/xcalendar/vendor/autoload.php'
    : __DIR__ . '/../vendor/autoload.php';

use Sabre\VObject\ITip;
use XFramework\Format;

class CalDavIMip extends \Sabre\CalDAV\Schedule\IMipPlugin
{
    private \rcube $rcmail;
    private $fromCaldav;

    function __construct($senderEmail = '', $fromCaldav = false)
    {
        parent::__construct(str_replace('mailto:', '', $senderEmail));
        $this->rcmail = xrc();
        $this->fromCaldav = $fromCaldav;
    }

    /**
     * Event handler for the 'schedule' event.
     */
    public function schedule(ITip\Message $iTipMessage, $modifiedEvent = false): void
    {
        // Not sending any emails if the system considers the update insignificant.
        if (!$iTipMessage->significantChange) {
            if (!$iTipMessage->scheduleStatus) {
                $iTipMessage->scheduleStatus = '1.0;We got the message, '.
                    'but it\'s not significant enough to warrant an email';
            }
            return;
        }

        if (parse_url($iTipMessage->sender, PHP_URL_SCHEME) !== 'mailto' ||
            parse_url($iTipMessage->recipient, PHP_URL_SCHEME) !== 'mailto'
        ) {
            return;
        }

        $recipient = substr($iTipMessage->recipient, 7);
        $senderEmail = substr($iTipMessage->sender, 7);
        $summary = $iTipMessage->message->VEVENT->SUMMARY;

        // add the sender name (CN property) from the itip message to $sender
        $sender = $iTipMessage->senderName ? "$iTipMessage->senderName <$senderEmail>" : $senderEmail;

        if ($caldavUsername = $this->getCaldavUsername()) {
            // MacOS Calendar uses the caldav username as the CN property -- let's check for it and set the sender to
            // email only
            if ($iTipMessage->senderName == $caldavUsername) {
                $sender = $senderEmail;
            }

            // outlook.com shows the event organizer CN property as the email sender and if it's the caldav username it
            // shows that as the sender name, which is nonsense -- let's check for it and set that CN value to an empty
            // value if the actual senderName is also the same as the caldav username or to senderName if it's not
            if (isset($iTipMessage->message->VEVENT->ORGANIZER->parameters['CN']) &&
                $caldavUsername == $iTipMessage->message->VEVENT->ORGANIZER->parameters['CN']->getValue()
            ) {
                $cn = $iTipMessage->senderName == $caldavUsername ? '' : $iTipMessage->senderName;
                $iTipMessage->message->VEVENT->ORGANIZER->parameters['CN']->setValue($cn);
            }
        }

        if ($iTipMessage->recipientName && $iTipMessage->recipientName != $recipient) {
            $recipient = "$iTipMessage->recipientName <$recipient>";
        }

        $this->caldavLog("[PREPARING IMIP EMAIL] From: $sender, To: $recipient");

        switch (strtoupper($iTipMessage->method)) {
            case 'REPLY':
                $subject = $this->rcmail->gettext('xcalendar.itip_email_subject_reply') . ' ' . $summary;
                break;
            case 'REQUEST':
                $subject = $this->rcmail->gettext('xcalendar.itip_email_subject_request' .
                        ($modifiedEvent ? '_modified' : '')) . ' ' . $summary;
                break;
            case 'CANCEL':
                $subject = $this->rcmail->gettext('xcalendar.itip_email_subject_cancel') . ' ' . $summary;
                break;
            default:
                $this->caldavLog("[UNKNOWN METHOD] $iTipMessage->method");
                return;
        }

        // if called from caldav/index.php
        if (!$this->senderEmail) {
            // set the smtp username and password into the config vars that will be used by rcmail to send the mail
            if ($smtpUser = $this->rcmail->config->get('xcalendar_smtp_user')) {
                $this->rcmail->config->set('smtp_user', $smtpUser);
            }

            if ($smtpPassword = $this->rcmail->config->get('xcalendar_smtp_pass')) {
                $this->rcmail->config->set('smtp_pass', $smtpPassword);
            }

            // set the sender email from config - this is required, since there's no logged in user at this point
            if (!($this->senderEmail = $this->rcmail->config->get('xcalendar_imip_sender_email'))) {
                $this->caldavLog('ERROR: xcalendar_imip_sender_email NOT SPECIFIED IN CONFIG');
                return;
            }
        }

        // change the product ID
        $iTipMessage->message->PRODID = Event::getICalProdId();

        // serialize the event (creates ics text wrapped in VCALENDAR)
        $serializedItipMessage = $iTipMessage->message->serialize();

        // get the timezones (if exist) and add them to the ics text - if this is not done, Outlook will display the
        // wrong time of the event in the small calendar shown above the email message
        $timezones = [];

        if (!empty($iTipMessage->message->VEVENT->DTSTART->parameters['TZID']) &&
            ($value = $iTipMessage->message->VEVENT->DTSTART->parameters['TZID']->getValue())
        ) {
            $timezones[$value] = Timezone::getVTimezone($value);
        }

        if (!empty($iTipMessage->message->VEVENT->DTEND->parameters['TZID']) &&
            ($value = $iTipMessage->message->VEVENT->DTEND->parameters['TZID']->getValue()) &&
            !array_key_exists($value, $timezones)
        ) {
            $timezones[$value] = Timezone::getVTimezone($value);
        }

        if (!empty($timezones)) {
            $serializedItipMessage = str_replace(
                'BEGIN:VEVENT',
                implode('', $timezones) . 'BEGIN:VEVENT',
                $serializedItipMessage
            );
        }

        // create and send the notification email
        $parts = explode('@', $senderEmail);
        $domain = count($parts) === 2 ? "@$parts[1]" : "";

        $message = new \Mail_mime([
            'eol' => $this->rcmail->config->header_delimiter(),
            'head_encoding' => 'quoted-printable',
            'html_encoding' => 'quoted-printable',
            'text_encoding' => 'base64',
            'head_charset' => RCUBE_CHARSET,
            'html_charset' => RCUBE_CHARSET,
            'text_charset' => RCUBE_CHARSET,
        ]);
        $message->headers([
            'Subject' => $subject,
            'Reply-To' => $sender,
            'From' => $sender,
            'To' => $recipient,
            'Date' => date('r'),
            'Message-ID' => '<' . uniqid('xcalendar_attendee_', true) . $domain . '>',
        ]);

        // the ics text must be included as one of the message text parts (not just attachment) or else most email programs won't show
        // the accept/decline/tentative buttons
        $message->setCalendarBody($serializedItipMessage, false, false, $iTipMessage->method, RCUBE_CHARSET, '7bit');
        $message->setHTMLBody($this->createHtml($subject, $iTipMessage));
        $message->setTXTBody($this->createText($subject, $iTipMessage));
        $message->addAttachment($serializedItipMessage, 'application/ics', 'invite.ics', false, 'base64');

        $this->caldavLog("[SENDING EMAIL] From: $sender, To: $recipient, Subject: $subject");

        $error = '';

        if (!$this->rcmail->deliver_message($message, $this->senderEmail, $recipient, $error)) {
            $this->caldavLog("[ERROR SENDING EMAIL] $error");
        }

        $iTipMessage->scheduleStatus = '1.1; Scheduling message is sent via iMip';
    }

    /**
     * Returns the caldav username (the 8 character random string) if called from caldav. Otherwise false.
     * @return false|string
     */
    private function getCaldavUsername(): bool|string
    {
        if (!empty($_SERVER['PHP_AUTH_DIGEST']) && is_array($values = explode(", ", $_SERVER['PHP_AUTH_DIGEST']))) {
            foreach ($values as $value) {
                if (str_starts_with($value, 'username=')) {
                    return substr($value, 10, -1);
                }
            }
        }

        return false;
    }

    private function caldavLog($text): void
    {
        if ($this->fromCaldav) {
            CalDavLog::log($text);
        }
    }

    private function createText($subject, $iTipMessage): string
    {
        $rows = [];
        $format = new Format();

        if (strlen($iTipMessage->message->VEVENT->DTSTART) > 8) {
            $rows[] = $this->createTextRow(
                'xcalendar.start',
                $format->formatDateTime(strtotime($iTipMessage->message->VEVENT->DTSTART))
            );
            $rows[] = $this->createTextRow(
                'xcalendar.end',
                $format->formatDateTime(strtotime($iTipMessage->message->VEVENT->DTEND))
            );
        } else {
            $rows[] = $this->createTextRow(
                'xcalendar.start',
                $format->formatDate(strtotime($iTipMessage->message->VEVENT->DTSTART))
            );
            $rows[] = $this->createTextRow(
                'xcalendar.end',
                $format->formatDate(strtotime($iTipMessage->message->VEVENT->DTEND))
            );
        }

        if (!empty($iTipMessage->message->VEVENT->LOCATION)) {
            $rows[] = $this->createTextRow(
                'xcalendar.location',
                \rcube::Q($iTipMessage->message->VEVENT->LOCATION)
            );
        }

        if (!empty($iTipMessage->message->VEVENT->DESCRIPTION)) {
            $rows[] = $this->createTextRow(
                'xcalendar.description',
                \rcube::Q($iTipMessage->message->VEVENT->DESCRIPTION)
            );
        }

        if (!empty($iTipMessage->message->VEVENT->URL)) {
            $rows[] = $this->createTextRow(
                'xcalendar.url',
                (string)$iTipMessage->message->VEVENT->URL
            );
        }

        if (!empty($iTipMessage->message->VEVENT->ORGANIZER)) {
            $organizer = str_replace('mailto:', '', $iTipMessage->message->VEVENT->ORGANIZER);
            if (!empty($iTipMessage->message->VEVENT->ORGANIZER->parameters['CN'])) {
                $organizer = $iTipMessage->message->VEVENT->ORGANIZER->parameters['CN'] . " <$organizer>";
            }
            $rows[] = $this->createTextRow('xcalendar.organizer', \rcube::Q($organizer));
        }

        if (!empty($iTipMessage->message->VEVENT->ATTENDEE)) {
            $attendees = [];
            foreach ($iTipMessage->message->VEVENT->ATTENDEE as $email) {
                $attendees[] = str_replace('mailto:', '', $email);
            }

            $rows[] = $this->createTextRow('xcalendar.attendees', \rcube::Q(implode(', ', $attendees)));
        }

        return $subject . "\n\n" . implode("\n\n", $rows);
    }

    private function createHtml($subject, $iTipMessage): array|bool|string
    {
        $rows = [];
        $format = new Format();
        $html = file_get_contents(__DIR__ . '/../skins/elastic/templates/email_itip.html');

        if (strlen($iTipMessage->message->VEVENT->DTSTART) > 8) {
            $rows[] = $this->createHtmlRow(
                'xcalendar.start',
                $format->formatDateTime(strtotime($iTipMessage->message->VEVENT->DTSTART))
            );
            $rows[] = $this->createHtmlRow(
                'xcalendar.end',
                $format->formatDateTime(strtotime($iTipMessage->message->VEVENT->DTEND))
            );
        } else {
            $rows[] = $this->createHtmlRow(
                'xcalendar.start',
                $format->formatDate(strtotime($iTipMessage->message->VEVENT->DTSTART))
            );
            $rows[] = $this->createHtmlRow(
                'xcalendar.end',
                $format->formatDate(strtotime($iTipMessage->message->VEVENT->DTEND))
            );
        }

        if (!empty($iTipMessage->message->VEVENT->LOCATION)) {
            $rows[] = $this->createHtmlRow(
                'xcalendar.location',
                \rcube::Q($iTipMessage->message->VEVENT->LOCATION)
            );
        }

        if (!empty($iTipMessage->message->VEVENT->DESCRIPTION)) {
            $rows[] = $this->createHtmlRow(
                'xcalendar.description',
                \rcube::Q($iTipMessage->message->VEVENT->DESCRIPTION)
            );
        }

        if (!empty($iTipMessage->message->VEVENT->URL)) {
            $rows[] = $this->createHtmlRow(
                'xcalendar.url',
                \html::a(['href' => $iTipMessage->message->VEVENT->URL], \rcube::Q($iTipMessage->message->VEVENT->URL))
            );
        }

        if (!empty($iTipMessage->message->VEVENT->ORGANIZER)) {
            $organizer = str_replace('mailto:', '', $iTipMessage->message->VEVENT->ORGANIZER);
            if (!empty($iTipMessage->message->VEVENT->ORGANIZER->parameters['CN'])) {
                $organizer = $iTipMessage->message->VEVENT->ORGANIZER->parameters['CN'] . " <$organizer>";
            }
            $rows[] = $this->createHtmlRow('xcalendar.organizer', \rcube::Q($organizer));
        }

        if (!empty($iTipMessage->message->VEVENT->ATTENDEE)) {
            $attendees = [];
            foreach ($iTipMessage->message->VEVENT->ATTENDEE as $email) {
                $attendees[] = str_replace('mailto:', '', $email);
            }

            $rows[] = $this->createHtmlRow('xcalendar.attendees', \rcube::Q(implode(', ', $attendees)));
        }

        return str_replace(['[~subject~]', '[~rows~]'], [\rcube::Q($subject), implode('', $rows)], $html);
    }

    private function createHtmlRow(string $label, string $value): string
    {
        return html::tag(
            'tr',
            null,
            html::tag('td', ['class' => 'label'], \rcube::Q($this->rcmail->gettext($label))) .
            html::tag('td', null, $value)
        );
    }

    private function createTextRow(string $label, string $value): string
    {
        return $this->rcmail->gettext($label) . "\n" . $value;
    }
}
