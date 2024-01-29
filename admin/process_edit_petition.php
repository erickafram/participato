<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $petition_id = $_POST["petition_id"];
    $titulo = $_POST["titulo"];
    $descricao = $_POST["descricao"];
    $caminho_imagem = $_POST["caminho_imagem"];
    $status = $_POST["status"];
    $quantidade_assinaturas = $_POST["quantidade_assinaturas"];
    $link_artigos = $_POST["link_artigos"];
    $link_grupo = $_POST["link_grupo"];
    $data_finalizacao = $_POST["data_finalizacao"];
    $turbinado = isset($_POST["turbinado"]) ? 1 : 0;  // Nova linha

    $query = "UPDATE abaixo_assinados
              SET titulo = ?, descricao = ?, caminho_imagem = ?, status = ?, quantidade_assinaturas = ?, link_artigos = ?, link_grupo = ?, data_finalizacao = ?, turbinado = ?
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("ssssisssii", $titulo, $descricao, $caminho_imagem, $status, $quantidade_assinaturas, $link_artigos, $link_grupo, $data_finalizacao, $turbinado, $petition_id); // Nova linha
        if ($stmt->execute()) {
            header("Location: petitions_list.php");
            exit();
        } else {
            echo "Erro ao atualizar petição: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da declaração: " . $conn->error;
    }
}

$conn->close();

?>
