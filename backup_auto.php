<?php
// Define o caminho absoluto para o backup
$diretorio_backup = 'C:\xampp\htdocs\sistema_portarias\data\backup diarios';

// Cria a pasta se ela não existir
if (!file_exists($diretorio_backup)) {
    mkdir($diretorio_backup, 0777, true);
}

// Caminho absoluto para o banco de dados original (sobe um nível se o script estiver em public/)
$origem = __DIR__ . '/data/portarias.db'; 

$nome_backup = $diretorio_backup . 'backup_' . date('Y-m-d') . '.db';

// echo "Testando backup...<br>";
// echo "Origem: $origem<br>";
// echo "Destino: $nome_backup<br>";

if (file_exists($origem)) {
    if (!file_exists($nome_backup)) {
        if (copy($origem, $nome_backup)) {
            echo "<b>Sucesso:</b> Backup criado com sucesso!<br>";
            // Lógica de limpeza de 7 dias...
        } else {
            echo "<b>Erro:</b> Falha ao copiar o arquivo. Verifique permissões na unidade S:<br>";
        }
    } else {
        echo "<b>Aviso:</b> O backup de hoje já existe.<br>";
    }
} else {
    echo "<b>Erro:</b> Arquivo original não encontrado em: $origem<br>";
}
?>