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

// Verifique se o ID da atualização a ser excluída foi fornecido via POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["atualizacao_id"])) {
    $atualizacao_id = $_POST["atualizacao_id"];

    // Consulta para excluir a atualização do banco de dados
    $sql = "DELETE FROM atualizacoes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $atualizacao_id);

    if ($stmt->execute()) {
        header("Location: add_update.php?id=" . $_POST["peticao_id"]); // Redirecionar de volta para a página de adicionar atualização
        exit();
    } else {
        echo "Erro ao excluir a atualização.";
    }
} else {
    header("Location: petitions_list.php"); // Redirecionar de volta para a lista de petições se o ID da atualização não estiver definido
    exit();
}
?>
