CREATE DATABASE IF NOT EXISTS `TaskMentor`;
USE `TaskMentor`;

CREATE TABLE `Materia` (
	`idMateria` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`nome` VARCHAR(150) NOT NULL,
	`docente` VARCHAR(150) NOT NULL
);

CREATE TABLE `ImageProfile` (
	`idImageProfile` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`nome` VARCHAR(100) NOT NULL UNIQUE,
	`path` VARCHAR(255) NOT NULL
);

CREATE TABLE `Aluno` (
	`idAluno` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`nome` VARCHAR(150) NOT NULL,
	`email` VARCHAR(255) NOT NULL UNIQUE,
	`senha` VARCHAR(255) NOT NULL,
	
	`idImageProfile` INTEGER,
	CONSTRAINT FK_ID_IMAGE_PROFILE 
	FOREIGN KEY(idImageProfile)
	REFERENCES `ImageProfile`(idImageProfile)
);

CREATE TABLE `Selo` (
	`idSelo` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`tipo` VARCHAR(150) NOT NULL,
	
	`idAluno` INTEGER,
	CONSTRAINT FK_ID_ALUNO
	FOREIGN KEY(idAluno)
	REFERENCES `Aluno`(idAluno)
);

CREATE TABLE `SeloRecebido` (
	`idSeloRecebido` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	
	`idSelo` INTEGER,
	CONSTRAINT FK_ID_SELO
	FOREIGN KEY(idSelo)
	REFERENCES `Selo`(idSelo),
	
	`idAluno` INTEGER,
	CONSTRAINT FK_ID_ALUNO_SELO
	FOREIGN KEY(idAluno)
	REFERENCES `Aluno`(idAluno)
);

CREATE TABLE `Tarefa` (
	`idTarefa` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`titulo` VARCHAR(255) NULL,
	`descricao` TEXT NULL,
	`status` ENUM("pendente", "em andamento", "concluída", "em atraso") NOT NULL,
	`prioridde` ENUM("baixa", "média", "alta", "muito alta") NOT NULL,
	`dataCriacao` DATETIME NOT NULL,
	`dataVencimento` DATETIME NOT NULL,
	
	`idAluno` INTEGER,
	CONSTRAINT FK_ID_ALUNO_TAREFA
	FOREIGN KEY(idAluno)
	REFERENCES `Aluno`(idAluno),
	
	`idMateria` INTEGER,
	CONSTRAINT FK_ID_MATERIA
	FOREIGN KEY(idMateria)
	REFERENCES `Materia`(idMateria)
);







