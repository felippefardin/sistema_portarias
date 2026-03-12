<?php
$db = new SQLite3('data/portarias.db');

// Cria a tabela de cadastro de procuradores se não existir
$db->exec("CREATE TABLE IF NOT EXISTS cadastro_procuradores(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT,
    oab TEXT,
    matricula TEXT,
    sexo TEXT
)");

// Lógica para carregar dados para edição
$edit_data = null;
if (isset($_GET['editar'])) {
    $id_edit = intval($_GET['editar']);
    $res = $db->query("SELECT * FROM cadastro_procuradores WHERE id=$id_edit");
    $edit_data = $res->fetchArray(SQLITE3_ASSOC);
}

// Salvar ou Atualizar Procurador
if(isset($_POST['salvar_procurador'])){
    $nome = $_POST['nome'];
    $oab = $_POST['oab'];
    $matricula = $_POST['matricula'];
    $sexo = $_POST['sexo'];

    if (isset($_POST['id_procurador']) && !empty($_POST['id_procurador'])) {
        $id = intval($_POST['id_procurador']);
        $stmt = $db->prepare("UPDATE cadastro_procuradores SET nome=:nome, oab=:oab, matricula=:matricula, sexo=:sexo WHERE id=:id");
        $stmt->bindValue(':id', $id);
    } else {
        $stmt = $db->prepare("INSERT INTO cadastro_procuradores (nome, oab, matricula, sexo) VALUES (:nome, :oab, :matricula, :sexo)");
    }

    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':oab', $oab);
    $stmt->bindValue(':matricula', $matricula);
    $stmt->bindValue(':sexo', $sexo);
    $stmt->execute();
    header("Location: procuradores.php?msg=sucesso");
    exit;
}

// Excluir Procurador
if(isset($_GET['confirmar_excluir'])){
    $db->exec("DELETE FROM cadastro_procuradores WHERE id=".intval($_GET['confirmar_excluir']));
    header("Location: procuradores.php?msg=excluido");
    exit;
}

// Definição das mensagens de Toast
$mensagem = "";
if(isset($_GET['msg'])){
    if($_GET['msg'] == 'sucesso') $mensagem = "Dados salvos com sucesso!";
    if($_GET['msg'] == 'excluido') $mensagem = "Procurador removido com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Procuradores - Sistema de Portarias</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #4CAF50;
            --accent-color: #ff5722;
            --bg-body: #f0f2f5;
            --bg-card: #ffffff;
            --text-main: #333;
            --border-radius: 12px;
            --shadow: 0 8px 30px rgba(0,0,0,0.05);
        }

        body.dark-mode {
            --bg-body: #121212;
            --bg-card: #1e1e1e;
            --text-main: #f5f5f5;
            --primary-color: #ffffff;
            --shadow: 0 8px 30px rgba(0,0,0,0.4);
        }

        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            margin: 0;
            padding: 20px;
            color: var(--text-main);
            transition: 0.3s;
        }

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-card);
            padding: 15px 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            max-width: 850px;
            margin-left: auto;
            margin-right: auto;
        }

        .card {
            background: var(--bg-card);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            max-width: 850px;
            margin: auto;
        }

        h2 {
            color: var(--primary-color);
            margin: 0 0 25px 0;
            font-size: 1.5rem;
            border-left: 6px solid var(--secondary-color);
            padding-left: 15px;
        }

        .btn-voltar, .btn-tutorial {
            color: white !important;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-voltar { background: #3498db; }
        .btn-voltar:hover { background: #2980b9; transform: translateY(-2px); }
        .btn-tutorial { background: var(--accent-color); }

        .dark-toggle {
            cursor: pointer; background: var(--primary-color); color: white; border: none; padding: 10px 18px; border-radius: 50px; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: 0.4s; font-weight: 600;
        }
        body.dark-mode .dark-toggle { background: #f1c40f; color: #2c3e50; }

        form label { display: block; font-weight: 600; margin: 15px 0 5px 0; font-size: 0.85em; color: #666; }
        body.dark-mode form label { color: #aaa; }

        input, select {
            width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #ddd; box-sizing: border-box; background: var(--bg-card); color: var(--text-main);
        }

        .btn-submit {
            background: var(--secondary-color); color: white; font-weight: bold; cursor: pointer; border: none; padding: 15px; border-radius: 8px; width: 100%; margin-top: 10px; font-size: 1rem;
        }
        .btn-cancel-edit { background: #95a5a6; color: white; text-decoration: none; display: block; text-align: center; padding: 10px; border-radius: 8px; margin-top: 10px; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        th { background: rgba(0,0,0,0.02); padding: 15px; text-align: left; border-bottom: 2px solid #eee; font-size: 0.9em; }
        td { padding: 15px; border-bottom: 1px solid rgba(0,0,0,0.03); }

        .action-links { display: flex; gap: 15px; font-weight: bold; }
        .edit-btn { color: #3498db; text-decoration: none; }
        .del-btn { color: #e74c3c; text-decoration: none; cursor: pointer; }

        /* TOAST MENSAGEM */
        #toast { position: fixed; top: 20px; right: 20px; background: var(--secondary-color); color: white; padding: 15px 25px; border-radius: 10px; z-index: 3000; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; }

        /* MODAL MODERNO */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center; z-index: 2000; opacity: 0; transition: 0.3s; }
        .modal.show { display: flex; opacity: 1; }
        .modal-content { background: var(--bg-card); padding: 40px; border-radius: 20px; text-align: center; width: 90%; max-width: 400px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); transform: scale(0.8); transition: 0.3s; }
        .modal.show .modal-content { transform: scale(1); }
        .modal-icon { font-size: 50px; color: #e74c3c; margin-bottom: 20px; }
        .modal-buttons { display: flex; gap: 15px; margin-top: 30px; }
        .modal-buttons button, .modal-buttons a { flex: 1; padding: 12px; border-radius: 10px; border: none; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-confirm-del { background: #e74c3c; color: white; }
        .btn-cancel-del { background: #eee; color: #333; }
    </style>
</head>
<body>

    <?php if($mensagem): ?>
        <div id="toast" style="display:block;"><?php echo $mensagem; ?></div>
        <script>setTimeout(()=>{document.getElementById('toast').style.display='none';},3000);</script>
    <?php endif; ?>

    <header class="header-actions">
        <div style="display:flex; gap:10px;">
            <a href="index.php" class="btn-voltar">← Portarias</a>
            <a href="tutorial.php" class="btn-tutorial">📖 Tutorial</a>
        </div>
        <button class="dark-toggle" id="btnDark" onclick="toggleDarkMode()">
            <span id="dark-icon">🌙</span> <span id="dark-text">Modo Escuro</span>
        </button>
    </header>

    <div class="card">
        <h2><?php echo $edit_data ? 'Editar Procurador' : 'Cadastrar Procurador'; ?></h2>
        <form method="post">
            <input type="hidden" name="id_procurador" value="<?php echo $edit_data['id'] ?? ''; ?>">

            <label>Nome Completo (Dra. / Dr.)</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($edit_data['nome'] ?? ''); ?>" placeholder="Ex: Dra. Carla Silva" required>
            
            <div style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label>OAB/ES</label>
                    <input type="text" name="oab" value="<?php echo htmlspecialchars($edit_data['oab'] ?? ''); ?>" placeholder="Ex: 4444" required>
                </div>
                <div style="flex: 1;">
                    <label>Matrícula</label>
                    <input type="text" name="matricula" value="<?php echo htmlspecialchars($edit_data['matricula'] ?? ''); ?>" placeholder="Ex: 123456" required>
                </div>
                <div style="flex: 1;">
                    <label>Sexo</label>
                    <select name="sexo" required>
                        <option value="M" <?php echo (isset($edit_data['sexo']) && $edit_data['sexo'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="F" <?php echo (isset($edit_data['sexo']) && $edit_data['sexo'] == 'F') ? 'selected' : ''; ?>>Feminino</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" name="salvar_procurador" class="btn-submit">
                <?php echo $edit_data ? 'Atualizar Cadastro' : 'Salvar Cadastro'; ?>
            </button>
            
            <?php if($edit_data): ?>
                <a href="procuradores.php" class="btn-cancel-edit">Cancelar Edição</a>
            <?php endif; ?>
        </form>

        <hr style="margin: 40px 0; border: 0; border-top: 1px solid rgba(0,0,0,0.1);">

        <h2>Procuradores Cadastrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>OAB</th>
                    <th>Matrícula</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $db->query("SELECT * FROM cadastro_procuradores ORDER BY nome ASC");
                while($row = $res->fetchArray()){
                    echo "<tr>
                        <td>{$row['nome']}</td>
                        <td>{$row['oab']}</td>
                        <td>{$row['matricula']}</td>
                        <td class='action-links'>
                            <a href='?editar={$row['id']}' class='edit-btn'>Editar</a>
                            <span class='del-btn' onclick='abrirModalExcluir({$row['id']})'>Apagar</span>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="modalExcluir" class="modal">
        <div class="modal-content">
            <div class="modal-icon">⚠️</div>
            <h3>Tem certeza?</h3>
            <p>Este cadastro de procurador será removido permanentemente.</p>
            <div class="modal-buttons">
                <button class="btn-cancel-del" onclick="fecharModalExcluir()">Cancelar</button>
                <a id="linkConfirmarExcluir" href="#" class="btn-confirm-del">Sim, Apagar</a>
            </div>
        </div>
    </div>

    <script>
        function abrirModalExcluir(id) {
            document.getElementById('linkConfirmarExcluir').href = '?confirmar_excluir=' + id;
            const modal = document.getElementById('modalExcluir');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function fecharModalExcluir() {
            const modal = document.getElementById('modalExcluir');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        }

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
            if(localStorage.getItem("darkmode") === "true") document.body.classList.add("dark-mode");
            updateDarkModeUI();
        };
    </script>
</body>
</html>