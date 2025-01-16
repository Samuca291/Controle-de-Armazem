<?php
require_once '../auth.php';
require_once '../Models/connect.php';
require_once '../Models/vendas.class.php';

if (isset($_POST['idItem']) && !empty($_POST['idItem'])) {
    $connect = new Connect;
    $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : uniqid();
    $_SESSION['cart'] = $cart;
    
    $vendas = new Vendas;
    $success = true;
    $messages = [];
    $valorTotal = 0;

    // Inicia transação
    $connect->SQL->begin_transaction();

    try {
        foreach ($_POST['idItem'] as $idItem => $value) {
            $quant = (int)$_POST['qtd'][$idItem];
            
            if ($quant <= 0) continue;

            // Verifica disponibilidade do item
            $verificacao = $vendas->itensVerify($idItem, $quant, $perm);
            
            if (!$verificacao || !$verificacao['status']) {
                throw new Exception("Produto {$verificacao['NomeProduto']}: {$verificacao['message']}");
            }

            // Processa a venda do item
            $vendaItem = $vendas->itensVendidos($idItem, $quant, $cart, $idUsuario, $perm);
            
            if (!$vendaItem) {
                throw new Exception("Erro ao processar venda do item {$idItem}");
            }

            $valorTotal += $vendaItem['valor'];
        }

        $connect->SQL->commit();
        
        // Limpa o carrinho e salva informações da venda
        unset($_SESSION['itens']);
        $_SESSION['notavd'] = $cart;
        $_SESSION['valorTotal'] = $valorTotal;
        
        $_SESSION['msg'] = "<div class='alert alert-success alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
            <strong>Sucesso!</strong> Venda realizada com sucesso!
        </div>";
        
        header('Location: ../../views/vendas/calculartroco.php');
        exit();

    } catch (Exception $e) {
        $connect->SQL->rollback();
        
        $_SESSION['msg'] = "<div class='alert alert-danger alert-dismissible'>
            <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
            <strong>Erro!</strong> " . $e->getMessage() . "
        </div>";
        
        header('Location: ../../views/vendas/');
        exit();
    }
} else {
    $_SESSION['msg'] = "<div class='alert alert-warning alert-dismissible'>
        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
        <strong>Atenção!</strong> Nenhum item selecionado para venda.
    </div>";
    
    header('Location: ../../views/vendas/');
    exit();
}

