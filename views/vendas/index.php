<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/vendas.class.php';
require_once '../../App/Models/cliente.class.php';

// Processar atualização de quantidade via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'updateQuantity') {
    $idItem = $_POST['idItem'];
    $novaQtde = $_POST['quantidade'];
    
    // Verificar estoque
    $connect = new Connect();
    $query = "SELECT i.*, p.NomeProduto 
              FROM itens i 
              INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
              WHERE i.idItens = ?";
    $stmt = $connect->SQL->prepare($query);
    $stmt->bind_param('i', $idItem);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    $estoqueDisponivel = $item['QuantItens'] - $item['QuantItensVend'];
    
    if ($novaQtde > $estoqueDisponivel) {
        echo json_encode([
            'success' => false,
            'message' => "Quantidade solicitada excede o estoque disponível ({$estoqueDisponivel})"
        ]);
        exit;
    }
    
    // Atualizar carrinho
    if (isset($_SESSION['itens'][$idItem])) {
        $_SESSION['itens'][$idItem]['qtde'] = $novaQtde;
        $_SESSION['itens'][$idItem]['valor'] = $item['ValVendItens'] * $novaQtde;
        
        // Calcular novo total
        $totalCarrinho = 0;
        foreach ($_SESSION['itens'] as $prod) {
            $totalCarrinho += $prod['valor'];
        }
        
        echo json_encode([
            'success' => true,
            'novoValorItem' => number_format($_SESSION['itens'][$idItem]['valor'], 2, ',', '.'),
            'totalCarrinho' => number_format($totalCarrinho, 2, ',', '.')
        ]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Item não encontrado no carrinho']);
    exit;
}

echo $head;
echo $header;
echo $aside;

// Conexão com o banco de dados e consulta ordenada por nome
$connect = new Connect();
$query = "SELECT 
    i.idItens,
    i.ValVendItens,
    i.Image,
    i.QuantItens,
    i.QuantItensVend,
    p.NomeProduto,
    p.CodRefProduto,
    (i.QuantItens - i.QuantItensVend) as EstoqueDisponivel 
FROM itens i 
INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
WHERE i.ItensAtivo = 1 
    AND (i.QuantItens - i.QuantItensVend) >= 0
ORDER BY EstoqueDisponivel DESC, p.NomeProduto ASC";
$result = $connect->SQL->query($query);
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Nova Venda
            <small>Selecione os produtos para venda</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Nova Venda</li>
        </ol>
    </section>

    <section class="content">
        <?php require '../../layout/alert.php'; ?>
        
        <div class="row">
            <!-- Coluna dos produtos -->
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-shopping-cart"></i> Produtos Disponíveis
                        </h3>
                        
                        <!-- Barra de pesquisa -->
                        <div class="box-tools">
                            <div class="search-container">
                                <div class="search-box">
                                    <input type="text" id="productSearch" class="form-control" placeholder="Pesquisar produto...">
                                    <div class="input-group-btn">
                                        <button class="btn btn-default"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                                <div class="search-box">
                                    <input type="text" id="barcodeInput" class="form-control" placeholder="Código de barras..." autofocus>
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary"><i class="fa fa-barcode"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="box-body">
                        <!-- Filtros rápidos -->
                        <div class="product-filters mb-3">
                            <button class="btn btn-default filter-btn active" data-filter="all">
                                Todos
                            </button>
                            <button class="btn btn-default filter-btn" data-filter="in-stock">
                                Em Estoque
                            </button>
                            <button class="btn btn-default filter-btn" data-filter="low-stock">
                                Estoque Baixo
                            </button>
                        </div>

                        <!-- Grid de produtos -->
                        <div class="row" id="products-grid">
                            <?php
                            while ($product = $result->fetch_assoc()) {
                                $estoqueDisponivel = $product['QuantItens'] - $product['QuantItensVend'];
                                $hasImage = !empty($product['Image']) && file_exists("../" . $product['Image']);
                                $statusClass = $estoqueDisponivel > 0 ? 'in-stock' : 'out-of-stock';
                                
                                echo "
                                <div class='col-lg-4 col-md-6 col-sm-6 product-item' data-stock='$estoqueDisponivel'>
                                    <div class='product-card $statusClass'>
                                        <div class='product-header'>
                                            " . ($hasImage ? "
                                            <div class='product-image-container'>
                                                <img src='../{$product['Image']}' alt='{$product['NomeProduto']}' class='product-image'>
                                            </div>" : "
                                            <div class='product-no-image'>
                                                <i class='fa fa-cube'></i>
                                            </div>") . "
                                            <div class='stock-badge " . ($estoqueDisponivel > 0 ? 'bg-success' : 'bg-danger') . "'>
                                                <span>" . ($estoqueDisponivel > 0 ? "Em estoque: $estoqueDisponivel" : "Sem estoque") . "</span>
                                            </div>
                                        </div>
                                        
                                        <div class='product-info'>
                                            <h4 class='product-name' title='{$product['NomeProduto']}'>{$product['NomeProduto']}</h4>
                                            <p class='product-price'>R$ " . number_format($product['ValVendItens'], 2, ',', '.') . "</p>
                                            
                                            <div class='product-actions'>
                                                <div class='quantity-control'>
                                                    <button type='button' class='quantity-btn minus' 
                                                            data-action='decrease'
                                                            " . ($estoqueDisponivel <= 0 ? 'disabled' : '') . ">
                                                        <i class='fa fa-minus'></i>
                                                    </button>
                                                    <input type='number' 
                                                           class='quantity-input' 
                                                           value='1' 
                                                           min='1' 
                                                           max='$estoqueDisponivel'
                                                           " . ($estoqueDisponivel <= 0 ? 'disabled' : '') . ">
                                                    <button type='button' class='quantity-btn plus'
                                                            data-action='increase'
                                                            " . ($estoqueDisponivel <= 0 ? 'disabled' : '') . ">
                                                        <i class='fa fa-plus'></i>
                                                    </button>
                                                </div>
                                                
                                                <button class='btn " . ($estoqueDisponivel > 0 ? 'btn-success' : 'btn-default') . " btn-block btn-add-cart' 
                                                        data-id='{$product['idItens']}' 
                                                        " . ($estoqueDisponivel <= 0 ? 'disabled' : '') . ">
                                                    <i class='fa fa-cart-plus'></i> " . ($estoqueDisponivel > 0 ? 'Adicionar' : 'Indisponível') . "
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna do carrinho -->
            <div class="col-md-4">
                <div class="box box-info cart-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-shopping-basket"></i> Carrinho</h3>
                    </div>
                    
                    <div class="box-body">
                        <form action="../../App/Database/insertVendas.php" method="POST" id="cart-form">
                            <div class="cart-items">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="cart-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Produto</th>
                                                <th>Qtde</th>
                                                <th>Valor</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="listable">
                                            <?php
                                            require_once '../../views/templates/cart_template.php';
                                            $cartData = generateCartHtml($_SESSION['itens'] ?? []);
                                            echo $cartData['html'];
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="cart-footer">
                                <!-- Footer do carrinho -->
                                <div class="total-value">
                                    Total: <span>R$ <?php echo number_format($cartData['total'], 2, ',', '.'); ?></span>
                                </div>
                                
                                <!-- Botões de ação -->
                                <?php include '../../views/templates/cart_actions.php'; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Estilos para os cards de produtos */
.product-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.product-header {
    position: relative;
    padding-top: 75%;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
    overflow: hidden;
}

.product-image-container, .product-no-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

.product-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    color: white;
}

.bg-success {
    background-color: #28a745;
}

.bg-danger {
    background-color: #dc3545;
}

/* Estilos para o carrinho */
.cart-items {
    max-height: calc(100vh - 400px);
    overflow-y: auto;
}

.cart-footer {
    padding: 15px;
    border-top: 1px solid #eee;
}

.total-value {
    font-size: 20px;
    font-weight: bold;
    text-align: right;
    margin-bottom: 20px;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Responsividade */
@media (max-width: 991px) {
    .col-md-8, .col-md-4 {
        width: 100%;
    }
    
    .product-card {
        height: auto;
    }
}

@media (max-width: 767px) {
    .box-tools {
        width: 100%;
        margin-top: 10px;
    }
    
    .input-group {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .product-item {
        width: 100%;
    }
    
    .cart-actions button {
        font-size: 14px;
    }
}

/* Estilos gerais da página */
.content-wrapper {
    background-color: #f4f6f9;
    min-height: 100vh;
    padding-bottom: 30px;
}

.content-header {
    padding: 25px 15px;
    background: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.content-header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
}

.content-header small {
    font-size: 14px;
    color: #7f8c8d;
    margin-left: 5px;
}

/* Estilos dos filtros */
.product-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 15px;
    border-radius: 20px;
    font-weight: 500;
    transition: all 0.3s ease;
    background: white;
    border: 1px solid #ddd;
}

.filter-btn:hover {
    background: #f8f9fa;
    transform: translateY(-1px);
}

.filter-btn.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

/* Melhorias nos cards de produtos */
.product-card {
    border: none;
    background: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.product-name {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin: 15px 0 10px;
    line-height: 1.4;
    height: 44px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-price {
    font-size: 18px;
    color: #27ae60;
    font-weight: 700;
    margin: 0 0 15px;
}

.quantity-control {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    gap: 8px;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    transition: all 0.2s ease;
}

.product-quantity {
    width: 60px;
    text-align: center;
    font-weight: 600;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px;
}

.product-quantity::-webkit-inner-spin-button,
.product-quantity::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.product-quantity {
    -moz-appearance: textfield;
}

.btn-add-cart {
    padding: 10px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.btn-add-cart:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Melhorias no carrinho */
.box-info {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.cart-items {
    background: white;
    border-radius: 8px;
    margin-bottom: 15px;
}

.table > thead > tr > th {
    border-bottom: 2px solid #3498db;
    color: #2c3e50;
    font-weight: 600;
    padding: 12px;
}

.cart-quantity-control {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.cart-quantity {
    font-weight: 600;
    min-width: 30px;
    text-align: center;
}

.total-value {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.total-value span {
    color: #27ae60;
    font-size: 24px;
}

/* Animações */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.product-item {
    animation: fadeIn 0.5s ease-out;
}

/* Melhorias na responsividade */
@media (max-width: 1200px) {
    .col-lg-4 {
        width: 50%;
    }
}

@media (max-width: 991px) {
    .content-wrapper {
        padding: 15px;
    }
    
    .cart-items {
        max-height: 400px;
    }
}

@media (max-width: 767px) {
    .content-header {
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .product-filters {
        justify-content: center;
    }
    
    .filter-btn {
        font-size: 13px;
        padding: 6px 12px;
    }
    
    .product-name {
        font-size: 14px;
        height: 40px;
    }
    
    .product-price {
        font-size: 16px;
    }
    
    .quantity-btn {
        width: 25px;
        height: 25px;
    }
    
    .cart-actions button {
        padding: 10px;
    }
}

@media (max-width: 576px) {
    .sales-dashboard .col-lg-3 {
        width: 100%;
    }
    
    .info-box {
        min-height: 80px;
    }
    
    .info-box-icon {
        width: 80px;
        font-size: 35px;
    }
}

.product-info {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.product-name {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin: 15px 0;
    line-height: 1.4;
    height: 44px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    width: 100%;
}

.product-price {
    font-size: 24px;
    color: #27ae60;
    font-weight: 700;
    margin: 0 0 20px;
    padding: 10px 20px;
    background: #f8f9fa;
    border-radius: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    display: inline-block;
    transition: all 0.3s ease;
}

.product-card:hover .product-price {
    transform: scale(1.1);
    background: #e8f6e9;
    color: #219a52;
}

.product-actions {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.saved-carts-indicator {
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    padding: 8px;
    border-radius: 50%;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    z-index: 10;
}

.saved-carts-indicator:hover {
    transform: translateX(-50%) translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.saved-carts-link {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    color: #3498db;
    text-decoration: none;
}

.saved-carts-link i {
    font-size: 20px;
}

.saved-carts-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #e74c3c;
    color: white;
    font-size: 12px;
    font-weight: bold;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
}

/* Animação de pulso para chamar atenção */
@keyframes pulse-badge {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.saved-carts-count {
    animation: pulse-badge 2s infinite;
}

/* Tooltip personalizado */
.saved-carts-indicator[data-tooltip]:before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    margin-bottom: 10px;
    padding: 8px 12px;
    background: rgba(0,0,0,0.8);
    color: white;
    font-size: 12px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.saved-carts-indicator[data-tooltip]:after {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: rgba(0,0,0,0.8);
    margin-bottom: -2px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.saved-carts-indicator[data-tooltip]:hover:before,
.saved-carts-indicator[data-tooltip]:hover:after {
    opacity: 1;
    visibility: visible;
}

/* Responsividade */
@media (max-width: 768px) {
    .saved-carts-indicator {
        bottom: -24px;
    }
}

/* Remove as setinhas do input number */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

.cart-quantity-control {
    display: flex;
    align-items: center;
    gap: 5px;
}

.cart-quantity-input {
    width: 60px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px;
}

.cart-quantity-btn {
    padding: 2px 6px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #fff;
}

.search-container {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    flex: 1;
    min-width: 200px;
}

.search-box .form-control {
    border-right: 0;
}

.search-box .input-group-btn {
    width: auto;
}

.search-box .btn {
    border-left: 0;
    height: 34px;
}

@media (max-width: 768px) {
    .search-container {
        flex-direction: column;
    }
    
    .search-box {
        width: 100%;
    }
}

/* Estilo para o alert temporário */
.floating-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    padding: 15px 25px;
    border-radius: 4px;
    color: #fff;
    font-weight: 500;
    transform: translateX(150%);
    transition: transform 0.3s ease-in-out;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.floating-alert.error {
    background-color: #dc3545;
}

.floating-alert.warning {
    background-color: #ffc107;
    color: #000;
}

.floating-alert.success {
    background-color: #28a745;
}

.floating-alert.show {
    transform: translateX(0);
}

/* Estilos para autocomplete */
.ui-autocomplete {
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 9999 !important;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.ui-autocomplete .ui-menu-item {
    padding: 8px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.ui-autocomplete .ui-menu-item:last-child {
    border-bottom: none;
}

.ui-autocomplete .ui-menu-item:hover {
    background: #f8f9fa;
}

.product-item.highlight {
    animation: highlight 2s;
}

@keyframes highlight {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Estilo para item selecionado */
.ui-state-active,
.ui-widget-content .ui-state-active {
    border: none;
    background: #3498db !important;
    color: #fff !important;
    margin: 0 !important;
}

/* Estilos profissionais para o carrinho */
.box-info.cart-box {
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
}

.cart-box .box-header {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border-radius: 8px 8px 0 0;
    padding: 15px;
    border-bottom: none;
}

.cart-box .box-header .box-title {
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.cart-box .box-header .box-title i {
    font-size: 20px;
}

.cart-items {
    padding: 15px;
    background: #fff;
    border-radius: 0 0 8px 8px;
}

#cart-table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0 8px;
}

#cart-table thead th {
    background: #f8f9fa;
    border: none;
    padding: 12px;
    font-weight: 600;
    color: #2c3e50;
}

#cart-table tbody tr {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border-radius: 8px;
    transition: all 0.3s ease;
}

#cart-table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#cart-table tbody td {
    padding: 15px;
    border: none;
    vertical-align: middle;
}

.cart-quantity-control {
    display: inline-flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 20px;
    padding: 3px;
    border: 1px solid #dee2e6;
}

.cart-quantity-btn {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: white;
    color: #2c3e50;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.cart-quantity-btn:hover:not(:disabled) {
    background: #e9ecef;
    transform: scale(1.1);
}

.cart-quantity-input {
    width: 50px !important;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 600;
    color: #2c3e50;
    padding: 0 8px;
}

.cart-footer {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 0 0 8px 8px;
    border-top: 2px dashed #dee2e6;
}

.total-value {
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.total-value span {
    display: block;
    font-size: 28px;
    color: #2c3e50;
    font-weight: 700;
    margin-top: 5px;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.cart-actions button,
.cart-actions a {
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.cart-actions button i,
.cart-actions a i {
    margin-right: 8px;
}

.cart-actions .btn-success {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border: none;
    box-shadow: 0 4px 15px rgba(46,204,113,0.2);
}

.cart-actions .btn-success:hover {
    background: linear-gradient(135deg, #27ae60, #219a52);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(46,204,113,0.3);
}

.cart-actions .btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
    box-shadow: 0 4px 15px rgba(52,152,219,0.2);
}

.cart-actions .btn-primary:hover {
    background: linear-gradient(135deg, #2980b9, #2472a4);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(52,152,219,0.3);
}

.cart-actions .btn-info {
    background: linear-gradient(135deg, #00cec9, #00b5b1);
    border: none;
    box-shadow: 0 4px 15px rgba(0,206,201,0.2);
}

.cart-actions .btn-info:hover {
    background: linear-gradient(135deg, #00b5b1, #009c98);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,206,201,0.3);
}

.cart-actions .btn-danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border: none;
    box-shadow: 0 4px 15px rgba(231,76,60,0.2);
}

.cart-actions .btn-danger:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(231,76,60,0.3);
}

.cart-actions .btn-default[disabled] {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.8;
}

/* Animação de carregamento do carrinho */
.cart-loading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.cart-loading.active {
    opacity: 1;
    visibility: visible;
}

.cart-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilo para carrinho vazio */
.empty-cart {
    text-align: center;
    padding: 30px 20px;
    color: #6c757d;
}

.empty-cart i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-cart p {
    font-size: 16px;
    margin: 0;
}

/* Badges e indicadores */
.item-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
}

.badge-stock {
    background: #e8f6e9;
    color: #27ae60;
}

.badge-low {
    background: #fff3cd;
    color: #856404;
}

/* Tooltips personalizados */
[data-tooltip] {
    position: relative;
}

[data-tooltip]:before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 12px;
    background: rgba(0,0,0,0.8);
    color: white;
    font-size: 12px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

[data-tooltip]:hover:before {
    opacity: 1;
    visibility: visible;
    bottom: calc(100% + 10px);
}

/* Scrollbar personalizada para o carrinho */
.cart-items::-webkit-scrollbar {
    width: 6px;
}

.cart-items::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

.cart-items::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

.cart-items::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

/* Estilos para o modal de salvar carrinho */
#modalSalvarCarrinho .modal-content {
    border-radius: 8px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
}

#modalSalvarCarrinho .modal-header {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border-radius: 8px 8px 0 0;
    padding: 15px 20px;
    border: none;
}

#modalSalvarCarrinho .modal-title {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

#modalSalvarCarrinho .modal-title:before {
    content: '\f0c7';
    font-family: 'FontAwesome';
    font-size: 20px;
}

#modalSalvarCarrinho .close {
    color: white;
    opacity: 0.8;
    text-shadow: none;
    transition: all 0.3s ease;
}

#modalSalvarCarrinho .close:hover {
    opacity: 1;
    transform: scale(1.1);
}

#modalSalvarCarrinho .modal-body {
    padding: 20px;
}

#modalSalvarCarrinho .form-group label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

#modalSalvarCarrinho .form-control {
    border-radius: 6px;
    border: 2px solid #e2e8f0;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

#modalSalvarCarrinho .form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
}

#modalSalvarCarrinho .text-muted {
    font-size: 13px;
    margin-top: 8px;
    color: #718096;
}

#modalSalvarCarrinho .modal-footer {
    background: #f8f9fa;
    border-top: 1px solid #e2e8f0;
    border-radius: 0 0 8px 8px;
    padding: 15px 20px;
}

#modalSalvarCarrinho .btn {
    padding: 8px 20px;
    font-weight: 600;
    border-radius: 6px;
    transition: all 0.3s ease;
}

#modalSalvarCarrinho .btn-default {
    background: #fff;
    border: 2px solid #e2e8f0;
    color: #4a5568;
}

#modalSalvarCarrinho .btn-default:hover {
    background: #f8f9fa;
    border-color: #cbd5e0;
}

#modalSalvarCarrinho .btn-success {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border: none;
    box-shadow: 0 4px 15px rgba(46,204,113,0.2);
}

#modalSalvarCarrinho .btn-success:hover {
    background: linear-gradient(135deg, #27ae60, #219a52);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(46,204,113,0.3);
}

/* Animações para o carrinho */
@keyframes addToCart {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.cart-item-added {
    animation: addToCart 0.5s ease;
}

/* Efeitos de hover melhorados */
.cart-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.total-value:hover span {
    transform: scale(1.05);
    color: #219a52;
}

/* Indicador de progresso do carrinho */
.cart-progress {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    margin: 15px 0;
    overflow: hidden;
}

.cart-progress-bar {
    height: 100%;
    background: linear-gradient(to right, #3498db, #2ecc71);
    transition: width 0.3s ease;
}

/* Melhorias visuais para o carrinho vazio */
.empty-cart {
    padding: 40px 20px;
    text-align: center;
    color: #a0aec0;
}

.empty-cart i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-cart p {
    font-size: 16px;
    margin: 0;
    line-height: 1.6;
}

.empty-cart .suggestion {
    font-size: 14px;
    margin-top: 10px;
    color: #718096;
}

.btn-remove-item {
    width: 28px;
    height: 28px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #dc3545;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-remove-item:hover {
    background: #dc3545;
    color: white;
    transform: rotate(90deg);
}

@keyframes quantity-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.quantity-pulse {
    animation: quantity-pulse 0.3s ease-in-out;
}

.quantity-control {
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border-radius: 25px;
    padding: 5px;
    gap: 8px;
    transition: all 0.3s ease;
}

.quantity-btn {
    width: 35px;
    height: 35px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: white;
    color: #3498db;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.quantity-btn:hover:not(:disabled) {
    background: #3498db;
    color: white;
    transform: scale(1.1);
}

.quantity-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #e9ecef;
}

.quantity-input {
    width: 60px;
    text-align: center;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px;
    font-weight: 600;
    color: #2c3e50;
    background: white;
}

.quantity-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}
</style>

<script>
// Funções principais
const cart = {
    // Função central para atualizar interface do carrinho
    updateInterface: function(data) {
        if (data.success) {
            document.getElementById('listable').innerHTML = data.cartHtml;
            document.querySelector('.total-value span').textContent = `R$ ${data.total}`;
            this.updateActions(data.pkCount);
            showTemporaryAlert(data.message, 'success');
        } else {
            showTemporaryAlert(data.message, 'error');
        }
    },

    // Atualiza botões de ação do carrinho
    updateActions: function(pkCount) {
        const cartActions = document.querySelector('.cart-actions');
        if (!cartActions) return;
        
        cartActions.innerHTML = pkCount > 0 ? `
            <button type="submit" class="btn btn-success btn-lg btn-block" name="comprar">
                <i class="fa fa-check"></i> Finalizar Compra
            </button>
            <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalSalvarCarrinho">
                <i class="fa fa-save"></i> Guardar Carrinho
            </button>
            <a href="carrinhos_salvos.php" class="btn btn-info btn-block">
                <i class="fa fa-list"></i> Ver Carrinhos Salvos
            </a>
            <button type="button" class="btn btn-danger btn-block" id="clear-cart">
                <i class="fa fa-trash"></i> Limpar Carrinho
            </button>` : `
            <button type="button" class="btn btn-default btn-lg btn-block" disabled>
                <i class="fa fa-shopping-cart"></i> Carrinho Vazio
            </button>`;
            
        this.initializeButtons();
    },

    // Adiciona item ao carrinho
    addItem: function(params) {
        showLoading();
        
        return fetch('../../App/Database/carrinho.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(params)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Animação do item adicionado
                const newItem = document.querySelector(`tr[data-id="${params.idItem}"]`);
                if (newItem) {
                    newItem.classList.add('cart-item-added');
                    setTimeout(() => newItem.classList.remove('cart-item-added'), 500);
                }
                
                // Atualiza a barra de progresso
                const progressBar = document.querySelector('.cart-progress-bar');
                if (progressBar) {
                    progressBar.style.width = '100%';
                }
            }
            this.updateInterface(data);
            if (data.success && params.prodSubmit === 'barcode') {
                const barcodeInput = document.getElementById('barcodeInput');
                barcodeInput.value = '';
                barcodeInput.focus();
            }
            return data;
        })
        .catch(error => {
            console.error('Erro:', error);
            showTemporaryAlert('Erro ao processar item', 'error');
            throw error;
        })
        .finally(() => hideLoading());
    },

    // Inicializa os event listeners dos botões
    initializeButtons: function() {
        // Limpar carrinho
        const clearCart = document.getElementById('clear-cart');
        if (clearCart) {
            clearCart.addEventListener('click', () => this.clearCart());
        }
    },

    clearCart: function() {
        Swal.fire({
            title: 'Limpar carrinho?',
            text: "Todos os itens serão removidos",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, limpar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                fetch('../../App/Database/limparCarrinho.php', { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Atualiza a interface
                        document.getElementById('listable').innerHTML = '<tr><td colspan="4" class="text-center">Carrinho Vazio</td></tr>';
                        document.querySelector('.total-value span').textContent = 'R$ 0,00';
                        
                        // Atualiza os botões
                        this.updateActions(0);
                        
                        // Reseta a barra de progresso
                        const progressBar = document.querySelector('.cart-progress-bar');
                        if (progressBar) {
                            progressBar.style.width = '0%';
                        }
                        
                        showTemporaryAlert('Carrinho limpo com sucesso!', 'success');
                    } else {
                        showTemporaryAlert('Erro ao limpar carrinho', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showTemporaryAlert('Erro ao limpar carrinho', 'error');
                })
                .finally(() => hideLoading());
            }
        });
    }
};

// Inicialização quando o DOM carrega
document.addEventListener('DOMContentLoaded', function() {
    // Unifica todas as inicializações em uma única função
    const initializeApp = () => {
        // Inicializa código de barras
        const barcodeInput = document.getElementById('barcodeInput');
        if (barcodeInput) {
            barcodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const barcode = this.value.trim();
                    
                    if (!barcode) {
                        showTemporaryAlert('Digite um código de barras', 'warning');
                        return;
                    }
                    
                    cart.addItem({
                        prodSubmit: 'barcode',
                        codigo: barcode
                    });
                }
            });
        }
        
        // Inicializa botões de quantidade
        initializeQuantityControls();
        
        // Inicializa botões do carrinho
        cart.initializeButtons();
        
        // Inicializa inputs de quantidade do carrinho
        initializeCartQuantityInputs();
    };

    // Função única para inicializar controles de quantidade
    const initializeQuantityControls = () => {
        document.querySelectorAll('.product-card').forEach(card => {
            const quantityInput = card.querySelector('.quantity-input');
            const minusBtn = card.querySelector('.quantity-btn.minus');
            const plusBtn = card.querySelector('.quantity-btn.plus');
            
            if (!quantityInput || !minusBtn || !plusBtn) return;
            
            const maxStock = parseInt(quantityInput.getAttribute('max')) || 0;
            
            // Remover eventos antigos
            minusBtn.replaceWith(minusBtn.cloneNode(true));
            plusBtn.replaceWith(plusBtn.cloneNode(true));
            
            // Obter referências aos novos elementos
            const newMinusBtn = card.querySelector('.quantity-btn.minus');
            const newPlusBtn = card.querySelector('.quantity-btn.plus');
            
            // Adicionar novos event listeners
            newMinusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                    updateQuantityState(card);
                }
            });
            
            newPlusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue < maxStock) {
                    quantityInput.value = currentValue + 1;
                    updateQuantityState(card);
                } else {
                    showTemporaryAlert(`Quantidade máxima disponível: ${maxStock}`, 'warning');
                }
            });
            
            // Validação do input
            quantityInput.addEventListener('change', () => {
                let value = parseInt(quantityInput.value);
                if (isNaN(value) || value < 1) {
                    value = 1;
                } else if (value > maxStock) {
                    value = maxStock;
                    showTemporaryAlert(`Quantidade máxima disponível: ${maxStock}`, 'warning');
                }
                quantityInput.value = value;
                updateQuantityState(card);
            });
            
            // Inicializar estado
            updateQuantityState(card);
        });

        // Dentro da função initializeQuantityControls(), adicione este trecho
        document.querySelectorAll('.btn-add-cart').forEach(addButton => {
            if (!addButton.dataset.initialized) {
                addButton.dataset.initialized = 'true';
                addButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (addButton.disabled) return;

                    // Desabilitar o botão temporariamente
                    addButton.disabled = true;
                    
                    const card = addButton.closest('.product-card');
                    const productId = addButton.getAttribute('data-id');
                    const quantityInput = card.querySelector('.quantity-input');
                    const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                    
                    // Feedback visual
                    const originalText = addButton.innerHTML;
                    addButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Adicionando...';
                    
                    // Adicionar ao carrinho com a quantidade selecionada
                    cart.addItem({
                        prodSubmit: 'carrinho',
                        idItem: productId,
                        qtde: quantity
                    }).then((response) => {
                        if (response.success) {
                            // Reseta a quantidade para 1 após adicionar com sucesso
                            if (quantityInput) {
                                quantityInput.value = 1;
                                updateQuantityState(card);
                            }
                        }
                    }).finally(() => {
                        // Restaurar botão
                        addButton.disabled = false;
                        addButton.innerHTML = originalText;
                    });
                });
            }
        });
    };

    // Função auxiliar para atualizar estados dos botões
    const updateQuantityState = (card) => {
        const input = card.querySelector('.quantity-input');
        const minusBtn = card.querySelector('.quantity-btn.minus');
        const plusBtn = card.querySelector('.quantity-btn.plus');
        const currentValue = parseInt(input.value);
        const maxStock = parseInt(input.getAttribute('max')) || 0;
        
        // Atualizar estado dos botões
        if (minusBtn) minusBtn.disabled = currentValue <= 1;
        if (plusBtn) plusBtn.disabled = currentValue >= maxStock;
        
        // Adicionar efeito visual de pulso
        const container = input.closest('.quantity-control');
        container.classList.add('quantity-pulse');
        setTimeout(() => container.classList.remove('quantity-pulse'), 300);
    };

    // Inicia a aplicação
    initializeApp();
});

// Funções auxiliares
function showTemporaryAlert(message, type = 'error') {
    const existingAlert = document.querySelector('.floating-alert');
    if (existingAlert) existingAlert.remove();
    
    const alert = document.createElement('div');
    alert.className = `floating-alert ${type}`;
    alert.textContent = message;
    document.body.appendChild(alert);
    
    setTimeout(() => alert.classList.add('show'), 10);
    setTimeout(() => {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// Funções de loading
function showLoading() {
    loadingOverlay.classList.add('active');
}

function hideLoading() {
    loadingOverlay.classList.remove('active');
}

// Inicialização do overlay de loading
const loadingOverlay = document.createElement('div');
loadingOverlay.className = 'loading-overlay';
loadingOverlay.innerHTML = '<div class="loading-spinner"></div>';
document.body.appendChild(loadingOverlay);

// Função de pesquisa em tempo real
document.getElementById('productSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const items = document.querySelectorAll('.product-item');
    
    items.forEach(item => {
        const productName = item.querySelector('.product-name').textContent.toLowerCase();
        const productId = item.querySelector('.btn-add-cart').getAttribute('data-id');
        
        // Pesquisa por nome ou ID
        if (searchTerm === '' || 
            productName.includes(searchTerm) || 
            productId.includes(searchTerm)) {
            
            item.style.display = '';
            
            // Adiciona destaque se houver termo de pesquisa
            if (searchTerm) {
                item.querySelector('.product-card').style.borderColor = '#3498db';
                item.querySelector('.product-name').style.color = '#3498db';
                
                // Mostra o ID do produto quando encontrado por ID
                if (productId.includes(searchTerm)) {
                    const idBadge = document.createElement('div');
                    idBadge.className = 'id-badge';
                    idBadge.textContent = `ID: ${productId}`;
                    
                    // Remove badge anterior se existir
                    const existingBadge = item.querySelector('.id-badge');
                    if (existingBadge) {
                        existingBadge.remove();
                    }
                    
                    item.querySelector('.product-info').prepend(idBadge);
                }
            } else {
                item.querySelector('.product-card').style.borderColor = '';
                item.querySelector('.product-name').style.color = '';
                const idBadge = item.querySelector('.id-badge');
                if (idBadge) idBadge.remove();
            }
        } else {
            item.style.display = 'none';
            item.querySelector('.product-card').style.borderColor = '';
            item.querySelector('.product-name').style.color = '';
            const idBadge = item.querySelector('.id-badge');
            if (idBadge) idBadge.remove();
        }
    });
});

// Adicione estes estilos ao seu CSS
const style = document.createElement('style');
style.textContent = `
    .id-badge {
        background: #3498db;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin-bottom: 5px;
    }
`;
document.head.appendChild(style);

// Adicionar manipuladores de eventos para os botões de quantidade nos cards de produtos
document.addEventListener('DOMContentLoaded', function() {
    // Manipula os botões de quantidade nos cards de produtos
    document.querySelectorAll('.product-card').forEach(card => {
        const minusBtn = card.querySelector('.quantity-btn.minus');
        const plusBtn = card.querySelector('.quantity-btn.plus');
        const quantityInput = card.querySelector('.product-quantity');
        const maxStock = parseInt(quantityInput.getAttribute('max'));

        if (minusBtn && plusBtn && quantityInput) {
            // Botão de diminuir
            minusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });

            // Botão de aumentar
            plusBtn.addEventListener('click', () => {
                let currentValue = parseInt(quantityInput.value);
                if (currentValue < maxStock) {
                    quantityInput.value = currentValue + 1;
                } else {
                    showTemporaryAlert(`Quantidade máxima disponível: ${maxStock}`, 'warning');
                }
            });

            // Validação de input manual
            quantityInput.addEventListener('change', () => {
                let value = parseInt(quantityInput.value);
                if (isNaN(value) || value < 1) {
                    quantityInput.value = 1;
                } else if (value > maxStock) {
                    quantityInput.value = maxStock;
                    showTemporaryAlert(`Quantidade máxima disponível: ${maxStock}`, 'warning');
                }
            });

            // Previne entrada de caracteres não numéricos
            quantityInput.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }
    });
});

// Função para atualizar quantidade no carrinho
function updateQuantity(idItem, change) {
    const qtyInput = document.getElementById(`qty_${idItem}`);
    const currentQty = parseInt(qtyInput.value);
    const newQty = currentQty + change;
    
    // Verifica limites
    if (newQty < 1) {
        showTemporaryAlert('Quantidade mínima é 1', 'warning');
        return;
    }
    
    updateQuantityDirect(idItem, newQty);
}

// Função para atualizar quantidade diretamente
function updateQuantityDirect(idItem, newQty) {
    showLoading();
    
    fetch('../../App/Database/carrinho.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'updateQuantity',
            idItem: idItem,
            quantidade: newQty
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza todo o conteúdo do carrinho
            document.getElementById('listable').innerHTML = data.cartHtml;
            document.querySelector('.total-value span').textContent = `R$ ${data.totalCarrinho}`;
            
            // Reativa os event listeners dos botões
            cart.initializeButtons();
            
            // Reativa os event listeners dos inputs de quantidade
            initializeCartQuantityInputs();
        } else {
            showTemporaryAlert(data.message, 'error');
            // Recarrega a página para restaurar o estado anterior
            if (data.message.includes('estoque')) {
                location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showTemporaryAlert('Erro ao atualizar quantidade', 'error');
    })
    .finally(() => hideLoading());
}

// Função para inicializar os inputs de quantidade do carrinho
function initializeCartQuantityInputs() {
    document.querySelectorAll('.cart-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const idItem = this.id.replace('qty_', '');
            let value = parseInt(this.value);
            
            if (isNaN(value) || value < 1) {
                value = 1;
                this.value = 1;
            }
            
            updateQuantityDirect(idItem, value);
        });

        input.addEventListener('keypress', (e) => {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    });
}

// Inicializa os inputs quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    initializeCartQuantityInputs();
    // ...resto do código existente...
});

document.addEventListener('DOMContentLoaded', function() {
    // ...existing code...

    // Adiciona validação para inputs de quantidade no carrinho
    document.querySelectorAll('.cart-quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const idItem = this.id.replace('qty_', '');
            let value = parseInt(this.value);
            
            if (isNaN(value) || value < 1) {
                value = 1;
                this.value = 1;
            }
            
            updateQuantityDirect(idItem, value);
        });

        // Previne entrada de caracteres não numéricos
        input.addEventListener('keypress', (e) => {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });
    });
});

// Adicionar após as outras funções do carrinho
function removeCartItem(idItem) {
    const button = document.querySelector(`button[onclick="removeCartItem(${idItem})"]`);
    const productName = button.dataset.product;
    const productValue = button.dataset.value;

    Swal.fire({
        title: 'Remover item?',
        html: `
            <p>Deseja remover o item:</p>
            <strong>${productName}</strong><br>
            <small>Valor: R$ ${productValue}</small>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            fetch('../../App/Database/carrinho.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'removeItem',
                    idItem: idItem
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza o carrinho
                    document.getElementById('listable').innerHTML = data.cartHtml;
                    document.querySelector('.total-value span').textContent = `R$ ${data.totalCarrinho}`;
                    
                    // Atualiza os botões de ação
                    cart.updateActions(data.pkCount);
                    
                    // Mostra mensagem de sucesso
                    showTemporaryAlert('Item removido com sucesso', 'success');
                    
                    // Atualiza contador do carrinho se existir
                    const cartCounter = document.querySelector('.cart-counter');
                    if (cartCounter) {
                        cartCounter.textContent = data.pkCount;
                        if (data.pkCount === 0) {
                            cartCounter.style.display = 'none';
                        }
                    }
                } else {
                    showTemporaryAlert(data.message || 'Erro ao remover item', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showTemporaryAlert('Erro ao processar requisição', 'error');
            })
            .finally(() => {
                hideLoading();
            });
        }
    });
}

// Substitua a função updateQuantity por esta versão corrigida
function updateQuantity(button, change) {
    const container = button.closest('.quantity-control');
    const input = container.querySelector('.quantity-input');
    const maxStock = parseInt(input.getAttribute('max'));
    let currentValue = parseInt(input.value);
    let newValue = currentValue + change;

    // Validar limites
    if (newValue < 1) {
        newValue = 1;
        showTemporaryAlert('Quantidade mínima é 1', 'warning');
        return;
    }
    
    if (newValue > maxStock) {
        newValue = maxStock;
        showTemporaryAlert(`Quantidade máxima disponível: ${maxStock}`, 'warning');
        return;
    }

    // Atualizar valor
    input.value = newValue;

    // Adicionar efeito visual
    container.classList.add('quantity-pulse');
    setTimeout(() => container.classList.remove('quantity-pulse'), 300);

    // Atualizar estado dos botões
    const minusBtn = container.querySelector('.quantity-btn:first-child');
    const plusBtn = container.querySelector('.quantity-btn:last-child');
    
    minusBtn.disabled = newValue <= 1;
    plusBtn.disabled = newValue >= maxStock;
}

// Adicione estas funções auxiliares
function validateQuantity(input) {
    const container = input.closest('.quantity-control');
    const maxStock = parseInt(input.getAttribute('max'));
    let value = parseInt(input.value);

    if (isNaN(value) || value < 1) {
        value = 1;
    } else if (value > maxStock) {
        value = maxStock;
        showTemporaryAlert(`Quantidade máxima disponível: ${maxStock}`, 'warning');
    }

    input.value = value;
    
    // Atualizar estado dos botões
    const minusBtn = container.querySelector('.quantity-btn:first-child');
    const plusBtn = container.querySelector('.quantity-btn:last-child');
    
    minusBtn.disabled = value <= 1;
    plusBtn.disabled = value >= maxStock;
}

// Adicione este evento para reinicializar os controles após mudanças no DOM
const observer = new MutationObserver(() => {
    initializeQuantityControls();
});

observer.observe(document.getElementById('products-grid'), {
    childList: true,
    subtree: true
});

// Dentro da função initializeQuantityControls(), substitua o trecho que configura o botão add-cart por:
if (addButton && !addButton.dataset.initialized) {
    addButton.dataset.initialized = 'true';
    addButton.addEventListener('click', (e) => {
        e.preventDefault();
        if (addButton.disabled) return;

        // Desabilitar o botão temporariamente
        addButton.disabled = true;
        
        const productId = addButton.getAttribute('data-id');
        // Pegar a quantidade do input dentro do mesmo card
        const quantityInput = card.querySelector('.quantity-input');
        const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
        
        // Validações
        if (!productId || isNaN(quantity) || quantity < 1) {
            showTemporaryAlert('Dados inválidos para adicionar ao carrinho', 'error');
            addButton.disabled = false;
            return;
        }

        // Feedback visual
        addButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Adicionando...';
        
        // Adicionar ao carrinho com a quantidade selecionada
        cart.addItem({
            prodSubmit: 'carrinho',
            idItem: productId,
            qtde: quantity
        }).then(() => {
            // Após adicionar com sucesso, resetar a quantidade para 1
            if (quantityInput) {
                quantityInput.value = 1;
                updateQuantityState(card); // Atualiza estado dos botões
            }
        }).finally(() => {
            // Restaurar botão
            addButton.disabled = false;
            addButton.innerHTML = '<i class="fa fa-cart-plus"></i> Adicionar';
        });
    });
}

// Adicione estas novas funções para controle de quantidade no carrinho
function updateCartQuantity(id, change) {
    const input = document.getElementById(`qty_${id}`);
    if (!input) return;
    
    const currentValue = parseInt(input.value);
    const maxStock = parseInt(input.getAttribute('max'));
    const newValue = currentValue + change;
    
    if (newValue < 1 || newValue > maxStock) {
        showTemporaryAlert(`Quantidade deve estar entre 1 e ${maxStock}`, 'warning');
        return;
    }
    
    updateCartQuantityDirect(id, newValue);
}

function updateCartQuantityDirect(id, value) {
    showLoading();
    
    fetch('../../App/Database/carrinho.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            action: 'updateQuantity',
            idItem: id,
            quantidade: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('listable').innerHTML = data.cartHtml;
            document.querySelector('.total-value span').textContent = `R$ ${data.totalCarrinho}`;
            
            // Adiciona efeito visual
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.classList.add('quantity-updated');
                setTimeout(() => row.classList.remove('quantity-updated'), 300);
            }
        } else {
            showTemporaryAlert(data.message || 'Erro ao atualizar quantidade', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showTemporaryAlert('Erro ao atualizar quantidade', 'error');
    })
    .finally(() => hideLoading());
}

// Adicione estes estilos CSS
const cartStyles = document.createElement('style');
cartStyles.textContent = `
    .cart-quantity {
        width: 60px !important;
        text-align: center;
        border: 1px solid #dee2e6;
        border-radius: 20px;
        padding: 4px;
        font-weight: 600;
    }
    
    .quantity-updated {
        animation: highlight-row 0.3s ease-in-out;
    }
    
    @keyframes highlight-row {
        0% { background-color: transparent; }
        50% { background-color: rgba(52, 152, 219, 0.1); }
        100% { background-color: transparent; }
    }
`;
document.head.appendChild(cartStyles);


    // If you need to validate before submission
document.getElementById('cart-form').addEventListener('submit', function(e) {
    // Check if cart is empty or invalid
    if (document.querySelectorAll('.cart-item').length === 0) {
        e.preventDefault();
        showTemporaryAlert('Please check the cart items', 'warning');
        return;
    }
    // Otherwise let the form submit normally
});
</script>

<!-- Adicione o SweetAlert2 no head do documento -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
echo $footer;
echo $javascript;
?>
<!-- Modal Salvar Carrinho -->
<div class="modal fade" id="modalSalvarCarrinho" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="carrinhos_salvos.php">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Guardar Carrinho</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome_carrinho">Nome do Carrinho</label>
                        <input type="text" class="form-control" id="nome_carrinho" name="nome_carrinho" 
                               placeholder="Ex: Venda Cliente João" required>
                        <small class="text-muted">
                            Digite um nome para identificar este carrinho posteriormente
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="salvar_carrinho">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
