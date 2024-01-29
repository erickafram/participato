<?php
session_start();
require_once "includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Define o número de resultados por página
$resultados_por_pagina = 10;

// Determina a página atual
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina_atual = max(1, $pagina_atual);

// Calcula o limite inicial para a consulta SQL
$inicio = ($pagina_atual - 1) * $resultados_por_pagina;

// Consulta para obter o número total de doações relacionadas às vaquinhas criadas pelo usuário
$sql_total = "SELECT COUNT(*) AS total FROM doacoes d
               LEFT JOIN vaquinhas v ON d.id_vaquinha = v.id
               WHERE d.status = 'Aprovado' AND v.id_usuario = ?";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("i", $user_id);
$stmt_total->execute();
$resultado_total = $stmt_total->get_result();
$total_doacoes = $resultado_total->fetch_assoc()['total'];

// Calcula o número total de páginas
$total_paginas = ceil($total_doacoes / $resultados_por_pagina);

// Consulta para obter as doações da página atual relacionadas às vaquinhas do usuário
$sql = "SELECT d.*, v.titulo, u.nome_usuario, d.anonimo FROM doacoes d
        LEFT JOIN vaquinhas v ON d.id_vaquinha = v.id
        LEFT JOIN usuarios u ON d.id_doador = u.id
        WHERE d.status = 'Aprovado' AND v.id_usuario = ?
        ORDER BY d.data DESC
        LIMIT $inicio, $resultados_por_pagina";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo "Erro na consulta SQL: " . $conn->error;
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Todos os Alertas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .table-font-small {
            font-size: 0.8em;
        }
    </style>
</head>
<body>

<?php include_once "header.php"; ?>

<div class="container mt-4" style="padding-top:25px;">
    <h3>Todos os Alertas de Doações</h3>
    <table class="table table-bordered table-font-small">
        <thead>
        <tr>
            <th>Data</th>
            <th>Alerta</th>
            <th>Nome do Doador</th>
            <th>Valor</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($doacao = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($doacao['data'])); ?></td>
                <td><?php echo $doacao['titulo']; ?></td>
                <td><?php echo $doacao['anonimo'] ? 'Anônimo' : $doacao['nome_completo']; ?></td>
                <td>R$ <?php echo number_format($doacao['valor'], 2, ',', '.'); ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Paginação -->
    <nav>
        <ul class="pagination" style="padding-top:15px;padding-bottom:20px;">
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php if ($i === $pagina_atual) echo 'active'; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

</body>
</html>

<?php
$conn->close();
?>
