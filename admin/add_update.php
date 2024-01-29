<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

// Verifique se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $peticao_id = $_POST["peticao_id"];
    $atualizacao = $_POST["atualizacao"];

    // Inserir a atualização no banco de dados
    $sql = "INSERT INTO atualizacoes (peticao_id, atualizacao) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $peticao_id, $atualizacao);

    if ($stmt->execute()) {
        header("Location: petitions_list.php"); // Redirecionar de volta para a lista de petições
        exit();
    } else {
        echo "Erro ao cadastrar a atualização.";
    }
}

// Recupere o ID da petição da URL
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $peticao_id = intval($_GET["id"]);
} else {
    header("Location: petitions_list.php"); // Redirecionar de volta para a lista de petições se o ID da petição não estiver definido
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Adicionar Atualização</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Adicionar Atualização</h2>

    <form action="" method="post" novalidate>
        <input type="hidden" name="peticao_id" value="<?php echo $peticao_id; ?>">
        <div class="form-group">
            <label for="atualizacao">Atualização:</label>
            <textarea name="atualizacao" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar Atualização</button>
    </form>
</div>

<div class="container mt-5">
    <div class="container mt-5">
        <h2>Atualizações Anteriores</h2>
        <ul class="list-group">
            <?php
            // Consulta para buscar todas as atualizações da petição
            $query_atualizacoes = "SELECT id, atualizacao, data_cadastro FROM atualizacoes WHERE peticao_id = ?";
            $stmt_atualizacoes = $conn->prepare($query_atualizacoes);
            $stmt_atualizacoes->bind_param("i", $peticao_id);
            $stmt_atualizacoes->execute();
            $result_atualizacoes = $stmt_atualizacoes->get_result();

            if ($result_atualizacoes->num_rows > 0) {
                while ($row_atualizacao = $result_atualizacoes->fetch_assoc()) {
                    echo '<li class="list-group-item">';
                    echo '<strong>Data:</strong> ' . date("d/m/Y H:i:s", strtotime($row_atualizacao["data_cadastro"])) . '<br>';
                    echo '<strong>Atualização:</strong> ' . $row_atualizacao["atualizacao"];
                    // Adicione um botão para excluir a atualização
                    echo '<form action="excluir_atualizacao.php" method="post">';
                    echo '<input type="hidden" name="atualizacao_id" value="' . $row_atualizacao["id"] . '">';
                    echo '<button type="submit" class="btn btn-danger btn-sm mt-2">Excluir</button>';
                    echo '</form>';
                    echo '</li>';
                }
            } else {
                echo '<li class="list-group-item">Nenhuma atualização disponível.</li>';
            }

            $stmt_atualizacoes->close();
            ?>
        </ul>
    </div>

<script>
    tinymce.init({
        selector: 'textarea[name="atualizacao"]', // Seletor do textarea que deseja transformar em editor TinyMCE
        plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        toolbar_mode: 'floating',
        height: 300,
        toolbar: 'bold italic underline | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
    });
</script>
</body>
</html>

<?php $conn->close(); ?>
