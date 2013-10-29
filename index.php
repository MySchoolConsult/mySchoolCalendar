<?php

// Look up other security checks in the docs!
\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('mySchoolCalendar');


\OCP\App::setActiveNavigationEntry('mySchoolCalendar');
$tpl = new \OCP\Template("mySchoolCalendar", "main", "user");
$tpl->assign('msg', 'Hello World');
$tpl->printPage();