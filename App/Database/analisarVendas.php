<?php
require_once '../auth.php';
require_once '../../App/Models/connect.php';

header('Content-Type: application/json');

if (isset($_POST['dataInicio']) && isset($_POST['dataFim'])) {
    try {
        $connect = new Connect();
        
        // Query principal com mais detalhes
        $query = "SELECT 
                    v.idVendas,
                    v.DataVenda,
                    TIME(v.DataVenda) as HoraVenda,
                    p.NomeProduto,
                    p.CodRefProduto,
                    f.NomeFabricante,
                    vi.QtdeVendida,
                    vi.ValorVendido,
                    (vi.QtdeVendida * vi.ValorVendido) as Total,
                    u.NomeUsuario as Vendedor,
                    c.NomeCliente,
                    fp.DescFormaPgto as FormaPagamento,
                    v.Observacoes
                 FROM vendas v
                 INNER JOIN vendas_itens vi ON v.idVendas = vi.Vendas_idVendas
                 INNER JOIN itens i ON vi.Itens_idItens = i.idItens
                 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
                 INNER JOIN fabricante f ON i.Fabricante_idFabricante = f.idFabricante
                 INNER JOIN usuario u ON v.Usuario_idUsuario = u.idUsuario
                 LEFT JOIN cliente c ON v.Cliente_idCliente = c.idCliente
                 LEFT JOIN formapgto fp ON v.FormaPgto_idFormaPgto = fp.idFormaPgto
                 WHERE v.DataVenda BETWEEN ? AND ?
                 ORDER BY v.DataVenda DESC, v.idVendas";
                 
        $stmt = $connect->SQL->prepare($query);
        $stmt->bind_param('ss', $_POST['dataInicio'], $_POST['dataFim']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Inicializa arrays para análises
        $vendas = [];
        $resumo = [
            'totalVendas' => 0,
            'totalValor' => 0,
            'totalItens' => 0,
            'vendasPorDia' => [],
            'vendasPorHora' => [],
            'produtosMaisVendidos' => [],
            'clientesMaisCompraram' => [],
            'formasPagamento' => []
        ];
        
        // Processa os resultados
        while ($row = $result->fetch_assoc()) {
            // Agrupa vendas por ID
            if (!isset($vendas[$row['idVendas']])) {
                $vendas[$row['idVendas']] = [
                    'id' => $row['idVendas'],
                    'data' => date('d/m/Y', strtotime($row['DataVenda'])),
                    'hora' => date('H:i', strtotime($row['HoraVenda'])),
                    'cliente' => $row['NomeCliente'] ?: 'Cliente não identificado',
                    'vendedor' => $row['Vendedor'],
                    'formaPagamento' => $row['FormaPagamento'],
                    'observacoes' => $row['Observacoes'],
                    'itens' => [],
                    'total' => 0
                ];
            }
            
            // Adiciona item à venda
            $vendas[$row['idVendas']]['itens'][] = [
                'produto' => $row['NomeProduto'],
                'codigo' => $row['CodRefProduto'],
                'fabricante' => $row['NomeFabricante'],
                'quantidade' => $row['QtdeVendida'],
                'valorUnitario' => $row['ValorVendido'],
                'total' => $row['Total']
            ];
            
            $vendas[$row['idVendas']]['total'] += $row['Total'];
            
            // Atualiza resumos
            $resumo['totalVendas']++;
            $resumo['totalValor'] += $row['Total'];
            $resumo['totalItens'] += $row['QtdeVendida'];
            
            // Vendas por dia
            $dia = date('Y-m-d', strtotime($row['DataVenda']));
            if (!isset($resumo['vendasPorDia'][$dia])) {
                $resumo['vendasPorDia'][$dia] = 0;
            }
            $resumo['vendasPorDia'][$dia] += $row['Total'];
            
            // Vendas por hora
            $hora = date('H', strtotime($row['HoraVenda']));
            if (!isset($resumo['vendasPorHora'][$hora])) {
                $resumo['vendasPorHora'][$hora] = 0;
            }
            $resumo['vendasPorHora'][$hora] += $row['Total'];
            
            // Produtos mais vendidos
            if (!isset($resumo['produtosMaisVendidos'][$row['NomeProduto']])) {
                $resumo['produtosMaisVendidos'][$row['NomeProduto']] = 0;
            }
            $resumo['produtosMaisVendidos'][$row['NomeProduto']] += $row['QtdeVendida'];
            
            // Clientes que mais compraram
            if ($row['NomeCliente']) {
                if (!isset($resumo['clientesMaisCompraram'][$row['NomeCliente']])) {
                    $resumo['clientesMaisCompraram'][$row['NomeCliente']] = 0;
                }
                $resumo['clientesMaisCompraram'][$row['NomeCliente']] += $row['Total'];
            }
            
            // Formas de pagamento
            if (!isset($resumo['formasPagamento'][$row['FormaPagamento']])) {
                $resumo['formasPagamento'][$row['FormaPagamento']] = 0;
            }
            $resumo['formasPagamento'][$row['FormaPagamento']] += $row['Total'];
        }
        
        // Ordena as análises
        arsort($resumo['produtosMaisVendidos']);
        arsort($resumo['clientesMaisCompraram']);
        arsort($resumo['formasPagamento']);
        
        // Formata valores monetários
        $resumo['totalValorFormatado'] = 'R$ ' . number_format($resumo['totalValor'], 2, ',', '.');
        $resumo['mediaVendas'] = $resumo['totalVendas'] > 0 ? 
            'R$ ' . number_format($resumo['totalValor'] / $resumo['totalVendas'], 2, ',', '.') : 'R$ 0,00';
        
        echo json_encode([
            'success' => true,
            'resumo' => $resumo,
            'vendas' => array_values($vendas)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Parâmetros inválidos'
    ]);
}
