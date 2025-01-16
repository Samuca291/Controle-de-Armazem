<?php
/*
Class produtos
*/

require_once 'connect.php';

class Produtos extends Connect
{

  public function index($value)
  {
    $query = "SELECT * FROM `produtos` WHERE `PublicProduto` = '$value'";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    if ($result) {

      while ($row = mysqli_fetch_array($result)) {

        if ($row['Ativo'] == 0) {
          $c = 'class="label-warning"';
        } else {
          $c = " ";
        }
        echo '<li ' . $c . '>

          <!-- drag handle -->
          <span class="handle">
            <i class="fa fa-ellipsis-v"></i>
            <i class="fa fa-ellipsis-v"></i>
          </span>
          <!-- checkbox -->
          <form class="label" name="ativ' . $row['CodRefProduto'] . '" action="../../App/Database/action.php" method="post">
            <input type="hidden" name="id" value="' . $row['CodRefProduto'] . '">
            <input type="hidden" name="current_status" value="' . $row['Ativo'] . '">
            <input type="hidden" name="tabela" value="produtos">                  
            <input type="checkbox" name="status" ' . ($row['Ativo'] == 1 ? 'checked' : '') . ' 
              value="1" onclick="this.form.submit();" ' . ($value == 0 ? 'disabled' : '') . '>
          </form>
          
          <!-- todo text -->
          <span class="text"><span class="badge left">' . $row['CodRefProduto'] . '</span> ' . $row['NomeProduto'] . '</span>
          <!-- Emphasis label -->
          <!-- <small class="label label-danger"><i class="fa fa-clock-o"></i> 2 mins</small> -->
          <!-- General tools such as edit or delete-->
          <div class="tools right">

                      <a href="" data-toggle="modal" data-target="#myModalup' . $row['CodRefProduto'] . '"><i class="fa fa-edit"></i></a> 
                    
                      <!-- Button trigger modal -->
                    <a href="" data-toggle="modal" data-target="#myModal' . $row['CodRefProduto'] . '">' ;

        if ($row['PublicProduto'] == 0) {
          echo '<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>';
        } else {
          echo '<i class="glyphicon glyphicon-ok" aria-hidden="true"></i>';
        }

        echo '</a>
                    <a href="" data-toggle="modal" data-target="#deleteModal' . $row['CodRefProduto'] . '">
                        <i class="fa fa-trash text-danger"></i>
                    </a>
                </div>';

        echo '<div class="modal fade" id="myModal' . $row['CodRefProduto'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <form id="delprod' . $row['CodRefProduto'] . '" name="delprod' . $row['CodRefProduto'] . '" action="../../App/Database/delprod.php" method="post" style="color:#000;">
            
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Você tem certeza que deseja alterar o status deste item na sua lista.</h4>
                  </div>
                  <div class="modal-body">
                    Nome: ' . $row['NomeProduto'] . '
                  </div>
                  <input type="hidden" id="id" name="id" value="' . $row['CodRefProduto'] . '">
                  <div class="modal-footer">
                    <button type="submit" value="Cancelar" class="btn btn-default">Não</button>
                    <button type="submit" name="update" value="Cadastrar" class="btn btn-primary">Sim</button>
                  </div>
                </div>
              </div>
            </form>
          </div>';

        echo '<div class="modal fade" id="myModalup' . $row['CodRefProduto'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <form id="Upprod' . $row['CodRefProduto'] . '" name="Upprod' . $row['CodRefProduto'] . '" action="../../App/Database/insertprod.php" method="post" style="color:#000;">
            
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Você tem certeza que deseja alterar o status deste item na sua lista.</h4>
                  </div>
                  <div class="modal-body">
                    Nome Atual:
                    <input type="text" id="nomeProduto" name="nomeProduto" value="' . $row['NomeProduto'] . '">
                  </div>
                  <input type="hidden" id="id" name="id" value="' . $row['CodRefProduto'] . '">
                  
                  <div class="modal-footer">
                    <button type="submit" value="Cancelar" class="btn btn-default">Não</button>
                    <button type="submit" name="update" value="Cadastrar" class="btn btn-primary">Sim</button>
                  </div>
                </div>
              </div>
            </form>
          </div>';

        echo '</li>';

        // Modal de confirmação de exclusão
        echo '<div class="modal fade" id="deleteModal' . $row['CodRefProduto'] . '" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Confirmar Exclusão</h4>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir o produto: ' . $row['NomeProduto'] . '?</p>
                        <p class="text-warning"><small>Esta ação não poderá ser desfeita.</small></p>
                    </div>
                    <div class="modal-footer">
                        <form action="../../App/Database/deleteprod.php" method="POST">
                            <input type="hidden" name="id" value="' . $row['CodRefProduto'] . '">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>';
      }
    }
  }

  public function listProdutos($selected = null) {
    $query = "SELECT CodRefProduto, NomeProduto FROM produtos WHERE Ativo = 1 ORDER BY NomeProduto ASC";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    if ($result) {
        while ($row = mysqli_fetch_array($result)) {
            $isSelected = ($selected == $row['CodRefProduto']) ? 'selected' : '';
            echo '<option value="' . $row['CodRefProduto'] . '" ' . $isSelected . '>' . 
                 htmlspecialchars($row['NomeProduto']) . 
                 '</option>';
        }
    }
  }

  public function getProdutoInfo($codProduto) {
    $query = "SELECT CodRefProduto, NomeProduto FROM produtos WHERE CodRefProduto = '$codProduto' AND Ativo = 1";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));
    return mysqli_fetch_assoc($result);
  }

  public function InsertProd($nomeProduto, $idUsuario)
  {

    $query = "INSERT INTO `produtos`(`CodRefProduto`, `NomeProduto`,`Ativo` ,`PublicProduto` , `Usuario_idUser`) VALUES (NULL,'$nomeProduto', 1 , 1 ,'$idUsuario')";
    if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

      header('Location: ../../views/prod/index.php?alert=1');
    } else {
      header('Location: ../../views/prod/index.php?alert=0');
    }
  }

  public function UpdateProd($id, $nomeProduto, $idUsuario)
  {
    if (mysqli_query($this->SQL, "UPDATE `produtos` SET `NomeProduto` = '$nomeProduto', `Usuario_idUser` = '$idUsuario' WHERE `CodRefProduto` = '$id'") or die(mysqli_error($this->SQL))) {

      header('Location: ../../views/prod/index.php?alert=1');
    } else {
      header('Location: ../../views/prod/index.php?alert=0');
    }
  }

  public function DelProdutos($value)
  {

    $query = "SELECT * FROM `produtos` WHERE `CodRefProduto` = '$value'";
    $result = mysqli_query($this->SQL, $query);
    if ($row = mysqli_fetch_array($result)) {

      $id = $row['CodRefProduto'];
      $public = $row['PublicProduto'];
      if ($public == 1) {
        $p = 0;
      } else {
        $p = 1;
      }

      mysqli_query($this->SQL, "UPDATE `produtos` SET `PublicProduto` = '$p' WHERE `CodRefProduto` = '$id'") or die(mysqli_error($this->SQL));
      header('Location: ../../views/prod/index.php?alert=1');
    } else {
      header('Location: ../../views/prod/index.php?alert=0');
    }
  }

  public function Ativo($value, $id)
  {

    if ($value == 0) {
      $v = 1;
    } else {
      $v = 0;
    }

    $query = "UPDATE `produtos` SET `Ativo` = '$v' WHERE `CodRefProduto` = '$id'";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    header('Location: ../../views/prod/');
  } //Ativo

  public function DeleteProduto($id) {
    // Primeiro verifica se existem itens vinculados
    $query = "SELECT COUNT(*) as total FROM itens WHERE Produto_CodRefProduto = '$id'";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));
    $row = mysqli_fetch_assoc($result);
    
    if ($row['total'] > 0) {
        return false; // Produto tem itens vinculados
    }
    
    // Se não houver itens vinculados, exclui o produto
    $query = "DELETE FROM produtos WHERE CodRefProduto = '$id'";
    if (mysqli_query($this->SQL, $query)) {
        return true;
    }
    return false;
  }

}

$produtos = new Produtos;
