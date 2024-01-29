<?php
session_start();
include('../includes/db_connection.php');
include('../header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Resultados das Enquetes</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Resultados das Enquetes</h1>

    <?php
    $sql = "SELECT * FROM enquetes WHERE status='fechado'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $poll_id = $row["id"];
            $poll_question = $row["pergunta"];

            // Obtenha os dados do gráfico
            $sql_chart = "SELECT o.opcao, COUNT(v.id) as votos FROM opcoes_enquete o LEFT JOIN votos_enquete v ON o.id = v.id_opcao WHERE o.id_enquete = ? GROUP BY o.id";
            $stmt_chart = $conn->prepare($sql_chart);
            $stmt_chart->bind_param("i", $poll_id);
            $stmt_chart->execute();
            $result_chart = $stmt_chart->get_result();
            $chart_data = $result_chart->fetch_all(MYSQLI_ASSOC);

            if (!empty($chart_data)) {
                ?>
                <h2><?php echo htmlspecialchars($poll_question); ?></h2>
                <canvas id="chart_<?php echo $poll_id; ?>" width="300" height="150"></canvas>
                <script>
                    var ctx_<?php echo $poll_id; ?> = document.getElementById('chart_<?php echo $poll_id; ?>').getContext('2d');
                    var labels_<?php echo $poll_id; ?> = <?php echo json_encode(array_column($chart_data, 'opcao')); ?>;
                    var data_<?php echo $poll_id; ?> = <?php echo json_encode(array_column($chart_data, 'votos')); ?>;

                    var myChart_<?php echo $poll_id; ?> = new Chart(ctx_<?php echo $poll_id; ?>, {
                        type: 'bar',
                        data: {
                            labels: labels_<?php echo $poll_id; ?>,
                            datasets: [{
                                label: 'Votos',
                                data: data_<?php echo $poll_id; ?>,
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        }
                    });
                </script>
                <?php
            } else {
                echo "<p>Não há dados disponíveis para esta enquete.</p>";
            }
        }
    } else {
        echo "<p>Nenhuma enquete fechada encontrada.</p>";
    }
    ?>
</div>

<?php
include('../footer.php');
?>
</body>
</html>
