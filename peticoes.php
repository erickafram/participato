<?php
session_start();
require_once "includes/db_connection.php";
//header("Location: manutencao.php");
if (!isset($_SESSION["user_id"])) {
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Petições | Participa Tocantins </title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "header.php"; ?>

<div class="container mt-5">
    <!-- Seção com mensagem e botão -->
    <div class="container-fluid text-center py-5">
        <h1 class="text-black" style="font-size: 2.05rem; font-weight: bold; line-height: 1.25;">A Plataforma Tocantinense Fazendo a Diferença, Juntos</h1>
        <h2 class="text-black" style="font-size: 1.35rem; line-height: 1.25;">Junte-se a uma comunidade com milhares de tocantinenses, agindo por um futuro melhor. Celebramos conquistas diárias.</h2>
        <a href="user/create_petition.php" class="btn btn-primary btn-lg mt-4">Fazer abaixo-assinado</a>
    </div>

    <!-- Slider para petições turbinadas -->
    <div id="turbinadoCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <?php
            $turbinado_query = "SELECT abaixo_assinados.*, usuarios.nome_usuario,
               (SELECT COUNT(*) FROM assinaturas WHERE id_abaixo_assinado = abaixo_assinados.id) AS assinaturas_atuais
               FROM abaixo_assinados 
               INNER JOIN usuarios ON abaixo_assinados.id_usuario = usuarios.id
               WHERE turbinado = 1 AND data_finalizacao >= CURRENT_DATE AND (status = 'aprovado' OR status = 'finalizado')";

            $turbinado_result = $conn->query($turbinado_query);
            $firstSlide = true;
            if ($turbinado_result->num_rows > 0) {
                while ($turbinado_row = $turbinado_result->fetch_assoc()) {
                    echo $firstSlide ? '<div class="carousel-item active">' : '<div class="carousel-item">';
                    $firstSlide = false;
                    echo '<div class="destaque-badge"><i class="bi bi-graph-up-arrow"></i> <span class="blink">Petição em Alta</span></div>';
                    echo '<div class="row">';
                    echo '<div class="col-md-6">';
                    echo '<a href="peticao/petition_details.php?id=' . $turbinado_row["id"] . '">';
                    echo '<img src="assets/' . $turbinado_row["caminho_imagem"] . '" class="img-fluid" alt="..." style="max-height: 363px; width: auto;">';
                    echo '</div>';
                    echo '<div class="col-md-6">';
                    echo '<div class="text-container">';
                    echo '<div class="carousel-title">' . $turbinado_row["titulo"] . '</div>';
                    echo '<div class="carousel-description">' . $turbinado_row["descricao"] . '</div>';
                    echo '</div>'; // end text-container
                    // Inicio das colunas adicionais
                    echo '<div class="row mt-3">';
                    echo '<div class="col-6">';
                    echo '<div class="author-info">';
                    echo '<p><i class="bi bi-person"></i>' . '<strong> Autor:</strong> ' . $turbinado_row["nome_usuario"] . '</p>';
                    echo '</div>'; // end author-info
                    echo '</div>'; // end col-6
                    echo '<div class="col-6">';
                    echo '<div class="supporters-info">';
                    echo '<p><i class="bi bi-people"></i>' . '<strong> Apoiadores:</strong> ' . number_format($turbinado_row["assinaturas_atuais"], 0, ',', '.') . '</p>';
                    echo '</div>'; // end supporters-info
                    echo '</div>'; // end col-6
                    echo '</div>'; // end row
                    echo '</div>'; // end col-md-6
                    echo '</div>'; // end row
                    echo '</div>'; // end carousel-item
                    echo '</a>';  // Fim da tag <a>
                }
            } else {
                echo '<div class="carousel-item">';
                echo '<p>Nenhum abaixo-assinado turbinado disponível no momento.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <h4 class="text-center">Acontecendo agora na ParticipaTO</h4>
    <div class="input-box">
        <i class="uil uil-search"></i>
        <input type="text" placeholder="Pesquisar petição..." class="form-control" id="search">
    </div>
    <!-- Checkbox para mostrar apenas petições vitoriosas -->
    <div class="filter-box">
        <input type="checkbox" id="showVictorious" name="showVictorious">
        <label for="showVictorious"> Mostrar apenas petições com meta atingida</label>
    </div>
    <div class="row" id="petition-list">
    </div>
    <!-- Botão de "Carregar Mais" -->
    <div class="row">
        <div class="col-md-12 text-center" style="margin-bottom:10px;">
            <button id="load-more-button" class="btn btn-primary btn-lg btn-block" style="font-size: 17px;">Ver Mais</button>

        </div>
    </div>
</div>
<?php include "footer.php"; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let currentPage = 1;  // Inicia na primeira página

        // Função para carregar uma página específica
        function loadPage() {
            const searchValue = $('#search').val();
            const showVictorious = $('#showVictorious').prop('checked') ? 1 : 0;

            $.ajax({
                url: 'search_petitions.php',
                method: 'POST',
                data: { page: currentPage, search: searchValue, showVictorious: showVictorious },
                success: function(response) {
                    $('#petition-list').append(response);  // Adiciona novos itens à lista existente
                    currentPage++;  // Avança para a próxima página
                }
            });
        }

        // Função para pesquisar e atualizar a lista de petições
        function searchAndUpdate() {
            const searchValue = $('#search').val();
            const showVictorious = $('#showVictorious').prop('checked') ? 1 : 0;

            $.ajax({
                url: 'search_petitions.php',
                method: 'POST',
                data: { page: 1, search: searchValue, showVictorious: showVictorious },
                success: function(response) {
                    $('#petition-list').html(response);  // Substitui a lista atual
                    currentPage = 2;  // Reinicializa a contagem de páginas
                }
            });
        }

        loadPage();

        // Manipulador de eventos para o botão "Carregar Mais"
        $('#load-more-button').click(function() {
            loadPage();
        });

        // Manipulador de eventos para o campo de pesquisa
        $('#search').on('input', searchAndUpdate);

        // Manipulador de eventos para o checkbox "Mostrar apenas petições vitoriosas"
        $('#showVictorious').on('change', searchAndUpdate);
    });
</script>
</body>
</html>
