<?php
session_start();
include('includes/db_connection.php');
include('header.php'); // Inclui o arquivo header.php que carrega o Bootstrap

// Consultas para buscar as últimas entradas
$query_peticoes = "SELECT * FROM abaixo_assinados WHERE status='aprovado' ORDER BY criado_em DESC LIMIT 3";
$query_vaquinhas = "SELECT * FROM vaquinhas WHERE status='aberto' ORDER BY criado_em DESC LIMIT 3";
$query_noticias = "SELECT * FROM noticias ORDER BY data_publicacao DESC LIMIT 3";
$query_enquetes = "SELECT * FROM enquetes WHERE status='aberto' ORDER BY criado_em DESC LIMIT 3";

$result_peticoes = mysqli_query($conn, $query_peticoes);
$result_vaquinhas = mysqli_query($conn, $query_vaquinhas);
$result_noticias = mysqli_query($conn, $query_noticias);
$result_enquetes = mysqli_query($conn, $query_enquetes);
?>

    <div class="container mt-5" style="padding-top:20px;">
        <!-- Últimas Notícias -->
        <div class="row" style="padding-bottom:20px;">
            <?php while($row = mysqli_fetch_assoc($result_noticias)): ?>
                <div class="col-md-4">
                    <a href="noticias/ver_noticia.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                        <div class="content-wrapper">
                            <div class="news-card">
                                <img src="noticias/<?php echo $row['imagem_destaque']; ?>" class="news-card__image" alt="">
                                <div class="news-card__text-wrapper">
                                    <h2 class="news-card__title"><?php echo $row['titulo']; ?></h2>
                                    <div class="news-card__details-wrapper">
                                        <p class="news-card__excerpt"><?php echo (strlen($row['subtitulo']) > 70) ? substr($row['subtitulo'], 0, 70) . "..." : $row['subtitulo']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center">
            <a href="noticias/index.php" class="btn btn-primary" style="margin-left:34px; margin-bottom:20px;">Ver todas as Notícias</a>
        </div>

        <!-- Vaquinhas -->
        <h5 style="font-size: 19px; border-left: 3px solid #4c9d60; padding: 0px 10px; margin: 0 13px;">Vaquinhas</h5>
        <div class="row" style="padding-bottom:20px;">
            <?php while($row = mysqli_fetch_assoc($result_vaquinhas)): ?>
                <div class="col-md-4">
                    <a href="vaquinha/view_crowdfund.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                        <div class="content-wrapper">
                            <div class="news-card">
                                <img src="vaquinha/images/<?php echo $row['imagem']; ?>" class="news-card__image" alt="<?php echo $row['titulo']; ?>">
                                <div class="news-card__text-wrapper">
                                    <h2 class="news-card__title"><?php echo $row['titulo']; ?></h2>
                                    <div class="news-card__details-wrapper">
                                        <p class="news-card__excerpt"><?php echo (strlen($row['descricao']) > 70) ? substr($row['descricao'], 0, 70) . "..." : $row['descricao']; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center">
            <a href="vaquinha/index.php" class="btn btn-primary" style="margin-left:34px; margin-bottom:20px;">Ver todas as Vaquinhas</a>
        </div>

        <!-- Petições -->
        <h5 style="font-size: 19px; border-left: 3px solid #006ebd; padding: 0px 10px; margin: 0 13px;">Petições</h5>
        <div class="row" style="padding-bottom:20px;">
            <?php while($row = mysqli_fetch_assoc($result_peticoes)): ?>
                <div class="col-md-4">
                    <a href="peticao/petition_details.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                        <div class="content-wrapper">
                            <div class="news-card">
                                <img src="assets/<?php echo $row['caminho_imagem']; ?>" class="news-card__image" alt="<?php echo htmlspecialchars($row['titulo']); ?>">
                                <div class="news-card__text-wrapper">
                                    <h2 class="news-card__title"><?php echo htmlspecialchars($row['titulo']); ?></h2>
                                    <div class="news-card__details-wrapper">
                                        <p class="news-card__excerpt"><?php echo (strlen($row['descricao']) > 70) ? substr($row['descricao'], 0, 70) . "..." : htmlspecialchars($row['descricao']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center">
            <a href="peticoes.php" class="btn btn-primary" style="margin-left:34px; margin-bottom:20px;">Ver todas as Petições</a>
        </div>

        <!-- ENQUETES -->
        <h5 style="font-size: 19px; border-left: 3px solid #d225fd; padding: 0px 10px; margin: 0 13px;">Enquetes</h5>
        <div class="row" style="padding-bottom:20px;">
            <?php while($row = mysqli_fetch_assoc($result_enquetes)): ?>
                <div class="col-md-4">
                    <a href="enquete/votar.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                        <div class="content-wrapper">
                            <div class="news-card">
                                <!-- Verifique se há uma imagem, caso contrário, use uma imagem padrão -->
                                <img src="<?php echo $row['imagem_path'] ? 'enquete/' . $row['imagem_path'] : 'assets/default-image.jpg'; ?>" class="news-card__image" alt="<?php echo htmlspecialchars($row['pergunta']); ?>">
                                <div class="news-card__text-wrapper">
                                    <h2 class="news-card__title"><?php echo htmlspecialchars($row['pergunta']); ?></h2>
                                    <div class="news-card__details-wrapper">
                                        <p class="news-card__excerpt"><?php echo (strlen($row['descricao']) > 70) ? substr($row['descricao'], 0, 70) . "..." : htmlspecialchars($row['descricao']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center">
            <a href="enquete/index.php" class="btn btn-primary" style="margin-left:34px; margin-bottom:20px;">Ver todas as Enquetes</a>
        </div>
    </div>

<?php include('footer.php'); // Se você tiver um arquivo de rodapé ?>