<?php

$arquivo = "data/portarias.json";

$dados = json_decode(file_get_contents($arquivo), true);

$id = uniqid();

$numero = count($dados) + 1;

$ano = date("Y");

$sexo = $_POST['sexo'];

if($sexo == "F"){
$descricao = "brasileira, advogada, inscrita na OAB/ES, sob o nº ".$_POST['oab'];
}else{
$descricao = "brasileiro, advogado, inscrito na OAB/ES, sob o nº ".$_POST['oab'];
}

$nova = [

"id"=>$id,
"numero"=>$numero."/".$ano,
"nome"=>$_POST['nome'],
"sexo"=>$sexo,
"oab"=>$_POST['oab'],
"descricao"=>$descricao,
"data"=>date("d/m/Y")

];

$dados[] = $nova;

file_put_contents($arquivo,json_encode($dados));

header("Location: index.php");