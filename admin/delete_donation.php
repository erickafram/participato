<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_tipo"] !== "administrador") {
    header("Location: access_denied.php");
    exit();
}

if (isset($_GET['id'])) {
    $donation_id = $_GET['id'];

    $sql_delete = "DELETE FROM doacoes WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $donation_id);

    if ($stmt_delete->execute()) {
        echo "<script>alert('Doação ID $donation_id excluída com sucesso!'); window.location.href='approve_donations.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir a doação ID $donation_id.'); window.location.href='approve_donations.php';</script>";
    }
}

$conn->close();
?>
