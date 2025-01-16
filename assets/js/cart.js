// ...existing code...
document.getElementById('btn-limpar-carrinho').addEventListener('click', function(e) {
    e.preventDefault();
    fetch('../App/Database/limparCarrinho.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ...atualiza carrinho na tela...
                alert('Carrinho limpo com sucesso!');
            }
        });
});
// ...existing code...
