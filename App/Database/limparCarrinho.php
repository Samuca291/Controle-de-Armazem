<?php
session_start();
// Adiciona lock opcional para consistência
$lockFile = sys_get_temp_dir() . '/cart_lock_' . session_id();
$fp = fopen($lockFile, 'w+');
if (flock($fp, LOCK_EX)) {
    unset($_SESSION['itens']);
    echo json_encode(['success' => true]);
    flock($fp, LOCK_UN);
}
fclose($fp);
?>
