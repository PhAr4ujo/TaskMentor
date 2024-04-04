<?php
  include_once "../../database/connection.php";

  $dados = [];

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dados['titulo'] = $_REQUEST['titulo'];
    $dados['descricao'] = $_REQUEST['descricao'];
    $dados['status'] = $_REQUEST['status'];
    $dados['prioridade'] = $_REQUEST['prioridade'];
    $dados['dataCriacao'] = $_REQUEST['dataCriacao'];
    $dados['dataVencimento'] = $_REQUEST['dataVencimento'];
    $dados['idMateria'] = $_REQUEST['idMateria'];

  }elseif ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    
  }elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
    # code...
  }elseif ($_SERVER["REQUEST_METHOD"] == "GET") {
    # code...
  }
?>