<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/vendas.class.php';

if (!isset($_SESSION['notavd'])) {
    header('Location: ../../views/vendas/');
    exit();
}

$valorTotal = isset($_SESSION['valorTotal']) ? $_SESSION['valorTotal'] : 0;
$nomeUsuario = 'Não identificado';
if (isset($_SESSION['idUsuario'])) {
    $connect = new Connect();
    $query = "SELECT Username FROM usuario WHERE idUser = ?";
    $stmt = $connect->SQL->prepare($query);
    $stmt->bind_param('i', $_SESSION['idUsuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nomeUsuario = $row['Username'];
    }
}

// Adicione este código logo após a definição de $valorTotal
$produtos = array();
if (isset($_SESSION['notavd'])) {
    $connect = new Connect();
    $cart = $_SESSION['notavd'];
    
    // Busca os produtos da venda
    $query = "SELECT v.quantitens, v.valor, p.NomeProduto 
             FROM vendas v 
             INNER JOIN itens i ON v.iditem = i.idItens 
             INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
             WHERE v.cart = ? 
             GROUP BY v.iditem"; // Agrupa por ID do item para evitar duplicatas
             
    $stmt = $connect->SQL->prepare($query);
    $stmt->bind_param('s', $cart);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $produtos[] = array(
                'nome' => $row['NomeProduto'],
                'quantidade' => $row['quantitens'],
                'valor_unitario' => $row['valor'] / $row['quantitens'],
                'valor_total' => $row['valor']
            );
        }
    }
}

// Adicione este código logo após as definições iniciais
if (!isset($_SESSION['venda_temp'])) {
    // Busca os dados da venda atual
    $connect = new Connect();
    $cart = $_SESSION['notavd'];
    
    $query = "SELECT v.*, p.NomeProduto, i.ValVendItens 
             FROM vendas v 
             INNER JOIN itens i ON v.iditem = i.idItens 
             INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
             WHERE v.cart = ?";
             
    $stmt = $connect->SQL->prepare($query);
    $stmt->bind_param('s', $cart);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $vendaTemp = array(
            'produtos' => array(),
            'total' => $valorTotal,
            'cart' => $cart,
            'data' => date('Y-m-d H:i:s'),
            'vendedor' => $nomeUsuario
        );
        
        while ($row = $result->fetch_assoc()) {
            $vendaTemp['produtos'][] = array(
                'nome' => $row['NomeProduto'],
                'quantidade' => $row['quantitens'],
                'valor_unitario' => $row['valor'] / $row['quantitens'],
                'valor_total' => $row['valor'],
                'iditem' => $row['iditem']
            );
        }
        
        $_SESSION['venda_temp'] = $vendaTemp;
    }
}

ob_start();

echo $head;
echo $header;
echo $aside;

// Processamento do pagamento
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['paymentMethod'])) {
    $paymentMethod = $_POST['paymentMethod'];
    $valorPago = isset($_POST['valorPago']) ? floatval($_POST['valorPago']) : 0.0;
    $cart = $_SESSION['notavd'];
    
    // Calcula o troco
    $troco = $valorPago - $valorTotal;
    
    // Verifica se o pagamento foi bem sucedido
    $pagamentoOK = false;
    
    switch($paymentMethod) {
        case 'dinheiro':
            if ($valorPago >= $valorTotal) {
                $pagamentoOK = true;
            }
            break;
        case 'credito':
        case 'debito':
        case 'pix':
            $pagamentoOK = true;
            break;
    }
    
    if ($pagamentoOK) {
        try {
            $connect = new Connect();
            $query = "UPDATE vendas 
                     SET status_pagamento = 'PAGO', 
                         forma_pagamento = ?, 
                         valor_pago = ? 
                     WHERE cart = ?";
            
            $stmt = $connect->SQL->prepare($query);
            $stmt->bind_param('sds', $paymentMethod, $valorPago, $cart);
            
            if ($stmt->execute()) {
                // Mostra o troco antes de limpar a sessão
                echo "<script>
                    alert('Pagamento finalizado! Troco: R$ " . number_format($troco, 2, ',', '.') . "');
                    window.location.href = '../../views/vendas/';
                </script>";
                unset($_SESSION['notavd']);
                unset($_SESSION['valorTotal']);
                unset($_SESSION['itens']);
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['msg'] = "<div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                <strong>Erro!</strong> Falha ao processar pagamento: " . $e->getMessage() . "
            </div>";
        }
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="header-title">
            <h1>
                <i class="fa fa-shopping-cart"></i> Finalizar Venda
                <small>Pagamento e Troco</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="index.php">Vendas</a></li>
                <li class="active">Finalizar Venda</li>
            </ol>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <!-- Coluna de Pagamento -->
            <div class="col-md-8">
                <div class="box box-primary payment-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-credit-card"></i> Método de Pagamento</h3>
                    </div>
                    <div class="box-body">
                        <div class="payment-alert">
                            <i class="fa fa-check-circle"></i>
                            <div class="alert-content">
                                <h4>Venda Registrada com Sucesso!</h4>
                                <p>Por favor, selecione a forma de pagamento para continuar.</p>
                            </div>
                        </div>

                        <form action="" method="POST" id="formPagamento">
                            <div class="payment-grid">
                                <div class="payment-option-card" onclick="selectPayment('dinheiro')">
                                    <input type="radio" name="paymentMethod" value="dinheiro" id="dinheiro" checked>
                                    <i class="fa fa-money"></i>
                                    <span>Dinheiro</span>
                                </div>
                                
                                <div class="payment-option-card" onclick="selectPayment('credito')">
                                    <input type="radio" name="paymentMethod" value="credito" id="credito">
                                    <i class="fa fa-credit-card"></i>
                                    <span>Crédito</span>
                                </div>
                                
                                <div class="payment-option-card" onclick="selectPayment('debito')">
                                    <input type="radio" name="paymentMethod" value="debito" id="debito">
                                    <i class="fa fa-credit-card"></i>
                                    <span>Débito</span>
                                </div>
                                
                                <div class="payment-option-card" onclick="selectPayment('pix')">
                                    <input type="radio" name="paymentMethod" value="pix" id="pix">
                                    <i class="fa fa-qrcode"></i>
                                    <span>PIX</span>
                                </div>
                            </div>

                            <div id="dinheiroFields" class="payment-details">
                                <div class="form-group">
                                    <label for="valorTotal">Valor Total da Venda</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">R$</span>
                                        <input type="number" name="valorTotal" class="form-control" id="valorTotal" 
                                               value="<?php echo number_format($valorTotal, 2, '.', ''); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="valorPago">Valor Recebido</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">R$</span>
                                        <input type="number" name="valorPago" step="0.01" class="form-control" 
                                               id="valorPago" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>

                            <div id="creditoFields" class="payment-details" style="display: none;">
                                <div class="form-group">
                                    <label>Valor Total</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">R$</span>
                                        <input type="text" class="form-control" value="<?php echo number_format($valorTotal, 2, ',', '.'); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Número do Cartão</label>
                                    <input type="text" class="form-control card-number" placeholder="0000 0000 0000 0000" maxlength="19">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Validade</label>
                                            <input type="text" class="form-control card-expiry" placeholder="MM/AA" maxlength="5">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>CVV</label>
                                            <input type="text" class="form-control card-cvv" placeholder="123" maxlength="3">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Parcelas</label>
                                    <select class="form-control" id="parcelas">
                                        <option value="1">1x de R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?> sem juros</option>
                                        <option value="2">2x de R$ <?php echo number_format($valorTotal/2, 2, ',', '.'); ?> sem juros</option>
                                        <option value="3">3x de R$ <?php echo number_format($valorTotal/3, 2, ',', '.'); ?> sem juros</option>
                                    </select>
                                </div>
                            </div>

                            <div id="debitoFields" class="payment-details" style="display: none;">
                                <div class="form-group">
                                    <label>Valor Total</label>
                                    <div class="input-group">
                                        <span class="input-group-addon">R$</span>
                                        <input type="text" class="form-control" value="<?php echo number_format($valorTotal, 2, ',', '.'); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Número do Cartão</label>
                                    <input type="text" class="form-control card-number" placeholder="0000 0000 0000 0000" maxlength="19">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Validade</label>
                                            <input type="text" class="form-control card-expiry" placeholder="MM/AA" maxlength="5">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>CVV</label>
                                            <input type="text" class="form-control card-cvv" placeholder="123" maxlength="3">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="pixFields" class="payment-details" style="display: none;">
                                <div class="text-center pix-container">
                                    <div class="qr-code-container">
                                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=" 
                                             alt="QR Code" id="qrCodeImage" class="qr-code">
                                    </div>
                                    <div class="pix-info">
                                        <p class="pix-value">Valor: R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></p>
                                        <div class="pix-copy">
                                            <input type="text" class="form-control" id="pixCode" readonly>
                                            <button type="button" class="btn btn-default" onclick="copyPixCode()">
                                                <i class="fa fa-copy"></i> Copiar Código
                                            </button>
                                        </div>
                                        <div class="pix-status">
                                            <i class="fa fa-spinner fa-spin"></i>
                                            <span>Aguardando pagamento...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="payment-actions">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fa fa-check"></i> Finalizar Pagamento
                                </button>
                                <a href="index.php" class="btn btn-default btn-lg">
                                    <i class="fa fa-arrow-left"></i> Voltar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Coluna do Resumo -->
            <div class="col-md-4">
                <div class="box box-info summary-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-file-text"></i> Resumo da Venda</h3>
                    </div>
                    <div class="box-body">
                        <!-- Informações da Venda -->
                        <div class="summary-section">
                            <h4 class="section-title">Informações da Venda</h4>
                            <div class="summary-item">
                                <span class="label">Código da Venda</span>
                                <span class="value">#<?php echo $_SESSION['notavd']; ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Data/Hora</span>
                                <span class="value"><?php echo date('d/m/Y H:i:s'); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Vendedor</span>
                                <span class="value"><?php echo $nomeUsuario; ?></span>
                            </div>
                        </div>

                        <!-- Lista de Produtos -->
                        <div class="summary-section">
                            <h4 class="section-title">Produtos</h4>
                            <div class="products-list">
                                <?php
                                if (!empty($produtos)) {
                                    foreach ($produtos as $produto) {
                                        echo "<div class='product-item'>
                                                <div class='product-details'>
                                                    <span class='product-name'>{$produto['nome']}</span>
                                                    <div class='product-info'>
                                                        <span class='quantity'>{$produto['quantidade']}x</span>
                                                        <span class='price'>R$ " . number_format($produto['valor_unitario'], 2, ',', '.') . "</span>
                                                    </div>
                                                </div>
                                                <div class='product-total'>
                                                    R$ " . number_format($produto['valor_total'], 2, ',', '.') . "
                                                </div>
                                            </div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-info'>
                                            <i class='fa fa-info-circle'></i> Nenhum produto encontrado
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Totais -->
                        <div class="summary-section totals">
                            <div class="summary-item subtotal">
                                <span class="label">Subtotal</span>
                                <span class="value">R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></span>
                            </div>
                            <div class="summary-item total">
                                <span class="label">Total</span>
                                <span class="value">R$ <?php echo number_format($valorTotal, 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Reset e Variáveis */
:root {
    --primary-color: #3498db;
    --success-color: #2ecc71;
    --danger-color: #e74c3c;
    --text-color: #2c3e50;
    --border-radius: 8px;
    --box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Layout Geral */
.content-wrapper {
    background: #f8f9fa;
    padding: 20px;
}

.header-title h1 {
    font-size: 24px;
    color: var(--text-color);
    margin-bottom: 20px;
}

/* Boxes */
.box {
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    border: none;
    margin-bottom: 30px;
}

.box-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.box-title {
    font-size: 18px;
    color: var(--text-color);
}

.box-body {
    padding: 30px;
}

/* Alerta de Sucesso */
.payment-alert {
    display: flex;
    align-items: center;
    background: #e8f7ef;
    padding: 20px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
}

.payment-alert i {
    font-size: 40px;
    color: var(--success-color);
    margin-right: 20px;
}

.alert-content h4 {
    color: var(--success-color);
    margin: 0 0 5px 0;
}

.alert-content p {
    margin: 0;
    color: #2c3e50;
}

/* Grid de Métodos de Pagamento */
.payment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.payment-option-card {
    background: #fff;
    border: 2px solid #eee;
    border-radius: var(--border-radius);
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-option-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.payment-option-card.selected {
    border-color: var(--primary-color);
    background: #f0f7ff;
}

.payment-option-card input[type="radio"] {
    display: none;
}

.payment-option-card i {
    font-size: 30px;
    color: var(--primary-color);
    margin-bottom: 10px;
    display: block;
}

.payment-option-card span {
    font-size: 16px;
    color: var(--text-color);
    font-weight: 500;
}

/* Campos de Pagamento */
.payment-details {
    background: #f8f9fa;
    padding: 20px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
}

.form-group label {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.input-group-addon {
    background: #f8f9fa;
    border: 1px solid #ddd;
    color: #666;
    font-weight: 600;
}

.form-control {
    height: 45px;
    font-size: 18px;
    font-weight: 600;
}

/* Botões */
.payment-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn-lg {
    padding: 12px 30px;
    font-size: 16px;
    border-radius: var(--border-radius);
    transition: all 0.3s ease;
}

.btn-success {
    background: var(--success-color);
    border: none;
    flex: 2;
}

.btn-default {
    background: #fff;
    border: 1px solid #ddd;
    flex: 1;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
}

/* Resumo da Venda */
.summary-box {
    position: sticky;
    top: 20px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.row {
    display: flex;
    flex-wrap: wrap;
}

.col-md-8, .col-md-4 {
    display: flex;
    flex-direction: column;
}

.box {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.box-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.summary-box .box-body {
    padding: 20px;
    justify-content: space-between;
}

.summary-items {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.summary-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item.total {
    margin-top: auto;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.summary-item .label {
    color: #666;
    font-weight: normal;
    font-size: 14px;
}

.summary-item .value {
    color: var(--text-color);
    font-weight: 600;
}

/* Modal de Troco */
.modal-content {
    border-radius: var(--border-radius);
}

.modal-header.bg-success {
    background: var(--success-color);
    color: white;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.troco-value {
    background: linear-gradient(145deg, var(--success-color), #27ae60);
}

@media (max-width: 768px) {
    .payment-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .payment-actions {
        flex-direction: column;
    }
    
    .btn-lg {
        width: 100%;
    }
}

/* Estilos para campos de cartão */
.card-number {
    background-image: url('path/to/card-icon.png');
    background-repeat: no-repeat;
    background-position: 98% center;
    background-size: 24px;
}

/* Estilos para PIX */
.pix-container {
    padding: 20px;
    background: #fff;
    border-radius: var(--border-radius);
}

.qr-code-container {
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border: 1px solid #ddd;
    display: inline-block;
    border-radius: 10px;
}

.qr-code {
    width: 200px;
    height: 200px;
}

.pix-info {
    margin-top: 20px;
}

.pix-value {
    font-size: 20px;
    font-weight: bold;
    color: var(--text-color);
    margin-bottom: 15px;
}

.pix-copy {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.pix-status {
    padding: 15px;
    background: #f8f9fa;
    border-radius: var(--border-radius);
    color: #666;
}

.pix-status i {
    margin-right: 10px;
    color: var(--primary-color);
}

/* Estilos para o modal de troco */
.troco-container {
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 20px;
}

.troco-header {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.success-animation {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: var(--success-color);
    position: relative;
    animation: pulse 1.5s infinite;
}

.checkmark {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
}

.checkmark-circle {
    width: 100%;
    height: 100%;
    border: 4px solid #fff;
    border-radius: 50%;
    box-sizing: border-box;
    position: absolute;
    top: 0;
    left: 0;
    transform: rotate(45deg);
}

.checkmark-stem {
    width: 4px;
    height: 20px;
    background: #fff;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(90deg);
}

.checkmark-kick {
    width: 10px;
    height: 4px;
    background: #fff;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(30deg);
}

.troco-details {
    margin-bottom: 20px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.detail-row .label {
    font-weight: bold;
}

.troco-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.btn-finalizar, .btn-imprimir {
    padding: 10px 20px;
    border: none;
    border-radius: var(--border-radius);
    transition: all 0.3s ease;
}

.btn-finalizar {
    background: var(--success-color);
    color: white;
}

.btn-finalizar:hover {
    background: #27ae60;
}

.btn-imprimir {
    background: #f8f9fa;
    color: var(--text-color);
}

.btn-imprimir:hover {
    background: #e8e8e8;
}

/* Estilos para o novo modal de troco */
#trocoModal .modal-dialog {
    max-width: 500px; /* Define a largura máxima do modal */
    margin-top: 50px; /* Adiciona margem superior ao modal */
}

#trocoModal .modal-content {
    border: none; /* Remove a borda padrão do modal */
    border-radius: 20px; /* Arredonda os cantos do modal */
    overflow: hidden; /* Esconde qualquer conteúdo que ultrapasse os limites do modal */
    box-shadow: 0 10px 30px rgba(0,0,0,0.1); /* Adiciona uma sombra suave ao modal */
}

.troco-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); /* Cria um gradiente de fundo suave */
}

.troco-header {
    background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%); /* Cria um gradiente de fundo verde */
    padding: 30px 20px; /* Adiciona espaçamento interno */
    text-align: center; /* Centraliza o texto */
    color: white; /* Define a cor do texto como branco */
}

.troco-header h3 {
    margin: 15px 0 0; /* Ajusta as margens do título */
    font-size: 24px; /* Define o tamanho da fonte */
    font-weight: 600; /* Define a espessura da fonte */
}

/* Animação do checkmark */
/* Animação de sucesso */
.success-animation {
    margin: 0 auto; /* Centraliza a animação */
    width: 80px; /* Define a largura */
    height: 80px; /* Define a altura */
    position: relative; /* Permite posicionamento absoluto dos elementos filhos */
}

/* Estilo do checkmark */
.checkmark {
    width: 100%; /* Ocupa toda a largura do contêiner pai */
    height: 100%; /* Ocupa toda a altura do contêiner pai */
    position: relative; /* Permite posicionamento absoluto dos elementos filhos */
    animation: scale 0.5s ease-in-out 0.2s both; /* Aplica animação de escala */
}

/* Círculo ao redor do checkmark */
.checkmark-circle {
    width: 80px; /* Define a largura do círculo */
    height: 80px; /* Define a altura do círculo */
    border-radius: 50%; /* Cria um círculo perfeito */
    background: rgba(255,255,255,0.1); /* Define a cor de fundo com transparência */
    position: absolute; /* Posiciona de forma absoluta */
    animation: stroke 0.5s cubic-bezier(0.65, 0, 0.45, 1) forwards; /* Aplica animação de traço */
}

/* Haste vertical do checkmark */
.checkmark-stem {
    width: 3px; /* Define a largura da haste */
    height: 33px; /* Define a altura da haste */
    background-color: #fff; /* Define a cor de fundo */
    position: absolute; /* Posiciona de forma absoluta */
    left: 38px; /* Posiciona horizontalmente */
    top: 20px; /* Posiciona verticalmente */
    border-radius: 2px; /* Arredonda levemente as bordas */
    transform: translateY(40px) scaleY(0); /* Posiciona inicialmente fora da vista */
    transform-origin: center bottom; /* Define o ponto de origem da transformação */
    animation: stem-animation 0.2s ease-out 0.4s forwards; /* Aplica animação da haste */
}

/* Haste horizontal do checkmark */
.checkmark-kick {
    width: 25px; /* Define a largura da haste */
    height: 3px; /* Define a altura da haste */
    background-color: #fff; /* Define a cor de fundo */
    position: absolute; /* Posiciona de forma absoluta */
    left: 20px; /* Posiciona horizontalmente */
    top: 45px; /* Posiciona verticalmente */
    border-radius: 2px; /* Arredonda levemente as bordas */
    transform: translateX(20px) rotate(-45deg) scaleX(0); /* Posiciona e rotaciona inicialmente */
    transform-origin: center left; /* Define o ponto de origem da transformação */
    animation: kick-animation 0.2s ease-out 0.5s forwards; /* Aplica animação da haste */
}

/* Conteúdo dos detalhes do troco */
.troco-details {
    padding: 30px; /* Adiciona espaçamento interno */
}

/* Linha de detalhe individual */
.detail-row {
    display: flex; /* Usa flexbox para layout */
    justify-content: space-between; /* Distribui os itens horizontalmente */
    align-items: center; /* Alinha os itens verticalmente */
    padding: 15px; /* Adiciona espaçamento interno */
    border-bottom: 1px solid #eee; /* Adiciona uma linha separadora */
    animation: slideIn 0.5s ease-out forwards; /* Aplica animação de entrada */
    opacity: 0; /* Inicia com opacidade zero para a animação */
}

/* Atrasos de animação para cada linha de detalhe */
.detail-row:nth-child(1) { animation-delay: 0.2s; } /* Atraso para a primeira linha */
.detail-row:nth-child(2) { animation-delay: 0.4s; } /* Atraso para a segunda linha */
.detail-row:nth-child(3) { animation-delay: 0.6s; } /* Atraso para a terceira linha */

/* Estilo específico para a linha de troco */
.detail-row.troco {
    border-bottom: none; /* Remove a borda inferior */
    margin-top: 10px; /* Adiciona margem superior */
    padding: 20px 15px; /* Ajusta o espaçamento interno */
    background: #f8f9fa; /* Define a cor de fundo */
    border-radius: 10px; /* Arredonda os cantos */
}

/* Rótulo na linha de detalhe */
.detail-row .label {
    color: #666; /* Define a cor do texto */
    font-size: 16px; /* Define o tamanho da fonte */
    font-weight: normal; /* Define a espessura da fonte */
}

/* Valor na linha de detalhe */
.detail-row .value {
    font-size: 18px; /* Define o tamanho da fonte */
    font-weight: 600; /* Define a espessura da fonte */
    color: #2c3e50; /* Define a cor do texto */
}

/* Valor específico para o troco */
.detail-row.troco .value {
    font-size: 24px; /* Aumenta o tamanho da fonte */
    color: #2ecc71; /* Define a cor do texto como verde */
    font-weight: 700; /* Aumenta a espessura da fonte */
}

/* Container para os botões de ação */
.troco-actions {
    padding: 20px 30px; /* Adiciona espaçamento interno */
    display: flex; /* Usa flexbox para layout */
    gap: 15px; /* Adiciona espaço entre os botões */
    animation: slideUp 0.5s ease-out 0.8s forwards; /* Aplica animação de entrada */
    opacity: 0; /* Inicia com opacidade zero para a animação */
}

/* Estilo geral dos botões */
.troco-actions button {
    flex: 1; /* Faz os botões ocuparem espaço igual */
    border: none; /* Remove a borda padrão */
    padding: 15px; /* Adiciona espaçamento interno */
    border-radius: 10px; /* Arredonda os cantos */
    font-size: 16px; /* Define o tamanho da fonte */
    cursor: pointer; /* Muda o cursor para indicar interatividade */
    transition: all 0.3s ease; /* Adiciona transição suave para efeitos hover */
    display: flex; /* Usa flexbox para alinhar o conteúdo do botão */
    align-items: center; /* Alinha os itens verticalmente */
    justify-content: center; /* Centraliza o conteúdo horizontalmente */
    gap: 10px; /* Adiciona espaço entre o ícone e o texto do botão */
}

/* Estilo do botão de finalizar */
.btn-finalizar {
    background: #2ecc71; /* Define a cor de fundo verde */
    color: white; /* Define a cor do texto como branco */
}

/* Estilo do botão de imprimir */
.btn-imprimir {
    background: #f8f9fa; /* Define a cor de fundo cinza claro */
    color: #666; /* Define a cor do texto como cinza escuro */
}

/* Efeito hover no botão de finalizar */
.btn-finalizar:hover {
    background: #27ae60; /* Escurece levemente o verde no hover */
    transform: translateY(-2px); /* Move o botão ligeiramente para cima */
    box-shadow: 0 5px 15px rgba(46,204,113,0.3); /* Adiciona uma sombra suave */
}

/* Efeito hover no botão de imprimir */
.btn-imprimir:hover {
    background: #e9ecef; /* Escurece levemente o cinza no hover */
    transform: translateY(-2px); /* Move o botão ligeiramente para cima */
}

/* Animações */
/* Animação de escala */
@keyframes scale {
    0% { transform: scale(0); } /* Inicia com escala zero */
    100% { transform: scale(1); } /* Termina com escala normal */
}

/* Animação do traço do círculo */
@keyframes stroke {
    100% {
        transform: scale(1); /* Escala o círculo até o tamanho normal */
    }
}

/* Animação da haste vertical do checkmark */
@keyframes stem-animation {
    0% { transform: translateY(40px) scaleY(0); } /* Inicia fora da vista e sem altura */
    100% { transform: translateY(0) scaleY(1); } /* Move para a posição final e aumenta a altura */
}

/* Animação da haste horizontal do checkmark */
@keyframes kick-animation {
    0% { transform: translateX(20px) rotate(-45deg) scaleX(0); } /* Inicia fora da vista e sem largura */
    100% { transform: translateX(0) rotate(-45deg) scaleX(1); } /* Move para a posição final e aumenta a largura */
}

/* Animação de deslizamento para dentro */
@keyframes slideIn {
    from {
        opacity: 0; /* Inicia invisível */
        transform: translateX(-20px); /* Inicia deslocado para a esquerda */
    }
    to {
        opacity: 1; /* Termina totalmente visível */
        transform: translateX(0); /* Termina na posição correta */
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsividade */
@media (max-width: 576px) {
    #trocoModal .modal-dialog {
        margin: 10px;
    }
    
    .troco-actions {
        flex-direction: column;
    }
    
    .detail-row .label,
    .detail-row .value {
        font-size: 14px;
    }
    
    .detail-row.troco .value {
        font-size: 20px;
    }
}

/* Substitua os estilos antigos da animação de sucesso por estes */
.success-checkmark {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    position: relative;
}

.check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid white;
}

.icon-line {
    height: 5px;
    background-color: #ffffff;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.icon-line.line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.icon-line.line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

@keyframes icon-line-tip {
    0% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    54% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    70% {
        width: 25px;
        left: 14px;
        top: 46px;
    }
    84% {
        width: 25px;
        left: 14px;
        top: 46px;
    }
    100% {
        width: 25px;
        left: 14px;
        top: 46px;
    }
}

@keyframes icon-line-long {
    0% {
        width: 0;
        right: 46px;
        top: 54px;
    }
    65% {
        width: 0;
        right: 46px;
        top: 54px;
    }
    84% {
        width: 47px;
        right: 8px;
        top: 38px;
    }
    100% {
        width: 47px;
        right: 8px;
        top: 38px;
    }
}

/* Adicione ao seu CSS existente */
.troco-preview {
    margin-top: 15px;
    padding: 10px 15px;
    border-radius: var(--border-radius);
    background: #f8f9fa;
    text-align: left;
    font-size: 18px;
    font-weight: 600;
    transition: all 0.3s ease;
}

#valorPago {
    text-align: left;
    font-size: 24px;
    font-weight: 600;
    color: var(--text-color);
    padding-left: 15px;
}

.input-group-addon {
    font-size: 18px;
    background: #f8f9fa;
    color: #666;
    min-width: 50px;
    text-align: center;
    font-weight: 600;
}

#valorTotal {
    text-align: left;
    font-size: 24px;
    font-weight: 600;
    color: var(--text-color);
    padding-left: 15px;
}

/* Adicione estes estilos */
.summary-section {
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.summary-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #eee;
}

.products-list {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;
}

.product-item:last-child {
    border-bottom: none;
}

.product-details {
    flex: 1;
}

.product-name {
    display: block;
    font-size: 14px;
    color: #2c3e50;
    margin-bottom: 5px;
}

.product-info {
    display: flex;
    gap: 10px;
    font-size: 13px;
    color: #666;
}

.quantity {
    color: #666;
}

.price {
    color: #3498db;
}

.product-total {
    font-weight: 600;
    color: #2c3e50;
}

.totals {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
}

.summary-item.subtotal {
    padding-bottom: 10px;
    margin-bottom: 10px;
    border-bottom: 1px dashed #ddd;
}

.summary-item.total {
    font-size: 18px;
}

.summary-item.total .value {
    color: #2ecc71;
    font-weight: 700;
}

/* Estilização da barra de rolagem */
.products-list::-webkit-scrollbar {
    width: 6px;
}

.products-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.products-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.products-list::-webkit-scrollbar-thumb:hover {
    background: #999;
}
</style>

<script>
function selectPayment(method) {
    // Esconde todos os campos de pagamento
    document.querySelectorAll('.payment-details').forEach(field => {
        field.style.display = 'none';
    });
    
    // Remove a seleção de todos os cards
    document.querySelectorAll('.payment-option-card').forEach(option => {
        option.classList.remove('selected');
    });
    
    // Mostra os campos do método selecionado
    document.getElementById(method + 'Fields').style.display = 'block';
    
    // Adiciona a classe selected ao card selecionado
    document.querySelector(`[onclick="selectPayment('${method}')"]`).classList.add('selected');
    
    // Marca o radio button correspondente
    document.getElementById(method).checked = true;
    
    // Configura os campos obrigatórios baseado no método
    switch(method) {
        case 'dinheiro':
            document.getElementById('valorPago').required = true;
            break;
            
        case 'credito':
        case 'debito':
            // Remove required do valor pago em dinheiro
            document.getElementById('valorPago').required = false;
            
            // Torna os campos de cartão obrigatórios
            const cardFields = document.querySelectorAll(`#${method}Fields input`);
            cardFields.forEach(field => {
                if (!field.readOnly) {
                    field.required = true;
                }
            });
            break;
            
        case 'pix':
            document.getElementById('valorPago').required = false;
            // Gera o QR Code assim que selecionar PIX
            generatePixQRCode();
            break;
    }
    
    // Atualiza o texto do botão de finalizar
    const submitButton = document.querySelector('#formPagamento button[type="submit"]');
    switch(method) {
        case 'dinheiro':
            submitButton.innerHTML = '<i class="fa fa-check"></i> Calcular Troco';
            break;
        case 'credito':
            submitButton.innerHTML = '<i class="fa fa-credit-card"></i> Processar Pagamento';
            break;
        case 'debito':
            submitButton.innerHTML = '<i class="fa fa-credit-card"></i> Processar Pagamento';
            break;
        case 'pix':
            submitButton.innerHTML = '<i class="fa fa-qrcode"></i> Gerar QR Code';
            break;
    }
}

// Adicione esta função para validar campos do cartão
function validateCardFields(method) {
    const cardNumber = document.querySelector(`#${method}Fields .card-number`).value.replace(/\s/g, '');
    const expiry = document.querySelector(`#${method}Fields .card-expiry`).value;
    const cvv = document.querySelector(`#${method}Fields .card-cvv`).value;
    
    if (cardNumber.length !== 16) {
        throw new Error('Número do cartão inválido');
    }
    
    if (!/^\d{2}\/\d{2}$/.test(expiry)) {
        throw new Error('Data de validade inválida');
    }
    
    if (cvv.length !== 3) {
        throw new Error('CVV inválido');
    }
    
    // Validação básica da data de validade
    const [month, year] = expiry.split('/');
    const currentDate = new Date();
    const cardDate = new Date(2000 + parseInt(year), parseInt(month) - 1);
    
    if (cardDate < currentDate) {
        throw new Error('Cartão vencido');
    }
}

// Modifique o event listener do formulário
document.getElementById('formPagamento').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    const valorTotal = parseFloat(document.getElementById('valorTotal').value);
    
    try {
        switch(paymentMethod) {
            case 'dinheiro':
                const valorPago = parseFloat(document.getElementById('valorPago').value || 0);
                
                // Validações do valor pago
                if (!valorPago || isNaN(valorPago)) {
                    throw new Error('Por favor, informe o valor pago.');
                }
                
                if (valorPago < valorTotal) {
                    throw new Error('Valor pago é menor que o valor total da compra!');
                }
                
                // Calcula o troco com precisão de 2 casas decimais
                const troco = Math.round((valorPago - valorTotal) * 100) / 100;
                showTrocoModal(troco);
                return false; // Impede o submit do formulário neste momento
                break;
                
            case 'credito':
            case 'debito':
                // Valida os campos do cartão
                validateCardFields(paymentMethod);
                
                // Mostra loading
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processando...';
                submitBtn.disabled = true;
                
                try {
                    const result = await processPayment(paymentMethod);
                    if (result.status === 'success') {
                        this.submit();
                    }
                } catch (error) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    throw error;
                }
                break;
                
            case 'pix':
                generatePixQRCode();
                const pixStatus = document.querySelector('.pix-status span');
                const pixInterval = setInterval(async () => {
                    try {
                        const result = await processPayment('pix');
                        if (result.status === 'success') {
                            clearInterval(pixInterval);
                            pixStatus.innerHTML = 'Pagamento confirmado!';
                            setTimeout(() => this.submit(), 1500);
                        }
                    } catch (error) {
                        pixStatus.innerHTML = 'Aguardando pagamento...';
                    }
                }, 5000);
                break;
        }
    } catch (error) {
        alert(error.message);
        return false;
    }
});

// Modifique a função showTrocoModal para usar o novo ícone
function showTrocoModal(troco) {
    // Remove modal anterior se existir
    const oldModal = document.getElementById('trocoModal');
    if (oldModal) oldModal.remove();

    // Formata os valores para exibição
    const valorPago = parseFloat(document.getElementById('valorPago').value);
    const valorTotal = parseFloat(document.getElementById('valorTotal').value);

    // Cria o HTML do modal
    const modalHTML = `
        <div class="modal fade" id="trocoModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="troco-container">
                            <div class="troco-header">
                                <div class="success-checkmark">
                                    <div class="check-icon">
                                        <span class="icon-line line-tip"></span>
                                        <span class="icon-line line-long"></span>
                                        <div class="icon-circle"></div>
                                        <div class="icon-fix"></div>
                                    </div>
                                </div>
                                <h3>Pagamento Realizado com Sucesso!</h3>
                            </div>
                            
                            <div class="troco-details">
                                <div class="detail-row">
                                    <span class="label">Valor Total</span>
                                    <span class="value">R$ ${valorTotal.toFixed(2).replace('.', ',')}</span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Valor Pago</span>
                                    <span class="value">R$ ${valorPago.toFixed(2).replace('.', ',')}</span>
                                </div>
                                <div class="detail-row troco">
                                    <span class="label">Troco</span>
                                    <span class="value">R$ ${troco.toFixed(2).replace('.', ',')}</span>
                                </div>
                            </div>
                            
                            <div class="troco-actions">
                                <button type="button" class="btn-finalizar" onclick="finalizarVenda()">
                                    <i class="fa fa-check-circle"></i>
                                    <span>Confirmar e Finalizar</span>
                                </button>
                                <button type="button" class="btn-imprimir">
                                    <i class="fa fa-print"></i>
                                    <span>Imprimir Comprovante</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Adiciona o modal ao documento
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Mostra o modal
    $('#trocoModal').modal({
        backdrop: 'static',
        keyboard: false
    });
}

// Modifique a função finalizarVenda
function finalizarVenda() {
    const form = document.getElementById('formPagamento');
    const valorPago = document.getElementById('valorPago').value;
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    
    // Adiciona os campos necessários ao form
    const campos = {
        'valorPago': valorPago,
        'paymentMethod': paymentMethod
    };
    
    Object.keys(campos).forEach(key => {
        if (!form.querySelector(`input[name="${key}"]`)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = campos[key];
            form.appendChild(input);
        }
    });
    
    // Envia o formulário
    form.submit();
}

// Adicione formatação para o campo de valor pago
document.getElementById('valorPago').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é número
    
    // Converte para centavos (move o ponto decimal)
    value = (parseInt(value) / 100).toFixed(2);
    
    // Atualiza o valor do input com 2 casas decimais
    e.target.value = value;
    
    // Atualiza visual do troco em tempo real (opcional)
    const valorTotal = parseFloat(document.getElementById('valorTotal').value);
    const valorPago = parseFloat(value);
    const troco = valorPago - valorTotal;
    
    // Se quiser mostrar o troco em tempo real, pode adicionar um elemento para isso
    const trocoPreview = document.querySelector('.troco-preview');
    if (trocoPreview) {
        if (troco >= 0) {
            trocoPreview.innerHTML = `Troco: R$ ${troco.toFixed(2).replace('.', ',')}`;
            trocoPreview.style.color = '#2ecc71';
        } else {
            trocoPreview.innerHTML = `Faltam: R$ ${Math.abs(troco).toFixed(2).replace('.', ',')}`;
            trocoPreview.style.color = '#e74c3c';
        }
    }
});

// Adicione um event listener para o foco no campo
document.getElementById('valorPago').addEventListener('focus', function(e) {
    if (e.target.value === '0.00') {
        e.target.value = '';
    }
});

// Adicione um event listener para quando o campo perder o foco
document.getElementById('valorPago').addEventListener('blur', function(e) {
    if (e.target.value === '') {
        e.target.value = '0.00';
    }
});

// Inicializa o valor pago como vazio ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('valorPago').value = '';
});

// Formatação do número do cartão
document.querySelectorAll('.card-number').forEach(input => {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = value;
    });
});

// Formatação da validade do cartão
document.querySelectorAll('.card-expiry').forEach(input => {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0,2) + '/' + value.substring(2);
        }
        e.target.value = value;
    });
});

// Formatação do CVV
document.querySelectorAll('.card-cvv').forEach(input => {
    input.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });
});

// Simulação de processamento de pagamento
async function processPayment(method) {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            const success = Math.random() > 0.1; // 90% de chance de sucesso
            if (success) {
                resolve({ status: 'success', message: 'Pagamento aprovado!' });
            } else {
                reject({ status: 'error', message: 'Falha no processamento do pagamento.' });
            }
        }, 2000);
    });
}

// Função para gerar QR Code do PIX
function generatePixQRCode() {
    // Simulação de geração de QR Code
    const pixCode = 'PIX' + Math.random().toString(36).substring(2, 15);
    document.getElementById('pixCode').value = pixCode;
    
    // Aqui você pode implementar a geração real do QR Code
    // Por enquanto, vamos apenas simular com uma imagem
    const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${pixCode}`;
    document.getElementById('qrCodeImage').src = qrCodeUrl;
}

// Função para copiar código PIX
function copyPixCode() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    document.execCommand('copy');
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fa fa-check"></i> Copiado!';
    setTimeout(() => button.innerHTML = originalText, 2000);
}

// Adicione um evento para limpar a venda temporária ao sair da página
window.addEventListener('beforeunload', function(e) {
    // Cancela o evento padrão
    e.preventDefault();
    
    // Envia requisição para limpar a sessão
    navigator.sendBeacon('clear_session.php');
    
    // Limpa variáveis locais
    localStorage.removeItem('carrinho');
    sessionStorage.clear();
});

// Limpa a sessão quando sair da página
window.addEventListener('beforeunload', function(e) {
    // Cancela o evento padrão
    e.preventDefault();
    
    // Envia requisição para limpar a sessão
    navigator.sendBeacon('clear_session.php');
    
    // Limpa variáveis locais
    localStorage.removeItem('carrinho');
    sessionStorage.clear();
});

// Limpa a sessão quando clicar em voltar ou em qualquer link de navegação
document.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.getAttribute('href');
        
        // Envia requisição para limpar a sessão
        fetch('clear_session.php')
            .then(() => {
                // Redireciona após limpar a sessão
                window.location.href = href;
            })
            .catch(() => {
                // Em caso de erro, redireciona mesmo assim
                window.location.href = href;
            });
    });
});

// Adicione este código para garantir a limpeza ao fechar/recarregar
window.onunload = window.onbeforeunload = function() {
    navigator.sendBeacon('clear_session.php');
};
</script>

<?php
echo $footer;
echo $javascript;
ob_end_flush();
?>

