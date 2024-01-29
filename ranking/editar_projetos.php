<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../login.php");
    exit();
}

$politico_id = null;
$projeto_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar o formulário de edição de projetos
    $projeto_id = (isset($_POST["projeto_id"])) ? $_POST["projeto_id"] : null;
    $plo = (isset($_POST["plo"])) ? $_POST["plo"] : 0;
    $req = (isset($_POST["req"])) ? $_POST["req"] : 0;

    // Atualizar os valores do projeto
    $update_query = "UPDATE projetos_legislativos SET plo = ?, req = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iii", $plo, $req, $projeto_id);
    $stmt->execute();

    // Redirecionar de volta para a página de detalhes do político
    header("Location: lista_politicos.php");
    exit();
} else {
    // Verifique se um ID de projeto foi fornecido na URL
    if (isset($_GET["id"])) {
        $projeto_id = $_GET["id"];
    } else {
        // ID do projeto não fornecido na URL, redirecione ou mostre uma mensagem de erro
        header("Location: lista_politicos.php");
        exit();
    }

    // Consulta SQL para obter informações do projeto
    $projeto_query = "SELECT politico_id, plo, req FROM projetos_legislativos WHERE id = ?";
    $stmt = $conn->prepare($projeto_query);
    $stmt->bind_param("i", $projeto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $politico_id = $row["politico_id"];
        $plo = $row["plo"];
        $req = $row["req"];
    } else {
        // Projeto não encontrado, redirecione ou mostre uma mensagem de erro
        header("Location: lista_politicos.php");
        exit();
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
    <form method="post" action="editar_projetos.php">
        <input type="hidden" name="projeto_id" value="<?php echo $projeto_id; ?>">
        <div class="form-group">
            <label for="plo">Quantidade de PLO:</label>
            <input type="number" name="plo" class="form-control" value="<?php echo $plo; ?>" required>
        </div>
        <div class="form-group">
            <label for="req">Quantidade de REQ:</label>
            <input type="number" name="req" class="form-control" value="<?php echo $req; ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
</div>

<?php include "../footer.php"; ?>
</body>
</html>
