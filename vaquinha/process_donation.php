<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vaquinha_id = $_POST["vaquinha_id"];
    $valor = floatval($_POST["valor"]);
    $anonimo = isset($_POST["anonimo"]) ? 1 : 0;

    $sql_insert = "INSERT INTO doacoes (id_vaquinha, id_doador, valor, data, anonimo, status) VALUES (?, ?, ?, NOW(), ?, 'Pendente')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiid", $vaquinha_id, $_SESSION["user_id"], $valor, $anonimo);

    if ($stmt_insert->execute()) {
        $sql_update_vaquinha = "UPDATE vaquinhas SET arrecadado = arrecadado + ? WHERE id = ?";
        $stmt_update_vaquinha = $conn->prepare($sql_update_vaquinha);
        $stmt_update_vaquinha->bind_param("di", $valor, $vaquinha_id);
        $stmt_update_vaquinha->execute();

        $_SESSION["donation_details"] = [
            "valor" => $valor,
            "anonimo" => $anonimo,
            "status" => 'Pendente'  // Supondo que o status inicial seja 'Pendente'
        ];

        header("Location: donation_success.php");
        exit();
    } else {
        $error_message = "Erro ao processar a doação.";
    }
}
$conn->close();
?>
