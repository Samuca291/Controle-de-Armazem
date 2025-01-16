-- Verifica se a coluna já existe e adiciona se não existir
ALTER TABLE itens
ADD COLUMN IF NOT EXISTS CodigoBarras varchar(50) DEFAULT NULL;
