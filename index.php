<?php
$db = new SQLite3('data/portarias.db');

// --- NOVA LÓGICA DE API PARA PREENCHIMENTO AUTOMÁTICO ---
if(isset($_GET['buscar_dados'])){
    $tipo = $_GET['tipo'];
    $valor = $_GET['valor'];
    $dados = [];
    
    if($tipo == 'procurador'){
        $res = $db->query("SELECT oab, sexo FROM portarias WHERE procurador = '$valor' ORDER BY id DESC LIMIT 1");
        $dados = $res->fetchArray(SQLITE3_ASSOC);
    } elseif($tipo == 'processo'){
        $res = $db->query("SELECT autor, vara FROM portarias WHERE processo = '$valor' ORDER BY id DESC LIMIT 1");
        $dados = $res->fetchArray(SQLITE3_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode($dados ?: []);
    exit;
}
// -------------------------------------------------------

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
    $numero = !empty($_POST['numero']) ? intval($_POST['numero']) : $proxNumero;
    
    $procurador = $_POST['procurador'];
    $sexo = $_POST['sexo'];
    $oab = $_POST['oab'];
    $processo = $_POST['processo'];
    $autor = $_POST['autor'];
    $vara = $_POST['vara'];
    $data = $_POST['data'];

    $db->exec("INSERT INTO portarias(numero,ano,procurador,sexo,oab,processo,autor,vara,data_portaria)
    VALUES('$numero', '$anoAtual', '$procurador', '$sexo', '$oab', '$processo', '$autor', '$vara', '$data')");

    header("Location: index.php?msg=editado&download_id=" . $db->lastInsertRowID());
    exit;
}

// LOGICA FILTROS
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

// BUSCA DE DADOS PARA PREENCHIMENTO AUTOMÁTICO (DATALISTS)
$lista_procuradores = $db->query("SELECT DISTINCT procurador FROM portarias ORDER BY procurador ASC");
$lista_oab = $db->query("SELECT DISTINCT oab FROM portarias ORDER BY oab ASC");
$lista_processos = $db->query("SELECT DISTINCT processo FROM portarias ORDER BY processo ASC");
$lista_autores = $db->query("SELECT DISTINCT autor FROM portarias ORDER BY autor ASC");
$lista_varas = $db->query("SELECT DISTINCT vara FROM portarias ORDER BY vara ASC");

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
--text-main:#ffffff;
--primary-color:#ffffff;
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
grid-template-rows:auto 1fr;
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

.dark-toggle {
    cursor: pointer;
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 50px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    font-weight: 600;
}

.dark-toggle:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

body.dark-mode .dark-toggle {
    background: #f1c40f;
    color: #2c3e50;
}

.sidebar{ background:var(--bg-card); padding:25px; border-radius:var(--border-radius); box-shadow:var(--shadow); height:fit-content; position:sticky; top:20px; }
.main-content{ background:var(--bg-card); padding:25px; border-radius:var(--border-radius); box-shadow:var(--shadow); }

h2{ color:var(--primary-color); margin: 0 0 20px 0; font-size: 1.4rem; border-left: 5px solid var(--secondary-color); padding-left: 15px; }

form label{ display:block; font-weight:600; margin:10px 0 5px 0; font-size:0.85em; color:#777; }
body.dark-mode form label { color: #bbbbbb; }

form input, form select{ width:100%; padding:10px; margin-bottom:5px; border-radius:5px; border:1px solid #ddd; box-sizing:border-box; background: var(--bg-card); color: var(--text-main); }

.filter-bar { 
    display: flex; 
    gap: 10px; 
    margin-bottom: 20px; 
    align-items: center; 
    flex-wrap: nowrap; 
}

.filter-bar input, .filter-bar select { 
    flex: 1; 
    margin-bottom: 0; 
    min-width: 150px; 
}

.filter-bar button { 
    padding: 10px 20px; 
    background: var(--primary-color); 
    color: #ffffff !important; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
}

body.dark-mode .filter-bar button {
    background: #2c3e50; 
    color: #ffffff !important;
}

table{ width:100%; border-collapse:collapse; margin-top:10px; }
th{ background:rgba(0,0,0,0.03); padding:15px; text-align:left; border-bottom:2px solid #dee2e6; }
td{ padding:12px 15px; border-bottom:1px solid rgba(0,0,0,0.05); font-size:0.9em; }
tr:hover{ background:rgba(0,0,0,0.02); }

.btn-group{ display:flex; gap:5px; }
.table-btn{ background:rgba(0,0,0,0.05); border:1px solid #ccc; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:0.75em; color: inherit; transition: 0.2s; }
.table-btn:hover { filter: brightness(0.9); transform: translateY(-1px); }
.table-btn.cancel{ color:#dc3545; border-color:#ffc9c9; }
.btn-tutorial{ background:var(--accent-color); color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:0.9em; }

/* MODAL MODERNO */
.modal { 
    display: none; 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 100%; 
    background: rgba(0, 0, 0, 0.4); 
    backdrop-filter: blur(8px); 
    -webkit-backdrop-filter: blur(8px);
    align-items: center; 
    justify-content: center; 
    z-index: 2000; 
}

.modal.show { display: flex; animation: fadeIn 0.3s ease; }

.modal-content { 
    background: var(--bg-card); 
    padding: 40px; 
    border-radius: 20px; 
    text-align: center; 
    width: 90%; 
    max-width: 380px; 
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); 
    border: 1px solid rgba(255,255,255,0.1);
    transform: translateY(20px);
    transition: 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal.show .modal-content { transform: translateY(0); }

.modal-icon {
    width: 80px;
    height: 80px;
    background: #fff5f5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: #dc3545;
    font-size: 40px;
    animation: pulseError 2s infinite;
}

body.dark-mode .modal-icon { background: #3d1a1a; }

.modal h3 { margin: 10px 0; font-size: 1.5rem; color: var(--text-main); }
.modal p { color: #666; margin-bottom: 30px; line-height: 1.5; }
body.dark-mode .modal p { color: #aaa; }

.modal-footer { display: flex; gap: 12px; }

.btn-modal {
    flex: 1;
    padding: 12px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.btn-cancelar { background: #e2e8f0; color: #475569; }
.btn-cancelar:hover { background: #cbd5e1; }
.btn-confirmar { background: #dc3545; color: white; box-shadow: 0 4px 14px 0 rgba(220, 53, 69, 0.39); }
.btn-confirmar:hover { background: #c82333; transform: scale(1.02); }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes pulseError { 
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
    70% { box-shadow: 0 0 0 15px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

</style>
</head>

<body>

<?php if($mensagem): ?>
<div id="toast" class="toast show" style="visibility:visible; position:fixed; top:20px; right:20px; background:var(--secondary-color); color:white; padding:15px; border-radius:5px; z-index:3000;"><?php echo $mensagem; ?></div>
<script>setTimeout(()=>{document.getElementById('toast').style.display='none';},3000);</script>
<?php endif; ?>

<?php if(isset($_GET['download_id'])): ?>
<script>
window.addEventListener("load", function(){
    const link = document.createElement('a');
    link.href = 'gerar_pdf.php?id=<?php echo intval($_GET['download_id']); ?>&download=1';
    link.click();
    window.history.replaceState({}, document.title, "index.php?msg=editado");
});
</script>
<?php endif; ?>

<div class="container">
<header class="header-actions">
    <img src="img/logoserra.png">
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="tutorial.php" class="btn-tutorial">Tutorial do Sistema</a>
        <button class="dark-toggle" id="btnDark" onclick="toggleDarkMode()">
            <span id="dark-icon">🌙</span> <span id="dark-text">Modo Escuro</span>
        </button>
    </div>
</header>

<aside class="sidebar">
    <h2>Nova Portaria</h2>
    <form method="post">
        <label>Nº Portaria (Opcional)</label>
        <input type="number" name="numero" placeholder="Automático se vazio">

        <label>Procurador(a)</label>
        <input type="text" name="procurador" id="proc_input" list="datalist_procurador" required autocomplete="off" onchange="autoPreencher('procurador', this.value)">
        <datalist id="datalist_procurador">
            <?php while($row = $lista_procuradores->fetchArray()) echo "<option value='".htmlspecialchars($row['procurador'])."'>"; ?>
        </datalist>      
        
        <label>Sexo</label>
        <select name="sexo" id="sexo_input" required><option value="M">Masculino</option><option value="F">Feminino</option></select>
        
        <label>OAB</label>
        <input type="text" name="oab" id="oab_input" list="datalist_oab" required autocomplete="off">
        <datalist id="datalist_oab">
            <?php while($row = $lista_oab->fetchArray()) echo "<option value='".htmlspecialchars($row['oab'])."'>"; ?>
        </datalist>
        
        <label>Processo</label>
        <input type="text" name="processo" id="processo_input" list="datalist_processo" required autocomplete="off" onchange="autoPreencher('processo', this.value)">
        <datalist id="datalist_processo">
            <?php while($row = $lista_processos->fetchArray()) echo "<option value='".htmlspecialchars($row['processo'])."'>"; ?>
        </datalist>
        
        <label>Autor</label>
        <input type="text" name="autor" id="autor_input" list="datalist_autor" required autocomplete="off">
        <datalist id="datalist_autor">
            <?php while($row = $lista_autores->fetchArray()) echo "<option value='".htmlspecialchars($row['autor'])."'>"; ?>
        </datalist>
        
        <label>Vara</label>
        <input type="text" name="vara" id="vara_input" list="datalist_vara" required autocomplete="off">
        <datalist id="datalist_vara">
            <?php while($row = $lista_varas->fetchArray()) echo "<option value='".htmlspecialchars($row['vara'])."'>"; ?>
        </datalist>
        
        <label>Data</label>
        <input type="date" name="data" required>

        <button type="submit" name="salvar" class="btn-save">Gerar e Salvar</button>
    </form>
</aside>

<main class="main-content">
    <h2>Histórico de Portarias</h2>

    <form method="get" class="filter-bar">
        <input type="text" name="buscar" placeholder="Busca..." value="<?php echo $_GET['buscar'] ?? ''; ?>">
        <select name="filtro_procurador">
            <option value="">Todos Procuradores</option>
            <?php 
            $lista_procuradores_filtro = $db->query("SELECT DISTINCT procurador FROM portarias ORDER BY procurador ASC");
            while($p = $lista_procuradores_filtro->fetchArray()){
                $sel = (isset($_GET['filtro_procurador']) && $_GET['filtro_procurador'] == $p['procurador']) ? 'selected' : '';
                echo "<option value='{$p['procurador']}' $sel>{$p['procurador']}</option>";
            }
            ?>
        </select>
        <button type="submit">Filtrar</button>
        <button type="button" onclick="window.location='index.php'" style="background:#7f8c8d">Limpar</button>
    </form>

    <form id="formExcluirVarias" method="post">
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" onclick="marcarTodos(this)"></th>
                        <th>Nº/Ano</th>
                        <th>Procurador</th>
                        <th>Processo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res=$db->query("SELECT * FROM portarias $filtro ORDER BY id DESC");
                    while($row=$res->fetchArray()){
                        echo "<tr>
                            <td><input type='checkbox' name='selecionados[]' value='".$row['id']."'></td>
                            <td><strong>".$row['numero']."/".$row['ano']."</strong></td>
                            <td>".$row['procurador']."</td>
                            <td><small>".$row['processo']."</small></td>
                            <td>
                                <div class='btn-group'>
                                    <button type='button' class='table-btn' onclick=\"window.open('gerar_pdf.php?id=".$row['id']."','_blank')\">Ver</button>
                                    <button type='button' class='table-btn cancel' onclick='abrirModal(".$row['id'].")'>Apagar</button>
                                </div>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="button" class="table-btn cancel" onclick="abrirModalMultiplo()">Apagar Selecionados</button>
            <button type="button" class="table-btn" style="background: #2980b9; color: white;" onclick="baixarVarios()">📦 Baixar ZIP</button>
        </div>
    </form>
</main>
</div>

<div id="modalExcluir" class="modal">
    <div class="modal-content">
        <div class="modal-icon">✕</div>
        <h3>Você tem certeza?</h3>
        <p>Esta ação não pode ser desfeita. Todos os dados selecionados serão removidos permanentemente.</p>
        <div class="modal-footer">
            <button class="btn-modal btn-cancelar" onclick="fecharModal()">Cancelar</button>
            <button class="btn-modal btn-confirmar" onclick="confirmarExclusao()">Sim, Apagar</button>
        </div>
    </div>
</div>

<script>
// FUNÇÃO DE AUTO-PREENCHIMENTO
function autoPreencher(tipo, valor) {
    if(!valor) return;
    fetch(`index.php?buscar_dados=1&tipo=${tipo}&valor=${encodeURIComponent(valor)}`)
        .then(response => response.json())
        .then(data => {
            if(Object.keys(data).length > 0) {
                if(tipo === 'procurador') {
                    document.getElementById('oab_input').value = data.oab || '';
                    document.getElementById('sexo_input').value = data.sexo || 'M';
                } else if(tipo === 'processo') {
                    document.getElementById('autor_input').value = data.autor || '';
                    document.getElementById('vara_input').value = data.vara || '';
                }
            }
        });
}

let idExcluir=null;
let excluirMultiplo=false;

function abrirModal(id) {
    idExcluir = id;
    excluirMultiplo = false;
    const m = document.getElementById("modalExcluir");
    m.style.display = "flex";
    setTimeout(()=> m.classList.add('show'), 10);
}

function abrirModalMultiplo() {
    if(document.querySelectorAll('input[name="selecionados[]"]:checked').length === 0) return alert("Selecione itens.");
    excluirMultiplo = true;
    const m = document.getElementById("modalExcluir");
    m.style.display = "flex";
    setTimeout(()=> m.classList.add('show'), 10);
}

function fecharModal() {
    const m = document.getElementById("modalExcluir");
    m.classList.remove('show');
    setTimeout(()=> m.style.display = "none", 300);
}

function confirmarExclusao(){
    const f = document.getElementById("formExcluirVarias");
    if(excluirMultiplo) { f.action="apagar_varios.php?msg=excluido"; f.submit(); }
    else { window.location="apagar.php?id="+idExcluir+"&msg=excluido"; }
}

function marcarTodos(s){ document.querySelectorAll('input[name="selecionados[]"]').forEach(c=>c.checked=s.checked); }

function updateDarkModeUI() {
    const isDark = document.body.classList.contains("dark-mode");
    const icon = document.getElementById("dark-icon");
    const text = document.getElementById("dark-text");
    
    if(icon && text) {
        if(isDark) {
            icon.innerText = "☀️";
            text.innerText = "Modo Claro";
        } else {
            icon.innerText = "🌙";
            text.innerText = "Modo Escuro";
        }
    }
}

function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");
    localStorage.setItem("darkmode", document.body.classList.contains("dark-mode"));
    updateDarkModeUI();
}

function baixarVarios() {
    if(document.querySelectorAll('input[name="selecionados[]"]:checked').length === 0) return alert("Selecione itens.");
    const f = document.getElementById("formExcluirVarias");
    f.action = "gerar_zip.php";
    f.submit();
    setTimeout(() => f.action = "", 500);
}

window.onload = () => { 
    if(localStorage.getItem("darkmode")==="true") {
        document.body.classList.add("dark-mode");
    }
    if (window.location.search.includes("msg=")) {
        const novaUrl = window.location.pathname + window.location.search.replace(/[?&]msg=[^&]+/, "").replace(/^&/, "?");
        window.history.replaceState({}, document.title, novaUrl);
    }
    
    if(localStorage.getItem("darkmode")==="true") {
        document.body.classList.add("dark-mode");
    }
    updateDarkModeUI();
};
</script>
</body>
</html>