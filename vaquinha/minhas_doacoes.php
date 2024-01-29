<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$sql_minhas_doacoes = "SELECT doacoes.*, vaquinhas.titulo AS vaquinha_titulo
                      FROM doacoes
                      INNER JOIN vaquinhas ON doacoes.id_vaquinha = vaquinhas.id
                      WHERE id_doador = ?
                      ORDER BY data DESC";

$stmt_minhas_doacoes = $conn->prepare($sql_minhas_doacoes);
$stmt_minhas_doacoes->bind_param("i", $user_id);
$stmt_minhas_doacoes->execute();
$result_minhas_doacoes = $stmt_minhas_doacoes->get_result();

// Restante do código HTML para exibir as doações feitas pelo usuário
?>
<!DOCTYPE html>
<html>
<head>
    <title>Minhas Doações - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h1>Minhas Doações</h1>
    <table class="table">
        <thead>
        <tr>
            <th>Doação</th>
            <th>Vaquinha</th>
            <th>Valor</th>
            <th>Status</th> <!-- Coluna adicionada -->
        </tr>
        </thead>
        <tbody>
        <?php while($minha_doacao = $result_minhas_doacoes->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php
                    if ($minha_doacao["anonimo"] === 0) {
                        echo '<i class="bi bi-person"></i> ';
                        $idDoador = $minha_doacao["id_doador"];
                        $sql_nome_doador = "SELECT nome_usuario FROM usuarios WHERE id = ?";
                        $stmt_nome_doador = $conn->prepare($sql_nome_doador);
                        $stmt_nome_doador->bind_param("i", $idDoador);
                        $stmt_nome_doador->execute();
                        $result_nome_doador = $stmt_nome_doador->get_result();
                        $nome_doador = $result_nome_doador->fetch_assoc();
                        echo $nome_doador["nome_usuario"];
                        $stmt_nome_doador->close(); // Feche o statement se não for anônimo
                    } else {
                        echo '<i class="bi bi-person"></i> Anônimo';
                    }
                    ?>
                </td>
                <td><a href="view_crowdfund.php?id=<?php echo $minha_doacao["id_vaquinha"]; ?>"><?php echo $minha_doacao["vaquinha_titulo"]; ?></a></td>
                <td>R$<?php echo number_format($minha_doacao["valor"], 2, ',', '.'); ?></td>
                <td><?php echo $minha_doacao["status"]; ?></td> <!-- Célula adicionada -->

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$stmt_minhas_doacoes->close();
$conn->close();
?>
