<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

if (isset($_GET["id"])) {
    $petition_id = $_GET["id"];

    // Excluir a petição do banco de dados
    $query = "DELETE FROM abaixo_assinados WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $petition_id);

    if ($stmt->execute()) {
        header("Location: petitions_list.php?message=Petição excluída com sucesso");
    } else {
        header("Location: petitions_list.php?message=Erro ao excluir petição");
    }

    $stmt->close();
} else {
    header("Location: petitions_list.php?message=ID da petição não fornecido");
}

$conn->close();
?>
