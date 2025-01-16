<?php
require_once '../auth.php';
require_once '../Models/connect.php';

if(isset($_POST['id']) && isset($_POST['current_public'])) {
    $id = mysqli_real_escape_string($connect->SQL, $_POST['id']);
    $currentPublic = mysqli_real_escape_string($connect->SQL, $_POST['current_public']);
    
    // Novo status será o oposto do status atual
    $novoPublic = ($currentPublic == 1) ? 0 : 1;
    
    // Se estiver indo para a lista de desativados (novoPublic = 0), 
    // também define ItensAtivo como 0
    if($novoPublic == 0) {
        $query = "UPDATE itens SET ItensPublic = '$novoPublic', ItensAtivo = '0' WHERE idItens = '$id'";
    } else {
        $query = "UPDATE itens SET ItensPublic = '$novoPublic' WHERE idItens = '$id'";
    }
    
    mysqli_query($connect->SQL, $query) or die(mysqli_error($connect->SQL));
}

// Redireciona de volta
header('Location: ' . $_SERVER['HTTP_REFERER']);
