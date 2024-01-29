<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /sistema/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$errors = []; // Inicialize $errors como um array vazio

// Consulta para excluir a imagem de perfil do usuário
$query = "UPDATE usuarios SET imagem_perfil = NULL WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    // Aqui você pode adicionar código para excluir fisicamente o arquivo de imagem do servidor, se desejar
    $_SESSION['successMessage'] = "Imagem de perfil excluída com sucesso.";
} else {
    $errors[] = "Erro ao excluir a imagem de perfil.";
}
$stmt->close();

// Armazena erros na sessão, se houver algum
if (!empty($errors)) {
    $_SESSION['errors'] = implode("<br>", $errors);
}

// Redireciona de volta para o dashboard
header("Location: dashboard.php");
exit();
?>
