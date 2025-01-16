-- Índices para otimizar as consultas de vendas
CREATE INDEX idx_vendas_datareg ON vendas(datareg);
CREATE INDEX idx_vendas_iditem ON vendas(iditem);
CREATE INDEX idx_vendas_idusuario ON vendas(idusuario);

-- Índices para relacionamentos
CREATE INDEX idx_itens_produto ON itens(Produto_CodRefProduto);
CREATE INDEX idx_itens_fabricante ON itens(Fabricante_idFabricante);

-- Índices para busca de produtos
CREATE INDEX idx_produtos_nome ON produtos(NomeProduto);
CREATE INDEX idx_produtos_codigo ON produtos(CodRefProduto);

-- Índices para otimizar buscas por código de barras
CREATE INDEX idx_itens_codigobarras ON itens(CodigoBarras);
