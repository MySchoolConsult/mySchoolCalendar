<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fwolbring
 * Date: 28.10.13
 * Time: 20:13
 */

class MyScEvents {
    static function getMyScSources($params) {


        $base_url = \OCP\Util::linkTo('calendar', 'ajax/events.php').'?calendar_id=';
        $params['sources'][]
            = array(
            'displayname' => "Stundenplan",
            'uri' => "stupla",
            'userid' => OC_User::getUser(),
            'url' => $base_url.'stupla_' . OC_User::getUser(),
            'backgroundColor' => '#00B32D',
            'borderColor' => '#888',
            'textColor' => 'black',
            'cache' => true,
            'editable' => false,
        );
    }

    static function getMyScEvents($params = array()) {
        list($type, $user) = explode("_", $params["calendar_id"]);

        $interval = new DateInterval("PT45M");
        $id_org = 863;
        $userkuerzel = $user;

        $oc_user = "stue"; //OC_User::getUser();

        if ($type == "stupla" && $user == $oc_user) {
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

            $sql = "SELECT id, tag, monat, jahr, stunde, fach, klasse, raum FROM stupla_stunden WHERE lehrer=:lehrer AND id_org=:id_org";

            if (isset($params["object_id"])) {
                $objectId = $params["object_id"];
                $sql .= " AND id = $objectId";
            }

            $st = $pdo->prepare($sql);

            $st->bindValue("lehrer", $userkuerzel);
            $st->bindValue("id_org", $id_org);

            if (!$st->execute()) {
                OCP\Util::writeLog(OC_App::getCurrentApp(), __METHOD__ . " PDO Error: " . print_r($pdo->errorInfo(), true), OCP\Util::DEBUG);
                return;
            }

            $data = $st->fetchAll(PDO::FETCH_ASSOC);
            $events = array();

            foreach ($data as $id => $stunde) {
                $tag = $stunde["tag"];
                $monat = $stunde["monat"];
                $jahr = $stunde["jahr"];

                $cstunde = $stunden_zeiten[$stunde["stunde"]];

                $start = new DateTime("$jahr-$monat-$tag $cstunde");
                $end = clone($start);
                $end->add($interval);

                $title = $stunde["fach"];
                $description = $stunde["klasse"] . " " . $stunde["raum"] . " " . $stunde["fach"];

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
}