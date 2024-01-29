<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /sistema/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$errors = [];

// Verifica se um arquivo foi enviado
if (isset($_FILES["imagem_perfil"])) {
    $imagem_perfil = $_FILES["imagem_perfil"];

    // Verifica se não há erros no upload
    if ($imagem_perfil["error"] === 0) {
        $nome_arquivo = basename($imagem_perfil["name"]);
        $caminho_arquivo = dirname(__FILE__) . "/profile/" . $nome_arquivo;
        // Move o arquivo para a pasta de perfil
        if (move_uploaded_file($imagem_perfil["tmp_name"], $caminho_arquivo)) {
            // Atualiza o nome do arquivo no banco de dados
            $query = "UPDATE usuarios SET imagem_perfil = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $nome_arquivo, $user_id);
            if ($stmt->execute()) {
                $successMessage = "Imagem de perfil atualizada com sucesso.";
            } else {
                $errors[] = "Erro ao atualizar a imagem de perfil no banco de dados.";
            }
            $stmt->close();
        } else {
            $errors[] = "Erro ao mover o arquivo de imagem para a pasta de perfil.";
        }
    } else {
        $errors[] = "Erro no upload da imagem.";
    }
} else {
    $errors[] = "Nenhum arquivo de imagem foi enviado.";
}

// Redireciona de volta para o dashboard com mensagens de erro ou sucesso
header("Location: dashboard.php?successMessage=$successMessage&errors=" . urlencode(implode("<br>", $errors)));
exit();
?>
