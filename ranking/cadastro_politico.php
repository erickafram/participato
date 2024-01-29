<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar o formulário de cadastro de político
    $nome = $_POST["nome"];
    $partido = $_POST["partido"];
    $telefone = $_POST["telefone"];
    $email = $_POST["email"];
    $cargo = $_POST["cargo"];
    $periodo_mandato = $_POST["periodo_mandato"];

    // Verifique se o "Período de Mandato" está no formato correto (exemplo: 2020-2024)
    if (!preg_match("/^\d{4}-\d{4}$/", $periodo_mandato)) {
        // Se não estiver no formato correto, você pode tratar o erro ou redirecionar
        echo "O campo 'Período de Mandato' deve estar no formato correto (exemplo: 2020-2024).";
        exit();
    }

// Lidar com o upload da imagem
    $imagem_dir = "../ranking/imagens/"; // Diretório onde as imagens serão armazenadas
    $imagem_nome = basename($_FILES["imagem"]["name"]); // Nome do arquivo de imagem
    $imagem_caminho = $imagem_dir . $imagem_nome; // Caminho completo da imagem no servidor

    if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $imagem_caminho)) {
        // Imagem enviada com sucesso, você pode armazenar o caminho no banco de dados
        // Insira os dados no banco de dados, incluindo o caminho da imagem e o período de mandato
        $insert_query = "INSERT INTO politicos (nome, partido, telefone, email, cargo, caminho_imagem, periodo_mandato) VALUES (?, ?, ?, ?, ?, ?, ?)"; // Adicione "periodo_mandato" à consulta
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssssss", $nome, $partido, $telefone, $email, $cargo, $imagem_nome, $periodo_mandato); // Adicione "$periodo_mandato" à lista de bind_param

        if ($stmt->execute()) {
            // Político cadastrado com sucesso, você pode redirecionar ou exibir uma mensagem de sucesso
            header("Location: lista_politicos.php"); // Redireciona para a página de lista de políticos
            exit();
        } else {
            // Tratar erros, exibir uma mensagem de erro, ou redirecionar para uma página de erro
            echo "Ocorreu um erro ao cadastrar o político.";
        }
    } else {
        // Tratar erros de upload de imagem
        echo "Ocorreu um erro ao enviar a imagem.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cadastrar Político | Sua Plataforma</title>
    <!-- Adicione links para folhas de estilo CSS e outros recursos aqui -->
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Cadastrar Político</h2>
    <form method="post" action="cadastro_politico.php" enctype="multipart/form-data"> <!-- Adicione enctype para upload de arquivos -->
        <div class="form-group">
            <label for="nome">Nome:</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="partido">Partido:</label>
            <input type="text" name="partido" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" name="telefone" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="imagem">Imagem do Político:</label>
            <input type="file" name="imagem" class="form-control-file" required>
        </div>
        <div class="form-group">
            <label for="cargo">Qual cargo político:</label>
            <select name="cargo" class="form-control" required>
                <option value="Vereador">Vereador</option>
                <option value="Governador">Governador</option>
                <option value="Deputado Estadual">Deputado Estadual</option>
                <option value="Deputado Federal">Deputado Federal</option>
                <option value="Senador">Senador</option>
                <option value="Prefeito">Prefeito</option>
            </select>
        </div>

        <!-- Adicione o campo "periodo_mandato" -->
        <div class="form-group">
            <label for="periodo_mandato">Período de Mandato (exemplo: 2020-2024):</label>
            <input type="text" name="periodo_mandato" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>
</div>
</body>
</html>
