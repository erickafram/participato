<?php
session_start();
require_once "includes/db_connection.php"; // Inclua o arquivo de conexão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    $query = "SELECT id, nome_usuario, senha, tipo_usuario FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $nome_usuario, $hashed_password, $tipo_usuario);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($senha, $hashed_password)) {
        $_SESSION["user_id"] = $user_id;
        $_SESSION["user_nome"] = $nome_usuario;
        $_SESSION["user_tipo"] = $tipo_usuario;

        if ($tipo_usuario == "administrador") {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: user/dashboard.php");
        }
        exit();
    } else {
        header("Location: login.php?error=invalid");
        exit();
    }
}

?>
