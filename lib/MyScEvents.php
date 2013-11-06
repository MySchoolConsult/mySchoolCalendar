<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fwolbring
 * Date: 28.10.13
 * Time: 20:13
 */

class MyScEvents {
    private static $sources = array(
        "stupla" => array("self", "getStuplaEvents"),
        "changes" => array("self", "getChanges"),
        "substitutions" => array("self", "getSubstitutions")
    );

    static function getMyScSources($params = array()) {
        $base_url = \OCP\Util::linkTo('calendar', 'ajax/events.php').'?calendar_id=';

        $sources = array(
            array(
                'displayname' => "Stundenplan",
                'uri' => "stupla",
                'userid' => OC_User::getUser(),
                'url' => $base_url.'stupla_' . OC_User::getUser(),
                'backgroundColor' => '#003DF5',
                'borderColor' => '#888',
                'textColor' => 'black',
                'cache' => true,
                'editable' => false
            ),
            array(
                'displayname' => "Änderungen",
                'uri' => "changes",
                'userid' => OC_User::getUser(),
                'url' => $base_url.'changes_' . OC_User::getUser(),
                'backgroundColor' => '#003DF5',
                'borderColor' => '#888',
                'textColor' => 'whitesmoke',
                'cache' => true,
                'editable' => false
            ),
            array(
                'displayname' => "Vertretung",
                'uri' => "substitutions",
                'userid' => OC_User::getUser(),
                'url' => $base_url.'substitutions_' . OC_User::getUser(),
                'backgroundColor' => '#CC0033',
                'borderColor' => '#888',
                'textColor' => 'whitesmoke',
                'cache' => true,
                'editable' => false
            )
        );

        if (!isset($params["sources"])) {
            return $sources;
        } else {
            $params["sources"] = array_merge($params["sources"], $sources);
        }

    }

    static function getMyScEvents($params = array()) {
        list($type, $user) = explode("_", $params["calendar_id"]);

        $interval = new DateInterval("PT45M");
        $id_org = 863;
        $userkuerzel = $user;

        $oc_user = OC_User::getUser();

        if (array_key_exists($type, self::$sources) && $user == $oc_user) {
            $func = self::$sources[$type];
            if ($func === null) {
                return;
            }

            $pdo = PdoMySchool::getPDO();

            $st = $pdo->prepare("SELECT stunde, zeit FROM liste_org_stundenzeiten WHERE id_org=:id_org");

            $st->bindValue("id_org", $id_org);

            if (!$st->execute()) {
                OCP\Util::writeLog(OC_App::getCurrentApp(), __METHOD__ . " PDO Error: " . print_r($pdo->errorInfo(), true), OCP\Util::DEBUG);
                return;
            }

            $data = $st->fetchAll(PDO::FETCH_ASSOC);

            $stunden_zeiten = array();

            foreach ($data as $c) {
                $stunden_zeiten[$c["stunde"]] = $c["zeit"];
            }

            if (isset($params["object_id"])) {
                $objectId = $params["object_id"];
                $sql .= " AND id = $objectId";
            } else {
                $objectId = false;
            }

            $events = array();

            $data = call_user_func($func, $userkuerzel, $id_org, $objectId);

            foreach ($data as $id => $stunde) {
                $tag = $stunde["tag"];
                $monat = $stunde["monat"];
                $jahr = $stunde["jahr"];

                $cstunde = $stunden_zeiten[$stunde["stunde"]];

                $start = new DateTime("$jahr-$monat-$tag $cstunde");
                $end = clone($start);
                $end->add($interval);

                $title = $stunde["title"];
                $description = $stunde["description"];

                $vevent = Sabre\VObject\Component::create('VEVENT');

                $vevent->add("DTSTART");
                $vevent->DTSTART->setDateTime($start);
                $vevent->add("DTEND");
                $vevent->DTEND->setDateTime($end);
                $vevent->{'UID'} = substr(md5(rand().time()), 0, 10);
                $vevent->{'SUMMARY'} = $description;

                $events[] = array(
                    "allDay"=> false,
                    "description" => $description,
                    "end" => $end->format("Y-m-d H:i:s"),
                    "id" => $stunde["id"],
                    "start" => $start->format("Y-m-d H:i:s"),
                    "title" => $title,
                    "summary" => "Test2",
                    "calendardata" => "BEGIN:VCALENDAR\nVERSION:2.0\n"
                    . "PRODID:mySchoolCalendar"
                    . \OCP\App::getAppVersion('mySchoolCalendar') . "\n"
                    . $vevent->serialize() .  "END:VCALENDAR"
                );
            }

            if (!isset($params["events"]))
                return $events;
            else
                $params["events"] = array_merge($params["events"], $events);
        }
    }

    private static function getStuplaEvents($userkuerzel, $id_org, $objectId = false) {
        $sql = "SELECT id, tag, monat, jahr, stunde, fach, klasse, raum FROM stupla_stunden WHERE lehrer=:lehrer AND id_org=:id_org";

        if ($objectId) {
            $objectId = $params["object_id"];
            $sql .= " AND id = $objectId";
        }

        $pdo = PdoMySchool::getPDO();
        $st = $pdo->prepare($sql);

        $st->bindValue("lehrer", $userkuerzel);
        $st->bindValue("id_org", $id_org);

        if (!$st->execute()) {
            OCP\Util::writeLog(OC_App::getCurrentApp(), __METHOD__ . " PDO Error: " . print_r($pdo->errorInfo(), true), OCP\Util::DEBUG);
            return;
        }

        $data = $st->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $line => $stunde) {
            $data[$line]["title"] = $stunde["fach"];
            $data[$line]["description"] = $stunde["klasse"] . " " . $stunde["raum"] . " " . $stunde["fach"];
        }

        return $data;
    }

    private static function getChanges($userkuerzel, $id_org, $objectId = false) {
        $data = array(
            array(
                "id" => 0,
                "tag" => 6,
                "monat" => 11,
                "jahr" => 2013,
                "stunde" => 1,
                "fach" => "ESI",
                "klasse" => "BFE61",
                "raum" => "C14",
                "title" => "ESI",
                "description" => "Teststunde"
            )
        );

        return $data;
    }

    private static function getSubstitutions($userkuerzel, $id_org, $objectId = false) {
        $data = array(
            array(
                "id" => 0,
                "tag" => 6,
                "monat" => 11,
                "jahr" => 2013,
                "stunde" => 1,
                "fach" => "ESI",
                "klasse" => "BFE61",
                "raum" => "C14",
                "title" => "ESI",
                "description" => "Teststunde"
            )
        );

        return $data;
    }
}