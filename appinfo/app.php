<?php
OC::$CLASSPATH["MyScEvents"] = 'mySchoolCalendar/lib/MyScEvents.php';
OC::$CLASSPATH["PdoMySchool"] = 'mySchoolCalendar/lib/PdoMySchool.php';


OC::$CLASSPATH["OC_Connector_Sabre_CalDAV_Backend_MySc"] = "mySchoolCalendar/lib/sabre/backend.php";
//OC::$CLASSPATH["OC_Connector_Sabre_CalDAV_Calendar_MySc"] = 'mySchoolCalendar/lib/sabre/calendar.php';
//OC::$CLASSPATH["OC_Connector_Sabre_CalDAV_UserCalendars_MySc"] = 'mySchoolCalendar/lib/sabre/usercalendars.php';

OCP\Util::connectHook("OC_Calendar", "getSources", "MyScEvents", "getMyScSources");
OCP\Util::connectHook("OC_Calendar", "getEvents", "MyScEvents", "getMyScEvents");
