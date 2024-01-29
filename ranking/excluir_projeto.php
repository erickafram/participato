<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../login.php");
    exit();
}

if (isset($_GET["ano"]) && is_numeric($_GET["ano"])) {
    $ano = $_GET["ano"];

    // Verifique se há projetos legislativos para o ano fornecido
    $query = "SELECT politicos.nome, projetos_legislativos.ano, SUM(projetos_legislativos.plo) AS total_plo, SUM(projetos_legislativos.req) AS total_req
              FROM politicos
              LEFT JOIN projetos_legislativos ON politicos.id = projetos_legislativos.politico_id
              WHERE projetos_legislativos.ano = $ano
              GROUP BY politicos.id, projetos_legislativos.ano";

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // Exclua os projetos legislativos para o ano fornecido
        $deleteQuery = "DELETE FROM projetos_legislativos WHERE ano = $ano";
        $conn->query($deleteQuery);

        // Redirecione de volta para a lista de projetos legislativos após a exclusão
        header("Location: lista_projetos_legislativos.php");
        exit();
    } else {
        // Ano não encontrado ou sem projetos legislativos
        header("Location: lista_projetos_legislativos.php");
        exit();
    }
} else {
    // Ano não fornecido ou não é um valor numérico válido
    header("Location: lista_projetos_legislativos.php");
    exit();
}
?>
