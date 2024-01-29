<?php
session_start();
require_once "../includes/db_connection.php"; // Substitua pelo caminho correto até o seu arquivo de conexão
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se o usuário está logado e se é um administrador
if (!isset($_SESSION["user_id"]) || $_SESSION["user_tipo"] !== "administrador") {
    header("Location: login.php");
    exit();
}

// Selecionar todas as doações pendentes
$sql = "SELECT * FROM doacoes WHERE status = 'Pendente'";
$result = $conn->query($sql);

// Configurar o PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtppro.zoho.com'; // Substitua pelo seu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'contato@participato.com.br'; // Substitua pelo seu e-mail
    $mail->Password = '<!#%&(erick2017>'; // Substitua pela sua senha
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    while ($row = $result->fetch_assoc()) {
        // Configurar o destinatário e conteúdo do e-mail
        $recipientEmail = $row['email'];
        $donationAmount = $row['valor'];
        $vaquinhaTitle = $row['nome_vaquinha'];

        $mail->setFrom('contato@participato.com.br', 'Participa Tocantins'); // Substitua pelo seu e-mail e nome de remetente
        $mail->addAddress($recipientEmail); // E-mail do destinatário

        $mail->isHTML(true);
        $mail->Subject = 'Notificação de Doação Pendente';
        $mail->CharSet = 'UTF-8'; // Definindo a codificação de caracteres como UTF-8
        $mail->Body = "Olá {$row['nome_completo']},
<br> Há uma doação pendente em seu nome para a vaquinha '{$vaquinhaTitle}'.
<br> Estamos aguardando a conclusão da sua doação no valor de R$ " . number_format($donationAmount, 2, ',', '.') . ".
<br> Ajude essa causa e faça o Pix para o seguinte chave: <a href='mailto:renato5462514@gmail.com?subject=Pagamento%20via%20Pix&body=Ol%C3%A1%2C%20estou%20realizando%20uma%20doa%C3%A7%C3%A3o%20no%20valor%20de%20R%24%20" . number_format($donationAmount, 2, ',', '.') . "%20para%20a%20vaquinha%20" . urlencode($vaquinhaTitle) . ".%20Por%20favor%20informe%20os%20dados%20necess%C3%A1rios%20para%20que%20eu%20possa%20efetuar%20o%20pagamento.'>renato5462514@gmail.com</a>.
<br> Qualquer dúvida estamos à disposição.
<br><br> Atenciosamente,
<br>Equipe Participa Tocantins";

        // Enviar o e-mail
        $mail->send();

        // Marcar a doação como notificada (atualize o status no banco de dados)
        $donationId = $row['id'];
        $updateSql = "UPDATE doacoes SET status_notificacao = 'Notificado' WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $donationId);
        $updateStmt->execute();
    }

    echo "E-mails de notificação enviados com sucesso.";
} catch (Exception $e) {
    echo "Erro ao enviar e-mails de notificação: " . $mail->ErrorInfo;
}

// Feche a conexão com o banco de dados
$conn->close();
?>
