<?php
require_once '../auth.php';
require_once '../../App/Models/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idItem = filter_var($_POST['idItem'], FILTER_SANITIZE_NUMBER_INT);
    $codigoBarras = filter_var($_POST['codigoBarras'], FILTER_SANITIZE_STRING);
    
    // Verifica se o código de barras já existe
    $connect = new Connect();
    $checkQuery = "SELECT idItens FROM itens WHERE CodigoBarras = ? AND idItens != ?";
    $checkStmt = $connect->SQL->prepare($checkQuery);
    $checkStmt->bind_param('si', $codigoBarras, $idItem);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['msg'] = "<div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <strong>Erro!</strong> Código de barras já cadastrado em outro item.
        </div>";
        header("Location: ../../views/itens/editar.php?id=$idItem");
        exit;
    }
    
    // Atualiza o item com o novo código de barras
    $query = "UPDATE itens SET 
                CodigoBarras = ?,
                // ...existing fields...
                WHERE idItens = ?";
                
    $stmt = $connect->SQL->prepare($query);
    $stmt->bind_param('s...i', 
        $codigoBarras,
        // ...existing params...,
        $idItem
    );
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = "<div class='alert alert-success alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <strong>Sucesso!</strong> Item atualizado.
        </div>";
    } else {
        $_SESSION['msg'] = "<div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            <strong>Erro!</strong> Não foi possível atualizar o item.
        </div>";
    }
    
    header("Location: ../../views/itens/");
    exit;
}
?>
