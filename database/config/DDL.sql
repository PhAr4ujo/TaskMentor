CREATE DATABASE IF NOT EXISTS `TaskMentor`;
USE `TaskMentor`;

CREATE TABLE `Materia` (
	`idMateria` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`nome` VARCHAR(150) NOT NULL,
	`docente` VARCHAR(150) NOT NULL,
	`idMateriaCriptografado` TEXT NOT NULL,
	`idAluno` INTEGER,
	FOREIGN KEY(idAluno)
	REFERENCES `Aluno`(idAluno)
	ON DELETE CASCADE
);

CREATE TABLE `ImageProfile` (
	`idImageProfile` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`nome` VARCHAR(100) NOT NULL,
	`path` VARCHAR(255) NOT NULL
);

CREATE TABLE `Aluno` (
	`idAluno` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`nome` VARCHAR(150) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`senha` VARCHAR(255) NOT NULL,
	`token` TEXT,
	`tempoExpiracao` INT,
	
	`idImageProfile` INTEGER,
	FOREIGN KEY(idImageProfile)
	REFERENCES `ImageProfile`(idImageProfile)
	ON DELETE CASCADE
);

CREATE TABLE `Selo` (
	`idSelo` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`tipo` VARCHAR(150) NOT NULL
);


CREATE TABLE `SeloRecebido` (
	`idSeloRecebido` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	
	`idSelo` INTEGER,
	FOREIGN KEY(idSelo)
	REFERENCES `Selo`(idSelo)
	ON DELETE CASCADE,
	
	`idAluno` INTEGER,
	FOREIGN KEY(idAluno)
	REFERENCES `Aluno`(idAluno)
	ON DELETE CASCADE
);

CREATE TABLE `Tarefa` (
	`idTarefa` INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL,
	`idTarefaCriptografado` TEXT NOT NULL,
	`titulo` VARCHAR(255) NULL,
	`descricao` TEXT NULL,
	`status` ENUM("pendente", "em andamento", "concluída", "em atraso", "arquivada") NOT NULL,
	`prioridade` ENUM("baixa", "média", "alta", "muito alta") NOT NULL,
	`dataCriacao` DATETIME NOT NULL,
	`dataVencimento` DATETIME NOT NULL,
	
	`idAluno` INTEGER,
	FOREIGN KEY(idAluno)
	REFERENCES `Aluno`(idAluno)
	ON DELETE CASCADE,
	
	`idMateria` INTEGER,
	FOREIGN KEY(idMateria)
	REFERENCES `Materia`(idMateria)
	ON DELETE CASCADE
);


