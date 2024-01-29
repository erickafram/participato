<?php
header('Content-Type: application/json');

session_start();
require_once "../includes/db_connection.php";

$response = [
    'status' => 'fail',
    'message' => 'Algo deu errado.'
];

if (!isset($_SESSION["user_id"])) {
    $response['message'] = 'Você precisa estar autenticado para assinar. Aguarde 5 segundos para ser redirecionado.';
    echo json_encode($response);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["petition_id"])) {
    $user_id = $_SESSION["user_id"];
    $petition_id = $_POST["petition_id"];

    // Verificar se o usuário já assinou essa petição
    $stmt = $conn->prepare("SELECT * FROM assinaturas WHERE id_usuario = ? AND id_abaixo_assinado = ?");
    $stmt->bind_param("ii", $user_id, $petition_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $response['message'] = 'Você já assinou.';
    } else {
        $stmt->close(); // Fechando o statement anterior para reutilizar a variável $stmt
        $stmt = $conn->prepare("INSERT INTO assinaturas (id_usuario, id_abaixo_assinado) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $petition_id);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Abaixo-assinado assinado com sucesso.';
        } else {
            $response['message'] = 'Erro ao assinar o abaixo-assinado.';
        }
    }

    $stmt->close();
}

echo json_encode($response);
?>
