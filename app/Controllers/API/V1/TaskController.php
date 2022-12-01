<?php

namespace App\Controllers\API\V1;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TaskModel;
use App\Models\ListModel;
use App\Entities\Task;

class TaskController extends BaseController
{
	protected $taskModel;
	protected $listModel;
	public function __construct()
	{
		$this->taskModel = new TaskModel();
		$this->listModel = new ListModel();
	}
	//*	****************************************************************************
	//*	Methods HTTP
	//*	****************************************************************************
	public function index($option)
	{
		try {
			//*	****************************************************************************
			//*	Recuperar información de usuario por el token JWT
			//*	****************************************************************************
			$info = $this->getInfoUserFromJWT();
			$userID = $info->id;
			//*	****************************************************************************
			//*	Proceso de consulta
			//*	****************************************************************************
			switch($option)
			{
				case 'all':
					$tittle = 'Todas la tareas';
					break;
				case 'today':
					$tittle = 'Tareas de hoy';
					break;
				case 'complete':
					$tittle = 'Tareas completadas';
					break;
				case 'important':
					$tittle = 'Tareas importantes';
					break;
			}
			$data = $this->findAllTaskByOption($option, $userID)?:null;
			if(is_null($data))
			{
				return $this->respond([]);
			}
			if(count($data) == 1)
			{
				return $this->respond([$data]);
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
			$userID = $info->id;
			//*	****************************************************************************
			//*	Proceso de consulta
			//*	****************************************************************************
			$data= $this->findTaskByIdAndUserId($id, $userID);
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
			$userID = $info->id;
			//*	****************************************************************************
			//*	Proceso de registro
			//*	****************************************************************************
			$req = $this->request->getVar();
			$task = new Task((array) $req);
			$task->created_by = $userID;
			if(!isset($task->list_id))
			{
				$task->list_id = 1;
			}
			else{
				$data = $this->findListById($task->list_id, $userID);
				if(count($data) == 0)
				{
					return $this->fail(['task_id'=> 'No existe']);
				}
			}
			if(!isset($task->completed))
			{
				$task->completed = false;
			}
			if(!isset($task->today))
			{
				$task->today = false;
			}
			if(!isset($task->important))
			{
				$task->important = false;
			}
			return $this->attach($task);
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
			$userID = $info->id;
			//*	****************************************************************************
			//*	Proceso de edición
			//*	****************************************************************************
			switch($option)
			{
				case 'all':
					$req = $this->request->getVar();
					//*	Buscar tarea
					$validTask = $this->findTaskByIdAndUserId($id, $userID)?:null;
					if(is_null($validTask))
					{
						return $this->fail(['general' => 'Información inválido']);
					}
					//*	Validar titulo y nota
					if(isset($req->tittle) || isset($req->note))
					{
						$task = new Task((array) $req);
						return $this->rewrite($task, $id);
					}
					return $this->fail(['general' => 'Información inválido']);
					break;
				case 'today':
					$data = $this->findTaskByIdAndUserId($id, $userID)?:null;
					if(is_null($data))
					{
						return $this->fail(['general' => 'Información inválido']);
					}
					$task = new Task();
					$task->today = !$data->today;
					$tittle = 'Tarea agregada ha hoy';
					if($task->today == false)
					{
						$tittle = 'Tarea eliminado de hoy';
					}
					return $this->rewrite($task, $id, $tittle);
					break;
				case 'complete':
					$data = $this->findTaskByIdAndUserId($id, $userID)?:null;
					if(is_null($data))
					{
						return $this->fail(['general' => 'Información inválida']);
					}
					$task = new Task();
					$task->completed= !$data->completed;
					$tittle = 'Tarea completada';
					if($task->completed === false)
					{
						$tittle= 'Tarea no completada';
					}
					return $this->rewrite($task, $id, $tittle);
					break;
				case 'important':
					$data = $this->findTaskByIdAndUserId($id, $userID)?:null;
					if(is_null($data))
					{
						return $this->fail(['general' => 'Información inválida']);
					}
					$task = new Task();
					$task->important = !$data->important;
					$tittle = 'Tarea importante completado';
					if($task->important === false)
					{
						$tittle = 'Tarea importante no completado';
					}
					return $this->rewrite($task, $id, $tittle);
					break;
			}
		} catch (\Exception $e) {
			return $this->failServerError($e->getMessage());
		}
	}
	//*	****************************************************************************
	//*	Methods Queries
	//*	****************************************************************************
	//*	GET
	private function findAllTaskByOption(string $option, string $userId)
	{
		switch($option)
		{
			case 'all':
				return $this->taskModel
					->select(['id', 'list_id AS list', 'tittle', 'note', 'today','completed', 'important'])
					->where('created_by', $userId)
					->findAll();
				break;
			case 'today':
				return $this->taskModel
					->select(['id', 'list_id as list', 'tittle', 'note'])
					->where('created_by', $userId)
					->where('today', true)
					->findAll();
				break;
			case 'complete':
				return $this->taskModel
					->select(['id', 'list_id AS list', 'tittle', 'note'])
					->where('created_by', $userId)
					->where('completed', true)
					->findAll();
				break;
			case 'important':
				return $this->taskModel
					->select(['id', 'list_id AS list', 'tittle', 'note'])
					->where('created_by', $userId)
					->where('important', true)
					->findAll();
				break;
		}
	}
	private function findTaskByIdAndUserId(string $id, string $userId)
	{
		return $this->taskModel
			->select(['id', 'list_id as list', 'tittle', 'note', 'today', 'important', 'completed'])
			->where('created_by', $userId)
			->where('id', $id)
			->first();
	}
	private function findListById(string $id, string $userId)
	{
		return $this->listModel
			->where('id', $id)
			->where('created_by', $userId)
			->findAll();
	}
	//*	CREATED
	private function attach(Task $data)
	{
		$created = $this->taskModel->insert($data);
		if($created === false)
		{
			return $this->fail($this->taskModel->errors());
		}
		return $this->respondCreated(['general' => 'Tarea registrada']);
	}
	//*	UPDATED
	private function rewrite(Task $data, string $id, string $tittle= 'Tarea editada')
	{
		$edited = $this->taskModel->update($id, $data);
		if($edited === false)
		{
			return $this->fail($this->taskModel->errors());
		}
		return $this->respond(['general' => $tittle]);
	}
}
