<?php

/*
     Class produtos
    */

require_once 'connect.php';

class Fabricante extends Connect
{

  public function index($perm, $value = NULL)
  {

    if ($perm != 1) {
      echo "Você não tem permissão!";
    } else {

      if ($value == NULL) {
        $value = 1;
      }

      $query = "SELECT * FROM `fabricante` WHERE `Public` = '$value'";
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
                   
                    <form class="label" name="ativ' . $row['idFabricante'] . '" action="../../App/Database/action.php" method="post">
                    <input type="hidden" name="id" value="' . $row['idFabricante'] . '">
                    <input type="hidden" name="current_status" value="' . $row['Ativo'] . '">
                    <input type="hidden" name="tabela" value="fabricante">                  
                    <input type="checkbox" name="status" ';
          if ($row['Ativo'] == 1) {
            echo 'checked';
          }
          echo ' onclick="this.form.submit();" />
                    </form>
                      
                      <!-- todo text -->
  <span class="text"> ' . $row['NomeFabricante'] . ' </span>
                     
                      <div class="tools right">

                      <a href="editfabricante.php?id=' . $row['idFabricante'] . '"><i class="fa fa-edit"></i></a> 
                        <a href="" data-toggle="modal" data-target="#myModal' . $row['idFabricante'] . '">';
          if ($row['Public'] == 0) {
            echo '<i class="glyphicon glyphicon-remove" aria-hidden="true"></i>';
          } else {
            echo '<i class="glyphicon glyphicon-ok" aria-hidden="true"></i>';
          }
          echo '</a>
                        <a href="" data-toggle="modal" data-target="#deleteModal' . $row['idFabricante'] . '">
                          <i class="fa fa-trash text-danger"></i>
                        </a>
                      </div>

                      <!-- Status Modal -->
                      <div class="modal fade" id="myModal' . $row['idFabricante'] . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <form id="delFab' . $row['idFabricante'] . '" name="delFab' . $row['idFabricante'] . '" action="../../App/Database/updatePublic.php" method="post" style="color:#000;">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Alterar Visibilidade do Fabricante</h4>
                              </div>
                              <div class="modal-body">
                                Deseja mover o fabricante ' . $row['NomeFabricante'] . ' para a lista de ';
          echo ($row['Public'] == 1) ? 'desativados' : 'ativos';
          echo '?
                              </div>
                              <input type="hidden" name="id" value="' . $row['idFabricante'] . '">
                              <input type="hidden" name="current_public" value="' . $row['Public'] . '">
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Não</button>
                                <button type="submit" class="btn btn-primary">Sim</button>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>

                      <!-- Delete Modal -->
                      <div class="modal fade" id="deleteModal' . $row['idFabricante'] . '" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel">
                        <form id="delFabricante' . $row['idFabricante'] . '" name="delFabricante' . $row['idFabricante'] . '" action="../../App/Database/delFabricante.php" method="post" style="color:#000;">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="deleteModalLabel">Excluir Fabricante</h4>
                              </div>
                              <div class="modal-body">
                                Tem certeza que deseja excluir o fabricante: ' . $row['NomeFabricante'] . '?
                              </div>
                              <input type="hidden" id="idFabricante" name="idFabricante" value="' . $row['idFabricante'] . '">
                              <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Não</button>
                                <button type="submit" name="deletar" value="Cadastrar" class="btn btn-danger">Sim</button>
                              </div>
                            </div>
                          </div>
                        </form>
                      </div>
  </li>';
        }
      }
    }
  }

  public function listfabricante()
  {


    $query = "SELECT * FROM `fabricante` WHERE (`Public` = 1) AND (`Ativo` = 1)";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    if ($result) {

      while ($row = mysqli_fetch_array($result)) {

        echo '<option value="' . $row['idFabricante'] . '">' . $row['NomeFabricante'] . '</option>';
      }
    }
  }

  public function InsertFabricante($NomeFabricante, $CNPJFabricante, $EmailFabricante, $EnderecoFabricante, $TelefoneFabricante, $idUsuario, $NomeRepresentante, $TelefoneRepresentante, $EmailRepresentante, $status, $perm)
  {

    if ($perm != 1) {
      echo "Você não tem permissão!";
    } else {

      $query = "SELECT * FROM `fabricante` WHERE `NomeFabricante` = '$NomeFabricante'";
      $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

      /*--Alteração de codigo para corriguir erro de verificação 
        se fabricante existe ou não no DB. */

      $total = mysqli_num_rows($result);

      if ($total > 0) {
        $row = mysqli_fetch_array($result);

        $idFabricante = $row['idFabricante'];
      } else {

        $query = "INSERT INTO `fabricante`(`NomeFabricante`, `CNPJFabricante`, `EmailFabricante`, `EnderecoFabricante`, `TelefoneFabricante`, `Public`, `Ativo`, `Usuario_idUser`) VALUES ('$NomeFabricante', '$CNPJFabricante', '$EmailFabricante', '$EnderecoFabricante', '$TelefoneFabricante', 1 , 1 , '$idUsuario')";

        $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));
        $idFabricante = mysqli_insert_id($this->SQL);
      }

      if ($idFabricante > 0) {

        $query = "INSERT INTO `representante`(`idRepresentante`, `NomeRepresentante`, `TelefoneRepresentante`, `EmailRepresentante`,`repAtivo`,`repPublic`, `Fabricante_idFabricante`, `Usuario_idUser`) VALUES (NULL, '$NomeRepresentante', '$TelefoneRepresentante', '$EmailRepresentante',1 , 1,'$idFabricante', '$idUsuario')";

        if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {
          header('Location: ../../views/fabricante/index.php?alert=1');
        } else {
          header('Location: ../../views/fabricante/index.php?alert=0');
        }
      } else {
        header("Location: ../../views/fabricante/index.php?alert=0");
      }
    } //Insert
  }



  public function EditFabricante($idFabricante)
  {

    $query = "SELECT *FROM `fabricante` WHERE `idFabricante` = '$idFabricante'";
    if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

      if ($row = mysqli_fetch_array($result)) {

        $NomeFabricante = $row['NomeFabricante'];
        $CNPJFabricante = $row['CNPJFabricante'];
        $EmailFabricante = $row['EmailFabricante'];
        $EnderecoFabricante = $row['EnderecoFabricante'];
        $TelefoneFabricante = $row['TelefoneFabricante'];
        $Ativo = $row['Ativo'];
        $Usuario_idUser  = $row['Usuario_idUser'];

        $array = array('Fabricante' => array('Nome' => $NomeFabricante, 'CNPJ' => $CNPJFabricante, 'Email' => $EmailFabricante, 'Endereco' => $EnderecoFabricante, 'Telefone' => $TelefoneFabricante, 'Ativo' => $Ativo, 'Usuario' => $Usuario_idUser),);
        return $array;
      }
    } else {
      return 0;
    }
  }

  public function UpdateFabricante($idFabricante, $NomeFabricante, $CNPJFabricante, $EmailFabricante, $EnderecoFabricante, $TelefoneFabricante, $Ativo, $idUsuario, $perm)
  {

    if ($perm != 1) {
      echo "Você não tem permissão!";
    } else {

      $query = "UPDATE `fabricante` SET `NomeFabricante`= '$NomeFabricante', `CNPJFabricante`='$CNPJFabricante',`EmailFabricante`='$EmailFabricante',`EnderecoFabricante`='$EnderecoFabricante',`TelefoneFabricante`='$TelefoneFabricante', `Ativo` = '$Ativo' ,`Usuario_idUser`='$idUsuario' WHERE `idFabricante` = '$idFabricante'";

      if ($result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL))) {

        header('Location: ../../views/fabricante/index.php?alert=5');
      } else {
        header('Location: ../../views/fabricante/index.php?alert=0');
      }
    }
  } //update


  public function DelFabricante($idFabricante, $perm)
  {
    if ($perm != 1) {
      echo "Você não tem permissão!";
      exit();
    }

    // Check for associated items first
    $query = "SELECT COUNT(*) as total FROM itens WHERE Fabricante_idFabricante = '$idFabricante'";
    $result = mysqli_query($this->SQL, $query);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['total'] > 0) {
      header('Location: ../../views/fabricante/index.php?alert=2'); // Cannot delete - has items
      exit();
    }
    
    // Delete associated representatives first
    mysqli_query($this->SQL, "DELETE FROM representante WHERE Fabricante_idFabricante = '$idFabricante'") or die(mysqli_error($this->SQL));
    
    // Now delete the manufacturer
    $deleteQuery = "DELETE FROM fabricante WHERE idFabricante = '$idFabricante'";
    if (mysqli_query($this->SQL, $deleteQuery)) {
      header('Location: ../../views/fabricante/index.php?alert=3'); // Successfully deleted
    } else {
      header('Location: ../../views/fabricante/index.php?alert=4'); // Error deleting
    }
    exit();
  }

  public function Ativo($value, $id)
  {
    if ($value == 0) {
      $v = 1;
    } else {
      $v = 0;
    }

    $query = "UPDATE `fabricante` SET `Ativo` = '$v' WHERE `idFabricante` = '$id'";
    $result = mysqli_query($this->SQL, $query) or die(mysqli_error($this->SQL));

    header('Location: ../../views/fabricante/index.php');
  }
}

$fabricante = new Fabricante;
