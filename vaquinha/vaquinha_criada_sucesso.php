<?php
session_start();

if (!isset($_SESSION["vaquinha_criada"])) {
    header("Location: criardoacao.php"); // Redireciona se a vaquinha não foi criada com sucesso
    exit();
}

// Limpa a variável de sessão
unset($_SESSION["vaquinha_criada"]);
?>

<!DOCTYPE html>
<html>
<head>
    <?php include_once "../header.php"; ?>
    <title>Vaquinha Criada com Sucesso</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="alert alert-success">
        Vaquinha criada com sucesso! Aguarde a aprovação do administrador em até 48 horas.
    </div>
    <a href="my_crowdfunds.php" class="btn btn-primary">Ir para Minhas Vaquinhas</a>
</div>
</body>
</html>
