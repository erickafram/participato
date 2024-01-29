<?php
session_start();
require_once "../includes/db_connection.php";

$id_vaquinha = $_GET["id"] ?? null;

if ($id_vaquinha === null) {
    header("Location: index.php");
    exit();
}
$sql_vaquinha = "SELECT * FROM vaquinhas WHERE id = ?";
$stmt_vaquinha = $conn->prepare($sql_vaquinha);
$stmt_vaquinha->bind_param("i", $id_vaquinha);
$stmt_vaquinha->execute();
$result_vaquinha = $stmt_vaquinha->get_result();
$vaquinha = $result_vaquinha->fetch_assoc();
$status = ($vaquinha["arrecadado"] >= $vaquinha["meta"]) ? "Alcançada" : "Não Alcançada";
$sql_doadores = "SELECT * FROM doacoes WHERE id_vaquinha = ? AND status = 'Aprovado' ORDER BY data DESC LIMIT 5";
$stmt_doadores = $conn->prepare($sql_doadores);
$stmt_doadores->bind_param("i", $id_vaquinha);
$stmt_doadores->execute();
$result_doadores = $stmt_doadores->get_result();

// Consulta para obter a soma das doações com status 'Aprovado' para esta vaquinha
$sql_soma_doacoes = "SELECT SUM(valor) AS total_arrecadado FROM doacoes WHERE id_vaquinha = ? AND status = 'Aprovado'";
$stmt_soma_doacoes = $conn->prepare($sql_soma_doacoes);
$stmt_soma_doacoes->bind_param("i", $id_vaquinha);
$stmt_soma_doacoes->execute();
$result_soma_doacoes = $stmt_soma_doacoes->get_result();
$soma_doacoes = $result_soma_doacoes->fetch_assoc();
$total_arrecadado = $soma_doacoes["total_arrecadado"] ?? 0;
$stmt_soma_doacoes->close();

$sql_soma_arrecadado = "SELECT SUM(valor) AS soma_arrecadado FROM doacoes WHERE id_vaquinha = ? AND status = 'Aprovado'";
$stmt_soma_arrecadado = $conn->prepare($sql_soma_arrecadado);
$stmt_soma_arrecadado->bind_param("i", $id_vaquinha);
$stmt_soma_arrecadado->execute();
$result_soma_arrecadado = $stmt_soma_arrecadado->get_result();
$soma_arrecadado = $result_soma_arrecadado->fetch_assoc();
$valor_arrecadado = $soma_arrecadado['soma_arrecadado'] ?? 0; // Use 0 se não houver valores.


// Consulta para obter o nome e a imagem do usuário que criou a vaquinha
$sql_usuario = "SELECT nome_usuario, imagem_perfil FROM usuarios WHERE id = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $vaquinha["id_usuario"]);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();
$stmt_usuario->close();

?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo $vaquinha["titulo"]; ?></title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- Other scripts if needed -->
        <!-- Open Graph Meta Tags -->
        <meta property="og:title" content="<?php echo $vaquinha['titulo']; ?>" />
        <meta property="og:image" content="https://participato.com.br/sistema/vaquinha/images/<?php echo $vaquinha['imagem']; ?>" />
        <meta property="og:url" content="https://participato.com.br/sistema/vaquinhas/view_crowdfund.php?id=<?php echo $id_vaquinha; ?>" />
        <meta property="og:site_name" content="Participato" />
    </head>
    <body>
    <?php include_once "../header.php"; ?>

    <div class="container mt-5" style="padding-top:20px;">
        <?php if ($vaquinha["aprovacao"] !== "pendente"): ?>
        <div class="row">
            <div class="col-12 col-md-8">
                <div class="petition-title"><?php echo $vaquinha["titulo"]; ?> </div>
                <?php if ($vaquinha["imagem"]): ?>
                    <img src="images/<?php echo $vaquinha["imagem"]; ?>" alt="Imagem da Vaquinha" style="max-width: 100%;">
                <?php endif; ?>
                <div class="criador-vaquinha">
                    <p><strong style="font-size: 12px;"> Postado em: <?php echo date('d/m/Y H:i', strtotime($vaquinha["criado_em"])); ?></strong> </p>
                    <p>
                        <?php if (!empty($usuario["imagem_perfil"])): ?>
                            <img src="/sistema/user/profile/<?php echo htmlspecialchars($usuario["imagem_perfil"]); ?>" alt="Imagem do Usuário" style="width:20px;height:20px; object-fit: cover;">
                        <?php else: ?>
                            <img src="/sistema/user/profile/avatar.png" alt="Avatar Padrão" style="width:20px;height:20px; object-fit: cover;">
                        <?php endif; ?>
                        <strong><?php echo htmlspecialchars($usuario["nome_usuario"]); ?></strong>
                    </p>
                </div>
                <div class="mt-3 text-center"><button id="shareFacebook" class="btn btn-primary">Facebook</button> <button id="shareTwitter" class="btn btn-info">Twitter</button> <button id="shareWhatsApp" class="btn btn-success">WhatsApp</button></div>
                <div class="descricao">
                    <p><?php echo $vaquinha["descricao"]; ?></p>
                </div>
            </div>
            <div class="col-12 col-md-4 coluna-direita" style="padding-top:85px;">
                <div class="coluna-detalhes-assinatura">
                    <?php
                    if ($valor_arrecadado >= $vaquinha["meta"]) {
                        echo '<center><p style="background-color: green; color: white; padding: 1px; border-radius: 5px;margin-bottom: 5px;">Meta Atingida</p></center>';
                    }
                    ?>
                    <div class="arrecadado">Arrecadado <p>R$ <?php echo number_format($valor_arrecadado, 2, ',', '.'); ?></p></div>
                    <div class="meta">
                        <p>De: R$<?php echo number_format($vaquinha["meta"], 2, ',', '.'); ?></p>
                    </div>
                    <div class="progress">
                        <?php
                        $percent = ($valor_arrecadado / $vaquinha["meta"]) * 100;
                        ?>
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percent; ?>%;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2); ?>%</div>
                    </div>
                    <div class="meta">
                        <a href="#" class="apoiadores-link" data-toggle="modal" data-target="#modalDoadores">
                            <p style="box-sizing: border-box; margin: 15px; min-width: 0px; font-family: 'Noto Sans', 'Helvetica Neue',
                            Helvetica, Arial, Tahoma, sans-serif; font-size: 14px; line-height: 20px; padding: 8px; background-color:
                            rgb(242, 242, 242); display: flex; flex-direction: row; -webkit-box-pack: center; justify-content: center;
                            -webkit-box-align: center; align-items: center; gap: 8px; border-radius: 18px;">Quantidade de Apoiadores:
                                <?php echo $result_doadores->num_rows; ?></p>
                        </a>
                    </div>
                    <?php if ($vaquinha["status"] === "aberto"): ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="doacoes.php?id=<?php echo $id_vaquinha; ?>" class="btn btn-success btn-lg d-block mx-auto mt-3 btn-fill btn-tremble btn-glow">Doar</a>
                            <!-- DESATIVADO ATÉ CRIAR PARA USUARIOS LOGADO DOAÇÃO PARA GERAR QRCODE E SALVAR NO BANCO
                            <a href="donate.php?id=<?php echo $id_vaquinha; ?>" class="btn btn-success btn-lg d-block mx-auto mt-3 btn-fill btn-tremble btn-glow"style="margin-bottom:15px;">Doar</a>
                            -->
                        <?php else: ?>
                            <a href="doacoes.php?id=<?php echo $id_vaquinha; ?>" class="btn btn-success btn-lg d-block mx-auto mt-3 btn-fill btn-tremble btn-glow">Doar</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="btn btn-danger btn-lg d-block mx-auto mt-3 btn-fill btn-tremble btn-glow disabled">Vaquinha Finalizada</span>
                    <?php endif; ?>
                    <div class="ultimos-doacao">
                        <h4>Últimas Doações</h4>
                        <ul class="remove-bullets">
                            <?php
                            $sql_ultimas_doacoes = "SELECT d.*, IFNULL(u.nome_usuario, d.nome_completo) as nome_doacao FROM doacoes d
                        LEFT JOIN usuarios u ON d.id_doador = u.id
                        WHERE d.id_vaquinha = ? AND d.status = 'Aprovado'
                        ORDER BY d.data DESC LIMIT 5";
                            $stmt_ultimas_doacoes = $conn->prepare($sql_ultimas_doacoes);
                            $stmt_ultimas_doacoes->bind_param("i", $id_vaquinha);
                            $stmt_ultimas_doacoes->execute();
                            $result_ultimas_doacoes = $stmt_ultimas_doacoes->get_result();

                            while ($doador = $result_ultimas_doacoes->fetch_assoc()):
                                if ($doador["anonimo"] === 0) {
                                    $nome_usuario = $doador["nome_doacao"] ?? "Nome não encontrado";
                                } else {
                                    $nome_usuario = "Anônimo";
                                }
                                ?>
                                <li><i class="bi bi-person"></i> <?php echo $nome_usuario; ?> - R$<?php echo number_format($doador["valor"], 2, ',', '.'); ?></li>
                            <?php endwhile; ?>

                        </ul>
                    </div>
                    <div class="btn-doadores"><a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalDoadores">Ver todos os Apoiadores</a></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalDoadores" tabindex="-1" role="dialog" aria-labelledby="modalDoadoresLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalApoiadoresLabel">Apoiadores da Vaquinha</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ultimos-doacoes">
                        <ul class="remove-bullets">
                            <?php
                            $sql_ultimas_doacoes = "SELECT d.*, IFNULL(u.nome_usuario, d.nome_completo) as nome_doacao FROM doacoes d
                        LEFT JOIN usuarios u ON d.id_doador = u.id
                        WHERE d.id_vaquinha = ? AND d.status = 'Aprovado'
                        ORDER BY d.data DESC LIMIT 5";

                            $stmt_ultimas_doacoes = $conn->prepare($sql_ultimas_doacoes);
                            $stmt_ultimas_doacoes->bind_param("i", $id_vaquinha);
                            $stmt_ultimas_doacoes->execute();
                            $result_ultimas_doacoes = $stmt_ultimas_doacoes->get_result();

                            while ($doador = $result_ultimas_doacoes->fetch_assoc()):
                                if ($doador["anonimo"] === 0) {
                                    $nome_usuario = $doador["nome_doacao"] ?? "Nome não encontrado";
                                } else {
                                    $nome_usuario = "Anônimo";
                                }
                                ?>
                                <li><i class="bi bi-person"></i> <?php echo $nome_usuario; ?> - R$<?php echo number_format($doador["valor"], 2, ',', '.'); ?></li>
                            <?php endwhile; ?>
                        </ul>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>

        <?php else: ?>
            <div class="aguardando-aprovacao">
                <div class="alert alert-danger" role="alert"> Aguardando aprovação do administrador </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="container mt-5">
        <h3>Vaquinhas relacionadas</h3>
        <div class="row">
            <?php
            $sql_outras_vaquinhas = "SELECT * FROM vaquinhas WHERE status = 'aberto' AND aprovacao = 'aprovado' AND id <> ? ORDER BY criado_em DESC LIMIT 3";
            $stmt_outras_vaquinhas = $conn->prepare($sql_outras_vaquinhas);
            $stmt_outras_vaquinhas->bind_param("i", $id_vaquinha);
            $stmt_outras_vaquinhas->execute();
            $result_outras_vaquinhas = $stmt_outras_vaquinhas->get_result();

            while ($outra_vaquinha = $result_outras_vaquinhas->fetch_assoc()):
                // Consulta para obter a soma dos valores doados com status "Aprovado" para esta vaquinha
                $sql_soma_arrecadado = "SELECT SUM(valor) AS soma_arrecadado FROM doacoes WHERE id_vaquinha = ? AND status = 'Aprovado'";
                $stmt_soma_arrecadado = $conn->prepare($sql_soma_arrecadado);
                $stmt_soma_arrecadado->bind_param("i", $outra_vaquinha['id']);
                $stmt_soma_arrecadado->execute();
                $result_soma_arrecadado = $stmt_soma_arrecadado->get_result();
                $soma_arrecadado = $result_soma_arrecadado->fetch_assoc();
                $valor_arrecadado = $soma_arrecadado['soma_arrecadado'] ?? 0;

                // Cálculo da porcentagem arrecadada em relação à meta
                $percent = ($valor_arrecadado / $outra_vaquinha["meta"]) * 100;
                ?>
                <div class="col-12 col-md-4">
                    <div class="relacionados">
                        <div class="card mb-4">
                            <img src="images/<?php echo $outra_vaquinha["imagem"]; ?>" class="card-img-top" alt="Imagem da Vaquinha">
                            <div class="card-body">
                                <h5 class="card-title limited-title"><?php echo $outra_vaquinha["titulo"]; ?></h5>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $percent; ?>%;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo number_format($percent, 2); ?>%</div>
                                </div>
                                <p class="card-text">Arrecadado: R$<?php echo number_format($valor_arrecadado, 2, ',', '.'); ?></p>
                                <p class="card-text">Meta: R$<?php echo number_format($outra_vaquinha["meta"], 2, ',', '.'); ?></p>
                                <center><a href="view_crowdfund.php?id=<?php echo $outra_vaquinha["id"]; ?>" class="btn btn-primary">Ajude agora</a></center>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <div style="padding-bottom:50px;"></div>
    <div class="dispositivo-mobile">
        <?php include "../footer.php"; ?>
    </div>
   <script>
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
   </script>
    </body>
    </html>
<?php
$stmt_vaquinha->close();
$stmt_doadores->close();
$conn->close();
?>