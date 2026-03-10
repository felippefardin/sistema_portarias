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
        $proxNumero = $numero + 1;
    } else {
        $numero = $proxNumero;
        $proxNumero++;
    }
    $procurador = $_POST['procurador'];
    $sexo = $_POST['sexo'];
    $oab = $_POST['oab'];
    $processo = $_POST['processo'];
    $autor = $_POST['autor'];
    $vara = $_POST['vara'];
    $data = $_POST['data'];

    $db->exec("INSERT INTO portarias(numero,ano,procurador,sexo,oab,processo,autor,vara,data_portaria)
    VALUES(
        '$numero',
        '$anoAtual',
        '$procurador',
        '$sexo',
        '$oab',
        '$processo',
        '$autor',
        '$vara',
        '$data'
    )");

    echo "<script>window.open('gerar_pdf.php?id=".$db->lastInsertRowID()."&download=1','_blank');</script>";
}

$filtro="";
if(isset($_GET['buscar'])){
    $busca=$_GET['buscar'];
    $filtro="WHERE processo LIKE '%$busca%' 
    OR procurador LIKE '%$busca%' 
    OR autor LIKE '%$busca%'";
}

$mensagem = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'excluido') $mensagem = "Portaria excluída com sucesso!";
    if($_GET['msg'] == 'editado') $mensagem = "Portaria editada com sucesso!";
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Sistema de Portarias</title>
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana,sans-serif; background:#f4f6f8; margin:0; padding:0; }
.container { max-width:900px; margin:40px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.1); }
h2 { color:#333; margin-bottom:20px; border-bottom:2px solid #4CAF50; padding-bottom:8px; }
form input, form select, form button { width:100%; max-width:300px; padding:8px 12px; margin:6px 0; border-radius:5px; border:1px solid #ccc; box-sizing:border-box; }
form button { background-color:#4CAF50; color:white; border:none; cursor:pointer; width:auto; padding:10px 20px; transition:0.3s; }
form button:hover { background-color:#45a049; }

table { border-collapse:collapse; width:100%; margin-top:20px; background:#fff; border-radius:5px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
th, td { padding:12px 15px; text-align:left; }
th { background-color:#4CAF50; color:white; }
tr:nth-child(even){ background:#f9f9f9; }
tr:hover { background:#f1f1f1; }

/* Botões da tabela em grupo */
.btn-group { display:flex; gap:5px; flex-wrap:wrap; }
.table-btn { 
    background:#4CAF50; color:white; border:none; padding:6px 12px; border-radius:5px; cursor:pointer; font-size:0.85em; transition:0.3s; white-space:nowrap;
}
.table-btn:hover { background:#45a049; }
.table-btn.cancel { background:#dc3545; }
.table-btn.cancel:hover { background:#c82333; }

/* Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; }
.modal-content { background:#fff; padding:25px 35px; border-radius:10px; text-align:center; max-width:400px; width:90%; }
.modal button { padding:10px 18px; margin:10px 5px; border-radius:5px; border:none; cursor:pointer; background:#4CAF50; color:#fff; transition:0.3s; }
.modal button:hover { background:#45a049; }
.modal button.cancel { background:#6c757d; }
.modal button.cancel:hover { background:#5a6268; }

/* Toast */
.toast { visibility:hidden; min-width:250px; background:#4CAF50; color:white; text-align:center; border-radius:5px; padding:15px; position:fixed; top:20px; right:20px; z-index:1000; font-weight:bold; box-shadow:0 2px 6px rgba(0,0,0,0.2); opacity:0; transition:opacity 0.5s, top 0.5s; }
.toast.show { visibility:visible; opacity:1; top:40px; }

.logo { text-align:center; margin-bottom:30px; }
.logo img { max-width:180px; }

/* Botão Tutorial fixo no canto superior direito */
.btn-tutorial {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #ff5722; /* cor mais chamativa */
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    padding: 12px 20px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: 0.3s;
    z-index: 10000;
    font-size: 1em;
}
.btn-tutorial:hover {
    background: #e64a19;
}
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
    <a href="tutorial.php"><button class="btn-tutorial">Tutorial</button></a>

<div class="logo">
    <img src="img/logoserra.png" alt="Logo Serra">    
</div>

<h2>Cadastro de Portaria</h2>
<form method="post">
Número da Portaria (opcional)
<input type="number" name="numero" placeholder="Deixe em branco para sequência automática">

Procurador
<input type="text" name="procurador" required>

Sexo
<select name="sexo" required>
<option value="M">Masculino</option>
<option value="F">Feminino</option>
</select>

OAB
<input type="text" name="oab" required>

Processo
<input type="text" name="processo" required>

Autor
<input type="text" name="autor" required>

Vara
<input type="text" name="vara" required>

Data
<input type="date" name="data" required>

<button type="submit" name="salvar">Salvar Portaria</button>
</form>

<hr>

<h2>Histórico de Portarias</h2>
<form id="formExcluirVarias" method="post">
<table>
<tr>
<th><input type="checkbox" id="selecionarTodos" onclick="marcarTodos(this)"></th>
<th>Nº</th>
<th>Ano</th>
<th>Procurador</th>
<th>Processo</th>
<th>Autor</th>
<th>Data</th>
<th>Ações</th>
</tr>

<?php
$res = $db->query("SELECT * FROM portarias $filtro ORDER BY id DESC");
while($row=$res->fetchArray()){
    echo "<tr>";
    echo "<td><input type='checkbox' name='selecionados[]' value='".$row['id']."'></td>";
    echo "<td>".$row['numero']."</td>";
    echo "<td>".$row['ano']."</td>";
    echo "<td>".$row['procurador']."</td>";
    echo "<td>".$row['processo']."</td>";
    echo "<td>".$row['autor']."</td>";
    echo "<td>".dataExtenso($row['data_portaria'])."</td>";
    echo "<td>
        <div class='btn-group'>
            <button type='button' class='table-btn' onclick=\"window.open('gerar_pdf.php?id=".$row['id']."','_blank')\">Visualizar</button>
            <button type='button' class='table-btn' onclick=\"window.open('gerar_pdf.php?id=".$row['id']."&download=1','_blank')\">Download</button>
            <button type='button' class='table-btn' onclick=\"window.location='editar.php?id=".$row['id']."'\">Editar</button>
            <button type='button' class='table-btn cancel' onclick='abrirModal(".$row['id'].")'>Apagar</button>
        </div>
    </td>";
    echo "</tr>";
}
?>
</table>

<br>
<button type="button" class="table-btn cancel" onclick="abrirModalMultiplo()">Apagar Selecionados</button>
</form>

<div id="modalExcluir" class="modal">
<div class="modal-content">
<h3>Confirmar exclusão</h3>
<p>Deseja realmente apagar esta(s) portaria(s)?</p>
<button onclick="confirmarExclusao()">Sim</button>
<button class="cancel" onclick="fecharModal()">Não</button>
</div>
</div>

<script>
let idExcluir = null;
let excluirMultiplo = false;

function abrirModal(id){
    idExcluir = id;
    excluirMultiplo = false;
    document.getElementById("modalExcluir").style.display="flex";
}

function abrirModalMultiplo(){
    const checkboxes = document.querySelectorAll('input[name="selecionados[]"]:checked');
    if(checkboxes.length===0){ alert("Selecione pelo menos uma portaria."); return; }
    excluirMultiplo = true;
    document.getElementById("modalExcluir").style.display="flex";
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

</div>
</body>
</html>