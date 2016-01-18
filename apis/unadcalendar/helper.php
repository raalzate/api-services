<?php

define('JSON_COUSER', dirname(__FILE__).'/static-data/cursos.json');

function get_course_by_code($jsonFile, $code){
    $string = file_get_contents($jsonFile);
    $json_a=json_decode($string,true);
    $json_a = $json_a['cursos'];
    foreach ($json_a as $cors){
     if($cors['code'] == $code){
       return $cors['name'];    
     }
    }
    return $code;
}

function get_date_format($data)
{
    
    $arrName   = array(
        'ENE',
        'FEB',
        'MAR',
        'ABR',
        'MAY',
        'JUN',
        'JUL',
        'AGO',
        'SEP',
        'OCT',
        'NOV',
        'DIC'
    );
    $arrNumber = array(
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '10',
        '11',
        '12'
    );
    
    $d = str_replace($arrName, $arrNumber, $data);
    
    $d = str_replace("/", "-", $d);
    $d = str_replace("-15", "-2015", $d);
    
    return strtotime($d);
}


/*
By:Raul .A Alzate
Funcion que obtiene los eventos de forma de respuesta correcta 
Realiza un map de lo que se necesita
*/
function _unadcalendar_map_events($calendarId, $events, $email){

    global $info;

 	if(empty($events)){
 		return null;
    }

    $response = array();
    $n = 0; 

    $gcVersion = $info['google/calendar']['version'];
    $clientId  = $info['unadcalendar']['client_id'];

    foreach ($events as $event) {
        
        $response[$n]['description'] = $event->description;
        $response[$n]['htmlLink']    = $event->htmlLink;
        $response[$n]['summary']     = $event->summary;
        $response[$n]['start']     = $event->start;
        $response[$n]['end']     = $event->end;
        $response[$n]['id']     = $event->id;
    
        $addAtt = true;
        foreach ($event->attendees as $attendee) {
            if ($attendee->email == $email) {
                $addAtt = false;
                break;
            }
        }
        
        if ($addAtt) {
            //agregar attandes
            google_calendar_add_attendees($gcVersion, 
                $clientId, 
                $calendarId, 
                $event->id, 
                $email);
        }
    
        $n++;        
    }

    return $response;
}

function _unadcalendar_add_events($v, $clientId, 
	$events, $calendarId, $cs, $email){

	$response = array();
	$location   = "UNAD - " . get_course_by_code(JSON_COUSER, $cs);
    $n = 0;

    foreach ($events as $data) {
        $inf = array();

        if(count($data)<5) continue;

        for ($i = (count($data) - 7) - 1; $i < count($data); $i++) {
            if($i >= 0) $inf[] = $data[$i];
        }

        $summary     = $cs. ' - ' . $inf[0] . ' Valor: ' . $inf[4];
        $description = $inf[1] . "\n";
        $description .= "Valor de la actividad: " . $inf[4];
        
        $dataTimeStart = date("Y-m-d", get_date_format($inf[2])) . "T12:00:00.000-05:00";
        $dataTimeEnd   = date("Y-m-d", get_date_format($inf[3])) . "T23:55:55.000-05:00";
        
        //inserta un envento al calendario
        $rqEvent = google_calendar_add_event($v, $clientId, 
            $calendarId, $summary, $description, $location, $dataTimeStart, $dataTimeEnd, $email)
        ->body();

        $rqEvent = json_decode($rqEvent);

        if($rqEvent->status) {//si el evento fue insertado con exito
            $response[$n]['description'] = $rqEvent->event->description;
            $response[$n]['htmlLink']    = $rqEvent->event->htmlLink;
            $response[$n]['summary']     = $rqEvent->event->summary;
            $response[$n]['start']       = $rqEvent->event->start;
            $response[$n]['end']         = $rqEvent->event->end;
            $response[$n]['id']          = $rqEvent->event->id;

            $n++; 
        }
        
    }

    return $response;
}