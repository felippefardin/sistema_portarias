<?php
$db = new SQLite3('data/portarias.db');

$db->exec("CREATE TABLE IF NOT EXISTS portarias(
id INTEGER PRIMARY KEY AUTOINCREMENT,
numero INTEGER,
ano INTEGER,
procurador TEXT,
sexo TEXT,
oab TEXT,
processo TEXT,
autor TEXT,
vara TEXT,
data_portaria TEXT
)");

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

if(isset($_POST['salvar'])){
    $anoAtual = date("Y");
    $resUltimo = $db->query("SELECT numero, ano FROM portarias ORDER BY id DESC LIMIT 1");
    $ultimo = $resUltimo->fetchArray();
    if(!$ultimo || $ultimo['ano'] != $anoAtual){
        $proxNumero = 1;
    } else {
        $proxNumero = $ultimo['numero'] + 1;
    }
    if(!empty($_POST['numero'])){
        $numero = intval($_POST['numero']);
    } else {
        $numero = $proxNumero;
    }
    
    $procurador = $_POST['procurador'];
    $sexo = $_POST['sexo'];
    $oab = $_POST['oab'];
    $processo = $_POST['processo'];
    $autor = $_POST['autor'];
    $vara = $_POST['vara'];
    $data = $_POST['data'];

    $db->exec("INSERT INTO portarias(numero,ano,procurador,sexo,oab,processo,autor,vara,data_portaria)
    VALUES('$numero', '$anoAtual', '$procurador', '$sexo', '$oab', '$processo', '$autor', '$vara', '$data')");

    echo "<script>window.open('gerar_pdf.php?id=".$db->lastInsertRowID()."&download=1','_blank'); window.location='index.php';</script>";
}

$filtro="";
if(isset($_GET['buscar'])){
    $busca=$_GET['buscar'];
    $filtro="WHERE processo LIKE '%$busca%' OR procurador LIKE '%$busca%' OR autor LIKE '%$busca%'";
}

$mensagem = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'excluido') $mensagem = "Portaria excluída com sucesso!";
    if($_GET['msg'] == 'editado') $mensagem = "Portaria editada com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Portarias - Desktop</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #4CAF50;
            --accent-color: #ff5722;
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
            color: var(--text-main);
        }

        .container { 
            display: grid;
            grid-template-columns: 350px 1fr;
            grid-template-rows: auto 1fr;
            gap: 20px;
            padding: 20px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .header-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-card);
            padding: 15px 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .header-actions img { height: 50px; }

        .sidebar {
            background: var(--bg-card);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .main-content {
            background: var(--bg-card);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        h2 { 
            color: var(--primary-color); 
            margin: 0 0 20px 0;
            font-size: 1.4rem;
            border-left: 5px solid var(--secondary-color);
            padding-left: 15px;
        }

        form label { display: block; font-weight: 600; margin: 10px 0 5px 0; font-size: 0.85em; color: #666; }
        form input, form select { 
            width: 100%; padding: 10px; margin-bottom: 5px; 
            border-radius: 5px; border: 1px solid #ddd; box-sizing: border-box; 
        }
        
        form button.btn-save { 
            background: var(--secondary-color); color: white; border: none; 
            width: 100%; padding: 12px; margin-top: 15px; border-radius: 5px;
            font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        form button.btn-save:hover { background: #45a049; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8f9fa; color: var(--primary-color); padding: 15px; text-align: left; border-bottom: 2px solid #dee2e6; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 0.9em; }
        tr:hover { background: #f1f4f7; }

        .btn-group { display: flex; gap: 5px; }
        .table-btn { 
            background: #eee; border: 1px solid #ccc; padding: 5px 10px; 
            border-radius: 4px; cursor: pointer; font-size: 0.75em; 
        }
        .table-btn.cancel { color: #dc3545; border-color: #ffc9c9; }
        .table-btn.cancel:hover { background: #dc3545; color: white; }

        .btn-tutorial {
            background: var(--accent-color); color: white; padding: 10px 20px;
            border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.9em;
        }

        .toast { visibility:hidden; min-width:250px; background:var(--secondary-color); color:white; text-align:center; border-radius:5px; padding:15px; position:fixed; top:20px; right:20px; z-index:1000; box-shadow: var(--shadow); opacity:0; transition: 0.5s; }
        .toast.show { visibility:visible; opacity:1; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index: 2000; }
        .modal-content { background:#fff; padding:30px; border-radius:10px; text-align:center; width: 350px; }
    </style>
</head>
<body>

<?php if($mensagem): ?>
<div id="toast" class="toast"><?php echo $mensagem; ?></div>
<script>
    const toast = document.getElementById('toast');
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
</script>
<?php endif; ?>

<div class="container">
    <header class="header-actions">
        <img src="img/logoserra.png" alt="Logo Serra">
        <a href="tutorial.php" class="btn-tutorial">Tutorial do Sistema</a>
    </header>

    <aside class="sidebar">
        <h2>Nova Portaria</h2>
        <form method="post">
            <button type="button" class="btn-save" style="background:#2980b9; margin-top: 10px;" onclick="window.location='gerar_zip.php'">
    📦 Baixar Todas (ZIP)
</button>

            <label>Nº Portaria (Opcional)</label>
            <input type="number" name="numero" placeholder="Automático se vazio">

            <label>Procurador(a)</label>
            <input type="text" name="procurador" required>

            <label>Sexo</label>
            <select name="sexo" required>
                <option value="M">Masculino</option>
                <option value="F">Feminino</option>
            </select>

            <label>OAB</label>
            <input type="text" name="oab" required>

            <label>Processo</label>
            <input type="text" name="processo" required>

            <label>Autor</label>
            <input type="text" name="autor" required>

            <label>Vara</label>
            <input type="text" name="vara" required>

            <label>Data</label>
            <input type="date" name="data" required>

            <button type="submit" name="salvar" class="btn-save">Gerar e Salvar</button>
        </form>
    </aside>

    <main class="main-content">
        <h2>Histórico de Portarias</h2>
        
        <form id="formExcluirVarias" method="post">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="marcarTodos(this)"></th>
                            <th>Nº/Ano</th>
                            <th>Procurador</th>
                            <th>Processo/Autor</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $db->query("SELECT * FROM portarias $filtro ORDER BY id DESC");
                        while($row=$res->fetchArray()){
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='selecionados[]' value='".$row['id']."'></td>";
                            echo "<td><strong>".$row['numero']."/".$row['ano']."</strong></td>";
                            echo "<td>".$row['procurador']."</td>";
                            echo "<td><small>".$row['processo']."<br>".$row['autor']."</small></td>";
                            echo "<td>".dataExtenso($row['data_portaria'])."</td>";
                            echo "<td>
                                <div class='btn-group'>
                                    <button type='button' class='table-btn' onclick=\"window.open('gerar_pdf.php?id=".$row['id']."','_blank')\">Ver</button>
                                    <button type='button' class='table-btn' onclick=\"window.location='editar.php?id=".$row['id']."'\">Editar</button>
                                    <button type='button' class='table-btn cancel' onclick='abrirModal(".$row['id'].")'>Apagar</button>
                                </div>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <br>
            <button type="button" class="table-btn cancel" onclick="abrirModalMultiplo()">Apagar Selecionados</button>
        </form>
    </main>
</div>

<div id="modalExcluir" class="modal">
    <div class="modal-content">
        <h3>Confirmar</h3>
        <p style="color: #dc3545; font-weight: bold;">A EXCLUSÃO É PERMANENTE E NÃO DÁ PRA RECUPERAR</p>
        <p>Deseja realmente apagar?</p>
        <button class="btn-save" style="background:#dc3545" onclick="confirmarExclusao()">Sim, Apagar</button>
        <button class="table-btn" onclick="fecharModal()">Cancelar</button>
    </div>
</div>

<script>
let idExcluir = null;
let excluirMultiplo = false;

function abrirModal(id){ idExcluir = id; excluirMultiplo = false; document.getElementById("modalExcluir").style.display="flex"; }
function abrirModalMultiplo(){
    const cb = document.querySelectorAll('input[name="selecionados[]"]:checked');
    if(cb.length===0){ alert("Selecione itens primeiro."); return; }
    excluirMultiplo = true; document.getElementById("modalExcluir").style.display="flex";
}
function fecharModal(){ document.getElementById("modalExcluir").style.display="none"; }
function confirmarExclusao(){
    if(excluirMultiplo){
        document.getElementById("formExcluirVarias").action="apagar_varios.php?msg=excluido";
        document.getElementById("formExcluirVarias").submit();
    } else {
        window.location = "apagar.php?id=" + idExcluir + "&msg=excluido";
    }
}
function marcarTodos(source){
    const checkboxes = document.querySelectorAll('input[name="selecionados[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>

</body>
</html>