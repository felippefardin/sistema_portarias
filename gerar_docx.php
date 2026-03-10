<?php

$contador_file = "data/contador.txt";

if(isset($_POST['gerar'])){

$numero = (int)file_get_contents($contador_file);
$numero++;

file_put_contents($contador_file,$numero);

$ano = date("Y");
$data = date("d/m/Y");

$procurador = $_POST['procurador'];
$genero = $_POST['genero'];
$oab = $_POST['oab'];
$processo = $_POST['processo'];
$autor = $_POST['autor'];
$vara = $_POST['vara'];

if($genero == "F"){

$genero_texto = "brasileira, advogada, inscrita na OAB/ES, sob o nº $oab";

}else{

$genero_texto = "brasileiro, advogado, inscrito na OAB/ES, sob o nº $oab";

}

}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<title>Sistema de Portarias</title>

<style>

body{
font-family:Arial;
background:#f2f2f2;
margin:40px;
}

.container{
background:white;
padding:30px;
max-width:900px;
margin:auto;
border-radius:8px;
}

input,select{
width:100%;
padding:8px;
margin-bottom:10px;
}

button{
padding:10px 15px;
margin-right:10px;
}

.portaria{
margin-top:30px;
white-space:pre-wrap;
border:1px solid #ccc;
padding:20px;
}

@media print{

form,button{
display:none;
}

}

</style>

</head>

<body>

<div class="container">

<h2>Gerador de Portaria</h2>

<form method="POST">

<label>Nome do Procurador</label>
<input name="procurador" required>

<label>Gênero</label>
<select name="genero">

<option value="F">Feminino</option>
<option value="M">Masculino</option>

</select>

<label>Número OAB</label>
<input name="oab" required>

<label>Número do Processo</label>
<input name="processo" required>

<label>Autor do Processo</label>
<input name="autor" required>

<label>Vara</label>
<input name="vara" required>

<button name="gerar">Gerar Portaria</button>

</form>

<?php

if(isset($_POST['gerar'])){

echo "<div class='portaria'>";

echo "PREFEITURA MUNICIPAL DA SERRA\n";
echo "ESTADO DO ESPÍRITO SANTO\n";
echo "PROCURADORIA-GERAL\n\n";

echo "PORTARIA Nº $numero/$ano\n\n";

echo "A PROCURADORA-GERAL DO MUNICÍPIO DE SERRA, nomeada por força do Decreto nº 027, de 02 de janeiro de 2025, no uso de suas atribuições legais.\n\n";

echo "R E S O L V E:\n\n";

echo "Designar o Procurador Municipal $procurador, $genero_texto, para promover, acompanhar e praticar todos os atos necessários, decorrentes do processo sob o nº $processo, impetrado por $autor, em face do MUNICÍPIO DE SERRA, perante $vara.\n\n";

echo "Serra/ES, $data.\n\n";

echo "ALESSANDRA COSTA FERREIRA NUNES\n";
echo "OAB/ES 11.483\n";
echo "Procuradora-Geral do Município de Serra";

echo "</div>";

echo "<br>";

echo "<button onclick='window.print()'>Gerar PDF</button>";

echo "<a href='gerar_docx.php?numero=$numero&ano=$ano&procurador=$procurador&oab=$oab&genero=$genero&processo=$processo&autor=$autor&vara=$vara'>
<button type='button'>Gerar Word</button>
</a>";

echo "<br><br>";

echo "<a href='editar.php'>Editar Número da Portaria</a>";

}

?>

</div>

</body>

</html>