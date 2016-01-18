<?php
require "hook.php";
require "docs.php";

$app->get('/unadcalendar/docs/:v','unadcalendar_docs');

$app->get('/unadcalendar/:v/status', 'unadcalendar_status');
$app->get('/unadcalendar/v','unadcalendar_version');

$app->post('/unadcalendar/:v/join/:pd/:cs/:em','unadcalendar_find_calendar',
    'unadcalendar_insert');

