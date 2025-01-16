<?php
$pkCount = count($_SESSION['itens'] ?? []);

if ($pkCount > 0): 
?>
    <div class="cart-actions">
        <button type="submit" class="btn btn-success btn-lg btn-block" name="comprar">
            <i class="fa fa-check"></i> Finalizar Compra
        </button>
        
        <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalSalvarCarrinho">
            <i class="fa fa-save"></i> Guardar Carrinho
        </button>
        
        <a href="carrinhos_salvos.php" class="btn btn-info btn-block">
            <i class="fa fa-list"></i> Ver Carrinhos Salvos
        </a>
        
        <button type="button" class="btn btn-danger btn-block" id="clear-cart">
            <i class="fa fa-trash"></i> Limpar Carrinho
        </button>
    </div>
<?php else: ?>
    <div class="cart-actions">
        <button type="button" class="btn btn-default btn-lg btn-block" disabled>
            <i class="fa fa-shopping-cart"></i> Carrinho Vazio
        </button>
    </div>
<?php endif; ?>
