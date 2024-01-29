<?php
session_start();
include('../includes/db_connection.php');
include('../header.php'); // Inclua o arquivo header.php aqui

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

// Excluir notícia
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM noticias WHERE id = $id";
    if (mysqli_query($conn, $sql_delete)) {
        $delete_message = "Notícia excluída com sucesso!";
    } else {
        echo "Erro ao excluir notícia: " . mysqli_error($conn);
    }
}

// Consulta para listar as notícias
$sql = "SELECT * FROM noticias";
$result = mysqli_query($conn, $sql);

?>

<div class="container mt-5">
    <h1>Listar Notícias</h1>
    <?php if (isset($delete_message)): ?>
        <div class="alert alert-success">
            <?php echo $delete_message; ?>
        </div>
    <?php endif; ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Título</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['titulo']; ?></td>
                <td>
                    <a href="editar_noticia.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Editar</a>
                    <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza de que deseja excluir esta notícia?')">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Inclua os scripts do Bootstrap 4.5.2 e outros scripts personalizados aqui -->
</body>
</html>
