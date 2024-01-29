<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$id_vaquinha = isset($_GET['id_vaquinha']) ? $_GET['id_vaquinha'] : null;

if (!$id_vaquinha) {
    echo "<script>alert('Vaquinha não especificada.'); window.location.href='my_crowfunds.php';</script>";
    exit();
}

// Consulta para obter o nome da vaquinha
$sql_vaquinha = "SELECT titulo FROM vaquinhas WHERE id = ?";
$stmt_vaquinha = $conn->prepare($sql_vaquinha);
$stmt_vaquinha->bind_param("i", $id_vaquinha);
$stmt_vaquinha->execute();
$result_vaquinha = $stmt_vaquinha->get_result();
$vaquinha = $result_vaquinha->fetch_assoc();
$nomeVaquinha = $vaquinha['titulo'];

$sql = "SELECT d.nome_completo, d.valor, d.data, d.anonimo FROM doacoes d 
        WHERE d.id_vaquinha = ? AND d.status = 'Aprovado'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_vaquinha);
$stmt->execute();
$result = $stmt->get_result();

$totalDoacoes = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doações para <?php echo $nomeVaquinha; ?> - Participa Tocantins</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h4>Doações para a Vaquinha: <?php echo $nomeVaquinha; ?></h4>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Nome</th>
            <th>Valor</th>
            <th>Data</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()):
            $nomeDoador = $row['anonimo'] ? 'Anônimo' : $row['nome_completo'];
            $valorDoacao = $row['valor'];
            $totalDoacoes += $valorDoacao;
            ?>
            <tr>
                <td><?php echo $nomeDoador; ?></td>
                <td>R$ <?php echo number_format($valorDoacao, 2, ',', '.'); ?></td>
                <td><?php echo date('d/m/Y H:i:s', strtotime($row['data'])); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <div class="alert alert-info">
        <strong>Total das Doações:</strong> R$ <?php echo number_format($totalDoacoes, 2, ',', '.'); ?>
    </div>
</div>

<!-- Botão Voltar -->
<div class="container mt-3">
    <a href="my_crowdfunds.php" class="btn btn-secondary">Voltar</a>
</div>

</body>
</html>

<?php
$stmt_vaquinha->close();
$stmt->close();
$conn->close();
?>
