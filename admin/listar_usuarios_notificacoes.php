<?php
session_start();
require_once "../includes/db_connection.php";

// Verifique se o usuário está autenticado (você pode adicionar mais verificações de segurança)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirecione para a página de login ou exiba uma mensagem de erro.
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

// Consulta para obter a lista de petições
$query_peticoes = "SELECT id, titulo FROM abaixo_assinados";
$result_peticoes = $conn->query($query_peticoes);

// Inicializa uma variável para armazenar a lista de e-mails
$emailsList = array(); // Array para armazenar e-mails

// Inicializa uma variável para armazenar os e-mails formatados
$emailsFormatted = '';

// Modelo de e-mail
$emailModel = '';

// Verifique se o ID da petição foi selecionado no dropdown
if (isset($_GET['petition_id'])) {
    $petition_id = $_GET['petition_id'];

    // Consulta para listar os usuários que desejam receber notificações por e-mail com base no ID da petição
    $query = "SELECT id, nome_temp, email_temp FROM assinaturas WHERE receber_notificacoes = 1 AND id_abaixo_assinado = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $petition_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        // Exibir a lista de usuários
        $emailsList = array();
        while ($row = $result->fetch_assoc()) {
            $emailsList[] = $row['email_temp']; // Adicione o e-mail à lista
        }
        // Formate os e-mails separados por vírgulas
        $emailsFormatted = implode(', ', $emailsList);

        // Buscar o nome da petição com base no ID selecionado no dropdown
        $stmt_peticao = $conn->prepare("SELECT titulo FROM abaixo_assinados WHERE id = ?");
        $stmt_peticao->bind_param("i", $petition_id);
        $stmt_peticao->execute();
        $stmt_peticao->bind_result($petition_name);
        $stmt_peticao->fetch();
        $stmt_peticao->close();

        // Modelo de e-mail para copiar e colar
        $emailModel = "Prezado(a) usuário,\n\n";
        $emailModel .= "A petição que você assinou teve uma atualização. Para conferir, acesse o link abaixo:\n";
        $emailModel .= "Nome da Petição: " . $petition_name . "\n"; // Nome da petição
        $emailModel .= "Link para conferir: www.participato.com.br/sistema/peticao/petition_details.php?id=" . $petition_id . "\n\n"; // ID da petição
        $emailModel .= "Agradecemos pelo seu apoio.\n";
        $emailModel .= "Atenciosamente,\n";
        $emailModel .= "Equipe da Petição\n";

    } else {
        // Se ocorrer um erro na consulta, você pode adicionar tratamento de erro aqui.
        $emailsFormatted = 'Erro na consulta: ' . $stmt->error;
    }

    // Feche a declaração preparada
    $stmt->close();
} else {
    $emailsFormatted = 'Selecione uma petição no dropdown.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Listar Usuários e Petições</title>
</head>
<body>
<?php include "../header.php"; // Inclua o cabeçalho para administrador ?>
<div class="container">
    <h4 class="mt-4" style="padding-top:20px;">Selecione uma petição:</h4>
    <form action="listar_usuarios_notificacoes.php" method="get">
        <div class="form-group">
            <select class="form-control" name="petition_id">
                <?php
                $result_peticoes->data_seek(0); // Reiniciar o ponteiro do resultado para o início
                while ($row_peticao = $result_peticoes->fetch_assoc()) {
                    echo '<option value="' . $row_peticao['id'] . '">' . $row_peticao['titulo'] . '</option>';
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Mostrar Usuários</button>
    </form>
    <div class="mt-4">
        <?php
        if (!empty($emailsFormatted)) {
            echo '<h4>Usuários que desejam receber notificações por e-mail para a petição selecionada:</h4>';
            echo '<p>' . $emailsFormatted . '</p>';
            echo '<hr>';
            echo '<h4>Modelo de E-mail:</h4>';
            echo '<pre>' . $emailModel . '</pre>';
        } else {
            echo $emailsFormatted;
        }
        ?>
    </div>
</div>
</body>
</html>
