<?php
/**
* Classe criada para fazer consultas no banco de dados
* Autor: Felipe Samuel
* Data: 27/06/2017
*/


/*
*******************************************************
*                     IMPORTANTE                      *
*******************************************************
*  o ARQUIVO config.json deve ser colocado na mesma   *
*  pasta que este arquivo. e configurado corretamente *
*******************************************************
*/

/*
* Classe responsável pela comunicação DIRETA com o banco de dados
* Não é recomendado o uso direto, apenas em condições muito especificas.
*/
class DB
{
  private $dbUser;
  private $dbPass;
  private $dbHost;
  private $dbPort;
  private $dbDriver;
  private $dbName;
  private $dbCharset;
  private static $instance;

  private function getConfig()
  {
    $json_file = file_get_contents("config.json");   

    $json = json_decode($json_file, true);

    $this->dbUser = $json['db_user'];
    $this->dbPass = $json['db_pass'];
    $this->dbHost = $json['db_host'];
    $this->dbPort = $json['db_port'];
    $this->dbDriver = $json['db_driver'];
    $this->dbName = $json['db_name'];
    $this->dbCharset = $json['db_charset'];
  }
  private function connect()
  {    
    if (!isset(self::$instance)) {
      try
      {
        //Carrega as configurações do arquivo
        $this->getConfig();
        switch ($this->dbDriver) {
          case 'mysql':
          self::$instance = new PDO($this->dbDriver.':host='.$this->dbHost.';dbname='.$this->dbName.';charset='.$this->dbCharset, $this->dbUser, $this->dbPass);
          break;
          default:
          break;
        }
      }catch(Exception $e)
      {
        return null;
      }
    }
    return self::$instance;
  }
  private function execute($query, $retornaId = false, $retornaStmt = true)
  {
    $pdo = $this->connect();
    if($pdo != null){
      $stmt = $pdo->prepare($query);
      $executou = $stmt->execute();
      if($stmt->rowCount() > 0)
      {
        if($retornaId)
        {
          return $pdo->lastInsertId();
        }
      }else
      {
        return $executou;
      }
      if($retornaStmt)
      {
        return $stmt;
      }else{
        return $executou;
      }
    }
  }

  public function insert($tabela,$dados, $retornaId = false)
  {
    $campos = implode(', ', array_keys($dados));
    //pega os valores do array, acrescenta aspas simples e virgula na separação
    $valores = "'".implode("', '", $dados)."'";
    //cria a query
    $query = "INSERT INTO {$tabela} ({$campos}) VALUES ({$valores})";
    //retorna se inseriu ou nao
    return $this->execute($query, $retornaId, false);
  }

  public function update($tabela, array $dados, $condicao = null, $retornaId = false)
  {
    foreach ($dados as $chave => $valor) {//percorre o array recebido
      $campos[] = "{$chave} = '{$valor}'"; //atribui a chave do array(nome do campo no banco de dados) e concatena com o valor pra atualizar
    }
    $campos = implode(', ', $campos); //divide os campos por uma virgula
    $condicao = ($condicao) ? " WHERE {$condicao}" : null; //se existir uma condicao ele atribui, se nao ele modifica tudo da tabela
    $query = "UPDATE {$tabela} SET {$campos} {$condicao}"; //query usada pra atualizar os registros
    return $this->execute($query, $retornaId, false);
  }

  public function select($tabela, $campos = '*', $condicao = null)
  {
    //monta a query
    $query = "SELECT {$campos} FROM {$tabela} {$condicao}";
    //executa a query
    $result = $this->execute($query);
    if(is_bool($result)){//se o numero de linhas de retorno for igual a 0...
      if(!$result){
        return null;
      }
    }else{
      while($linha = $result->fetch(PDO::FETCH_OBJ)){ //transorma os dados do bd em um array
        $dados[] = $linha; // tribue os dados a outro array
      }
      return $dados;
    }
    return $result;
  }

  public function innerJoin($tabela, array $tabelas, $campos = '*', $condicao = null)
  {
    $query = "SELECT {$campos} FROM {$tabela} {$condicao}";
    foreach ($tabelas as $key => $tab) {
      $query .= " INNER JOIN ".$key." ON ".$tab;
    }
    $result = $this->execute($query);
    if(is_bool($result)){//se o numero de linhas de retorno for igual a 0...
      if(!$result){
        return null;
      }
    }
    else{
      while($linha = $result->fetch(PDO::FETCH_OBJ)){ //transorma os dados do bd em um array
        $dados[] = $linha; // tribue os dados a outro array
      }
      return $dados;
    }
  }
  public function delete($tabela, $condicao = null, $retornaId = false)
  {
    $condicao = ($condicao) ? " WHERE {$condicao}" : null;
    $query = "DELETE FROM {$tabela} {$condicao}";
    return $this->execute($query, $retornaId, false);
  }
}


/*
* Classe Responsável pela abstração dos métodos (CRUD)
* Deve ser estendida pelo objeto que será usado
*/
abstract class SamDB extends DB
{
    //Este atributo deve estar em TODOS os objetos que estendem esta classe
    //e com o modificador de acesso PROTECTED
   protected $tabela;

   public function inserir()
   {
     
      $dados = $this->getDados();
      unset($dados['id']);
      return $this->insert($this->tabela, $dados, true);
   }
   //Por padrão ele altera levando em consideração que no seu objeto o id esteja setado.
   public function editar()
   {
     $dados = $this->getDados();
     return $this->update($this->tabela, $dados, "id=".$dados['id'], false);
   }

    //Pode ser passado uma condição SQL para fazer o update, por exemplo: "nome = 'samuel' AND id=3"
   public function editarOnde($condicao=null)
   {
     $dados = $this->getDados();
     if($condicao==null)
     {
        return $this->update($this->tabela, $dados, "id=".$dados['id'], false);
     }else{
        return $this->update($this->tabela, $dados, $condicao, false);
     }
     
   }
   public function excluir()
   {
     $dados = $this->getDados();
     return $this->delete($this->tabela, "id=".$dados['id']);
   }
   //Pode ser passado uma condição SQL para a exclusão, por exemplo: "nome = 'samuel' AND id=3"
   public function excluirOnde($condicao = null)
   {
     $dados = $this->getDados();
     if($condicao==null)
     {
       return $this->delete($this->tabela, "id=".$dados['id']);
     }
     else
     {
        return $this->delete($this->tabela, $condicao);
     }
    
   }

   //Exibe apenas os dados do objeto atual com o id setado.
   public function listar()
   {
     $dados = $this->getDados();
     return $this->select($this->tabela, "*", "WHERE id=".$dados['id']);
   }
   public function listarTodos()
   {
     return $this->select($this->tabela);
   }

  //Exibe todos os dados do objeto, podendo ser passado quais os campos que quer obter, e uma condição em formato SQL
  public function listarOnde($campos = "*", $condicao = "")
  {
    $cond = "WHERE ";
    $cond .= $condicao;
    $condicao = $cond;
    return $this->select($this->tabela, $campos, $condicao);
  }

    //Retorna um array chave => valor com os atributos da classe e seus valores.
   public function getDados()
   {
     $dados = array();
     foreach($this as $key => $value)
     {
        if($key != 'tabela'):
          $dados[$key] = $value;
        endif;
     }
     return $dados;
   }
}
?>