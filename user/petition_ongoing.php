<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Petições em Andamento - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include_once "../header.php"; // Inclua o cabeçalho ?>
<div class="container mt-5">
    <h2>Petições em Andamento</h2>

    <?php
    // Consulta para obter as petições em andamento
    $query = "SELECT id, titulo, descricao, caminho_imagem, criado_em
              FROM abaixo_assinados
              WHERE status = 'aprovado'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="card mb-3">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $row["titulo"] . '</h5>';
            echo '<p class="card-text">' . $row["descricao"] . '</p>';
            echo '<p class="card-text">Criado em: ' . $row["criado_em"] . '</p>';
            echo '<a href="sign_petition.php?id=' . $row["id"] . '" class="btn btn-primary">Assinar</a>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo "<p>Nenhuma petição em andamento no momento.</p>";
    }

    $conn->close();
    ?>
</div>
<?php include_once "../footer.php"; // Inclua o rodapé ?>
</body>
</html>
