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

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $petition_id = $_GET["id"];

    // Atualize o status do abaixo-assinado para "aprovado"
    $query = "UPDATE abaixo_assinados SET status = 'aprovado' WHERE id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $petition_id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: dashboard.php");
            exit();
        } else {
            $stmt->close();
            echo "Erro ao aprovar o abaixo-assinado.";
        }
    } else {
        echo "Erro na preparação da declaração: " . $conn->error;
    }
}

$conn->close();
?>
