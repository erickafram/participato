<!DOCTYPE html>
<html>
<head>
    <title>Registro - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- Certifique-se de que jQuery venha antes de outros scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript">

        var $j = jQuery.noConflict();
        $j(document).ready(function(){
            $j('#cpf').mask('000.000.000-00', {reverse: true});
        });

        var $j = jQuery.noConflict();
        $j(document).ready(function(){
            $j('#telefone').mask('(99) 99999-9999');
        });
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "../header.php"; ?>
<div class="container mt-5">
    <div class="register-container">
        <h2>Registro</h2>

        <!-- Mensagens de erro e sucesso aqui -->
        <?php
        if (isset($_GET['error'])) {
            $error = $_GET['error'];
            if ($error == "already_registered") {
                echo "<p style='color:red;'>E-mail ou telefone já cadastrados!</p>";
            } elseif ($error == "password_mismatch") {
                echo "<p style='color:red;'>As senhas não coincidem!</p>";
            } elseif ($error == "registration_failed") {
                echo "<p style='color:red;'>Falha no registro!</p>";
            } elseif ($error == "cpf_already_exists") {
                echo "<p style='color:red;'>CPF já cadastrado!</p>";
            }
        }

        if (isset($_GET['success'])) {
            $success = $_GET['success'];
            if ($success == "registration_completed") {
                echo "<p style='color:green;'>Cadastro realizado com sucesso! Você será redirecionado para a tela de login.</p>";
                echo "<script>
                      setTimeout(function(){
                        window.location.href = '../login.php';
                      }, 3000);
                      </script>";
            }
        }
        ?>

        <form class="register-form" action="process_register.php" method="POST">
            <div class="row"> <!-- Linha de grid para campos lado a lado -->
                <div class="col-md-6 form-group">
                    <label for="username">Nome Completo:</label>
                    <input type="text" class="form-control" name="username" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="cpf">CPF:</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" required>
                </div>
            </div>

            <div class="row"> <!-- Linha de grid para campos lado a lado -->
                <div class="col-md-6 form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="telefone">Telefone:</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" required>
                </div>
            </div>

            <div class="row"> <!-- Linha de grid para campos lado a lado -->
                <div class="col-md-6 form-group">
                    <label for="data_nascimento">Data de Nascimento:</label>
                    <input type="date" class="form-control" name="data_nascimento" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirmacao_senha">Confirmação de Senha:</label>
                <input type="password" class="form-control" name="confirmacao_senha" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Registrar</button>
            <a href="javascript:history.back()" class="btn btn-secondary btn-block mt-3">Voltar</a>
        </form>
    </div>
</div>
</body>
</html>
