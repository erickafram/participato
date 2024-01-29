<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"]) || !isset($_GET["id"])) {
    header("Location: ../login.php");
    exit();
}

$vaquinha_id = $_GET["id"];

$sql = "SELECT titulo, imagem, status, aprovacao FROM vaquinhas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vaquinha_id);
$stmt->execute();
$result = $stmt->get_result();
$vaquinha = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doar para <?php echo $vaquinha["titulo"]; ?> - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">
        function showPixPopup() {
            alert("Forma de Pagamento Selecionado");
            document.getElementById('pix').checked = true;
        }
    </script>
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5">
    <div class="row">

        <!-- Lado Esquerdo: Titulo da vaquinha e imagem -->
        <div class="col-12 col-md-4">
            <img src="images/<?php echo $vaquinha['imagem']; ?>" alt="<?php echo $vaquinha["titulo"]; ?>" class="img-fluid">
            <div style="color:#6d7279;font-weight: 400; font-size: 1rem;"><?php echo"Você está doando para a vaquinha"?></div>
            <h5><?php echo $vaquinha["titulo"]; ?></h5>
        </div>

        <!-- Lado Direito: Forma de pagamento -->
        <div class="col-12 col-md-8">
            <form method="post" action="process_donation.php" id="paymentForm">
                <div class="forma-pagamento">
                    <input type="hidden" name="vaquinha_id" value="<?php echo $vaquinha_id; ?>">
                    <div class="form-group">
                        <label for="valor">Valor da Doação (R$):</label>
                        <input type="text" class="form-control" name="valor" id="valor" required oninput="maskMoney(event)">
                    </div>
                    <div class="form-group">
                        <label for="anonimo">Doar Anonimamente:</label>
                        <input type="checkbox" name="anonimo" id="anonimo">
                    </div>
                </div>

                <div class="forma-pagamento">
                    <div class="form-group">
                        <label>Forma de Pagamento:</label>
                        <div class="mt-3" style="display: flex; align-items: center;">
                            <input type="radio" id="pix" name="formaPagamento" value="pix" style="display:none;">
                            <span class="btn btn-light" onclick="showPixPopup()" style="display: flex; flex-direction: column; align-items: center; padding: 10px 20px; border: 2px solid #00bdae;">
                                    <img src="images/icon-pix.png" alt="Ícone PIX" style="width: 20px; height: 20px;">
                                    <span>PIX</span>
                                </span>
                        </div>
                    </div>
                </div>


                <!-- Payment Modal -->
                <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="paymentModalLabel">Quase lá! Sua doação será confirmada após a transferência PIX.</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <center><p style="font-weight:bold; font-size:15px; color:#3a3a3a;">Você está doando: <span id="donationAmount"></span></p></center>
                                <div class="row">
                                    <div class="col-md-6 border-right">
                                        <!-- QR Code Column -->
                                        <h6>QR Code</h6>
                                        <p>Passos para o pagamento:</p>
                                        <ol style="margin-left: 1rem; font-size: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                                            <li>Abra o aplicativo do seu banco usando o seu celular;</li>
                                            <li>Entre na área PIX e selecione a opção de pagar com QR Code;</li>
                                            <li>Escaneie o QR Code abaixo e confirme o pagamento. O nome que vai aparecer pra você é ParticipaBR.</li>
                                            <img src="images/qrcode.jpeg" alt="Ícone PIX" style="width: 60%; height: auto; padding-top:5px;">
                                        </ol>
                                    </div>
                                    <div class="col-md-6">
                                        <!-- PIX Copia e Cola Column -->
                                        <h6>PIX Copia e Cola</h6>
                                        <div class="copy-pix">
                                            <p><label for="pixCode">Copie o código abaixo:</label></p>
                                            <p><input type="text" id="pixCode" value="00020101021126580014br.gov.bcb.pix013620682071-6569-47b4-ae5b-50033846afda520400005303986540510.005802BR5917ERICK V RODRIGUES6006PALMAS62070503***6304065D" readonly></p>
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                <button type="submit" class="btn btn-primary" onclick="prepareForSubmission()" form="paymentForm">Confirmação de PIX</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                if ($vaquinha['aprovacao'] === 'aprovado' && $vaquinha['status'] === 'aberto') {
                    echo '<button type="button" class="btn btn-primary" onclick="if(validateForm()) { $(\'#paymentModal\').modal(\'show\'); }">Doar</button>';
                } elseif ($vaquinha['status'] !== 'aberto') {
                    echo '<button type="button" class="btn btn-danger disabled">Vaquinha fechada</button>';
                } else {
                    echo '<button type="button" class="btn btn-warning disabled">Aguardando aprovação</button>';
                }

                ?>
                <a href="view_crowdfund.php?id=<?php echo $vaquinha_id; ?>" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    function prepareForSubmission() {
        var valorElement = document.getElementById("valor");
        var valor = valorElement.value;
        console.log("Valor original: ", valor); // Valor com máscara

        // Remover a máscara do valor
        valor = valor.replace("R$ ", "");
        valor = valor.replace(/\./g, ""); // Note que adicionei o modificador 'g' para remover todos os pontos
        valor = valor.replace(",", ".");
        console.log("Valor tratado: ", valor); // Valor sem máscara

        // Converte para float e de volta para string para ter duas casas decimais
        valor = parseFloat(valor).toFixed(2);
        console.log("Valor convertido para float e formatado: ", valor); // Valor como float com duas casas decimais

        // Substituir o valor do elemento de entrada pelo valor desmascarado
        valorElement.value = valor;
    }




    function maskMoney(event) {
        var value = event.target.value.replace(/\D/g, "");
        value = (value / 100).toFixed(2) + "";
        value = value.replace(".", ",");
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        event.target.value = "R$ " + value;
    }

    function copyPixCode() {
        /* Obtém o elemento de entrada de texto */
        var copyText = document.getElementById("pixCode");

        /* Seleciona o texto dentro do elemento de entrada de texto */
        copyText.select();
        copyText.setSelectionRange(0, 99999); // Para dispositivos móveis

        /* Copia o texto para a área de transferência */
        document.execCommand("copy");

        /* Alerta o usuário de que o texto foi copiado */
        alert("Código PIX copiado: " + copyText.value);
    }



    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("paymentForm").addEventListener("keydown", function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                return false;
            }
        });
    });

    function showPixPopup() {
        alert("Forma de Pagamento Selecionado");
        document.getElementById('pix').checked = true;
    }

    function validateForm() {
        var valor = document.getElementById("valor").value;
        if (valor === "" || valor == null) {
            alert("O campo 'Valor da Doação' deve ser preenchido.");
            return false;
        }
        document.getElementById("donationAmount").textContent = valor;  // Atualizar o elemento do modal com o valor da doação
        return true;
    }

    function showPixPopup() {
        alert("Forma de Pagamento Selecionado");
        document.getElementById('pix').checked = true;
    }

</script>

</body>
</html>


<?php $conn->close(); ?>
