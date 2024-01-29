<?php
session_start();
require_once "../includes/db_connection.php";

$id_peticao = $_GET['id_peticao'] ?? null;

// Consulta SQL para pegar os dados da petição
$query_peticao = "SELECT * FROM abaixo_assinados WHERE id = ?";
$stmt = $conn->prepare($query_peticao);
$stmt->bind_param("i", $id_peticao);
$stmt->execute();
$result_peticao = $stmt->get_result();

$dados_peticao = [];
if ($result_peticao->num_rows > 0) {
    $dados_peticao = $result_peticao->fetch_assoc();
}

// Consulta SQL para pegar os dados dos contribuintes com doações aprovadas
$query_contribuintes = "SELECT valor FROM doacoes_pix WHERE id_abaixo_assinado = ? AND status = 'aprovado'";
$stmt_contribuintes = $conn->prepare($query_contribuintes);
$stmt_contribuintes->bind_param("i", $id_peticao);
$stmt_contribuintes->execute();
$result_contribuintes = $stmt_contribuintes->get_result();


$dados_contribuintes = [];
if ($result_contribuintes->num_rows > 0) {
    while ($row = $result_contribuintes->fetch_assoc()) {
        $dados_contribuintes[] = $row;
    }
}
// Dump dos dados para depuração
//var_dump($dados_contribuintes);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Turbinar Petição</title>
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
            <title><?php echo htmlspecialchars($row_petition['titulo']); ?> | Participa Tocantins</title>
            <!-- Open Graph meta tags -->
            <meta property="og:image" itemprop="image" content="<?php echo $image_path; ?>">
            <meta property="og:title" content="<?php echo htmlspecialchars($row_petition['titulo']); ?>">
            <meta property="og:description" content="<?php echo substr(strip_tags(htmlspecialchars_decode($row_petition['descricao'])), 0, 200); ?>">
            <meta property="og:site_name" content="Abaixo-Assinado">
            <meta property="og:url" content="https://participato.com.br/sistema/peticao/turbinar.php?id=<?php echo $petition_id; ?>">

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
<div class="sessao-turbinar">
<div class="container mt-5">
    <div class="row">
        <!-- Coluna da Esquerda -->
        <div class="col-md-7 coluna-esquerda">
            <?php if (!empty($dados_peticao)): ?>
                <?php if (!empty($dados_peticao)): ?>
                    <!-- <div class="alert alert-info" role="alert"><p style="font-weight: bold; font-size: 23px;line-height: 1.3588; margin: 0.3072em 0;">Poderia considerar fazer uma doação de R$10,00 para ampliar o alcance desta causa? O tempo é um fator crucial para o êxito de qualquer petição.</p></div> -->
                    <div class="alert alert-info" role="alert"><p style="font-weight: bold; font-size: 23px;line-height: 1.3588; margin: 0.3072em 0;">Você poderia pensar em compartilhar esta petição para ajudar a espalhar a conscientização sobre essa causa? O tempo desempenha um papel fundamental no sucesso de qualquer petição.</p></div>
                    <div class="nav-divider"></div>
                    <h4><?php echo $dados_peticao['titulo']; ?></h4>
                    <img src="../assets/<?php echo $dados_peticao['caminho_imagem']; ?>" class="img-fluid" alt="...">
                <?php endif; ?>
            <?php else: ?>
                <p>Nenhuma informação disponível.</p>
            <?php endif; ?>
            <p style="margin-top:15px; font-size:19px;">Seu apoio pode acelerar o processo de atração de novos simpatizantes, fazendo toda a diferença em pouco tempo.</p>
            <p style=" font-size:19px;">Com essa ajuda, conseguimos destacar a petição tanto na interface do PetiçãoTO quanto via comunicações por e-mail.</p>
            <div class="alert alert-secondary" role="alert">
                <p style="color:#E01A2B; font-weight: bold; font-size: 14px; margin-bottom: 5px;">IMPACTO</p>
                <!-- As pessoas que promoveram com alguma quantia permitiram que esta petição recolhesse 100 assinaturas adicionais. -->
                As pessoas que contribuíram ao compartilhar esta petição nas redes sociais desempenharam um papel crucial na disseminação desta causa..
            </div>

                <center>
                    <!-- <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#donationModal-indisponivel" style="margin-bottom:15px;padding: 10px 10px 0px;font-weight: bold;font-size:17px;">
                        Sim, vou doar R$10 para ampliar <p>o alcance desta petição.</p>
                    </button><br> -->
               <button class="btn btn-warning btn-lg" data-toggle="modal" data-target="#shareModal" style="margin-bottom:25px;font-weight: bold;">Compartilhar Agora</button>
            </center>

            <!-- Modal -->
            <div class="modal fade" id="donationModal" tabindex="-1" role="dialog" aria-labelledby="donationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document"> <!-- Adicione modal-lg ou modal-xl aqui -->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="donationModalLabel">Informações para doação via Pix</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                                <center><p style="font-weight:bold; font-size:17px; color:#3a3a3a;">Você está doando: <span>R$10,00</span></p></center>
                                <div class="form-group">
                                    <label for="donorName">Nome completo:</label>
                                    <input type="text" class="form-control" id="donorName" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 border-right">
                                        <!-- QR Code Column -->
                                        <h6>QR Code</h6>
                                        <p>Passos para o pagamento:</p>
                                        <ol style="margin-left: 1rem; font-size: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                            <li>Abra o aplicativo do seu banco usando o seu celular;</li>
                                            <li>Entre na área PIX e selecione a opção de pagar com QR Code;</li>
                                            <li>Escaneie o QR Code abaixo e confirme o pagamento. O nome que vai aparecer pra você é #.</li>
                                            <img src="imagem/" alt="Ícone PIX" style="width: 60%; height: auto; padding-top:5px;">
                                        </ol>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- PIX Copia e Cola Column -->
                                        <h6>PIX Copia e Cola</h6>
                                        <div class="copy-pix">
                                            <p><label for="pixCode">Copie o código abaixo:</label></p>
                                            <p><input type="text" id="pixCode" value="INDISPONIVEL NO MOMENTO" readonly></p>
                                            <p><button type="button" class="btn btn-primary" style="margin-bottom:8px;" onclick="copyPixCode()">COPIAR CÓDIGO</button></p>
                                        </div>
                                        <ol style="margin-left: 1rem; font-size: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                            <li>Abra o aplicativo ou site do seu banco;</li>
                                            <li>Entre na área PIX e escolha a opção PIX Copia e Cola;</li>
                                            <li>Coloque o valor que você ira doar.</li>
                                            <li>Cole o código e confirme o pagamento. O nome que vai aparecer é ParticipaBR.</li>
                                        </ol>
                                        <div style="font-size: 0.875rem;color:#6d7279;">Após a confirmação do pagamento você receberá um email de confirmação</div>
                                    </div>
                                </div>
                            <!-- Fim dos novos campos -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" id="confirmDonation" class="btn btn-primary">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Compartilhar -->
        <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shareModalLabel">Compartilhe esta Petição</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="share-buttons">
                            <!-- Botão para compartilhar no Facebook -->
                            <a href="https://www.facebook.com/sharer/sharer.php?u=https://participato.com.br/sistema/peticao/turbinar.php?id_peticao=<?php echo $id_peticao; ?>" target="_blank">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <!-- Botão para compartilhar no Twitter -->
                            <a href="https://twitter.com/intent/tweet?url=https://participato.com.br/sistema/peticao/turbinar.php?id_peticao=<?php echo $id_peticao; ?>&text=<?php echo urlencode('Ajude-nos a fazer a diferença! Assine esta petição: ' . $dados_peticao['titulo']); ?>" target="_blank">
                                <i class="bi bi-twitter"></i> Twitter
                            </a>
                            <!-- Botão para compartilhar no WhatsApp -->
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode('Ajude-nos a fazer a diferença! Assine esta petição: ' . $dados_peticao['titulo'] . ' https://participato.com.br/sistema/peticao/turbinar.php?id_peticao=' . $id_peticao); ?>" target="_blank">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna da Direita -->
        <div class="col-md-4 contribuintes">

            <h4>Contribuintes:</h4>
            <ul>
                <?php if (!empty($dados_contribuintes)): ?>
                    <?php foreach ($dados_contribuintes as $contribuinte): ?>
                        <li>
                            <?php
                            // Verificar se 'donor_name' existe e não está vazio, caso contrário, usar "Uma pessoa"
                            $donorName = !empty($contribuinte['donor_name']) ? $contribuinte['donor_name'] : 'Uma pessoa';

                            echo '<i class="bi bi-person"></i>' . $donorName . ' <b>turbinou com - R$ ' . number_format($contribuinte['valor'], 2) . '</b>';
                            ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Nenhum contribuinte no momento.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
</div>
<?php include "../footer.php"; ?>

<script>

    // Compartilhar nas Redes Sociais
    document.addEventListener("DOMContentLoaded", function() {
        // Configurar os URLs para compartilhamento em redes sociais
        document.getElementById("shareToFacebook").addEventListener("click", function(e) {
            e.preventDefault();
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(location.href), 'facebook-share-dialog', 'width=626,height=436');
        });

        document.getElementById("shareToTwitter").addEventListener("click", function(e) {
            e.preventDefault();
            window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(document.title) + '&url=' + encodeURIComponent(location.href), 'twitter-share-dialog', 'width=626,height=436');
        });

        document.getElementById("shareToLinkedIn").addEventListener("click", function(e) {
            e.preventDefault();
            window.open('https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(location.href), 'linkedin-share-dialog', 'width=626,height=436');
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("confirmDonation").addEventListener("click", function() {
            var id_peticao = <?php echo json_encode($id_peticao); ?>;
            var donorName = document.getElementById("donorName").value;

            if (!donorName) {
                alert("Por favor, preencha os campos Nome.");
                return;
            }

            // Código AJAX para confirmar a doação
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "confirmar_doacao.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = xhr.responseText;
                    alert(response);

                    // Fechar o modal se a doação for confirmada
                    if (response === "Doação confirmada e aguardando aprovação.") {
                        $('#donationModal').modal('hide');
                    }
                }
            };

            xhr.send("id_peticao=" + id_peticao + "&donorName=" + donorName);
        });
    });
</script>

</body>
</html>
