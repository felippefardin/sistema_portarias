<?php
$db = new SQLite3('data/portarias.db');

$id = $_GET['id'] ?? null;
if(!$id){
    header("Location:index.php");
    exit;
}

// Busca a portaria atual
$res = $db->query("SELECT * FROM portarias WHERE id=$id");
$portaria = $res->fetchArray();
if(!$portaria){
    header("Location:index.php");
    exit;
}

function dataExtenso($data){
    $meses = [
        "01"=>"janeiro","02"=>"fevereiro","03"=>"março","04"=>"abril",
        "05"=>"maio","06"=>"junho","07"=>"julho","08"=>"agosto",
        "09"=>"setembro","10"=>"outubro","11"=>"novembro","12"=>"dezembro"
    ];
    $d = date("d",strtotime($data));
    $m = $meses[date("m",strtotime($data))];
    $a = date("Y",strtotime($data));
    return "$d de $m de $a";
}

// Salvar alterações
if(isset($_POST['salvar'])){
    $numero = intval($_POST['numero']);
    $procurador = $_POST['procurador'];
    $sexo = $_POST['sexo'];
    $oab = $_POST['oab'];
    $processo = $_POST['processo'];
    $autor = $_POST['autor'];
    $vara = $_POST['vara'];
    $data = $_POST['data'];

    $db->exec("UPDATE portarias SET
        numero='$numero',
        procurador='$procurador',
        sexo='$sexo',
        oab='$oab',
        processo='$processo',
        autor='$autor',
        vara='$vara',
        data_portaria='$data'
        WHERE id=$id
    ");

    // Redireciona para index.php com mensagem de sucesso
    header("Location:index.php?msg=editado");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Editar Portaria</title>
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana,sans-serif; background:#f4f6f8; margin:0; padding:0; }
.container { max-width:600px; margin:40px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.1); }
h2 { color:#333; margin-bottom:20px; border-bottom:2px solid #4CAF50; padding-bottom:8px; }
form input, form select, form button { width:100%; max-width:300px; padding:8px 12px; margin:6px 0; border-radius:5px; border:1px solid #ccc; box-sizing:border-box; }
form button { background-color:#4CAF50; color:white; border:none; cursor:pointer; width:auto; padding:10px 20px; transition:0.3s; }
form button:hover { background-color:#45a049; }
</style>
</head>
<body>

<div class="container">
<h2>Editar Portaria nº <?php echo $portaria['numero']; ?></h2>
<form method="post">
Número da Portaria
<input type="number" name="numero" value="<?php echo $portaria['numero']; ?>" required>

Procurador
<input type="text" name="procurador" value="<?php echo $portaria['procurador']; ?>" required>

Sexo
<select name="sexo" required>
<option value="M" <?php if($portaria['sexo']=="M") echo "selected"; ?>>Masculino</option>
<option value="F" <?php if($portaria['sexo']=="F") echo "selected"; ?>>Feminino</option>
</select>

OAB
<input type="text" name="oab" value="<?php echo $portaria['oab']; ?>" required>

Processo
<input type="text" name="processo" value="<?php echo $portaria['processo']; ?>" required>

Autor
<input type="text" name="autor" value="<?php echo $portaria['autor']; ?>" required>

Vara
<input type="text" name="vara" value="<?php echo $portaria['vara']; ?>" required>

Data
<input type="date" name="data" value="<?php echo $portaria['data_portaria']; ?>" required>

<button type="submit" name="salvar">Salvar Alterações</button>
</form>
</div>

</body>
</html>