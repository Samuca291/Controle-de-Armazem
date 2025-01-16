<?php
/**
 * Vendas
 */

require_once 'connect.php';

class Vendas extends Connect
{

  public function itensVerify($iditem, $quant, $perm)
  {

    // Permite vendedor (perm=2) e administrador (perm=1)
    if ($perm > 2) {
      $_SESSION['msg'] = '<div class="alert alert-danger alert-dismissible">
                          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                          <strong>Permissão Negada!</strong> Somente vendedores e administradores podem realizar vendas!
                        </div>';
      header('Location: ../../views/vendas/index.php');
      exit();
    }
  

    $query = "SELECT i.*, p.NomeProduto 
              FROM itens i 
              INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
              WHERE i.idItens = ?";

    $stmt = $this->SQL->prepare($query);
    $stmt->bind_param('i', $iditem);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $estoqueDisponivel = $row['QuantItens'] - $row['QuantItensVend'];
        
        if ($quant > $estoqueDisponivel) {
            return array(
                'status' => false,
                'NomeProduto' => $row['NomeProduto'],
                'estoque' => $estoqueDisponivel,
                'message' => "Quantidade solicitada ({$quant}) excede o limite disponível em estoque ({$estoqueDisponivel})"
            );
        }

        return array(
            'status' => true,
            'NomeProduto' => $row['NomeProduto'],
            'estoque' => $estoqueDisponivel
        );
    } 

    $_SESSION['msg'] = '<div class="alert alert-warning">
        <strong>Ops!</strong> Produto (' . $iditem . ') não encontrado!</div>';
    header('Location: ../../views/vendas/index.php');
    exit;
  }

  public function itensVendidos($iditem, $quant, $cart, $idUsuario, $perm, $block = null)
  {
    if ($perm > 2) {
        $this->setError('Permissão negada para realizar vendas!');
        return false;
    }

    // Inicia transação
    $this->SQL->begin_transaction();

    try {
        // Verifica disponibilidade do item
        $query = "SELECT i.*, p.NomeProduto 
                 FROM itens i 
                 INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
                 WHERE i.idItens = ? FOR UPDATE";
        $stmt = $this->SQL->prepare($query);
        $stmt->bind_param('i', $iditem);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            throw new Exception("Item não encontrado");
        }

        $row = $result->fetch_assoc();
        $estoqueAtual = $row['QuantItens'];
        $vendasAtuais = $row['QuantItensVend'];
        $nomeProduto = $row['NomeProduto'];

        // Verifica se há estoque suficiente
        if ($estoqueAtual < ($vendasAtuais + $quant)) {
            throw new Exception("Estoque insuficiente para o produto {$nomeProduto}");
        }

        // Calcula valor
        $valor = ($row['ValVendItens'] * $quant);

        // Registra a venda
        $query = "INSERT INTO vendas (quantitens, valor, iditem, cart, idusuario, datareg) 
                 VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->SQL->prepare($query);
        $stmt->bind_param('idisi', $quant, $valor, $iditem, $cart, $idUsuario);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao registrar venda do produto {$nomeProduto}");
        }

        // Atualiza o estoque
        $novaQuantidadeVendida = $vendasAtuais + $quant;
        $query = "UPDATE itens 
                 SET QuantItensVend = ? 
                 WHERE idItens = ?";
        $stmt = $this->SQL->prepare($query);
        $stmt->bind_param('ii', $novaQuantidadeVendida, $iditem);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar estoque do produto {$nomeProduto}");
        }

        $this->SQL->commit();
        return array('status' => true, 'valor' => $valor);

    } catch (Exception $e) {
        $this->SQL->rollback();
        $this->setError('Erro ao processar venda: ' . $e->getMessage());
        return false;
    }
  }

  private function setError($message) {
    $_SESSION['msg'] = "<div class='alert alert-danger alert-dismissible'>
        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
        <strong>Erro!</strong> $message
    </div>";
  }

  public function idcliente($cpfCliente)
  {

    $client = "SELECT * FROM `cliente` WHERE `cpfCliente` = '$cpfCliente'";

    if ($resultcliente = mysqli_query($this->SQL, $client)  or die(mysqli_error($this->SQL))) {

      $row = mysqli_fetch_array($resultcliente);
      return $idCliente = $row['idCliente'];
    }
  }

  //----------itemNome

  public function itemNome($idItens)
  {

    $query = "SELECT * FROM `produtos` WHERE `CodRefProduto` IN (SELECT `Produto_CodRefProduto` FROM `itens` WHERE `idItens` = '$idItens' AND `ItensAtivo` = 1 AND `ItensPublic` = 1)";

    $result = mysqli_query($this->SQL, $query)  or die(mysqli_error($this->SQL));

    $row = mysqli_fetch_array($result);

    if ($row['NomeProduto'] != NULL) {
      $resp = $row['NomeProduto'];
    } else {
      $resp = NULL;
    }

    return $resp;
  } //--itemNome

  public function notavd($cart)
  {

    $query = "SELECT * FROM `vendas` WHERE `cart` = '$cart' ";

    if ($result = mysqli_query($this->SQL, $query)  or die(mysqli_error($this->SQL))) {

      while ($row = mysqli_fetch_array($result)) {
        $out[] = $row;
      }
    }

    return $out;
  } //--notavd

  public function dadosItem($idItem)
  {

    $query = "SELECT * FROM `fabricante`, `produtos`, `itens` WHERE `idItens` = '$idItem' AND `Produto_CodRefProduto` = `CodRefProduto` AND `Fabricante_idFabricante` = `idFabricante`";

    if ($result = mysqli_query($this->SQL, $query)  or die(mysqli_error($this->SQL))) {

      $row = mysqli_fetch_array($result);

      return $row;
    }
  } //---dadosItem

  public function jaComprou($idCliente, $idItem = null, $block = null)
  {
    if (!empty($block)) {

      $Ano = date('Y');
      $dataAno = $Ano . '-01-01 00:00:00';
      $dataIn = $dataAno;
      $dataFim = date('Y-m-d H:i:s');

      $query = "SELECT COUNT(*) AS TOTAL FROM `vendas` WHERE `cliente_idCliente` = '$idCliente' AND (`datareg` BETWEEN '$dataIn' AND '$dataFim' AND `iditem` = '$idItem')";
      $result = mysqli_query($this->SQL, $query);

      $row = mysqli_fetch_assoc($result);
      return $row['TOTAL'];
    } else {
      return 0;
    }
  }
}//Fim Class Vendas
