<?php
require_once '../auth.php';
require_once '../../App/Models/connect.php';

if (isset($_GET['query'])) {
    $query = filter_var($_GET['query'], FILTER_SANITIZE_STRING);
    $connect = new Connect();
    $connection = $connect->SQL;

    $sql = "SELECT NomeProduto FROM produtos WHERE NomeProduto LIKE ? AND Ativo = 1 LIMIT 10";
    $stmt = $connection->prepare($sql);
    $searchTerm = "%{$query}%";
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['NomeProduto'];
    }

    echo json_encode($suggestions);
}
?> 