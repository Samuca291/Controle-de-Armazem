<?php
require_once '../auth.php';
header('Content-Type: application/json');

if (isset($_POST['action']) && $_POST['action'] == "removerItem") {
    $idProduto = $_POST['idItem'];
    
    try {
        // Remove o item da sessão
        unset($_SESSION['itens'][$idProduto]);
        
        // Calcula novo total
        $totalCarrinho = 0;
        foreach ($_SESSION['itens'] as $item) {
            $totalCarrinho += $item['valor'];
        }
        
        // Prepara resposta com dados atualizados
        echo json_encode([
            'success' => true,
            'message' => 'Item removido com sucesso',
            'total' => number_format($totalCarrinho, 2, ',', '.'),
            'pkCount' => count($_SESSION['itens']),
            'empty' => count($_SESSION['itens']) === 0
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao remover item: ' . $e->getMessage()
        ]);
    }
    exit;
}
?>
