<?php
require_once '../../App/auth.php';
require_once '../../layout/script.php';
require_once '../../App/Models/representante.class.php';

echo $head;
echo $header;
echo $aside;
?>

<div class="content-wrapper">
    <section class="content-header">
      <div class="row">
        <div class="col-md-12">
          <div class="title-header">
            <h1>
              Gerenciamento de Representantes
              <small>Controle e listagem</small>
            </h1>
            <ol class="breadcrumb">
              <li><a href="../"><i class="fa fa-dashboard"></i> Home</a></li>
              <li class="active">Representantes</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <?php require '../../layout/alert.php'; ?>
      
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <div class="box-header-content">
                <i class="ion ion-person-stalker"></i>
                <h3 class="box-title">Representantes Cadastrados</h3>
              </div>
              
              <?php
              if(isset($_POST['public']) != NULL) {               
                $value = $_POST['public']; 
                if($value == 1) {
                  $public = 0;
                  $button_name = "Listar Desativados";
                } else {
                  $public = 1;
                  $button_name = "Listar Ativos";
                }     
              } else {
                $value = 1;
                $public = 0;
                $button_name = "Listar Desativados";
              }
              ?>

              <div class="box-tools">
                <div class="btn-group">
                  <form action="index.php" method="post" class="pull-right">
                    <div class="button-group">
                      <button name="public" type="submit" value="<?php echo $public; ?>" class="btn btn-default">
                        <i class="fa fa-filter"></i> <?php echo $button_name; ?>
                      </button>
                      <a href="addrepresentante.php" class="btn btn-success">
                        <i class="fa fa-plus"></i> Novo Representante
                      </a>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <div class="box-body">
              <div class="table-responsive">
                <table class="table table-hover table-striped">
                  <thead>
                    <tr>
                      <th width="5%">Status</th>
                      <th width="25%">Fabricante</th>
                      <th width="25%">Nome</th>
                      <th width="20%">Telefone</th>
                      <th width="15%">Email</th>
                      <th width="10%">Ações</th>
                    </tr>
                  </thead>
                  <tbody class="todo-list">
                    <?php $representante->index($value); ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="box-footer clearfix">
              <div class="pull-left">
                <small class="text-muted">
                  <i class="fa fa-info-circle"></i> 
                  Clique no checkbox para ativar/desativar o representante
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
</div>

<?php
echo $footer;
echo $javascript;
?>

<style>
.title-header {
    margin-bottom: 20px;
}

.box-header-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.box-header-content i {
    font-size: 20px;
    color: #3c8dbc;
}

.box-tools .button-group {
    display: flex;
    gap: 10px;
}

.table > tbody > tr {
    transition: all 0.2s ease;
}

.table > tbody > tr:hover {
    background-color: #f5f5f5;
}

.table > tbody > tr.item-desativado {
    background-color: #fde9e8;
}

.table > tbody > tr.item-desativado td {
    color: #dd4b39;
}

.table > tbody > tr .badge {
    padding: 5px 12px;
    border-radius: 3px;
    font-weight: 600;
    background-color: #3c8dbc;
}

.table > tbody > tr.item-desativado .badge {
    background-color: #dd4b39;
}

.table > tbody > tr .tools {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.table > tbody > tr .tools a {
    color: #3c8dbc;
    transition: color 0.2s ease;
}

.table > tbody > tr.item-desativado .tools a {
    color: #dd4b39;
}

.label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin: 0;
}

@media (max-width: 768px) {
    .box-tools .button-group {
        flex-direction: column;
        width: 100%;
    }

    .box-tools .button-group button,
    .box-tools .button-group a {
        width: 100%;
        margin-bottom: 5px;
        text-align: center;
    }

    .table-responsive {
        border: 0;
    }

    .table > thead > tr > th,
    .table > tbody > tr > td {
        white-space: nowrap;
    }

    .table > tbody > tr .tools {
        min-width: 80px;
    }
}

/* Estilos para impressão */
@media print {
    .no-print {
        display: none !important;
    }
    
    .table {
        border-collapse: collapse !important;
    }
    
    .table td,
    .table th {
        background-color: #fff !important;
    }
}

.table > tbody > tr .tools a.text-danger {
    color: #dd4b39;
}

.table > tbody > tr .tools a.text-danger:hover {
    color: #c9302c;
}

.modal-body .text-danger {
    margin-top: 10px;
}
</style>

<script>
$(document).ready(function() {
    // Inicializa o DataTable
    $('.table').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
        }
    });

    // Tooltip para os botões de ação
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

