<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $petition_id = $_POST["id"];
    $new_title = $_POST["titulo"];
    $new_description = $_POST["descricao"];

    // Verifique se a petição ainda está pendente
    $query_check_status = "SELECT status FROM abaixo_assinados WHERE id = ?";
    $stmt_check_status = $conn->prepare($query_check_status);
    $stmt_check_status->bind_param("i", $petition_id);
    $stmt_check_status->execute();
    $stmt_check_status->bind_result($status);
    $stmt_check_status->fetch();
    $stmt_check_status->close();

    if ($status == 'pendente') {
        // Atualize os dados da petição
        $query_update = "UPDATE abaixo_assinados SET titulo = ?, descricao = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ssi", $new_title, $new_description, $petition_id);
        $stmt_update->execute();
        $stmt_update->close();

        header("Location: petition_pending.php");
        exit();
    } else {
        // A petição já foi aprovada, o usuário não pode editar
        header("Location: petition_pending.php");
        exit();
    }
}

$conn->close();
?>
