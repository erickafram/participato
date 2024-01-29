<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard do Usuário - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; // Inclua o cabeçalho ?>
<div class="container mt-5">
    <h2>Dashboard do Usuário</h2>

    <h5>Lista de Petições Assinadas</h5>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $user_id = $_SESSION["user_id"];

        // Consulta para obter abaixo-assinados assinados pelo usuário logado
        $query = "SELECT abaixo_assinados.id, abaixo_assinados.titulo
                    FROM abaixo_assinados
                    INNER JOIN assinaturas ON abaixo_assinados.id = assinaturas.id_abaixo_assinado
                    WHERE assinaturas.id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row["id"] . '</td>';
                echo '<td>' . $row["titulo"] . '</td>';
                echo '<td><a href="../peticao/petition_details.php?id=' . $row["id"] . '">Ver detalhes</a></td>';
                echo '</tr>';
            }
        } else {
            echo "<tr><td colspan='3'>Nenhum abaixo-assinado assinado por você.</td></tr>";
        }
        $stmt->close();
        ?>
        </tbody>
    </table>
    <a href="create_petition.php" class="btn btn-primary mt-3">Criar Novo Abaixo-Assinado</a>
</div>
</body>
</html>
