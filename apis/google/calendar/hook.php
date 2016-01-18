<?php

require 'class/BCalendar.class.php';	

/*
By:Raul .A Alzate
Funcion que obtiene el estado
*/
 function google_calendar_status ($v){
    global $response, $info;

    $response->status(200);
    $request = array(
        'current version' => $info['google/calendar']['version'],
        'status' => $v == $info['google/calendar']['version'],
    );
    $response->body(json_encode($request));
    return $response;
}

/*
By:Raul .A Alzate
Funcion que obtiene la version
*/
function google_calendar_version() {
    global $response, $info;

    $response->status(200);
    $request = array(
        'version' => $info['google/calendar']['version'],
        'build' => $info['google/calendar']['build'],
        'description' => $info['google/calendar']['description'],
    );
    $response->body(json_encode($request));
    return $response;
}

/*
By:Raul .A Alzate
Funcion que encuentra un calendario segun su summary
*/
function google_calendar_find($v,$clientId, $summary) {
    global $response;

    $data = Spyc::YAMLLoad(dirname(__FILE__)."/client/$clientId.yaml");
    $BCalendar = new BCalendar($data);

    $response->status(200);

    $calendar = $BCalendar->getCalendar($summary);
    $request = array(
        'status' => !is_null($calendar),
        'calendar' => $calendar
    );
    $response->body(json_encode($request));

    return $response;
}

/*
By:Raul .A Alzate
Esta function se encarga de obtener los eventos de un calendario
*/
function google_calendar_get_events($v,$clientId, $calendarId) {
    global $response;

    $data = Spyc::YAMLLoad(dirname(__FILE__)."/client/$clientId.yaml");
    $BCalendar = new BCalendar($data);

    $response->status(200);

    $events = $BCalendar->getEvents($calendarId);

    $_events = array();

    foreach ($events as $event) {
        $n = $event->getId();
        $_events[$n]['description'] = $event->getDescription();
        $_events[$n]['htmlLink']    = $event->getHtmlLink();
        $_events[$n]['summary']     = $event->getSummary();
        $_events[$n]['start']       = $event->getStart()->getDateTime();
        $_events[$n]['end']         = $event->getEnd()->getDateTime();
        $_events[$n]['id']          = $event->getId();
        $_events[$n]['attendees']   = $event->getAttendees();
    }

    $request = array(
        'status' => !is_null($events),
        'events' => $_events
    );
    $response->body(json_encode($request));

    return $response;
}

/*
By:Raul .A Alzate
Esta funcion se encarga de agregar un email al evento
*/
function google_calendar_add_attendees($v, $clientId, $calendarId, $eventId, $email) {
    global $response;

    $data = Spyc::YAMLLoad(dirname(__FILE__)."/client/$clientId.yaml");
    $BCalendar = new BCalendar($data);

    $response->status(200);

    $status = $BCalendar->setAttendees($calendarId, $eventId, $email, true);
    $request = array(
        'status' => $status
    );
    $response->body(json_encode($request));

    return $response;
}


/*
By:Raul .A Alzate
Funcion que elimina un calendario
*/
function google_calendar_delete($v, $clientId, $calendarId) {
    global $response;

    $data = Spyc::YAMLLoad(dirname(__FILE__)."/client/$clientId.yaml");
    $BCalendar = new BCalendar($data);

    $response->status(200);

    try{
		$status = $BCalendar->deleteCalendar($calendarId);
    	$request = array(
        	'status' => true,
        	'calendar_id' => $calendarId
    	);
    } catch (Google_Service_Exception $e) {
    	$request = array(
        	'status' => false,
        	'calendar_id' => $calendarId,
        	'error'  => $e->getMessage()
    	);
    }
    
    $response->body(json_encode($request));

    return $response;
}

/*
By:Raul .A Alzate
Esta funcion se encarga de agregar un email al evento
*/
function google_calendar_add_event($v, $clientId, 
    $calendarId, $summary, $description, $location, $dataTimeStart, $dataTimeEnd, $email) {

    global $response;

    $event = null;
    $data = Spyc::YAMLLoad(dirname(__FILE__)."/client/$clientId.yaml");
    $BCalendar = new BCalendar($data);

    $response->status(200);

    try {
    	$event = $BCalendar->addEvent($calendarId, 
	        $summary, 
	        $description, 
	        $location, 
	        $dataTimeStart, 
	        $dataTimeEnd, 
	        $email,
	        true);
    } catch (Google_Service_Exception $e) {

    	$request = array(
        	'status' => false,
        	'error' => $e->getMessage()
    	);

    	$response->body(json_encode($request));
    	return $response;
    }
    

    $_events['description'] = $event->getDescription();
    $_events['htmlLink']    = $event->getHtmlLink();
    $_events['summary']     = $event->getSummary();
    $_events['start']       = $event->getStart()->getDateTime();
    $_events['end']         = $event->getEnd()->getDateTime();
    $_events['id']          = $event->getId();
    $_events['attendees']   = $event->getAttendees();
    

    $request = array(
        'status' => !is_null($event),
        'event' => $_events
    );

    $response->body(json_encode($request));

    return $response;
}


/*
By:Raul .A Alzate
Funcion que valida el cliente de google calendar
*/
function google_calendar_valid_client(Slim\Route $route){
    global $app;
    $clientId = $route->getParam('clientId');
    $data = Spyc::YAMLLoad(dirname(__FILE__)."/client/$clientId.yaml");
    if(!isset($data['key']) || !isset($data['key_api']) || !isset($data['key_client'])){
        $request = array(
            'status' => false,
            'message' => 'Denied ClientID o Params not found',
        );
        $app->response()->body(json_encode($request));
        $app->response()->status(302);
        $app->stop();
    }
}

/*
By:Raul .A Alzate
Funcion que permite crear un calendario nuevo
*/
function google_calendar_create($v,$clientId, $summary) {
    global $response;
    $data = Spyc::YAMLLoad(dirname(__FILE__)."/client/$clientId.yaml");
    $BCalendar = new BCalendar($data);

    $response->status(200);
    $calendar = $BCalendar->createCalendar($summary);

    $request = array(
        'status' => !is_null($calendar),
        'calendar_id' => $calendar
    );
    $response->body(json_encode($request));

    return $response;
}