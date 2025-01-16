<?php
require_once '../auth.php';
require_once '../Models/itens.class.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $result = $itens->DeleteItem($id);
    
    if ($result) {
        header('Location: ../../views/itens/index.php?alert=1');
    } else {
        header('Location: ../../views/itens/index.php?alert=4');
    }
} else {
    header('Location: ../../views/itens/index.php');
} 