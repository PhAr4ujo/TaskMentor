<?php
  include_once "../../database/connection.php";
  $requestData = $_REQUEST;
  $dados = array();

  if($requestData["operation"] == "create") {
    if(empty($requestData["tipo"])) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Nome do selo não foi informado"
      ));
      return;
    }

    $tipo = $requestData["tipo"];

    try {
      $sql = "SELECT * FROM Selo WHERE tipo = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$tipo]);

      if($stmt->rowCount() > 0) {
        echo json_encode($dados = array (
          "type" => "error",
          "message" => "Já existe um selo cadastrado com esse nome"
        ));
        return;
      }

      $sql = "INSERT INTO Selo (tipo) VALUES (?)";
      $stmt = $database->prepare($sql);
      $stmt->execute([$tipo]);

      echo json_encode($dados = array (
        "type" => "success",
        "message" => "Selo cadastrado"
      ));
      return;

    } catch(PDOException $err) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Não foi possível cadastrar esse selo",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if($requestData["operation"] == "read") {
    if(empty($requestData["idSelo"])) {
      try {
        $sql = "SELECT * FROM Selo";
        $stmt = $database->prepare($sql);
        $stmt->execute();
        
        if($stmt->rowCount() == 0) {
          echo json_encode($dados = array (
            "type" => "success",
            "message" => "Não há selos cadastrados"
          ));
          return;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $dados[] = $row;
        }

        echo json_encode($dados);
        return;
  
      } catch(PDOException $err) {
        echo json_encode($dados = array (
          "type" => "error",
          "message" => "Não foi possível exibir os selos",
          "more" => $err->getMessage()
        ));
        return;
      }
    }

    $idSelo = $requestData["idSelo"];

    try {
      $sql = "SELECT * FROM Selo WHERE idSelo = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idSelo]);

      if($stmt->rowCount() == 0) {
        throw new PDOException("Esse selo não existe");
      }

      $dados = $stmt->fetch(PDO::FETCH_ASSOC);

      echo json_encode($dados);
      return;

    } catch(PDOException $err) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Não foi localiar esse selo",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if($requestData["operation"] == "update") {
    if(empty($requestData["idSelo"])) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Não foi possível localizar esse selo",
      ));
      return;
    }

    $idSelo = $requestData["idSelo"];

    try {

      $sql = "SELECT * FROM Selo WHERE idSelo = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idSelo]);

      if($stmt->rowCount() == 0) {
        throw new PDOException("Esse selo não existe");
      }

      $data = $stmt->fetch(PDO::FETCH_ASSOC);

      if(empty($requestData["tipo"])) $requestData["tipo"] = $data['tipo'];

      $sql = "SELECT * FROM Selo WHERE tipo = ? AND idSelo != ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$requestData["tipo"], $idSelo]);

      if($stmt->rowCount() > 0) {
        throw new PDOException("Já existe um selo com esse nome");
      }

      $sql = "UPDATE Selo SET tipo = ? WHERE idSelo = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$requestData["tipo"], $idSelo]);

      echo json_encode($dados = array (
        "type" => "success",
        "message" => "Selo alterado com sucesso",
      ));
      return;

    } catch(PDOException $err) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Não foi possível atualizar esse selo",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if($requestData["operation"] == "delete") {
    if(empty($requestData["idSelo"])) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Selo não econtrado"
      ));
      return;
    }

    $idSelo = $requestData["idSelo"];

    try {
      $sql = "SELECT * FROM Selo WHERE idSelo = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idSelo]);

      if($stmt->rowCount() == 0 ) {
        throw new PDOException("Esse selo não existe");
      }

      $sql = "DELETE FROM Selo WHERE idSelo = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idSelo]);

      echo json_encode($dados = array (
        "type" => "success",
        "message" => "Selo deletado com sucesso"
      ));
      return;

    } catch(PDOException $err) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Não foi possível deletar esse selo",
        "more" => $err->getMessage()
      ));
      return;
    }
  }
?>