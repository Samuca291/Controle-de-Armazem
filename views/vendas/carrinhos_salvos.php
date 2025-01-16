<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/vendas.class.php';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'salvar_carrinho':
                if (!empty($_SESSION['itens'])) {
                    $nome = $_POST['nome_carrinho'];
                    $data = date('Y-m-d H:i:s');
                    
                    if (!isset($_SESSION['carrinhos_salvos'])) {
                        $_SESSION['carrinhos_salvos'] = [];
                    }
                    
                    $_SESSION['carrinhos_salvos'][] = [
                        'id' => uniqid(), // Identificador único
                        'nome' => $nome,
                        'itens' => $_SESSION['itens'],
                        'data' => $data
                    ];
                    
                    // Limpar carrinho atual
                    unset($_SESSION['itens']);
                    $_SESSION['msg'] = '<div class="alert alert-success">Carrinho salvo com sucesso!</div>';
                }
                break;
                
            case 'recuperar_carrinho':
                $id_carrinho = $_POST['id_carrinho'];
                
                if (isset($_SESSION['carrinhos_salvos'])) {
                    foreach ($_SESSION['carrinhos_salvos'] as $key => $carrinho) {
                        if ($carrinho['id'] === $id_carrinho) {
                            $_SESSION['itens'] = $carrinho['itens'];
                            unset($_SESSION['carrinhos_salvos'][$key]);
                            $_SESSION['msg'] = '<div class="alert alert-success">Carrinho recuperado com sucesso!</div>';
                            header('Location: index.php');
                            exit;
                        }
                    }
                }
                break;
                
            case 'excluir_carrinho':
                $id_carrinho = $_POST['id_carrinho'];
                
                if (isset($_SESSION['carrinhos_salvos'])) {
                    foreach ($_SESSION['carrinhos_salvos'] as $key => $carrinho) {
                        if ($carrinho['id'] === $id_carrinho) {
                            unset($_SESSION['carrinhos_salvos'][$key]);
                            $_SESSION['msg'] = '<div class="alert alert-success">Carrinho excluído com sucesso!</div>';
                            break;
                        }
                    }
                }
                break;
        }
    }
}

echo $head;
echo $header;
echo $aside;
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Carrinhos Salvos
            <small>Gerenciar carrinhos temporários</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="index.php">Vendas</a></li>
            <li class="active">Carrinhos Salvos</li>
        </ol>
    </section>

    <section class="content">
        <?php 
        if (isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
        ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-shopping-cart"></i> Carrinhos Salvos
                        </h3>
                        
                        <?php if (!empty($_SESSION['itens'])): ?>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalSalvarCarrinho">
                                <i class="fa fa-save"></i> Salvar Carrinho Atual
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Data</th>
                                        <th>Qtd. Total de Itens</th>
                                        <th>Itens Diferentes</th>
                                        <th>Valor Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($_SESSION['carrinhos_salvos'])) {
                                        echo '<tr><td colspan="6" class="text-center">Nenhum carrinho salvo</td></tr>';
                                    } else {
                                        foreach ($_SESSION['carrinhos_salvos'] as $carrinho) {
                                            $total = 0;
                                            $qtdTotalItens = 0;
                                            
                                            // Calcular total e quantidade de itens
                                            foreach ($carrinho['itens'] as $item) {
                                                $total += $item['valor'];
                                                $qtdTotalItens += $item['qtde']; // Soma a quantidade de cada item
                                            }
                                            
                                            echo "<tr>
                                                <td>{$carrinho['nome']}</td>
                                                <td>" . date('d/m/Y H:i', strtotime($carrinho['data'])) . "</td>
                                                <td><strong>{$qtdTotalItens}</strong> unidade(s)</td>
                                                <td>" . count($carrinho['itens']) . " produto(s)</td>
                                                <td>R$ " . number_format($total, 2, ',', '.') . "</td>
                                                <td>
                                                    <form method='POST' style='display: inline;'>
                                                        <input type='hidden' name='action' value='recuperar_carrinho'>
                                                        <input type='hidden' name='id_carrinho' value='{$carrinho['id']}'>
                                                        <button type='submit' class='btn btn-xs btn-primary' title='Recuperar Carrinho'>
                                                            <i class='fa fa-shopping-cart'></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form method='POST' style='display: inline;' onsubmit='return confirm(\"Tem certeza que deseja excluir este carrinho?\");'>
                                                        <input type='hidden' name='action' value='excluir_carrinho'>
                                                        <input type='hidden' name='id_carrinho' value='{$carrinho['id']}'>
                                                        <button type='submit' class='btn btn-xs btn-danger' title='Excluir Carrinho'>
                                                            <i class='fa fa-trash'></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type='button' class='btn btn-xs btn-info' 
                                                            onclick='mostrarDetalhes(" . json_encode($carrinho['itens']) . ")'
                                                            title='Ver Detalhes'>
                                                        <i class='fa fa-eye'></i>
                                                    </button>
                                                </td>
                                            </tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Salvar Carrinho -->
<div class="modal fade" id="modalSalvarCarrinho" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Salvar Carrinho</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nome_carrinho">Nome do Carrinho</label>
                        <input type="text" class="form-control" id="nome_carrinho" name="nome_carrinho" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" value="salvar_carrinho">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Detalhes do Carrinho -->
<div class="modal fade" id="modalDetalhesCarrinho" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Detalhes do Carrinho</h4>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detalhesCarrinhoBody">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarDetalhes(itens) {
    let html = '';
    let total = 0;
    
    for (const [id, item] of Object.entries(itens)) {
        const subtotal = parseFloat(item.valor);
        total += subtotal;
        
        html += `
            <tr>
                <td>${item.nameproduto}</td>
                <td>${item.qtde}</td>
                <td>R$ ${(subtotal / item.qtde).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                <td>R$ ${subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
            </tr>
        `;
    }
    
    // Adiciona linha do total
    html += `
        <tr class="info">
            <td colspan="3"><strong>Total</strong></td>
            <td><strong>R$ ${total.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</strong></td>
        </tr>
    `;
    
    document.getElementById('detalhesCarrinhoBody').innerHTML = html;
    $('#modalDetalhesCarrinho').modal('show');
}
</script>

<style>
.box {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table > tbody > tr > td {
    vertical-align: middle;
}

.btn-xs {
    margin: 0 2px;
}

.modal-content {
    border-radius: 8px;
}

.modal-header {
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.form-control:focus {
    border-color: #3c8dbc;
    box-shadow: none;
}

/* Estilos adicionais para a tabela */
.table > tbody > tr > td {
    vertical-align: middle;
}

.table > tbody > tr > td:nth-child(3),
.table > tbody > tr > td:nth-child(4) {
    text-align: center;
}

.table > tbody > tr > td:nth-child(5) {
    text-align: right;
    font-weight: bold;
}

/* Estilo para o modal de detalhes */
#modalDetalhesCarrinho .table {
    margin-bottom: 0;
}

#modalDetalhesCarrinho .info {
    background-color: #f9f9f9;
}

#modalDetalhesCarrinho .info td {
    font-size: 1.1em;
}
</style>

<?php
echo $footer;
echo $javascript;
?> 