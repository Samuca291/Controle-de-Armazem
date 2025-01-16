<?php
function generateCartHtml($items) {
    if (empty($items)) {
        return [
            'html' => '<tr><td colspan="5" class="text-center">Carrinho Vazio</td></tr>',
            'total' => 0
        ];
    }

    $html = '';
    $totalCarrinho = 0;
    $cont = 1;

    foreach ($items as $id => $item) {
        $totalCarrinho += $item['valor'];
        
        $html .= '<tr data-id="' . $id . '">
            <td class="text-center">' . $cont . '</td>
            <td>' . $item['nameproduto'] . '</td>
            <td>
                <div class="quantity-control">
                    <button type="button" class="quantity-btn minus" 
                            onclick="updateCartQuantity(' . $id . ', -1)"
                            ' . ($item['qtde'] <= 1 ? 'disabled' : '') . '>
                        <i class="fa fa-minus"></i>
                    </button>
                    <input type="number" 
                           class="quantity-input cart-quantity" 
                           id="qty_' . $id . '" 
                           value="' . $item['qtde'] . '" 
                           min="1"
                           max="' . $item['estoqueDisponivel'] . '"
                           onchange="updateCartQuantityDirect(' . $id . ', this.value)">
                    <button type="button" class="quantity-btn plus" 
                            onclick="updateCartQuantity(' . $id . ', 1)"
                            ' . ($item['qtde'] >= $item['estoqueDisponivel'] ? 'disabled' : '') . '>
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </td>
            <td style="text-align: right" id="valor_' . $id . '">
                R$ ' . number_format($item['valor'], 2, ',', '.') . '
            </td>
            <td class="text-center">
                <button type="button" 
                        class="btn-remove-item" 
                        onclick="removeCartItem(' . $id . ')"
                        data-product="' . htmlspecialchars($item['nameproduto']) . '"
                        data-value="' . number_format($item['valor'], 2, ',', '.') . '">
                    <i class="fa fa-times"></i>
                </button>
            </td>
        </tr>';
        $cont++;
    }

    return [
        'html' => $html,
        'total' => $totalCarrinho
    ];
}
?>
