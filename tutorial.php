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
<title>Tutorial do Sistema de Portarias</title>
<style>
body { font-family:'Segoe UI', Tahoma, Geneva, Verdana,sans-serif; background:#f4f6f8; margin:0; padding:0; }
.container { max-width:900px; margin:40px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.1); }
h1,h2,h3 { color:#333; }
h1 { text-align:center; margin-bottom:30px; }
h2 { border-bottom:2px solid #4CAF50; padding-bottom:8px; margin-top:30px; }
p, li { font-size:1em; line-height:1.6; }
ul { margin-left:20px; }
a { color:#4CAF50; text-decoration:none; }
a:hover { text-decoration:underline; }
button { background:#4CAF50; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; transition:0.3s; margin-top:20px; }
button:hover { background:#45a049; }
</style>
</head>
<body>

<div class="container">
<h1>Tutorial do Sistema de Portarias</h1>

<h2>1. Acesso ao sistema</h2>
<ul>
<li>Acesse o endereço do sistema no navegador.</li>
<li>Você verá o formulário de cadastro e o histórico de portarias.</li>
</ul>

<h2>2. Cadastrando uma nova portaria</h2>
<ul>
<li>Preencha os campos do formulário:</li>
<ul>
    <li><strong>Número da Portaria (opcional):</strong> você pode digitar manualmente o número que deseja atribuir à portaria.  
    <strong>Observação:</strong> se você digitar um número manualmente, o sistema continuará a sequência automática a partir desse número.  
    Todo início de ano, os números são reiniciados e a contagem volta do 1.</li>
    <li>Procurador, Sexo, OAB, Processo, Autor, Vara e Data</li>
</ul>
<li>Clique em <strong>Salvar Portaria</strong>.</li>
<li>O PDF será gerado e aberto em nova aba para visualização e download.</li>
<li>Uma mensagem de confirmação aparecerá no topo da tela.</li>
</ul>

<h2>3. Editando uma portaria</h2>
<ul>
<li>No histórico, clique em <strong>Editar</strong> na portaria desejada.</li>
<li>Altere os campos que desejar e clique em <strong>Salvar Alterações</strong>.</li>
<li>A mensagem <strong>Portaria editada com sucesso!</strong> será exibida.</li>
</ul>

<h2>4. Apagando portarias</h2>
<ul>
<li><strong>Apagar individual:</strong> Clique em <strong>Apagar</strong> na linha desejada e confirme no modal.</li>
<li><strong>Apagar múltiplas:</strong> Selecione as portarias desejadas e clique em <strong>Apagar Selecionados</strong>.</li>
<li>Após a exclusão, a mensagem <strong>Portaria excluída com sucesso!</strong> aparecerá.</li>
</ul>

<h2>5. Visualizar e baixar PDF</h2>
<ul>
<li>Na coluna Ações, clique em <strong>Visualizar</strong> para abrir o PDF.</li>
<li>Clique em <strong>Download</strong> para salvar o PDF no seu computador.</li>
</ul>

<h2>6. Buscar portarias</h2>
<ul>
<li>Use o campo de busca para filtrar portarias por Processo, Procurador ou Autor.</li>
</ul>

<h2>7. Mensagens de sucesso</h2>
<ul>
<li>As mensagens aparecem no canto superior direito e desaparecem após alguns segundos:</li>
<ul>
<li>Portaria cadastrada com sucesso!</li>
<li>Portaria editada com sucesso!</li>
<li>Portaria excluída com sucesso!</li>
</ul>
</ul>

<h2>8. Dicas de uso</h2>
<ul>
<li>Verifique os dados antes de salvar ou editar.</li>
<li>Use a busca para localizar portarias antigas rapidamente.</li>
<li>Use a seleção múltipla com cuidado.</li>
<li>Mantenha backup do banco <code>portarias.db</code> periodicamente.</li>
</ul>

<a href="index.php"><button>Voltar ao Sistema</button></a>

</div>
</body>
</html>