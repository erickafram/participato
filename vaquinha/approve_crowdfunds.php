<?php
// Somente para usuários administradores
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

$sql = "SELECT * FROM vaquinhas WHERE aprovacao = 'pendente'";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Aprovação de Vaquinhas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>
<div class="container mt-5">
    <h1>Aprovação de Vaquinhas</h1>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Aprovar</th>
            <th>Rejeitar</th>
            <th>Editar</th> <!-- Novo cabeçalho para a coluna de edição -->
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row["id"]; ?></td>
                <td><?php echo $row["titulo"]; ?></td>
                <td><a href="process_approval.php?id=<?php echo $row["id"]; ?>&status=aprovado" class="btn btn-success">Aprovar</a></td>
                <td><a href="process_approval.php?id=<?php echo $row["id"]; ?>&status=rejeitado" class="btn btn-danger">Rejeitar</a></td>
                <td><a href="edit_crowdfund.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary">Editar</a></td> <!-- Botão de edição -->
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
