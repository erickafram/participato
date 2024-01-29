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
    <title>Lista de Usuários - Painel do Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "../header.php"; // Inclua o cabeçalho para administrador ?>

<div class="container mt-5">
    <h2>Lista de Usuários Cadastrados</h2>

    <!-- Filtro de busca -->
    <form action="" method="get">
        <input type="text" name="search" placeholder="Buscar usuário...">
        <input type="submit" value="Buscar">
    </form>

    <?php
    require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $query = "SELECT * FROM usuarios WHERE nome_usuario LIKE '%$search%'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo '<table class="table table-bordered">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Nome do Usuário</th>';
        echo '<th>Email</th>';
        echo '<th>Tipo</th>';
        echo '<th>Ações</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row["id"] . '</td>';
            echo '<td>' . $row["nome_usuario"] . '</td>';
            echo '<td>' . $row["email"] . '</td>';
            echo '<td>' . $row["tipo_usuario"] . '</td>';
            echo '<td>';
            echo '<a href="edit_user.php?id=' . $row["id"] . '" class="btn btn-primary">Editar</a>';
            echo '<a href="delete_user.php?id=' . $row["id"] . '" class="btn btn-danger ml-2">Excluir</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo "<p>Nenhum usuário encontrado.</p>";
    }

    $conn->close();
    ?>
</div>
<?php include "../footer.php"; // Inclua o rodapé ?>
</body>
</html>
