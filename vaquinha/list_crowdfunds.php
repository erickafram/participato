<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT id, titulo, descricao, meta, arrecadado, status FROM vaquinhas WHERE status = 'aberto'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Vaquinhas - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>
<div class="container mt-5">
    <h2>Vaquinhas disponíveis</h2>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Título</th>
            <th>Descrição</th>
            <th>Meta</th>
            <th>Status</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['titulo'] . "</td>";
                echo "<td>" . substr($row['descricao'], 0, 50) . "..." . "</td>";
                echo "<td>" . $row['meta'] . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td><a href='donate.php?id=" . $row['id'] . "' class='btn btn-primary'>Doar</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>Nenhuma vaquinha disponível no momento.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php $conn->close(); ?>
