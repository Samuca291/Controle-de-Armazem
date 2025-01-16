<?php
// ...existing code...
?>

<!-- Seção de Análise de Vendas -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Análise Detalhada de Vendas</h3>
    </div>
    <div class="box-body">
        <form id="analiseVendasForm" class="form-inline mb-3">
            <div class="form-group">
                <label>De: </label>
                <input type="date" class="form-control" id="dataInicio" name="dataInicio" required>
            </div>
            <div class="form-group ml-2">
                <label>Até: </label>
                <input type="date" class="form-control" id="dataFim" name="dataFim" required>
            </div>
            <button type="submit" class="btn btn-primary ml-2">
                <i class="fa fa-search"></i> Analisar
            </button>
        </form>

        <!-- Cards de Resumo -->
        <div class="row">
            <div class="col-md-4">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="totalVendas">0</h3>
                        <p>Total de Vendas</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 id="totalValor">R$ 0,00</h3>
                        <p>Valor Total</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="mediaVendas">R$ 0,00</h3>
                        <p>Ticket Médio</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-line-chart"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Vendas por Dia</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="vendasPorDia"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Vendas por Hora</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="vendasPorHora"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rankings -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Produtos Mais Vendidos</h3>
                    </div>
                    <div class="box-body">
                        <div id="produtosMaisVendidos"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Melhores Clientes</h3>
                    </div>
                    <div class="box-body">
                        <div id="clientesMaisCompraram"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Formas de Pagamento</h3>
                    </div>
                    <div class="box-body">
                        <div id="formasPagamento"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Detalhada -->
        <div class="box box-default mt-4">
            <div class="box-header with-border">
                <h3 class="box-title">Detalhamento de Vendas</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tabelaVendas">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data/Hora</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Itens</th>
                                <th>Forma Pgto</th>
                                <th>Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Preenchido via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes da Venda -->
<div class="modal fade" id="modalDetalhesVenda">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Preenchido via JavaScript -->
        </div>
    </div>
</div>

<!-- Inclui Chart.js para os gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.getElementById('analiseVendasForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const dataInicio = document.getElementById('dataInicio').value;
    const dataFim = document.getElementById('dataFim').value;
    
    fetch('../../App/Database/analisarVendas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `dataInicio=${dataInicio}&dataFim=${dataFim}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza os cards de resumo
            document.getElementById('totalVendas').textContent = data.resumo.totalVendas;
            document.getElementById('totalValor').textContent = data.resumo.totalValor;
            document.getElementById('mediaVendas').textContent = data.resumo.mediaVendas;
            
            // Atualiza a tabela
            const tbody = document.querySelector('#tabelaItensVendidos tbody');
            tbody.innerHTML = data.itens.map(item => `
                <tr>
                    <td>${item.data}</td>
                    <td>${item.produto}</td>
                    <td>${item.quantidade}</td>
                    <td>R$ ${item.valorUnitario}</td>
                    <td>R$ ${item.total}</td>
                </tr>
            `).join('');
        } else {
            alert('Erro ao buscar dados: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar requisição');
    });
});

// Funções para gerar gráficos e atualizar a interface
</script>

<?php
// ...existing code...
?>
