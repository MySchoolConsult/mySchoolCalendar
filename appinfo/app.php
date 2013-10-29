<?php
OC::$CLASSPATH["MyScEvents"] = 'mySchoolCalendar/lib/MyScEvents.php';
OC::$CLASSPATH["PdoMySchool"] = 'mySchoolCalendar/lib/PdoMySchool.php';

OCP\Util::connectHook("OC_Calendar", "getSources", "MyScEvents", "getMyScSources");
OCP\Util::connectHook("OC_Calendar", "getEvents", "MyScEvents", "getMyScEvents");