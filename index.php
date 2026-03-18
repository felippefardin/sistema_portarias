<?php
include 'backup_auto.php';
$db = new SQLite3('data/portarias.db');
$db->exec('PRAGMA journal_mode = WAL;');

// --- LÓGICA DE API PARA PREENCHIMENTO AUTOMÁTICO ---
if(isset($_GET['buscar_dados'])){
    $tipo = $_GET['tipo'];
    $valor = $_GET['valor'];
    $dados = [];
    
    if($tipo == 'procurador'){
        $res = $db->query("SELECT oab, sexo, matricula FROM cadastro_procuradores WHERE nome = '$valor' LIMIT 1");
        $dados = $res->fetchArray(SQLITE3_ASSOC);
    } elseif($tipo == 'processo'){
        $res = $db->query("SELECT autor, vara FROM portarias WHERE processo = '$valor' ORDER BY id DESC LIMIT 1");
        $dados = $res->fetchArray(SQLITE3_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode($dados ?: []);
    exit;
}

$db->exec("CREATE TABLE IF NOT EXISTS portarias(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero INTEGER,
    ano INTEGER,
    procurador TEXT,
    sexo TEXT,
    oab TEXT,
    matricula TEXT, 
    processo TEXT,
    autor TEXT,
    vara TEXT,
    data_portaria TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS cadastro_procuradores(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    oab TEXT,
    sexo TEXT,
    matricula TEXT,
    email TEXT,
    status TEXT DEFAULT 'Ativo'
)");

$checkColumn = $db->query("PRAGMA table_info(portarias)");
$hasMatricula = false;
while($col = $checkColumn->fetchArray()) {
    if($col['name'] == 'matricula') $hasMatricula = true;
}
if(!$hasMatricula) $db->exec("ALTER TABLE portarias ADD COLUMN matricula TEXT");

if(isset($_POST['salvar'])){
    $anoAtual = date("Y");
    $resUltimo = $db->query("SELECT numero, ano FROM portarias ORDER BY id DESC LIMIT 1");
    $ultimo = $resUltimo->fetchArray();
    $proxNumero = (!$ultimo || $ultimo['ano'] != $anoAtual) ? 1 : $ultimo['numero'] + 1;
    $numero = !empty($_POST['numero']) ? intval($_POST['numero']) : $proxNumero;
    
    $stmt = $db->prepare("INSERT INTO portarias(numero,ano,procurador,sexo,oab,matricula,processo,autor,vara,data_portaria)
    VALUES(:numero, :ano, :procurador, :sexo, :oab, :matricula, :processo, :autor, :vara, :data)");
    $stmt->bindValue(':numero', $numero, SQLITE3_INTEGER);
    $stmt->bindValue(':ano', $anoAtual, SQLITE3_INTEGER);
    $stmt->bindValue(':procurador', $_POST['procurador'], SQLITE3_TEXT);
    $stmt->bindValue(':sexo', $_POST['sexo'], SQLITE3_TEXT);
    $stmt->bindValue(':oab', $_POST['oab'], SQLITE3_TEXT);
    $stmt->bindValue(':matricula', $_POST['matricula'], SQLITE3_TEXT);
    $stmt->bindValue(':processo', $_POST['processo'], SQLITE3_TEXT);
    $stmt->bindValue(':autor', $_POST['autor'], SQLITE3_TEXT);
    $stmt->bindValue(':vara', $_POST['vara'], SQLITE3_TEXT);
    $stmt->bindValue(':data', $_POST['data'], SQLITE3_TEXT);
    $stmt->execute();

    // Redireciona passando o ID para acionar o download automático
    header("Location: index.php?msg=editado&download_id=" . $db->lastInsertRowID());
    exit;
}

$filtro="WHERE 1=1";
if(!empty($_GET['buscar'])){
    $busca=$_GET['buscar'];
    $filtro .= " AND (processo LIKE '%$busca%' OR procurador LIKE '%$busca%' OR autor LIKE '%$busca%')";
}

$mensagem = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'excluido') $mensagem = "Portaria excluída com sucesso!";
    if($_GET['msg'] == 'editado') $mensagem = "Portaria salva com sucesso!";
}

$lista_procuradores = $db->query("SELECT DISTINCT nome as procurador FROM cadastro_procuradores ORDER BY nome ASC");
$lista_processos = $db->query("SELECT DISTINCT processo FROM portarias ORDER BY processo ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Sistema de Portarias - Desktop</title>
<style>
:root{--primary-color:#2c3e50;--secondary-color:#4CAF50;--accent-color:#ff5722;--bg-body:#f0f2f5;--bg-card:#ffffff;--text-main:#333;--border-radius:12px;--shadow:0 8px 30px rgba(0,0,0,0.05);}
body.dark-mode{--bg-body:#121212;--bg-card:#1e1e1e;--text-main:#f5f5f5;--primary-color:#ffffff;--shadow:0 8px 30px rgba(0,0,0,0.4);}

body{font-family:'Segoe UI',Roboto,sans-serif;background:var(--bg-body);margin:0;color:var(--text-main);transition:0.3s; overflow: hidden;}

.container{display:grid;grid-template-columns:380px 1fr; grid-template-rows: auto 1fr; gap:20px; padding:20px; height: 100vh; box-sizing:border-box;}
.header-actions{grid-column:1 / -1;display:flex;justify-content:space-between;align-items:center;background:var(--bg-card);padding:10px 25px;border-radius:var(--border-radius);box-shadow:var(--shadow);}

.sidebar{ background:var(--bg-card); padding:20px; border-radius:var(--border-radius); box-shadow:var(--shadow); overflow-y: auto; height: 100%;}
.main-content{ background:var(--bg-card); padding:20px; border-radius:var(--border-radius); box-shadow:var(--shadow); overflow-y: auto; height: 100%; }

h2{ color:var(--primary-color); margin: 0 0 15px 0; font-size: 1.3rem; border-left: 5px solid var(--secondary-color); padding-left: 12px; }
form label{ display:block; font-weight:600; margin:8px 0 3px 0; font-size:0.8em; color:#666; }
body.dark-mode form label { color: #aaa; }
form input, form select{ width:100%; padding:10px; margin-bottom:5px; border-radius:6px; border:1px solid #ddd; box-sizing:border-box; background: var(--bg-card); color: var(--text-main); font-size: 13px; }

.procurador-box { background: rgba(0,0,0,0.02); border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
body.dark-mode .procurador-box { background: rgba(255,255,255,0.03); border-color: #333; }
.procurador-box h3 { margin: 0 0 10px 0; font-size: 0.9em; text-transform: uppercase; color: var(--secondary-color); }

.filter-bar { display: flex; gap: 10px; margin-bottom: 20px; }
.filter-bar input{ flex: 1; }
.filter-bar button { padding: 10px 20px; background: #2c3e50; color: white !important; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
body.dark-mode .filter-bar button { background: #4a69bd; }

table{ width:100%; border-collapse:collapse; }
th{ background:rgba(0,0,0,0.02); padding:12px; text-align:left; border-bottom:2px solid #eee; font-size: 0.85em; }
td{ padding:10px 12px; border-bottom:1px solid rgba(0,0,0,0.03); font-size: 0.9em; }

.btn-cadastro, .btn-tutorial{ color:white !important; padding:8px 15px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:0.85em; display: flex; align-items: center; gap: 5px; }
.btn-cadastro{ background:#3498db; }
.btn-tutorial{ background: var(--accent-color); }

.dark-toggle { cursor: pointer; background: var(--primary-color); color: white; border: none; padding: 8px 15px; border-radius: 50px; font-size: 13px; display: flex; align-items: center; gap: 5px; font-weight: 600; }
body.dark-mode .dark-toggle { background: #f1c40f; color: #2c3e50; }

.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center; z-index: 2000; }
.modal.show { display: flex; }
.modal-content { background: var(--bg-card); padding: 30px; border-radius: 15px; text-align: center; width: 90%; max-width: 350px; }
.modal-buttons { display: flex; gap: 10px; margin-top: 20px; }
.modal-buttons button { flex: 1; padding: 10px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; }

.action-bar { margin-top: 20px; display: flex; gap: 10px; }
.table-btn { padding: 6px 12px; border-radius: 4px; border: 1px solid #ddd; background: white; cursor: pointer; font-size: 0.8em; color: #333; }
</style>
</head>
<body>

<?php if($mensagem): ?><div id="toast" style="position:fixed; top:20px; right:20px; background:var(--secondary-color); color:white; padding:15px 25px; border-radius:10px; z-index:3000;"><?php echo $mensagem; ?></div><script>setTimeout(()=>{document.getElementById('toast').style.display='none';},3000);</script><?php endif; ?>

<?php if(isset($_GET['download_id'])): ?>
<script>
window.addEventListener("load", function(){
    const id = "<?php echo intval($_GET['download_id']); ?>";
    window.open('gerar_pdf.php?id=' + id + '&download=1', '_blank');
    // Limpa a URL para não baixar novamente ao atualizar a página
    const novaUrl = window.location.pathname + window.location.search.replace(/&download_id=\d+/, "");
    window.history.replaceState({}, document.title, novaUrl);
});
</script>
<?php endif; ?>

<div class="container">
<header class="header-actions">
    <img src="img/logoserra.png" style="height: 40px;">
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="tutorial.php" class="btn-tutorial">📖 Tutorial</a>
        <a href="procuradores.php" class="btn-cadastro">⚖️ Procuradores</a>
        <button class="dark-toggle" id="btnDark" onclick="toggleDarkMode()">
            <span id="dark-icon">🌙</span> <span id="dark-text">Modo Escuro</span>
        </button>
    </div>
</header>

<aside class="sidebar">
    <h2>Nova Portaria</h2>
    <form method="post">
        <label>Nº Portaria</label>
        <input type="number" name="numero" placeholder="Automático">

        <div class="procurador-box">
            <h3>Dados do Procurador</h3>
            <label>Procurador(a)</label>
            <input type="text" name="procurador" id="proc_input" list="datalist_procurador" required onchange="autoPreencher('procurador', this.value)">
            <datalist id="datalist_procurador"><?php while($row = $lista_procuradores->fetchArray()) echo "<option value='".htmlspecialchars($row['procurador'])."'>"; ?></datalist>
            
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>Sexo</label>
                    <select name="sexo" id="sexo_input" required><option value="M">M</option><option value="F">F</option></select>
                </div>
                <div style="flex: 2;">
                    <label>OAB</label>
                    <input type="text" name="oab" id="oab_input" required>
                </div>
            </div>
            <label>Matrícula</label>
            <input type="text" name="matricula" id="matricula_input" required>
        </div>

        <label>Processo</label>
        <input type="text" name="processo" id="processo_input" list="datalist_processo" required onchange="autoPreencher('processo', this.value)">
        <datalist id="datalist_processo"><?php while($row = $lista_processos->fetchArray()) echo "<option value='".htmlspecialchars($row['processo'])."'>"; ?></datalist>
        <label>Autor</label>
        <input type="text" name="autor" id="autor_input" required>
        <label>Vara</label>
        <input type="text" name="vara" id="vara_input" required>
        <label>Data</label>
        <input type="date" name="data" required>
        <button type="submit" name="salvar" style="background:var(--secondary-color); color:white; font-weight:bold; width:100%; border:none; border-radius:8px; padding:12px; margin-top:10px; cursor:pointer;">Gerar e Salvar</button>
    </form>
</aside>

<main class="main-content">
    <h2>Histórico de Portarias</h2>
    <form method="get" class="filter-bar">
        <input type="text" name="buscar" placeholder="Pesquisar..." value="<?php echo $_GET['buscar'] ?? ''; ?>">
        <button type="submit">Filtrar</button>
    </form>

    <form id="formAcoesMassa" method="post">
        <table>
            <thead><tr><th><input type="checkbox" onclick="marcarTodos(this)"></th><th>Nº/Ano</th><th>Procurador</th><th>Processo</th><th>Ações</th></tr></thead>
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
            <button type='button' class='table-btn' onclick=\"window.open('gerar_pdf.php?id=".$row['id']."','_blank')\">Ver</button>
            
            <button type='button' class='table-btn' style='background:#3498db; color:white; border:none;' onclick=\"location.href='gerar_docx.php?id=".$row['id']."'\">WORD</button>
            
            <button type='button' class='table-btn' style='color:#e74c3c' onclick='abrirModal(".$row['id'].")'>Apagar</button>
        </td>
    </tr>";
}
                ?>
            </tbody>
        </table>
        <div class="action-bar">
            <button type="button" class="table-btn" style="background:#e74c3c; color:white; border:none;" onclick="abrirModalMultiplo()">Apagar Selecionados</button>
            <button type="button" class="table-btn" style="background:#2ecc71; color:white; border:none;" onclick="baixarZIP()">📦 Backup (ZIP)</button>
        </div>
    </form>
</main>
</div>

<div id="modalExcluir" class="modal">
    <div class="modal-content">
        <div class="modal-icon" style="font-size:40px; color:#e74c3c;">⚠️</div>
        <h3>Confirma exclusão?</h3>
        <p id="modalMsg" style="font-size: 0.9em; color: #666;">Esta ação não pode ser desfeita.</p>
        <div class="modal-buttons">
            <button onclick="fecharModal()" style="background:#eee;">Voltar</button>
            <button onclick="confirmarExclusao()" style="background:#e74c3c; color:white;">Apagar</button>
        </div>
    </div>
</div>

<script>
let idExcluir = null, multiplo = false;

function autoPreencher(tipo, valor) {
    if(!valor) return;
    fetch(`index.php?buscar_dados=1&tipo=${tipo}&valor=${encodeURIComponent(valor)}`)
        .then(response => response.json())
        .then(data => {
            if(Object.keys(data).length > 0) {
                if(tipo === 'procurador') {
                    document.getElementById('oab_input').value = data.oab || '';
                    document.getElementById('sexo_input').value = data.sexo || 'M';
                    document.getElementById('matricula_input').value = data.matricula || '';
                } else if(tipo === 'processo') {
                    document.getElementById('autor_input').value = data.autor || '';
                    document.getElementById('vara_input').value = data.vara || '';
                }
            }
        });
}

function abrirModal(id) { 
    idExcluir = id; multiplo = false;
    document.getElementById("modalExcluir").style.display = "flex";
    document.getElementById("modalExcluir").classList.add("show");
}

function abrirModalMultiplo() {
    if(document.querySelectorAll('input[name="selecionados[]"]:checked').length === 0) return alert("Selecione um item.");
    multiplo = true;
    document.getElementById("modalExcluir").style.display = "flex";
}

function fecharModal() { document.getElementById("modalExcluir").style.display = "none"; }

function confirmarExclusao(){
    const form = document.getElementById("formAcoesMassa");
    if(multiplo) { form.action = "apagar_varios.php?msg=excluido"; form.submit(); }
    else { window.location = "apagar.php?id=" + idExcluir + "&msg=excluido"; }
}

function baixarZIP() {
    const form = document.getElementById("formAcoesMassa");
    form.action = "gerar_zip.php"; form.submit();
    setTimeout(() => form.action = "", 500);
}

function marcarTodos(s){ document.querySelectorAll('input[name="selecionados[]"]').forEach(c=>c.checked=s.checked); }

function updateDarkModeUI() {
    const isDark = document.body.classList.contains("dark-mode");
    document.getElementById("dark-icon").innerText = isDark ? "☀️" : "🌙";
    document.getElementById("dark-text").innerText = isDark ? "Modo Claro" : "Modo Escuro";
}

function toggleDarkMode(){
    document.body.classList.toggle("dark-mode");
    localStorage.setItem("darkmode", document.body.classList.contains("dark-mode"));
    updateDarkModeUI();
}

window.onload = () => { 
    if(localStorage.getItem("darkmode")==="true") document.body.classList.add("dark-mode");
    updateDarkModeUI();
};

if (window.location.search.includes('msg=')) {
    const novaUrl = window.location.pathname;
    window.history.replaceState({}, document.title, novaUrl);
}
</script>
</body>
</html>