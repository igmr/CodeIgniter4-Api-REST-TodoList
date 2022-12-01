<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
	protected $DBGroup				=	'default';
	protected $table				=	'task';
	protected $primaryKey			=	'id';
	protected $useAutoIncrement		=	true;
	protected $insertID				=	0;
	protected $returnType			=	\App\Entities\Task::class;
	protected $useSoftDeletes		=	false;
	protected $protectFields		=	true;
	protected $allowedFields		=	[ 'id', 'list_id', 'tittle', 'note'
									, 'today',	'important', 'completed'];

	//*	****************************************************************************
	//*	Dates
	//*	****************************************************************************
	protected $useTimestamps		=	true;
	protected $dateFormat			=	'datetime';
	protected $createdField			=	'created_at';
	protected $updatedField			=	'updated_at';
	protected $deletedField			=	'deleted_at';

	//*	****************************************************************************
	//*	Validation
	//*	****************************************************************************
	protected $validationRules		=	[
		'tittle'	=>	'required',
	];
	protected $validationMessages	=	[
		'tittle'	=>	[
							'required'	=>	'Es requerido',
						],
	];
	protected $skipValidation		=	false;
	protected $cleanValidationRules	=	true;

	//*	****************************************************************************
	//*	Callbacks
	//*	****************************************************************************
	protected $allowCallbacks		=	true;
	protected $beforeInsert			=	[];
	protected $afterInsert			=	[];
	protected $beforeUpdate			=	[];
	protected $afterUpdate			=	[];
	protected $beforeFind			=	[];
	protected $afterFind			=	[];
	protected $beforeDelete			=	[];
	protected $afterDelete			=	[];
}
