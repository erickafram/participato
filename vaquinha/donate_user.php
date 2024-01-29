<?php
session_start();
require_once "../includes/db_connection.php";

$vaquinha_id = isset($_GET["id"]) ? $_GET["id"] : null;

// Verifique se o usuário está logado
if (isset($_SESSION["user_id"])) {
    // Se o usuário estiver logado, redirecione-o para a página de doação para usuários logados (donate.php)
    header("Location: donate.php?id=$vaquinha_id");
    exit();
}

// Se o usuário não estiver logado, continue com a página de doação para usuários não logados

// Recupere informações sobre a vaquinha (substitua pela sua lógica)
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
    <!-- Adicione seus links de estilo adicionais aqui, se necessário -->
</head>
<body>
<?php include_once "../header.php"; ?>

<div class="container mt-5" style="padding-top:30px;">
    <div class="row">
        <!-- Lado Esquerdo: Titulo da vaquinha e imagem -->
        <div class="col-12 col-md-4">
            <div class="alert alert-success" style="font-size: 12px; font-weight: bold;    margin-top: 5px;">
                Cada centavo contribui para tornar nosso mundo um lugar melhor. Obrigado por sua generosidade e apoio.
            </div>
            <img src="images/<?php echo $vaquinha['imagem']; ?>" alt="<?php echo $vaquinha["titulo"]; ?>" class="img-fluid" style="max-width: 610px; max-height: 475px;">
            <div style="color:#6d7279;font-weight: 400; font-size: 1rem;"><?php echo "Você está doando para a vaquinha" ?></div>
            <h5><?php echo $vaquinha["titulo"]; ?></h5>
            <hr style="margin-bottom: 5px;"> <!-- Adiciona espaçamento vertical -->
            <b><span id="doacaoAtual" style="color:#5f5f5f; font-size: 14px;">Valor da Doação Atual: R$0.00</span></b>
        </div>

        <!-- Lado Direito: Formulário para usuários não logados -->
        <div class="col-12 col-md-8 donate_user">
            <form method="post" action="process_donation_user.php" id="paymentForm">
                <div class="forma-pagamento">
                    <input type="hidden" name="vaquinha_id" value="<?php echo $vaquinha_id; ?>">
                    <div class="form-group valor_doacao">
                        <label for="valor">Valor da Doação (R$):</label>
                        <input type="text" class="form-control" name="valor" id="valor" required oninput="maskMoney(event)" onblur="validateForm()">
                        <div id="valorError" style="color: #b50000; font-size: 14px;"></div>
                    </div>
                    <div class="form-group">
                        <label for="nome_completo">Nome Completo:</label>
                        <input type="text" class="form-control" name="nome_completo" id="nome_completo" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col">
                            <label for="cpf">CPF:</label>
                            <input type="text" class="form-control" name="cpf" id="cpf" required oninput="maskCPF(event)">
                        </div>
                        <div class="form-group col">
                            <label for="telefone">Telefone:</label>
                            <input type="text" class="form-control" name="telefone" id="telefone" required oninput="maskTelefone(event)">
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

                </div>

                <div class="forma-pagamento">
                    <div class="form-group">
                        <label style="font-weight: 600; font-size: 15px !important; color:#171717;">Formas de pagamento</label>
                        <div class="mt-3" style="display: flex; align-items: center;">
                            <input type="radio" id="pix" name="formaPagamento" value="pix" style="display:none;">
                            <span class="btn btn-light" onclick="showPixPopup()" style="display: flex; flex-direction: column; align-items: center; padding: 5px 15px; border: 1px solid #00bdae;">
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
                                            <li>Coloque o valor que você irá doar.</li>
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
                    echo '<button type="button" class="btn btn-success btn-block" onclick="if(validateForm()) { $(\'#paymentModal\').modal(\'show\'); }">Doar</button>';
                } elseif ($vaquinha['status'] !== 'aberto') {
                    echo '<button type="button" class="btn btn-danger disabled">Vaquinha fechada</button>';
                } else {
                    echo '<button type="button" class="btn btn-warning disabled">Aguardando aprovação</button>';
                }
                ?>

                <!-- <a href="view_crowdfund.php?id=<?php echo $vaquinha_id; ?>" class="btn btn-secondary btn-lg">Voltar</a> -->
            </form>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        // Aplicar máscara de CPF
        $('#cpf').mask('000.000.000-00', { reverse: true });

        $('#telefone').mask('(00) 0000-00009', {
            onKeyPress: function(val, e, field, options) {
                field.mask(val.length > 14 ? '(00) 00000-0000' : '(00) 0000-00009', options);
            }
        });

    });

    const anonimoCheckbox = document.getElementById('anonimo');
    const anonimoLabel = document.getElementById('anonimoLabel');

    anonimoCheckbox.addEventListener('change', function() {
        if (this.checked) {
            anonimoLabel.textContent = 'Ativado';
        } else {
            anonimoLabel.textContent = 'Desativado';
        }
    });

    function maskMoney(event) {
        var value = event.target.value;
        value = value.replace(/\D/g, ""); // Remova todos os caracteres não numéricos
        value = (parseFloat(value) / 100).toFixed(2); // Divida por 100 para obter os centavos e arredonde para 2 casas decimais
        event.target.value = "R$ " + value.replace(".", ","); // Exiba o valor formatado com vírgula
    }

    function maskCPF(event) {
    }

    function maskTelefone(event) {
    }

    function validateForm() {
        var valorInput = document.getElementById("valor");
        var valor = parseFloat(valorInput.value.replace("R$ ", "").replace(",", "."));

        if (isNaN(valor) || valor < 5) {
            document.getElementById("valorError").textContent = "O valor da doação deve ser maior ou igual a R$ 5,00.";
            return false;
        } else {
            document.getElementById("valorError").textContent = "";
        }

        return true;
    }

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

    function copyPixCode() {
        var copyText = document.getElementById("pixCode");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("Código PIX copiado: " + copyText.value);
    }

    // Função para mostrar a mensagem
    function showPixPopup() {
        alert("Forma de pagamento PIX selecionado");
    }

    // Função para atualizar o valor da doação à medida que o usuário digita
    function updateDonationAmount() {
        var valorInput = document.getElementById("valor");
        var valorSpan = document.getElementById("doacaoAtual");

        var valor = valorInput.value;
        valor = valor.replace("R$ ", "").replace(",", ".");
        valor = parseFloat(valor);

        if (!isNaN(valor)) {
            valorSpan.textContent = "Valor da Doação: R$" + valor.toFixed(2);
        } else {
            valorSpan.textContent = "Valor da Doação: R$0.00";
        }
    }

    // Adicionar ouvintes de eventos para chamar a função de atualização
    var valorInput = document.getElementById("valor");
    valorInput.addEventListener("input", updateDonationAmount);

    // Chame a função de atualização uma vez para definir o valor inicial
    updateDonationAmount();

</script>
</body>
</html>
