<?php
// Conexão ao banco de dados
require_once "../includes/db_connection.php";

// Verifica se o ID foi passado na URL
if(isset($_GET['id']) && is_numeric($_GET['id'])) {
    $doacao_id = $_GET['id'];

    // Atualiza o status da doação para 'aprovado'
    $query = "UPDATE doacoes_pix SET status = 'aprovado' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doacao_id);

    if ($stmt->execute()) {
        // Redireciona para a página de aprovação de doações com uma mensagem de sucesso
        header("Location: admin_aprovar_doacoes.php?success=Doação aprovada com sucesso.");
    } else {
        // Redireciona para a página de aprovação de doações com uma mensagem de erro
        header("Location: admin_aprovar_doacoes.php?error=Erro ao aprovar a doação.");
    }

    $stmt->close();
} else {
    // Redireciona para a página de aprovação de doações com uma mensagem de erro
    header("Location: admin_aprovar_doacoes.php?error=ID de doação inválido.");
}

$conn->close();
?>
