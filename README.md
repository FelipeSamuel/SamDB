# SamDB
Classe Responsável pela comunicação simples com o banco de dados na linguagem PHP

Para inserir: 

$objeto->inserir();

<hr/>

para editar pelo id do objeto:

$objeto->editar();

<hr/>

para editar por outra condição:

$objeto->editarOnde("id = 3 AND nome='samuel'");

<hr/>

para excluir pelo id do objeto:

$objeto->excluir();

<hr/>

para excluir por outra condição:

$objeto->excluirOnde("id = 3 AND nome='samuel'");

<hr/>

para listar os dados do objeto pelo seu id

$objeto->listar();

<hr/>

para retornar uma lista de objetos

$objeto->listarTodos();

<hr/>

para retornar uma lista de objetos atraves de uma condição:

$objeto->listarOnde("nome = 'smauel' AND data_aniversario='2000-05-06'");

