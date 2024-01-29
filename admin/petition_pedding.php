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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Painel do Administrador - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "../header.php"; // Inclua o cabeçalho para administrador ?>
<div class="container mt-5">
    <h2>Painel do Administrador</h2>

    <h3>Abaixo-Assinados Pendentes de Aprovação</h3>

    <?php
    require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

    // Consulta para obter os abaixo-assinados pendentes de aprovação
    $query = "SELECT aa.id, aa.titulo, aa.descricao, aa.caminho_imagem, aa.id_usuario, u.nome_usuario
          FROM abaixo_assinados aa
          INNER JOIN usuarios u ON aa.id_usuario = u.id
          WHERE aa.status = 'pendente'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Título</th>';
        echo '<th>Usuário</th>';
        echo '<th>Ações</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row["titulo"] . '</td>';
            echo '<td>' . $row["nome_usuario"] . '</td>';
            echo '<td>';
            echo '<a href="approve_petition.php?id=' . $row["id"] . '" class="btn btn-success">Aprovar</a>';
            echo '<a href="edit_petition.php?id=' . $row["id"] . '" class="btn btn-primary ml-2">Editar</a>';
            echo '<a href="reject_petition.php?id=' . $row["id"] . '" class="btn btn-danger ml-2">Rejeitar</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>Nenhum abaixo-assinado pendente de aprovação no momento.</p>";
    }

    $conn->close();
    ?>
</div>
<?php include "../footer.php"; // Inclua o rodapé ?>
</body>
</html>
