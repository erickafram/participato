<?php
require_once "../includes/db_connection.php";

if (isset($_GET["id"])) {
    $politico_id = $_GET["id"];
    // Defina a consulta SQL para obter as informações do político
    $query = "SELECT nome, partido, pontuacao, telefone, email FROM politicos WHERE id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $politico_id);
        $stmt->execute();
        $stmt->bind_result($nome, $partido, $pontuacao, $telefone, $email);
        $stmt->fetch();

        // Formatando as informações em HTML
        $politicoInfo = "Nome: $nome<br>Partido: $partido<br>Pontos: $pontuacao<br>Telefone: $telefone<br>Email: $email";

        echo $politicoInfo;
    } else {
        echo "Erro na preparação da consulta: " . $conn->error;
    }
} else {
    echo "ID do político não especificado.";
}
?>
