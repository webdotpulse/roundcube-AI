<?php
namespace XCalendar;

use XFramework\DatabaseGeneric;
use XFramework\Utils;
use XFramework\Format;

class Itip
{
    private \rcube $rcmail;
    private DatabaseGeneric $db;
    private Format $format;
    private Event $event;
    private ?int $userId;

    public function __construct(Event $event)
    {
        $this->rcmail = xrc();
        $this->db = xdb();
        $this->format = xformat();
        $this->event = $event;
        $this->userId = $this->rcmail->get_user_id();
    }

    /**
     * Processes the ajax call to respond to an event invitation (clicking the buttons Accept, Tentative, Decline in the
     * email message in Roundcube.) It pulls the itip file from the message based on $_POST variables, decodes it,
     * changes the response in the ics file and sends the response to the organizer (if needed). It also adds or removes
     * the event to the calendar (if needed.)
     */
    public function processResponse(): void
    {
        try {
            // check the response (ACCEPTED, TENTATIVE, DECLINED)
            if (!EventData::isAttendanceResponseValid(
                $response = \rcube_utils::get_input_value("response", \rcube_utils::INPUT_POST))
            ) {
                throw new \Exception("Invalid response.");
            }

            // decrypt the identifying message data
            if (!($data = json_decode(
                $this->rcmail->decrypt(\rcube_utils::get_input_value("data", \rcube_utils::INPUT_POST)))
            )) {
                throw new \Exception("Invalid message data.");
            }

            // create a message and pull out its appropriate ics part
            if (!($message = new \rcube_message($data->uid, $data->folder)) ||
                !($ics = $message->get_part_body($data->mimeId))
            ) {
                throw new \Exception("Cannot retrieve itip.");
            }

            // check the calendar ID (only if not 0, which means don't add to calendar)
            if (($calendarId = \rcube_utils::get_input_value("calendarId", \rcube_utils::INPUT_POST)) &&
                !$this->db->row("xcalendar_calendars", ["id" => $calendarId, "user_id" => $this->userId, "type" => Calendar::LOCAL])
            ) {
                throw new \Exception("Invalid calendar ID (937483)");
            }

            // get the data from ics
            $array = $this->event->vEventToDataArray($ics);

            if (empty($array)) {
                throw new \Exception("No vevents in ics.");
            }

            // process only the first event, normally there won't be any more than one
            $data = $array[0];
            $userEmail = "";

            // check the essential data
            if (empty($data['uid'])) {
                throw new \Exception("Event UID empty.");
            }

            if (empty($data['attendees'])) {
                throw new \Exception("Event attendee list empty.");
            }

            // find the user email used in the ics
            foreach (Event::getCurrentUserEmails(false) as $email) {
                if (array_key_exists($email, $data['attendees'])) {
                    $userEmail = $email;
                    break;
                }
            }

            if (empty($userEmail)) {
                throw new \Exception("User email not found in ics.");
            }

            $notifications = [];
            $eventData = new EventData();

            // load the existing event and save the fact that it exists
            $exists = $eventData->loadFromDb(["vevent_uid" => $data['uid'], "user_id" => $this->userId], true);

            // update the object's data with the data from ics
            $eventData->importData($data);

            // backup the vevent string at this point so we can use it to send notifications
            $vevent = $eventData->createVEvent($eventData->getData());

            // set the attendance for the current user
            $eventData->setAttendanceByEmail($userEmail, $response);

            // if 'add to calendar' is set, specify (overwrite) the calendar
            if ($calendarId) {
                $eventData->setValue("calendar_id", $calendarId);
            }

            // if event exists but declined attendance, remove the event (but keep it in the db so if the user opens the
            // email message again she'll see that she'd declined the invite)
            if ($exists) {
                if ($response == "DECLINED") {
                    $eventData->setValue("removed_at", date("Y-m-d H:i:s"));
                    $notifications[] = "itip_popup_event_deleted";
                } else {
                    $eventData->setValue("removed_at", NULL);
                    $notifications[] = "itip_popup_event_updated";
                }
            } else {
                if ($calendarId) {
                    $notifications[] = "itip_popup_event_added";
                } else {
                    $eventData->setValue("removed_at", date("Y-m-d H:i:s"));
                }
            }

            // save the event
            $eventData->saveToDb();

            if (\rcube_utils::get_input_value("sendReply", \rcube_utils::INPUT_POST) &&
                !empty($eventData->getValue("attendees"))
            ) {
                Event::sendAttendeeEmailNotifications($vevent, $eventData->getValue("vevent"));
            }

            // update the response for all copies of this event existing in the database belonging to different users
            // each attendee will have his or her own version of this event if they accept the invitation
            // this update processes also deleted events
            Event::setAttendeeStatus($eventData->getValue("uid"), $userEmail, $response, $eventData->getValue("id"));

            // show the popup notifications
            foreach(array_unique($notifications) as $notification) {
                $this->rcmail->output->command("display_message", $this->rcmail->gettext("xcalendar.$notification"), "confirmation");
            }
        } catch (\Exception $e) {
            $message = $e->getMessage() ?: $this->rcmail->gettext("xcalendar.error_importing_events");
            $this->rcmail->output->command("display_message", $e->getMessage(), "error");
            Utils::logError($message . " (489923)");
        }
    }

    /**
     * Processes ajax request to update the event that has been modified by the organizer.
     */
    public function processUpdateEvent(): void
    {
        try {
            // decrypt the identifying event data
            $data = \rcube_utils::get_input_value("data", \rcube_utils::INPUT_POST);
            $data = json_decode($this->rcmail->decrypt($data));

            if (!$data) {
                throw new \Exception("Invalid data.");
            }

            // get the event
            $eventData = new EventData();

            if (!$eventData->loadFromDb($data->eventId)) {
                throw new \Exception("Invalid event.");
            }

            // create a message and pull out its appropriate ics part
            if (!($message = new \rcube_message($data->uid, $data->folder)) ||
                !($ics = $message->get_part_body($data->mimeId)) ||
                !($events = $this->event->vEventToDataArray($ics))
            ) {
                throw new \Exception("Cannot retrieve itip.");
            }

            foreach ($events as $data) {
                if (empty($data['uid'])) {
                    throw new \Exception("Event UID empty.");
                }

                if ($data['uid'] == $eventData->getValue("uid") &&
                    $this->userId == $eventData->getValue("user_id")
                ) {
                    $fields = ["start", "end", "timezone_start", "timezone_end", "all_day", "title", "location",
                        "description", "url"];

                    foreach ($fields as $field) {
                        if (array_key_exists($field, $data)) {
                            $eventData->setValue($field, $data[$field]);
                        }
                    }

                    $eventData->saveToDb();

                    $this->rcmail->output->command(
                        "display_message",
                        $this->rcmail->gettext("xcalendar.itip_popup_event_updated"),
                        "confirmation"
                    );
                }
            }
        } catch (\Exception $e) {
            $message = $e->getMessage() ?: $this->rcmail->gettext("xcalendar.error_importing_events");
            $this->rcmail->output->command("display_message", $e->getMessage(), "error");
            Utils::logError($message . " (164344)");
        }
    }

    /**
     * Processes ajax request to update the attendance status of a user that replied to an invitation.
     */
    public function processUpdateReply(): void
    {
        try {
            // decrypt the identifying event data
            $data = \rcube_utils::get_input_value("data", \rcube_utils::INPUT_POST);
            $data = json_decode($this->rcmail->decrypt($data));

            if (!$data) {
                throw new \Exception("Invalid data.");
            }

            $eventData = new EventData();

            if (!$eventData->loadFromDb($data->eventId)) {
                throw new \Exception("Invalid event.");
            }

            if ($eventData->getValue("user_id") != $this->userId) {
                throw new \Exception("Invalid event.");
            }

            if ($eventData->setAttendanceByEmail($data->email, $data->status)) {
                $eventData->saveToDb();
            } else {
                throw new \Exception("Invalid attendee.");
            }

            $this->rcmail->output->command(
                "display_message",
                $this->rcmail->gettext("xcalendar.itip_popup_event_updated"),
                "confirmation"
            );

        } catch (\Exception $e) {
            $message = $e->getMessage() ?: $this->rcmail->gettext("xcalendar.error_importing_events");
            $this->rcmail->output->command("display_message", $e->getMessage(), "error");
            Utils::logError($message . " (489924)");
        }
    }

    /**
     * Processes ajax request to delete an event from the calendar after the organizer has canceled the event.
     */
    public function processDelete(): void
    {
        try {
            // decrypt the identifying event data
            $data = \rcube_utils::get_input_value("data", \rcube_utils::INPUT_POST);
            $data = json_decode($this->rcmail->decrypt($data));

            if (!$data) {
                throw new \Exception("Invalid data.");
            }

            // remove the event
            if (!$this->event->removeEvent($data->eventId)) {
                throw new \Exception("Invalid event.");
            }

            $this->rcmail->output->command(
                "display_message",
                $this->rcmail->gettext("xcalendar.itip_popup_event_deleted"),
                "confirmation"
            );

        } catch (\Exception $e) {
            $message = $e->getMessage() ?: $this->rcmail->gettext("xcalendar.error_importing_events");
            $this->rcmail->output->command("display_message", $e->getMessage(), "error");
            Utils::logError($message . " (449032)");
        }
    }

    /**
     * Retrieves the ics files included in the email message (specified in itipInfo) decodes the ics and if the current
     * user is in the attendee list and the attachment has the correct method type (meaning, it should be handled as an
     * invitation) creates the html boxes that will be inserted at the top of the email to show the user the information
     * about the event and allow him/her to accept, decline, etc. If the user is not in the attendee list or the method
     * is not set, display the "Add events to calendar" popup instead.
     *
     * @param $itipInfo
     * @param array $popupMimeIds
     * @return string
     */
    public function getItipHtmlForEmail($itipInfo, array &$popupMimeIds): string
    {
        $popupMimeIds = [];
        $processedEventIds = [];
        $result = "";

        if (empty($itipInfo) ||
            !is_array($itipInfo) ||
            !($message = new \rcube_message($itipInfo['uid'], $itipInfo['folder']))
        ) {
            return $result;
        }

        // get the events from the email message part and decode them to an event array
        foreach ($itipInfo['mimeIds'] as $mimeId) {
            if (!($ics = $message->get_part_body($mimeId)) ||
                !($events = $this->event->vEventToDataArray($ics))
            ) {
                continue;
            }

            if (!($method = $this->getVCalendarMethod($ics))) {
                $popupMimeIds[] = $mimeId; // add the "Add event to calendar" item to the popup menu instead
                continue;
            }

            // iterate the events (there can be more than one ics file in the email)
            foreach ($events as $data) {
                if (empty($data['uid']) || empty($data['attendees']) || empty($data['title'])) {
                    continue;
                }

                // the same event can be attached in two different files or doubled inside the same file -- process it
                // only once otherwise we'll get multiple html boxes for the same event
                if (in_array($data['uid'], $processedEventIds)) {
                    continue;
                }

                $processedEventIds[] = $data['uid'];
                $attendees = [];
                $organizer = false;
                $userEmail = false;

                // find the email address of the user as it appears in the ics file, if it's not there, don't display
                // the event box
                foreach (Event::getCurrentUserEmails(false) as $email) {
                    if (array_key_exists($email, $data['attendees'])) {
                        $userEmail = $email;
                        break;
                    }
                }

                // handle the new or updated invitation
                if ($userEmail && $method == "REQUEST") {
                    // find this event in the users' calendars
                    $eventInfo = new ItipEventInfo($data['uid'], $this->userId);

                    $update = $eventInfo->getEventId() && $eventInfo->isInfoDifferent($data);
                    $array = [
                        "action" => "request",
                        "showUpdate" => $update,
                        "header" => $this->rcmail->gettext("xcalendar.itip_title_" . ($update ? "modified" : "request")),
                        "calendars" => $this->getCalendarSelectHtml(),
                        "class" => "user-status-" . EventData::statusToVStatus($eventInfo->getStatus($userEmail), true),
                        "data" => $this->rcmail->encrypt(json_encode([
                            "action" => "request",
                            "eventId" => $eventInfo->getEventId(),
                            "uid" => $itipInfo['uid'],
                            "folder" => $itipInfo['folder'],
                            "mimeId" => $mimeId,
                        ])),
                    ];

                // handle the cancellation of the event
                } else if ($userEmail && $method == "CANCEL") {
                    // find this event in the users' calendars
                    $eventInfo = new ItipEventInfo($data['uid'], $this->userId);

                    $array = [
                        "action" => "cancel",
                        "header" => $this->rcmail->gettext("xcalendar.itip_title_cancel"),
                        "eventExists" => $eventInfo->getEventId() && !$eventInfo->isRemoved(),
                        "data" => $this->rcmail->encrypt(json_encode(["eventId" => $eventInfo->getEventId()])),
                    ];

                // handle the reply from attendee informing us (the organizer) that she accepted/declined, etc.
                } else if ($userEmail &&
                    $method == "REPLY" &&
                    ($icsStatus = $this->getMessageResponseStatus($data, $message, $attendeeEmail))
                ) {
                    // find this event in the users' calendars
                    $eventInfo = new ItipEventInfo($data['uid'], $this->userId);

                    $array = [
                        "action" => "reply",
                        "header" => $this->rcmail->gettext([
                            'name' => "xcalendar.itip_title_reply_" . strtolower($icsStatus),
                            'vars' => ['u' => $attendeeEmail]
                        ]),
                        "eventExists" => $eventInfo->getEventId() && !$eventInfo->isRemoved(),
                        "statusDifferent" => EventData::statusToVStatus($eventInfo->getStatus($attendeeEmail)) != $icsStatus,
                        "data" => $this->rcmail->encrypt(json_encode([
                            "eventId" => $eventInfo->getEventId(),
                            "email" => $attendeeEmail,
                            "status" => $icsStatus,
                        ])),
                    ];
                } else {
                    // if the current user is not in the attendee list or method is not specified, add the "Add event to
                    // calendar" item to the attachment's popup menu instead
                    $popupMimeIds[] = $mimeId;
                    continue;
                }

                // iterate through the ics event's attendees and create the html spans with the correct classes
                // (displaying correct icons)
                foreach ($data['attendees'] as $attendeeData) {
                    $email = $attendeeData['email'];

                    if ($email == $userEmail) {
                        $class = "current-user attendee-" . EventData::statusToVStatus($eventInfo->getStatus($userEmail), true);
                    } else {
                        $class = "attendee-" .
                            (!empty($attendeeData['status']) ? EventData::statusToVStatus($attendeeData['status'], true) : "needs-action");
                    }

                    $attendees[] = \html::span(["class" => $class], \rcube::Q($email));

                    if (!empty($attendeeData['organizer']) && !$organizer) {
                        $organizer = $email;
                    }
                }

                $array['mimeId'] = $mimeId;
                $array['title'] = \rcube::Q((string)$data["title"]);
                $array['start'] = (int)$data['all_day'] ?
                    $this->format->formatDate($data['start']) :
                    $this->format->formatDateTime($data['start']);
                $array['organizer'] = $organizer ? \rcube::Q($organizer) : "-";
                $array['attendees'] = implode(", ", $attendees);
                $array['description'] = empty($data['description']) ? "" : \rcube::Q($data['description']);

                // create a url link, but only for http(s) urls
                $url = trim((string)($data['url'] ?? ''));
                if (preg_match('~^https?://~i', $url)) {
                    $array['url'] = \html::a(
                        ['href' => $url, 'target' => '_blank', 'rel' => 'noopener noreferrer'],
                        \rcube::Q($url)
                    );
                } else {
                    // not a safe http(s) URL, render as escaped text
                    $array['url'] = \rcube::Q($url);
                }

                $result .= \XFramework\Plugin::view("elastic", "xcalendar.message_itip", $array);
            }
        }

        return $result;
    }

    private function getMessageResponseStatus($event, $message, &$attendeeEmail): bool|string
    {
        foreach ($event['attendees'] as $attendeeData) {
            $attendeeEmail = $attendeeData['email'];

            if ($message->sender['string'] == $attendeeEmail || $message->sender['mailto'] == $attendeeEmail) {
                $response = EventData::statusToVStatus($attendeeData['status']);

                if (EventData::isAttendanceResponseValid($response)) {
                    return $response;
                }
            }
        }

        return false;
    }

    /**
     * Returns an html string of the select box that includes all the local calendars (excluding shared calendars.)
     *
     * @return string
     */
    private function getCalendarSelectHtml(): string
    {
        if ($calendars = $this->event->getCalendarList(Calendar::LOCAL, false)) {
            $select = new \html_select([]);
            $select->add("-- " . $this->rcmail->gettext("xcalendar.itip_dont_import") . " --", 0);

            foreach ($calendars as $val) {
                $select->add($val['name'], $val['id']);
            }

            // select the default calendar as specified in the user's settings, or the first one if the default doesn't
            // exist
            return $select->show((int)CalendarData::loadDefault()->get("id") ?: array_keys($calendars)[0]);
        }

        return "";
    }

    public static function getVCalendarMethod($text)
    {
        // parse the calendar, this will throw an exception if the text is invalid
        try {
            $vcalendar = \Sabre\VObject\Reader::read(
                $text,
                \Sabre\VObject\Reader::OPTION_FORGIVING | \Sabre\VObject\Reader::OPTION_IGNORE_INVALID_LINES
            );

            foreach ($vcalendar->children() as $item) {
                if (is_a($item, 'Sabre\VObject\Property\FlatText') && $item->name == "METHOD") {
                    return $item->getValue();
                }
            }
        } catch (\Exception) {
        }

        return false;
    }
}