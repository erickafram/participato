<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Verifique se o usuário é um administrador
if ($_SESSION["user_tipo"] !== "administrador") {
    header("Location: access_denied.php");
    exit();
}

if (isset($_GET['approve'])) {
    $donation_id = $_GET['approve'];
    $sql_approve = "UPDATE doacoes SET status = 'Aprovado' WHERE id = ?";
    $stmt_approve = $conn->prepare($sql_approve);
    $stmt_approve->bind_param("i", $donation_id);

    if ($stmt_approve->execute()) {
        echo "<script>alert('Doação ID $donation_id aprovada com sucesso!');</script>";
    } else {
        echo "<script>alert('Erro ao aprovar a doação ID $donation_id.');</script>";
    }
}

$sql_admin = "SELECT doacoes.*, usuarios.nome_usuario, vaquinhas.titulo AS nome_vaquinha FROM doacoes 
              LEFT JOIN usuarios ON doacoes.id_doador = usuarios.id 
              LEFT JOIN vaquinhas ON doacoes.id_vaquinha = vaquinhas.id
              WHERE doacoes.status = 'Pendente'
              ORDER BY doacoes.data DESC"; // Adicionado ORDER BY para ordenar pela data
$result_admin = $conn->query($sql_admin);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Aprovação de Doações - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .table-font-small {
            font-size: 0.85em;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h2>Doações Pendentes</h2>
    <table class="table table-striped table-font-small">
        <thead>
        <tr>
            <th style="width: 20%;">Nome da Vaquinha</th>
            <th>Doador</th>
            <th>Email</th>
            <th>CPF</th>
            <th>Valor</th>
            <th>Data</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result_admin->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['nome_vaquinha']; ?></td>
                <td><?php echo $row['anonimo'] ? 'Anônimo' : ($row['nome_usuario'] ?? $row['nome_completo']); ?></td>
                <td><?php echo $row['email'] ?? 'Não disponível'; ?></td>
                <td><?php echo $row['cpf'] ?? 'Não disponível'; ?></td>
                <td>R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                <td><?php echo date("d/m/Y H:i:s", strtotime($row['data'])); ?></td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Ações
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href='approve_donations.php?approve=<?php echo $row["id"]; ?>'>Aprovar</a>
                            <a class="dropdown-item" href='edit_donation.php?id=<?php echo $row["id"]; ?>'>Editar</a>
                            <a class="dropdown-item" href='delete_donation.php?id=<?php echo $row["id"]; ?>' onclick='return confirm("Tem certeza que deseja excluir esta doação?")'>Excluir</a>
                            <a class="dropdown-item" href='detalhes_doacao.php?id=<?php echo $row["id"]; ?>'>Detalhes</a>
                            <a class="dropdown-item" href='notify_donor.php?id=<?php echo $row["id"]; ?>'>Notificar Doador</a>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php $conn->close(); ?>