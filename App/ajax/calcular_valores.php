<?php
require_once '../Models/calculadora.class.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesoTotal = floatval($_POST['pesoTotal']);
    $valorTotal = floatval($_POST['valorTotal']);
    $quantidadeDesejada = floatval($_POST['quantidadeDesejada']);
    $valorVendaDesejado = floatval($_POST['valorVendaDesejado']);

    $calculadora = new CalculadoraProduto($valorTotal, $pesoTotal);
    $resultado = $calculadora->calcularValores($quantidadeDesejada, $valorVendaDesejado);
    
    // Adiciona os valores da request ao resultado
    $resultado['quantidadeDesejada'] = $quantidadeDesejada;
    $resultado['valorVendaDesejado'] = $valorVendaDesejado;

    header('Content-Type: application/json');
    echo json_encode($resultado);
    exit;
}
