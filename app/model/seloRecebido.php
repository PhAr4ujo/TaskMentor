<?php

include_once "../../database/connection.php";
$requestData = $_REQUEST;
$dados = array();

if ($requestData["operation"] == "create") {

  try {
    if (empty($requestData["token"])) {
      throw new PDOException("Token não informado");
    }

    $token = $requestData["token"];

    $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$token, time()]);

    $row = $stmt->fetch();
    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Token de autenticação inválido ou expirado");
    }

    $idAluno = $row["idAluno"];

    if (empty($requestData["idSelo"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "O selo a ser recebido não foi informado"
      ));
      return;
    }

    $idSelo = $requestData["idSelo"];


    $sql = "SELECT * FROM Aluno WHERE idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idAluno]);

    if ($stmt->rowCount() == 0) {
      throw new PDOException("Não foi possível localizar o aluno");
    }

    $sql = "SELECT * FROM Selo WHERE idSelo = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idSelo]);

    if ($stmt->rowCount() == 0) {
      throw new PDOException("Não foi possível localizar esse selo");
    }

    $sql = "INSERT INTO seloRecebido (idAluno, idSelo) VALUES (?,?)";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idAluno, $idSelo]);

    echo json_encode($dados = array(
      "type" => "success",
      "message" => "Selo adicionado com sucesso"
    ));
    return;
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Não foi possivel atribuir esse selo",
      "more" => $err->getMessage()
    ));
    return;
  }
}

if ($requestData["operation"] == "read") {

  try {
    if (empty($requestData["token"])) {
      throw new PDOException("Token não informado");
    }

    $token = $requestData["token"];

    $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$token, time()]);

    $row = $stmt->fetch();

    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Token de autenticação inválido ou expirado");
    }

    $idAluno = $row["idAluno"];
    
    $sql = "SELECT A.nome AS Aluno, S.tipo AS Selo
              FROM SeloRecebido SR
                LEFT JOIN Aluno AS A ON SR.idAluno = A.idAluno
                LEFT JOIN Selo AS S ON SR.idSelo = S.idSelo
              WHERE SR.idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idAluno]);

    if ($stmt->rowCount() == 0) {
      throw new PDOException("Não foi possível econtrar os selos desse aluno");
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $dados[] = $row;
    }

    echo json_encode($dados);
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Não foi possível listar os selos",
      "more" => $err->getMessage()
    ));
    return;
  }
}
