<?php
require_once '../auth.php';
require_once '../Models/produtos.class.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $result = $produtos->DeleteProduto($id);
    
    if ($result) {
        header('Location: ../../views/prod/index.php?alert=1');
    } else {
        header('Location: ../../views/prod/index.php?alert=3'); // Erro ao excluir - produto tem itens vinculados
    }
} else {
    header('Location: ../../views/prod/index.php');
} 