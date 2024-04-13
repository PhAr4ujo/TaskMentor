<?php

include_once "../../database/connection.php";
$requestData = $_REQUEST;
$dados = array();

if ($requestData["operation"] == "create") {
  if (empty($requestData["nome"])) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Nome da matéria está vazio"
    ));
    return;
  }

  if (empty($requestData["docente"])) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Essa matéria precisa ter um professor"
    ));
    return;
  }

  if (empty($requestData["token"])) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Token de autenticação não foi informado"
    ));
    return;
  }

  $nome = $requestData["nome"];
  $docente = $requestData["docente"];
  $token = $requestData["token"];

  try {

    $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$token, time()]);

    $row = $stmt->fetch();
    if ($stmt->rowCount() <= 0) {
      throw new PDOException("Token de autenticação inválido ou expirado");
    }

    $idAluno = $row["idAluno"];

    $sql = "SELECT * FROM Materia WHERE nome = ? AND idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$nome, $idAluno]);

    if ($stmt->rowCount() > 0) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Já existe uma matéria com esse nome cadastrada"
      ));
      return;
    }

    $idMateriaCriptografado = substr(md5(uniqid(rand(), true)), 0, 16);

    $sql = "INSERT INTO Materia (idMateriaCriptografado, nome, docente, idAluno) VALUES (?, ?, ?, ?)";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idMateriaCriptografado, $nome, $docente, $idAluno]);


    echo json_encode($dados = array(
      "type" => "success",
      "message" => "Matéria adicionada com sucesso"
    ));
    return;
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Não foi possível criar uma nova matéria",
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

    if (empty($requestData["idMateriaCriptografado"])) {
      $sql = "SELECT * FROM Materia WHERE idAluno = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$idAluno]);

      if ($stmt->rowCount() == 0) {
        echo json_encode($dados = array(
          "type" => "success",
          "message" => "Não há matérias para listar"
        ));
        return;
      }

      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dados[] = $row;
      }

      echo json_encode($dados);
      return;
    }

    $idMateria = $requestData["idMateriaCriptografado"];

    $sql = "SELECT * FROM Materia WHERE idAluno = ? AND idMateriaCriptografado = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idAluno, $idMateria]);

    if ($stmt->rowCount() == 0) {
      echo json_encode($dados = array(
        "type" => "success",
        "message" => "Não foi possível localizar essa matéria"
      ));
      return;
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $dados[] = $row;
    }

    echo json_encode($dados);
    return;
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "error",
      "message" => "Não foi listar as matérias",
      "more" => $err->getMessage()
    ));
    return;
  }
}

if ($requestData["operation"] == "update") {

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

    if (empty($requestData["idMateria"])) {
      echo json_encode($dados = array(
        "type" => "erro",
        "message" => "Não foi possível localizar essa matéria"
      ));
      return;
    }

    $idMateria = $requestData["idMateria"];

    $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado = ? AND idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idMateria, $idAluno]);

    $data = $stmt->fetch();

    if ($stmt->rowCount() == 0) {
      echo json_encode($dados = array(
        "type" => "erro",
        "message" => "Não foi possível alterar essa matéria porque ela não existe",
      ));
      return;
    }

    if (empty($requestData["nome"])) $requestData["nome"] = $data["nome"];
    if (empty($requestData["docente"])) $requestData["docente"] = $data["docente"];

    $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado != ? AND idAluno = ? AND nome = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idMateria, $idAluno, $requestData["nome"]]);

    if ($stmt->rowCount() > 0) {
      echo json_encode($dados = array(
        "type" => "erro",
        "message" => "Já existe uma matéria com esse nome",
      ));
      return;
    }

    $sql = "UPDATE Materia SET nome = ?, docente = ? WHERE idMateriaCriptografado = ? AND idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$requestData["nome"], $requestData["docente"], $idMateria, $idAluno]);

    echo json_encode($dados = array(
      "type" => "success",
      "message" => "Materia atualizada com sucesso",
    ));
    return;
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "erro",
      "message" => "Não foi possível alterar essa matéria",
      "more" => $err->getMessage()
    ));
    return;
  }
}

if ($requestData["operation"] == "delete") {

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

    if (empty($requestData["idMateria"])) {
      echo json_encode($dados = array(
        "type" => "erro",
        "message" => "Não foi possível localizar essa matéria"
      ));
      return;
    }

    $idMateria = $requestData["idMateria"];

    $sql = "SELECT * FROM Materia WHERE idMateriaCriptografado = ? AND idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idMateria, $idAluno]);

    if ($stmt->rowCount() == 0) {
      echo json_encode($dados = array(
        "type" => "erro",
        "message" => "Não foi possível excluir essa matéria porque ela não existe"
      ));
      return;
    }

    $sql = "DELETE FROM Materia WHERE idMateriaCriptografado = ? AND  idAluno = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$idMateria, $idAluno]);

    echo json_encode($dados = array(
      "type" => "success",
      "message" => "Matéria excluída"
    ));
    return;
  } catch (PDOException $err) {
    echo json_encode($dados = array(
      "type" => "erro",
      "message" => "Não foi possível exlcuir essa matéria",
      "more" => $err->getMessage()
    ));
    return;
  }
}
