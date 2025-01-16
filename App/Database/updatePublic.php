<?php
require_once '../auth.php';
require_once '../Models/fabricante.class.php';

if(isset($_POST['id']) && isset($_POST['current_public'])) {
    $id = mysqli_real_escape_string($connect->SQL, $_POST['id']);
    $currentPublic = mysqli_real_escape_string($connect->SQL, $_POST['current_public']);
    
    // Novo valor de Public será o oposto do atual
    $novoPublic = ($currentPublic == 1) ? 0 : 1;
    
    // Atualiza o Public do fabricante
    $query = "UPDATE fabricante SET Public = '$novoPublic' WHERE idFabricante = '$id'";
    if(mysqli_query($connect->SQL, $query)) {
        // Atualiza também o Public dos representantes relacionados
        $query = "UPDATE representante SET repPublic = '$novoPublic' WHERE Fabricante_idFabricante = '$id'";
        mysqli_query($connect->SQL, $query);
        
        header('Location: ../../views/fabricante/index.php');
    } else {
        header('Location: ../../views/fabricante/index.php?alert=0');
    }
} else {
    header('Location: ../../views/fabricante/index.php');
}
exit();
?>
