<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar o formulário de edição de político
    $id = (isset($_POST["id"])) ? $_POST["id"] : null;
    $nome = (isset($_POST["nome"])) ? $_POST["nome"] : null;
    $partido = (isset($_POST["partido"])) ? $_POST["partido"] : null;
    $telefone = (isset($_POST["telefone"])) ? $_POST["telefone"] : null;
    $email = (isset($_POST["email"])) ? $_POST["email"] : null;
    $cargo = (isset($_POST["cargo"])) ? $_POST["cargo"] : null;
    $periodo_mandato = (isset($_POST["periodo_mandato"])) ? $_POST["periodo_mandato"] : null; // Adicione o campo "Período de Mandato"

    // Verifique se o "Período de Mandato" está no formato correto (xxxx-xxxx)
    $periodo_mandato = $_POST["periodo_mandato"];

    if (!preg_match("/^\d{4}-\d{4}$/", $periodo_mandato)) {
        // Se o formato não for válido, exiba uma mensagem de erro
        echo "O campo 'Período de Mandato' deve estar no formato correto (exemplo: 2020-2024).";
        exit();
    }

    if (empty($id) || empty($nome) || empty($partido) || empty($telefone) || empty($email) || empty($cargo) || empty($periodo_mandato)) {
        // Tratar erros caso algum campo obrigatório não seja preenchido
        echo "Por favor, preencha todos os campos obrigatórios.";
        exit();
    }

    // Recupere o caminho da imagem existente no banco de dados
    $query = "SELECT caminho_imagem FROM politicos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($caminho_imagem);
    $stmt->fetch();

    // Verifique se uma nova imagem foi enviada
    if (!empty($_FILES["nova_imagem"]["name"])) {
        $imagem_dir = "../ranking/imagens/"; // Diretório onde as imagens são armazenadas
        $imagem_nome = basename($_FILES["nova_imagem"]["name"]); // Nome do novo arquivo de imagem
        $imagem_caminho = $imagem_dir . $imagem_nome; // Caminho completo da nova imagem no servidor

        if (move_uploaded_file($_FILES["nova_imagem"]["tmp_name"], $imagem_caminho)) {
            // Nova imagem enviada com sucesso, você pode atualizar o caminho no banco de dados
            $caminho_imagem = $imagem_nome;
        } else {
            // Tratar erros de upload de nova imagem
            echo "Ocorreu um erro ao enviar a nova imagem.";
            exit();
        }
    }

    // Atualize os dados no banco de dados
    $update_query = "UPDATE politicos SET nome=?, partido=?, telefone=?, email=?, cargo=?, caminho_imagem=?, periodo_mandato=? WHERE id=?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssssssi", $nome, $partido, $telefone, $email, $cargo, $caminho_imagem, $periodo_mandato, $id);

    if ($stmt->execute()) {
        // Dados do político atualizados com sucesso, você pode redirecionar ou exibir uma mensagem de sucesso
        header("Location: lista_politicos.php"); // Redireciona para a lista de políticos
        exit();
    } else {
        // Tratar erros, exibir uma mensagem de erro, ou redirecionar para uma página de erro
        echo "Ocorreu um erro ao atualizar os dados do político.";
    }
} else {
    // Recupere os dados do político com base no ID passado na URL
    if (isset($_GET["id"])) {
        $politico_id = $_GET["id"];
        $query = "SELECT * FROM politicos WHERE id = $politico_id";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            // Político não encontrado, redirecione ou mostre uma mensagem de erro
            header("Location: lista_politicos.php");
            exit();
        }
    } else {
        // ID do político não fornecido na URL, redirecione ou mostre uma mensagem de erro
        header("Location: lista_politicos.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Político | Sua Plataforma</title>
    <!-- Adicione links para folhas de estilo CSS e outros recursos aqui -->
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Editar Político</h2>
    <form method="post" action="editar_politico.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" class="form-control" value="<?php echo $row["nome"]; ?>" required>
        </div>
        <div class="form-group">
            <label for="partido">Partido:</label>
            <input type="text" name="partido" class="form-control" value="<?php echo $row["partido"]; ?>" required>
        </div>
        <div class "form-group">
        <label for="telefone">Telefone:</label>
        <input type="text" name="telefone" class="form-control" value="<?php echo $row["telefone"]; ?>" required>
</div>
<div class="form-group">
    <label for="email">Email:</label>
    <input type="email" name="email" class="form-control" value="<?php echo $row["email"]; ?>" required>
</div>
<!-- Adicione o campo "Período de Mandato" -->
<div class="form-group">
    <label for="periodo_mandato">Período de Mandato (exemplo: 2020-2024):</label>
    <input type="text" name="periodo_mandato" class="form-control" value="<?php echo $row["periodo_mandato"]; ?>" required>
</div>
<div class="form-group">
    <label for="imagem_atual">Imagem Atual:</label>
    <img src="../ranking/imagens/<?php echo $row["caminho_imagem"]; ?>" alt="Imagem Atual" class="img-thumbnail" style="max-width: 200px">
</div>
<div class="form-group">
    <label for="nova_imagem">Nova Imagem:</label>
    <input type="file" name="nova_imagem" class="form-control-file">
</div>
<div class="form-group">
    <label for="cargo">Qual cargo político:</label>
    <select name="cargo" class="form-control" required>
        <option value="Vereador" <?php if ($row["cargo"] == "Vereador") echo "selected"; ?>>Vereador</option>
        <option value="Governador" <?php if ($row["cargo"] == "Governador") echo "selected"; ?>>Governador</option>
        <option value="Deputado Estadual" <?php if ($row["cargo"] == "Deputado Estadual") echo "selected"; ?>>Deputado Estadual</option>
        <option value="Deputado Federal" <?php if ($row["cargo"] == "Deputado Federal") echo "selected"; ?>>Deputado Federal</option>
        <option value="Senador" <?php if ($row["cargo"] == "Senador") echo "selected"; ?>>Senador</option>
        <option value="Prefeito" <?php if ($row["cargo"] == "Prefeito") echo "selected"; ?>>Prefeito</option>
    </select>
</div>
<button type="submit" class="btn btn-primary">Salvar</button>
</form>
</div>
</body>
</html>
