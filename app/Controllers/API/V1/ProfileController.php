<?php

namespace App\Controllers\API\V1;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Entities\User;

class ProfileController extends BaseController
{
	protected $userModel;
	public function __construct()
	{
		$this->userModel = new UserModel();
	}
	//*	****************************************************************************
	//*	Methods HTTP
	//*	****************************************************************************
	public function index()
	{
		try {
			//*	****************************************************************************
			//*	Recuperar información de usuario por el token JWT
			//*	****************************************************************************
			$info = $this->getInfoUserFromJWT();
			if(is_null($info))
			{
				throw new \Exception('Usuario no localizado');
			}
			$userID = $info->id;
			//*	****************************************************************************
			//*	Recuperar información de usuario
			//*	****************************************************************************
			$data = $this->findUserById($userID)?:null;
			if(is_null($data))
			{
				throw new \Exception('No pudo ser recuperado, la información del usuario');
			}
			return $this->respond($data);
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	public function edit()
	{
		try {
			//*	****************************************************************************
			//*	Recuperar información de usuario por el token JWT
			//*	****************************************************************************
			$info = $this->getInfoUserFromJWT();
			if(is_null($info))
			{
				throw new \Exception('Usuario no localizado');
			}
			$userID = $info->id;
			//*	******************************************************
			//*	Proceso de edición
			//*	******************************************************
			$req = $this->request->getVar();
			$user = new User((array) $req);
			return $this->rewrite($user, $userID);
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	//*	****************************************************************************
	//*	Methods Queries
	//*	****************************************************************************
	//*	GET
	private function findUserById(string $userId)
	{
		return $this->userModel
			->select(['id', 'full_name as name', 'user_name as user', 'email'])
			->where('id', $userId)
			->first();
	}
	//*	UPDATED
	private function rewrite(User $user, string $userId)
	{
		$edited = $this->userModel
			->update($userId, $user);
		if($edited === false)
		{
			return $this->fail($this->userModel->errors());
		}
		return $this->respond(['general' => 'Perfil actualizado']);
	}
}
