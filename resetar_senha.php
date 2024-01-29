<?php
require_once "includes/db_connection.php"; // Inclua o arquivo de conexão
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $message = 'As senhas não coincidem. Por favor, tente novamente.';
    } else {
        $sql = "SELECT * FROM usuarios WHERE reset_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $expiration_date = strtotime($row['reset_token_expiration']);
            if ($expiration_date < time()) {
                $message = 'O token de redefinição de senha expirou. Solicite um novo.';
            } else {
                $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateSql = "UPDATE usuarios SET senha = ?, reset_token = NULL, reset_token_expiration = NULL WHERE reset_token = ?";
                $stmt = $conn->prepare($updateSql);
                $stmt->bind_param("ss", $hashed_password, $token);

                if ($stmt->execute()) {
                    $message = 'Sua senha foi redefinida com sucesso. Você pode agora fazer login com a nova senha.';
                } else {
                    $message = 'Ocorreu um erro ao redefinir sua senha. Por favor, tente novamente mais tarde.';
                }
            }
        } else {
            $message = 'Token inválido. Solicite um novo link de redefinição de senha.';
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h3>Redefinir Senha</h3>
            <?php if (!empty($message)) : ?>
                <div class="alert alert-info"><?= $message; ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <div class="form-group">
                    <label for="new_password">Nova Senha:</label>
                    <input type="password" class="form-control" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nova Senha:</label>
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Redefinir Senha</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
