<?php
session_start();

$username = $_POST['username'];
$password  = md5($_POST['password']);  // MD5 deixa a senha criptografada

if ($username == NULL || $password == NULL) {

	echo "<script>alert('Para login, deve preencher o campo de Nome e Senha! ');</script>";
	echo "<script> window.location.href='../login.php'</script>";
	exit;
} else {

	require_once 'Models/connect.php';

	$connect->login($username, $password);
}
