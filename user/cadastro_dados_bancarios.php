<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /sistema/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$errors = [];
$successMessage = '';

// Verifique se os dados bancários já estão cadastrados para o usuário
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Processar o envio do formulário
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtenha os dados do formulário e realize a validação, se necessário
    $numero_conta = $_POST["numero_conta"];
    $nome_banco = $_POST["nome_banco"];
    $cpf_cnpj_titular = $_POST["cpf_cnpj_titular"];
    $agencia = $_POST["agencia"];

    // Insira ou atualize os dados bancários na tabela de usuários
    if (empty($user_data)) {
        // Se não existirem dados, insira-os
        $query = "INSERT INTO usuarios (id, numero_conta, nome_banco, cpf_cnpj_titular, agencia) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issss", $user_id, $numero_conta, $nome_banco, $cpf_cnpj_titular, $agencia);
    } else {
        // Se existirem dados, atualize-os
        $query = "UPDATE usuarios SET numero_conta = ?, nome_banco = ?, cpf_cnpj_titular = ?, agencia = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $numero_conta, $nome_banco, $cpf_cnpj_titular, $agencia, $user_id);
    }

    if ($stmt->execute()) {
        $successMessage = "Dados bancários atualizados com sucesso.";

        // Após a atualização, recarregue os dados do usuário para refletir as alterações no formulário
        $query = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
    } else {
        $errors[] = "Erro ao atualizar os dados bancários.";
    }

    $stmt->close();
}

include_once "../header.php"; // Inclua o cabeçalho
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Dados Bancários - Minha Plataforma</title>
</head>
<body>
<div class="container mt-5">
    <h2>Cadastro de Dados Bancários</h2>
    <!-- Mensagens de sucesso ou erro -->
    <?php
    if (!empty($successMessage)) {
        echo '<div class="alert alert-success">' . $successMessage . '</div>';
    }
    if (!empty($errors)) {
        echo '<div class="alert alert-danger">' . implode("<br>", $errors) . '</div>';
    }
    ?>

    <!-- Seção de Informações do Usuário -->
    <div class="mt-4 mb-5">
        <h4>Dados do Usuário</h4>
        <p><strong>Nome:</strong> <?php echo $user_data['nome_usuario']; ?></p>
        <p><strong>CPF:</strong> <?php echo $user_data['cpf']; ?></p>
    </div>

    <!-- Seção de Dados Bancários -->
    <form method="post" action="cadastro_dados_bancarios.php">
        <h4>Dados Bancários</h4>
        <div class="alert alert-primary" role="alert">
            Para receber doações, a conta bancária deve estar registrada em nome do titular do cadastro na plataforma Participa Tocantins.
            Se você tiver alguma dúvida, por favor, entre em contato com o departamento financeiro através do menu "institucional/Contato".
        </div>

        <div class="form-group">
            <label for="nome_banco">Nome do Banco:</label>
            <input type="text" class="form-control" id="nome_banco" name="nome_banco"
                   value="<?php echo isset($user_data['nome_banco']) ? $user_data['nome_banco'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="agencia">Agência:</label>
            <input type="text" class="form-control" id="agencia" name="agencia"
                   value="<?php echo isset($user_data['agencia']) ? $user_data['agencia'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="numero_conta">Número da Conta:</label>
            <input type="text" class="form-control" id="numero_conta" name="numero_conta"
                   value="<?php echo isset($user_data['numero_conta']) ? $user_data['numero_conta'] : ''; ?>">
        </div>

        <div class="form-group">
            <label for="cpf_cnpj_titular">Informe seu PIX para Receber Doações:</label>
            <input type="text" class="form-control" id="cpf_cnpj_titular" name="cpf_cnpj_titular"
                   value="<?php echo isset($user_data['cpf_cnpj_titular']) ? $user_data['cpf_cnpj_titular'] : ''; ?>"
                   placeholder="Digite aqui a chave PIX (e-mail, telefone, CPF ou chave aleatória).">
        </div>

        <button type="submit" class="btn btn-primary">Salvar Dados Bancários</button>
    </form>
</div>
</body>
</html>
