<!DOCTYPE html>
<html>

<head>
    <title>Faça uma Doação</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
<?php include "header.php"; ?> <!-- Inclui o cabeçalho -->

<div class="container mt-5">
    <h1>Faça uma Doação</h1>
    <p>Obrigado por considerar fazer uma doação para nosso projeto!</p>
    <p>O nosso projeto é dedicado a causas nobres e precisa de seu apoio para continuar crescendo.</p>

    <h2>Opções de Pagamento</h2>
    <h3>Pagamento via PIX</h3>
    <p>Selecione a opção de pagamento via PIX e siga as instruções para concluir sua doação.</p>
    <p>Chave PIX: <strong>chave_pix@example.com</strong></p>
    <p>Valor: <input type="number" id="valor-pix" placeholder="Digite o valor da doação"> BRL</p>
    <button id="btn-pix" class="btn btn-primary">Pagar via PIX</button>
</div>

<?php include "footer.php"; ?> <!-- Inclui o rodapé -->

<!-- Include JQuery and Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">

<script>
    $(document).ready(function () {
        $("#btn-pix").click(function () {
            // Obtenha o valor digitado pelo usuário
            var valorDoacao = $("#valor-pix").val();

            // Exemplo de integração de pagamento real aqui (não incluído neste exemplo)
            // Você deve enviar os detalhes da doação para um serviço de pagamento ou gateway

            // Exiba uma mensagem de sucesso (simplificado para este exemplo)
            alert("Sua doação de " + valorDoacao + " BRL via PIX foi recebida com sucesso!");

            // Redirecione para uma página de confirmação ou agradecimento
            window.location.href = "confirmacao.php";
        });
    });
</script>
</body>

</html>
