<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifique se os índices estão definidos antes de acessá-los
    $titulo = isset($_POST["titulo"]) ? $_POST["titulo"] : null;
    $descricao = isset($_POST["descricao"]) ? $_POST["descricao"] : null;
    $quantidade_assinaturas = isset($_POST["quantidade_assinaturas"]) ? $_POST["quantidade_assinaturas"] : null;
    $data_finalizacao = isset($_POST["data_finalizacao"]) ? $_POST["data_finalizacao"] : null;
    $link_artigos = isset($_POST["link_artigos"]) ? $_POST["link_artigos"] : null;
    $link_grupo = isset($_POST["link_grupo"]) ? $_POST["link_grupo"] : null;

    if ($titulo && $descricao && $quantidade_assinaturas && $data_finalizacao) { // verifica se os campos necessários estão presentes
        // Trate o arquivo de imagem, se fornecido
        $imagem_path = null;
        if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] === UPLOAD_ERR_OK) {
            $imagem_path = "images/" . basename($_FILES["imagem"]["name"]);
            move_uploaded_file($_FILES["imagem"]["tmp_name"], "../assets/$imagem_path");
        }

        // Trate os links de artigos
        $links_array = preg_split("/\r\n|\n|\r/", $link_artigos);
        $links_array = array_filter($links_array); // Remover linhas vazias
        $links_artigos_final = implode(", ", $links_array);

        // Insira os dados na tabela abaixo_assinados
        $query = "INSERT INTO abaixo_assinados (titulo, descricao, caminho_imagem, id_usuario, status, quantidade_assinaturas, data_finalizacao, link_artigos, link_grupo)
                  VALUES (?, ?, ?, ?, 'pendente', ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("sssiisss", $titulo, $descricao, $imagem_path, $_SESSION["user_id"], $quantidade_assinaturas, $data_finalizacao, $links_artigos_final, $link_grupo);
            if ($stmt->execute()) {
                $_SESSION["aguardando_aprovacao"] = true;
                header("Location: create_petition.php"); // Redirecionar de volta para a página de criação
                exit();
            } else {
                echo "Erro ao registrar abaixo-assinado: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erro na preparação da declaração: " . $conn->error;
        }
    } else {
        echo "Alguns campos estão faltando!";
    }
}

$conn->close();
?>
