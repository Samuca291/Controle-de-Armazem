<?php
if(isset($_GET['alert']) || isset($_SESSION['alert'])){

	echo '<div class="row">
        <div class="col-md-12">';

	$alert = (isset($_SESSION['alert']))? $_SESSION['alert'] : $_GET['alert'] ;

	switch($alert){

		case 0:
			$alert = "danger";
			$mensagem ="Erro ao realizar operação!";
			echo '<div class="alert alert-'.$alert.' alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> '.$mensagem.'</h4>
                
              </div>';
			break;

		case 1:
			$alert = "success";
			$mensagem ="Operação realizada com sucesso!";
			echo '<div class="alert alert-'.$alert.' alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> '.$mensagem.'</h4>
                
              </div>';
			break;

		case 2:
			$alert = "warning";
			$mensagem ="Não é possível excluir este fabricante pois existem itens associados a ele!";
			echo '<div class="alert alert-'.$alert.' alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-warning"></i> Atenção!</h4>
                '.$mensagem.'
              </div>';
			break;

		case 3:
			$alert = "success";
			$mensagem ="Fabricante excluído com sucesso!";
			echo '<div class="alert alert-'.$alert.' alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-check"></i> Sucesso!</h4>
                '.$mensagem.'
              </div>';
			break;

		case 4:
			$alert = "danger";
			$mensagem ="Erro ao excluir fabricante!";
			echo '<div class="alert alert-'.$alert.' alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-ban"></i> Erro!</h4>
                '.$mensagem.'
              </div>';
			break;

		case 'error_item':
			echo '<div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h4><i class="icon fa fa-warning"></i> Atenção!</h4>
                Não é possível excluir este item pois existem vendas vinculadas a ele.
              </div>';
			break;
		
		}//switch
	echo '</div>
        </div>';

	unset($_GET['alert'], $_SESSION['alert']);
}
?>