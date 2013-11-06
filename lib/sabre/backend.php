<?php
/**
 * Created by PhpStorm.
 * User: fwolbring
 * Date: 05.11.13
 * Time: 16:45
 */

class OC_Connector_Sabre_CalDAV_Backend_MySc extends Sabre_CalDAV_Backend_Abstract {
    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    calendar. This can be the same as the uri or a database key.
     *  * uri, which the basename of the uri with which the calendar is
     *    accessed.
     *  * principaluri. The owner of the calendar. Almost always the same as
     *    principalUri passed to this method.
     *
     * Furthermore it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * @param string $principalUri
     * @return array
     */
    public function getCalendarsForUser($principalUri)
    {
        $user = OC_User::getUser();

        $sources = MyScEvents::getMyScSources();

        foreach ($sources as $source) {
            $uri = $source["uri"];
            $displayname = $source["displayname"];

            $calendar = array(
                "id" => $uri . "_$user",
                "uri" => $uri,
                "principaluri" => $principalUri,
                '{DAV:}displayname' => $displayname
            );

            $calendars[] = $calendar;
        }

        return $calendars;
    }

    /**
     * Returns all calendar objects within a calendar.
     *
     * Every item contains an array with the following keys:
     *   * id - unique identifier which will be used for subsequent updates
     *   * calendardata - The iCalendar-compatible calendar data
     *   * uri - a unique key which will be used to construct the uri. This can be any arbitrary string.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * calendarid - The calendarid as it was passed to this function.
     *   * size - The size of the calendar objects, in bytes.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * If neither etag or size are specified, the calendardata will be
     * used/fetched to determine these numbers. If both are specified the
     * amount of times this is needed is reduced by a great degree.
     *
     * @param mixed $calendarId
     * @return array
     */
    public function getCalendarObjects($calendarId)
    {
        $params["calendar_id"] = $calendarId;

        list($type, $user) = explode("_", $calendarId);

        $events = MyScEvents::getMyScEvents($params);

// Info: Array scheme
//        $events[] = array(
//            "allDay"=> false,
//            "description" => $description,
//            "end" => $end->format("Y-m-d H:i:s"),
//            "id" => $id,
//            "start" => $start->format("Y-m-d H:i:s"),
//            "title" => $title,
//            "summary" => "Test2",
//            "calendardata" => "BEGIN:VCALENDAR\nVERSION:2.0\n"
//                . "PRODID:mySchoolCalendar"
//                . \OCP\App::getAppVersion('mySchoolCalendar') . "\n"
//                . $vevent->serialize() .  "END:VCALENDAR"
//        );

        $objects = array();

        foreach ($events as $event) {
            $object = array(
                "id" => $event["id"],
                "uri" => $type . "_event_" . $event["id"],
                "lastmodified" => time(),
                "calendarid" => $calendarId,
                "calendardata" => $event["calendardata"]
            );

            $objects[] = $object;
        }

        return $objects;
    }

    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @return array
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        list(,, $objectId) = explode("_", $objectUri);
        $params["calendar_id"] = $calendarId;
        $params["object_id"] = $objectId;


        $events = MyScEvents::getMyScEvents($params);

        $object = null;

        foreach ($events as $event) {
            if ($event["id"] == $objectId) {
                $object = array(
                    "id" => $event["id"],
                    "uri" => "stupla_event_" . $event["id"],
                    "lastmodified" => time(),
                    "calendarid" => $calendarId,
                    "calendardata" => $event["calendardata"]
                );
            }
        }

        return $object;
    }

// Edit functions, that must exist to match the Interface definition, but needn't to be imlemented because
// no editing is possible.
    public function createCalendar($principalUri, $calendarUri, array $properties) {}
    public function deleteCalendar($calendarId) {}
    public function createCalendarObject($calendarId, $objectUri, $calendarData) {}
    public function updateCalendarObject($calendarId, $objectUri, $calendarData) {}
    public function deleteCalendarObject($calendarId, $objectUri) {}

} 