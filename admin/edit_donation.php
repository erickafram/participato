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

if (isset($_GET['id'])) {
    $donation_id = $_GET['id'];

    // Buscar detalhes da doação para edição
    $sql_fetch = "SELECT * FROM doacoes WHERE id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $donation_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $donation = $result->fetch_assoc();

    // Se a doação não existir, redirecionar
    if (!$donation) {
        header("Location: approve_donations.php");
        exit();
    }

} else {
    header("Location: approve_donations.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_value = $_POST['new_value'];

    $sql_update = "UPDATE doacoes SET valor = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("di", $new_value, $donation_id);

    if ($stmt_update->execute()) {
        header("Location: approve_donations.php");
        exit();
    } else {
        echo "<script>alert('Erro ao atualizar a doação.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Doação - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h2>Editar Doação</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label for="new_value">Novo Valor da Doação:</label>
            <input type="number" step="0.01" class="form-control" id="new_value" name="new_value" value="<?php echo $donation['valor']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
</div>

</body>
</html>

<?php $conn->close(); ?>
