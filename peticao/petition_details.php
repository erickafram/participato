<?php
session_start();
require_once "../includes/db_connection.php";
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <?php
    if (isset($_GET["id"])) {
        $petition_id = $_GET["id"];

        $query_petition = "SELECT titulo, descricao, caminho_imagem
                          FROM abaixo_assinados
                          WHERE id = ?";

        $stmt_petition = $conn->prepare($query_petition);
        $stmt_petition->bind_param("i", $petition_id);
        $stmt_petition->execute();
        $result_petition = $stmt_petition->get_result();

        if ($result_petition->num_rows > 0) {
            $row_petition = $result_petition->fetch_assoc();

            $image_path = '../assets/' . $row_petition["caminho_imagem"];
            ?>
            <title><?php echo $row_petition["titulo"]; ?> | Participa Tocantins</title></title>
            <!-- Open Graph meta tags -->
            <meta property="og:image" itemprop="image" content="<?php echo $image_path; ?>">
            <meta property="og:title" content="<?php echo htmlspecialchars($row_petition['titulo']); ?>">
            <meta property="og:site_name" content="Abaixo-Assinado">
            <meta property="og:url" content="https://participato.com.br/sistema/peticao/petition_details.php?id=<?php echo $petition_id; ?>">

            <!-- Twitter Card meta tags -->
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="<?php echo htmlspecialchars($row_petition['titulo']); ?>">
            <meta name="twitter:description" content="<?php echo substr(strip_tags(htmlspecialchars_decode($row_petition['descricao'])), 0, 200); ?>">

            <meta name="twitter:image" content="<?php echo $image_path; ?>">
            <?php
        }
    }
    ?>
</head>
<body>
<?php include "../header.php"; ?>
<div id="progress-bar"></div>
<div class="container mt-5">
    <?php
    require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
        $petition_id = $_GET["id"];

        $query_petition = "SELECT titulo, descricao, caminho_imagem, id_usuario, quantidade_assinaturas, link_artigos, link_grupo, criado_em, data_finalizacao, status
                      FROM abaixo_assinados
                      WHERE id = ?";
        $stmt_petition = $conn->prepare($query_petition);
        $stmt_petition->bind_param("i", $petition_id);
        $stmt_petition->execute();
        $result_petition = $stmt_petition->get_result();

        if ($result_petition->num_rows > 0) {
            $row_petition = $result_petition->fetch_assoc();
            // Consulta para obter a quantidade de assinaturas atuais para o abaixo-assinado
            $query_signature_count = "SELECT COUNT(id) AS total FROM assinaturas WHERE id_abaixo_assinado = ?";
            $stmt_signature_count = $conn->prepare($query_signature_count);
            $stmt_signature_count->bind_param("i", $petition_id);
            $stmt_signature_count->execute();
            $result_signature_count = $stmt_signature_count->get_result();
            $row_signature_count = $result_signature_count->fetch_assoc();
            $quantidade_assinaturas_atuais = $row_signature_count["total"];
            $stmt_signature_count->close();

            if ($row_petition["status"] != "pendente") {
                // Verificar se a petição expirou
                $data_vencimento = $row_petition["data_finalizacao"];
                $data_atual = date("Y-m-d");
                if ($data_vencimento < $data_atual) {
                    // A petição expirou, então exiba a mensagem
                    echo '<div class="alert alert-danger" role="alert">Petição Encerrada!</div>';
                    // INICIO: Informações sobre a meta e a quantidade atual de assinaturas
                    echo '<div class="meta-info">';
                    echo '<strong>Meta de assinaturas:</strong> ' . $row_petition["quantidade_assinaturas"];
                    echo '<p><strong>Assinaturas recebidas:</strong> ' . $quantidade_assinaturas_atuais . '</p>';  // Usando a variável que você já tem
                    echo '</div>';
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
                    echo '<p><i class="bi bi-calendar"></i>' . '<strong> Data de criação:</strong> ' . date("d/m/Y", strtotime($row_petition["criado_em"])) . '</p>';echo '</div>';

                    echo '<div class="descricao" style="padding-top: 10px;">';
                    echo $row_petition["descricao"];
                    echo '</div>';

                    // FIM PETIÇÃO ENCERRADA
                } else {

                    echo '<div class="row">';
                    echo '<div class="col-12 col-md-8">'; // Coluna da esquerda com 60%
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
                    echo '<div class="mt-3 text-center"><button id="shareFacebook" class="btn btn-primary">Facebook</button> <button id="shareTwitter" class="btn btn-info">Twitter</button> <button id="shareWhatsApp" class="btn btn-success">WhatsApp</button> </div>';
                    echo '</div>';

                    echo '<div class="descricao">';
                    echo $row_petition["descricao"];
                    echo '</div>';

                    /* echo '<div class="link-telegram">';
                    // Link para Grupo do Telegram ou WhatsApp
                    $link_grupo = $row_petition["link_grupo"];
                    if (!empty($link_grupo)) {
                        echo '<h5 class="mt-3">Link para Telegram/WhatsApp: Entre para apoiar nosso abaixo-assinado:</h5>';
                        echo '<p><a href="' . $link_grupo . '" target="_blank"><i class="bi bi-link-45deg"></i>Clique aqui para acessar o grupo</a></p>';
                    }
                    echo '</div>'; // Fecha a coluna esquerda
                    */
                    ?>

                <?php
                $query_count_updates = "SELECT COUNT(id) AS num_updates FROM atualizacoes WHERE peticao_id = ?";
                $stmt_count_updates = $conn->prepare($query_count_updates);
                $stmt_count_updates->bind_param("i", $petition_id);
                $stmt_count_updates->execute();
                $result_count_updates = $stmt_count_updates->get_result();
                $row_count_updates = $result_count_updates->fetch_assoc();
                $num_updates_total = $row_count_updates["num_updates"];
                $stmt_count_updates->close();
                ?>

                    <!-- Atualizações -->
                    <div class="atualizacoes">
                        <h5 class="mt-3">Atualizações: <span class="update-count-circle"><?php echo $num_updates_total; ?></span></h5>
                        <ul class="list-group">
                            <?php
                            $query_atualizacoes = "SELECT atualizacao, data_cadastro FROM atualizacoes WHERE peticao_id = ?";
                            $stmt_atualizacoes = $conn->prepare($query_atualizacoes);
                            $stmt_atualizacoes->bind_param("i", $petition_id);
                            $stmt_atualizacoes->execute();
                            $result_atualizacoes = $stmt_atualizacoes->get_result();

                            if ($result_atualizacoes->num_rows > 0) {
                                while ($row_atualizacao = $result_atualizacoes->fetch_assoc()) {
                                    echo '<li class="list-group-item">';
                                    echo '<strong>Data:</strong> ' . date("d/m/Y H:i:s", strtotime($row_atualizacao["data_cadastro"])) . '<br>';
                                    echo '<strong>Atualização:</strong> ' . $row_atualizacao["atualizacao"];
                                    echo '</li>';
                                }
                            } else {
                                echo '<li class="list-group-item">Nenhuma atualização disponível.</li>';
                            }

                            $stmt_atualizacoes->close();
                            ?>
                        </ul>
                    </div>

                    <!-- Artigos Relacionados -->
                        <?php
                        // Links de Artigos Relacionados
                        $links_artigos = $row_petition["link_artigos"];
                        if (!empty($links_artigos)) {
                            echo '<div class="artigos-relacionados">';
                            echo '<h5 class="mt-3">Artigos Relacionados a Esta Petição:</h5>';
                            $links_array = explode("\n", $links_artigos);
                            foreach ($links_array as $link) {
                                $link = trim($link); // Remova qualquer espaço em branco em excesso
                                if (!empty($link)) {
                                    $formatted_link = nl2br($link); // Converte quebras de linha em <br>
                                    echo '<p>' . $formatted_link . '</p>';
                                    echo '<div style="border-bottom: 1px solid #eee;"></div>'; // fim artigos-relacionados
                                }
                            }
                            echo'</div>';
                        }
                        ?>

                    <!-- FIM -->


                   <h3 style="border-top:1px solid #eee;margin-top:50px;">Comentários</h3>
                   <div style="color: #555555; font-weight: bold; font-size: 12px;">Os comentários são de responsabilidade exclusiva de seus autores e não representam a opinião deste site. Se achar algo que viole os termos de uso, denuncie. Leia as perguntas mais frequentes para saber o que é impróprio ou ilegal.</div>
                   <div class="fb-comments" style="width: 100%;" data-href="https://participato.com.br/sistema/peticao/petition_details.php?id=<?php echo $petition_id; ?>" data-width="100%" data-numposts="5"></div>
                   <!-- SDK do Facebook para JavaScript -->
                   <div id="fb-root"></div>
                   <script async defer crossorigin="anonymous" src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v10.0" nonce="YOUR_NONCE"></script>
              

                    <?php
                    //INICIO COMENTARIOS
                    echo '<div id="comentariosContainer">';
                    echo '<div class="comentarios">';
                    echo '<h5 class="mt-3">Comentários</h5>';
                    if (isset($_SESSION['user_id'])) {
                        echo'<div class="comentarios-aviso">Os comentários são de responsabilidade exclusiva de seus autores e não representam a opinião deste site. Se achar algo que viole os termos de uso, denuncie. Leia as perguntas mais frequentes para saber o que é impróprio ou ilegal.</div>';
                        echo '<form id="comment-form" action="process_comment.php" method="post">';
                        echo '<textarea name="comment_text" class="form-control" rows="3" placeholder="Deixe um comentário" required></textarea>';
                        echo '<input type="hidden" name="petition_id" value="' . $petition_id . '">';
                        echo '<button type="submit" name="submit_comment" class="btn btn-primary mt-2">Enviar Comentário</button>';
                        echo '</form>';
                        echo '<div id="comment-success" style="display: none; color: green;">Comentário feito com sucesso!</div>';
                    } else {
                        echo '<p>Para deixar um comentário, faça o <a href="login.php">login</a> ou <a href="user/register.php">cadastre-se</a>.</p>';
                    }

                    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_comment"])) {
                        if (isset($_SESSION['user_id']) && isset($_POST['comment']) && !empty(trim($_POST['comment']))) {
                            $user_id = $_SESSION['user_id'];
                            $comment = htmlspecialchars($_POST['comment']);

                            $query_insert_comment = "INSERT INTO comentarios (id_abaixo_assinado, id_usuario, comentario, data) VALUES (?, ?, ?, NOW())";
                            $stmt_insert_comment = $conn->prepare($query_insert_comment);
                            $stmt_insert_comment->bind_param("iis", $petition_id, $user_id, $comment);

                            if ($stmt_insert_comment->execute()) {
                                echo '<script>';
                                echo 'document.getElementById("popup-message").innerHTML = "Comentário enviado com sucesso!";';
                                echo 'document.getElementById("popup").style.display = "block";';
                                echo '</script>';
                            } else {
                                echo '<script>';
                                echo 'document.getElementById("popup-message").innerHTML = "Erro ao enviar comentário. Por favor, tente novamente mais tarde.";';
                                echo 'document.getElementById("popup").style.display = "block";';
                                echo '</script>';
                            }

                            $stmt_insert_comment->close();
                        }
                    }

                    // Recuperar e exibir comentários
                    $offset = 0;
                    $limit = 5;
                    $query_comments = "SELECT c.comentario, u.nome_usuario, c.data
                   FROM comentarios c
                   INNER JOIN usuarios u ON c.id_usuario = u.id
                   WHERE c.id_abaixo_assinado = ?
                   ORDER BY c.data DESC
                   LIMIT ?, ?";
                    $stmt_comments = $conn->prepare($query_comments);
                    $stmt_comments->bind_param("iii", $petition_id, $offset, $limit);
                    $stmt_comments->execute();
                    $result_comments = $stmt_comments->get_result();
                    echo '<div class="comentarios" id="comentarioContainer">';
                    if ($result_comments->num_rows > 0) {
                        while ($row_comment = $result_comments->fetch_assoc()) {
                            echo '<div class="comment">';
                            echo '<p><strong>' . $row_comment["nome_usuario"] . '</strong> (' . date("d/m/Y H:i", strtotime($row_comment["data"])) . ')</p>';
                            echo '<p>' . nl2br($row_comment["comentario"]) . '</p>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-primary" role="alert">Nenhum comentário foi feito ainda.</div>';
                    }

                    // Consulta SQL para contar o número total de comentários
                    $query_count_comments = "SELECT COUNT(*) AS total FROM comentarios WHERE id_abaixo_assinado = ?";
                    $stmt_count_comments = $conn->prepare($query_count_comments);
                    $stmt_count_comments->bind_param("i", $petition_id);
                    $stmt_count_comments->execute();
                    $result_count = $stmt_count_comments->get_result();
                    $row_count = $result_count->fetch_assoc();
                    $total_comments = $row_count['total'];

                    if ($total_comments > 5) {
                        echo '<div class="ver-mais-comentarios">';
                        echo '<button class="btn btn-primary btn-lg mt-2 text-center" id="ver-comentarios-1">Ver Mais Comentários</button>';
                        echo '</div>';
                    }

                    $stmt_comments->close();
                    echo '</div>'; // Fecha a div .comentarios
                    echo '</div>'; // Fechar Coluna Esquerda
                    echo '</div>'; // Fechar Coluna Esquerda
                    echo '</div>';


                    // INICIO COLUNA DIREITA
                    echo '<div class="col-12 col-md-4 coluna-direita">'; // Coluna da direita com 40% e sticky
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
                    $status = $row_petition["status"];
                    if ($status == 'finalizado') {
                        echo '<center><div class="alert alert-success" style="margin:5px; padding:4px"><i class="bi bi-trophy"></i> VITÓRIA!</div></center>';
                    } elseif ($quantidade_assinaturas_atuais >= $quantidade_assinaturas_necessarias) {
                        echo '<center><div class="alert alert-success" style="margin:5px; padding:4px"><i class="bi bi-flag-fill"></i> Meta Atingida!</div></center>';
                    } else {
                        echo '<center><h6 style="color:#7f8b9e;font-weight: bold;padding-top:5px;">Contribua para este propósito</h6></center>';
                    }

                    echo '<ul class="remove-bullets">'; // Adding the class to remove bullets
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
                    echo '<i class="bi bi-people"></i>'; // Bootstrap icon "person"
                    echo number_format($quantidade_assinaturas_atuais, 0, ',', '.') . ' pessoas assinaram</li>';

                    // Pega apenas a parte do dia da data de finalização e ajusta a hora para meia-noite
                    $data_finalizacao = strtotime(date("Y-m-d", strtotime($row_petition["data_finalizacao"])) . " 00:00:00");
                    $data_atual = strtotime(date("Y-m-d") . " 00:00:00");
                    $dias_restantes = floor(($data_finalizacao - $data_atual) / (60 * 60 * 24));

                    echo '<li>';
                    echo '<div class="progress">';
                    echo '<div class="progress-bar animated-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-target-width="' . $porcentagem_assinaturas . '">' . round($porcentagem_assinaturas, 2) . '%</div>';
                    echo '</div>';
                    echo '</li>';
                    echo '<li style="margin-top: 3px; display: flex; -webkit-box-pack: justify; justify-content: space-between; font-size: 14px; line-height: 22px;color: #7f8b9e;font-weight: bold;">';
                    echo 'Assinaturas Necessárias: ' . $quantidade_assinaturas_necessarias;
                    echo '</li>';

                    echo '<li style="margin-top: 0px; display: flex; -webkit-box-pack: justify; justify-content: space-between; font-size: 14px; line-height: 22px;color: #7f8b9e;font-weight: bold;">';
                    if ($dias_restantes == 0) {
                        echo '<p style="margin-bottom: 5px;">Petição encerra em: <span style="color: #c50000;font-weight: bold;">Encerra hoje</span></p>';
                    } elseif ($dias_restantes < 0) {
                        echo '<p style="margin-bottom: 5px;">Petição encerra em: <span style="color: #c50000;font-weight: bold;">Petição encerrada</span></p>';
                    } else {
                        echo '<p style="margin-bottom: 5px;">Petição encerra em: <span style="color: #c50000;font-weight: bold;">' . $dias_restantes . ' dias</span></p>';
                    }

                    echo '<li style="margin-top: 3px; display: flex; align-items: center; font-size: 12px; line-height: 20px; color: #7f8b9e; font-weight: bold;">';
                    echo '<i class="bi bi-bar-chart-fill"></i>';  // Ícone de progressão do Bootstrap
                    echo '<div class="texto";>'. 'É provável que essa petição apareça na mídia';
                    echo '</li>';
                    echo '</li>';
                    echo '</ul>';

                    // Consulta para obter a lista de pessoas que assinaram o abaixo-assinado
                    $query_signatures = "SELECT 
                        CASE 
                            WHEN a.id_usuario IS NOT NULL THEN u.nome_usuario
                            ELSE a.nome_temp
                        END AS nome_exibicao,
                        a.anonimo 
                    FROM assinaturas a
                    LEFT JOIN usuarios u ON a.id_usuario = u.id
                    WHERE a.id_abaixo_assinado = ?
                    ORDER BY a.id DESC
                    LIMIT 3"; // Limitando para mostrar apenas os 5 últimos
                    $stmt_signatures = $conn->prepare($query_signatures);
                    $stmt_signatures->bind_param("i", $petition_id);
                    $stmt_signatures->execute();
                    $result_signatures = $stmt_signatures->get_result();

                    if ($result_signatures->num_rows > 0) {
                        echo '<div class="assinaturas">';
                        echo '<h6>Últimas assinaturas</h6>';
                        echo '<ul class="remove-bullets">'; // Adicionando a classe para remover os marcadores

                        while ($row_signature = $result_signatures->fetch_assoc()) {
                            $is_anonimo = intval($row_signature['anonimo']);  // Converte o valor para inteiro
                            $nome_exibicao = $is_anonimo ? 'Anônimo' : $row_signature["nome_exibicao"];
                            $icon = '<i class="bi bi-person"></i>';  // Ícone para cada assinatura
                            echo '<li>' . $icon . ' ' . $nome_exibicao . '</li>';
                        }

                        echo '</ul>';
                    } else {
                        echo '<div class="alert alert-warning mt-3" style="font-size: 13px;">Nenhuma assinatura foi adicionada a este abaixo-assinado até o momento.</div>';;
                    }

                    // Verifica se o usuário está logado
                    if (!isset($_SESSION["user_id"])) {
                        // Verifica o status do abaixo-assinado
                        $status = $row_petition["status"];
                        if ($status == 'finalizado') {
                            echo '<center><p>Este abaixo-assinado foi vitorioso</p></center>';
                        } else {
                            // Verifica se a mensagem de sucesso deve ser exibida
                            if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1) {
                                echo '<div class="alert alert-success mt-3" id="success-alert">Assinado com sucesso!</div>';
                                echo '<div style="font-weight: bold; border:1px solid #eee; padding:5px 5px; margin-bottom:5px;"><a href="turbinar.php?id_peticao=' . $petition_id . '">Em 3 segundos, você será direcionado para potencializar esta petição e conquistar ainda mais apoiadores!</a></div>';
                                echo '<script type="text/javascript">
            setTimeout(function() {
            window.location.href = "turbinar.php?id_peticao=' . $petition_id . '";
            }, 3000);
            </script>';
                            }
                            if (isset($_GET['erro']) && $_GET['erro'] == 1) {
                                echo '<div class="alert alert-danger">Você já assinou.</div>';
                            }
                            // Usuário não está logado, mostra formulário

                            // Para usuários de celular
                            echo '<div id="mobileButton">';
                            if ($status == 'finalizado') {
                                echo '<center><p>Este abaixo-assinado foi vitorioso</p></center>';
                            } else {
                                echo '<button class="btn btn-success btn-lg btn-block mx-auto mt-3"  data-toggle="modal" data-target="#signModal" style="margin-bottom:8px;">Assine agora</button>';
                            }
                            echo '</div>';

                            // Para usuários de desktop
                            echo '<div id="desktopForm" style="display:none;">';
                            if ($status == 'finalizado') {
                                echo '<center><p>Este abaixo-assinado foi vitorioso</p></center>';
                            } else {
                                echo '<form action="salvar_assinatura.php" method="post">';
                                echo '<div class="form-group">';
                                echo '<input type="text" class="form-control" name="nome_temp" placeholder="Nome Completo" required>';
                                echo '</div>';
                                echo '<div class="form-group">';
                                echo '<input type="email" class="form-control" name="email_temp" placeholder="Email" required>';
                                echo '</div>';
                                echo '<div class="form-group">';
                                echo '<input type="text" class="form-control" name="telefone_temp" placeholder="Telefone (Opcional)">';
                                echo '</div>';
                                echo '<input type="hidden" name="id_abaixo_assinado" value="' . $petition_id . '">';
                                echo '<div class="form-check mt-2">';
                                echo '<input class="form-check-input" type="checkbox" name="anonimo" id="anonimo">';
                                echo '<label class="form-check-label" for="anonimo">Manter minha assinatura anônima</label>';
                                echo '</div>';
                                echo '<div class="form-check mt-2">';
                                echo '<input class="form-check-input" type="checkbox" name="receber_notificacoes" id="receber_notificacoes">';
                                echo '<label class="form-check-label" for="receber_notificacoes">Desejo receber notificações por e-mail sobre esta petição</label>';
                                echo '</div>';
                                echo '<input type="submit" class="btn btn-success btn-lg btn-block mx-auto mt-3"  value="Assine agora">';
                                echo '</form>';
                            }
                            echo '</div>';
                        }
                    } else {
                        echo '<div style="font-weight: bold; border:1px solid #eee; padding:5px 5px; margin-bottom:15px; font-size:13px;"><a href="turbinar.php?id_peticao=' . $petition_id . '"><center>Ajude a potencializar esta petição e conquistar ainda mais apoiadores!</center></a></div>';
                        // Botão Assinar Abaixo-Assinado centralizado vertical e horizontalmente
                        echo '<div class="botao-assinar">';
                        echo '<div class="d-flex justify-content-center align-items-center mt-3">';
                        if ($status == 'finalizado') {
                            echo '<center><p>Este abaixo-assinado foi vitorioso</p></center>';
                        } else {
                            echo '<button class="btn btn-success btn-lg btn-block mx-auto mt-3" id="btn-assinar">Assine agora</button>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                    echo '</div>'; // Fecha a coluna direita
                    echo '</div>'; // Fecha a row
                    echo '</div>';
                    echo '</div>';
                }

            } else {
                echo '<div class="alert alert-info">Esta petição está aguardando aprovação.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Abaixo-assinado não encontrado.</div>';
        }
    }
    ?>
</div> <!-- Fecha a div container -->

<div class="modal fade" id="signModal" tabindex="-1" role="dialog" aria-labelledby="signModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="signModalLabel">Assinar petição</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="salvar_assinatura.php" method="post">
                    <div class="form-group">
                        <input type="text" class="form-control" name="nome_temp" placeholder="Nome Completo" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" name="email_temp" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="telefone_temp" placeholder="Telefone (Opcional)">
                    </div>
                    <input type="hidden" name="id_abaixo_assinado" value="<?php echo $petition_id; ?>">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="anonimo" id="anonimo">
                        <label class="form-check-label" for="anonimo">Manter minha assinatura anônima</label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="receber_notificacoes" id="receber_notificacoes">
                        <label class="form-check-label" for="receber_notificacoes">Desejo receber notificações por e-mail sobre esta petição</label>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg d-block mx-auto mt-3">Assinar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h4>Outras Petições em Aberto</h4>
    <div class="row" id="other-petitions-list">
        <?php
        // Consulta para obter outras petições em aberto
        $query_other_petitions = "SELECT a.id, a.titulo, a.descricao, a.caminho_imagem, a.quantidade_assinaturas, COUNT(s.id) AS assinaturas_atuais, a.data_finalizacao, u.nome_usuario
                                  FROM abaixo_assinados a
                                  LEFT JOIN assinaturas s ON a.id = s.id_abaixo_assinado
                                  INNER JOIN usuarios u ON a.id_usuario = u.id
                                  WHERE a.status = 'aprovado' AND a.data_finalizacao >= NOW() AND a.id != ?
                                  GROUP BY a.id, a.titulo, a.descricao, a.caminho_imagem, a.quantidade_assinaturas, a.data_finalizacao, u.nome_usuario
                                  LIMIT 3"; // Você pode ajustar a quantidade de petições exibidas
        $stmt_other_petitions = $conn->prepare($query_other_petitions);
        $stmt_other_petitions->bind_param("i", $petition_id);
        $stmt_other_petitions->execute();
        $result_other_petitions = $stmt_other_petitions->get_result();

        if ($result_other_petitions->num_rows > 0) {
            while ($row_other_petition = $result_other_petitions->fetch_assoc()) {
                echo '<div class="col-12 col-md-4">';
                echo ' <div class="relacionados">';
                echo ' <div class="card mb-4">';
                // Verificar se a meta de assinaturas foi atingida
                if ($row_other_petition["assinaturas_atuais"] >= $row_other_petition["quantidade_assinaturas"]) {
                    echo '<div class="victory-badge"><i class="bi bi-flag"></i> Meta Atingida!</div>'; // Este é o novo elemento para mostrar "Vitória"
                }
                echo '<img src="../assets/' . $row_other_petition["caminho_imagem"] . '" alt="Imagem da Petição" class="card-img-top" style="width: 348px; height: 231px;">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title limited-title">' . $row_other_petition["titulo"] . '</h5>';

                // Cálculo da barra de progressão
                $porcentagem_assinaturas = ($row_other_petition["assinaturas_atuais"] / $row_other_petition["quantidade_assinaturas"]) * 100;

                echo '<div class="progress">';
                echo '<div class="progress-bar animated-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" data-target-width="' . $porcentagem_assinaturas . '">' . round($porcentagem_assinaturas, 2) . '%</div>';
                echo '</div>';

                // Ícone de usuário e nome do usuário em uma linha
                echo '<div class="sessao-assinatura">';
                echo '<p style="margin-top:5px;"><i class="bi bi-person-fill"></i> <b>' . $row_other_petition["nome_usuario"] . '</b></p>';
                // Ícone de assinatura e número de apoiadores em outra linha
                echo '<p style="margin-bottom:0px;"><i class="bi bi-pencil-fill"></i> ' . number_format($row_other_petition["assinaturas_atuais"], 0, ',', '.') . ' Apoiadores</p>';

                echo '<center><a href="petition_details.php?id=' . $row_other_petition["id"] . '" class="btn btn-primary btn-lg btn-block">Assine agora</a></center>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }

        } else {
            echo "<p>Não há outras petições em aberto no momento.</p>";
        }
        $stmt_other_petitions->close();
        ?>
    </div>
</div>

<div id="popup" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);z-index:9999;">
    <div style="margin:15% auto;width:300px;padding:20px;background-color:#fff;border-radius:10px;">
        <p id="popup-message"></p>
        <button id="closePopup">Fechar</button>
    </div>
</div>
</div>

<div style="padding-bottom:50px;"></div>
<div class="dispositivo-mobile">
<?php include "../footer.php"; ?>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>

    window.onscroll = function() { scrollFunction() };

    function scrollFunction() {
        var scrollTop = document.documentElement.scrollTop;
        var scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var scrollPercentage = (scrollTop / scrollHeight) * 100;
        document.getElementById("progress-bar").style.width = scrollPercentage + "%";
    }

    // Função para detectar se o usuário está em um dispositivo móvel
    function isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    // Mostrar ou ocultar elementos com base no dispositivo
    window.addEventListener("load", function() {
        if (isMobile()) {
            document.getElementById("mobileButton").style.display = "block";
            document.getElementById("desktopForm").style.display = "none";
        } else {
            document.getElementById("mobileButton").style.display = "none";
            document.getElementById("desktopForm").style.display = "block";
        }
    });

    $(document).ready(function() {
        $('#comment-form').submit(function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'process_comment.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === "success") {
                        $('#comment-success').show();
                        $('textarea[name="comment_text"]').val(''); // Limpa o campo de texto

                        // Cria o novo comentário e adiciona na seção de comentários
                        var newComment = `
                        <div class="comment">
                            <p><strong>${response.user_name}</strong> (${response.date})</p>
                            <p>${response.comment}</p>
                        </div>`;
                        $('#comentarioContainer').prepend(newComment);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Erro ao enviar o comentário: ' + error);
                }
            });
        });
    });

    $(document).ready(function() {
        var offset = 5; // Inicialmente carregamos 5 comentários, então começamos do sexto
        var limit = 5;  // Quantidade de comentários a serem carregados
        var petitionId = <?php echo $petition_id; ?>;  // Pegue o ID da petição do PHP

        $('#ver-comentarios-1').click(function() {
            $.ajax({
                type: 'GET',
                url: 'carregar_mais_comentarios.php',
                data: {
                    offset: offset,
                    limit: limit,
                    petition_id: petitionId
                },
                success: function(response) {
                    $('#ver-comentarios-1').before(response);  // Usa 'before' para adicionar antes do botão
                    offset += limit; // Atualiza o offset
                },
                error: function(xhr, status, error) {
                    alert('Erro ao carregar mais comentários: ' + error);
                }
            });
        });
    });

    $(document).ready(function(){
        $('input[name="telefone_temp"]').mask('(00) 0 0000-0000');
    });


    $(document).ready(function() {
        $('.animated-progress-bar').each(function() {
            var targetWidth = $(this).data('target-width');
            $(this).css('width', targetWidth + '%');
            $(this).attr('aria-valuenow', targetWidth);
        });
    });

    //COMPARTILHAMENTO PARA REDES SOCIAIS
    document.addEventListener("DOMContentLoaded", function() {
        // Mais código já existente aqui

        // Botão de compartilhamento do Facebook
        document.getElementById("shareFacebook").addEventListener("click", function() {
            const url = window.location.href;
            const title = document.querySelector('.petition-title').textContent;
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(title)}`, "_blank");
        });

        // Botão de compartilhamento do Twitter
        document.getElementById("shareTwitter").addEventListener("click", function() {
            const url = window.location.href;
            const title = document.querySelector('.petition-title').textContent;
            window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`, "_blank");
        });

        // Botão de compartilhamento do WhatsApp
        document.getElementById("shareWhatsApp").addEventListener("click", function() {
            const url = window.location.href;
            const title = document.querySelector('.petition-title').textContent;
            window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(title + " " + url)}`, "_blank");
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const btnAssinar = document.getElementById("btn-assinar");
        const popup = document.getElementById("popup");
        const popupMessage = document.getElementById("popup-message");
        const closePopup = document.getElementById("closePopup");

        if (btnAssinar) {
            btnAssinar.addEventListener("click", function() {
                console.log("Botão Assinar Abaixo-Assinado clicado");

                // Usar AJAX para assinar a petição
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "sign_petition.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        const response = JSON.parse(xhr.responseText);
                        popupMessage.textContent = response.message;
                        popup.style.display = "block";

                        // Se o usuário não estiver logado, redirecione para a página de login
                        if (response.message === 'Você precisa estar autenticado para assinar. Aguarde 5 segundos para ser redirecionado.') {
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 3000); // 3000 ms = 3 segundos
                        }
                    }
                };

                xhr.send("petition_id=" + <?php echo $petition_id; ?>);
            });
        }

        closePopup.addEventListener("click", function() {
            popup.style.display = "none";
        });
    });


</script>
</body>
</html>