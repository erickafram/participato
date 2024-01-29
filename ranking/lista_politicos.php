<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../login.php");
    exit();
}

// Consulta SQL para recuperar os políticos cadastrados e as quantidades totais de PLO e REQ de todos os anos
$query = "SELECT politicos.id, politicos.nome, politicos.partido, politicos.telefone, politicos.email, politicos.cargo, politicos.periodo_mandato,
    SUM(projetos_legislativos.plo) AS total_plo, SUM(projetos_legislativos.req) AS total_req
    FROM politicos
    LEFT JOIN projetos_legislativos ON politicos.id = projetos_legislativos.politico_id
    GROUP BY politicos.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Políticos | Sua Plataforma</title>
    <!-- Inclua os links para o Bootstrap e jQuery de uma CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Lista de Políticos</h2>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Partido</th>
            <th>Telefone</th>
            <th>Email</th>
            <th>Cargo Político</th>
            <th>Total de PLO</th>
            <th>Total de REQ</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["nome"] . "</td>";
                echo "<td>" . $row["partido"] . "</td>";
                echo "<td>" . $row["telefone"] . "</td>";
                echo "<td>" . $row["email"] . "</td>";
                echo "<td>" . $row["cargo"] . "</td>";
                echo "<td>" . $row["total_plo"] . "</td>";
                echo "<td>" . $row["total_req"] . "</td>";
                echo "<td class='btn-group'>";
                echo "  <button type='button' class='btn btn-primary dropdown-toggle' data-bs-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
                echo "    Ações";
                echo "  </button>";
                echo "  <div class='dropdown-menu'>";
                echo "    <a class='dropdown-item' href='editar_politico.php?id=" . $row["id"] . "'>Editar</a>";
                echo "    <a class='dropdown-item' href='adicionar_projetos.php?id=" . $row["id"] . "'>+ PLO/REQ</a>";
                echo "  </div>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9'>Nenhum político cadastrado.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Botão para ir para a página lista_projetos_legislativos.php -->
<div class="container mt-3">
    <a href="lista_projetos_legislativos.php" class="btn btn-info">Ver Lista de Projetos Legislativos</a>
</div>

</body>
</html>
