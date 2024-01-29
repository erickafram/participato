<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["user_tipo"] !== "administrador") {
    header("Location: access_denied.php");
    exit();
}

if (isset($_GET['id'])) {
    $donation_id = $_GET['id'];
    $sql = "SELECT doacoes.*, usuarios.nome_usuario, vaquinhas.titulo AS nome_vaquinha FROM doacoes 
            LEFT JOIN usuarios ON doacoes.id_doador = usuarios.id 
            LEFT JOIN vaquinhas ON doacoes.id_vaquinha = vaquinhas.id
            WHERE doacoes.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $donation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $donation_details = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detalhes da Doação</title>
    <!-- Seus outros links e estilos -->
</head>
<body>

<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <?php if ($donation_details): ?>
        <h2>Detalhes da Doação para: <?php echo $donation_details['nome_vaquinha']; ?></h2>
        <p><strong>Nome Completo:</strong> <?php echo $donation_details['nome_completo']; ?></p>
        <p><strong>CPF:</strong> <?php echo $donation_details['cpf']; ?></p>
        <p><strong>Telefone:</strong> <?php echo $donation_details['telefone']; ?></p>
        <p><strong>Email:</strong> <?php echo $donation_details['email']; ?></p>
    <?php else: ?>
        <p>Detalhes da doação não encontrados.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
