<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}


// Consulta SQL para pegar todas as doações pendentes
$query = "SELECT * FROM doacoes_pix WHERE status = 'pendente'";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Aprovar Doações</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Aprovar Doações</h2>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Nome</th>
            <th>ID da Petição</th>
            <th>Valor</th>
            <th>Data</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['donor_name']; ?></td>
                <td><?php echo $row['id_abaixo_assinado']; ?></td>
                <td><?php echo $row['valor']; ?></td>
                <td><?php echo $row['criado_em']; ?></td>
                <td>
                    <a href="aprovar_doacao.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Aprovar</a>
                    <a href="rejeitar_doacao.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Rejeitar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include "../footer.php"; ?>

</body>
</html>

<?php $conn->close(); ?>
