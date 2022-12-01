<?php

namespace App\Controllers\API\V1;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TaskItemModel;
use App\Models\TaskModel;
use App\Entities\TaskItem;
use App\Entities\Task;

class TaskItemController extends BaseController
{
	protected $taskItemModel;
	protected $taskModel;
	public function __construct()
	{
		$this->taskItemModel = new TaskItemModel();
		$this->taskModel = new TaskModel();
	}
	//*	****************************************************************************
	//*	Methods HTTP
	//*	****************************************************************************
	public function index($id)
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
			$data = $this->findAllTaskItemByTaskIdAndUserId($id, $userID)?:null;
			if(is_null($data))
			{
				return $this->respond([]);
			}
			return $this->respond($data);
		} catch (\Exception $e) {
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
			$data = $this->findTaskItemByIdAndUserId($id, $userID)?:null;
			if(is_null($data))
			{
				return $this->respond([]);
			}
			return $this->respond($data);
		} catch (\Exception $e) {
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
			if(is_null($info))
			{
				throw new \Exception('Usuario no localizado');
			}
			$userID = $info->id;
		
			//*	****************************************************************************
			//*	Proceso de registro
			//*	****************************************************************************
			$req = $this->request->getVar();
			$taskItem = new TaskItem((array) $req);
			//*	Valida tarea
			if(isset($taskItem->task_id))
			{
				$task = $this->findTaskByIdAndUserId($taskItem->task_id, $userID);
				if(is_null($task))
				{
					return $this->fail([['task_id' => 'No localizado']]);
				}
			}
			//*	Valida tarea completada
			if(!isset($taskItem->completed))
			{
				$taskItem->completed = false;
			}
			$taskItem->created_by = $userID;
			return $this->attach($taskItem);
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	public function edit($option, $id)
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
			//*	Proceso de edición
			//*	****************************************************************************
			$req = $this->request->getVar();
			$existTaskItem = $this->findTaskItemByIdAndUserId($id, $userID)?:null;
			$taskItem = new TaskItem();
			if(is_null($existTaskItem))
			{
				throw new \Exception('Detalle de tarea, no localizado');
			}
			switch($option)
			{
				case 'all':
					$req = $this->request->getVar();
					if(isset($req->description))
					{
						$taskItem->description = $req->description;
						return $this->rewrite($taskItem, $id);
					}
					return $this->fail(['general' => 'Información inválida']);
					break;
				case 'complete':
					$data = $this->findTaskItemByIdAndUserId($id, $userID)?:null;
					if(is_null($data))
					{
						return $this->fail(['general' => 'Detalle de tarea, no localizada']);
					}
					$taskItem->completed = !$data->completed;
					$tittle = 'Detalle de tarea, completada';
					if($taskItem->completed === false)
					{
						$tittle = 'Detalle de tarea, no completada';
					}
					return $this->rewrite($taskItem, $id, $tittle);
					break;
			}
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	public function remove($id)
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
			//*	Proceso de eliminación
			//*	****************************************************************************
			$data  = $this->taskItemModel->delete($id);
			if(!$data)
			{
				return $this->fail(['general' => 'Algo salio mal']);
			}
			return $this->respond(['general'=> 'Detalle de tarea, eliminada.']);
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	//*	****************************************************************************
	//*	Methods Queries
	//*	****************************************************************************
	//*	GET
	private function findAllTaskItemByTaskIdAndUserId(string $taskId, string $userId)
	{
		return $this->taskItemModel
			->select(['id', 'task_id AS task', 'description', 'completed'])
			->where('task_id',$taskId)
			->where('created_by', $userId)
			->findAll();
	}
	private function findTaskItemByIdAndUserId(string $id, string $userId)
	{
		return $this->taskItemModel
			->select(['id', 'task_id AS task', 'description', 'completed'])
			->where('id', $id)
			->where('created_by', $userId)
			->first();
	}
	private function findTaskByIdAndUserId(string $id, string $userId)
	{
		return $this->taskModel
			->where('id', $id)
			->where('created_by', $userId)
			->first();
	}
	//*	CREATED
	private function attach(TaskItem $data)
	{
		$created = $this->taskItemModel->insert($data);
		if($created === false)
		{
			return $this->fail($this->taskItemModel->errors());
		}
		return $this->respondCreated(['general' => 'Detalle de tarea registrada']);
	}
	//*	UPDATED
	private function rewrite(TaskItem $data, string $id, string $tittle = 'Edición exitoso')
	{
		$edited = $this->taskItemModel->update($id, $data);
		if($edited === false)
		{
			return $this->fail($this->taskItemModel->errors());
		}
		return $this->respond(['general' => $tittle]);
	}
}
