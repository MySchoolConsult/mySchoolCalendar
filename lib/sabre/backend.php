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
        $calendar = array(
            "id" => "stupla_stue",
            "uri" => "stupla",
            "principaluri" => $principalUri,
            '{DAV:}displayname' => "Stundenplan"
        );

        return array($calendar);
    }

    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used to reference
     * this calendar in other methods, such as updateCalendar.
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     * @return void
     */
    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        // TODO: Implement createCalendar() method.
    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param mixed $calendarId
     * @return void
     */
    public function deleteCalendar($calendarId)
    {
        // TODO: Implement deleteCalendar() method.
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

        $events = MyScEvents::getMyScEvents($params);

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
                "uri" => "stupla_event_" . $event["id"],
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

    /**
     * Creates a new calendar object.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        // TODO: Implement createCalendarObject() method.
    }

    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * It is possible return an etag from this function, which will be used in
     * the response to this PUT request. Note that the ETag must be surrounded
     * by double-quotes.
     *
     * However, you should only really return this ETag if you don't mangle the
     * calendar-data. If the result of a subsequent GET to this object is not
     * the exact same as this request body, you should omit the ETag.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return string|null
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        // TODO: Implement updateCalendarObject() method.
    }

    /**
     * Deletes an existing calendar object.
     *
     * @param mixed $calendarId
     * @param string $objectUri
     * @return void
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        // TODO: Implement deleteCalendarObject() method.
    }

} 