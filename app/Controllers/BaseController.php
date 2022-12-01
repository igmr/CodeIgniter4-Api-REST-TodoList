<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\API\ResponseTrait;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
	/**
	 * Instance of the main Request object.
	 *
	 * @var CLIRequest|IncomingRequest
	 */
	protected $request;

	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = [];

	/**
	 * Constructor.
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		// Preload any models, libraries, etc, here.

		// E.g.: $this->session = \Config\Services::session();
	}

	//*	****************************************************************************
	//*	Obtener información de usuario JWT
	//*	****************************************************************************
	public function getInfoUserFromJWT()
	{
		try {
			//*	Recuperar información del usuario
			$user = $this->request->user;
			//*	Validar información del usuario
			if(is_null($user))
			{
				return $this->fail([['general' => 'Error al recuperar información']],401);
			}
			return $user;
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	//*	****************************************************************
	//*	Response API REST
	//*	****************************************************************
	use ResponseTrait;
	
}
