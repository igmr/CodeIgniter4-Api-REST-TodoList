<?php

namespace App\Controllers\API\V1;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ListModel;
use App\Entities\Lists;

class ListController extends BaseController
{
	protected $listModel;
	public function __construct()
	{
		$this->listModel = new ListModel();
	}
	//*	****************************************************************************
	//*	Methods HTTP
	//*	****************************************************************************
	public function index()
	{
		try
		{
			//*	****************************************************************************
			//*	Recuperar información de usuario por el token JWT
			//*	****************************************************************************
			$info = $this->getInfoUserFromJWT();
			$userID = $info->id;
			//*	****************************************************************************
			//*	Proceso de consulta
			//*	****************************************************************************
			$data = $this->findAllListByUserId($userID)?:null;
			if(is_null($data))
			{
				return $this->respond([]);
			}
			return $this->respond($data);
		}catch(\Exception $e)
		{
			return $this->failServerError($e->getMessage());
		}
	}
	public function show($id)
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
			//*	Proceso de consulta
			//*	****************************************************************************
			$data = $this->findListByIdAndUserId($id, $userID)?:null;
			if(is_null($data))
			{
				return $this->respond([]);
			}
			return $this->respond($data);
		} catch (\exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	public function store()
	{
		try {
			//*	****************************************************************************
			//*	Recuperar información de usuario por el token JWT
			//*	****************************************************************************
			$info = $this->getInfoUserFromJWT();
			$userID = $info->id;
			//*	****************************************************************************
			//*	Proceso de registro
			//*	****************************************************************************
			$req = $this->request->getVar();
			//*	Validar si existe lista
			$data = $this->findListByNameAndUserId($req->name, $userID)?: null;
			if(!is_null($data))
			{
				return $this->fail(['name'	=> 'Inválido']);
			}
			$lists = new Lists((array) $req);
			$lists->created_by = $userID;
			return $this->attach($lists);
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	public function edit($id)
	{
		try {
			//*	****************************************************************************
			//*	Recuperar información de usuario por el token JWT
			//*	****************************************************************************
			$info = $this->getInfoUserFromJWT();
			$userID = $info->id;
			//*	****************************************************************************
			//*	Proceso de edición
			//*	****************************************************************************
			$req = $this->request->getVar();
			$validList = $this->findListByIdAndUserId($id, $userID)?:null;
			if(is_null($validList))
			{
				throw new \Exception('Lista no localizada');
			}
			//*	comprobar nombre de lista
			$validListName = $this->findListByNameAndIdAndUserId($req->name, $id, $userID)?:null;
			if(!is_null($validListName))
			{
				return $this->fail(['name' => 'Ya existe']);
			}
			$lists = new Lists((array) $req);
			return $this->rewrite($lists, $id);
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	public function remove($id)
	{
		try {
			//*	******************************************************
			//*	Datos Jwt
			//*	******************************************************
			$info = $this->getInfoUserFromJWT();
			$userID = $info->id;
			//*	******************************************************
			//*	Datos de lista
			//*	******************************************************
			//*	Validar lista
			if((int) $id <= 1)
			{
				throw new Exception('Algo salio mal(1)');
			}
			//*	Eliminar lista
			$deleted = $this->listModel->delete($id);
			if($deleted !== true)
			{
				throw new \Exception('Algo salio mal(2)');
			}
			return $this->respond(['general' => 'Lista eliminada']);
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	//*	****************************************************************************
	//*	Methods Queries
	//*	****************************************************************************
	//*	GET
	private function findAllListByUserId(string $userId)
	{
		return $this->listModel
			->select(['id', 'name'])
			->where('created_by', $userId)
			->findAll();
	}
	private function findListByIdAndUserId(string $id, string $userId)
	{
		return $this->listModel
			->select(['id', 'name'])
			->where('created_by', $userId)
			->where('id', $id)
			->first();
	}
	private function findListByNameAndUserId(string $name, string $userId)
	{
		return $this->listModel
			->where('name', $name)
			->where('created_by', $userId)
			->first();
	}
	private function findListByNameAndIdAndUserId(string $name, string $id, string $userId)
	{
		return $this->listModel
			->where('name', $name)
			->where('created_by', $userId)
			->whereNotIn('id',[$id])
			->findAll();
	}
	//*	CREATED
	private function attach(Lists $data)
	{
		$created = $this->listModel->insert($data);
		if($created === false)
		{
			return $this->fail($this->listModel->errors());
		}
		return $this->respondCreated(['general' => 'Lista registrada']);
	}
	//*	UPDATED
	private function rewrite(lists $data, string $id)
	{
		$edited = $this->listModel->update($id, $data);
		if($edited === false)
		{
			return $this->fail($this->listModel->errors());
		}
		return $this->respond(['general'=> 'Lista editada']);
	}
}
