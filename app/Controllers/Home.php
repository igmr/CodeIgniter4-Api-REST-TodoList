<?php

namespace App\Controllers;

class Home extends BaseController
{
	//*	****************************************************************************
	//*	Methods HTTP
	//*	****************************************************************************
	public function index()
	{
		$data = [[
			'general'	=>	'API REST - CodeIgniter 4',
			'base_api'	=>	base_url().'/api/v1'
		],];
		return $this->respond($data);
	}
}
