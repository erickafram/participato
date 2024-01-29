<?php
session_start();
include "phpqrcode/qrlib.php";
include "funcoes_pix.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de QRCode</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <form id="gerarQRCodeForm" class="needs-validation" novalidate>
        <div class="form-group">
            <label for="valor_pix">Valor do Pix:</label>
            <input type="text" class="form-control" id="valor_pix" required>
        </div>
        <button type="submit" class="btn btn-primary">Gerar QRCode</button>
    </form>
</div>

<!-- Modal -->
<div class="modal fade" id="qrcodeModal" tabindex="-1" role="dialog" aria-labelledby="qrcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrcodeModalLabel">QRCode Gerado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qrcode-container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        $("#gerarQRCodeForm").submit(function (event) {
            event.preventDefault();
            event.stopPropagation();

            var valorPix = $("#valor_pix").val();

            $.ajax({
                type: "POST",
                url: "teste1.php", // Certifique-se de que este é o caminho correto para o seu script PHP
                data: { valor_pix: valorPix },
                success: function (response) {
                    $("#qrcode-container").html(response);
                    $("#qrcodeModal").modal("show");
                },
                error: function () {
                    alert("Ocorreu um erro ao gerar o QRCode.");
                }
            });
        });
    });
</script>
</body>
</html>
