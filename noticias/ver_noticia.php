<?php
session_start();
include('../includes/db_connection.php');
include('../header.php'); // Inclua o arquivo header.php aqui

// Verificar se um ID de notícia foi passado via parâmetro
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Recuperar a notícia específica
    $sql = "SELECT n.*, u.nome_usuario AS autor_nome 
            FROM noticias n
            JOIN usuarios u ON n.id_administrador = u.id
            WHERE n.id = $id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    // Separar as tags e pegar a primeira
    $tags = explode(',', $row['tags']);
    $first_tag = $tags[0];

   // Buscar as notícias mais recentes (exceto a notícia atual)
$sql_related = "SELECT * FROM noticias WHERE id != $id ORDER BY data_publicacao DESC LIMIT 3";
$result_related = mysqli_query($conn, $sql_related);

} else {
    // Se nenhum ID de notícia foi passado, redirecione para a página de listagem de notícias
    header("Location: listar_noticias.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta charset="UTF-8">
    <title><?php echo $row['titulo']; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- Open Graph Meta Tags -->
    <title><?php echo $row['titulo']; ?></title>
    <meta property="og:title" content="<?php echo $row['titulo']; ?>" />
    <meta property="og:description" content="<?php echo substr($row['descricao'], 0, 200); ?>" />
    <meta property="og:image" content="<?php echo $row['imagem_destaque']; ?>" />
    <meta property="og:url" content="https://participato.com.br/sistema/noticias/ver_noticia.php?id=<?php echo $id; ?>" />
    <!-- Meta Tags do Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $row['titulo']; ?>">
    <meta name="twitter:description" content="<?php echo substr($row['descricao'], 0, 200); ?>">
    <meta name="twitter:image" content="<?php echo $row['imagem_destaque']; ?>">
    <meta property="og:site_name" content="Participato" />
</head>
<body>
<div id="progress-bar"></div>

<div class="container mt-5 ver_noticia">
<div class="card" style="border: 0px solid rgba(0,0,0,.125);">
    <div class="card-body" style="box-shadow: none;">
        <center>
            <h1 class="noticia card-title"><?php echo $row['titulo']; ?></h1>
            <h5 class="card-subtitle mb-2 text-muted" style="padding-bottom:10px;"><?php echo $row['subtitulo']; ?></h5>

                <!-- Botões de Compartilhamento -->
                <div class="share-buttons text-center mb-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://participato.com.br/sistema/noticias/ver_noticia.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-primary">
                        <i class="fa fa-facebook"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=https://participato.com.br/sistema/noticias/ver_noticia.php?id=<?php echo $id; ?>&text=<?php echo urlencode($row['titulo']); ?>" target="_blank" class="btn btn-light">
                        <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($row['titulo'] . "\n" . "\n" . "Leia Agora: https://participato.com.br/sistema/noticias/ver_noticia.php?id=" . $id); ?>" data-action="share/whatsapp/share" target="_blank" class="btn btn-success">
                        <i class="fa fa-whatsapp"></i>
                    </a>
                </div>
            </center>
            <img src="<?php echo $row['imagem_destaque']; ?>" class="card-img-top" alt="Imagem de Destaque">
        <!-- Nome do Autor -->
        <div class="author-info" style="padding-top:10px;">
            <p style="margin-bottom: 0; font-size: 14px;"><i class="fa fa-user"></i> Por: <?php echo $row['autor_nome']; ?> - Participa Tocantins</p>
            <p style="font-size: 14px;"><i class="fa fa-clock-o"></i> <?php echo date("d/m/Y", strtotime($row['data_publicacao'])); ?></p>
        </div>
            <div class="card-text" style="font-size:20px;"><?php echo $row['descricao']; ?></div>
            <p class="mt-3"><strong>Tags:</strong> <?php echo $row['tags']; ?></p>
            <p><strong>Palavra-chave:</strong> <?php echo $row['palavra_chave']; ?></p>
        </div>
    </div>

    <div class="fb-comments-container">
        <h3 style="border-top:1px solid #eee;margin-top:10px;">Comentários</h3>
        <div style="color: #555555; font-weight: bold; font-size: 12px;">Os comentários são de responsabilidade exclusiva de seus autores e não representam a opinião deste site. Se achar algo que viole os termos de uso, denuncie. Leia as perguntas mais frequentes para saber o que é impróprio ou ilegal.</div>
        <div class="fb-comments" style="width: 100%;" data-href="https://participato.com.br/sistema/noticias/ver_noticia.php?id=<?php echo $id; ?>" data-width="100%" data-numposts="5"></div>
        <!-- SDK do Facebook para JavaScript -->
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v10.0" nonce="YOUR_NONCE"></script>
    </div>

    <!-- Barra de Compartilhamento para Dispositivos Móveis -->
    <div class="mobile-share-bar">
        <span class="share-text">Compartilhar:</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=https://participato.com.br/sistema/noticias/ver_noticia.php?id=<?php echo $id; ?>" class="btn btn-primary" style="margin-right: 10px;" target="_blank">
            <i class="fa fa-facebook"></i>
        </a>
        <a href="https://twitter.com/intent/tweet?url=https://participato.com.br/sistema/noticias/ver_noticia.php?id=<?php echo $id; ?>&text=<?php echo urlencode($row['titulo']); ?>" class="btn btn-light" style="margin-right: 10px;" target="_blank">
            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
        </a>
        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($row['titulo'] . "\n" . "\n" . "Leia Agora: https://participato.com.br/sistema/noticias/ver_noticia.php?id=" . $id); ?>" data-action="share/whatsapp/share" target="_blank" class="btn btn-success">
            <i class="fa fa-whatsapp"></i>
        </a>
    </div>

    <!-- Seção de Notícias Relacionadas -->
    <div class="related-news mt-5">
        <h5>Notícias Relacionadas</h5>
        <div class="row">
            <?php while ($related_row = mysqli_fetch_assoc($result_related)): ?>
            <a href="ver_noticia.php?id=<?php echo $related_row['id']; ?>">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <img src="<?php echo $related_row['imagem_destaque']; ?>" class="card-img-top" alt="Imagem Relacionada">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $related_row['titulo']; ?></a></h5>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

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