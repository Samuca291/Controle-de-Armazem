<?php
require_once '../auth.php';
require_once '../Models/representante.class.php';

if(isset($_POST['excluir']) && isset($_POST['idRepresentante'])) {
    $id = mysqli_real_escape_string($representante->SQL, $_POST['idRepresentante']);
    $representante->ExcluirRepresentante($id);
} else {
    header('Location: ../../views/representante/index.php?alert=0');
}
?> 