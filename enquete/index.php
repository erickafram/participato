<?php
session_start();
include('../includes/db_connection.php');
?>
    <!DOCTYPE html>
    <head>
        <title>Enquetes | Participa Tocantins </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
<body>
<?php include('popup.php'); ?>
<?php include "../header.php"; ?>
    <div class="container mt-5">
        <hr>
        <div class="row">
            <?php
            $sql = "SELECT e.id, e.pergunta, e.status, e.imagem_path, COUNT(v.id) AS quantidade_votos
                    FROM enquetes e
                    LEFT JOIN opcoes_enquete o ON e.id = o.id_enquete
                    LEFT JOIN votos_enquete v ON o.id = v.id_opcao
                    WHERE e.status='aberto'
                    GROUP BY e.id, e.pergunta, e.status, e.imagem_path
                    ORDER BY e.criado_em DESC";

            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $titulo = isset($row["pergunta"]) ? $row["pergunta"] : '';

                    echo '<div class="col-md-4">';
                    echo '<div class="card mb-4">';
                    if(!empty($row["imagem_path"])) {
                        echo '<div class="imagem-enquete"><img src="/sistema/enquete/' . $row["imagem_path"] . '" class="card-img-top" alt="Imagem da Enquete"></div>';
                    }
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($titulo) . '</h5>';
                    echo '<p class="card-text"><i class="bi bi-people"></i> Total de Votos: ' . number_format($row["quantidade_votos"], 0, ',', '.') . '</p>';
                    echo '<a href="/sistema/enquete/votar.php?id=' . $row["id"] . '" class="btn btn-primary btn-lg btn-block">Votar</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Nenhuma enquete disponível no momento.</p>';
            }
            ?>
        </div>
    </div>

<?php
?>
</body>
</html>