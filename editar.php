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

    header("Location:index.php?msg=editado");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Portaria nº <?php echo $portaria['numero']; ?></title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #4CAF50;
            --bg-body: #f0f2f5;
            --bg-card: #ffffff;
            --text-main: #333;
            --border-radius: 8px;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        body { 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            background: var(--bg-body); 
            margin: 0; 
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-main);
        }

        .container { 
            width: 100%;
            max-width: 500px; /* Largura métrica para formulários de edição */
            background: var(--bg-card);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            box-sizing: border-box;
        }

        .header-edit {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            gap: 15px;
        }

        h2 { 
            color: var(--primary-color); 
            margin: 0;
            font-size: 1.5rem;
            border-left: 5px solid var(--secondary-color);
            padding-left: 15px;
        }

        form label { 
            display: block; 
            font-weight: 600; 
            margin: 15px 0 5px 0; 
            font-size: 0.9em; 
            color: #555; 
        }

        form input, form select { 
            width: 100%; 
            padding: 12px; 
            border-radius: 5px; 
            border: 1px solid #ddd; 
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        form input:focus, form select:focus {
            border-color: var(--secondary-color);
            outline: none;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-save { 
            flex: 2;
            background: var(--secondary-color); 
            color: white; 
            border: none; 
            padding: 14px; 
            border-radius: 5px;
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s;
        }

        .btn-save:hover { background: #45a049; }

        .btn-back { 
            flex: 1;
            background: #eee; 
            color: var(--primary-color); 
            text-decoration: none;
            text-align: center;
            padding: 14px; 
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9em;
            transition: 0.3s;
        }

        .btn-back:hover { background: #ddd; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-edit">
        <h2>Editar Portaria nº <?php echo $portaria['numero']; ?></h2>
    </div>

    <form method="post">
        <label>Número da Portaria</label>
        <input type="number" name="numero" value="<?php echo $portaria['numero']; ?>" required>

        <label>Procurador(a)</label>
        <input type="text" name="procurador" value="<?php echo $portaria['procurador']; ?>" required>

        <label>Sexo</label>
        <select name="sexo" required>
            <option value="M" <?php if($portaria['sexo']=="M") echo "selected"; ?>>Masculino</option>
            <option value="F" <?php if($portaria['sexo']=="F") echo "selected"; ?>>Feminino</option>
        </select>

        <label>OAB</label>
        <input type="text" name="oab" value="<?php echo $portaria['oab']; ?>" required>

        <label>Processo</label>
        <input type="text" name="processo" value="<?php echo $portaria['processo']; ?>" required>

        <label>Autor</label>
        <input type="text" name="autor" value="<?php echo $portaria['autor']; ?>" required>

        <label>Vara</label>
        <input type="text" name="vara" value="<?php echo $portaria['vara']; ?>" required>

        <label>Data</label>
        <input type="date" name="data" value="<?php echo $portaria['data_portaria']; ?>" required>

        <div class="btn-group">
            <a href="index.php" class="btn-back">Cancelar</a>
            <button type="submit" name="salvar" class="btn-save">Salvar Alterações</button>
        </div>
    </form>
</div>

</body>
</html>