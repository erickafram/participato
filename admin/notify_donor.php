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

if (isset($_GET['id'])) {
    $donation_id = $_GET['id'];

    // Buscar informações da doação e do doador
    $sql = "SELECT doacoes.*, vaquinhas.titulo AS nome_vaquinha FROM doacoes 
            LEFT JOIN vaquinhas ON doacoes.id_vaquinha = vaquinhas.id 
            WHERE doacoes.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $donation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation = $result->fetch_assoc();

    if ($donation) {
        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtppro.zoho.com'; // Substitua pelo seu servidor SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'contato@participato.com.br'; // Substitua pelo seu e-mail
            $mail->Password = '<!#%&(erick2017>'; // Substitua pela sua senha
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinatários
            $mail->setFrom('contato@participato.com.br', 'Participa Tocantins'); // Substitua pelo seu e-mail e nome de remetente
            $mail->addAddress($donation['email']); // E-mail do destinatário

            // Conteúdo do E-mail
            $mail->isHTML(true);
            $mail->Subject = 'Notificação de Doação Pendente';
            $mail->CharSet = 'UTF-8'; // Definindo a codificação de caracteres como UTF-8
            $mail->Body = "Olá " . $donation['nome_completo'] . ",
<br> Há uma doação pendente em seu nome para a vaquinha '{$donation['nome_vaquinha']}'.
<br> Estamos aguardando a conclusão da sua doação no valor de R$ " . number_format($donation['valor'], 2, ',', '.') . ".
<br> Ajude essa causa e faça o Pix para o seguinte chave: <a href='mailto:renato5462514@gmail.com?subject=Pagamento%20via%20Pix&body=Ol%C3%A1%2C%20estou%20realizando%20uma%20doa%C3%A7%C3%A3o%20no%20valor%20de%20R%24%20" . number_format($donation['valor'], 2, ',', '.') . "%20para%20a%20vaquinha%20" . urlencode($donation['nome_vaquinha']) . ".%20Por%20favor%20informe%20os%20dados%20necess%C3%A1rios%20para%20que%20eu%20possa%20efetuar%20o%20pagamento.'>renato5462514@gmail.com</a>.
<br> Qualquer dúvida estamos à disposição.
<br><br> Atenciosamente,
<br>Equipe Participa Tocantins";

            $mail->send();
            echo "E-mail de notificação enviado com sucesso para " . $donation['email'] . "<br>";

            // Marque a doação como notificada
            $donationId = $donation['id'];
            $updateSql = "UPDATE doacoes SET status_notificacao = 'Notificado' WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $donationId);

            if ($updateStmt->execute()) {
                echo "Doação marcada como notificada no banco de dados.<br>";
            } else {
                echo "Erro ao atualizar o status da doação no banco de dados.<br>";
            }

            $mail->clearAddresses(); // Limpe os destinatários para o próximo e-mail

        } catch (Exception $e) {
            echo "Erro ao enviar a notificação. Erro: {$mail->ErrorInfo}<br>";
        }
    } else {
        echo "Doação não encontrada.<br>";
    }
} else {
    header("Location: aprovar_doacoes.php");
    exit();
}

$conn->close();
?>
