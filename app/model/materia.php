<?php

  include_once "../../database/connection.php";
  $requestData = $_REQUEST;
  $dados = array();

  if($requestData["operation"] == "create") {
    if(empty($requestData["nome"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Nome da matéria está vazio"
      ));
      return;
    }

    if(empty($requestData["docente"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Essa matéria precisa ter um professor"
      ));
      return;
    }

    $nome = $requestData["nome"];
    $docente = $requestData["docente"];

    try {
      $sql = "SELECT * FROM Materia WHERE nome = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$nome]);

      if($stmt->rowCount() > 0) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Já existe uma matéria com esse nome cadastrada"
        ));
        return;
      } 

      $idMateriaCriptografado = substr(md5(uniqid(rand(), true)), 0, 16);

      $sql = "INSERT INTO Materia (idMateriaCriptografado, nome, docente) VALUES (?, ?, ?)";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idMateriaCriptografado, $nome, $docente]);

      
      echo json_encode($dados = array(
        "type" => "success",
        "message" => "Matéria adicionada com sucesso"
      ));
      return;
      
    } catch(PDOException $err) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Não foi possível criar uma nova matéria",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if($requestData["operation"] == "read") {
    if(empty($requestData["idMateria"])) {
      try {
        $sql = "SELECT * FROM Materia";
        $stmt = $database->prepare($sql);
        $stmt->execute();

        if($stmt->rowCount() == 0) {
          echo json_encode($dados = array(
            "type" => "success",
            "message" => "Não há registros salvos"
          ));
          return;
        }
  
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $dados[] = $row;
        }

        echo json_encode($dados);
        return;

      } catch(PDOException $err) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Não foi possível listar as matérias",
          "more" => $err->getMessage()
        ));
        return;
      }
    }

    try {
      $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$requestData["idMateria"]]);

      if($stmt->rowCount() == 0) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Matéria não localizada",
        ));
        return;
      }

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dados[] = $row;
      }

      echo json_encode($dados);
      return;

    } catch(PDOException $err) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Não foi possível listar as matérias",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if($requestData["operation"] == "update") {
    if(empty($requestData["idMateria"])) {
      echo json_encode($dados = array (
        "type" => "erro",
        "message" => "Não foi possível localizar essa matéria"
      ));
      return;
    }

    $idMateria = $requestData["idMateria"];

    try {
      $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idMateria]);

      $data = $stmt->fetch();

      if($stmt->rowCount() == 0) {
        echo json_encode($dados = array (
          "type" => "erro",
          "message" => "Não foi possível alterar essa matéria porque ela não existe",
        ));
        return;
      }

      if(empty($requestData["nome"])) $requestData["nome"] = $data["nome"];
      if(empty($requestData["docente"])) $requestData["docente"] = $data["docente"];

      $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado != ? AND nome = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idMateria, $requestData["nome"]]);

      if($stmt->rowCount() > 0) {
        echo json_encode($dados = array (
          "type" => "erro",
          "message" => "Já existe uma matéria com esse nome",
        ));
        return;
      }

      $sql = "UPDATE Materia SET nome = ?, docente = ? WHERE idMateriaCriptografado = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$requestData["nome"], $requestData["docente"], $idMateria]);

      echo json_encode($dados = array (
        "type" => "success",
        "message" => "Materia atualizada com sucesso",
      ));
      return;
    } catch(PDOException $err) {
      echo json_encode($dados = array (
        "type" => "erro",
        "message" => "Não foi possível alterar essa matéria",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if($requestData["operation"] == "delete") {
    if(empty($requestData["idMateria"])) {
      echo json_encode($dados = array (
        "type" => "erro",
        "message" => "Não foi possível localizar essa matéria"
      ));
      return;
    }

    $idMateria = $requestData["idMateria"];

    try {
      $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idMateria]);

      if($stmt->rowCount() == 0) {
        echo json_encode($dados = array (
          "type" => "erro",
          "message" => "Não foi possível excluir essa matéria porque ela não existe"
        ));
        return;
      }

      $sql = "DELETE FROM Materia WHERE idMateriaCriptografado = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idMateria]);

      echo json_encode($dados = array (
        "type" => "success",
        "message" => "Matéria excluída"
      ));
      return;

    } catch(PDOException $err) {
      echo json_encode($dados = array (
        "type" => "erro",
        "message" => "Não foi possível exlcuir essa matéria",
        "more" => $err->getMessage()
      ));
      return;
    }
  }



?>