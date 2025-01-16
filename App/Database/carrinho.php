<?php
require_once '../auth.php';
require_once '../../App/Models/vendas.class.php';
require_once '../../App/Models/connect.php';
require_once '../../views/templates/cart_template.php';

// Previne duplicação de requisições
session_write_close();
session_start();

header('Content-Type: application/json');

if (isset($_POST['prodSubmit'])) {
    // Adiciona um lock para prevenir requisições simultâneas
    $lockFile = sys_get_temp_dir() . '/cart_lock_' . session_id();
    $fp = fopen($lockFile, 'w+');
    
    if (!flock($fp, LOCK_EX | LOCK_NB)) {
        echo json_encode([
            'success' => false,
            'message' => 'Operação em andamento, aguarde...'
        ]);
        exit;
    }

    // Trata a adição por código de barras
    if ($_POST['prodSubmit'] == 'barcode') {
        $codigo = trim(filter_var($_POST['codigo'], FILTER_SANITIZE_STRING));
        
        // Valida código de barras
        if (empty($codigo)) {
            echo json_encode([
                'success' => false,
                'message' => 'Digite um código de barras válido',
                'alert' => true,
                'alertType' => 'warning'
            ]);
            exit;
        }

        try {
            // Busca o item pelo código de barras com prepared statement
            $query = "SELECT i.*, p.NomeProduto, p.CodRefProduto, f.NomeFabricante 
                     FROM itens i 
                     INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
                     INNER JOIN fabricante f ON i.Fabricante_idFabricante = f.idFabricante
                     WHERE i.CodigoBarras = ? AND i.ItensAtivo = 1
                     LIMIT 1";
                  
            $stmt = $connect->SQL->prepare($query);
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta");
            }

            $stmt->bind_param("s", $codigo);
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta");
            }

            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode([
                    'success' => false,
                    'message' => "Código de barras não encontrado: $codigo",
                    'alert' => true,
                    'alertType' => 'error',
                    'code' => $codigo
                ]);
                exit;
            }

            $item = $result->fetch_assoc();
            
            // Verifica se o item está ativo
            if (!$item['ItensAtivo']) {
                throw new Exception("Item está inativo no sistema");
            }

            // Verifica estoque
            $estoqueDisponivel = $item['QuantItens'] - $item['QuantItensVend'];
            
            if ($estoqueDisponivel <= 0) {
                throw new Exception("Item sem estoque disponível: {$item['NomeProduto']}");
            }

            // Verifica se o item já está no carrinho
            $quantidadeNoCarrinho = isset($_SESSION['itens'][$item['idItens']]) 
                ? $_SESSION['itens'][$item['idItens']]['qtde'] 
                : 0;

            // Adiciona +1 à quantidade se já existir no carrinho
            $novaQuantidade = $quantidadeNoCarrinho + 1;

            // Verifica se a nova quantidade não excede o estoque
            if ($novaQuantidade > $estoqueDisponivel) {
                throw new Exception("Quantidade excederia o estoque disponível ({$estoqueDisponivel})");
            }

            // Atualiza o item no carrinho diretamente
            $carrinho_item = array(
                'idItem' => $item['idItens'],
                'qtde' => 1, // Sempre adiciona 1 unidade via código de barras
                'nameproduto' => $item['NomeProduto'],
                'codRefProduto' => $item['CodRefProduto'],
                'fabricante' => $item['NomeFabricante'],
                'valor' => $item['ValVendItens'],
                'valorUnitario' => $item['ValVendItens'],
                'estoqueDisponivel' => $estoqueDisponivel,
                'Fabricante_idFabricante' => $item['Fabricante_idFabricante'],
                'Produto_CodRefProduto' => $item['Produto_CodRefProduto']
            );

            // Adiciona ou atualiza o item no carrinho
            if (!isset($_SESSION['itens'][$item['idItens']])) {
                $_SESSION['itens'][$item['idItens']] = $carrinho_item;
            } else {
                $_SESSION['itens'][$item['idItens']]['qtde']++;
                $_SESSION['itens'][$item['idItens']]['valor'] = 
                    $_SESSION['itens'][$item['idItens']]['qtde'] * $item['ValVendItens'];
            }

            // Substitua todas as ocorrências de geração de HTML do carrinho por:
            $cartData = generateCartHtml($_SESSION['itens']);

            echo json_encode([
                'success' => true,
                'message' => "Item adicionado: {$item['NomeProduto']}",
                'cartHtml' => $cartData['html'],
                'total' => number_format($cartData['total'], 2, ',', '.'),
                'pkCount' => count($_SESSION['itens'])
            ]);
            
            exit;

        } catch (Exception $e) {
            error_log("Erro ao processar código de barras: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'alert' => true,
                'alertType' => 'error',
                'code' => $codigo
            ]);
            exit;
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    // Continua com o código existente para adicionar ao carrinho
    if ($_POST['prodSubmit'] == "carrinho") {
        // Adiciona debug log
        error_log('Requisição recebida: ' . json_encode($_POST));
        
        $idItem = filter_var($_POST['idItem'], FILTER_SANITIZE_NUMBER_INT);
        $qtde = filter_var($_POST['qtde'], FILTER_SANITIZE_NUMBER_INT);
        
        // Log do estado atual da sessão
        error_log('Estado atual do carrinho: ' . json_encode($_SESSION['itens'] ?? []));
        
        if ($qtde <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Quantidade inválida'
            ]);
            exit;
        }

        $connect = new Connect();
        
        // Query ajustada para pegar todos os dados necessários das tabelas relacionadas
        $query = "SELECT i.*, p.NomeProduto, p.CodRefProduto, f.NomeFabricante 
                  FROM itens i 
                  INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
                  INNER JOIN fabricante f ON i.Fabricante_idFabricante = f.idFabricante
                  WHERE i.idItens = ? AND i.ItensAtivo = 1";
                  
        $stmt = $connect->SQL->prepare($query);
        $stmt->bind_param("i", $idItem);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();

        if ($item) {
            // Cálculo do estoque disponível
            $estoqueDisponivel = $item['QuantItens'] - $item['QuantItensVend'];
            
            if ($estoqueDisponivel < $qtde) {
                echo json_encode([
                    'success' => false,
                    'message' => "Quantidade solicitada maior que o estoque disponível ($estoqueDisponivel)"
                ]);
                exit;
            }

            // Item para o carrinho com dados completos
            $carrinho_item = array(
                'idItem' => $idItem,
                'qtde' => $qtde,
                'nameproduto' => $item['NomeProduto'],
                'codRefProduto' => $item['CodRefProduto'],
                'fabricante' => $item['NomeFabricante'],
                'valor' => $item['ValVendItens'] * $qtde,
                'valorUnitario' => $item['ValVendItens'],
                'estoqueDisponivel' => $estoqueDisponivel,
                'Fabricante_idFabricante' => $item['Fabricante_idFabricante'],
                'Produto_CodRefProduto' => $item['Produto_CodRefProduto']
            );

            // Adiciona ou atualiza item no carrinho
            if (!isset($_SESSION['itens'][$idItem])) {
                $_SESSION['itens'][$idItem] = $carrinho_item;
                error_log('Novo item adicionado: ' . json_encode($carrinho_item));
            } else {
                // Soma a nova quantidade com a quantidade existente
                $novaQtde = $_SESSION['itens'][$idItem]['qtde'] + $qtde;
                
                if ($novaQtde > $estoqueDisponivel) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Quantidade total excederia o estoque disponível ($estoqueDisponivel)"
                    ]);
                    exit;
                }
                
                $_SESSION['itens'][$idItem]['qtde'] = $novaQtde;
                $_SESSION['itens'][$idItem]['valor'] = $novaQtde * $item['ValVendItens'];
                error_log('Item atualizado: ' . json_encode($_SESSION['itens'][$idItem]));
            }

            // Substitua todas as ocorrências de geração de HTML do carrinho por:
            $cartData = generateCartHtml($_SESSION['itens']);

            echo json_encode([
                'success' => true,
                'message' => 'Item adicionado ao carrinho',
                'cartHtml' => $cartData['html'],
                'total' => number_format($cartData['total'], 2, ',', '.'),
                'pkCount' => count($_SESSION['itens']),
                'debug' => [
                    'item' => $item,
                    'estoque' => $estoqueDisponivel,
                    'session' => $_SESSION['itens'][$idItem]
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Item não encontrado',
                'debug' => [
                    'query' => $query,
                    'idItem' => $idItem
                ]
            ]);
        }

        // Libera o lock
        flock($fp, LOCK_UN);
        fclose($fp);
        @unlink($lockFile);
        exit;
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'updateQuantity') {
    $idItem = $_POST['idItem'];
    $novaQtde = (int)$_POST['quantidade'];
    
    // Verificar se o item existe no carrinho
    if (!isset($_SESSION['itens'][$idItem])) {
        echo json_encode([
            'success' => false,
            'message' => 'Item não encontrado no carrinho'
        ]);
        exit;
    }
    
    // Instanciar classe Vendas para verificar estoque
    $vendas = new Vendas();
    $verify = $vendas->itensVerify($idItem, $novaQtde, $_SESSION['perm']);
    
    if (!$verify['status']) {
        echo json_encode([
            'success' => false,
            'message' => $verify['message']
        ]);
        exit;
    }

    // Atualizar quantidade no carrinho
    $valorUnitario = $_SESSION['itens'][$idItem]['valor'] / $_SESSION['itens'][$idItem]['qtde'];
    $_SESSION['itens'][$idItem]['qtde'] = $novaQtde;
    $_SESSION['itens'][$idItem]['valor'] = $valorUnitario * $novaQtde;
    
    // Em updateQuantity, substitua o ob_start() e todo o HTML por:
    $cartData = generateCartHtml($_SESSION['itens']);
    
    echo json_encode([
        'success' => true,
        'cartHtml' => $cartData['html'],
        'novoValorItem' => number_format($_SESSION['itens'][$idItem]['valor'], 2, ',', '.'),
        'totalCarrinho' => number_format($cartData['total'], 2, ',', '.'),
        'message' => 'Quantidade atualizada com sucesso'
    ]);
    exit;
} 

// Adicione este bloco antes do final do arquivo
if (isset($_POST['action']) && $_POST['action'] == 'removeItem') {
    try {
        // Validação e sanitização
        $idItem = filter_var($_POST['idItem'], FILTER_SANITIZE_NUMBER_INT);
        if (!$idItem) {
            throw new Exception('ID do item inválido');
        }
        
        // Verifica se o item existe no carrinho
        if (!isset($_SESSION['itens'][$idItem])) {
            throw new Exception('Item não encontrado no carrinho');
        }

        // Guarda informações para log
        $itemRemovido = $_SESSION['itens'][$idItem];
        
        // Remove o item
        unset($_SESSION['itens'][$idItem]);
        
        // Gera HTML atualizado do carrinho
        $cartData = generateCartHtml($_SESSION['itens']);
        
        // Log da operação
        error_log(sprintf(
            "Item removido do carrinho - ID: %d, Produto: %s, Qtde: %d, Valor: %.2f, Usuário: %d",
            $idItem,
            $itemRemovido['nameproduto'],
            $itemRemovido['qtde'],
            $itemRemovido['valor'],
            $_SESSION['idUsuario']
        ));
        
        echo json_encode([
            'success' => true,
            'cartHtml' => $cartData['html'],
            'totalCarrinho' => number_format($cartData['total'], 2, ',', '.'),
            'pkCount' => count($_SESSION['itens']),
            'message' => 'Item removido com sucesso'
        ]);

    } catch (Exception $e) {
        error_log("Erro ao remover item do carrinho: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>
