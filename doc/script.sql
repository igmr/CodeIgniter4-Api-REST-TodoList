

DROP DATABASE IF EXISTS CodeIgniter4_Api_REST_Todo_List;
CREATE DATABASE IF NOT EXISTS CodeIgniter4_Api_REST_Todo_List;
USE CodeIgniter4_Api_REST_Todo_List;

DROP TABLE IF EXISTS list;
CREATE TABLE list(
	id			INT(15)			NOT	NULL	AUTO_INCREMENT,
	name		VARCHAR(128)	NOT	NULL	DEFAULT	'',
	created_by	INT(15)	NOT		NULL		DEFAULT	0,
	created_at	TIMESTAMP		NOT	NULL	DEFAULT	current_timestamp(),
	updated_at	TIMESTAMP			NULL	DEFAULT	NULL,
	PRIMARY KEY	(id),
	UNIQUE KEY	id(id),
	UNIQUE KEY	name(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO list(name) VALUES('(predeterminado)'); 

DROP TABLE IF EXISTS task;
CREATE TABLE task(
	id			INT(15)		NOT	NULL	AUTO_INCREMENT,
	list_id		INT(15)		NOT	NULL	DEFAULT	1,
	tittle		VARCHAR(65)	NOT	NULL	DEFAULT	'',
	note		TEXT		NOT	NULL,
	today		tinyINT(1)	NOT	NULL	DEFAULT	0,
	important	tinyINT(1)	NOT	NULL	DEFAULT	0,
	completed	tinyINT(1)	NOT	NULL	DEFAULT	0,
	created_by	INT(15)		NOT	NULL	DEFAULT	1,
	created_at	TIMESTAMP	NOT	NULL	DEFAULT	current_timestamp(),
	updated_at	TIMESTAMP		NULL	DEFAULT	NULL,
	PRIMARY KEY (id),
	UNIQUE KEY id(id),
	CONSTRAINT task_list_id_foreign FOREIGN KEY(list_id) REFERENCES list(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO task(list_id,tittle,note,important,completed)
VALUES(1 ,'(predeterminado)','(predeterminado)', false, false); 


DROP TABLE IF EXISTS task_item;
CREATE TABLE task_item	(
	id			INT(15)		NOT	NULL	AUTO_INCREMENT,
	task_id		INT(15)		NOT	NULL	DEFAULT	1,
	description	TEXT		NOT	NULL,
	completed	tinyINT(1)	NOT	NULL	DEFAULT	0,
	created_by	INT(15)		NOT	NULL	DEFAULT	1,
	created_at	TIMESTAMP	NOT	NULL	DEFAULT	current_timestamp(),
	updated_by	TIMESTAMP		NULL	DEFAULT	NULL,
	PRIMARY KEY (id),
	UNIQUE KEY id (id),
	CONSTRAINT task_item_task_id_foreign FOREIGN KEY (task_id) REFERENCES task(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

DROP TABLE IF EXISTS user;
CREATE TABLE user(
	id			INT(15)			NOT	NULL	AUTO_INCREMENT,
	full_name	VARCHAR(65)		NOT	NULL	DEFAULT	'',
	user_name	VARCHAR(65)		NOT	NULL	DEFAULT	'',
	email		VARCHAR(65)		NOT	NULL	DEFAULT	'',
	password	VARCHAR(512)	NOT	NULL	DEFAULT	'',
	created_at	TIMESTAMP		NOT	NULL	DEFAULT	current_timestamp(),
	updated_at	TIMESTAMP			NULL	DEFAULT	NULL,
	deleted_at	TIMESTAMP			NULL	DEFAULT	NULL,
	PRIMARY KEY (id),
	UNIQUE KEY id(id),
	UNIQUE KEY user_name(user_name),
	UNIQUE KEY email(email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

