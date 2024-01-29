<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css">
    <!-- Inclua FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Estilo para o fundo cinza (#eee) */
        body {
            background-color: #eee;
        }
        /* Estilo para centralizar os ícones */
        .social-icons {
            text-align: center;
            margin-top: 20px;
        }
        /* Estilo para os ícones */
        .social-icon {
            font-size: 24px;
            margin: 0 10px;
        }
    </style>
    <title>Contato</title>
</head>
<body>
<?php include "header.php"; ?>

<div class="container mt-5">
    <h1>Entre em Contato</h1>
    <p>Para entrar em contato conosco, você pode utilizar os seguintes endereços de e-mail:</p>

    <ul>
        <li><strong>E-mail:</strong> participatocantins@gmail.com</li>
        <li><strong>E-mail Alternativo:</strong>  contato@participato.com.br</li>
        <li><strong>Endereço:</strong> 208 sul, Av. LO 03, n° 09, sala 04 - Centro, Palmas - TO, 77020-542</li>
    </ul>

    <p>Estamos à disposição para responder às suas perguntas e atender às suas necessidades. Não hesite em nos contatar!</p>

    <!-- Adicione os ícones das redes sociais -->
    <div class="social-icons">
        <a href="https://www.instagram.com/participa.to/?igshid=OGQ5ZDc2ODk2ZA==" target="_blank" class="social-icon"><i class="fab fa-instagram"></i></a>
        <a href="https://t.me/participatocantins" target="_blank" class="social-icon"><i class="fab fa-telegram"></i></a>
        <a href="https://www.facebook.com/people/Participa-Tocantins/61551844856499/" target="_blank" class="social-icon"><i class="fab fa-facebook"></i></a>
        <a href="https://www.tiktok.com/@participatocantins" target="_blank" class="social-icon"><i class="fab fa-tiktok"></i></a>
    </div>
</div>

</body>
</html>
