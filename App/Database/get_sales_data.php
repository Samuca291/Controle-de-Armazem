<?php
require_once '../auth.php';
require_once '../Models/connect.php';

header('Content-Type: application/json');

try {
    $connect = new Connect();
    $period = $_GET['period'] ?? 'daily';
    
    // Define o intervalo de datas baseado no período
    $endDate = date('Y-m-d H:i:s');
    switch($period) {
        case 'weekly':
            $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
            break;
        case 'monthly':
            $startDate = date('Y-m-d H:i:s', strtotime('-30 days'));
            break;
        case 'yearly':
            $startDate = date('Y-m-d H:i:s', strtotime('-1 year'));
            break;
        case 'custom':
            $startDate = $_GET['startDate'] . ' 00:00:00';
            $endDate = $_GET['endDate'] . ' 23:59:59';
            break;
        default: // daily
            $startDate = date('Y-m-d 00:00:00');
            $endDate = date('Y-m-d 23:59:59');
    }

    // Query modificada para mostrar TODAS as vendas, mesmo com produtos/itens excluídos
    $query = "SELECT 
                v.idvendas,
                v.datareg,
                v.quantitens,
                v.valor as valor_total,
                v.iditem,
                v.valor/v.quantitens as valor_unit,
                COALESCE(p.NomeProduto, CONCAT('Produto #', v.iditem, ' (Removido)')) as NomeProduto,
                COALESCE(u.Username, CONCAT('Usuário #', v.idusuario, ' (Removido)')) as vendedor,
                COALESCE(i.ValVendItens, v.valor/v.quantitens) as ValVendItens
              FROM vendas v
              LEFT JOIN itens i ON v.iditem = i.idItens
              LEFT JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
              LEFT JOIN usuario u ON v.idusuario = u.idUser
              WHERE v.datareg BETWEEN ? AND ?
              ORDER BY v.datareg DESC";

    $stmt = $connect->SQL->prepare($query);
    $stmt->bind_param('ss', $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $vendas = [];
    $totalVendas = 0;
    $totalValor = 0;
    $totalItens = 0;

    while($row = $result->fetch_assoc()) {
        $vendas[] = [
            'id' => $row['idvendas'],
            'datareg' => date('d/m/Y H:i', strtotime($row['datareg'])),
            'produto' => $row['NomeProduto'],
            'quantidade' => $row['quantitens'],
            'valor_unit' => number_format($row['valor_unit'], 2, ',', '.'),
            'valor_total' => number_format($row['valor_total'], 2, ',', '.'),
            'vendedor' => $row['vendedor']
        ];
        
        $totalVendas++;
        $totalValor += $row['valor_total'];
        $totalItens += $row['quantitens'];
    }

    $mediaVendas = $totalVendas > 0 ? $totalValor / $totalVendas : 0;

    echo json_encode([
        'success' => true,
        'vendas' => $vendas,
        'resumo' => [
            'totalVendas' => $totalVendas,
            'totalValor' => 'R$ ' . number_format($totalValor, 2, ',', '.'),
            'mediaVendas' => 'R$ ' . number_format($mediaVendas, 2, ',', '.'),
            'totalItens' => $totalItens
        ],
        'period' => $period,
        'dateRange' => [
            'start' => date('d/m/Y', strtotime($startDate)),
            'end' => date('d/m/Y', strtotime($endDate))
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar dados: ' . $e->getMessage()
    ]);
}
