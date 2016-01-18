<?php

/**
* Funcion que permite integrar la documentacion de la API
* Esta documentacion esta en formato RAML, que permite la navegacion adecuada de la API
* @By: Raul .A Alzate
* @Route: /unadcalendar/docs
*/
function unadcalendar_docs($v){
	
	global $app;

	$response = $app->response();
    $response['Content-Type'] = 'text/html; charset=UTF-8';
    $RAMLsource = dirname(__FILE__).'/raml/'.$v.'.raml';

    if(file_exists($RAMLsource)) {
    	// Types of Action Verbs Allowed
		$RAMLactionVerbs = array('get', 'post', 'put', 'patch', 'delete', 'connect', 'trace');

		// APC Cache Time Limit - set to "0" to disable
		$cacheTimeLimit = '36000';

		// Path to the theme file for the docs
		$docsTheme = 'templates/grey/index.phtml';

		include 'libs/Raml2html/index.php';
    } else {
    	print "No Hay documentaciòn!!";
    }
	
}

?>