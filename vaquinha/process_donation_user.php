<?php
session_start();
require_once "../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recuperar dados do formulário
    $vaquinha_id = $_POST["vaquinha_id"];
    $nome_completo = $_POST["nome_completo"];
    $email = $_POST["email"];
    $cpf = $_POST["cpf"];
    $telefone = $_POST["telefone"];

// Remover vírgulas e pontos do valor antes de inserir no banco de dados
    $valor_input = $_POST["valor"];
    $valor_input = str_replace(",", ".", $valor_input); // Substitui vírgulas por pontos
    $valor = floatval($valor_input); // Converte para float

    $anonimo = isset($_POST["anonimo"]) ? 1 : 0;

// Remova a função maskMoney e formate o valor corretamente
    $valor = str_replace(["R$", ".", ","], "", $valor); // Remove o R$, ponto e vírgula
    $valor = str_replace(",", ".", $valor); // Substitui a vírgula por ponto para representar decimal

// Inserir a doação na tabela 'doacoes'
    $sql = "INSERT INTO doacoes (id_vaquinha, id_doador, valor, anonimo, nome_completo, email, cpf, telefone, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendente')";
    $stmt = $conn->prepare($sql);

// Definir $doador_id com base na sessão do usuário
    $doador_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;

    $stmt->bind_param("iiisssss", $vaquinha_id, $doador_id, $valor, $anonimo, $nome_completo, $email, $cpf, $telefone);

    if ($stmt->execute()) {
        // Recuperar informações da vaquinha (substitua pela sua lógica)
        $sql_vaquinha = "SELECT titulo FROM vaquinhas WHERE id = ?";
        $stmt_vaquinha = $conn->prepare($sql_vaquinha);
        $stmt_vaquinha->bind_param("i", $vaquinha_id);
        $stmt_vaquinha->execute();
        $result_vaquinha = $stmt_vaquinha->get_result();
        $vaquinha = $result_vaquinha->fetch_assoc();

        // Redirecionar para a página de confirmação de doação com os parâmetros da vaquinha
        header("Location: donation_confirmation.php?id_vaquinha=$vaquinha_id&titulo_vaquinha=" . urlencode($vaquinha["titulo"]));
        exit();
    } else {
        // Tratar erros de inserção de doação
        echo "Erro ao processar a doação. Por favor, tente novamente mais tarde.";
    }
} else {
    // Se alguém tentar acessar diretamente o arquivo process_donation_user.php via URL, você pode redirecioná-los para a página de doação
    header("Location: donate_user.php?id=$vaquinha_id");
    exit();
}
?>
