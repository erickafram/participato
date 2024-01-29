<?php
session_start();
include('../includes/db_connection.php');
include('../header.php'); // Inclua o arquivo header.php aqui

// Recuperar todas as notícias disponíveis para os usuários
$sql = "SELECT * FROM noticias ORDER BY data_publicacao DESC";
$result = mysqli_query($conn, $sql);

$remaining_news = []; // Inicialize a variável para armazenar as notícias restantes
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Notícias</title>
</head>
<body>

<div class="container mt-5">
    <div class="row">
        <?php
        $news_count = 0;
        while ($row = mysqli_fetch_assoc($result)):
            if ($news_count < 3):
                ?>

                <div class="col-md-4">
                    <a href="ver_noticia.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                        <div class="content-wrapper">
                            <div class="news-card">
                                <!-- Removido o link interno desnecessário aqui -->
                                <img src="<?php echo $row['imagem_destaque']; ?>" class="news-card__image" alt="">
                                <div class="news-card__text-wrapper">
                                    <h2 class="news-card__title"><?php echo $row['titulo']; ?></h2>
                                    <!-- <div class="news-card__post-date">Jan 29, 2018</div> -->
                                    <div class="news-card__details-wrapper">
                                        <p class="news-card__excerpt"><?php echo (strlen($row['subtitulo']) > 70) ? substr($row['subtitulo'], 0, 70) . "..." : $row['subtitulo']; ?></p>
                                        <!-- Você pode corrigir este link para apontar para um destino específico, se necessário -->
                                        <!-- <a href="ver_noticia.php?id=<?php echo $row['id']; ?>" class="news-card__read-more">Read more <i class="fas fa-long-arrow-alt-right"></i></a> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <!-- <div class="col-md-4">
                                <a href="ver_noticia.php?id=<?php echo $row['id']; ?>" style="color: inherit; text-decoration: none;">
                                    <div class="card mb-4">
                                        <img src="<?php echo $row['imagem_destaque']; ?>" class="card-img-top" alt="Imagem de Destaque">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $row['titulo']; ?></h5>
                                            <p class="card-text"><?php echo (strlen($row['subtitulo']) > 70) ? substr($row['subtitulo'], 0, 70) . "..." : $row['subtitulo']; ?></p>
                                        </div>
                                    </div>
                                </a>
                            </div> -->
                <?php
                $news_count++;
            else:
                $remaining_news[] = $row;
            endif;
        endwhile;
        ?>
    </div>

    <!-- Lista de notícias restantes com layout melhorado -->
    <div class="card mb-4">
        <?php foreach ($remaining_news as $row): ?>
            <div class="news-item">
                <img src="<?php echo $row['imagem_destaque']; ?>" class="news-img" alt="Imagem de Destaque">
                <div class="news-content">
                    <h5><a href="ver_noticia.php?id=<?php echo $row['id']; ?>"><?php echo $row['titulo']; ?></a></h5>
                    <p><?php echo $row['subtitulo']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>