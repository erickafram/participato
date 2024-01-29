<?php
require_once "includes/db_connection.php"; // Inclua o arquivo de conexão

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token = uniqid();
        $expiration_date = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $updateSql = "UPDATE usuarios SET reset_token = ?, reset_token_expiration = ? WHERE email = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sss", $token, $expiration_date, $email);

        if ($stmt->execute()) {
            $resetLink = "https://participato.com.br/sistema/resetar_senha.php?token=" . $token;

            $mail = new PHPMailer(true);

            try {
                // Ativa a saída de debug detalhada
                $mail->SMTPDebug = 0;
                //$mail->Debugoutput = function($str, $level) {echo "Debug level $level; message: $str";};

                // Configurações do servidor
                $mail->isSMTP();
                $mail->Host       = 'smtppro.zoho.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'contato@participato.com.br'; // E-mail do Gmail
                $mail->Password   = '<!#%&(erick2017>'; // Senha do Gmail ou senha de aplicativo
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinatários
                $mail->setFrom('contato@participato.com.br', 'Participa Tocantins');
                $mail->addAddress($email); // E-mail do destinatário

                // Conteúdo
                $mail->isHTML(true);
                $mail->Subject = 'Redefinição de Senha';
                $mail->Body    = "Você solicitou a redefinição de sua senha. Clique no link abaixo para criar uma nova senha:<br><br><a href='$resetLink'>$resetLink</a>";

                $mail->send();
                $message = 'Um e-mail com instruções para redefinir sua senha foi enviado para o endereço fornecido.';
            } catch (Exception $e) {
                $message = "O e-mail não pôde ser enviado. Erro do PHPMailer: {$mail->ErrorInfo}";
            }
        } else {
            $message = 'Ocorreu um erro ao gerar o token de redefinição de senha. Por favor, tente novamente mais tarde.';
        }
    } else {
        $message = 'O endereço de e-mail fornecido não está associado a uma conta. Por favor, verifique novamente.';
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h3>Recuperar Senha</h3>
            <?php if (!empty($message)) : ?>
                <div class="alert alert-info"><?= $message; ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="email">Endereço de E-mail:</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
                <button type="submit" class="btn btn-primary">Recuperar Senha</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
