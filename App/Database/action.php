<?php
require_once '../auth.php';
require_once '../Models/produtos.class.php';
require_once '../Models/itens.class.php';
require_once '../Models/fabricante.class.php';
require_once '../Models/representante.class.php';

if(isset($_POST['id']) && isset($_POST['current_status']) && isset($_POST['tabela'])) {
    $id = mysqli_real_escape_string($connect->SQL, $_POST['id']);
    $currentStatus = mysqli_real_escape_string($connect->SQL, $_POST['current_status']);
    $tabela = mysqli_real_escape_string($connect->SQL, $_POST['tabela']);
    
    // Novo status será o oposto do status atual
    $novoStatus = ($currentStatus == 1) ? 0 : 1;
    
    switch($tabela) {
        case 'produtos':
            // Atualiza o status do produto
            $query = "UPDATE produtos SET Ativo = '$novoStatus' WHERE CodRefProduto = '$id'";
            mysqli_query($connect->SQL, $query) or die(mysqli_error($connect->SQL));
            
            // Atualiza também o status dos itens relacionados
            $query = "UPDATE itens SET ItensAtivo = '$novoStatus' WHERE Produto_CodRefProduto = '$id'";
            mysqli_query($connect->SQL, $query) or die(mysqli_error($connect->SQL));
            break;
            
        case 'itens':
            $query = "UPDATE itens SET ItensAtivo = '$novoStatus' WHERE idItens = '$id'";
            mysqli_query($connect->SQL, $query) or die(mysqli_error($connect->SQL));
            break;
            
        case 'fabricante':
            // Atualiza o status e o Public do fabricante
            $novoPublic = ($novoStatus == 1) ? 1 : 0;
            $query = "UPDATE fabricante SET Ativo = '$novoStatus', Public = '$novoPublic' WHERE idFabricante = '$id'";
            mysqli_query($connect->SQL, $query) or die(mysqli_error($connect->SQL));
            
            // Atualiza também o status dos representantes relacionados
            $query = "UPDATE representante SET repAtivo = '$novoStatus', repPublic = '$novoPublic' WHERE Fabricante_idFabricante = '$id'";
            mysqli_query($connect->SQL, $query) or die(mysqli_error($connect->SQL));
            break;
            
        case 'representante':
            $query = "UPDATE representante SET repAtivo = '$novoStatus' WHERE idRepresentante = '$id'";
            mysqli_query($connect->SQL, $query) or die(mysqli_error($connect->SQL));
            break;
    }
    
    // Redireciona de volta para a página do fabricante
    if($tabela == 'fabricante') {
        header('Location: ../../views/fabricante/index.php');
    } else {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    exit();
} else {
    header('Location: ../../views/');
    exit();
}
?>