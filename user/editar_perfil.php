<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: /sistema/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$errors = [];
$successMessage = ''; // Nova variável para mensagem de sucesso

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST["nome_usuario"], $_POST["email"], $_POST["telefone"])) {

        $nome_usuario = $_POST["nome_usuario"];
        $email = $_POST["email"];
        $telefone = $_POST["telefone"];

        $update_query = "UPDATE usuarios SET nome_usuario = ?, email = ?, telefone = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $nome_usuario, $email, $telefone, $user_id);

        if ($stmt->execute()) {
            $successMessage = 'Dados atualizados com sucesso!'; // Atribui a mensagem de sucesso
        } else {
            $errors[] = "Erro ao atualizar dados.";
        }
    } else {
        $errors[] = "Todos os campos são obrigatórios.";
    }
}

// Busca os dados atuais do usuário para preenchimento automático do formulário
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="/sistema/assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Editar Perfil</h2>

    <?php
    if ($successMessage) {
        echo "<div class='alert alert-success'>$successMessage</div>";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
    ?>

    <?php
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
    ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="nome_usuario">Nome de usuário:</label>
            <input type="text" class="form-control" name="nome_usuario" value="<?php echo $user_data['nome_usuario']; ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" name="email" value="<?php echo $user_data['email']; ?>" required>
        </div>

        <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" class="form-control" name="telefone" value="<?php echo $user_data['telefone']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Atualizar</button>
    </form>
</div>
</body>
</html>
