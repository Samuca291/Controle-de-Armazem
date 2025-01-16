<?php
session_start();

// Limpa todas as variáveis relacionadas à venda
if (isset($_SESSION['notavd'])) {
    // Limpa o carrinho atual
    unset($_SESSION['notavd']);
    unset($_SESSION['valorTotal']);
    unset($_SESSION['itens']);
    unset($_SESSION['venda_temp']);
    
    // Limpa qualquer outra variável relacionada à venda
    unset($_SESSION['cart']);
    unset($_SESSION['produtos']);
    
    // Força a limpeza do buffer de sessão
    session_write_close();
    session_start();
}

http_response_code(200);
exit;
?> 