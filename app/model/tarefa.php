<?php
include_once "../../database/connection.php";
$requestData = $_REQUEST;
$dados = array();
$statusDeTarefa = array("pendente", "em andamento", "concluída", "em atraso", "arquivada");
$prioridadesDeTarefa = array("baixa", "média", "alta", "muito alta");

if ($requestData["operation"] == "create") {
  try {
    if (empty($requestData["titulo"])) {
      throw new PDOException("Título não foi informado");
    }

    if (empty($requestData["status"])) {
      throw new PDOException("Selecione um status para essa tarefa");
    }

    if (empty($requestData["prioridade"])) {
      throw new PDOException("Define a prioridade dessa tarefa");
    }

    if (empty($requestData["dataCriacao"])) {
      throw new PDOException("Informe a data de criação dessa tarefa");
    }

    if (empty($requestData["dataVencimento"])) {
      throw new PDOException("Informe a data de vencimento dessa tarefa");
    }

    if (empty($requestData["token"])) {
      throw new PDOException("Token de autenticação inválido");
    }

    if (empty($requestData["idMateriaCriptografado"])) {
      throw new PDOException("A Matéria não foi informada");
    }

    $token = $requestData["token"];
    $idMateriaCriptografado = $requestData["idMateriaCriptografado"];

    $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$token, time()]);

    $dataAluno = $stmt->fetch();
    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Token de autenticação inválido ou expirado");
    }

    $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idMateriaCriptografado]);

    $dataMateria = $stmt->fetch();
    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Essa matéria não está cadastrada");
    }

    $idAluno = $dataAluno["idAluno"];
    $idMateria = $dataMateria["idMateria"];
    $idTarefaCriptografado = substr(md5(uniqid(rand(), true)), 0, 16);
    $titulo = $requestData["titulo"];
    if (empty($requestData["descricao"])) {
      $descricao = null;
    } else {
      $descricao = $requestData["descricao$descricao"];
    }
    $status = $requestData["status"];
    $prioridade = $requestData["prioridade"];
    $dataCriacao = $requestData["dataCriacao"];
    $dataVencimento = $requestData["dataVencimento"];

    if (!in_array($status, $statusDeTarefa)) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Esse status de tarefa é inválido"
      ));
      return;
    }

    if (!in_array($prioridade, $prioridadesDeTarefa)) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Essa prioridade de tarefa é inválida"
      ));
      return;
    }

    $sql = "INSERT INTO Tarefa (titulo, descricao,`status`, prioridade, dataCriacao, dataVencimento, idTarefaCriptografado, idAluno, idMateria) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $database->prepare($sql);
    $stmt->execute([$titulo, $descricao, $status, $prioridade, $dataCriacao, $dataVencimento, $idTarefaCriptografado, $idAluno, $idMateria]);

    echo json_encode($dados = array(
      "type" => "success",
      "message" => "Tarefa adicionada com sucesso"
    ));
    return;
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Não foi possível criar essa tarefa",
      "more" => $err->getMessage()
    ));
    return;
  }
}

if ($requestData["operation"] == "read") {
  try {
    if (empty($requestData["token"])) {
      throw new PDOException("Token inválido");
    }

    $token = $requestData["token"];

    $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$token, time()]);

    $dataAluno = $stmt->fetch();
    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Token de autenticação inválido ou expirado");
    }

    $idAluno = $dataAluno["idAluno"];

    if (empty($requestData["idTarefaCriptografado"])) {
      $sql =  "SELECT Tarefa.*, Aluno.nome AS Nome, Materia.nome AS Matéria FROM Tarefa INNER JOIN Aluno ON Tarefa.idAluno = Aluno.idALuno INNER JOIN Materia ON Materia.idMateria = Tarefa.idMateria WHERE Aluno.idAluno = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idAluno]);

      if ($stmt->rowCount() == 0) {
        echo json_encode($dados = array(
          "type" => "success",
          "message" => "Não há tarefas para listar",
        ));
        return;
      }

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dados[] = $row;
      }

      echo json_encode($dados);
      return;
    }

    $idTarefaCriptografado = $requestData["idTarefaCriptografado"];

    $sql =  "SELECT Tarefa.*, Aluno.nome AS Nome, Materia.nome AS Matéria FROM Tarefa INNER JOIN Aluno ON Tarefa.idAluno = Aluno.idALuno INNER JOIN Materia ON Materia.idMateria = Tarefa.idMateria WHERE Tarefa.idTarefaCriptografado = ? AND Aluno.idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idTarefaCriptografado, $idAluno]);

    if ($stmt->rowCount() == 0) {
      echo json_encode($dados = array(
        "type" => "success",
        "message" => "Não há tarefas para listar",
      ));
      return;
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $dados[] = $row;
    }

    echo json_encode($dados);
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Não foi possível listar as tarefas",
      "more" => $err->getMessage()
    ));
    return;
  }
}

if ($requestData["operation"] == "update") {
  try {
    if (empty($requestData["idTarefaCriptografado"])) {
      throw new PDOException("A tarefa a ser editada não foi informada");
    }

    $idTarefaCriptografado = $requestData["idTarefaCriptografado"];

    if (empty($requestData["token"])) {
      throw new PDOException("Token inválido");
    }

    $token = $requestData["token"];

    $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$token, time()]);

    $dataAluno = $stmt->fetch();
    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Token de autenticação inválido ou expirado");
    }

    $idAluno = $dataAluno["idAluno"];

    $sql =  "SELECT Tarefa.*, Aluno.nome AS Nome, Materia.nome AS Matéria FROM Tarefa INNER JOIN Aluno ON Tarefa.idAluno = Aluno.idALuno INNER JOIN Materia ON Materia.idMateria = Tarefa.idMateria WHERE Tarefa.idTarefaCriptografado = ? AND Aluno.idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idTarefaCriptografado, $idAluno]);

    if ($stmt->rowCount() == 0) {
      echo json_encode($dados = array(
        "type" => "success",
        "message" => "Não há tarefas para listar",
      ));
      return;
    }

    $dadosTarefa = $stmt->fetch();

    if(empty($requestData["titulo"])) {
      $titulo = $dadosTarefa["titulo"];
    } else {
      $titulo = $requestData["titulo"];
    }

    if(empty($requestData["descricao"])) {
      $descricao = $dadosTarefa["descricao"];
    } else {
      $descricao = $requestData["descricao"];
    }

    if(empty($requestData["status"])) {
      $status = $dadosTarefa["status"];
    } else {
      if(!in_array($requestData["status"], $statusDeTarefa)) {
        throw new PDOException("Status de tarefa inválido");
      }
      $status = $requestData["status"];
    }

    if(empty($requestData["prioridade"])) {
      $prioridade = $dadosTarefa["prioridade"];
    } else {
      if(!in_array($requestData["prioridade"], $prioridadesDeTarefa)) {
        throw new PDOException("Prioridade de tarefa inválida");
      }
      $prioridade = $requestData["prioridade"];
    }

    if(empty($requestData["dataVencimento"])) {
      $dataVencimento = $dadosTarefa["dataVencimento"];
    } else {
      $dataVencimento = $requestData["dataVencimento"];
    }

    if(empty($requestData["idMateria"])) {
      $idMateria = $dadosTarefa["idMateria"];
    } else {
      $idMateria = $requestData["idMateria"];
    }

    $sql = "UPDATE Tarefa SET titulo = ?, descricao = ?, `status` = ?, prioridade = ?, dataVencimento = ? WHERE idTarefaCriptografado = ? AND idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$titulo, $descricao, $status, $prioridade, $dataVencimento, $idTarefaCriptografado, $idAluno]);

    echo json_encode($dados = array(
      "type" => "success",
      "messaage" => "Tarefa alterada com sucesso"
    ));
    return;

  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "error",
      "messaage" => "Não foi possível atualizar essa tarefa",
      "more" => $err->getMessage()
    ));
    return;
  }
}

if($requestData["operation"] == "delete") {
  try {
    if (empty($requestData["idTarefaCriptografado"])) {
      throw new PDOException("A tarefa a ser excluída não foi informada");
    }

    $idTarefaCriptografado = $requestData["idTarefaCriptografado"];

    if (empty($requestData["token"])) {
      throw new PDOException("Token inválido");
    }

    $token = $requestData["token"];

    $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$token, time()]);

    $dataAluno = $stmt->fetch();
    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Token de autenticação inválido ou expirado");
    }

    $idAluno = $dataAluno["idAluno"];

    $sql =  "SELECT Tarefa.*, Aluno.nome AS Nome, Materia.nome AS Matéria FROM Tarefa INNER JOIN Aluno ON Tarefa.idAluno = Aluno.idALuno INNER JOIN Materia ON Materia.idMateria = Tarefa.idMateria WHERE Tarefa.idTarefaCriptografado = ? AND Aluno.idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idTarefaCriptografado, $idAluno]);

    if ($stmt->rowCount() == 0) {
      throw new PDOException("Essa tarefa não existe");
    }

    $sql = "DELETE FROM Tarefa WHERE idTarefaCriptografado = ? AND idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idTarefaCriptografado, $idAluno]);

    echo json_encode($dados = array (
      "type" => "success",
      "message" => "Tarefa excluída com sucesso",
    ));
    return;

  } catch(PDOException $err) {
    echo json_encode($dados = array (
      "type" => "error",
      "message" => "Não foi possível excluir essa tarefa",
      "more" => $err->getMessage()
    ));
    return;
  }
  
}


