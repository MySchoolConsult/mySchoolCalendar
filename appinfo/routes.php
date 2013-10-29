<?php

$this->create('mySchoolCalendar_index', '/')->action(
    function($params){
        require __DIR__ . '/../index.php';
    }
);