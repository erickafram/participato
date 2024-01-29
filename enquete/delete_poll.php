<?php
session_start();
include('../includes/db_connection.php');

if (isset($_GET['id'])) {
    $poll_id = intval($_GET['id']);

    // Primeiro, excluir registros relacionados em votos_enquete
    $sqlDeleteVotes = "DELETE FROM votos_enquete WHERE id_opcao IN (SELECT id FROM opcoes_enquete WHERE id_enquete = ?)";
    $stmtDeleteVotes = $conn->prepare($sqlDeleteVotes);
    $stmtDeleteVotes->bind_param("i", $poll_id);
    $stmtDeleteVotes->execute();

    // Em seguida, excluir a enquete
    $sql = "DELETE FROM enquetes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $poll_id);

    if ($stmt->execute()) {
        header("Location: list_poll.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro ao excluir a enquete: " . $stmt->error . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>ID da enquete não fornecido.</div>";
}
?>