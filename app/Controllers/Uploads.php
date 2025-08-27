<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Uploads extends Controller
{
	public function show($filename)
	{
		$path = WRITEPATH . 'uploads/' . $filename;
		if (!is_file($path)) {
			return service('response')->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setBody('Archivo no encontrado');
		}

		$mime = mime_content_type($path) ?: 'application/octet-stream';
		return service('response')->setHeader('Content-Type', $mime)->setBody(file_get_contents($path));
	}
}
