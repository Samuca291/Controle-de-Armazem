<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/relatorios.class.php';

echo $head;
echo $header;
echo $aside;

$relatorio = new Relatorio();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> Dashboard de Vendas
            <small>Análise de desempenho</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="index.php">Relatórios</a></li>
            <li class="active">Dashboard de Vendas</li>
        </ol>
    </section>

    <section class="content">
        <!-- Filtros -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Filtros</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="period-filters">
                                    <button class="btn btn-filter active" data-period="daily" data-tooltip="Visualizar vendas de hoje">
                                        <i class="fa fa-calendar-day"></i>
                                        <span>Hoje</span>
                                        <div class="filter-indicator"></div>
                                    </button>
                                    <button class="btn btn-filter" data-period="weekly" data-tooltip="Visualizar vendas desta semana">
                                        <i class="fa fa-calendar-week"></i>
                                        <span>Esta Semana</span>
                                        <div class="filter-indicator"></div>
                                    </button>
                                    <button class="btn btn-filter" data-period="monthly" data-tooltip="Visualizar vendas deste mês">
                                        <i class="fa fa-calendar"></i>
                                        <span>Este Mês</span>
                                        <div class="filter-indicator"></div>
                                    </button>
                                    <button class="btn btn-filter" data-period="yearly" data-tooltip="Visualizar vendas deste ano">
                                        <i class="fa fa-calendar-alt"></i>
                                        <span>Este Ano</span>
                                        <div class="filter-indicator"></div>
                                    </button>
                                    <button class="btn btn-filter" data-period="custom" data-tooltip="Escolher período personalizado">
                                        <i class="fa fa-calendar-plus"></i>
                                        <span>Período Personalizado</span>
                                        <div class="filter-indicator"></div>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="filter-info">
                                    <span class="current-filter">
                                        <i class="fa fa-clock"></i>
                                        Período atual: <strong>Hoje</strong>
                                    </span>
                                    <span class="last-update">
                                        <i class="fa fa-sync"></i>
                                        Última atualização: <strong>Agora</strong>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Filtro de data personalizada -->
                        <div id="customDateFilter" class="custom-date-filter" style="display: none;">
                            <form id="customDateForm" class="form-inline">
                                <div class="input-daterange">
                                    <div class="form-group">
                                        <label>Data Inicial</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                            <input type="date" class="form-control" id="startDate" name="startDate">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Data Final</label>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </span>
                                            <input type="date" class="form-control" id="endDate" name="endDate">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="quick-dates">
                                        <button type="button" class="btn btn-default btn-sm" data-quick-date="7">Últimos 7 dias</button>
                                        <button type="button" class="btn btn-default btn-sm" data-quick-date="30">Últimos 30 dias</button>
                                        <button type="button" class="btn btn-default btn-sm" data-quick-date="90">Últimos 90 dias</button>
                                    </div>
                                </div>
                                <div class="form-group pull-right">
                                    <button type="button" class="btn btn-default" data-dismiss="custom-filter">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Aplicar Filtro
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Resumo -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>R$ <span id="total-vendas">0,00</span></h3>
                        <p>Total em Vendas</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><span id="total-produtos">0</span></h3>
                        <p>Produtos Vendidos</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cube"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><span id="media-vendas">0</span></h3>
                        <p id="media-vendas-label">Média de Vendas/Dia</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>R$ <span id="ticket-medio">0,00</span></h3>
                        <p>Ticket Médio</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-ticket-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico de Vendas -->
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-chart-line"></i> Evolução de Vendas
                        </h3>
                        <div class="box-tools pull-right">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default btn-sm" data-chart-type="line">
                                    <i class="fa fa-line-chart"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm" data-chart-type="bar">
                                    <i class="fa fa-bar-chart"></i>
                                </button>
                                <button type="button" class="btn btn-default btn-sm" data-chart-type="area">
                                    <i class="fa fa-area-chart"></i> 
                                </button>
                            </div>
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body chart-responsive">
                        <div class="chart-container" style="position: relative; height:400px;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Produtos -->
            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Top Produtos</h3>
                    </div>
                    <div class="box-body">
                        <div class="top-products-list">
                            <!-- Lista será preenchida via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-list"></i> Detalhamento de Vendas
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="salesTable">
                                <thead>
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Valor Unit.</th>
                                        <th>Total</th>
                                        <th>Vendedor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Será preenchido via JavaScript -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Totais:</th>
                                        <th id="totalQuantidade">0</th>
                                        <th></th>
                                        <th id="totalValor">R$ 0,00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
/* Dashboard Cards */
.small-box {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 20px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
}

.small-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.small-box .inner {
    padding: 25px 20px;
    z-index: 2;
    position: relative;
}

.small-box h3 {
    font-size: 36px;
    font-weight: 700;
    margin: 0;
    white-space: nowrap;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.small-box p {
    font-size: 16px;
    margin: 10px 0 0;
    color: rgba(255,255,255,0.9);
}

.small-box .icon {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 60px;
    color: rgba(255,255,255,0.2);
    z-index: 1;
    transition: all 0.3s ease;
}

.small-box:hover .icon {
    font-size: 70px;
    transform: rotate(12deg);
}

/* Filtros */
.period-filters {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    padding: 5px;
    background: #f8f9fa;
    border-radius: 15px;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
}

.btn-filter {
    position: relative;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    background: white;
    min-width: 160px;
    justify-content: center;
    color: #566573;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.btn-filter i {
    font-size: 16px;
    transition: transform 0.3s ease;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(52,152,219,0.15);
    border-color: rgba(52,152,219,0.3);
    color: #3498db;
}

.btn-filter:hover i {
    transform: scale(1.2);
}

.btn-filter.active {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border-color: transparent;
    box-shadow: 0 5px 15px rgba(52,152,219,0.3);
}

.btn-filter .filter-indicator {
    position: absolute;
    bottom: -2px;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    height: 4px;
    width: 80%;
    background: #3498db;
    border-radius: 2px;
    transition: transform 0.3s ease;
    opacity: 0;
}

.btn-filter.active .filter-indicator {
    transform: translateX(-50%) scaleX(1);
    opacity: 1;
    box-shadow: 0 0 10px rgba(52,152,219,0.5);
}

/* Estilo especial para o botão personalizado */
.btn-filter[data-period="custom"] {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
}

.btn-filter[data-period="custom"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(46,204,113,0.3);
    border-color: rgba(46,204,113,0.3);
}

/* Efeito de pressionar */
.btn-filter:active {
    transform: translateY(1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Animação de loading adaptada para os novos estilos */
.btn-filter.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-filter.loading::after {
    content: '';
    position: absolute;
    right: 15px;
    width: 18px;
    height: 18px;
    border: 2px solid;
    border-radius: 50%;
    border-color: currentColor currentColor currentColor transparent;
    animation: spin 1s linear infinite;
}

/* Responsividade aprimorada */
@media (max-width: 768px) {
    .period-filters {
        padding: 10px;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-filter {
        width: calc(50% - 10px);
        min-width: 140px;
        padding: 10px 15px;
        font-size: 13px;
    }
    
    .btn-filter[data-period="custom"] {
        width: 100%;
        margin-top: 5px;
    }
}

@media (max-width: 480px) {
    .btn-filter {
        width: 100%;
        margin: 2px 0;
    }
}

/* Tooltip personalizado para os botões */
.btn-filter::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-5px);
    padding: 5px 10px;
    background: rgba(0,0,0,0.8);
    color: white;
    font-size: 12px;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.btn-filter:hover::before {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-10px);
}

/* Gráficos e Cards */
.box {
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    background: white;
    margin-bottom: 25px;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}

.box:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.box-header {
    padding: 20px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    background: linear-gradient(to right, #f8f9fa, #ffffff);
}

.box-header .box-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.box-body {
    padding: 25px;
}

/* Top Produtos */
.product-item {
    display: flex;
    align-items: center;
    padding: 15px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.product-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.product-rank {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 15px;
    box-shadow: 0 2px 8px rgba(52,152,219,0.3);
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 14px;
}

.product-sales {
    font-size: 13px;
    color: #7f8c8d;
}

/* Data personalizada */
.custom-date-filter {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 15px;
    box-shadow: inset 0 2px 8px rgba(0,0,0,0.05);
}

.custom-date-filter .form-inline {
    display: flex;
    gap: 20px;
    align-items: flex-end;
}

.custom-date-filter .form-group {
    position: relative;
}

.custom-date-filter label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    display: block;
}

.custom-date-filter input[type="date"] {
    padding: 8px 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    min-width: 200px;
    transition: all 0.3s ease;
}

.custom-date-filter input[type="date"]:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52,152,219,0.2);
    outline: none;
}

/* Responsividade */
@media (max-width: 768px) {
    .period-filters {
        justify-content: center;
    }
    
    .custom-date-filter .form-inline {
        flex-direction: column;
        gap: 15px;
    }
    
    .custom-date-filter input[type="date"] {
        width: 100%;
    }
    
    .small-box h3 {
        font-size: 28px;
    }
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.loading-overlay.active {
    opacity: 1;
    visibility: visible;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos adicionais para os botões de tipo de gráfico */
.btn-group [data-chart-type] {
    padding: 6px 12px;
    transition: all 0.3s ease;
}

.btn-group [data-chart-type].active {
    background-color: #3498db;
    color: white;
    border-color: #2980b9;
}

.chart-container {
    padding: 10px;
    background: linear-gradient(to bottom right, #fff, #f8f9fa);
    border-radius: 8px;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);
}

/* Animação de hover para os botões */
.btn-group [data-chart-type]:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Efeito de pressionar o botão */
.btn-group [data-chart-type]:active {
    transform: translateY(0);
    box-shadow: none;
}

/* Estilos dos filtros aprimorados */
.period-filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.btn-filter {
    position: relative;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: 1px solid #ddd;
    background: white;
    min-width: 140px;
    justify-content: center;
}

.btn-filter:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-filter.active {
    background: #3498db;
    color: white;
    border-color: #2980b9;
}

.btn-filter .filter-indicator {
    position: absolute;
    bottom: -1px;
    left: 50%;
    transform: translateX(-50%) scaleX(0);
    height: 3px;
    width: 80%;
    background: #3498db;
    transition: transform 0.3s ease;
    border-radius: 3px;
}

.btn-filter.active .filter-indicator {
    transform: translateX(-50%) scaleX(1);
}

.filter-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 13px;
}

.filter-info span {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.filter-info i {
    color: #3498db;
}

.custom-date-filter {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #eee;
}

.input-daterange {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.input-daterange .form-group {
    flex: 1;
}

.input-daterange label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 600;
}

.input-daterange .input-group {
    width: 100%;
}

.quick-dates {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.quick-dates .btn {
    padding: 6px 12px;
    border-radius: 15px;
    transition: all 0.2s ease;
}

.quick-dates .btn:hover {
    background: #e9ecef;
}

/* Animação de loading para os botões */
.btn-filter.loading {
    position: relative;
    pointer-events: none;
}

.btn-filter.loading:after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    right: 10px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsividade */
@media (max-width: 768px) {
    .period-filters {
        justify-content: center;
    }
    
    .btn-filter {
        width: calc(50% - 5px);
    }
    
    .input-daterange {
        flex-direction: column;
    }
    
    .quick-dates {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .filter-info {
        margin-top: 15px;
        text-align: center;
    }
}

/* Ajustes para o período personalizado */
#customDateFilter {
    color: #2c3e50; /* Cor do texto principal */
}

#customDateFilter label {
    color: #34495e; /* Cor dos labels */
    font-weight: 600;
}

#customDateFilter input[type="date"] {
    color: #2c3e50; /* Cor do texto dos inputs */
}

.custom-date-filter {
    color: #2c3e50; /* Garantir cor do texto em todo o container */
}

.btn-filter.active span {
    color: #fff; /* Mantém o texto branco apenas nos botões ativos */
}

.btn-filter:not(.active) span {
    color: #2c3e50; /* Cor do texto nos botões não ativos */
}

/* Ajuste para os botões de data rápida */
.quick-dates .btn {
    color: #2c3e50;
}

.quick-dates .btn:hover {
    color: #3498db;
}

/* Estilos para a tabela de vendas */
#salesTable {
    font-size: 14px;
    width: 100%;
    margin-bottom: 1rem;
    background-color: transparent;
}

#salesTable thead th {
    background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    color: #2c3e50;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    padding: 12px;
    border-bottom: 2px solid #3498db;
}

#salesTable tbody tr {
    transition: all 0.3s ease;
}

#salesTable tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.05);
    transform: translateX(5px);
}

#salesTable tbody td {
    padding: 12px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

#salesTable tfoot {
    font-weight: bold;
    background: #f8f9fa;
}

#salesTable tfoot th {
    padding: 12px;
    border-top: 2px solid #3498db;
}

/* Estilos para valores monetários */
.money-value {
    font-family: monospace;
    color: #27ae60;
}

/* Estilos para timestamps */
.timestamp {
    color: #7f8c8d;
    font-size: 0.9em;
}

/* Estilos para as quantidades */
.quantity {
    font-weight: 600;
    color: #2c3e50;
}

/* Animação para novos registros */
@keyframes highlightNew {
    from { background-color: rgba(52, 152, 219, 0.2); }
    to { background-color: transparent; }
}

.new-record {
    animation: highlightNew 1s ease-out;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurações do gráfico
    const chartConfig = {
        line: {
            type: 'line',
            tension: 0.4,
            fill: false
        },
        bar: {
            type: 'bar',
            tension: 0,
            backgroundColor: 'rgba(52, 152, 219, 0.6)'
        },
        area: {
            type: 'line',
            tension: 0.4,
            fill: true
        }
    };

    // Configuração base do gráfico
    const baseConfig = {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Vendas',
                data: [],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                pointBackgroundColor: '#fff',
                pointBorderColor: '#3498db',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#3498db',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animations: {
                tension: {
                    duration: 1000,
                    easing: 'linear'
                }
            },
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Evolução de Vendas',
                    font: {
                        size: 16,
                        weight: 'bold',
                        family: "'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif"
                    },
                    padding: 20,
                    color: '#2c3e50'
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#2c3e50',
                    titleFont: {
                        weight: 'bold',
                        size: 13
                    },
                    bodyColor: '#2c3e50',
                    bodyFont: {
                        size: 12
                    },
                    borderColor: '#ddd',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 6,
                    usePointStyle: true,
                    callbacks: {
                        label: function(context) {
                            return `R$ ${context.parsed.y.toFixed(2).replace('.', ',')}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#7f8c8d',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return 'R$ ' + value.toFixed(2).replace('.', ',');
                        }
                    }
                }
            }
        }
    };

    // Inicialização do gráfico
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, baseConfig);

    // Event listeners para troca de tipo de gráfico
    document.querySelectorAll('[data-chart-type]').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.chartType;
            const config = chartConfig[type];
            
            // Remove classe ativa de todos os botões
            document.querySelectorAll('[data-chart-type]').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Adiciona classe ativa ao botão clicado
            this.classList.add('active');
            
            // Atualiza o tipo do gráfico
            salesChart.config.type = config.type;
            salesChart.data.datasets[0].tension = config.tension;
            salesChart.data.datasets[0].fill = config.fill;
            
            if (config.backgroundColor) {
                salesChart.data.datasets[0].backgroundColor = config.backgroundColor;
            }
            
            salesChart.update();
        });
    });

    // Função para gradiente no fundo do gráfico (área)
    function createGradient() {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(52, 152, 219, 0.3)');
        gradient.addColorStop(1, 'rgba(52, 152, 219, 0)');
        return gradient;
    }

    // Aplica o gradiente quando o tipo é área
    document.querySelector('[data-chart-type="area"]').addEventListener('click', function() {
        salesChart.data.datasets[0].backgroundColor = createGradient();
        salesChart.update();
    });

    // Função para atualizar os dados
    async function updateDashboard(period) {
        try {
            // Mostra indicadores de carregamento
            showLoadingState();
            
            const response = await fetch(`get_sales_data.php?period=${period}`);
            if (!response.ok) {
                throw new Error('Erro na resposta do servidor');
            }
            
            const data = await response.json();
            if (data.error) {
                throw new Error(data.message);
            }
            
            // Atualiza os dados
            updateDashboardData(data);
            
            // Atualiza informações do filtro
            updateFilterInfo(period);
            
            return true;
        } catch (error) {
            console.error('Erro:', error);
            showError('Erro ao atualizar os dados: ' + error.message);
            resetDashboard();
            return false;
        } finally {
            hideLoadingState();
        }
    }

    // Função para mostrar estado de loading
    function showLoadingState() {
        const elements = ['total-vendas', 'total-produtos', 'media-vendas', 'ticket-medio'];
        elements.forEach(id => {
            document.getElementById(id).textContent = 'Carregando...';
        });
        
        // Adiciona classe de loading ao container do gráfico
        document.querySelector('.chart-container').classList.add('loading');
    }

    // Função para esconder estado de loading
    function hideLoadingState() {
        document.querySelector('.chart-container').classList.remove('loading');
    }

    // Função para resetar o dashboard em caso de erro
    function resetDashboard() {
        const elements = {
            'total-vendas': '0,00',
            'total-produtos': '0',
            'media-vendas': '0',
            'ticket-medio': '0,00'
        };
        
        Object.entries(elements).forEach(([id, value]) => {
            document.getElementById(id).textContent = value;
        });
    }

    // Função para atualizar os dados do dashboard
    function updateDashboardData(data) {
        // Atualiza os cards
        document.getElementById('total-vendas').textContent = data.totalVendas;
        document.getElementById('total-produtos').textContent = data.totalProdutos;
        document.getElementById('media-vendas').textContent = data.mediaVendas;
        document.getElementById('ticket-medio').textContent = data.ticketMedio;

        // Atualiza o gráfico com os novos dados
        salesChart.data.labels = data.labels;
        salesChart.data.datasets[0].data = data.values;
        
        // Atualiza o título do gráfico baseado no período selecionado
        let chartTitle = 'Evolução de Vendas';
        if (data.period === 'custom') {
            chartTitle += ` (${data.startDate} até ${data.endDate})`;
        }
        salesChart.options.plugins.title.text = chartTitle;
        
        // Atualiza o gráfico
        salesChart.update();

        // Atualiza a lista de top produtos
        updateTopProducts(data.topProducts);

        // Adicione esta linha
        updateSalesTable(data);
    }

    // Handler para o formulário de data personalizada
    document.getElementById('customDateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        
        if (!startDate || !endDate) {
            alert('Por favor, selecione ambas as datas.');
            return;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            alert('A data inicial não pode ser maior que a data final.');
            return;
        }

        // Mostra indicador de carregamento
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Carregando...';
        submitBtn.disabled = true;
        
        // Busca os dados do período personalizado
        fetch(`get_sales_data.php?period=custom&startDate=${startDate}&endDate=${endDate}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor');
                }
                return response.json();
            })
            .then(data => {
                // Adiciona as datas ao objeto de dados
                data.period = 'custom';
                data.startDate = startDate;
                data.endDate = endDate;
                
                // Atualiza o dashboard com os novos dados
                updateDashboardData(data);
                
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao buscar dados. Por favor, tente novamente.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // Inicialização do datepicker (opcional, se quiser melhorar a seleção de data)
    $(document).ready(function() {
        // Define a data máxima como hoje
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('startDate').max = today;
        document.getElementById('endDate').max = today;
        
        // Define valores iniciais
        document.getElementById('endDate').value = today;
        document.getElementById('startDate').value = today;
    });

    // Função auxiliar para atualizar a lista de top produtos
    function updateTopProducts(products) {
        const topProductsList = document.querySelector('.top-products-list');
        topProductsList.innerHTML = products.map((product, index) => `
            <div class="product-item">
                <div class="product-rank">${index + 1}</div>
                <div class="product-info">
                    <div class="product-name">${product.name}</div>
                    <div class="product-sales">${product.quantity} vendas - R$ ${product.total}</div>
                </div>
            </div>
        `).join('');
    }

    // Adicione esta função dentro do seu evento DOMContentLoaded
    function updateSalesTable(data) {
        const tbody = document.querySelector('#salesTable tbody');
        tbody.innerHTML = '';
        
        let totalQuantidade = 0;
        let totalValor = 0;
        
        data.vendas?.forEach(venda => {
            const tr = document.createElement('tr');
            tr.classList.add('new-record');
            
            tr.innerHTML = `
                <td class="timestamp">${formatDateTime(venda.datareg)}</td>
                <td>${venda.produto}</td>
                <td class="quantity text-center">${venda.quantidade}</td>
                <td class="money-value text-right">R$ ${formatMoney(venda.valor_unit)}</td>
                <td class="money-value text-right">R$ ${formatMoney(venda.valor_total)}</td>
                <td>${venda.vendedor}</td>
            `;
            
            tbody.appendChild(tr);
            
            totalQuantidade += parseInt(venda.quantidade);
            totalValor += parseFloat(venda.valor_total);
        });
        
        // Atualiza os totais
        document.getElementById('totalQuantidade').textContent = totalQuantidade;
        document.getElementById('totalValor').textContent = `R$ ${formatMoney(totalValor)}`;
    }

    function formatDateTime(datetime) {
        const date = new Date(datetime);
        return date.toLocaleString('pt-BR', { 
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatMoney(value) {
        return parseFloat(value).toFixed(2).replace('.', ',');
    }

    // Adicione esta função para atualizar o label da média
    function updateMediaLabel(period) {
        const mediaLabel = document.getElementById('media-vendas-label');
        switch(period) {
            case 'daily':
                mediaLabel.textContent = 'Média de Vendas/Hora';
                break;
            case 'yearly':
                mediaLabel.textContent = 'Média de Vendas/Mês';
                break;
            default:
                mediaLabel.textContent = 'Média de Vendas/Dia';
                break;
        }
    }

    // Botões de datas rápidas
    document.querySelectorAll('[data-quick-date]').forEach(button => {
        button.addEventListener('click', function() {
            const days = parseInt(this.dataset.quickDate);
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - days);
            
            document.getElementById('startDate').value = startDate.toISOString().split('T')[0];
            document.getElementById('endDate').value = endDate.toISOString().split('T')[0];
        });
    });

    // Atualizar informação do filtro atual
    function updateFilterInfo(period) {
        const filterLabel = document.querySelector('.current-filter strong');
        const now = new Date();
        const timeLabel = document.querySelector('.last-update strong');
        
        const periods = {
            daily: 'Hoje',
            weekly: 'Esta Semana',
            monthly: 'Este Mês',
            yearly: 'Este Ano',
            custom: 'Período Personalizado'
        };
        
        // Define "Hoje" como padrão se period não existir
        filterLabel.textContent = periods[period] || periods.daily;
        timeLabel.textContent = now.toLocaleTimeString();
    }

    // Cancelar filtro personalizado
    document.querySelector('[data-dismiss="custom-filter"]').addEventListener('click', function() {
        document.getElementById('customDateFilter').style.display = 'none';
        document.querySelector('[data-period="daily"]').click();
    });

    // Sobrescrever o handler de clique dos filtros existente
    document.querySelectorAll('.period-filters .btn').forEach(button => {
        button.addEventListener('click', async function() {
            const period = this.dataset.period;
            
            // Previne múltiplos cliques durante o loading
            if (this.classList.contains('loading')) return;
            
            // Remove active de todos os botões
            document.querySelectorAll('.period-filters .btn').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('loading');
            });
            
            // Adiciona estados ao botão atual
            this.classList.add('active');
            this.classList.add('loading');
            
            // Toggle do filtro personalizado
            const customFilter = document.getElementById('customDateFilter');
            customFilter.style.display = period === 'custom' ? 'block' : 'none';
            
            if (period !== 'custom') {
                await updateDashboard(period);
                updateMediaLabel(period);
            }
            
            this.classList.remove('loading');
        });
    });

    // Adicione estes estilos
    const style = document.createElement('style');
    style.textContent = `
        .chart-container.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .btn-filter.loading {
            position: relative;
            pointer-events: none;
        }

        .btn-filter.loading::after {
            content: '';
            position: absolute;
            right: 10px;
            width: 16px;
            height: 16px;
            border: 2px solid;
            border-radius: 50%;
            border-color: #fff #fff #fff transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Função para simular o clique no botão "Hoje"
    function initializeDashboard() {
        const dailyButton = document.querySelector('[data-period="daily"]');
        if (dailyButton) {
            dailyButton.click();
        }
    }

    // Inicializa o dashboard com o período "Hoje"
    initializeDashboard();
});
</script>

<?php
echo $footer;
echo $javascript;
?>