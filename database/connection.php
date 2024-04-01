<?php

  $dbhost = "127.0.0.1";
  $dbuser = "root";
  $dbpassword = "root";
  $dbname = "TaskMentor";

  $dados = array();

  try {
    $database = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpassword);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dados = array (
      "type" => "success",
      "message" => "Conexão realizada com sucesso",
    );
  } catch(PDOException $err) {
    $dados = array (
      "type" => "error",
      "message" => "Houve um erro ao conectar com o Banco de Dados",
      "more" => $err->getMessage()
    );
  }
  
  // echo  json_encode($dados);


?>