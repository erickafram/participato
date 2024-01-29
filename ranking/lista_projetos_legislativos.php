<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../login.php");
    exit();
}

// Consulta SQL para recuperar os políticos que possuem PLO ou REQ cadastrados
$query = "SELECT politicos.nome, projetos_legislativos.ano, SUM(projetos_legislativos.plo) AS total_plo, SUM(projetos_legislativos.req) AS total_req
          FROM politicos
          LEFT JOIN projetos_legislativos ON politicos.id = projetos_legislativos.politico_id
          WHERE projetos_legislativos.plo > 0 OR projetos_legislativos.req > 0
          GROUP BY politicos.id, projetos_legislativos.ano
          ORDER BY politicos.nome, projetos_legislativos.ano";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lista de Projetos Legislativos | Sua Plataforma</title>
    <!-- Adicione links para folhas de estilo CSS e outros recursos aqui -->
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Lista de Projetos Legislativos por Ano</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Político</th>
            <th>Ano</th>
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
                echo "<td>" . $row["nome"] . "</td>";
                echo "<td>" . $row["ano"] . "</td>";
                echo "<td>" . $row["total_plo"] . "</td>";
                echo "<td>" . $row["total_req"] . "</td>";
                echo "<td>";
                echo "<a href='editar_projeto.php?ano=" . $row["ano"] . "' class='btn btn-primary'>Editar</a> ";
                echo "<a href='excluir_projeto.php?ano=" . $row["ano"] . "' class='btn btn-danger'>Excluir</a>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>Nenhum projeto legislativo cadastrado.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<?php include "../footer.php"; ?>
</body>
</html>
