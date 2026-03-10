<?php
$db = new SQLite3('data/portarias.db');

$id = $_GET['id'] ?? null;

if($id){
    $db->exec("DELETE FROM portarias WHERE id=$id");
}

header("Location:index.php?msg=excluido");
exit;
?>