<?php
// confirmar_doacao.php
require_once "../includes/db_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_peticao = $_POST['id_peticao'];
    $donorName = $_POST['donorName'];
    $id_usuario = 1;
    $valor = 10.00;
    if (empty($donorName)) {
        echo "Nome e email são obrigatórios.";
        return;
    }


    $query = "INSERT INTO doacoes_pix (id_usuario, id_abaixo_assinado, valor, status, donor_name) VALUES (?, ?, ?, 'pendente', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iids", $id_usuario, $id_peticao, $valor, $donorName);
    $result = $stmt->execute();

    if ($result) {
        echo "Doação confirmada e aguardando aprovação.";
    } else {
        echo "Erro na confirmação da doação.";
    }
}
?>
