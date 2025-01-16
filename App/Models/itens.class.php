<?php

/*
Class produtos
*/

require_once 'connect.php';

class Itens extends Connect
{

  public function listItens($idprodutos, $idFabricante)
  {
    $query = "SELECT * FROM `itens`,`fabricante`,`produtos` WHERE (`Fabricante_idFabricante` = `idFabricante` AND `Produto_CodRefProduto` = `CodRefProduto`) AND (`Fabricante_idFabricante` = '$idFabricante' AND `Produto_CodRefProduto` = '$idprodutos') ";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));
    $q = 0;
    $v = 0;
    $e = 0;
    while ($rowlist = mysqli_fetch_array($result)) {

      $q = $q + $rowlist['QuantItens'];
      $v = $v + $rowlist['QuantItensVend'];
      $e = $q - $v;
      $NomeProduto = $rowlist['NomeProduto'];
      $fabricante  = $rowlist['NomeFabricante'];
    }

    return array('NomeProduto' => $NomeProduto, 'Fabricante' => $fabricante, 'QuantItens' => $q, 'QuantItensVend' => $v, 'Estoque' => $e,);
  }

  public function totalitens($value)
  {
    $query = "SELECT `Produto_CodRefProduto`, `Fabricante_idFabricante` FROM `itens` WHERE `itensPublic` = '$value' GROUP BY `Produto_CodRefProduto`, `Fabricante_idFabricante`";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));
    while ($row = mysqli_fetch_array($result)) {

      $idprodutos = $row['Produto_CodRefProduto'];
      $idFabricante = $row['Fabricante_idFabricante'];

      echo '<li>';
      $resp = Itens::listItens($idprodutos, $idFabricante);
      echo '<b> Produto: ' . $resp['NomeProduto'];
      echo ' / Fabricante: ' . $resp['Fabricante'];
      echo '</b> Comprados: ' . $resp['QuantItens'];
      echo ' | Vendidos: ' . $resp['QuantItensVend'];
      echo ' | Em Estoque: ' . $resp['Estoque'];
      echo '</li>';
    }
  }

  public function index($value = 1)
  {
    $query = "SELECT i.*, p.NomeProduto, f.NomeFabricante 
              FROM itens i
              INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto
              INNER JOIN fabricante f ON i.Fabricante_idFabricante = f.idFabricante
              WHERE i.ItensPublic = '$value'";
              
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    if ($result) {
        echo '<table id="example1" class="table">
        <thead class="thead-inverse">
          <tr>
            <th>Ativo</th>
            <th>Image</th>
            <th>Nome Produto</th>
            <th>Fabricante</th>
            <th>Código de Barras</th> <!-- Nova coluna -->
            <th>Quant. Estoque</th>
            <th>Quant. Vendido</th>
            <th>V. Compra</th>
            <th>V. Venda</th>
            <th>Data Compra</th>
            <th>Data Vencimento</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>';

        while ($row = mysqli_fetch_array($result)) {
            $valor_compra = $this->format_moeda($row['ValCompItens']);
            $valor_venda = $this->format_moeda($row['ValVendItens']);

            if ($row['ItensAtivo'] == 0) {
                $c = 'class="label-warning"';
            } else {
                $c = "";
            }

            echo '<tr ' . $c . '>
                    <td>
                        <form class="label" name="ativ' . $row['idItens'] . '" action="../../App/Database/action.php" method="post">
                            <input type="hidden" name="id" value="' . $row['idItens'] . '">
                            <input type="hidden" name="current_status" value="' . $row['ItensAtivo'] . '">
                            <input type="hidden" name="tabela" value="itens">
                            <input type="checkbox" name="status" ' . ($row['ItensAtivo'] == 1 ? 'checked' : '') . ' 
                                value="1" onclick="this.form.submit();" ' . ($value == 0 ? 'disabled' : '') . '>
                        </form>
                    </td>
                    <td>' . (!empty($row['Image']) ? '<img src="../' . $row['Image'] . '" width="50" />' : '') . '</td>
                    <td>' . $row['NomeProduto'] . '</td>
                    <td>' . $row['NomeFabricante'] . '</td>
                    <td>' . ($row['CodigoBarras'] ? $row['CodigoBarras'] : 'Não cadastrado') . '</td> <!-- Nova coluna -->
                    <td>' . $row['QuantItens'] . '</td>
                    <td>' . $row['QuantItensVend'] . '</td>
                    <td>' . $valor_compra . '</td>
                    <td>' . $valor_venda . '</td>
                    <td>' . date('d/m/Y', strtotime($row['DataCompraItens'])) . '</td>
                    <td>' . date('d/m/Y', strtotime($row['DataVenci_Itens'])) . '</td>
                    <td>
                        <div class="tools">
                            <a href="edititens.php?q=' . $row['idItens'] . '" title="Editar">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="#" data-toggle="modal" data-target="#statusModal' . $row['idItens'] . '" title="Alterar Status">
                                <i class="glyphicon ' . ($row['ItensPublic'] == 0 ? 'glyphicon-remove' : 'glyphicon-ok') . '"></i>
                            </a>
                            <a href="#" data-toggle="modal" data-target="#deleteModal' . $row['idItens'] . '" title="Excluir" class="text-danger">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>';

            // Modal de alteração de status
            echo '<div class="modal fade" id="statusModal' . $row['idItens'] . '" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="../../App/Database/updatePublicItens.php" method="post">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">Alterar Status do Item</h4>
                            </div>
                            <div class="modal-body">
                                <p>Deseja ' . ($row['ItensPublic'] == 1 ? 'desativar' : 'ativar') . ' o item: ' . $row['NomeProduto'] . '?</p>
                                <p class="text-warning"><small>' . ($row['ItensPublic'] == 1 ? 'O item será movido para a lista de desativados e terá seu status alterado para inativo.' : '') . '</small></p>
                            </div>
                            <input type="hidden" name="id" value="' . $row['idItens'] . '">
                            <input type="hidden" name="current_public" value="' . $row['ItensPublic'] . '">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Confirmar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>';

            // Modal de confirmação de exclusão
            echo '<div class="modal fade" id="deleteModal' . $row['idItens'] . '" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="../../App/Database/deleteitem.php" method="post">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">Excluir Item</h4>
                            </div>
                            <div class="modal-body">
                                <p>Tem certeza que deseja excluir o item: ' . $row['NomeProduto'] . '?</p>
                            </div>
                            <input type="hidden" name="id" value="' . $row['idItens'] . '">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                <button type="submit" name="deletar" class="btn btn-danger">Sim</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>';
        }
        echo '</tbody></table>';
    }
  }

  public function InsertItens($nomeimagem, $QuantItens, $ValCompItens, $ValVendItens, $DataCompraItens, $DataVenci_Itens, $Produto_CodRefProduto, $Fabricante_idFabricante, $idusuario, $codigoBarras = null)
  {
    $codigoBarras = $codigoBarras ? "'$codigoBarras'" : "NULL";
    
    $query = "INSERT INTO `itens`(
        `idItens`,
        `Image`,
        `QuantItens`, 
        `QuantItensVend`, 
        `ValCompItens`, 
        `ValVendItens`, 
        `DataCompraItens`, 
        `DataVenci_Itens`, 
        `ItensAtivo`,
        `ItensPublic`, 
        `Produto_CodRefProduto`, 
        `Fabricante_idFabricante`, 
        `Usuario_idUser`,
        `CodigoBarras`
    ) VALUES (
        NULL, 
        '$nomeimagem', 
        '$QuantItens', 
        0, 
        '$ValCompItens', 
        '$ValVendItens', 
        '$DataCompraItens', 
        '$DataVenci_Itens', 
        1, 
        1, 
        '$Produto_CodRefProduto', 
        '$Fabricante_idFabricante', 
        '$idusuario',
        $codigoBarras
    )";
    if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

      header('Location: ../../views/itens/index.php?alert=1');
    } else {
      header('Location: ../../views/itens/index.php?alert=0');
    }
  } //InsertItens

  public function editItens($value) {
    $query = "SELECT i.*, p.NomeProduto, p.CodRefProduto 
              FROM itens i 
              INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
              WHERE i.idItens = ?";
              
    $stmt = $this->SQL->prepare($query);
    $stmt->bind_param('i', $value);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return [
            'Itens' => [
                'idItens' => $row['idItens'],
                'Image' => $row['Image'],
                'QuantItens' => $row['QuantItens'],
                'ValCompItens' => $row['ValCompItens'],
                'ValVendItens' => $row['ValVendItens'],
                'DataCompraItens' => $row['DataCompraItens'],
                'DataVenci_Itens' => $row['DataVenci_Itens'],
                'CodRefProduto' => $row['CodRefProduto'],
                'idFabricante' => $row['Fabricante_idFabricante'],
                'CodigoBarras' => $row['CodigoBarras']  // Certifique-se de que este campo existe na tabela
            ]
        ];
    }
    return null;
  }

  public function updateItens($idItens, $nomeimagem, $QuantItens, $ValCompItens, $ValVendItens, $DataCompraItens, $DataVenci_Itens, $Produto_CodRefProduto, $Fabricante_idFabricante, $idusuario, $codigoBarras = null)
  {
    $codigoBarras = $codigoBarras ? "'$codigoBarras'" : "NULL";
    
    $query = "UPDATE `itens` SET
      `Image` = '$nomeimagem', 
      `QuantItens`= '$QuantItens',
      `ValCompItens`='$ValCompItens',
      `ValVendItens`='$ValVendItens',
      `DataCompraItens`='$DataCompraItens',
      `DataVenci_Itens`='$DataVenci_Itens',
      `Produto_CodRefProduto`='$Produto_CodRefProduto',
      `Fabricante_idFabricante`='$Fabricante_idFabricante',
      `Usuario_idUser`='$idusuario',
      `CodigoBarras`= $codigoBarras
      WHERE `idItens`= '$idItens'";

    if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

      header('Location: ../../views/itens/index.php?alert=1');
    } else {
      header('Location: ../../views/itens/index.php?alert=0');
    }
  }

  public function QuantItensVend($value, $idItens)
  {
    $query = "UPDATE `itens` SET `QuantItensVend` = '$value' WHERE `idItens`= '$idItens'";

    if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

      header('Location: ../../views/itens/index.php?alert=1');
    } else {
      header('Location: ../../views/itens/index.php?alert=0');
    }
  }

  public function DelItens($value)
  {

    $query = "SELECT * FROM `itens` WHERE `idItens` = '$value'";
    $result = mysqli_query($this->SQL, $query);
    if ($row = mysqli_fetch_array($result)) {

      $id = $row['idItens'];
      $public = $row['ItensPublic'];

      if ($public == 1) {
        $p = 0;
      } else {
        $p = 1;
      }

      mysqli_query($this->SQL, "UPDATE `itens` SET `ItensPublic` = '$p' WHERE `idItens` = '$id'") or die(mysqli_error($this->SQL));
      header('Location: ../../views/itens/index.php?alert=1');
    } else {
      header('Location: ../../views/itens/index.php?alert=0');
    }
  }

  public function Ativo($value, $id)
  {

    if ($value == 0) {
      $v = 1;
    } else {
      $v = 0;
    }

    $query = "UPDATE `itens` SET `ItensAtivo` = '$v' WHERE `idItens` = '$id'";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    header('Location: ../../views/itens/');
  } //ItensAtivo

  public function search($value)
  {
    if (isset($value)) {
      //$output = '';  
      $query = "SELECT P.CodRefProduto, P.NomeProduto, I.idItens, I.Produto_CodRefProduto, I.* FROM itens AS I, produtos AS P WHERE (I.Produto_CodRefProduto = P.CodRefProduto) AND (I.Produto_CodRefProduto LIKE '" . $value . "%' OR P.NomeProduto LIKE '%" . $value . "%') GROUP BY I.idItens, P.CodRefProduto LIMIT 5";
      $result = mysqli_query($this->SQL, $query);

      if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_array($result)) {

          $output[] = $row;
        }

        return array('data' => $output);
      } else {

        return 0;
      }
    }
  }

  public function DeleteItem($id) {
    // Se não houver vendas vinculadas, exclui o item
    $query = "DELETE FROM itens WHERE idItens = '$id'";
    if (mysqli_query($this->SQL, $query)) {
        return true;
    }
    return false;
  }

  public function getItemByBarcode($codigoBarras) {
    $query = "SELECT i.*, p.NomeProduto, p.CodRefProduto, f.NomeFabricante 
              FROM itens i 
              INNER JOIN produtos p ON i.Produto_CodRefProduto = p.CodRefProduto 
              INNER JOIN fabricante f ON i.Fabricante_idFabricante = f.idFabricante
              WHERE i.CodigoBarras = ? AND i.ItensAtivo = 1
              LIMIT 1";
              
    $stmt = $this->SQL->prepare($query);
    $stmt->bind_param('s', $codigoBarras);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function verificaCodigoBarras($codigoBarras, $idItens = null) {
    if (empty($codigoBarras)) return true; // Se não houver código, permite passar
    
    if ($idItens) {
        $query = "SELECT idItens FROM itens WHERE CodigoBarras = ? AND idItens != ? AND ItensPublic = 1";
        $stmt = $this->SQL->prepare($query);
        $stmt->bind_param('si', $codigoBarras, $idItens);
    } else {
        $query = "SELECT idItens FROM itens WHERE CodigoBarras = ? AND ItensPublic = 1";
        $stmt = $this->SQL->prepare($query);
        $stmt->bind_param('s', $codigoBarras);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows === 0; // Retorna true se não existir duplicado
  }
}

$itens = new Itens;
