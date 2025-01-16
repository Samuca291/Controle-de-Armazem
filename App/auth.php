<?php
session_start(); //Iniciando a sessão

if (!isset($_SESSION["idUsuario"]) || !isset($_SESSION["usuario"])) { // Caso o 
	header('Location: ../');
} else {
// Se o usuário estive autenticado a entrar no programa, o $_SESSION irá pegar os dados do usuário para deixá-lo conectado enqaunto a sessão (a página) estiver aberta.
	$idUsuario = $_SESSION["idUsuario"];
	$username   = $_SESSION["usuario"];
	$perm	   = $_SESSION["perm"];
	$foto      = $_SESSION["foto"];
}
