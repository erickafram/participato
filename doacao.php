<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faça uma Doação - ParticipaTO</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include "header.php"; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h2>Faça uma Doação</h2>
            <p>Seu apoio é fundamental para o sucesso do nosso projeto ParticipaTO. Com sua generosidade, podemos continuar a promover a participação cidadã e fazer a diferença em nossa comunidade.</p>
            <p>Seja parte deste movimento e multiplique seu impacto. Cada contribuição, por menor que seja, nos aproxima do nosso objetivo de construir um futuro mais promissor para todos.</p>
            <p>Com sua doação, podemos expandir nossos projetos de petições, vaquinhas e enquetes, alcançar um público maior e envolver mais cidadãos. Além disso, sua contribuição nos permite melhorar nossa plataforma, tornando-a mais eficaz e acessível.</p>
            <p>Seu apoio faz a diferença. Junte-se a nós hoje para criar um impacto positivo na nossa comunidade e no mundo em geral. Sua doação é um investimento no futuro que todos desejamos.</p>
            <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#pixModal-DESATIVAR">Doar Agora</a>
        </div>

        <div class="col-md-6">
            <div class="donate-section">
                <div class="donate-box text-center">
                    <h3>Como Sua Doação Ajuda</h3>
                    <p>Com sua doação, podemos:</p>
                    <ul class="list-group text-left">
                        <li class="list-group-item">Expandir nossos projetos de petições, vaquinhas e enquetes.</li>
                        <li class="list-group-item">Alcançar um público maior e envolver mais cidadãos.</li>
                        <li class="list-group-item">Melhorar nossa plataforma para torná-la mais eficaz e acessível.</li>
                    </ul>
                    <p>Seu apoio faz a diferença. Junte-se a nós hoje para criar um impacto positivo!</p>
                    <h3>Formas de Doar</h3>
                    <p>Oferecemos várias opções de doação:</p>
                    <ul class="list-group text-left">
                        <li class="list-group-item">Doação única.</li>
                        <li class="list-group-item">Doação mensal recorrente.</li>
                    </ul>
                    <p>Qualquer quantia é bem-vinda e apreciada.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para informações de pagamento PIX -->
<div class="modal fade" id="pixModal" tabindex="-1" role="dialog" aria-labelledby="pixModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pixModalLabel">Informações de Pagamento PIX</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Adicione aqui as informações de pagamento PIX, como o código QR e os detalhes da conta -->
                <p>Escaneie o código QR abaixo ou utilize os seguintes dados para fazer a doação via PIX:</p>
                <center><img src="vaquinha/images/qrcode.jpeg" width="50%" height="50%" alt="Código QR PIX"></center>
                <!-- Exemplo de detalhes da conta (substitua pelos seus detalhes reais) -->
                <div class="input-group mb-3" style="padding-top:50px;">
                    <input type="text" id="pixChave" class="form-control" value="00020101021126580014br.gov.bcb.pix013620682071-6569-47b4-ae5b-50033846afda520400005303986540510.005802BR5917ERICK V RODRIGUES6006PALMAS62070503***6304065D" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-secondary" type="button" id="btnCopiar">Copiar</button>
                    </div>
                </div>
                <div id="mensagemAgradecimento">
                    <p>Agradecemos muito pela sua doação! Seu apoio é fundamental para o sucesso do nosso projeto.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Exibir a mensagem de agradecimento automaticamente ao abrir o modal
    $('#pixModal').on('shown.bs.modal', function () {
        document.getElementById("mensagemAgradecimento").style.display = "block";
    });

    // Copiar a chave PIX para a área de transferência quando o botão "Copiar" for clicado
    document.getElementById("btnCopiar").addEventListener("click", function() {
        var pixChave = document.getElementById("pixChave");
        pixChave.select();
        document.execCommand("copy");
    });
</script>

</body>
</html>
