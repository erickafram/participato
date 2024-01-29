<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"]) || !isset($_GET["id"])) {
    header("Location: login.php");
    exit();
}

$vaquinha_id = $_GET["id"];

// Debugging: Check if $vaquinha_id has a valid value
// echo "Vaquinha ID: " . $vaquinha_id;

// Check the database connection
if ($conn === false) {
    die("Database connection failed: " . $conn->connect_error);
}

// Buscar doadores para a vaquinha selecionada
$sql = "SELECT d.id, d.valor, d.data, d.anonimo, u.nome_usuario AS nome_doador FROM doacoes d INNER JOIN usuarios u ON d.id_doador = u.id WHERE d.id_vaquinha = ? ORDER BY d.data DESC";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $vaquinha_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result === false) {
    die("Execute failed: " . $stmt->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Doadores - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include_once "../header.php"; ?>
<div class="container mt-5">
    <h2>Lista de Doadores</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Nome do Doador</th>
            <th>Valor (R$)</th>
            <th>Data</th>
        </tr>
        </thead>
        <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php
                    if ($row["anonimo"] === 1) {
                        echo "Doação Anônima";
                    } else {
                        echo $row["nome_doador"];
                    }
                    ?>
                </td>
                <td><?php echo number_format($row["valor"], 2, ',', '.'); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row["data"])); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a href="view_crowdfund.php?id=<?php echo $vaquinha_id; ?>" class="btn btn-secondary">Voltar</a>

</div>
</body>
</html>

<?php $conn->close(); ?>
