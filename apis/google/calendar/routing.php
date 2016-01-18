<?php
require "hook.php";



#METODOS GET (Consulta)
$app->get('/google/calendar/:v/status', 'google_calendar_status');

$app->get('/google/calendar/v','google_calendar_version');

$app->get('/google/calendar/:v/:clientId/find/:summary',
    'google_calendar_valid_client', 'google_calendar_find');

#METODOS POS (Insercion)
$app->post('/google/calendar/:v/:clientId/create/:summary',
    'google_calendar_valid_client', 'google_calendar_create');

$app->post('/google/calendar/:v/:clientId/:calendarId/:eventId/add/:email/attendees',
    'google_calendar_valid_client', 'google_calendar_add_attendees');

#METODOS DELETE
$app->delete('/google/calendar/:v/:clientId/:calendarId',
    'google_calendar_valid_client', 'google_calendar_delete');

