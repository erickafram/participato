<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

// Verifica se o usuário está logado e é um administrador
if (!isset($_SESSION["user_id"]) || $_SESSION["user_tipo"] !== "administrador") {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifica se o ID do abaixo-assinado foi passado
if (isset($_GET['id'])) {
    $petitionId = $_GET['id'];

    // Consulta para atualizar o status do abaixo-assinado para 'rejeitado'
    $query = "UPDATE abaixo_assinados SET status = 'rejeitado' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $petitionId);

    if ($stmt->execute()) {
        // Redireciona de volta para o painel com uma mensagem de sucesso
        $_SESSION['successMessage'] = "Abaixo-assinado rejeitado com sucesso.";
    } else {
        // Redireciona de volta para o painel com uma mensagem de erro
        $_SESSION['errorMessage'] = "Erro ao rejeitar o abaixo-assinado.";
    }

    $stmt->close();
} else {
    $_SESSION['errorMessage'] = "ID do abaixo-assinado não especificado.";
}

header("Location: dashboard.php");
exit();
?>
