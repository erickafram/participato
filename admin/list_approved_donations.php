<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

// Verifique se o usuário está logado e se é administrador
if (!isset($_SESSION["user_id"]) || $_SESSION["user_tipo"] !== "administrador") {
    header("Location: login.php");
    exit();
}

// Consulta para obter todas as vaquinhas
$sql_vaquinhas = "SELECT id, titulo FROM vaquinhas";
$result_vaquinhas = $conn->query($sql_vaquinhas);

$vaquinha_selecionada = $_GET['vaquinha'] ?? null; // Pega a vaquinha selecionada

$sql_doacoes = "SELECT doacoes.*, usuarios.nome_usuario, vaquinhas.titulo AS nome_vaquinha FROM doacoes 
                LEFT JOIN usuarios ON doacoes.id_doador = usuarios.id 
                LEFT JOIN vaquinhas ON doacoes.id_vaquinha = vaquinhas.id
                WHERE doacoes.status = 'Aprovado'";


if ($vaquinha_selecionada) {
    $sql_doacoes .= " AND doacoes.id_vaquinha = ?";
    $stmt_doacoes = $conn->prepare($sql_doacoes);
    $stmt_doacoes->bind_param("i", $vaquinha_selecionada);
    $stmt_doacoes->execute();
    $result_doacoes = $stmt_doacoes->get_result();
} else {
    $result_doacoes = $conn->query($sql_doacoes);
}

// Variável para armazenar o valor total das doações aprovadas para a vaquinha selecionada
$total_doacoes_aprovadas = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Listagem de Doações Aprovadas - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <h2>Doações Aprovadas</h2>
    <form action="list_approved_donations.php" method="get">
        <div class="form-group">
            <label for="vaquinha">Selecione uma Vaquinha:</label>
            <select class="form-control" id="vaquinha" name="vaquinha">
                <option value="">Todas</option>
                <?php while ($vaquinha = $result_vaquinhas->fetch_assoc()) {
                    echo "<option value='" . $vaquinha['id'] . "' " . ($vaquinha_selecionada == $vaquinha['id'] ? "selected" : "") . ">" . $vaquinha['titulo'] . "</option>";
                } ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Nome da Vaquinha</th>
            <th>Doador</th>
            <th>CPF</th>
            <th>Valor</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result_doacoes->num_rows > 0) {
            while ($doacao = $result_doacoes->fetch_assoc()) {
                $valor_formatado = 'R$ ' . number_format($doacao['valor'], 2, ',', '.');
                $total_doacoes_aprovadas += $doacao['valor'];
                echo "<tr>";
                echo "<td>" . $doacao['nome_vaquinha'] . "</td>";
                echo "<td>" . ($doacao['nome_usuario'] ?? $doacao['nome_completo']) . "</td>";
                echo "<td>" . ($doacao['cpf'] ?? $doacao['cpf']) . "</td>";
                echo "<td>" . $valor_formatado . "</td>"; // Use o valor formatado aqui
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Nenhuma doação aprovada.</td></tr>";
        }
        ?>
        </tbody>
    </table>

    <?php
    echo "<h3>Total de Doações Aprovadas: R$" . number_format($total_doacoes_aprovadas, 2, ',', '.') . "</h3>";
    ?>

</div>

</body>
</html>

<?php $conn->close(); ?>
