<?php
session_start();
require_once "../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_temp = $_POST['nome_temp'];
    $email_temp = $_POST['email_temp'];
    $telefone_temp = $_POST['telefone_temp'];
    $telefone_temp = preg_replace("/[^0-9]/", "", $telefone_temp);
    $petition_id = $_POST['id_abaixo_assinado'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $anonimo = isset($_POST['anonimo']) ? 1 : 0;
    $receber_notificacoes = isset($_POST['receber_notificacoes']) ? 1 : 0; // Adicione esta linha para capturar a opção de receber notificações

    // Verifica se o usuário já assinou a petição com os mesmos detalhes
    $query_check_signature = "SELECT * FROM assinaturas WHERE id_abaixo_assinado=? AND nome_temp=? AND email_temp=? AND telefone_temp=?";
    $stmt_check_signature = $conn->prepare($query_check_signature);
    $stmt_check_signature->bind_param("isss", $petition_id, $nome_temp, $email_temp, $telefone_temp);
    $stmt_check_signature->execute();
    $result = $stmt_check_signature->get_result();

    if ($result->num_rows > 0) {
        // O usuário já assinou a petição com os mesmos detalhes
        header("Location: petition_details.php?id=".$petition_id."&erro=1");
    } else {
        // Insira a assinatura na tabela de assinaturas
        $query_insert_signature = "INSERT INTO assinaturas (id_abaixo_assinado, id_usuario, nome_temp, email_temp, telefone_temp, anonimo, receber_notificacoes) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_signature = $conn->prepare($query_insert_signature);
        $stmt_insert_signature->bind_param("iisssii", $petition_id, $user_id, $nome_temp, $email_temp, $telefone_temp, $anonimo, $receber_notificacoes); // Adicione $receber_notificacoes à função bind_param
        $stmt_insert_signature->execute();

        // Redirecione o usuário ou mostre uma mensagem de sucesso
        header("Location: petition_details.php?id=".$petition_id."&sucesso=1");
    }
}
?>
