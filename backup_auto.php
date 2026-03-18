<?php
// Define o caminho absoluto para a nova pasta de destino
$diretorio_backup = 'S:/ATUACAO MEIO/Divisao de Apoio Administrativo/Felippe.andreata/data-sistema-portaria/';

// Cria a pasta se ela não existir
if (!file_exists($diretorio_backup)) {
    mkdir($diretorio_backup, 0777, true);
}

// Caminho do banco de dados original (relativo ao local de execução do script)
$origem = 'data/portarias.db';

// Nome do arquivo com a data atual no novo diretório
$nome_backup = $diretorio_backup . 'backup_' . date('Y-m-d') . '.db';

// Executa o backup apenas se o original existir e se ainda não foi feito hoje
if (file_exists($origem) && !file_exists($nome_backup)) {
    if (copy($origem, $nome_backup)) {
        // Limpeza: Remove backups com mais de 7 dias na nova pasta
        $arquivos = glob($diretorio_backup . "*.db");
        foreach($arquivos as $arquivo){
            if(is_file($arquivo) && time() - filemtime($arquivo) > 7 * 24 * 60 * 60) {
                unlink($arquivo);
            }
        }
    }
}
?>