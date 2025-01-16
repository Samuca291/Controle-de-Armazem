<?php
require_once '../../App/auth.php';
require_once '../../App/Models/relatorios.class.php';

$relatorio = new Relatorio();

// Busca os dados conforme os filtros
if (isset($_POST['idproduto']) && isset($_POST['statusR'])) {
    $idProduto = $_POST['idproduto'];
    $status = $_POST['statusR'];
    $rows = $relatorio->qtdeItensEstoque($perm, $status, $idProduto);
} elseif (isset($_POST['statusR'])) {
    $status = $_POST['statusR'];
    $rows = $relatorio->qtdeItensEstoque($perm, $status, null);
} else {
    $rows = $relatorio->qtdeItensEstoque($perm);
}

$rows = json_decode($rows, true);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Produtos</title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }

        .company-logo {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }

        .report-subtitle {
            font-size: 14px;
            color: #7f8c8d;
            margin: 5px 0;
        }

        .report-info {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .report-info p {
            margin: 5px 0;
            font-size: 13px;
        }

        .table-container {
            margin: 20px 0;
            overflow-x: auto;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .report-table th {
            background: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        .report-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 13px;
        }

        .report-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .report-table tr:hover {
            background: #f1f1f1;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .status-em-estoque {
            background: #2ecc71;
        }

        .status-baixo {
            background: #f1c40f;
        }

        .status-sem-estoque {
            background: #e74c3c;
        }

        .totals-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #ddd;
        }

        .total-item:last-child {
            border-bottom: none;
            font-weight: bold;
        }

        .report-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #3498db;
            font-size: 12px;
            color: #7f8c8d;
            text-align: center;
        }

        .page-number {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #7f8c8d;
        }

        @media print {
            .no-print {
                display: none;
            }
            
            .page-break {
                page-break-before: always;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .print-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">Imprimir Relatório</button>

    <div class="report-header">
        <img src="../../dist/img/logo.png" alt="Logo" class="company-logo">
        <h1 class="report-title">Relatório de Produtos em Estoque</h1>
        <p class="report-subtitle">Gerado em <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

    <div class="report-info">
        <p><strong>Filtros aplicados:</strong></p>
        <p>Status: <?php echo isset($_POST['statusR']) ? ($_POST['statusR'] == '1' ? 'Ativo' : 'Inativo') : 'Todos'; ?></p>
        <p>Produto: <?php echo isset($_POST['idproduto']) && !empty($_POST['idproduto']) ? 'Específico' : 'Todos'; ?></p>
    </div>

    <div class="table-container">
        <table class="report-table">
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
                $totalItens = 0;
                $totalEstoque = 0;
                $totalVendidos = 0;

                if ($rows) {
                    foreach ($rows as $row) {
                        if (isset($row['QuantItens'])) {
                            $qi = intval($row['QuantItens']);
                            $qiv = intval($row['QuantItensVend']);
                            $estoque = $qi - $qiv;
                            
                            $totalItens += $qi;
                            $totalEstoque += $estoque;
                            $totalVendidos += $qiv;
                            
                            // Define o status
                            if ($estoque <= 0) {
                                $statusClass = 'status-sem-estoque';
                                $statusText = 'Sem Estoque';
                            } elseif ($estoque <= 10) {
                                $statusClass = 'status-baixo';
                                $statusText = 'Estoque Baixo';
                            } else {
                                $statusClass = 'status-em-estoque';
                                $statusText = 'Em Estoque';
                            }
                            
                            echo "<tr>
                                <td>{$row['Produto_CodRefProduto']}</td>
                                <td>{$row['NomeProduto']}</td>
                                <td>{$qi}</td>
                                <td>{$qiv}</td>
                                <td>{$estoque}</td>
                                <td><span class='status-badge {$statusClass}'>{$statusText}</span></td>
                            </tr>";
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="totals-section">
        <div class="total-item">
            <span>Total de Itens Comprados:</span>
            <span><?php echo $totalItens; ?></span>
        </div>
        <div class="total-item">
            <span>Total de Itens Vendidos:</span>
            <span><?php echo $totalVendidos; ?></span>
        </div>
        <div class="total-item">
            <span>Total em Estoque:</span>
            <span><?php echo $totalEstoque; ?></span>
        </div>
    </div>

    <div class="report-footer">
        <p>Relatório gerado por: <?php echo isset($_SESSION['Username']) ? $_SESSION['Username'] : 'Sistema'; ?></p>
        <p>Este é um documento oficial da empresa. Todos os direitos reservados.</p>
    </div>

    <div class="page-number"></div>

    <script>
        // Adiciona números de página
        window.onload = function() {
            var pages = document.getElementsByClassName('page-number');
            for(var i = 0; i < pages.length; i++) {
                pages[i].textContent = 'Página ' + (i + 1);
            }
        }
    </script>
</body>
</html> 