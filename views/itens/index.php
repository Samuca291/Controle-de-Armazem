<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/itens.class.php';

echo $head;
echo $header;
echo $aside;
echo '<div class="content-wrapper">
		<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Itens cadastrados
      </h1>
      <ol class="breadcrumb">
        <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Itens</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
    ';
    require '../../layout/alert.php';
    echo '
      <!-- Small boxes (Stat box) -->
      <div class="row">
      	<div class="box">
            <div class="box-header">
              <i class="ion ion-clipboard"></i>

              <h3 class="box-title">Lista de Itens</h3>

              
            </div>
            <!-- /.box-header -->
            <div class="box-body">';


        if(isset($_POST['public']) != NULL){               

          $value = $_POST['public']; 
          if($value == 1){
            $public = 0;
            $button_name = "Inativos";

          }else{
            $public = 1;
            $button_name = "Publicados";
          }     

        }else{
          $value = 1;
          $public = 0;
          $button_name = "Inativos";
        }

               $itens->index($value);
              
        echo ' </div>
            <!-- /.box-body -->
           
            <div class="box-footer clearfix no-border">
             <form action="index.php" method="post">
         <button name="public" type="submit" value="'.$public.'" class="btn btn-default pull-left"><i class="fa fa-plus"></i> '.$button_name.'</button></form>
              <a href="additens.php" type="button" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Add Itens</a>
            </div>
          </div>
	 
';

// Alertas
if (isset($_GET['alert'])) {
    switch($_GET['alert']) {
        case 0:
            echo '<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-ban"></i> Erro!</h4>
                    Erro ao realizar operação!
                  </div>';
            break;
        case 1:
            echo '<div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                    Operação realizada com sucesso!
                  </div>';
            break;
        case 4:
            echo '<div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa fa-exclamation-triangle"></i> Atenção!</h4>
                    O código de barras informado já está em uso por outro produto.
                  </div>';
            break;
    }
}

echo '</div>';
echo '</section>';
echo '</div>';

echo $footer;
echo $javascript;
?>

