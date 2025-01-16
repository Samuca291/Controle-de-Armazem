<?php
require_once '../auth.php';
require_once '../Models/itens.class.php';

if (isset($_POST['upload']) == 'Cadastrar') {

	$QuantItens = $_POST['QuantItens'];
	$ValCompItens = $_POST['ValCompItens'];
	$ValVendItens = $_POST['ValVendItens'];
	$DataCompraItens = $_POST['DataCompraItens'];
	$DataVenci_Itens = $_POST['DataVenci_Itens'];
	$Produto_CodRefProduto = $_POST['codProduto'];
	$Fabricante_idFabricante = $_POST['idFabricante'];

	$iduser = $_POST['iduser'];

	if ($iduser == $idUsuario && $QuantItens != NULL) {

		if (!file_exists($_FILES['arquivo']['name'])) {

			$pt_file =  '../../views/dist/img/products/' . $_FILES['arquivo']['name'];

			if ($pt_file != '../../views/dist/img/products/') {

				$destino =  '../../views/dist/img/products/' . $_FILES['arquivo']['name'];
				$arquivo_tmp = $_FILES['arquivo']['tmp_name'];
				move_uploaded_file($arquivo_tmp, $destino);
				chmod($destino, 0644);

				$nomeimagem =  'dist/img/products/' . $_FILES['arquivo']['name'];
			} elseif ($_POST['valor'] != NULL) {

				$nomeimagem = $_POST['valor'];
			}
		}

		if (isset($_POST['idItens'])) {

			$idItens = $_POST['idItens'];
			$codigoBarras = !empty($_POST['codigoBarras']) ? trim($_POST['codigoBarras']) : null;
            
            // Verifica código de barras duplicado
            if ($codigoBarras && !$itens->verificaCodigoBarras($codigoBarras, $idItens)) {
                header('Location: ../../views/itens/edititens.php?q=' . $idItens . '&alert=4'); // Código duplicado
                exit;
            }
            
			$itens->updateItens($idItens, $nomeimagem, $QuantItens, $ValCompItens, $ValVendItens, $DataCompraItens, $DataVenci_Itens, $Produto_CodRefProduto, $Fabricante_idFabricante, $idUsuario, $codigoBarras);
		} else {
			$codigoBarras = !empty($_POST['codigoBarras']) ? trim($_POST['codigoBarras']) : null;
            
            // Verifica código de barras duplicado
            if ($codigoBarras && !$itens->verificaCodigoBarras($codigoBarras, null)) {
                header('Location: ../../views/itens/index.php?alert=4'); // Código duplicado
                exit;
            }
            
			$itens->InsertItens($nomeimagem, $QuantItens, $ValCompItens, $ValVendItens, $DataCompraItens, $DataVenci_Itens, $Produto_CodRefProduto, $Fabricante_idFabricante, $idUsuario, $codigoBarras);
		}
	} else {
		header('Location: ../../views/itens/index.php?alert=3');
	}
} else {
	header('Location: ../../views/itens/index.php');
}
