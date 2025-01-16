<?php
/*
  // Para utilização em hospedagem web
    
    $ref = explode('://', $_SERVER['HTTP_REFERER']);
    $ref = $ref[0].'://';
    $url = $ref.$_SERVER['HTTP_HOST'].'/views/';  
*/
$url = 'http://localhost/Controle-de-Estoque-em-PHP/views/'; // Remova em caso de utilizar o código para hospedagem web 

$head = '<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-language" content="pt-br" /> 
  <title>GCV - Gestão de Estoque</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="' . $url . 'bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="' . $url . 'plugins/datatables/dataTables.bootstrap.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="' . $url . 'dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="' . $url . 'dist/css/skins/_all-skins.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="' . $url . 'plugins/iCheck/flat/blue.css">
  <!-- Morris chart -->
  <link rel="stylesheet" href="' . $url . 'plugins/morris/morris.css">
  <!-- jvectormap -->
  <link rel="stylesheet" href="' . $url . 'plugins/jvectormap/jquery-jvectormap-1.2.2.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="' . $url . 'plugins/datepicker/datepicker3.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="' . $url . 'plugins/daterangepicker/daterangepicker.css">

  <link rel="stylesheet" href="' . $url . 'plugins/datatables/dataTables.bootstrap.css">
  
  
  
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="' . $url . 'plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <script src="https://apis.google.com/js/platform.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

  <!-- Lista Cliente CPF -->

<script type="text/javascript">
 
 $(document).ready(function(){  
  
      $("#cpfCliente").keyup(function(){  
           var query = $(this).val();  
           if(query != "")  
           {  
                $.ajax({  
                     url:"' . $url . '../App/Database/search.php",  
                     method:"POST",  
                     data:{query:query},  
                     success:function(data)  
                     {  
                          
                          $(\'#Listdata\').fadeIn();  
                          $(\'#Listdata\').html(data);  
                     }  
                });  
           }  
      });  


      $(\'#Listdata\').on("click","li", function(){  
           $(\'#cpfCliente\').val($(this).text());  
           $(\'#Listdata\').fadeOut();
           <!-- console.log(event.target);-->
      });
  });  
 </script>

<!-- List products -->
<script type="text/javascript">
 
 $(document).ready(function(){  
  
      $("#idItem").keyup(function(){  
           var idItem = $(this).val();  
           console.log(idItem);
           if(idItem != "")  
           {  
                $.ajax({  
                     url:"' . $url . '../App/Database/searchproducts.php",  
                     method:"POST",  
                     data:{idItem:idItem},  
                     success:function(data)  
                     {  
                          console.log(data);
                          $(\'#ListProd\').fadeIn();  
                          $(\'#ListProd\').html(data); 
                           
                     }  
                });  
           }  
      });  


      $(\'#ListProd\').on("click","li", function(){  
           $(\'#idItem\').val($(this).text());  
           $(\'#ListProd\').fadeOut();
           <!-- console.log(event.target);-->
      });
  });  
 </script>
<!-- Fim List products -->


 <!-- FIM Lista Cliente CPF --> 

 <!-- Consulta Qtd venda -->

<script type="text/javascript">

 $(document).ready(function(){

      $("#prodSubmit").click(function()  {
    var prodSubmit = $("#prodSubmit").val();
    var idItens = $("#idItem").val();
    var idItens = idItens.split(\' - \');
    var idItem = idItens[0];
    var nameprod = idItens[1];
    var qtde = $("#qtd").val();

    console.log(idItem);
    
    $.ajax({
      type: "POST",
      url: "' . $url . '../App/Database/carrinho.php",
      data: {prodSubmit: prodSubmit, idItem: idItem, nameprod: nameprod, qtde:qtde},
      success: function(data){
              $(\'#listable\').fadeIn();  
              $(\'#listable\').html(data);
              document.getElementById(\'idItem\').value = null;
                          document.getElementById(\'qtd\').value = null;

          }
      });
    }); 

    $(\'#listable\').on("click","li", function(){  
           $(\'#idItem\').val($(data).text());
           $(\'#qtd\').val($(data).text());  
           $(\'#listable\').fadeOut();
          
            return false;

           <!-- console.log(event.target);-->
      });           
            
    
 });  
 </script>

<!-- Imprimir Venda -->

  <script type="text/javascript">
    
    function cont(){
       var conteudo = document.getElementById(\'print\').innerHTML;
       tela_impressao = window.open(\'about:blank\');
       tela_impressao.document.write(conteudo);
       tela_impressao.window.print();
       tela_impressao.window.close(); 
    }

</script>

<!-- Imprimir Venda --> 

  <script type="text/javascript">
    $(document).ready(function(){
    $("input[name=\'status[]\']").click(function(){
      var $this = $( this );//guardando o ponteiro em uma variavel, por performance


      var status = $this.attr(\'checked\') ? 1 : 0;
      var id = $this.next(\'input\').val();


      $.ajax({
        url: \'action.php\',
        type: \'GET\',
        data: \'status=\'+status+\'&id=\'+id,
        success: function( data ){
          alert( data );
        }
      });
    });
  }); 
  </script>



 <script type="text/javascript">
(function ($) {

    RemoveTableRow = function (handler) {
        var tr = $(handler).closest(\'tr\');

        tr.fadeOut(400, function () {
            tr.remove();
        });

        return false;
    };

    AddTableRow = function () {

        var newRow = $("<tr>");
        var cols = \'<td></td>\';
        var tabela = document.getElementById(\'products-table\');
        var a = (tabela.getElementsByTagName(\'tr\'));
        var b = a.length;
        var i = b - 2;
        var cont = 7 + i;

        cols += \'<td><input type="text" class="form-control" id="idItem" name="idItem[]" autocomplete="off" /></td>\';
        cols += \'<td><input type="text" class="form-control" id="qtd" name="qtd[]" autocomplete="off" /><span id="stv" name="stv[]"></span></td>\';
        cols += \'<td class="actions">\';
        cols += \'<button class="btn btn-danger btn-xs" onclick="RemoveTableRow(this)" type="button"><i class="fa fa-trash"></i></button>\';
        cols += \'</td>\';

        newRow.append(cols);
        $("#products-table").append(newRow);
        return false;
    };


})(jQuery);
</script>

<!-- Consulta Qtd Vendas -->

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn\'t work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <style>
  .sidebar-menu .treeview:hover > .treeview-menu {
      display: block !important;
  }

  .sidebar-menu .treeview > a {
      position: relative;
  }

  .sidebar-menu .treeview-menu {
      display: none;
      transition: all 0.3s ease;
  }

  body:not(.sidebar-collapse) .sidebar-menu .treeview:hover > .treeview-menu {
      display: block !important;
  }

  /* Estilos para a imagem do user panel */
  .sidebar-mini:not(.sidebar-collapse) .user-panel .pull-left.image {
      width: 45px;
      height: 45px;
      margin-right: 10px;
      transition: all 0.3s ease;
  }

  .sidebar-mini.sidebar-collapse .user-panel .pull-left.image {
      width: 30px;
      height: 30px;
      margin: 5px auto;
      float: none !important;
      transition: all 0.3s ease;
  }

  .user-panel .pull-left.image img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
  }

  /* Ajustes para o painel de usuário quando o sidebar está fechado */
  .sidebar-collapse .user-panel {
      padding: 5px;
      text-align: center;
  }

  .sidebar-collapse .user-panel .pull-left.info {
      display: none;
  }

  /* Ajuste para centralizar a imagem quando o sidebar está fechado */
  .sidebar-collapse .user-panel .pull-left.image {
      float: none !important;
      display: block;
      margin: 0 auto;
  }

  /* Transição suave para todos os elementos do user panel */
  .user-panel,
  .user-panel .pull-left.image,
  .user-panel .pull-left.info {
      transition: all 0.3s ease;
  }

  /* Estilos para os ícones do sidebar */
  .sidebar-menu > li > a > .fa {
      transition: all 0.3s ease;
  }

  .sidebar-collapse .sidebar-menu > li > a > .fa {
      font-size: 20px; /* Aumenta o tamanho do ícone quando retraído */
      width: 30px;
      height: 30px;
      line-height: 30px;
      margin: 0;
      text-align: center;
  }

  .sidebar-collapse .sidebar-menu > li > a {
      padding: 12px 5px;
  }

  /* Novos estilos para o nome do usuário e permissão */
  .user-panel .pull-left.info {
      padding: 9px 5px 5px 15px;  /* Aumentado o padding-top para 9px */
      margin-top: 4px;  /* Adicionado margin-top de 4px */
  }

  .user-panel .pull-left.info p {
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 0;
      position: relative;
      top: 4px;  /* Move o texto 4px para baixo */
  }

  .sidebar-mini:not(.sidebar-collapse) .user-panel .pull-left.info p {
      font-size: 13px;
      line-height: 1.3;
      white-space: normal;
      padding-right: 5px;
      position: relative;
      top: 4px;  /* Move o texto 4px para baixo também quando expandido */
  }

  .user-panel .pull-left.info p a {
      color: #fff;
      text-decoration: none;
  }

  /* Ajuste para quando o sidebar está retraído */
  .sidebar-collapse .user-panel .pull-left.info {
      display: none;
  }

  /* Estilo para o texto da permissão */
  .permission-text {
      color: #FFD700;  /* Cor dourada */
      text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);  /* Brilho dourado */
      animation: glow 1.5s ease-in-out infinite alternate;  /* Animação de brilho */
  }

  @keyframes glow {
      from {
          text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
      }
      to {
          text-shadow: 0 0 10px rgba(255, 215, 0, 0.8),
                       0 0 15px rgba(255, 215, 0, 0.3);
      }
  }

  /* Estilos para o painel de usuário e imagem */
  .user-panel {
      position: relative;
      width: 100%;
      padding: 10px;
      overflow: hidden;
      transition: all 0.3s ease;
  }

  /* Estilos para quando o sidebar está expandido */
  .sidebar-mini:not(.sidebar-collapse) .user-panel {
      display: flex;
      align-items: center;
  }

  .sidebar-mini:not(.sidebar-collapse) .user-panel .pull-left.image {
      width: 45px;
      height: 45px;
      margin-right: 10px;
  }

  /* Estilos para quando o sidebar está recolhido */
  .sidebar-mini.sidebar-collapse .user-panel {
      padding: 10px 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: auto;
  }

  .sidebar-mini.sidebar-collapse .user-panel .pull-left.image {
      width: 25px;  /* Reduzido o tamanho */
      height: 25px; /* Reduzido o tamanho */
      margin: 0;
      padding: 0;
      float: none;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      left: 0; /* Remove qualquer deslocamento */
  }

  /* Estilos comuns para a imagem */
  .user-panel .pull-left.image img {
      width: 100% !important; /* Força o tamanho da imagem */
      height: 100% !important;
      border-radius: 50%;
      object-fit: cover;
      max-width: none;
      padding: 0;
      margin: 0;
  }

  .user-panel .pull-left.image a {
      display: flex;
      width: 100%;
      height: 100%;
      justify-content: center;
      align-items: center;
      padding: 0;
      margin: 0;
  }

  /* Remove os estilos float e ajusta o posicionamento quando o sidebar está recolhido */
  .sidebar-collapse .user-panel .pull-left {
      float: none !important;
      position: static;
  }

  .sidebar-collapse .user-panel {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 45px;
      padding: 10px 0;
  }

  /* Remove margens e paddings desnecessários no modo recolhido */
  .sidebar-collapse .main-sidebar .user-panel {
      margin: 0;
      padding: 10px 0;
  }

  /* Fixar header e sidebar */
  .main-header {
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
  }

  .main-sidebar {
      position: fixed;
      top: 50px;
      bottom: 0;
      left: 0;
      padding-top: 0;
      min-height: 100%;
      z-index: 810;
      overflow: visible;
  }

  /* Ajuste do conteúdo principal */
  .content-wrapper {
      margin-top: 50px;
  }

  /* Ajuste para o scroll do sidebar */
  .sidebar {
      height: auto;
      overflow: visible;
  }

  /* Ajuste para o treeview-menu quando o sidebar está fechado */
  .sidebar-mini.sidebar-collapse .sidebar-menu > li:hover > .treeview-menu {
      display: block !important;
      position: absolute;
      left: 50px;
      top: 0;
      margin: 0;
      padding: 10px 0;
      width: 180px;
      background: #2c3b41;
      z-index: 1000;
  }

  /* Garante que o item pai tenha posição relativa */
  .sidebar-mini.sidebar-collapse .sidebar-menu > li {
      position: relative !important;
  }

  /* Garante que o submenu fique visível */
  .sidebar-mini.sidebar-collapse .sidebar-menu > li:hover {
      overflow: visible !important;
  }
  </style>

</head>
<body class="skin-blue sidebar-mini sidebar-collapse" style="height: auto; min-height: 100%;">
<div class="wrapper" style="height: auto; min-height: 100%;">';

$header = '<header class="main-header">
    <!-- Logo -->
    <a href="' . $url . '" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b>A</b>LT</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>GCV</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
         

            </a>
            <ul class="dropdown-menu">
              <li class="header">You have 4 messages</li>
              <li>
                <!-- inner menu: contains the actual data -->
                <ul class="menu">
                  <li><!-- start message -->
                    <a href="#">
                      <div class="pull-left">
                        <img src="' . $url . 'dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                      </div>
                      <h4>
                        Support Team
                        <small><i class="fa fa-clock-o"></i> 5 mins</small>
                      </h4>
                      <p>Why not buy a new awesome theme?</p>
                    </a>
                  </li>
                  <!-- end message -->
                  <li>
                    <a href="#">
                      <div class="pull-left">
                        <img src="' . $url . 'dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                      </div>
                      <h4>
                        AdminLTE Design Team
                        <small><i class="fa fa-clock-o"></i> 2 hours</small>
                      </h4>
                      <p>Why not buy a new awesome theme?</p>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <div class="pull-left">
                        <img src="' . $url . 'dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">
                      </div>
                      <h4>
                        Developers
                        <small><i class="fa fa-clock-o"></i> Today</small>
                      </h4>
                      <p>Why not buy a new awesome theme?</p>
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <div class="pull-left">
                        <img src="' . $url . 'dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                      </div>
                      <h4>
                      
                </ul>
              </li>
              <li class="footer"><a href="#">See All Messages</a></li>
            </ul>
          </li>
          <!-- Notifications: style can be found in dropdown.less -->

          <!-- Tasks: style can be found in dropdown.less -->

          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <img src="' . $url . '' . $foto . '" class="user-image" alt="User Image">
              <span class="hidden-xs">' . $username . '</span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <img src="' . $url . '' . $foto . '" class="img-circle" alt="User Image">

                <p>
                  ' . $username . ' - ';
switch ($perm) {

  case 0:
    $header .= 'Cliente';
    break;
  case 1:
    $header .= 'Administrador';
    break;
  case 2:
    $header .= 'Vendedor';
    break;
}

$header .= '
                </p>
              </li>
              <!-- Menu Body -->
             
                <!-- /.row -->
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                
                </div>
              <div style="text-align: center;">
  <a href="' . $url . 'destroy.php" class="btn btn-default btn-flat">Sair</a>
</div>

              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
          </li>
        </ul>
      </div>
    </nav>
  </header>';

$aside = '<!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <a href="' . $url . 'usuarios/">
            <img src="' . $url . '' . $foto . '" class="img-circle" style="height:40px; width:40px;" alt="User Image">
          </a>
        </div>
        <div class="pull-left info">
          <a href="' . $url . 'usuarios/">
            <p>' . strtoupper($username) . ' - <span class="permission-text">';
            
// Adiciona a permissão do usuário
switch ($perm) {
    case 0:
        $aside .= 'Cliente';
        break;
    case 1:
        $aside .= 'Administrador';
        break;
    case 2:
        $aside .= 'Vendedor';
        break;
}

$aside .= '</span></p>
          </a>
        </div>
      </div>

      <ul class="sidebar-menu">
        <li class="active treeview">
          <a href="' . $url . '">
            <i class="fa fa-home"></i> <span>Dashboard</span>
          </a>
        </li>

        <li class="treeview">
          <a href="' . $url . 'prod/">
            <i class="fa fa-cubes" onclick="window.location.href=\'' . $url . 'prod/\'"></i>
            <span>Produtos</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="' . $url . 'prod/"><i class="fa fa-cube"></i>Produtos</a></li>
            <li><a href="' . $url . 'prod/addprod.php"><i class="fa fa-plus-circle"></i>Add Produtos</a></li>
            <li><a href="' . $url . 'itens/"><i class="fa fa-list"></i>Itens</a></li>
            <li><a href="' . $url . 'itens/totalitens.php"><i class="fa fa-calculator"></i>Total Itens</a></li>
            <li><a href="' . $url . 'itens/additens.php"><i class="fa fa-plus-circle"></i>Add Itens</a></li>
          </ul>
        </li>';

if ($perm != 2) {
    $aside .= '<li class="treeview">
          <a href="' . $url . 'relatorios/">
            <i class="fa fa-bar-chart" onclick="window.location.href=\'' . $url . 'relatorios/\'"></i>
            <span>Relatorios</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="' . $url . 'relatorios/"><i class="fa fa-line-chart"></i>Relatorios Produtos</a></li>
            <li><a href="' . $url . 'relatorios/dashboard_vendas.php"><i class="fa fa-dashboard"></i>Dashboard de Vendas</a></li>
          </ul>
        </li>

        <li class="treeview">
          <a href="' . $url . 'fabricante/">
            <i class="fa fa-industry" onclick="window.location.href=\'' . $url . 'fabricante/\'"></i>
            <span>Fabricante</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="' . $url . 'fabricante/"><i class="fa fa-building"></i>Fabricantes</a></li>
            <li><a href="' . $url . 'fabricante/addfabricante.php"><i class="fa fa-plus-circle"></i>Add Fabricante</a></li>
          </ul>
        </li>';
}

$aside .= '<li class="treeview">
          <a href="' . $url . 'representante/">
            <i class="fa fa-suitcase" onclick="window.location.href=\'' . $url . 'representante/\'"></i>
            <span>Representante</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="' . $url . 'representante/"><i class="fa fa-user-md"></i>Representantes</a></li>
            <li><a href="' . $url . 'representante/addrepresentante.php"><i class="fa fa-plus-circle"></i>Add Representante</a></li>
          </ul>
        </li>';

if ($perm != 2) {
    $aside .= '<li class="treeview">
          <a href="' . $url . 'usuarios/">
            <i class="fa fa-user" onclick="window.location.href=\'' . $url . 'usuarios/\'"></i>
            <span>Usuários</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="' . $url . 'usuarios/"><i class="fa fa-group"></i>Lista</a></li>
            <li><a href="' . $url . 'usuarios/addusuarios.php"><i class="fa fa-user-plus"></i>Add Usuários</a></li>
          </ul>
        </li>';
}

$aside .= '<li class="treeview">
          <a href="' . $url . 'vendas/">
            <i class="fa fa-shopping-cart" onclick="window.location.href=\'' . $url . 'vendas/\'"></i>
            <span>Vendas</span>
            <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li><a href="' . $url . 'vendas/"><i class="fa fa-money"></i>Vendas</a></li>
          </ul>
        </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>';

$footer = '<footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Version</b> 2.3.8
    </div>
    <strong>Copyright &copy; GCV</strong> Todos os direitos reservados.<br>
    <small>Contato: <a href="mailto:toledosamuel400@gmail.com">toledosamuel400@gmail.com</a> | Tel: (22) 9992-22049</small>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane" id="control-sidebar-home-tab">
        <h3 class="control-sidebar-heading">Recent Activity</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-birthday-cake bg-red"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Langdon\'s Birthday</h4>

                <p>Will be 23 on April 24th</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-user bg-yellow"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                <p>New phone +1(800)555-1234</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                <p>nora@example.com</p>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <i class="menu-icon fa fa-file-code-o bg-green"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                <p>Execution time 5 seconds</p>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

        <h3 class="control-sidebar-heading">Tasks Progress</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Custom Template Design
                <span class="label label-danger pull-right">70%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Update Resume
                <span class="label label-success pull-right">95%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-success" style="width: 95%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Laravel Integration
                <span class="label label-warning pull-right">50%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
              </div>
            </a>
          </li>
          <li>
            <a href="javascript:void(0)">
              <h4 class="control-sidebar-subheading">
                Back End Framework
                <span class="label label-primary pull-right">68%</span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

      </div>
      <!-- /.tab-pane -->
      <!-- Stats tab content -->
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <!-- /.tab-pane -->
      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Report panel usage
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Some information about this general settings option
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Allow mail redirect
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Other sets of options are available
            </p>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Expose author name in posts
              <input type="checkbox" class="pull-right" checked>
            </label>

            <p>
              Allow the user to show his name in blog posts
            </p>
          </div>
          <!-- /.form-group -->

          <h3 class="control-sidebar-heading">Chat Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Show me as online
              <input type="checkbox" class="pull-right" checked>
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Turn off notifications
              <input type="checkbox" class="pull-right">
            </label>
          </div>
          <!-- /.form-group -->

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Delete chat history
              <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
            </label>
          </div>
          <!-- /.form-group -->
        </form>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar\'s background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>';

$javascript = '

  </div>
<!-- jQuery 2.2.3 -->
<script src="https://code.jquery.com/jquery-2.2.3.js"></script>

<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge(\'uibutton\', $.ui.button);
</script>
<!-- Bootstrap 3.3.6 -->
<script src="' . $url . 'bootstrap/js/bootstrap.min.js"></script>
<!-- Morris.js charts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="' . $url . 'plugins/morris/morris.min.js"></script>
<!-- Sparkline -->
<script src="' . $url . 'plugins/sparkline/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="' . $url . 'plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="' . $url . 'plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- jQuery Knob Chart -->
<script src="' . $url . 'plugins/knob/jquery.knob.js"></script>
<!-- daterangepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="' . $url . 'plugins/datatables/jquery.dataTables.min.js"></script>
<script src="' . $url . 'plugins/datatables/dataTables.bootstrap.min.js"></script>

<script src="' . $url . 'plugins/daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="' . $url . 'plugins/datepicker/bootstrap-datepicker.js"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="' . $url . 'plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<!-- Slimscroll -->
<script src="' . $url . 'plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="' . $url . 'plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="' . $url . 'dist/js/app.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="' . $url . 'dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="' . $url . 'dist/js/demo.js"></script>
<script>
  
  $(function () {
    $(\'#example1\').DataTable()
    $(\'#example2\').DataTable({
      \'paging\'      : true,
      \'lengthChange\': false,
      \'searching\'   : false,
      \'ordering\'    : true,
      \'info\'        : true,
      \'autoWidth\'   : false
    })
})
</script>

<script>
$(document).ready(function() {
    // Manipulador para hover do submenu
    $(".sidebar-menu .treeview").hover(
        function() {
            if(!$("body").hasClass("sidebar-collapse")) {
                $(this).find(".treeview-menu").stop().slideDown(300);
            }
        },
        function() {
            if(!$("body").hasClass("sidebar-collapse")) {
                $(this).find(".treeview-menu").stop().slideUp(300);
            }
        }
    );

    // Manipulador para clique no item principal do treeview
    $(".sidebar-menu .treeview > a").click(function(e) {
        e.preventDefault();
        e.stopPropagation();
        var firstLink = $(this).siblings(".treeview-menu").find("li:first-child a").attr("href");
        if(firstLink) {
            window.location.href = firstLink;
        }
    });
});
</script>

</body>
</html>
';
