<?php
session_start();
require_once "../includes/db_connection.php";

// Certifique-se de que a requisição é do tipo POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $valor_pix = isset($_POST["valor_pix"]) ? $_POST["valor_pix"] : "0.00";
    $valor_pix = str_replace('R$', '', $valor_pix); // Remove o símbolo de real
    $valor_pix = str_replace('.', '', $valor_pix); // Remove os pontos
    $valor_pix = str_replace(',', '.', $valor_pix); // Substitui vírgula por ponto
    $valor_pix = floatval($valor_pix); // Converte para float

    $nome_completo = isset($_POST["nome_completo"]) ? $_POST["nome_completo"] : "";
    $email = isset($_POST["email"]) ? $_POST["email"] : "";
    $cpf = isset($_POST["cpf"]) ? $_POST["cpf"] : "";
    $telefone = isset($_POST["telefone"]) ? $_POST["telefone"] : "";
    $vaquinha_id = isset($_POST["vaquinha_id"]) ? $_POST["vaquinha_id"] : null;
    $anonimo = isset($_POST["anonimo"]) ? 1 : 0;

    // Converter $valor_pix para float
    $valor_pix = str_replace(['R$', ',', '.'], ['', '', '.'], $valor_pix);
    $valor_pix = floatval($valor_pix);

    // Aqui você deve adicionar a lógica para obter o id do doador, se disponível
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;

    // Preparar a consulta SQL para inserir os dados da doação
    $sql = "INSERT INTO doacoes (id_vaquinha, id_doador, valor, anonimo, nome_completo, email, cpf, telefone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidsssss", $vaquinha_id, $user_id, $valor_pix, $anonimo, $nome_completo, $email, $cpf, $telefone);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        // Em caso de erro na inserção, retorne uma mensagem de erro
        echo json_encode(["success" => false, "error" => "Erro ao salvar a doação."]);
    }
} else {
    // Se o método da requisição não for POST, retorne um erro
    echo json_encode(["success" => false, "error" => "Método de requisição inválido."]);
}

// Fechar a conexão
$stmt->close();
$conn->close();
?>
