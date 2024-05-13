<?php
  include_once "../../database/connection.php";

  $requestData = $_REQUEST;
  $dados = array();

  $destino =  realpath("../../tmp/avatar/") . "/";
  $extensoes = array ("image/png", "image/jpg", "image/jpeg");


  if($requestData["operation"] == "create") {
    if(empty($requestData["nome"])) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Nome da imagem está em branco"
      ));
      return;
    }

    if(empty($_FILES["imagem"])) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Nenhuma imagem foi selecionada"
      ));
      return; 
    }

    $sql = "SELECT * FROM ImageProfile WHERE nome = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$requestData["nome"]]);

    if($stmt->rowCount() > 0) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Já existe uma imagem com esse nome"
      ));
      return;  
    }

    $idImagem = substr(md5(uniqid(rand(), true)), 0, 10);
    $nome = $requestData["nome"];
    $file = $_FILES["imagem"];
    $arr = explode(".", $file["name"]);
    $fileExtension = $arr[1]; 
    $path = $destino . $idImagem . "-" . str_replace(" ", "", $nome) . "." . $fileExtension;

    if(!in_array($file["type"], $extensoes)) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Apenas imagens com extensão 'png', 'jpg' e 'jpeg'",
      )); 
      return;
    }

    try {
      if(move_uploaded_file($file['tmp_name'], $path)) {
        $sql = "INSERT INTO ImageProfile (nome, path) VALUES (:nome, :path)";
        $stmt = $database->prepare($sql);
        $stmt -> bindParam(":nome", $nome, PDO::PARAM_STR);
        $stmt -> bindParam(":path", $path, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode($dados = array (
          "type" => "success",
          "message" => "Imagem salva com sucesso"
        )); 
        return;
      }
    } catch(PDOException $err) {
       echo json_encode($dados = array (
        "type" => "error",
        "message" => "Não foi possível salvar essa imagem",
        "more" =>  $err->getMessage()
      )); 
    }
  }

  if($requestData["operation"] == "read") {
    try { 
      $sql = "SELECT * FROM ImageProfile";
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

    } catch (PDOException $err) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Erro na leitura de dados",
        "more" =>  $err->getMessage()
      ));
    }
  }

  if($requestData["operation"] == "delete") {
    if(empty($requestData["idImageProfile"])) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "ID da imagem não foi informado" 
      ));
      return;
    }

    $sql = "SELECT * FROM ImageProfile WHERE idImageProfile = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$requestData["idImageProfile"]]);

    if($stmt->rowCount() == 0) {
      echo json_encode($dados = array(
        "type" => "error",
        "message"  => "Não foi possível deletar essa imagem porque ela não existe no banco de dados"
      ));
      return;
    } else {
      $pathImage = $stmt->fetch(PDO::FETCH_ASSOC)["path"];
    }

    try {
      if(unlink($pathImage)) {
        $sql = "DELETE FROM ImageProfile WHERE idImageProfile = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$requestData["idImageProfile"]]);
  
        echo json_encode($dados = array (
          "type" => "success",
          "message" => "Imagem deletada com sucesso",
        ));
  
        return;
      }
    } catch(PDOException $err) {
      echo json_encode($dados = array (
        "type" => "error",
        "message" => "Houve um erro durante a exclusão da imagem",
        "more" => $err->getMessage()
      ));
    }




  }
  
?>