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
    <title>Petições Pendentes - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; // Inclua o cabeçalho ?>
<div class="container mt-5">
    <h2>Petições Pendentes</h2>

    <?php
    // Consulta para obter as petições pendentes
    $query = "SELECT id, titulo, descricao, caminho_imagem, criado_em
              FROM abaixo_assinados
              WHERE status = 'pendente' AND id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Título</th>';
        echo '<th>Criado em</th>';
        echo '<th>Editar</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row["titulo"] . '</td>';
            echo '<td>' . date('d/m/Y H:i:s', strtotime($row["criado_em"])) . '</td>';
            echo '<td><a href="edit_petition.php?id=' . $row["id"] . '" class="btn btn-primary">Editar</a></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>Nenhuma petição pendente no momento.</p>";
    }

    $stmt->close();
    $conn->close();
    ?>
</div>
<?php include_once "../footer.php"; // Inclua o rodapé ?>
</body>
</html>
