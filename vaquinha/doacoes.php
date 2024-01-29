<?php
session_start();
require_once "../includes/db_connection.php";
include "phpqrcode/qrlib.php";
include "funcoes_pix.php";
$vaquinha_id = isset($_GET["id"]) ? $_GET["id"] : null;

// Verifique se o usuário está logado
// DESATIVADO E CRIAR PARA USUARIO LOGADO GERAR QRCODE E SALVAR DADOS NO BANCO DE DADOS E NA LISTA DE DOACOES PARA USUARIOS LOGADO
/* if (isset($_SESSION["user_id"])) {
    // Se o usuário estiver logado, redirecione-o para a página de doação para usuários logados (donate.php)
    header("Location: donate.php?id=$vaquinha_id");
    exit();
}
*/

// Recupere informações sobre a vaquinha (substitua pela sua lógica)
$sql = "SELECT titulo, imagem, status, aprovacao FROM vaquinhas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $vaquinha_id);
$stmt->execute();
$result = $stmt->get_result();
$vaquinha = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doar para <?php echo $vaquinha["titulo"]; ?> - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5 ver_noticia doacoes" style="padding-top:20px;">
    <div style="color:#6d7279;font-weight: 400; font-size: 1rem;"><center><?php echo "Você está doando para a vaquinha" ?></center></div>
    <h5 style="color:#323232 !important;font-weight: bold;font-size: 30px; text-align: center;"><?php echo $vaquinha["titulo"]; ?></h5>

    <form id="gerarQRCodeForm" class="needs-validation" novalidate>
        <input type="hidden" name="vaquinha_id" value="<?= $vaquinha_id ?>">

        <div class="form-group" style="padding: 5px 0 5px;">
            <label for="valor_pix" style="font-weight: bold; color: rgb(64, 64, 64);">Valor da contribuição:</label>
            <input type="text" class="form-control" id="valor_pix" name="valor_pix" required>
            <span class="text-danger" id="valorPixError"></span>
            <small class="text-danger" id="valorPixError"></small> <!-- Elemento para exibir a mensagem de erro -->
        </div>

        <hr style="margin-bottom: 5px;">

        <div class="form-group">
            <label for="nome_completo" style="font-weight: bold; color: rgb(64, 64, 64);">Nome Completo:</label>
            <input type="text" class="form-control" id="nome_completo" name="nome_completo" required>
            <span class="text-danger" id="nomeCompletoError"></span>
        </div>
        <div class="form-group">
            <label for="email" style="font-weight: bold; color: rgb(64, 64, 64);">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <span class="text-danger" id="emailError"></span>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cpf" style="font-weight: bold; color: rgb(64, 64, 64);">CPF:</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" required>
                    <span class="text-danger" id="cpfError"></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="telefone" style="font-weight: bold; color: rgb(64, 64, 64);">Telefone Celular (WhatsApp):</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" required>
                    <span class="text-danger" id="telefoneError"></span>
                </div>
            </div>
        </div>

        <div class="form-group row" style="background-color: #f9f9f9; border-radius: 5px;">
            <label class="col-sm-6 col-form-label" for="anonimo">Fazer doação anônima(Ocultar meu nome)</label>
            <div class="col-sm-6">
                <div class="custom-control custom-switch" style="padding-top:4px;">
                    <input type="checkbox" class="custom-control-input" id="anonimo" name="anonimo">
                    <label class="custom-control-label" for="anonimo" id="anonimoLabel">Desativado</label>
                </div>
            </div>
        </div>

        <div class="forma-pagamento">
            <div class="form-group">
                <label style="font-weight: 600; font-size: 15px !important; color:#171717;">Escolha como doar</label>
                <div class="mt-3" style="display: flex; align-items: center;">
                    <div class="custom-control custom-radio" style="margin-right: 15px;">
                        <input type="radio" id="cartao_credito" name="formaPagamento" value="cartao_credito" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="cartao_credito">Cartão de Crédito</label>
                    </div>
                    <div class="custom-control custom-radio" style="margin-right: 15px;">
                        <input type="radio" id="pix" name="formaPagamento" value="pix" class="custom-control-input" checked>
                        <label class="custom-control-label" for="pix">PIX</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="boleto" name="formaPagamento" value="boleto" class="custom-control-input" disabled>
                        <label class="custom-control-label" for="boleto">Boleto</label>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-success btn-block">Contribuir</button>
        <div style="padding-bottom:30px;"></div>
    </form>
</div>
</div>
<!-- Modal -->
<div class="modal fade" id="qrcodeModal" tabindex="-1" role="dialog" aria-labelledby="qrcodeModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Efetue o pagamento para confirmar a contribuição.</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <center><p style="font-weight:bold; font-size:18px; color:#3a3a3a;">Você está doando: <span id="donationAmount"></span></p></center>
                <div class="row">
                    <div class="col-md-6 border-right qrcode">
                        <!-- QR Code Column -->
                        <h6>QR Code</h6>
                        <p>Passos para o pagamento:</p>
                        <ol style="margin-left: 1rem; font-size: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                            <li>Abra o aplicativo do seu banco usando o seu celular;</li>
                            <li>Entre na área PIX e selecione a opção de pagar com QR Code;</li>
                            <li>Escaneie o QR Code abaixo e confirme o pagamento. O nome que vai aparecer pra você é Renato Gomes de Aguiar junior.</li>
                            <div id="qrcode-container"></div>
                        </ol>
                    </div>
                    <div class="col-md-6 pixcopia">
                        <!-- PIX Copia e Cola Column -->
                        <center><h6>PIX Copia e Cola</h6></center>
                        <div class="copy-pix text-center">
                            <p><label for="pixCode">Copie o código abaixo:</label></p>
                            <p><input type="text" id="pixCode" value="" readonly></p>
                            <p><button type="button" class="btn btn-primary" style="margin-bottom:8px;" onclick="copyPixCode()">COPIAR CÓDIGO</button></p>
                        </div>
                        <ol style="margin-left: 1rem; font-size: 1rem; display: flex; flex-direction: column; gap: 0.75rem;">
                            <li>Abra o aplicativo ou site do seu banco;</li>
                            <li>Entre na área PIX e escolha a opção PIX Copia e Cola;</li>
                            <li>Coloque o valor que você irá doar.</li>
                            <li>Cole o código e confirme o pagamento. O nome que vai aparecer é Renato Gomes de Aguiar junior.</li>
                        </ol>
                        <div style="font-size: 0.875rem;color:#6d7279;">Após realizar o Pix para a doação, o pagamento será processado, o que pode demorar até 2 horas para ser contabilizado, dependendo do banco. Assim que a transação for confirmada e o pagamento for efetivado, o valor da doação será contabilizado na vaquinha.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="salvarDoacao" class="btn btn-success" style="display: none;">Confirmar Doação</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
<script>
    document.getElementById("salvarDoacao").addEventListener("click", function() {
        // Redireciona para o arquivo doacao_sucesso.php após o clique no botão
        window.location.href = "doacao_sucesso.php";
    });
    function copyPixCode() {
        var copyText = document.getElementById("pixCode");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // Para dispositivos móveis
        document.execCommand("copy");
        alert("Código copiado: " + copyText.value); // Opcional: Exibir um alerta confirmando a cópia
    }

    $(document).ready(function () {
        // Máscara para o Valor do Pix
        $("#valor_pix").inputmask("currency", {
            radixPoint: ',',
            groupSeparator: '.',
            autoGroup: true,
            digits: 2,
            prefix: 'R$ ',
            rightAlign: false,
            autoUnmask: true,
            numericInput: true
        });

        // Máscara para o Telefone
        $("#telefone").inputmask("(99) 99999-9999");

        // Máscara para o CPF
        $("#cpf").inputmask("999.999.999-99", { "placeholder": " " });
    });

    $(document).ready(function () {
        const anonimoCheckbox = document.getElementById('anonimo');
        const anonimoLabel = document.getElementById('anonimoLabel');
        const anonimoHiddenInput = $("input[name='anonimo']");

        anonimoCheckbox.addEventListener('change', function () {
            if (this.checked) {
                anonimoLabel.textContent = 'Ativado';
                console.log("anonimo: 1");
                anonimoHiddenInput.val(1); // Defina o valor do campo oculto para 1
            } else {
                anonimoLabel.textContent = 'Desativado';
                console.log("anonimo: 0");
                anonimoHiddenInput.val(0); // Defina o valor do campo oculto para 0
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $("#saveForm").submit(function() {
            // Atualiza o valor do campo oculto com base no estado do checkbox
            $("#anonimo_hidden").val($("#anonimo").is(":checked") ? 1 : 0);
        });
    });

    $(document).ready(function () {
        $("#gerarQRCodeForm").submit(function (event) {
            event.preventDefault();
            var isValid = true;

            // Validar valor da doação
            var valorPix = $("#valor_pix").val().replace('R$ ', '').replace('.', '').replace(',', '.');
            if (parseFloat(valorPix) < 5.0) {
                $("#valorPixError").text("Valor da contribuição deve ser no mínimo R$ 5,00");
                isValid = false;
            } else {
                $("#valorPixError").text("");
            }

            // Validar Nome Completo
            if ($("#nome_completo").val().trim() === '') {
                $("#nomeCompletoError").text("Nome completo é obrigatório");
                isValid = false;
            } else {
                $("#nomeCompletoError").text("");
            }

            // Validar Email
            if ($("#email").val().trim() === '') {
                $("#emailError").text("E-mail é obrigatório");
                isValid = false;
            } else {
                $("#emailError").text("");
            }

            // Validar CPF
            if ($("#cpf").val().trim() === '') {
                $("#cpfError").text("CPF é obrigatório");
                isValid = false;
            } else {
                $("#cpfError").text("");
            }

            // Validar Telefone
            if ($("#telefone").val().trim() === '') {
                $("#telefoneError").text("Telefone Celular (WhatsApp) é obrigatório");
                isValid = false;
            } else {
                $("#telefoneError").text("");
            }

            if (isValid) {
                var formData = $(this).serialize();

                // Salva a doação
                $.ajax({
                    type: "POST",
                    url: "save_donation.php",
                    data: formData,
                    success: function () {
                        // Gera o QR Code
                        $.ajax({
                            type: "POST",
                            url: "qrcode_pix.php",
                            data: formData,
                            success: function (response) {
                                $("#qrcode-container").html(response);
                                $("#qrcodeModal").modal("show");
                            },
                            error: function () {
                                alert("Erro ao gerar o QRCode.");
                            }
                        });
                    },
                    error: function () {
                        alert("Erro ao salvar a doação.");
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Atualiza o valor da doação no modal quando o valor no campo de doação é alterado
        $("#valor_pix").on("change", function () {
            var valorDoacao = $(this).val();
            $("#donationAmount").text(valorDoacao);
        });
    });

    $(document).ready(function () {
        // Manipulador de evento para quando o modal é fechado
        $('#qrcodeModal').on('hidden.bs.modal', function () {
            // Limpa o formulário
            $('#gerarQRCodeForm').trigger("reset");
        });
    });


    $(document).ready(function () {
        // Adicione o evento blur para o campo "valor_pix"
        $("#valor_pix").on("blur", function () {
            var valorPix = $(this).val().replace('R$ ', '').replace('.', '').replace(',', '.');

            if (parseFloat(valorPix) < 5.0) {
                $("#valorPixError").text("O valor da contribuição deve ser no mínimo R$5,00.");
            } else {
                $("#valorPixError").text(""); // Limpa a mensagem de erro
            }
        });
    });
</script>
</body>
</html>