<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão
header("Location: ../login.php");

if (isset($_SESSION["user_id"])) {
    if ($_SESSION["user_tipo"] == "administrador") {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();
}

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
        $login_error = "Credenciais inválidas.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Minha Plataforma</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "header.php"; ?>
<div class="container mt-5">
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($login_error)) : ?>
            <div class="login-error"><?php echo $login_error; ?></div>
        <?php endif; ?>
        <form class="login-form" method="post" action="process_login.php">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" class="form-control" name="senha" id="senha" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        <a class="register-link" href="user/register.php">Não tem uma conta? Registrar</a>
    </div>
</div>
</body>
</html>

