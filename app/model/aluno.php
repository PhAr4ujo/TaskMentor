<?php

  require_once "../../database/connection.php";
  $requestData = $_REQUEST;
  $dados = array();

  if ($requestData["operation"] == "register") {
    if (empty($requestData["nome"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "O nome não pode estar vazio"
      ));
      return;
    }

    if (empty($requestData["email"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "O email não foi informado"
      ));
      return;
    }

    if (empty($requestData["senha"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "A senha não foi definida"
      ));
      return;
    }

    if (empty($requestData["confirmarSenha"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Por favor, confirme sua senha"
      ));
      return;
    }

    if (empty($requestData["idImageProfile"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Nenhum avatar foi escolhido"
      ));
      return;
    }

    $nome = $requestData["nome"];
    $email = $requestData["email"];
    $senha = $requestData["senha"];
    $confirmarSenha = $requestData["confirmarSenha"];
    $idImageProfile = $requestData["idImageProfile"];

    if ($senha !== $confirmarSenha) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Suas senhas estão diferentes"
      ));
      return;
    }

    $sql = "SELECT * FROM Aluno WHERE email = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Não foi possível cadastrar pois já existe um usuário com esse e-mail"
      ));
      return;
    }

    try {

      $sql = "INSERT INTO Aluno (nome, email, senha, idImageProfile) VALUES (?, ?, ?, ?)";
      $stmt = $database->prepare($sql);
      $stmt->execute([$nome, $email, $senha, $idImageProfile]);

      echo json_encode($dados = array(
        "type" => "success",
        "message" => "Cadastro realizado com sucesso!"
      ));
      return;
    } catch (PDOException $err) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Não foi possível cadastrar um novo usuário",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if ($requestData["operation"] == "read") {
    try {
      $sql = "SELECT Aluno.nome, Aluno.email, Aluno.token, ImageProfile.path AS imageProfilePath
        FROM Aluno
        INNER JOIN ImageProfile ON Aluno.idImageProfile = ImageProfile.idImageProfile";

      $stmt = $database->prepare($sql);
      $stmt->execute();

      if ($stmt->rowCount() == 0) {
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
    } catch (PDOException $err) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Não foi possível listar os alunos"
      ));
      return;
    }
  }

  if ($requestData["operation"] == "login") {
    if (empty($requestData["email"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "E-mail não foi informado"
      ));

      return;
    }

    if (empty($requestData["senha"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "A senha não foi informada"
      ));
      return;
    }

    $email = $requestData["email"];
    $senha = $requestData["senha"];

    try {
      $sql = "SELECT * FROM Aluno WHERE email = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$email]);

      $row = $stmt->fetch();
      if ($stmt->rowCount() <= 0 || !$row || $senha !== $row["senha"]) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Usuário ou Senha inválidos",
        ));
        return;
      }

      $token = bin2hex(random_bytes(16));
      $tempoExpiracao = time() + (5 * 60);

      $sql = "UPDATE Aluno SET token = ?, tempoExpiracao = ? WHERE email = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$token, $tempoExpiracao, $email]);

      echo json_encode($dados = array(
        "type" => "success",
        "message" => "Login realizado com sucesso",
      ));
    } catch (PDOException $err) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Falha ao realizar login",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if ($requestData["operation"] == "delete") {
    if (empty($requestData["token"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Token de autenticação inválido",
      ));
      return;
    }

    if (empty($requestData["senha"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Informe sua senha",
      ));
      return;
    }

    $senha = $requestData["senha"];
    $token = $requestData["token"];

    try {
      $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$token, time()]);

      $row = $stmt->fetch();
      if ($stmt->rowCount() <= 0) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Token de autenticação inválido ou expirado",
        ));
        return;
      }

      if ($senha !== $row["senha"]) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Senha inválida",
        ));
        return;
      }

      $sql = "DELETE FROM Aluno WHERE token = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$token]);

      echo json_encode($dados = array(
        "type" => "success",
        "message" => "Conta excluída com sucesso",
      ));
    } catch (PDOException $err) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Não foi possível excluir essa conta",
        "more" => $err->getMessage()
      ));
      return;
    }
  }

  if($requestData["operation"] == "update") {
    if (empty($requestData["token"])) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Token de autenticação inválido",
      ));
      return;
    }

    $token = $requestData["token"];
    $nome = $requestData["nome"];
    $email = $requestData["email"];
    $senha = $requestData["senha"];
    $confirmarSenha = $requestData["confirmarSenha"];
    $idImageProfile = $requestData["idImageProfile"];

    try {
      $sql = "SELECT * FROM Aluno WHERE token = ? AND tempoExpiracao > ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$token, time()]);

      $row = $stmt->fetch();
      if ($stmt->rowCount() <= 0) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Token de autenticação inválido ou expirado",
        ));
        return;
      }

      if(empty($nome)) $nome = $row['nome'];
      if(empty($email)) $email = $row['email'];
      if(empty($senha)) $senha = $row['senha'];
      if(empty($confirmarSenha)) $confirmarSenha = $row['senha'];
      if(empty($idImageProfile)) $idImageProfile = $row['idImageProfile'];

      if($senha !== $confirmarSenha) {
        echo json_encode($dados = array(
          "type" => "error",
          "message" => "Suas senhas estão diferentes"
        ));
        return;
      }

      $sql = "UPDATE Aluno SET nome = ?, email = ?, senha = ?, idImageProfile = ? WHERE token = ?";
      $stmt = $database->prepare($sql);
      $stmt->execute([$nome, $email, $senha, $idImageProfile, $token]);

      echo json_encode($dados = array(
        "type" => "succes",
        "message" => "Informações atualizadas"
      ));

    } catch(PDOException $err) {
      echo json_encode($dados = array(
        "type" => "error",
        "message" => "Não foi possível atualizar suas informações",
        "more" => $err->getMessage()
      ));
      return;
    }
  }
  
?>
