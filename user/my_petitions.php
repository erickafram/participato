<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$query = "SELECT id, titulo, descricao, status, quantidade_assinaturas FROM abaixo_assinados WHERE id_usuario = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$petitions = [];
while ($row = $result->fetch_assoc()) {
    $petition = $row;

    $query_count = "SELECT quantidade_assinaturas FROM abaixo_assinados WHERE id = ?";
    $stmt_count = $conn->prepare($query_count);
    $stmt_count->bind_param("i", $petition['id']);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $row_count = $result_count->fetch_assoc();
    $petition['quantidade_assinaturas_necessarias'] = $row_count['quantidade_assinaturas'];

    $query_count = "SELECT COUNT(*) AS num_assinaturas FROM assinaturas WHERE id_abaixo_assinado = ?";
    $stmt_count = $conn->prepare($query_count);
    $stmt_count->bind_param("i", $petition['id']);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $row_count = $result_count->fetch_assoc();
    $petition['quantidade_assinaturas_existentes'] = $row_count['num_assinaturas'];

    $petitions[] = $petition;
}

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Minhas Petições - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>
<div class="container mt-5">
    <h2>Minhas Petições Criada</h2>

    <?php if (count($petitions) > 0): ?>
        <table class="table">
            <thead>
            <tr>
                <th>Título</th>
                <th>Status</th>
                <th>Assinaturas</th>
                <th>Meta</th>
                <th>Ação</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($petitions as $petition): ?>
                <tr>
                    <td><a href="<?php echo $petition['status'] === 'pendente' ? 'petition_details.php' : '../peticao/petition_details.php'; ?>?id=<?php echo $petition['id']; ?>"><?php echo $petition['titulo']; ?></a></td>
                    <td><?php echo $petition['status']; ?></td>
                    <td><?php echo $petition['quantidade_assinaturas_existentes']; ?></td>
                    <td><?php echo $petition['quantidade_assinaturas_necessarias']; ?></td>
                    <td><a href="edit_petition.php?id=<?php echo $petition['id']; ?>" class="btn btn-primary">Editar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Você ainda não criou nenhuma petição.</p>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Voltar para o Dashboard</a>
</div>
</body>
</html>
