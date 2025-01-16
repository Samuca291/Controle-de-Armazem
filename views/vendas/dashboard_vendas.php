// ...existing code...

<script>
// ...existing code...

function updateSalesTable(data) {
    const tbody = document.querySelector('#salesTable tbody');
    const tfoot = document.querySelector('#salesTable tfoot');
    
    tbody.innerHTML = '';
    let totalQuantidade = 0;
    let totalValor = 0;
    
    if (data.vendas && data.vendas.length > 0) {
        data.vendas.forEach(venda => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${venda.datareg}</td>
                <td>${venda.produto}</td>
                <td class="text-center">${venda.quantidade}</td>
                <td class="text-right">R$ ${venda.valor_unit}</td>
                <td class="text-right">R$ ${venda.valor_total}</td>
                <td>${venda.vendedor}</td>
            `;
            tbody.appendChild(row);
            
            // Atualiza totais
            totalQuantidade += parseInt(venda.quantidade);
            totalValor += parseFloat(venda.valor_total.replace('.', '').replace(',', '.'));
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhuma venda encontrada no período</td></tr>';
    }
    
    // Atualiza rodapé com totais
    document.getElementById('totalQuantidade').textContent = totalQuantidade;
    document.getElementById('totalValor').textContent = 'R$ ' + totalValor.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    // Atualiza cards de resumo
    document.getElementById('total-vendas').textContent = data.resumo.totalVendas;
    document.getElementById('total-produtos').textContent = data.resumo.totalItens;
    document.getElementById('media-vendas').textContent = data.resumo.mediaVendas;
    document.getElementById('total-valor').textContent = data.resumo.totalValor;
}

// ...existing code...
</script>
