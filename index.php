<?php

require_once("SamDB.php");


class Cliente extends SamDB{

    //Deve conter o nome da tabela relacionada a esta classe.
    protected $tabela = 'cliente';

    // Todos os atributos devem ter o mesmo nome que no banco de dados
    // Todos os atributos devem ser declarados com o modificador PROTECTED
    
    protected $id;
    protected $nome;
    protected $telefone;

    function __construct($id = 0, $nome = "", $telefone="")
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->telefone = $telefone;
    }
    function setNome($nome)
    {
        $this->nome = $nome;
    }
    function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }
    function setId($id)
    {
        $this->id = $id;
    }
    function getNome()
    {
        return $this->nome;
    }
    function getTelefone()
    {
        return $this->telefone;
    }
    function getId()
    {
        return $this->id;
    }
}

$cliente = new Cliente();
$cliente->setId(12); //Opcional para inserção
$cliente->setNome("Felipe Samuel");
$cliente->setTelefone("879877212323");

$cliente->inserir(); //Retorna o ID da inserção

?>