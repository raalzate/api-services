<?php

require 'vendor/autoload.php';

require_once 'libs/Spyc/Spyc.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$response = $app->response();
$response['Content-Type'] = 'application/json';

//requiere apis
require 'apis/unadcalendar/routing.php';
require 'apis/google/calendar/routing.php';

$info['unadcalendar']    = Spyc::YAMLLoad(dirname(__FILE__).'/apis/unadcalendar/info.yaml');
$info['google/calendar'] = Spyc::YAMLLoad(dirname(__FILE__).'/apis/google/calendar/info.yaml');

$app->get( '/',
    function () {
        echo "<center>Sistema integral de servicios.<br>";
        echo "Soporte por el Ing. <b>Raul .A Alzate</b><br>";
        echo "Email: alzategomez.raul@gmail.com<br></center>";
    }
);


$app->run();
