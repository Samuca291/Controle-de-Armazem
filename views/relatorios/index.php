<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/relatorios.class.php';

echo $head;
echo $header;
echo $aside;
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bar-chart"></i> Relatório de Vendas
            <small>Análise de produtos e estoque</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Relatórios</li>
        </ol>
    </section>

    <section class="content">
        <?php require '../../layout/alert.php'; ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-cubes"></i> Controle de Produtos
                        </h3>
                    </div>

                    <div class="box-body">
                        <?php if ($perm == 1): ?>
                            <!-- Filtros -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="filter-panel">
                                        <form action="" method="POST" class="form-inline">
                                            <div class="form-group">
                                                <label>Produto</label>
                                                <select name="produto" class="form-control select2">
                                                    <option value="">Todos os Produtos</option>
                                                    <?php
                                                    $relatorio = new Relatorio();
                                                    $resps = $relatorio->selectProduto($perm);
                                                    $resps = json_decode($resps, true);
                                                    foreach ($resps as $resp) {
                                                        if (isset($resp['CodRefProduto'])) {
                                                            echo '<option value="' . $resp['CodRefProduto'] . '">' . $resp['NomeProduto'] . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="1">Ativo</option>
                                                    <option value="0">Inativo</option>
                                                    <option value="">Todos</option>
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-primary">
                                                <i class="fa fa-search"></i> Filtrar
                                            </button>
                                        </form>

                                        <form action="imprimir_relatorio.php" method="post" target="_blank" class="export-form">
                                            <input type="hidden" name="idproduto" value="<?php echo isset($_POST['produto']) ? $_POST['produto'] : ''; ?>">
                                            <input type="hidden" name="statusR" value="<?php echo isset($_POST['status']) ? $_POST['status'] : ''; ?>">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa fa-print"></i> Imprimir Relatório
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabela de Resultados -->
                            <div class="table-responsive">
                                <table id="relatorio-table" class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Produto</th>
                                            <th>Qtde Comprada</th>
                                            <th>Qtde Vendida</th>
                                            <th>Estoque Atual</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (isset($_POST['produto']) || isset($_POST['status'])) {
                                            $idProduto = isset($_POST['produto']) ? $_POST['produto'] : null;
                                            $status = isset($_POST['status']) ? $_POST['status'] : null;
                                            $rows = $relatorio->qtdeItensEstoque($perm, $status, $idProduto);
                                        } else {
                                            $rows = $relatorio->qtdeItensEstoque($perm);
                                        }

                                        $rows = json_decode($rows, true);
                                        if ($rows) {
                                            foreach ($rows as $row) {
                                                if (isset($row['QuantItens'])) {
                                                    $qi = intval($row['QuantItens']);
                                                    $qiv = intval($row['QuantItensVend']);
                                                    $estoque = $qi - $qiv;
                                                    
                                                    // Define as classes e textos baseado no estoque
                                                    $statusClass = $estoque > 0 ? 'success' : 'danger';
                                                    $statusText = $estoque > 0 ? 'Em Estoque' : 'Sem Estoque';
                                                    
                                                    // Adiciona classe de alerta para estoque baixo
                                                    if ($estoque > 0 && $estoque <= 10) {
                                                        $statusClass = 'warning';
                                                        $statusText = 'Estoque Baixo';
                                                    }
                                                    
                                                    echo "<tr>
                                                        <td>{$row['Produto_CodRefProduto']}</td>
                                                        <td>{$row['NomeProduto']}</td>
                                                        <td><span class='badge bg-blue'>{$qi}</span></td>
                                                        <td><span class='badge bg-yellow'>{$qiv}</span></td>
                                                        <td><span class='badge bg-" . ($estoque <= 10 ? 'orange' : 'green') . "'>{$estoque}</span></td>
                                                        <td><span class='label label-{$statusClass}'>{$statusText}</span></td>
                                                    </tr>";
                                                }
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center'>Nenhum produto encontrado</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="header-actions">
                                <div class="dashboard-link-container">
                                    <a href="dashboard_vendas.php" class="btn btn-primary">
                                        <i class="fa fa-line-chart"></i> Dashboard de Vendas
                                    </a>
                                    <span class="dashboard-description">
                                        Acesse o painel de análise detalhada de vendas com gráficos e métricas em tempo real
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i>
                                Você não tem permissão para visualizar este conteúdo!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Estilos gerais */
.content-wrapper {
    background: #f4f6f9;
    padding: 20px;
}

.content-header {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.content-header h1 {
    margin: 0;
    font-size: 24px;
    color: #2c3e50;
}

.content-header small {
    color: #7f8c8d;
    font-size: 14px;
}

/* Box principal */
.box {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: none;
    margin-bottom: 30px;
}

.box-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.box-title {
    font-size: 18px;
    color: #2c3e50;
}

/* Painel de filtros */
.filter-panel {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.form-inline {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-size: 13px;
    color: #666;
    font-weight: 600;
}

.form-control {
    min-width: 200px;
    height: 38px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.select2-container {
    min-width: 250px;
}

/* Botões */
.btn {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #3498db;
    border: none;
}

.btn-success {
    background: #2ecc71;
    border: none;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Tabela */
.table-responsive {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

#relatorio-table {
    margin: 0;
}

#relatorio-table thead th {
    background: #f8f9fa;
    border-bottom: 2px solid #3498db;
    color: #2c3e50;
    font-weight: 600;
    padding: 12px;
}

#relatorio-table tbody td {
    padding: 12px;
    vertical-align: middle;
}

/* Badges e Labels */
.badge {
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: 600;
    font-size: 12px;
}

.bg-blue { background: #3498db; }
.bg-yellow { background: #f1c40f; }
.bg-green { background: #2ecc71; }

.label {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.label-success { background: #2ecc71; }
.label-danger { background: #e74c3c; }

/* Responsividade */
@media (max-width: 768px) {
    .filter-panel {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-inline {
        flex-direction: column;
    }
    
    .form-group {
        width: 100%;
    }
    
    .form-control {
        width: 100%;
    }
    
    .export-form {
        width: 100%;
    }
    
    .export-form button {
        width: 100%;
    }
}

/* Animações */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.box {
    animation: fadeIn 0.5s ease-out;
}

/* Melhorias na tabela */
#relatorio-table tbody tr {
    transition: all 0.3s ease;
}

#relatorio-table tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.01);
}

/* Estilização da barra de rolagem */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #bdc3c7;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #95a5a6;
}

/* Cores para os badges */
.bg-orange {
    background-color: #f39c12 !important;
}

/* Melhorias na visualização da tabela */
.table > tbody > tr > td {
    vertical-align: middle;
}

/* Animação para linhas da tabela */
.table > tbody > tr {
    transition: all 0.3s ease;
}

/* Efeito hover nas linhas */
.table > tbody > tr:hover {
    background-color: #f5f7fa;
    transform: translateX(5px);
}

/* Estilo para mensagem de nenhum resultado */
.text-center {
    padding: 20px;
    color: #666;
    font-style: italic;
}

.dashboard-link-container {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.dashboard-link-container:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.dashboard-link-container .btn-primary {
    padding: 12px 24px;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #3498db;
    border: none;
    box-shadow: 0 4px 6px rgba(52, 152, 219, 0.2);
    transition: all 0.3s ease;
}

.dashboard-link-container .btn-primary:hover {
    background: #2980b9;
    box-shadow: 0 6px 8px rgba(52, 152, 219, 0.3);
    transform: translateY(-2px);
}

.dashboard-description {
    color: #555;
    font-size: 15px;
    line-height: 1.5;
    max-width: 600px;
}

/* Adicione um ícone de seta para indicar ação */
.dashboard-link-container .btn-primary i {
    margin-right: 8px;
    transition: transform 0.3s ease;
}

.dashboard-link-container .btn-primary:hover i {
    transform: translateX(3px);
}
</style>

<script>
// Inicialização do Select2
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Selecione um produto",
        allowClear: true
    });
    
    // Inicialização do DataTables
    $('#relatorio-table').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
        },
        "pageLength": 25,
        "order": [[0, "asc"]],
        "responsive": true
    });
});

// Atualiza a tabela automaticamente quando mudar a seleção
$(document).ready(function() {
    $('select[name="produto"], select[name="status"]').change(function() {
        $(this).closest('form').submit();
    });

    // Inicializa Select2 com busca
    $('.select2').select2({
        placeholder: "Selecione um produto",
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Nenhum produto encontrado";
            }
        }
    });
});
</script>

<?php
echo $footer;
echo $javascript;
?>