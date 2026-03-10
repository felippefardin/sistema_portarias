<?php
$db = new SQLite3('data/portarias.db');

if(isset($_POST['selecionados'])){
    $ids = $_POST['selecionados'];
    $ids_str = implode(",", array_map('intval', $ids));
    $db->exec("DELETE FROM portarias WHERE id IN ($ids_str)");
}

header("Location:index.php?msg=excluido");
exit;
?>