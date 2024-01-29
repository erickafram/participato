<?php
session_start();
require_once "../includes/db_connection.php"; // Certifique-se de incluir seu arquivo de conexão com o banco de dados aqui

// Recupere informações sobre a vaquinha (substitua pela sua lógica)
$vaquinha_id = isset($_SESSION["vaquinha_id"]) ? $_SESSION["vaquinha_id"] : null; // Certifique-se de usar a variável correta para o ID da vaquinha
$sql = "SELECT titulo FROM vaquinhas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vaquinha_id);
$stmt->execute();
$result = $stmt->get_result();
$vaquinha = $result->fetch_assoc();

// Certifique-se de incluir a lógica para obter o valor da doação do banco de dados
$valor_doacao = 100.00; // Substitua pelo valor real da doação

// Exiba a mensagem de agradecimento
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirmação de Doação</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h1>Obrigado pela sua doação!</h1>
    <p>Você fez uma doação para a vaquinha "<?php echo $vaquinha["titulo"]; ?>".</p>
    <p>Sua generosidade é muito apreciada e ajudará a tornar este projeto uma realidade.</p>
    <div class="alert alert-primary" role="alert">Depois de confirmarmos o recebimento do seu Pix, ele será contabilizado no valor da vaquinha. O status da sua doação será atualizado para 'Aprovado'.</div>
    <a href="index.php" class="btn btn-primary">Voltar para a página inicial</a>
</div>

</body>
</html>
