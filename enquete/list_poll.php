<?php
session_start();
include('../includes/db_connection.php');
include('../header.php');

// Função para deletar uma enquete
function deletePoll($poll_id, $conn) {
    // Certifique-se de realizar a lógica adequada para excluir a enquete do banco de dados
    // Substitua o seguinte código pelo código de exclusão real
    $sql = "DELETE FROM enquetes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $poll_id);

    if ($stmt->execute()) {
        // Redirecione de volta para a página de lista de enquetes após a exclusão bem-sucedida
        header("Location: lista_enquetes.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Erro ao excluir a enquete.</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lista de Enquetes</title>
</head>
<body>
<div class="container mt-5">
    <h1>Lista de Enquetes</h1>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Pergunta</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM enquetes";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["pergunta"] . "</td>";
                echo "<td>" . $row["status"] . "</td>";
                echo "<td>";
                echo "<a href='votar.php?id=" . $row["id"] . "' class='btn btn-primary'>Visualizar</a> ";
                echo "<a href='edit_poll.php?id=" . $row["id"] . "' class='btn btn-warning'>Editar</a> ";

                // Adicione o botão "Deletar" e confirme a exclusão com um prompt de confirmação
                echo "<button class='btn btn-danger' onclick='confirmDelete(" . $row["id"] . ")'>Deletar</button>";

                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Nenhuma enquete cadastrada.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<!-- Adicione um script JavaScript para confirmar a exclusão -->
<script>
    function confirmDelete(pollId) {
        if (confirm("Tem certeza de que deseja excluir esta enquete?")) {
            // Redirecione para uma página ou script que realizará a exclusão
            window.location.href = 'delete_poll.php?id=' + pollId;
        }
    }
</script>

<?php
include('../footer.php');
?>
</body>
</html>
