<?php
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Todas as Vaquinhas Cadastradas</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2 class="text-center">Todas as Vaquinhas Cadastradas</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Meta</th>
                <th>Arrecadado</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php

            $sql = "SELECT * FROM vaquinhas";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row["id"] . '</td>';
                    echo '<td>' . $row["titulo"] . '</td>';
                    echo '<td>R$ ' . $row["meta"] . '</td>';
                    echo '<td>R$ ' . $row["arrecadado"] . '</td>';
                    echo '<td>' . $row["status"] . '</td>';
                    echo '<td>';
                    echo '<a href="view_crowdfund.php?id=' . $row["id"] . '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i> Visualizar</a>';
                    echo '<a href="edit_crowdfund.php?id=' . $row["id"] . '" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Editar</a>';
                    echo '<a href="delete_crowdfund.php?id=' . $row["id"] . '" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Excluir</a>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6">Nenhuma vaquinha cadastrada.</td></tr>';
            }

            $conn->close();
            ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../footer.php"; ?>

</body>
</html>
