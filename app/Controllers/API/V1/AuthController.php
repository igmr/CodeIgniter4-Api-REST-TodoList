<?php

namespace App\Controllers\API\V1;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Entities\User;

class AuthController extends BaseController
{
	protected $userModel;
	public function __construct()
	{
		$this->userModel = new UserModel();
	}
	//*	******************************************************
	//*	Methods HTTP
	//*	******************************************************
	public function index()
	{
		try
		{
			//*	****************************************************************************
			//*	Datos de usuario
			//*	****************************************************************************
			$req = $this->request->getVar();
			//*	Encontrar y validar existencia de usuario
			$data = $this->findUserByEmail($req->email)?:null;
			if(is_null($data))
			{
				throw new \Exception('Correo y/o contraseña son inválidos (1)');
			}
			//*	Encriptar y validar contraseña
			$passwordEncrypt = hash("sha512", $req->password);
			if($passwordEncrypt !== $data->password)
			{
				throw new \Exception('Correo y/o contraseña son inválidos (2)');
			}
			//*	****************************************************************************
			//*	JWT: token
			//*	****************************************************************************
			helper('jwt');
			$token = getSignedJwtUser($data->id);
			$payload = [
				'message'	=>	'Bienvenido '. $data->full_name,
				'token'		=>	$token,
			];
			return $this->respond($payload);
		}
		catch(\Exception $e)
		{
			return $this->failServerError($e->getMessage());
		}
	}
	public function register()
	{
		try{
			//*	*****************************************************
			//*	Datos de usuario
			//*	*****************************************************
			$req = $this->request->getVar();
			$user = new User((array) $req);
			return $this->attach($user);
		}catch(\Exception $e)
		{
			return $this->failServerError($e->getMessage());
		}
	}
	//*	******************************************************
	//*	Method Queries
	//*	******************************************************
	//*	GET
	private function findUserByEmail(string $email)
	{
		return $this->userModel
			->where('email', $email)
			->first();
	}
	//*	CREATED
	private function attach(User $data)
	{
		$created = $this->userModel->insert($data);
		if($created === false)
		{
			return $this->fail($this->userModel->errors());
		}
		return $this->respond(['general' => 'Usuario registrado']);
	}
}
