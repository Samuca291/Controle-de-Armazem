class ControleEstoque:
    def __init__(self):
        """
        Inicializa o controle de estoque com um dicionário vazio.
        """
        self.estoque = {}

    def adicionar_item(self, item, quantidade):
        """
        Adiciona a quantidade especificada de um item ao estoque.
        
        Args:
            item (str): Nome do item a ser adicionado.
            quantidade (int): Quantidade do item a ser adicionada.
        
        Raises:
            ValueError: Se a quantidade for negativa.
        """
        if quantidade < 0:
            raise ValueError("A quantidade não pode ser negativa.")
        
        if item in self.estoque:
            self.estoque[item] += quantidade
        else:
            self.estoque[item] = quantidade

    def remover_item(self, item, quantidade):
        """
        Remove a quantidade especificada de um item do estoque.
        Se a quantidade a ser removida for maior ou igual à quantidade
        disponível, o item é removido do estoque.

        Args:
            item (str): Nome do item a ser removido.
            quantidade (int): Quantidade do item a ser removida.
        
        Raises:
            ValueError: Se a quantidade for negativa.
        """
        if quantidade < 0:
            raise ValueError("A quantidade não pode ser negativa.")
        
        if item in self.estoque:
            if quantidade >= self.estoque[item]:
                del self.estoque[item]
            else:
                self.estoque[item] -= quantidade
        else:
            raise ValueError("Item não encontrado no estoque.")

    def verificar_estoque(self, item):
        """
        Verifica a quantidade disponível de um item no estoque.

        Args:
            item (str): Nome do item a ser verificado.
        
        Returns:
            int: Quantidade do item no estoque (ou 0 se não estiver presente).
        """
        return self.estoque.get(item, 0)

    def listar_itens(self):
        """
        Retorna uma lista com todos os itens disponíveis no estoque.

        Returns:
            list: Lista dos nomes dos itens disponíveis.
        """
        return list(self.estoque.keys())

    def imprimir_estoque(self):
        """
        Imprime todos os itens do estoque com suas quantidades.
        """
        if not self.estoque:
            print("Estoque vazio.")
        else:
            print("Estoque:")
            for item, quantidade in self.estoque.items():
                print(f"{item}: {quantidade}")


def main():
    controle = ControleEstoque()

    # Adiciona alguns itens ao estoque inicial
    controle.adicionar_item("Maçã", 100)
    controle.adicionar_item("Banana", 150)
    controle.adicionar_item("Laranja", 200)

    # Remove alguns itens do estoque inicial
    controle.remover_item("Maçã", 30)
    controle.remover_item("Banana", 50)

    # Exibe o estoque atual de alguns itens
    print("Estoque de Maçã:", controle.verificar_estoque("Maçã"))
    print("Estoque de Banana:", controle.verificar_estoque("Banana"))
    print("Estoque de Laranja:", controle.verificar_estoque("Laranja"))

    # Lista todos os itens disponíveis no estoque
    print("Itens disponíveis no estoque:", controle.listar_itens())

    # Imprime o estoque completo
    controle.imprimir_estoque()


if __name__ == "__main__":
    main()

