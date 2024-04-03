<?php


    class Materia{
        private int $idMateria;
        private String $nome;
        private String $docente;



        public function setNome(String $nome): void{
            $this->nome = $nome;
        }

        public function getNome(): String{
            return $this->nome;
        }

        
        
    }
?>