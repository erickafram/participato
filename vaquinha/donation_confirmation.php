<!DOCTYPE html>
<html>
<head>
    <title>Confirmação de Doação</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Adicione seus links de estilo adicionais aqui, se necessário -->
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h1>Obrigado pela sua doação!</h1>
    <p class="mb-0">Você fez a diferença na vaquinha <strong><?php echo isset($_GET["titulo_vaquinha"]) ? htmlspecialchars($_GET["titulo_vaquinha"]) : "Título da Vaquinha Desconhecido"; ?></strong>.</p>

    <p>Sua generosidade é muito apreciada e ajudará a tornar este projeto uma realidade.</p>
    <div class="alert alert-primary" role="alert">Depois de confirmarmos o recebimento do seu Pix, ele será contabilizado no valor da vaquinha. O status da sua doação será atualizado para 'Aprovado'.</div>
    <a href="index.php" class="btn btn-primary">Voltar para a página inicial</a>
</div>

</body>
</html>
