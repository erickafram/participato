<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../login.php");
    exit();
}

// Verifique se um ano foi fornecido na URL
if (isset($_GET["ano"])) {
    $ano = $_GET["ano"];

    // Consultar o banco de dados para obter os detalhes do projeto legislativo com base no ano
    $query = "SELECT * FROM projetos_legislativos WHERE ano = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $ano);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
    } else {
        // Redirecione ou mostre uma mensagem de erro se o projeto não for encontrado
        header("Location: lista_projetos_legislativos.php");
        exit();
    }
} else {
    // Redirecione ou mostre uma mensagem de erro se o ano não for fornecido
    header("Location: lista_projetos_legislativos.php");
    exit();
}

// Lógica para processar a edição do projeto legislativo após o envio do formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plo = (isset($_POST["plo"])) ? $_POST["plo"] : 0;
    $req = (isset($_POST["req"])) ? $_POST["req"] : 0;

    // Atualizar os valores do projeto legislativo no banco de dados
    $update_query = "UPDATE projetos_legislativos SET plo = ?, req = ? WHERE ano = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iii", $plo, $req, $ano);
    if ($stmt->execute()) {
        // Redirecionar de volta para a lista de projetos legislativos após a edição
        header("Location: lista_projetos_legislativos.php");
        exit();
    } else {
        // Mostrar uma mensagem de erro se a atualização falhar
        $error_message = "Ocorreu um erro ao editar o projeto legislativo.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Projeto Legislativo | Sua Plataforma</title>
    <!-- Adicione links para folhas de estilo CSS e outros recursos aqui -->
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Editar Projeto Legislativo</h2>
    <form method="post" action="editar_projeto.php?ano=<?php echo $ano; ?>">
        <div class="form-group">
            <label for="plo">Quantidade de PLO:</label>
            <input type="number" name="plo" class="form-control" value="<?php echo $row['plo']; ?>" required>
        </div>
        <div class="form-group">
            <label for="req">Quantidade de REQ:</label>
            <input type="number" name="req" class="form-control" value="<?php echo $row['req']; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Editar</button>
        <a href="lista_projetos_legislativos.php" class="btn btn-secondary">Voltar</a>
    </form>
    <?php
    if (isset($error_message)) {
        echo "<div class='alert alert-danger mt-3'>$error_message</div>";
    }
    ?>
</div>

<?php include "../footer.php"; ?>
</body>
</html>
