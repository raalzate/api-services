<?php
namespace Unad;

include dirname(__FILE__) . '/Scraper.class.php';  


class Formate {
	

	function get_scraper($service_agenda, $periodo, $curso)
	{
		$scraper = new \Scraper();

		$response = $this->get_html($service_agenda, $periodo, $curso);
		
		if(!$response['status']) {//se presenta un error en la consulta del html
			return $response;
		}

		$result = $scraper->execute('#contenido table tr td table tr td', $response['content']); //contenido

		if ($result == null) {//vuelve a intentar con otro formato
		    $result = $scraper->execute('#contenido table tr td', $response['content']); //contenido
		     return array(
		        "status" => true,
		        "result" => $result[0],
				"code" => 200
		   	);
		} else {//OK

		   return array(
		        "status" => true,
		        "result" => $result,
				"code" => 200
		   	);
		}
	}

	/**
	*/
	function get_html($service_agenda, $periodo, $curso, $formato = 'htm'){
		
		$html = @file_get_contents("$service_agenda/$periodo/$curso.$formato");
		if($html === FALSE) {
			if($formato == 'htm')
				return $this->get_html($service_agenda, $periodo, $curso, 'html');
			else
				return array(
					'status' => false,
				    'error'=>'La ruta presenta errores con los parametos indicados', 
					'url' => "$service_agenda/$periodo/$curso.$formato",
					'code' => 404
				);
		} else {
			return array(
				'status' => true,
				'content' => $html,
				'format' => $formato
			);
		}
			
	}
}