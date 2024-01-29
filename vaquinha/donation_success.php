<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["donation_details"])) {
    header("Location: index.php");
    exit();
}

$donation_details = $_SESSION["donation_details"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doação Bem-Sucedida - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h1>Obrigado pela sua doação!</h1>
    <p>Sua generosidade é muito apreciada e ajudará a tornar este projeto uma realidade.</p>
    <div class="alert alert-primary" role="alert"> Depois de confirmarmos o recebimento do seu Pix, o status da sua doação será atualizado para "Aprovado". </div>
    <div class="details">
        <h2>Detalhes da sua doação:</h2>
        <ul>
            <li><strong>Valor da Doação:</strong> R$ <?php echo number_format($donation_details["valor"], 2); ?></li>
            <li><strong>Anônimo:</strong> <?php echo $donation_details["anonimo"] ? "Sim" : "Não"; ?></li>
            <li><strong>Status:</strong> <?php echo $donation_details["status"]; ?></li>
        </ul>
    </div>
    <a href="index.php" class="btn btn-primary">Voltar</a>
    <a href="minhas_doacoes.php" class="btn btn-primary">Minhas Doações</a>
</div>

</body>
</html>
