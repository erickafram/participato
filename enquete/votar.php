<?php
ob_start();
session_start();
include('../includes/db_connection.php');
if (isset($_GET['id'])) {
    $poll_id = intval($_GET['id']);
    $sql = "SELECT id, pergunta, descricao, imagem_path FROM enquetes WHERE id=? AND status='aberto'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $poll_result = $stmt->get_result();
    $poll_data = $poll_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title><?php if (isset($poll_data)) echo htmlspecialchars($poll_data['pergunta']); ?> | Participa Tocantins</title>
    <link rel="stylesheet" href="/sistema/assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php if (isset($poll_data)): ?>
        <!-- Open Graph meta tags -->
        <meta property="og:image" itemprop="image" content="<?php echo $poll_data['imagem_path']; ?>">
        <meta property="og:title" content="<?php echo htmlspecialchars($poll_data['pergunta']); ?>">
        <meta property="og:description" content="<?php echo substr(htmlspecialchars_decode($poll_data['descricao']), 0, 200); ?>">
        <meta property="og:site_name" content="Participa Tocantins"/>
        <meta property="og:url" content="https://participato.com.br/sistema/enquete/votar.php?id=<?php echo $poll_id; ?>">
        <!-- Twitter Card meta tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo htmlspecialchars($poll_data['pergunta']); ?>">
        <meta name="twitter:description" content="<?php echo substr(strip_tags(htmlspecialchars_decode($poll_data['descricao'])), 0, 200); // limitando a descrição a 200 caracteres ?>">
        <meta name="twitter:image" content="<?php echo $poll_data['imagem_path']; ?>">
    <?php endif; ?>
</head>
<body>
<?php include('popup.php'); ?>
<?php include "../header.php"; ?>
<div id="progress-bar"></div>
<div class="container mt-5 ver_noticia">
    <?php
    if (isset($_SESSION['vote_success'])) {
        echo "<div class='alert alert-success'>Voto registrado com sucesso!</div>";
        unset($_SESSION['vote_success']);
    }
    if (isset($_GET['id'])) {
        $poll_id = intval($_GET['id']);
        $sql = "SELECT id, pergunta, descricao, imagem_path FROM enquetes WHERE id=? AND status='aberto'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $poll_id);
        $stmt->execute();
        $poll_result = $stmt->get_result();
        $poll_data = $poll_result->fetch_assoc();

        if ($poll_data) {
            // Exibir o gráfico
            // Get chart data
            $sql = "SELECT o.opcao, COUNT(v.id) as votos, (COUNT(v.id) / (SELECT COUNT(*) FROM votos_enquete WHERE id_enquete = ?)) * 100 as porcentagem FROM opcoes_enquete o LEFT JOIN votos_enquete v ON o.id = v.id_opcao WHERE o.id_enquete = ? GROUP BY o.id";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $poll_id, $poll_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $chart_data = $result->fetch_all(MYSQLI_ASSOC);

            // Verificar se o usuário já votou
            $has_voted = false;

            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $sql = "SELECT * FROM votos_enquete WHERE id_opcao IN (SELECT id FROM opcoes_enquete WHERE id_enquete=?) AND (id_usuario=? OR ip=?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $poll_id, $user_id, $_SERVER['REMOTE_ADDR']);
                $stmt->execute();
                $vote_result = $stmt->get_result();
                $has_voted = $vote_result->num_rows > 0;
            } else {
                // Verificar pelo IP se o usuário não estiver logado
                $user_ip = $_SERVER['REMOTE_ADDR'];
                $sql = "SELECT * FROM votos_enquete WHERE id_opcao IN (SELECT id FROM opcoes_enquete WHERE id_enquete=?) AND ip=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $poll_id, $user_ip);
                $stmt->execute();
                $vote_result = $stmt->get_result();
                $has_voted = $vote_result->num_rows > 0;
            }

        if ($has_voted) {
            echo "<div class='alert alert-warning'>Você já votou nesta enquete.</div>";
            echo "<center><div style='font-size: 26px; font-weight: bold;padding-bottom:5px;'>";
            echo htmlspecialchars($poll_data['pergunta']);?>
            <center>
                <div class="share-buttons mt-3">
                    <!-- Compartilhar no WhatsApp -->
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Confira esta enquete:\n" . htmlspecialchars($poll_data['pergunta']) . "\nhttps://participato.com.br/sistema/enquete/votar.php?id=" . $poll_id); ?>" target="_blank" class="btn btn-success">WhatsApp</a>

                    <!-- Compartilhar no Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://participato.com.br/sistema/enquete/votar.php?id=' . $poll_id); ?>" target="_blank" class="btn btn-primary">Facebook</a>

                    <!-- Compartilhar no Twitter -->
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://participato.com.br/sistema/enquete/votar.php?id=' . $poll_id); ?>&text=<?php echo urlencode('Confira esta enquete: ' . htmlspecialchars($poll_data['pergunta'])); ?>" target="_blank" class="btn btn-info">Twitter</a>
                </div>
            </center>

            <!-- INSERIR IMAGEM NA ENQUETE -->
            <?php if (isset($poll_data['imagem_path']) && !empty($poll_data['imagem_path'])): ?>
            <div class="text-center mt-3">
                <img src="<?php echo $poll_data['imagem_path']; ?>" alt="Imagem da Enquete" class="img-fluid">
            </div>
        <?php endif; ?>

            <?php
            echo "</div></center>";
            echo "<div style='font-size: 19px;'> <p>" . htmlspecialchars_decode($poll_data['descricao']) . "</p></div>";
            ?>

            <div class="container-sm mt-5" style="max-width: 900px;border-radius: 4px; border: 2px solid #25D366;">
                <div class="row align-items-center" style="padding:10px; background-color: #fdfdfd;">
                    <!-- Imagem -->
                    <div class="col-lg-1 col-md-2 col-sm-3 col-4">
                        <img src="../assets/imagem_site/logo-site.png" alt="Participa Tocantins" width="44" height="48" class="custom-border">
                    </div>

                    <!-- Texto -->
                    <div class="col-lg-7 col-md-6 col-sm-5 col-8" style="font-size: 12px;">
                        Receba, em primeira mão, as principais notícias do Participa Tocantins no seu Telegram!
                    </div>

                    <!-- Botão -->
                    <div class="col-lg-4 col-md-4 col-sm-4 col-12 text-right mt-sm-2 mt-2">
                        <!-- <a href="https://t.me/participatocantins" target="_blank" class="btn btn-success custom-border">
                            Inscreva-se <i class="bi bi-telegram"></i> -->
                        </a>
                    </div>
                </div>
            </div>
            <div style="border-bottom:1px solid #eee; margin-top:10px;"></div>

            <?php

        } else {
            // Se o usuário ainda não votou, exibir o formulário de votação
            $sql = "SELECT * FROM opcoes_enquete WHERE id_enquete=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $poll_id);
            $stmt->execute();
            $option_result = $stmt->get_result();
            $options = $option_result->fetch_all(MYSQLI_ASSOC);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $selected_option = intval($_POST['option']);
                $sql = "INSERT INTO votos_enquete (id_opcao, id_usuario, ip) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $selected_option, $_SESSION['user_id'], $_SERVER['REMOTE_ADDR']);
                if ($stmt->execute()) {
                    $_SESSION['vote_success'] = true;
                    // Redirecionar após o sucesso
                    header("Location: votar.php?id=" . $poll_id);
                    exit;
                } else {
                    echo "<div class='alert alert-danger'>Erro ao registrar voto.</div>";
                }
            }
            ?>
             <div style="font-size: 26px; font-weight: bold;padding-bottom: 5px;">
            <center><?php echo htmlspecialchars($poll_data['pergunta']); ?></center>
             </div>
            <center>
                <div class="share-buttons mt-3" style="padding-bottom:10px;">
                    <!-- Compartilhar no WhatsApp -->
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('Confira esta enquete: ' . htmlspecialchars($poll_data['pergunta']) . ' - https://participato.com.br/sistema/enquete/votar.php?id=' . $poll_id); ?>" target="_blank" class="btn btn-success">WhatsApp</a>
                    <!-- Compartilhar no Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://participato.com.br/sistema/enquete/votar.php?id=' . $poll_id); ?>" target="_blank" class="btn btn-primary">Facebook</a>
                    <!-- Compartilhar no Twitter -->
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://participato.com.br/sistema/enquete/votar.php?id=' . $poll_id); ?>&text=<?php echo urlencode('Confira esta enquete: ' . htmlspecialchars($poll_data['pergunta'])); ?>" target="_blank" class="btn btn-info">Twitter</a>
                </div>
            </center>
            <!-- INSERIR IMAGEM NA ENQUETE -->
            <?php if (isset($poll_data['imagem_path']) && !empty($poll_data['imagem_path'])): ?>
            <div class="text-center mt-3">
                <img src="<?php echo $poll_data['imagem_path']; ?>" alt="Imagem da Enquete" class="img-fluid">
            </div>
        <?php endif; ?>
        <div style="font-size: 19px;"><?php if (!empty($poll_data['descricao'])): ?></div>
        <div style="font-size: 19px;"><p><?php echo htmlspecialchars_decode($poll_data['descricao']); ?></p></div>
        <?php endif; ?>
            <div class="container-sm mt-5" style="max-width: 900px;border-radius: 4px; border: 2px solid #25D366;">
                <div class="row align-items-center" style="padding:10px; background-color: #fdfdfd;">
                    <!-- Imagem -->
                    <div class="col-lg-1 col-md-2 col-sm-3 col-4">
                        <img src="../assets/imagem_site/logo-site.png" alt="Participa Tocantins" width="44" height="48" class="custom-border">
                    </div>

                    <!-- Texto -->
                    <div class="col-lg-7 col-md-6 col-sm-5 col-8" style="font-size: 12px;">
                        Receba, em primeira mão, as principais notícias do Participa Tocantins no seu Telegram!
                    </div>

                    <!-- Botão -->
                    <div class="col-lg-4 col-md-4 col-sm-4 col-12 text-right mt-sm-2 mt-2">
                        <!-- <a href="https://t.me/participatocantins" target="_blank" class="btn btn-success custom-border">
                            Inscreva-se <i class="bi bi-telegram"></i> -->
                        </a>
                    </div>
                </div>
            </div>

            <hr>
            <div style="font-size: 16px; font-weight: bold;"><?php echo htmlspecialchars($poll_data['pergunta']); ?></div>
            <form action="" method="post">
                <?php foreach ($options as $option): ?>
                    <?php if (!empty($option['opcao'])): ?> <!-- Verificar se a opção tem texto -->
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="option" value="<?php echo $option['id']; ?>" required>
                            <label class="form-check-label">
                                <?php echo htmlspecialchars($option['opcao']); ?>
                            </label>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary mt-3">Votar</button>
            </form>


        <?php
        }
        // Contar o número total de votos na enquete
        $sql_count_votes = "SELECT COUNT(id) as total_votos FROM votos_enquete WHERE id_opcao IN (SELECT id FROM opcoes_enquete WHERE id_enquete=?)";
        $stmt_count_votes = $conn->prepare($sql_count_votes);
        $stmt_count_votes->bind_param("i", $poll_id);
        $stmt_count_votes->execute();
        $result_count_votes = $stmt_count_votes->get_result();
        $row_count_votes = $result_count_votes->fetch_assoc();
        $total_votos = $row_count_votes['total_votos'];

        // Filtrar opções sem texto
        $filtered_chart_data = [];
        foreach ($chart_data as $option_data) {
            if (!empty($option_data['opcao'])) {
                $filtered_chart_data[] = $option_data;
            }
        }
        ?>
            <!-- INICIO DO GRAFICO -->
            <center><h5 style="font-size: 13px;font-weight: bold; padding-top:10px;"><?php echo htmlspecialchars($poll_data['pergunta']); ?></h5></center>
        <?php if (!empty($filtered_chart_data)): ?>
            <canvas id="myChart" class="mx-auto" width="800" height="300"></canvas>
            <script>
                var ctx = document.getElementById('myChart').getContext('2d');
                var labels = <?php echo json_encode(array_column($filtered_chart_data, 'opcao')); ?>;
                var data = <?php echo json_encode(array_column($filtered_chart_data, 'votos')); ?>;

                var myChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Votos',
                            data: data,
                            backgroundColor: [
                                'rgb(190,16,0)',  // Cor da primeira fatia
                                'rgba(54, 162, 235, 0.6)',  // Cor da segunda fatia
                                'rgba(255, 206, 86, 0.6)',  // Cor da terceira fatia
                                'rgba(75, 192, 192, 0.6)',  // Cor da quarta fatia
                                'rgba(153, 102, 255, 0.6)', // Cor da quinta fatia
                                'rgba(255, 159, 64, 0.6)',  // Cor da sexta fatia
                            ],
                        }]
                    },
                    options: {
                        responsive: false,
                        width: 300,
                        height: 300,
                    }
                });
            </script>
        <?php endif; ?>
            <!-- FIM DO GRAFICO -->
        <?php if (!empty($total_votos)): ?>
            <center><strong><p>Total de Votos: <?php echo number_format($total_votos, 0, ',', '.'); ?></p></strong></center>
            <ul>
                <?php foreach ($filtered_chart_data as $option_data): ?>
                    <li style="margin-left:15px;list-style-type: none;font-size: 16px;font-weight: bold;padding:0 0 5px;"><i class="bi bi-person"></i> <?php echo htmlspecialchars($option_data['opcao']) . ': ' . number_format($option_data['votos'], 0, ',', '.') . ' votos'; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
            <!-- INÍCIO DAS REDES SOCIAIS -->
            <div class="social-icons mt-4 text-center" style="background-color: #eee; padding: 20px;">
                <a href="https://instagram.com/participa.to?igshid=OGQ5ZDc2ODk2ZA==" class="mr-3"><i class="bi bi-instagram" style="font-size: 2rem;"></i></a>
                <a href="https://t.me/participatocantins" class="mr-3"><i class="bi bi-telegram" style="font-size: 2rem;"></i></a>
                <a href="https://www.youtube.com/@ParticipaTocantins/" class="mr-3"><i class="bi bi-youtube" style="font-size: 2rem;"></i></a>
                <a href="https://www.facebook.com/profile.php?id=61551844856499" class="mr-3"><i class="bi bi-facebook" style="font-size: 2rem;"></i></a>
                <a href="https://www.tiktok.com/@participatocantins"><i class="bi bi-tiktok" style="font-size: 2rem;"></i></a>
                <p class="mt-3">Siga-nos nas redes sociais para ficar informado.</p>
            </div>
    <div class="fb-comments-container">
    <h3 style="border-top:1px solid #eee;margin-top:50px;">Comentários</h3>
            <div style="color: #555555; font-weight: bold; font-size: 12px;">Os comentários são de responsabilidade exclusiva de seus autores e não representam a opinião deste site. Se achar algo que viole os termos de uso, denuncie. Leia as perguntas mais frequentes para saber o que é impróprio ou ilegal.</div>
            <div class="fb-comments" style="width: 100%;" data-href="https://participato.com.br/sistema/enquete/votar.php?id=<?php echo $poll_id; ?>" data-width="100%" data-numposts="5"></div>
            <!-- SDK do Facebook para JavaScript -->
            <div id="fb-root"></div>
            <script async defer crossorigin="anonymous" src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v10.0" nonce="YOUR_NONCE"></script>
    </div>
            <!-- Seção de Enquetes Relacionadas -->
    <h4 class="mt-5">Enquetes Relacionadas</h4>
    <div class="row">
        <?php

        function countVotesForPoll($poll_id, $conn) {
            $count_votes_sql = "SELECT COUNT(*) AS total_votes FROM votos_enquete WHERE id_opcao IN (SELECT id FROM opcoes_enquete WHERE id_enquete = ?)";
            $stmt = $conn->prepare($count_votes_sql);
            $stmt->bind_param("i", $poll_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['total_votes'];
            } else {
                return 0;
            }
        }


        // Selecionando outras enquetes com status 'aberto' e que não são a enquete atual
        $related_polls_sql = "SELECT * FROM enquetes WHERE status='aberto' AND id != ? ORDER BY criado_em DESC LIMIT 5";
        $stmt = $conn->prepare($related_polls_sql);
        $stmt->bind_param("i", $poll_id);
        $stmt->execute();
        $related_polls_result = $stmt->get_result();

        if ($related_polls_result->num_rows > 0) {
            while ($related_poll = $related_polls_result->fetch_assoc()) {
                echo '<div class="col-md-4">';
                echo '<div class="card mb-4">';
                if (!empty($related_poll["imagem_path"])) {
                    echo '<img src="/sistema/enquete/' . $related_poll["imagem_path"] . '" class="card-img-top" alt="Imagem da Enquete">';
                }
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($related_poll["pergunta"]) . '</h5>';

                // Chama a função para contar os votos e exibe a quantidade de votos
                $votes_count = countVotesForPoll($related_poll["id"], $conn);
                echo '<p class="card-text">Total de votos: ' . number_format($votes_count, 0, ',', '.') . '</p>';

                echo '<a href="votar.php?id=' . $related_poll["id"] . '" class="btn btn-primary btn-lg btn-block">Votar</a>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "<li>Não há enquetes relacionadas no momento.</li>";
        }
        ?>
        </ul>
        <?php } else {
            echo "<div class='alert alert-danger'>Enquete não encontrada ou já fechada.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>ID da enquete não fornecido.</div>";
    }
    ?>
</div>
</div>
<?php
include('../footer.php');
ob_end_flush();
?>

<script>
    window.onscroll = function() { scrollFunction() };

    function scrollFunction() {
        var scrollTop = document.documentElement.scrollTop;
        var scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var scrollPercentage = (scrollTop / scrollHeight) * 100;
        document.getElementById("progress-bar").style.width = scrollPercentage + "%";
    }
</script>
</body>
</html>