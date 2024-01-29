<?php
session_start();
require_once "../includes/db_connection.php";
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$id = $_GET["id"] ?? null;
$status = $_GET["status"] ?? null;

if ($id !== null && $status !== null) {
    $sql = "UPDATE vaquinhas SET aprovacao = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
}

header("Location: approve_crowdfunds.php");
exit();
?>
