<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Criar Vaquinha - Minha Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>
<div class="container mt-5">
    <h4>Criar uma nova Vaquinha</h4>
    <div id="progressbar">
        <div class="progress" style="height: 30px;">
            <div class="progress-bar" role="progressbar" style="width: 16.66%;" aria-valuenow="16.66" aria-valuemin="0" aria-valuemax="100">16.66%</div>
        </div>
    </div>
    <hr>
    <form action="process_create_crowdfund.php" method="post" enctype="multipart/form-data">
    <div id="step0">
        <h5>Termos e Condições da Plataforma de Vaquinha</h5>
        <p style="font-size: 14px;">Bem-vindo à Plataforma de Vaquinha, operada pela Participa Tocantins. Leia cuidadosamente estes termos antes de usar nossos serviços, pois eles definem as regras para criar e participar de campanhas de arrecadação de fundos na plataforma.</p>
        <!-- Adicione um div com a propriedade overflow para exibir os termos e condições -->
        <div style="max-height: 300px; overflow: auto; border: 2px solid #eee; padding:10px; border-radius: 5px;">
            É importante destacar que a taxa administrativa de 15%, que é cobrada sobre o valor arrecadado e está prevista contratualmente em cada campanha, desempenha um papel fundamental em nossa sustentabilidade como empresa. Essa taxa é essencial para cobrir despesas como a remuneração de nossos colaboradores, prestadores de serviços, tecnologia, impostos, meios de pagamento e manutenção da plataforma. Tudo isso é feito com o objetivo de garantir a melhor experiência para os doadores em um ambiente fácil e seguro, bem como garantir a máxima eficácia na arrecadação para os beneficiários. Além disso, ela também assegura a veracidade e idoneidade de todas as nossas campanhas.
            <p></p>
            Fica estritamente proibido que os usuários beneficiários recebam diretamente fundos na conta bancária pessoal após a vaquinha ser disponibilizada na Plataforma. Qualquer tentativa de receber fundos fora da Plataforma é considerada uma violação destes Termos e Condições. Caso um usuário tenha recebido fundos diretamente em sua conta bancária, é obrigatório que o usuário informe a Participa Tocantins imediatamente. A Plataforma não se responsabiliza por transações fora do seu ambiente.
            <p></p>
            <b>Considerações Finais</b>
            <p></p>
            Estes Termos e Condições são uma parte essencial da utilização da Plataforma de Vaquinha da Participa Tocantins. A não conformidade com estes termos pode resultar em ações adequadas, incluindo a suspensão ou encerramento da sua conta. Ao criar ou participar de uma campanha de arrecadação de fundos na Plataforma, você concorda com estes termos e compromete-se a seguir todas as diretrizes estabelecidas. A Participa Tocantins reserva-se o direito de alterar ou atualizar estes Termos e Condições a qualquer momento, e é de sua responsabilidade verificar regularmente as atualizações. Se você não concordar com estes termos, por favor, não utilize nossa Plataforma.
            <p></p>
            Se tiver alguma dúvida ou preocupação sobre estes Termos e Condições, entre em contato conosco através dos nossos canais de suporte.
        </div>
        <div class="form-check" style="padding-bottom:5px;">
            <input class="form-check-input" type="checkbox" id="aceitarTermos" required>
            <label class="form-check-label" for="aceitarTermos">
                Eu li e aceito os termos e condições.
            </label>
        </div>
        <button type="button" class="btn btn-primary" id="next0" style="margin-bottom: 10px;" disabled>Avançar</button>
    </div>
    <div id="step1" style="display: none;">
            <div class="form-group">
                <label for="meta">Informe o valor necessário (R$):</label>
                <input type="text" class="form-control" name="meta" id="meta" required>
                <small class="form-text text-muted">Aqui você precisa colocar o valor que deseja para sua vaquinha, por exemplo, R$ 1.000,00</small>
            </div>
            <button type="button" class="btn btn-primary" id="next1" disabled>Avançar</button>
        </div>

        <div id="step2" style="display: none;">
            <div class="form-group">
                <label for="titulo">Título da Vaquinha:</label>
                <input type="text" class="form-control" name="titulo" id="titulo" required>
                <small class="form-text text-muted">Aqui você deve inserir um título descritivo para a sua vaquinha, por exemplo, "Ajuda para Tratamento Médico".</small>
            </div>
            <button type="button" class="btn btn-primary" id="prev2">Anterior</button>
            <button type="button" class="btn btn-primary" id="next2" disabled>Avançar</button>
        </div>

        <div id="step3" style="display: none;">
            <div class="form-group">
                <label for="descricao">Descrição da Vaquinha:</label>
                <div class="alert alert-info">
                    Por favor, forneça informações detalhadas sobre a vaquinha, incluindo seu propósito, quem será beneficiado e como os fundos serão utilizados. Isso ajudará os doadores a entenderem melhor sua causa.
                </div>
                <textarea class="form-control" name="descricao" id="descricao" rows="5"></textarea>
            </div>
            <button type="button" class="btn btn-primary" id="prev3">Anterior</button>
            <button type="button" class="btn btn-primary" id="next3">Avançar</button>
        </div>

        <div id="step4" style="display: none;">
            <div class="form-group">
                <label for="imagem">Imagem da Vaquinha:</label>
                <input type="file" class="form-control-file" name="imagem" id="imagem" required>
                <small class="form-text text-muted">Aqui você deve selecionar uma imagem representativa para a sua vaquinha. A imagem ajuda os doadores a se conectarem com a sua causa.</small>
            </div>
            <button type="button" class="btn btn-primary" id="prev4">Anterior</button>
            <button type="submit" class="btn btn-success" id="submitBtn" disabled>Salvar Vaquinha</button>
        </div>
    </form>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
<script>
    var currentStep = 0;
    function showStep(step) {
        $("#step" + currentStep).hide();
        $("#step" + step).show();
        currentStep = step;
        updateProgressBar();
    }

    $(document).ready(function() {
        // Aplicar a máscara de moeda ao campo de valor necessário
        $('#meta').inputmask('currency', {
            prefix: 'R$ ',
            numericInput: true, // Permite apenas números
            rightAlign: false, // Alinha à esquerda
            groupSeparator: '.', // Separador de milhar
            radixPoint: ',', // Separador decimal
            allowMinus: false // Desabilita a entrada de números negativos (opcional)
        });

        // Habilitar o botão "Avançar" após aceitar os termos
        $('#aceitarTermos').change(function() {
            if ($(this).is(':checked')) {
                $("#next0").prop("disabled", false);
            } else {
                $("#next0").prop("disabled", true);
            }
        });

        // Adicione o código para avançar para o próximo passo quando o botão "Avançar" no passo 0 for clicado
        $("#next0").click(function() {
            showStep(1);
        });
    });

    // Adicione um evento de escuta para o campo de entrada de imagem
    $("#imagem").change(function() {
        // Verifique se um arquivo foi selecionado
        if ($(this).val() !== "") {
            $("#submitBtn").prop("disabled", false); // Habilita o botão "Salvar Vaquinha"
        } else {
            $("#submitBtn").prop("disabled", true); // Desabilita o botão "Salvar Vaquinha"
        }
    });

    $("#meta").on("input", function() {
        if ($(this).val().trim() !== "") {
            $("#next1").prop("disabled", false); // Habilita o botão "Avançar"
        } else {
            $("#next1").prop("disabled", true); // Desabilita o botão "Avançar"
        }
    });

    // Adicione um evento de escuta para o campo de entrada de Titulo
    $("#titulo").on("input", function() {
        if ($(this).val().trim() !== "") {
            $("#next2").prop("disabled", false); // Habilita o botão "Avançar"
        } else {
            $("#next2").prop("disabled", true); // Desabilita o botão "Avançar"
        }
    });

    function updateProgressBar() {
        var progress = (currentStep * 16.66) + 16.66; // Aumenta o progresso em 20% a cada passo
        $(".progress-bar").css("width", progress + "%").text(progress.toFixed(2) + "%");
    }

    $("#next1").click(function() {
        showStep(2);
    });

    $("#prev2").click(function() {
        showStep(1);
    });

    $("#next2").click(function() {
        showStep(3);
    });

    $("#prev3").click(function() {
        showStep(2);
    });

    $("#next3").click(function() {
        showStep(4);
    });

    $("#prev4").click(function() {
        showStep(3);
    });
    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: '#descricao',
            plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code', 'fullscreen', 'insertdatetime media table paste'],
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            images_upload_url: 'upload_image.php', // URL para fazer o upload da imagem
            images_upload_handler: function (blobInfo, success, failure) {
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', 'upload_image.php');
                xhr.onload = function() {
                    var json;
                    if (xhr.status != 200) {
                        failure('HTTP Error: ' + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    if (!json || typeof json.location != 'string') {
                        failure('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    success(json.location);
                };
                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            }
        });
    });
</script>
</body>
</html>
