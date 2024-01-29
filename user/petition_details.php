<?php
session_start();
require_once "../includes/db_connection.php";
if (!isset($_SESSION["user_id"]))
?>

<!DOCTYPE html>
<html>

<head>
    <title>Detalhes do Abaixo-Assinado</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <?php
    require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
        $petition_id = $_GET["id"];

        // Consulta para obter os detalhes do abaixo-assinado
        $query_petition = "SELECT titulo, descricao, caminho_imagem, id_usuario, quantidade_assinaturas, link_artigos, link_grupo, criado_em, data_finalizacao
                  FROM abaixo_assinados
                  WHERE id = ?";
        $stmt_petition = $conn->prepare($query_petition);
        $stmt_petition->bind_param("i", $petition_id);
        $stmt_petition->execute();
        $result_petition = $stmt_petition->get_result();

        if ($result_petition->num_rows > 0) {
            $row_petition = $result_petition->fetch_assoc();

            echo '<div class="row">';
            echo '<div class="col-12 col-md-8">'; // Coluna da esquerda com 60%
            echo '<div class="alert alert-danger" role="alert"> Visualização de Rascunho da Petição para o Usuário até aprovação </div>';
            echo '<div class="petition-title  ">' . $row_petition["titulo"] . '</div>';
            if (!empty($row_petition["caminho_imagem"])) {
                $image_path = '../assets/' . $row_petition["caminho_imagem"];
                echo '<img src="' . $image_path . '" alt="Imagem do Abaixo-Assinado" class="img-fluid mb-3">';
            }
            // Consulta para obter o nome do usuário que criou a petição
            $query_user = "SELECT nome_usuario FROM usuarios WHERE id = ?";
            $stmt_user = $conn->prepare($query_user);
            $stmt_user->bind_param("i", $row_petition["id_usuario"]);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            $row_user = $result_user->fetch_assoc();

            echo '<div class="criador">';
            echo '<p><i class="bi bi-person"></i>' . '<strong> Criado por:</strong> ' . $row_user["nome_usuario"] . '</p>';
            echo '<p><i class="bi bi-calendar"></i>' . '<strong> Data de criação:</strong> ' . date("d/m/Y", strtotime($row_petition["criado_em"])) . '</p>';
            echo '<br>';
            echo '</div>';

            echo '<div class="descricao">';
            echo $row_petition["descricao"];
            echo '</div>';

            echo '<div class="artigos-relacionados">';
            // Links de Artigos Relacionados
            $links_artigos = $row_petition["link_artigos"];
            if (!empty($links_artigos)) {
                echo '<h5 class="mt-3">Links de Artigos Relacionados:</h5>';
                echo '<ul class="remove-bullets">'; // Adicionando a classe para remover os marcadores
                $links_array = explode("\n", $links_artigos);
                foreach ($links_array as $link) {
                    echo '<li><a href="' . $link . '" target="_blank">Clique aqui para ler o artigo</a></li>';
                }
                echo '</ul>';
            }
            echo '</div>'; // fim artigos-relacionados

            echo '<div class="link-telegram">';
            // Link para Grupo do Telegram ou WhatsApp
            $link_grupo = $row_petition["link_grupo"];
            if (!empty($link_grupo)) {
                echo '<h5 class="mt-3">Link para Grupo do Telegram ou WhatsApp:</h5>';
                echo '<p><a href="' . $link_grupo . '" target="_blank">Clique aqui para acessar o grupo</a></p>';
            }
            echo '</div>'; // Fecha a coluna esquerda
            echo '</div>'; // fim artigos-relacionados




            // INICIO COLUNA DIREITA
            echo '<div class="col-12 col-md-4 coluna-direita">'; // Coluna da direita com 40% e sticky
            // Consulta para obter a quantidade de assinaturas atuais para o abaixo-assinado
            $query_signature_count = "SELECT COUNT(id) AS total FROM assinaturas WHERE id_abaixo_assinado = ?";
            $stmt_signature_count = $conn->prepare($query_signature_count);
            $stmt_signature_count->bind_param("i", $petition_id);
            $stmt_signature_count->execute();
            $result_signature_count = $stmt_signature_count->get_result();
            $row_signature_count = $result_signature_count->fetch_assoc();
            $quantidade_assinaturas_atuais = $row_signature_count["total"];
            $stmt_signature_count->close();

            $quantidade_assinaturas_necessarias = $row_petition["quantidade_assinaturas"];
            $porcentagem_assinaturas = ($quantidade_assinaturas_atuais / $quantidade_assinaturas_necessarias) * 100;

            echo '<div class="coluna-detalhes-assinatura">';
            echo '<h5>Detalhes das Assinaturas:</h5>';
            echo '<ul class="remove-bullets">'; // Adicionando a classe para remover os marcadores
            echo '<li style="box-sizing: border-box;
            margin: 15px;
            min-width: 0px;
            font-family: &quot;Noto Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, Tahoma, sans-serif;
            font-size: 14px;
            line-height: 20px;
            padding: 8px;
            background-color: rgb(242, 242, 242);
            display: flex;
            flex-direction: row;
            -webkit-box-pack: center;
            justify-content: center;
            -webkit-box-align: center;
            align-items: center;
            gap: 8px;
            border-radius: 18px;">';
            echo '<i class="bi bi-people"></i>'; // Ícone Bootstrap "person"
            echo $quantidade_assinaturas_atuais . ' pessoas assinaram</li>';

            // Calcula a diferença em dias entre a data de finalização e a data atual
            $data_finalizacao = strtotime($row_petition["data_finalizacao"]);
            $data_atual = time();
            $dias_restantes = floor(($data_finalizacao - $data_atual) / (60 * 60 * 24));

            echo '<li>';
            echo '<div class="progress">';
            echo '<div class="progress-bar" role="progressbar" style="width: ' . $porcentagem_assinaturas . '%;" aria-valuenow="' . $porcentagem_assinaturas . '" aria-valuemin="0" aria-valuemax="100">' . round($porcentagem_assinaturas, 2) . '%</div>';
            echo '</div>';
            echo '</li>';
            echo '<li style="margin-top: 3px; display: flex; -webkit-box-pack: justify; justify-content: space-between; font-size: 14px; line-height: 22px;color: #7f8b9e;font-weight: bold;">';
            echo 'Assinaturas Necessárias: ' . $quantidade_assinaturas_necessarias;
            echo '</li>';
            echo '<li style="margin-top: 0px; display: flex; -webkit-box-pack: justify; justify-content: space-between; font-size: 14px; line-height: 22px;color: #7f8b9e;font-weight: bold;">';
            echo '<p style="margin-bottom: 5px;">Petição encerra em: <span style="color: #c50000;font-weight: bold;">' . $dias_restantes . ' dias</span></p>';
            echo '</li>';
            echo '</ul>';

            // Botão Assinar Abaixo-Assinado centralizado vertical e horizontalmente
            echo '<div class="botao-assinar">';
            echo '<div class="d-flex justify-content-center align-items-center mt-3">';
            echo '</div>';
            echo '</div>'; // Fecha o div do sticky


            // Consulta para obter a lista de pessoas que assinaram o abaixo-assinado
            $query_signatures = "SELECT u.nome_usuario
                     FROM assinaturas a
                     INNER JOIN usuarios u ON a.id_usuario = u.id
                     WHERE a.id_abaixo_assinado = ?
                     ORDER BY a.id DESC
                     LIMIT 5"; // Limitando para mostrar apenas os 5 últimos
            $stmt_signatures = $conn->prepare($query_signatures);
            $stmt_signatures->bind_param("i", $petition_id);
            $stmt_signatures->execute();
            $result_signatures = $stmt_signatures->get_result();

            if ($result_signatures->num_rows > 0) {
                echo '<div class="assinaturas">';
                echo '<h6>Últimas assinaturas</h6>';
                echo '<ul class="remove-bullets">'; // Adicionando a classe para remover os marcadores

                while ($row_signature = $result_signatures->fetch_assoc()) {
                    $nome_usuario = $row_signature["nome_usuario"];
                    $icon = '<i class="bi bi-person"></i>'; // Ícone para cada assinatura

                    echo '<li>' . $icon . ' ' . $nome_usuario . '</li>';
                }

                echo '</ul>';
            } else {
                echo '<p>Nenhuma assinatura foi adicionada a este abaixo-assinado até o momento. Seja o pioneiro em assinar e apoie esta causa.</p>';
            }

            echo '</div>'; // Fecha a coluna direita
            echo '</div>'; // Fecha a row
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-danger">Abaixo-assinado não encontrados.</div>';
        }
    }
    ?>
</div> <!-- Fecha a div container -->

</div>

<div id="popup" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);z-index:9999;">
    <div style="margin:15% auto;width:300px;padding:20px;background-color:#fff;border-radius:10px;">
        <p id="popup-message"></p>
        <button id="closePopup">Fechar</button>
    </div>
</div>

<div style="padding-bottom:30px;"></div>
<?php include "../footer.php"; ?>

</body>

</html>