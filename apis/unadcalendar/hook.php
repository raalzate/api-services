<?php

require 'class/Formate.class.php';	
require 'helper.php';

/*
By:Raul .A Alzate
Funcion que obtiene el estado
*/
 function unadcalendar_status ($v){
    global $response, $info;

    $response->status(200);
    $request = array(
        'current version' => $info['unadcalendar']['version'],
        'status' => $v == $info['unadcalendar']['version'],
    );
    $response->body(json_encode($request));
    return $response;
}

/*
By:Raul .A Alzate
Funcion que obtiene la version
*/
function unadcalendar_version() {
    global $response, $info;

    $response->status(200);
    $request = array(
        'version' => $info['unadcalendar']['version'],
        'build' => $info['unadcalendar']['build'],
        'description' => $info['unadcalendar']['description'],
    );
    $response->body(json_encode($request));
    return $response;
}

/*
By:Raul .A Alzate
Funcion que inserta un calendario
*/
function unadcalendar_insert($v, $pd, $cs, $em) {
    global $response, $info;
    
    switch ($v) {

        case 'v3':
            $response->status(200);

            $formte = new Unad\Formate();
            $service = $info['unadcalendar']['service_agenda'];
            $scraper = $formte->get_scraper($service, $pd, $cs);

            if($scraper['status']) {//OK

               $result    = $scraper['result'];//el scrapper retorna los eventos
               $gcVersion = $info['google/calendar']['version'];
               $clientId  = $info['unadcalendar']['client_id'];
               
               //crea el calendario
               $calendar = google_calendar_create($gcVersion, $clientId, $cs)->body();   
               $calendar = json_decode($calendar);

               if($calendar->status) {//se creo el calendario correctamente
                    $calendarId = $calendar->calendar_id;

                    $request = array(
                    	'name'        => get_course_by_code(JSON_COUSER, $cs),
                    	'code'        => $cs,
                        'status'      => true,
                        'calendar_id' => $calendarId,
                        'events'      => _unadcalendar_add_events($gcVersion, $clientId, 
                            $scraper['result'], $calendarId, $cs, $em) 
                    );

                    $response->body(json_encode($request));
                    return $response;
               }

               //existio un error
               $response->body($calendar);
               return $response;

            } else {//ERROR
                $response->body(json_encode($scraper));
                return $response;
            }
        default:
           $request = array(
                'status' => false, 
                'error' => 'Not version stable'
            );
            $response->body(json_encode($request));
            return $response;
    }
    
}



/*
By:Raul .A Alzate
Funcion que valida si existe calendario 
Si existe el calendario se visualiza la informacion del mismo
*/
function unadcalendar_find_calendar(Slim\Route $route){
    global $app, $info;

    $curso     = $route->getParam('cs');
    $email     = $route->getParam('em');
    $gcVersion = $info['google/calendar']['version'];
    $clientId  = $info['unadcalendar']['client_id'];

    $rqCalendar = google_calendar_find($gcVersion,$clientId,$curso)->body();
    $rqCalendar = json_decode($rqCalendar);

    if($rqCalendar->status) {//no existe problemas
        $calendarId = $rqCalendar->calendar->id;

        $rqEvents = google_calendar_get_events($gcVersion,$clientId,$calendarId)->body();
        $rqEvents = json_decode($rqEvents);
        if(!$rqEvents->status) return;

        $mpEvent = _unadcalendar_map_events($calendarId, $rqEvents->events, $email);

        if(is_null($mpEvent)) {//existe problemas con el calendario
            //no hay eventos, se elimina el calendario
            google_calendar_delete($gcVersion, $clientId, $calendarId);
            return;
        } 

        $request['name'] = get_course_by_code(JSON_COUSER, $rqCalendar->calendar->summary);
        $request['code'] = $rqCalendar->calendar->summary;
        $request['status'] = true;
        $request['calendar_id'] = $calendarId;
        $request['events'] = $mpEvent;

        $app->response()->body(json_encode($request));
        $app->stop();
    }
}
