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
// Filtro de pesquisa
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = " WHERE titulo LIKE '%" . $_GET['search'] . "%'";
}

// Definir o número de registros por página e a página atual
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;

// Calcular o valor inicial para a consulta LIMIT
$start_from = ($page - 1) * $records_per_page;

// Modificar a consulta SQL para incluir LIMIT
$query = "SELECT * FROM abaixo_assinados" . $search_query . " ORDER BY criado_em DESC LIMIT " . $start_from . "," . $records_per_page;
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Lista de Petições</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Lista de Petições</h2>

    <!-- Campo de pesquisa -->
    <form action="" method="get" class="form-inline mb-3">
        <div class="form-group mr-2">
            <input type="text" name="search" class="form-control" placeholder="Pesquisar petições...">
        </div>
        <button type="submit" class="btn btn-primary">Pesquisar</button>
    </form>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Status</th>
            <th>Data de Finalização</th>
            <th>Turbinado</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>

        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row["id"] . '</td>';
                echo '<td style="max-width: 220px; overflow: hidden; text-overflow: ellipsis;">';
                echo '<a href="../peticao/petition_details.php?id=' . $row["id"] . '">' . $row["titulo"] . '</a>';
                echo '</td>';
                echo '<td>' . $row["status"] . '</td>';
                echo '<td>' . $row["data_finalizacao"] . '</td>';
                echo '<td>' . ($row["turbinado"] == 1 ? "Sim" : "Não") . '</td>';
                echo '<td>';
                echo '<a href="edit_petition.php?id=' . $row["id"] . '" class="btn btn-primary">Editar</a>';
                echo ' <a href="delete_petition.php?id=' . $row["id"] . '" class="btn btn-danger" onclick="return confirm(\'Tem certeza que deseja excluir esta petição?\')">Excluir</a>';
                echo ' <a href="add_update.php?id=' . $row["id"] . '" class="btn btn-info"> +Atualização</a>'; // Novo botão de adicionar atualização
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo "<tr><td colspan='6'>Nenhuma petição cadastrada.</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <!-- Controles de paginação -->
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <?php
            $query = "SELECT COUNT(id) FROM abaixo_assinados" . $search_query;
            $result = $conn->query($query);
            $row = $result->fetch_row();
            $total_records = $row[0];
            $total_pages = ceil($total_records / $records_per_page);

            // Botão "Anterior"
            if($page > 1){
                echo '<li class="page-item"><a class="page-link" href="petitions_list.php?page=' . ($page - 1) . '">Anterior</a></li>';
            }

            // Links numéricos
            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<li class="page-item ' . $active . '"><a class="page-link" href="petitions_list.php?page=' . $i . '">' . $i . '</a></li>';
            }

            // Botão "Próximo"
            if($page < $total_pages){
                echo '<li class="page-item"><a class="page-link" href="petitions_list.php?page=' . ($page + 1) . '">Próximo</a></li>';
            }
            ?>
        </ul>
    </nav>

</div>

<?php include "../footer.php"; ?>

</body>
</html>

<?php $conn->close(); ?>
