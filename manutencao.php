<?php
session_start();
require_once "includes/db_connection.php";
if (!isset($_SESSION["user_id"])) {
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Página em Manutenção</title>
    <!-- Inclua os arquivos CSS e JS do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <?php include "header.php"; ?>
    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .maintenance-container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            padding: 100px 20px;
        }

        .maintenance-icon {
            font-size: 4rem;
            color: #dc3545;
        }

        .maintenance-title {
            font-size: 2rem;
            margin-top: 20px;
        }

        .maintenance-text {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="maintenance-container">
    <i class="bi bi-exclamation-circle maintenance-icon"></i>
    <h1 class="maintenance-title">Página em Manutenção</h1>
    <p class="maintenance-text">Estamos trabalhando para melhorar nossa página. Voltaremos em breve.</p>
    <a href="https://participato.com.br" class="btn btn-primary">Ir para Notícias</a>

</div>
</body>
</html>
