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

    $novoId = $db->lastInsertRowID();

    header("Location: index.php?msg=editado&download_id=" . $novoId);
    exit;
}

// LOGICA DASHBOARD
$anoFiltroStat = date("Y");
$totalAno = $db->querySingle("SELECT COUNT(*) FROM portarias WHERE ano = '$anoFiltroStat'");
$maisDesignado = $db->querySingle("SELECT procurador FROM portarias GROUP BY procurador ORDER BY COUNT(*) DESC LIMIT 1") ?: "Nenhum";

// Dados do Gráfico
$mesesGrafico = [];
for($i=1; $i<=12; $i++){
    $m = str_pad($i, 2, "0", STR_PAD_LEFT);
    $qtd = $db->querySingle("SELECT COUNT(*) FROM portarias WHERE strftime('%m', data_portaria) = '$m' AND ano = '$anoFiltroStat'");
    $mesesGrafico[] = $qtd;
}

// LOGICA FILTROS AVANÇADOS
$filtro="WHERE 1=1";
if(!empty($_GET['buscar'])){
    $busca=$_GET['buscar'];
    $filtro .= " AND (processo LIKE '%$busca%' OR procurador LIKE '%$busca%' OR autor LIKE '%$busca%')";
}
if(!empty($_GET['filtro_ano'])){
    $fAno = intval($_GET['filtro_ano']);
    $filtro .= " AND ano = $fAno";
}
if(!empty($_GET['filtro_procurador'])){
    $fProc = $_GET['filtro_procurador'];
    $filtro .= " AND procurador = '$fProc'";
}

$mensagem = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'excluido') $mensagem = "Portaria excluída com sucesso!";
    if($_GET['msg'] == 'editado') $mensagem = "Portaria salva com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Sistema de Portarias - Desktop</title>

<style>
:root{
--primary-color:#2c3e50;
--secondary-color:#4CAF50;
--accent-color:#ff5722;
--bg-body:#f0f2f5;
--bg-card:#ffffff;
--text-main:#333;
--border-radius:8px;
--shadow:0 4px 12px rgba(0,0,0,0.08);
}

body.dark-mode{
--bg-body:#121212;
--bg-card:#1e1e1e;
--text-main:#e4e4e4;
--primary-color:#e4e4e4;
--shadow:0 4px 12px rgba(0,0,0,0.6);
}

body{
font-family:'Segoe UI',Roboto,sans-serif;
background:var(--bg-body);
margin:0;
color:var(--text-main);
transition:0.3s;
}

.container{
display:grid;
grid-template-columns:350px 1fr;
grid-template-rows:auto auto 1fr;
gap:20px;
padding:20px;
min-height:100vh;
box-sizing:border-box;
}

.header-actions{
grid-column:1 / -1;
display:flex;
justify-content:space-between;
align-items:center;
background:var(--bg-card);
padding:15px 25px;
border-radius:var(--border-radius);
box-shadow:var(--shadow);
}

.header-actions img{height:50px;}

/* DASHBOARD STYLE */
.dashboard {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.stat-card {
    background: var(--bg-card);
    padding: 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border-top: 4px solid var(--secondary-color);
}
.stat-card h3 { margin: 0; font-size: 0.85em; opacity: 0.7; text-transform: uppercase; }
.stat-card p { margin: 10px 0 0; font-size: 1.4em; font-weight: bold; }

.chart-container { height: 50px; display: flex; align-items: flex-end; gap: 3px; margin-top: 10px; }
.chart-bar { background: var(--secondary-color); flex: 1; border-radius: 2px 2px 0 0; min-height: 2px; }

.dark-toggle{
cursor:pointer;
background:var(--primary-color);
color:white;
border:none;
padding:8px 12px;
border-radius:6px;
font-size:14px;
}

.sidebar{
background:var(--bg-card);
padding:25px;
border-radius:var(--border-radius);
box-shadow:var(--shadow);
height:fit-content;
position:sticky;
top:20px;
}

.main-content{
background:var(--bg-card);
padding:25px;
border-radius:var(--border-radius);
box-shadow:var(--shadow);
}

h2{
color:var(--primary-color);
margin:0 0 20px 0;
font-size:1.4rem;
border-left:5px solid var(--secondary-color);
padding-left:15px;
}

form label{display:block;font-weight:600;margin:10px 0 5px 0;font-size:0.85em;color:#777;}

form input,form select{
width:100%;
padding:10px;
margin-bottom:5px;
border-radius:5px;
border:1px solid #ddd;
box-sizing:border-box;
}

body.dark-mode input,
body.dark-mode select{
background:#2a2a2a;
color:#fff;
border:1px solid #444;
}

form button.btn-save{
background:var(--secondary-color);
color:white;
border:none;
width:100%;
padding:12px;
margin-top:15px;
border-radius:5px;
font-weight:bold;
cursor:pointer;
}

.filter-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}
.filter-bar input, .filter-bar select { margin-bottom: 0; }
.filter-bar button { padding: 0 20px; background: var(--primary-color); color: white; border: none; border-radius: 5px; cursor: pointer; }

table{
width:100%;
border-collapse:collapse;
margin-top:10px;
}

th{
background:#f8f9fa;
padding:15px;
text-align:left;
border-bottom:2px solid #dee2e6;
}

body.dark-mode th{
background:#2a2a2a;
}

td{
padding:12px 15px;
border-bottom:1px solid #eee;
font-size:0.9em;
}

body.dark-mode td{
border-bottom:1px solid #333;
}

tr:hover{
background:#f1f4f7;
}

body.dark-mode tr:hover{
background:#2a2a2a;
}

.btn-group{display:flex;gap:5px;}

.table-btn{
background:#eee;
border:1px solid #ccc;
padding:5px 10px;
border-radius:4px;
cursor:pointer;
font-size:0.75em;
}

body.dark-mode .table-btn{
background:#333;
color:#fff;
border:1px solid #444;
}

.table-btn.cancel{
color:#dc3545;
border-color:#ffc9c9;
}

.table-btn.cancel:hover{
background:#dc3545;
color:white;
}

.btn-tutorial{
background:var(--accent-color);
color:white;
padding:10px 20px;
border-radius:8px;
text-decoration:none;
font-weight:bold;
font-size:0.9em;
}

.toast{
visibility:hidden;
min-width:250px;
background:var(--secondary-color);
color:white;
text-align:center;
border-radius:5px;
padding:15px;
position:fixed;
top:20px;
right:20px;
z-index:1000;
box-shadow:var(--shadow);
opacity:0;
transition:0.5s;
}

.toast.show{visibility:visible;opacity:1;}

/* Estilo Moderno do Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px); /* Efeito de vidro no fundo */
    align-items: center;
    justify-content: center;
    z-index: 2000;
    transition: all 0.3s;
}

.modal-content {
   background:var(--bg-card);
padding:30px;
border-radius:10px;
text-align:center;
width:350px;
    max-width: 400px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.1);
    transform: scale(0.7); /* Começa menor para a animação */
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Classe para animar a abertura */
.modal.show .modal-content {
    transform: scale(1);
}

.modal-content h3{
font-size:26px;       /* título maior */
margin-bottom:15px;
}

.modal-content p{
font-size:18px;
margin-bottom:25px;
}

</style>
</head>

<body>

<?php if($mensagem): ?>
<div id="toast" class="toast"><?php echo $mensagem; ?></div>
<script>
const toast=document.getElementById('toast');
toast.classList.add('show');
setTimeout(()=>{toast.classList.remove('show');},2000);
</script>
<?php endif; ?>

<?php if(isset($_GET['download_id'])): ?>
<script>
window.addEventListener("load", function(){
const id="<?php echo intval($_GET['download_id']); ?>";
const link = document.createElement('a');
link.href = 'gerar_pdf.php?id=' + id + '&download=1';
link.download = '';
document.body.appendChild(link);
link.click();
document.body.removeChild(link);
window.history.replaceState({}, document.title, "index.php?msg=editado");
});
</script>
<?php endif; ?>

<div class="container">
<header class="header-actions">
<img src="img/logoserra.png">
<div style="display:flex;gap:10px;align-items:center;">
<a href="tutorial.php" class="btn-tutorial">Tutorial do Sistema</a>
<button class="dark-toggle" onclick="toggleDarkMode()">🌙</button>
</div>
</header>

<section class="dashboard">
    <div class="stat-card">
        <h3>Total de portaria em <?php echo $anoFiltroStat; ?></h3>
        <p><?php echo $totalAno; ?></p>
    </div>
    <div class="stat-card">
        <h3>Mais Designado</h3>
        <p style="font-size: 1.1em;"><?php echo $maisDesignado; ?></p>
    </div>
    <div class="stat-card">
        <h3>Fluxo Mensal (<?php echo $anoFiltroStat; ?>)</h3>
        <div class="chart-container">
            <?php 
            $max = max($mesesGrafico) ?: 1;
            foreach($mesesGrafico as $mQtd) {
                $height = ($mQtd / $max) * 100;
                echo "<div class='chart-bar' style='height: {$height}%' title='{$mQtd} portarias'></div>";
            }
            ?>
        </div>
    </div>
</section>

<aside class="sidebar">
<h2>Nova Portaria</h2>
<form method="post">
<label>Nº Portaria (Opcional)</label>
<input type="number" name="numero" placeholder="Automático se vazio">

<label>Procurador(a)</label>
<input type="text" name="procurador" list="lista_procuradores" required>
<datalist id="lista_procuradores">
    <?php 
    $resP = $db->query("SELECT DISTINCT procurador FROM portarias");
    while($p = $resP->fetchArray()) echo "<option value='{$p['procurador']}'>";
    ?>
</datalist>

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

<form method="get" class="filter-bar">
    <input type="text" name="buscar" placeholder="Busca geral..." value="<?php echo $_GET['buscar'] ?? ''; ?>">
    
    <select name="filtro_procurador">
        <option value="">Todos Procuradores</option>
        <?php 
        $resP = $db->query("SELECT DISTINCT procurador FROM portarias");
        while($p = $resP->fetchArray()){
            $selected = (isset($_GET['filtro_procurador']) && $_GET['filtro_procurador'] == $p['procurador']) ? 'selected' : '';
            echo "<option value='{$p['procurador']}' $selected>{$p['procurador']}</option>";
        }
        ?>
    </select>

    <select name="filtro_ano" style="width: 120px;">
        <option value="">Todos Anos</option>
        <?php 
        $resA = $db->query("SELECT DISTINCT ano FROM portarias ORDER BY ano DESC");
        while($a = $resA->fetchArray()){
            $selected = (isset($_GET['filtro_ano']) && $_GET['filtro_ano'] == $a['ano']) ? 'selected' : '';
            echo "<option value='{$a['ano']}' $selected>{$a['ano']}</option>";
        }
        ?>
    </select>
    
    <button type="submit">Filtrar</button>
    <?php if(count($_GET) > 0): ?>
        <button type="button" onclick="window.location='index.php'" style="background:#7f8c8d">Limpar</button>
    <?php endif; ?>
</form>

<form id="formExcluirVarias" method="post">
<div style="overflow-x:auto;">
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
$res=$db->query("SELECT * FROM portarias $filtro ORDER BY id DESC");
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
<div style="margin-top: 20px; display: flex; gap: 10px;">
    <button type="button" class="table-btn cancel" onclick="abrirModalMultiplo()">Apagar Selecionados</button>
    
    <button type="button" class="table-btn" style="background: #2980b9; color: white; border: none;" onclick="baixarVarios()">
        📦 Baixar Selecionados (ZIP)
    </button>
</div>
</form>
</main>
</div>

<div id="modalExcluir" class="modal">
    <div class="modal-content">
        <div style="font-size: 50px; color: #dc3545; margin-bottom: 15px;">⚠️</div>
        <h3>Confirmar Exclusão</h3>
        <p>Você está prestes a apagar os registros selecionados.</p>
        <p style="color: #dc3545; font-weight: bold; font-size: 0.9em; background: rgba(220, 53, 69, 0.1); padding: 10px; border-radius: 5px;">
            Os itens deletados não podem mais ser recuperados.
        </p>
        
        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
            <button class="table-btn" onclick="fecharModal()" style="padding: 10px 20px; flex: 1;">Cancelar</button>
            <button class="btn-save" style="background:#dc3545; margin: 0; padding: 10px 20px; flex: 1;" onclick="confirmarExclusao()">Sim, Apagar</button>
        </div>
    </div>
</div>
<div id="modalExcluir" class="modal">
    <div class="modal-content">
        <div style="font-size: 50px; color: #dc3545; margin-bottom: 15px;">⚠️</div>
        <h3>Confirmar Exclusão</h3>
        <p>Você está prestes a apagar os registros selecionados.</p>
        <p style="color: #dc3545; font-weight: bold; font-size: 0.9em; background: rgba(220, 53, 69, 0.1); padding: 10px; border-radius: 5px;">
            Os itens deletados não podem mais ser recuperados.
        </p>
        
        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
            <button class="table-btn" onclick="fecharModal()" style="padding: 10px 20px; flex: 1;">Cancelar</button>
            <button class="btn-save" style="background:#dc3545; margin: 0; padding: 10px 20px; flex: 1;" onclick="confirmarExclusao()">Sim, Apagar</button>
        </div>
    </div>
</div>

<script>
let idExcluir=null;
let excluirMultiplo=false;

function abrirModal(id) {
    idExcluir = id;
    excluirMultiplo = false;
    const modal = document.getElementById("modalExcluir");
    modal.style.display = "flex";
    setTimeout(() => modal.classList.add('show'), 10); // Gatilho para animação
}

function abrirModalMultiplo() {
    const cb = document.querySelectorAll('input[name="selecionados[]"]:checked');
    if (cb.length === 0) {
        alert("Selecione itens primeiro.");
        return;
    }
    excluirMultiplo = true;
    const modal = document.getElementById("modalExcluir");
    modal.style.display = "flex";
    setTimeout(() => modal.classList.add('show'), 10);
}

function fecharModal() {
    const modal = document.getElementById("modalExcluir");
    modal.classList.remove('show');
    setTimeout(() => modal.style.display = "none", 300); // Espera a animação sumir
}

window.onclick = function(event) {
    const modal = document.getElementById("modalExcluir");
    if (event.target == modal) {
        fecharModal();
    }
}

function confirmarExclusao(){
if(excluirMultiplo){
document.getElementById("formExcluirVarias").action="apagar_varios.php?msg=excluido";
document.getElementById("formExcluirVarias").submit();
}else{
window.location="apagar.php?id="+idExcluir+"&msg=excluido";
}
}

function marcarTodos(source){
const checkboxes=document.querySelectorAll('input[name="selecionados[]"]');
checkboxes.forEach(cb=>cb.checked=source.checked);
}

function toggleDarkMode(){
document.body.classList.toggle("dark-mode");
localStorage.setItem("darkmode",document.body.classList.contains("dark-mode"));
}

function baixarVarios() {
    const cb = document.querySelectorAll('input[name="selecionados[]"]:checked');
    if (cb.length === 0) {
        alert("Selecione pelo menos uma portaria para baixar.");
        return;
    }
    const form = document.getElementById("formExcluirVarias");
    form.action = "gerar_zip.php"; // Muda o destino para o gerador de ZIP
    form.submit();
    
    // Restaura o action original após o envio para não quebrar a função de apagar
    setTimeout(() => { form.action = ""; }, 500);
}

window.addEventListener("load", function(){
if(localStorage.getItem("darkmode")==="true"){
document.body.classList.add("dark-mode");
}

});
</script>
</body>
</html>