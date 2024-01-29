<?php
require_once "../includes/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $telefone = $_POST["telefone"];
    $data_nascimento = $_POST["data_nascimento"];
    $cpf = $_POST["cpf"];
    $password = $_POST["password"];
    $confirmacao_senha = $_POST["confirmacao_senha"];
    $data_cadastro = date("Y-m-d H:i:s"); // Data e hora atuais em formato SQL

    // Verificar se a senha e a confirmação de senha coincidem
    if ($password != $confirmacao_senha) {
        header("Location: register.php?error=password_mismatch");
        exit();
    }

    // Verificar se o CPF já existe
    $cpf_check = "SELECT * FROM usuarios WHERE cpf = ?";
    $stmt_check = $conn->prepare($cpf_check);
    $stmt_check->bind_param("s", $cpf);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // CPF já existe
        header("Location: register.php?error=cpf_already_exists");
        exit();
    }

    // Hash da senha antes de inserir no banco de dados
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO usuarios (nome_usuario, email, telefone, senha, confirmacao_senha, data_nascimento, cpf, data_cadastro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssss", $username, $email, $telefone, $hashed_password, $confirmacao_senha, $data_nascimento, $cpf, $data_cadastro);

    if ($stmt->execute()) {
        header("Location: register.php?success=registration_completed");
        exit();
    } else {
        header("Location: register.php?error=registration_failed");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
