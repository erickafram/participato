<?php
session_start();
require_once "../includes/db_connection.php";

// Inicialize a variável de resposta como um array vazio
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["comment_text"]) && isset($_POST["petition_id"])) {
    $comment_text = $_POST["comment_text"];
    $petition_id = $_POST["petition_id"];
    $user_id = $_SESSION["user_id"];

    $query_insert_comment = "INSERT INTO comentarios (id_usuario, id_abaixo_assinado, comentario, data) VALUES (?, ?, ?, NOW())";
    $stmt_insert_comment = $conn->prepare($query_insert_comment);

    if (!$stmt_insert_comment) {
        $response['status'] = 'error';
        $response['message'] = 'Erro na preparação da consulta: ' . $conn->error;
    } else {
        $stmt_insert_comment->bind_param("iis", $user_id, $petition_id, $comment_text);

        if ($stmt_insert_comment->execute()) {
            // Verifique se a ID do usuário está disponível na sessão
            if(isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];

                // Faça uma consulta para obter o nome do usuário com base no ID
                $query_get_username = "SELECT nome_usuario FROM usuarios WHERE id = ?";
                $stmt_get_username = $conn->prepare($query_get_username);

                if($stmt_get_username) {
                    $stmt_get_username->bind_param("i", $user_id);
                    $stmt_get_username->execute();
                    $stmt_get_username->bind_result($user_name);
                    if ($stmt_get_username->fetch()) {
                        // $user_name agora deve conter o nome do usuário
                    } else {
                        $user_name = 'ID não encontrado';
                    }
                    $stmt_get_username->close();
                } else {
                    $user_name = 'Erro na consulta';
                }
            } else {
                $user_name = 'Anônimo';
            }

            $response = [
                "status" => "success",
                "user_name" => $user_name,
                "comment" => $comment_text,
                "date" => date("d/m/Y H:i")
            ];
        } else {
            $response['status'] = 'error';
            $response['message'] = "Erro ao inserir o comentário: " . $stmt_insert_comment->error;
        }

        $stmt_insert_comment->close();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Dados insuficientes para processar o comentário.';
}

// Enviar resposta como JSON
echo json_encode($response);

// Fechar a conexão com o banco de dados
$conn->close();
?>
