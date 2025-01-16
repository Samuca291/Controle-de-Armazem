<?php
/*
Class produtos
*/

require_once 'connect.php';

class Representante extends Connect
{

  public function index($value = NULL)
  {
    if ($value == NULL) {
      $value = 1;
    }
    $query = "SELECT * FROM `representante`, `fabricante` 
              WHERE `Fabricante_idFabricante` = `idFabricante` 
              AND (`repPublic` = '$value')
              ORDER BY `NomeFabricante`, `NomeRepresentante`";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    if ($result) {
      while ($row = mysqli_fetch_array($result)) {
        $statusClass = $row['repAtivo'] == 0 ? 'class="item-desativado"' : '';
        $checkedStatus = $row['repAtivo'] == 1 ? 'checked' : '';
        $publicIcon = $row['repPublic'] == 0 ? 'glyphicon-remove' : 'glyphicon-ok';
        
        // Linha da tabela
        echo '<tr ' . $statusClass . '>
          <td class="text-center">
            <form class="label" name="ativ' . $row['idRepresentante'] . '" action="../../App/Database/action.php" method="post">
              <input type="hidden" name="id" value="' . $row['idRepresentante'] . '">
              <input type="hidden" name="current_status" value="' . $row['repAtivo'] . '">
              <input type="hidden" name="tabela" value="representante">                  
              <input type="checkbox" name="status" ' . $checkedStatus . ' value="1" onclick="this.form.submit();">
            </form>
          </td>
          <td><span class="badge">' . $row['NomeFabricante'] . '</span></td>
          <td>' . $row['NomeRepresentante'] . '</td>
          <td>' . $row['TelefoneRepresentante'] . '</td>
          <td>' . $row['EmailRepresentante'] . '</td>
          <td>
            <div class="tools">
              <a href="#" data-toggle="modal" data-target="#editModal' . $row['idRepresentante'] . '" title="Editar">
                <i class="fa fa-edit"></i>
              </a>
              <a href="#" data-toggle="modal" data-target="#statusModal' . $row['idRepresentante'] . '" title="Alterar Status">
                <i class="glyphicon ' . $publicIcon . '"></i>
              </a>
              <a href="#" data-toggle="modal" data-target="#deleteModal' . $row['idRepresentante'] . '" title="Excluir" class="text-danger">
                <i class="fa fa-trash"></i>
              </a>
            </div>
          </td>
        </tr>';

        // Modal de Edição
        echo '<div class="modal fade" id="editModal' . $row['idRepresentante'] . '" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form action="../../App/Database/insertrepresentante.php" method="post">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">Editar Representante</h4>
                </div>
                <div class="modal-body">
                  <div class="form-group">
                    <label>Nome</label>
                    <input type="text" class="form-control" name="NomeRepresentante" value="' . $row['NomeRepresentante'] . '">
                  </div>
                  <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" class="form-control" name="TelefoneRepresentante" value="' . $row['TelefoneRepresentante'] . '">
                  </div>
                  <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control" name="EmailRepresentante" value="' . $row['EmailRepresentante'] . '">
                  </div>
                  <input type="hidden" name="idRepresentante" value="' . $row['idRepresentante'] . '">
                  <input type="hidden" name="idFabricante" value="' . $row['Fabricante_idFabricante'] . '">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                  <button type="submit" name="update" class="btn btn-primary" value="Cadastrar">Salvar</button>
                </div>
              </form>
            </div>
          </div>
        </div>';

        // Modal de Status
        echo '<div class="modal fade" id="statusModal' . $row['idRepresentante'] . '" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form action="../../App/Database/delRepresentante.php" method="post">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">Alterar Status do Representante</h4>
                </div>
                <div class="modal-body">
                  <p>Deseja alterar o status do representante: ' . $row['NomeRepresentante'] . '?</p>
                  <input type="hidden" name="idRepresentante" value="' . $row['idRepresentante'] . '">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                  <button type="submit" name="update" class="btn btn-primary" value="Cadastrar">Confirmar</button>
                </div>
              </form>
            </div>
          </div>
        </div>';

        // Modal de Exclusão
        echo '<div class="modal fade" id="deleteModal' . $row['idRepresentante'] . '" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form action="../../App/Database/excluirRepresentante.php" method="post">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">Excluir Representante</h4>
                </div>
                <div class="modal-body">
                  <p>Tem certeza que deseja excluir o representante: <strong>' . $row['NomeRepresentante'] . '</strong>?</p>
                  <p class="text-danger"><small>Esta ação não poderá ser desfeita.</small></p>
                  <input type="hidden" name="idRepresentante" value="' . $row['idRepresentante'] . '">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                  <button type="submit" name="excluir" class="btn btn-danger">Excluir</button>
                </div>
              </form>
            </div>
          </div>
        </div>';
      }
    }
  }

  public function listRepresentantes()
  {

    $query = "SELECT *FROM `representante`";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    if ($result) {

      while ($row = mysqli_fetch_array($result)) {
        echo '<option value="' . $row['idRepresentante'] . '">' . $row['NomeRepresentante'] . '</option>';
      }
    }
  }

  public function InsertRepresentante($NomeRepresentante, $TelefoneRepresentante, $EmailRepresentante, $Fabricante_idFabricante, $idUsuario)
  {

    $query = "INSERT INTO `representante`(`idRepresentante`, `NomeRepresentante`, `TelefoneRepresentante`, `EmailRepresentante`,`repAtivo`,`repPublic`, `Fabricante_idFabricante`, `Usuario_idUser`) VALUES (NULL, '$NomeRepresentante', '$TelefoneRepresentante', '$EmailRepresentante', 1, 1, '$Fabricante_idFabricante', '$idUsuario')";
    if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

      header('Location: ../../views/representante/index.php?alert=1');
    } else {
      header('Location: ../../views/representante/index.php?alert=0');
    }
  }

  public function UpdateRepresentante($idRepresentante, $NomeRepresentante, $TelefoneRepresentante, $EmailRepresentante, $idUsuario)
  {
    $query = "UPDATE `representante` SET `NomeRepresentante`='$NomeRepresentante',`TelefoneRepresentante`='$TelefoneRepresentante',`EmailRepresentante`='$EmailRepresentante',`Usuario_idUser`='$idUsuario' WHERE `idRepresentante` = '$idRepresentante'";

    if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

      header('Location: ../../views/representante/index.php?alert=1');
    } else {
      header('Location: ../../views/representante/index.php?alert=0');
    }
  }

  public function DelRepresentante($id)
  {
    $query = "SELECT * FROM `representante` WHERE `idRepresentante` = '$id'";
    $result = mysqli_query($this->SQL, $query);
    if ($row = mysqli_fetch_array($result)) {

      $id = $row['idRepresentante'];
      $public = $row['repPublic'];
      if ($public == 1) {
        $p = 0;
      } else {
        $p = 1;
      }

      mysqli_query($this->SQL, "UPDATE `representante` SET `repPublic` = '$p' WHERE `idRepresentante` = '$id'") or die(mysqli_error($this->SQL));
      header('Location: ../../views/representante/index.php?alert=1');
    } else {
      header('Location: ../../views/representante/index.php?alert=0');
    }
  }

  public function Ativo($value, $id)
  {
    // Verifica o valor atual no banco
    $query = "SELECT repAtivo FROM `representante` WHERE `idRepresentante` = '$id'";
    $result = mysqli_query($this->SQL, $query);
    
    if($row = mysqli_fetch_array($result)) {
        // Define o novo valor (inverte o status atual)
        $novoStatus = ($row['repAtivo'] == 1) ? 0 : 1;
        
        // Atualiza o status no banco
        $updateQuery = "UPDATE `representante` 
                       SET `repAtivo` = '$novoStatus' 
                       WHERE `idRepresentante` = '$id'";
                       
        if(mysqli_query($this->SQL, $updateQuery)) {
            // Se a atualização foi bem sucedida, redireciona com mensagem de sucesso
            header('Location: ../../views/representante/index.php?alert=1');
        } else {
            // Se houve erro na atualização, redireciona com mensagem de erro
            header('Location: ../../views/representante/index.php?alert=0');
        }
    } else {
        // Se não encontrou o representante, redireciona com mensagem de erro
        header('Location: ../../views/representante/index.php?alert=0');
    }
    exit();
  }

  public function ExcluirRepresentante($id)
  {
    // Verifica se existe o representante
    $query = "SELECT * FROM `representante` WHERE `idRepresentante` = '$id'";
    $result = mysqli_query($this->SQL, $query);
    
    if($row = mysqli_fetch_array($result)) {
        // Deleta o representante
        $deleteQuery = "DELETE FROM `representante` WHERE `idRepresentante` = '$id'";
        
        if(mysqli_query($this->SQL, $deleteQuery)) {
            header('Location: ../../views/representante/index.php?alert=1');
        } else {
            header('Location: ../../views/representante/index.php?alert=0');
        }
    } else {
        header('Location: ../../views/representante/index.php?alert=0');
    }
    exit();
  }

}

$representante = new Representante;
