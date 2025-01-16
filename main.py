import tkinter as tk
from tkinter import messagebox, simpledialog, filedialog, ttk
import csv
import datetime
from collections import defaultdict
import matplotlib.pyplot as plt

class ControleEstoque:
    def __init__(self):
        self.estoque = {}
        self.historico_movimentacao = []

    def adicionar_item(self, item, quantidade):
        if quantidade < 0:
            raise ValueError("A quantidade não pode ser negativa.")
        
        if item in self.estoque:
            self.estoque[item] += quantidade
        else:
            self.estoque[item] = quantidade

        self.historico_movimentacao.append((datetime.datetime.now(), item, quantidade, 'adicionar'))
    
    def remover_item(self, item, quantidade):
        if quantidade < 0:
            raise ValueError("A quantidade não pode ser negativa.")
        
        if item in self.estoque:
            if quantidade >= self.estoque[item]:
                del self.estoque[item]
            else:
                self.estoque[item] -= quantidade
            self.historico_movimentacao.append((datetime.datetime.now(), item, -quantidade, 'remover'))
        else:
            raise ValueError("Item não encontrado no estoque.")

    def verificar_estoque(self, item):
        return self.estoque.get(item, 0)

    def listar_itens(self):
        return list(self.estoque.keys())

    def carregar_estoque(self, filename):
        with open(filename, 'r', newline='') as csvfile:
            reader = csv.DictReader(csvfile)
            for row in reader:
                item = row['Item']
                quantidade = int(row['Quantidade'])
                self.estoque[item] = quantidade

    def editar_quantidade(self, item, nova_quantidade):
        if item in self.estoque:
            self.historico_movimentacao.append((datetime.datetime.now(), item, nova_quantidade - self.estoque[item], 'editar'))
            self.estoque[item] = nova_quantidade
        else:
            raise ValueError("Item não encontrado no estoque.")

    def gerar_relatorio_estoque(self, filename):
        with open(filename, 'w', newline='') as csvfile:
            fieldnames = ['Item', 'Quantidade']
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            writer.writeheader()
            for item, quantidade in self.estoque.items():
                writer.writerow({'Item': item, 'Quantidade': quantidade})
        print(f"Relatório de estoque gerado: {filename}")

    def gerar_grafico_estoque(self):
        items = list(self.estoque.keys())
        quantities = list(self.estoque.values())
        plt.bar(items, quantities)
        plt.xlabel('Itens')
        plt.ylabel('Quantidades')
        plt.title('Estoque Atual')
        plt.xticks(rotation=45)
        plt.tight_layout()
        plt.show()

    def gerar_historico_movimentacao(self, filename):
        with open(filename, 'w', newline='') as csvfile:
            fieldnames = ['Data e Hora', 'Item', 'Quantidade', 'Ação']
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            writer.writeheader()
            for registro in self.historico_movimentacao:
                writer.writerow({
                    'Data e Hora': registro[0],
                    'Item': registro[1],
                    'Quantidade': registro[2],
                    'Ação': registro[3]
                })
        print(f"Histórico de movimentação gerado: {filename}")

    def identificar_itens_criticos(self, limite):
        itens_criticos = [item for item, quantidade in self.estoque.items() if quantidade < limite]
        return itens_criticos

    def prever_demanda(self):
        demanda = defaultdict(int)
        for registro in self.historico_movimentacao:
            item, quantidade, acao = registro[1], registro[2], registro[3]
            if acao == 'remover':
                demanda[item] += abs(quantidade)
        
        previsao = {item: (quantidade / len([r for r in self.historico_movimentacao if r[1] == item and r[3] == 'remover'])) 
                    for item, quantidade in demanda.items()}
        
        return previsao

class EstoqueApp:
    def __init__(self, root):
        self.controle = ControleEstoque()
        self.root = root
        self.root.title("Controle de Estoque")
        self.root.geometry("600x600")
        self.root.resizable(False, False)
        
        # Estilos
        style = ttk.Style()
        style.configure("TFrame", background="#f0f0f0")
        style.configure("TLabel", background="#f0f0f0", font=("Helvetica", 12))
        style.configure("TButton", font=("Helvetica", 12))
        style.configure("TEntry", font=("Helvetica", 12))

        self.frame = ttk.Frame(root, padding="10")
        self.frame.pack(fill=tk.BOTH, expand=True)

        self.label_item = ttk.Label(self.frame, text="Item:")
        self.label_item.grid(row=0, column=0, padx=5, pady=5, sticky=tk.W)

        self.entry_item = ttk.Entry(self.frame)
        self.entry_item.grid(row=0, column=1, padx=5, pady=5, sticky=tk.EW)

        self.label_quantidade = ttk.Label(self.frame, text="Quantidade:")
        self.label_quantidade.grid(row=1, column=0, padx=5, pady=5, sticky=tk.W)

        self.entry_quantidade = ttk.Entry(self.frame)
        self.entry_quantidade.grid(row=1, column=1, padx=5, pady=5, sticky=tk.EW)

        self.btn_adicionar = ttk.Button(self.frame, text="Adicionar", command=self.adicionar_item)
        self.btn_adicionar.grid(row=2, column=0, padx=5, pady=5)

        self.btn_remover = ttk.Button(self.frame, text="Remover", command=self.remover_item)
        self.btn_remover.grid(row=2, column=1, padx=5, pady=5)

        self.btn_editar = ttk.Button(self.frame, text="Editar Quantidade", command=self.editar_quantidade)
        self.btn_editar.grid(row=3, column=0, columnspan=2, padx=5, pady=5)

        self.btn_listar = ttk.Button(self.frame, text="Listar Itens", command=self.listar_itens)
        self.btn_listar.grid(row=4, column=0, columnspan=2, padx=5, pady=5)

        self.btn_grafico = ttk.Button(self.frame, text="Gerar Gráfico", command=self.gerar_grafico_estoque)
        self.btn_grafico.grid(row=5, column=0, columnspan=2, padx=5, pady=5)

        self.btn_relatorio = ttk.Button(self.frame, text="Gerar Relatório de Estoque", command=self.gerar_relatorio_estoque)
        self.btn_relatorio.grid(row=6, column=0, columnspan=2, padx=5, pady=5)

        self.btn_historico = ttk.Button(self.frame, text="Gerar Histórico de Movimentação", command=self.gerar_historico_movimentacao)
        self.btn_historico.grid(row=7, column=0, columnspan=2, padx=5, pady=5)

        self.btn_criticos = ttk.Button(self.frame, text="Identificar Itens Críticos", command=self.identificar_itens_criticos)
        self.btn_criticos.grid(row=8, column=0, columnspan=2, padx=5, pady=5)

        self.btn_previsao = ttk.Button(self.frame, text="Prever Demanda", command=self.prever_demanda)
        self.btn_previsao.grid(row=9, column=0, columnspan=2, padx=5, pady=5)

        self.text_estoque = tk.Text(self.frame, height=10, width=50, wrap=tk.WORD, font=("Helvetica", 12))
        self.text_estoque.grid(row=10, column=0, columnspan=2, padx=5, pady=5, sticky=tk.EW)
        self.text_estoque.config(state=tk.DISABLED)

        for child in self.frame.winfo_children():
            child.grid_configure(padx=5, pady=5)

    def adicionar_item(self):
        try:
            item = self.entry_item.get()
            quantidade = int(self.entry_quantidade.get())
            
            self.controle.adicionar_item(item, quantidade)
            messagebox.showinfo("Sucesso", f"{quantidade} unidades de {item} adicionadas ao estoque.")
            self.atualizar_texto_estoque()
        except ValueError:
            messagebox.showerror("Erro", "A quantidade deve ser um número inteiro.")

    def remover_item(self):
        try:
            item = self.entry_item.get()
            quantidade = int(self.entry_quantidade.get())
    
            self.controle.remover_item(item, quantidade)
            messagebox.showinfo("Sucesso", f"{quantidade} unidades de {item} removidas do estoque.")
            self.atualizar_texto_estoque()
        except ValueError as e:
            messagebox.showerror("Erro", str(e))

    def editar_quantidade(self):
        try:
            item = self.entry_item.get()
            nova_quantidade = int(self.entry_quantidade.get())
            
            self.controle.editar_quantidade(item, nova_quantidade)
            messagebox.showinfo("Sucesso", f"Quantidade de {item} atualizada para {nova_quantidade}.")
            self.atualizar_texto_estoque()
        except ValueError as e:
            messagebox.showerror("Erro", str(e))

    def listar_itens(self):
        self.text_estoque.config(state=tk.NORMAL)
        self.text_estoque.delete(1.0, tk.END)
        itens = self.controle.listar_itens()
        if itens:
            self.text_estoque.insert(tk.END, "Itens no estoque:\n")
            for item in itens:
                self.text_estoque.insert(tk.END, f"{item}\n")
        else:
            self.text_estoque.insert(tk.END, "Estoque vazio.\n")
        self.text_estoque.config(state=tk.DISABLED)

    def atualizar_texto_estoque(self):
        self.text_estoque.config(state=tk.NORMAL)
        self.text_estoque.delete(1.0, tk.END)
        self.text_estoque.insert(tk.END, "Itens no estoque:\n")
        for item, quantidade in self.controle.estoque.items():
            self.text_estoque.insert(tk.END, f"{item}: {quantidade}\n")
        self.text_estoque.config(state=tk.DISABLED)

    def gerar_grafico_estoque(self):
        self.controle.gerar_grafico_estoque()

    def gerar_relatorio_estoque(self):
        filename = filedialog.asksaveasfilename(defaultextension=".csv", filetypes=[("CSV files", "*.csv")])
        if filename:
            self.controle.gerar_relatorio_estoque(filename)
            messagebox.showinfo("Sucesso", f"Relatório de estoque gerado: {filename}")

    def gerar_historico_movimentacao(self):
        filename = filedialog.asksaveasfilename(defaultextension=".csv", filetypes=[("CSV files", "*.csv")])
        if filename:
            self.controle.gerar_historico_movimentacao(filename)
            messagebox.showinfo("Sucesso", f"Histórico de movimentação gerado: {filename}")

    def identificar_itens_criticos(self):
        limite = simpledialog.askinteger("Limite", "Digite o limite mínimo de quantidade:")
        if limite is not None:
            itens_criticos = self.controle.identificar_itens_criticos(limite)
            if itens_criticos:
                messagebox.showinfo("Itens Críticos", f"Itens com quantidade abaixo de {limite}:\n" + "\n".join(itens_criticos))
            else:
                messagebox.showinfo("Itens Críticos", "Nenhum item com quantidade abaixo do limite.")

    def prever_demanda(self):
        previsao = self.controle.prever_demanda()
        mensagem = "Previsão de demanda:\n"
        for item, quantidade in previsao.items():
            mensagem += f"{item}: {quantidade:.2f} unidades\n"
        messagebox.showinfo("Previsão de Demanda", mensagem)

def main():
    root = tk.Tk()
    app = EstoqueApp(root)
    root.mainloop()

if __name__ == "__main__":
    main()


