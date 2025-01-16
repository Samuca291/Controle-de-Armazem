<?php
require_once '../../App/auth.php';
require_once '../../App/Models/relatorios.class.php';

header('Content-Type: application/json');

try {
    $period = $_GET['period'] ?? 'daily';
    
    // Melhor tratamento das datas
    $startDate = null;
    $endDate = null;
    
    if (isset($_GET['startDate']) && !empty($_GET['startDate'])) {
        $startDate = date('Y-m-d', strtotime($_GET['startDate']));
    }
    
    if (isset($_GET['endDate']) && !empty($_GET['endDate'])) {
        $endDate = date('Y-m-d', strtotime($_GET['endDate']));
    }
    
    $relatorio = new Relatorio();
    
    // Validação das datas para período personalizado
    if ($period === 'custom') {
        if (!$startDate || !$endDate) {
            $response = array(
                'totalVendas' => '0,00',
                'totalProdutos' => '0',
                'mediaVendas' => '0,00',
                'ticketMedio' => '0,00',
                'labels' => [],
                'values' => [],
                'topProducts' => [],
                'period' => $period
            );
            echo json_encode($response);
            exit;
        }
    }
    
    // Busca os dados
    $dados = $relatorio->getVendasPorPeriodo($period, $startDate, $endDate);
    
    // Formata os dados para o retorno
    $response = array(
        'totalVendas' => number_format($dados['total_vendas'], 2, ',', '.'),
        'totalProdutos' => $dados['total_produtos'],
        'mediaVendas' => number_format($dados['total_vendas'] / count($dados['labels']), 2, ',', '.'),
        'ticketMedio' => $dados['num_vendas'] > 0 ? 
            number_format($dados['total_vendas'] / $dados['num_vendas'], 2, ',', '.') : '0,00',
        'labels' => $dados['labels'],
        'values' => $dados['values'],
        'topProducts' => $dados['produtos'],
        'vendas' => $dados['vendas'], // Adiciona os dados detalhados das vendas
        'period' => $period,
        'startDate' => $startDate,
        'endDate' => $endDate
    );

    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}
?>