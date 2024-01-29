<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Obter lista de vaquinhas criadas pelo usuário
$user_id = $_SESSION["user_id"];
$sql = "SELECT v.id, v.titulo, v.meta, v.status, v.aprovacao, 
               IFNULL(SUM(CASE WHEN d.status = 'Aprovado' THEN d.valor ELSE 0 END), 0) AS arrecadado
        FROM vaquinhas v
        LEFT JOIN doacoes d ON v.id = d.id_vaquinha
        WHERE v.id_usuario = ?
        GROUP BY v.id, v.titulo, v.meta, v.status, v.aprovacao";

$query = "SELECT numero_conta, nome_banco, cpf_cnpj_titular, agencia FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_bank_data = $result->fetch_assoc();

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$totalRepassar_user = 0; // Inicialize a variável totalRepassar
?>

<!DOCTYPE html>
<html>
<head>
    <title>Minhas Vaquinhas - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h4>Minhas Vaquinhas</h4>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Título</th>
            <th>Meta</th>
            <th>Arrecadado</th>
            <th>Status</th>
            <th>Aprovado?</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td style="width: 40%;">
                    <a href="view_crowdfund.php?id=<?php echo $row["id"]; ?>"><?php echo $row["titulo"]; ?></a>
                </td>
                <td>R$ <?php echo number_format($row["meta"], 2, ',', '.'); ?></td>
                <td>R$ <?php echo isset($row["arrecadado"]) ? number_format($row["arrecadado"], 2, ',', '.') : '0,00'; ?></td>
                <td>
                    <?php
                    if ($row["status"] === "finalizada") {
                        echo '<span style="color: green;">PAGO</span>';
                    } else {
                        echo ucfirst($row["status"]);
                    }
                    ?>
                </td>
                <td><?php echo ucfirst($row["aprovacao"]); ?></td>
                <td>
                    <a href="view_donations_user.php?id_vaquinha=<?php echo $row["id"]; ?>" class="btn btn-info btn-sm">Ver Doadores</a>
                    <!-- <a href="view_crowdfund.php?id=<?php echo $row["id"]; ?>" class="btn btn-primary btn-sm">Vanquinha</a> -->
                </td>
            </tr>
            <?php
            if (isset($row["arrecadado"])) {
                $valorArrecadado = $row["arrecadado"];
                if ($row["status"] !== "finalizada") {
                    $desconto = 0.15; // 15%
                    $valorARepassar = $valorArrecadado - ($valorArrecadado * $desconto); // Subtrai 15% do valor arrecadado
                    $totalRepassar_user += $valorARepassar; // Adicione o valor calculado ao total
                }
            }
            ?>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div class="alert alert-success" role="alert">
        <b>Total a ser repassado ao usuário: R$ <?php echo number_format($totalRepassar_user, 2, ',', '.'); ?></b>
        <p>Observação: Para manter o sistema ParticipaTO em funcionamento e cobrir os gastos relacionados ao projeto, 15% do valor arrecadado em cada vaquinha será destinado a esse fim.</p>
    </div>
</div>

<!-- Exibir ou editar dados bancários do usuário -->
<div class="container mt-5" style="padding-bottom:20px;">
    <h2>Dados Bancários</h2>
    <?php if (!empty($user_bank_data["numero_conta"])): ?>
        <p><b>Nome do Banco:</b> <?php echo $user_bank_data["nome_banco"]; ?></p>
        <p><b>Agência:</b> <?php echo $user_bank_data["agencia"]; ?></p>
        <p><b>Número da Conta:</b> <?php echo $user_bank_data["numero_conta"]; ?></p>
        <p><b>Pix:</b> <?php echo $user_bank_data["cpf_cnpj_titular"]; ?></p>
        <a href="../user/cadastro_dados_bancarios.php" class="btn btn-primary">Editar Dados Bancários</a>
    <?php else: ?>
        <p>Você ainda não cadastrou seus dados bancários para receber saldo das doações de vaquinha que você cadastrou.</p>
        <a href="../user/cadastro_dados_bancarios.php" class="btn btn-primary">Cadastrar Dados Bancários</a>
    <?php endif; ?>
</div>
</body>
</html>

<?php $stmt->close(); $conn->close(); ?>