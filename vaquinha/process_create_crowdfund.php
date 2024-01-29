<?php
session_start();
require_once "../includes/db_connection.php"; // Substitua pelo seu arquivo de conexão ao banco de dados

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Variáveis do formulário
$titulo = $_POST["titulo"] ?? null;
$descricao = $_POST["descricao"] ?? null;
$meta = $_POST["meta"] ?? null;  // Valor no formato "150,00"
$id_usuario = $_SESSION["user_id"];

if ($titulo && $descricao && $meta && isset($_FILES["imagem"])) {
    // Remove qualquer formatação não numérica (exceto vírgula)
    $meta = preg_replace("/[^0-9,]/", "", $meta);

    // Substitui a vírgula pelo ponto para garantir que seja reconhecido como decimal
    $meta = str_replace(",", ".", $meta);

    // Converte para um número decimal
    $meta = floatval($meta);

    // Verifica se o upload do arquivo foi bem-sucedido
    if ($_FILES["imagem"]["error"] === UPLOAD_ERR_OK) {
        $imagem_tmp = $_FILES["imagem"]["tmp_name"];
        $imagem_nome = $_FILES["imagem"]["name"];

        // Define o diretório de upload
        $upload_dir = "images/"; // Substitua pelo diretório desejado
        $imagem_destino = $upload_dir . $imagem_nome;

        // Move a imagem para o diretório de upload
        if (move_uploaded_file($imagem_tmp, $imagem_destino)) {
            // Preparando a inserção no banco de dados
            $sql = "INSERT INTO vaquinhas (titulo, descricao, meta, imagem, id_usuario, aprovacao) VALUES (?, ?, ?, ?, ?, 'pendente')";

            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                // Handle error, for example by throwing an exception
                die("Error preparing the SQL query: " . $conn->error);
            }

            $stmt->bind_param("ssdsi", $titulo, $descricao, $meta, $imagem_nome, $id_usuario);

            $success = $stmt->execute();

            if ($success) {
                $_SESSION["vaquinha_criada"] = true;
                header("Location: vaquinha_criada_sucesso.php"); // Redireciona para o arquivo de sucesso
                exit();
            } else {
                // Define uma mensagem de erro em uma variável de sessão
                $_SESSION["erro_vaquinha"] = "Erro ao criar vaquinha: " . $stmt->error;
                header("Location: criardoacao.php"); // Redireciona de volta para criardoacao.php
                exit();
            }
        } else {
            die("Erro ao fazer o upload da imagem.");
        }
    } else {
        die("Erro no upload da imagem: " . $_FILES["imagem"]["error"]);
    }
} else {
    die("Por favor, preencha todos os campos necessários.");
}
?>
