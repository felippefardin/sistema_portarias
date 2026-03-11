<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Tutorial - Sistema de Portarias</title>

<style>

:root{
--primary:#2c3e50;
--secondary:#4CAF50;
--danger:#dc3545;
--bg:#f4f7f6;
--card:#ffffff;
--text:#333;
}

/* DARK MODE */
body.dark-mode{
--bg:#121212;
--card:#1e1e1e;
--text:#ffffff;
--primary:#ffffff;
}

body{
font-family:'Segoe UI',sans-serif;
line-height:1.6;
color:var(--text);
background:var(--bg);
margin:0;
padding:20px;
transition:0.3s;
}

.tutorial-container{
max-width:800px;
margin:0 auto;
background:var(--card);
padding:40px;
border-radius:12px;
box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

h1{
color:var(--primary);
border-bottom:3px solid var(--secondary);
padding-bottom:10px;
}

h2{
color:var(--primary);
margin-top:30px;
display:flex;
align-items:center;
}

.step{
background:#e8f5e9;
border-left:5px solid var(--secondary);
padding:15px;
margin:20px 0;
border-radius:4px;
}

.warning{
background:#ffebee;
border-left:5px solid var(--danger);
padding:15px;
color:#b71c1c;
font-weight:bold;
}

.info-box{
background:#e3f2fd;
border-left:5px solid #2196f3;
padding:15px;
margin:20px 0;
}

.number-rule{
background:#fff3e0;
border-left:5px solid #ff9800;
padding:15px;
margin:20px 0;
}

.btn-back{
display:inline-block;
margin-bottom:20px;
text-decoration:none;
color:var(--secondary);
font-weight:bold;
}

/* botão dark moderno */

.dark-toggle {
    position: fixed;
    top: 20px;
    right: 20px;
    cursor: pointer;
    background: var(--primary);
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 50px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    font-weight: 600;
    z-index: 1000;
}

.dark-toggle:hover {
    transform: scale(1.05);
}

body.dark-mode .dark-toggle {
    background: #f1c40f;
    color: #2c3e50;
}

body.dark-mode .step{background:#1f2d22; color:#e4e4e4;}
body.dark-mode .warning{background:#2b1c1f;color:#ffb3b3;}
body.dark-mode .info-box{background:#1b2a38; color:#e4e4e4;}
body.dark-mode .number-rule{background:#2b241a; color:#e4e4e4;}
body.dark-mode .btn-back { color: #81c784; }

</style>
</head>

<body>

<button class="dark-toggle" id="btnDark" onclick="toggleDarkMode()">
    <span id="dark-icon">🌙</span> <span id="dark-text">Modo Escuro</span>
</button>

<div class="tutorial-container">

<a href="index.php" class="btn-back">← Voltar para o Sistema</a>

<h1>Manual de Uso do Sistema</h1>

<p>Este guia ajudará você a criar e gerenciar suas portarias de forma simples e rápida.</p>

<div class="info-box">
<strong>💡 Dica de Ouro:</strong> O sistema salva tudo automaticamente. Se você preencher o formulário e clicar em salvar, a portaria já estará guardada no histórico abaixo.
</div>

<h2>1. Como Criar uma Nova Portaria</h2>

<div class="step">
<ol>
<li>No lado esquerdo da tela, preencha os dados do <strong>Procurador</strong>, <strong>OAB</strong> e os dados do <strong>Processo</strong>.</li>
<li><strong>Nº Portaria:</strong> Se você deixar em branco, o sistema colocará o próximo número disponível sozinho.</li>
<li>Clique no botão verde <strong>"Gerar e Salvar"</strong>.</li>
</ol>
</div>

<h2>2. Entendendo a Numeração</h2>

<div class="number-rule">

<strong>Como os números são gerados:</strong>

<ul>
<li><strong>Sequência Automática:</strong> Se você deixar o campo "Nº Portaria" vazio, o sistema verifica qual foi o último número usado e soma +1.</li>
<li><strong>Número Manual:</strong> Se você digitar um número manualmente, o sistema usará exatamente o que você digitou.</li>
<li><strong>Virada de Ano:</strong> Assim que o ano muda, o sistema detecta a mudança e <strong>zera a contagem automaticamente</strong>.</li>
</ul>

</div>

<h2>3. O que acontece após clicar em Salvar?</h2>

<p>O sistema faz duas coisas ao mesmo tempo:</p>

<ul>
<li><strong>Guarda os dados:</strong> A portaria aparece imediatamente na tabela de "Histórico".</li>
<li><strong>Baixa o PDF:</strong> O arquivo da portaria será baixado automaticamente para o seu computador.</li>
</ul>

<h2>4. Como Ver ou Editar uma Portaria Antiga</h2>

<p>Na tabela de histórico, você verá três botões para cada linha:</p>

<ul>
<li><strong>Ver:</strong> Abre o documento PDF novamente para conferência ou impressão.</li>
<li><strong>Editar:</strong> Caso tenha digitado algo errado, clique aqui para corrigir os dados.</li>
<li><strong>Apagar:</strong> Remove a portaria do sistema.</li>
</ul>

<div class="warning">
⚠️ ATENÇÃO: Ao apagar uma portaria, ela é removida para sempre. Não é possível recuperar portarias excluídas.
</div>

<h2>5. Dúvidas Comuns</h2>

<p><strong>O download não iniciou:</strong> Verifique se o seu navegador não bloqueou um "pop-up".</p>

<p><strong>Como imprimir?</strong> Após o download, abra o arquivo PDF e use o comando de imprimir do seu navegador.</p>

<br>

<p style="text-align:center;color:#999;font-size:0.9em;">
Sistema desenvolvido para a Procuradoria Geral do Município.
</p>

</div>

<script>

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

window.onload = function(){
    if(localStorage.getItem("darkmode") === "true"){
        document.body.classList.add("dark-mode");
    }
    updateDarkModeUI();
}

</script>

</body>
</html>