<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$abaixo_assinado_id = $_GET["id"];

// Consulta para obter os detalhes do abaixo-assinado
$query = "SELECT id, titulo, descricao, caminho_imagem, status, link_artigos, link_grupo
          FROM abaixo_assinados
          WHERE id = ? AND id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $abaixo_assinado_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: dashboard.php");
    exit();
}

$row = $result->fetch_assoc();
$titulo = $row["titulo"];
$descricao = $row["descricao"];
$caminho_imagem = $row["caminho_imagem"];
$status = $row["status"];
$link_artigos = $row["link_artigos"];
$link_grupo = $row["link_grupo"];

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Abaixo-Assinado - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
<?php include_once "../header.php"; // Inclua o cabeçalho ?>
<div class="container mt-5">
    <h2>Editar Abaixo-Assinado</h2>

    <form method="post" action="process_edit_petition.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $abaixo_assinado_id; ?>">
        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" class="form-control" name="titulo" id="titulo" value="<?php echo $titulo; ?>" required>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição:</label>
            <textarea class="form-control" name="descricao" id="descricao" rows="5" required><?php echo $descricao; ?></textarea>
        </div>

        <div class="form-group">
            <label for="imagem">Imagem:</label>
            <input type="file" class="form-control-file" name="imagem" id="imagem">
        </div>

        <div class="form-group">
            <label for="link_grupo">Link para Grupo do Telegram ou WhatsApp:</label>
            <input type="text" class="form-control" name="link_grupo" id="link_grupo" value="<?php echo $link_grupo; ?>">
        </div>
        <?php
        if ($status == 'pendente') {
            echo '<button type="submit" class="btn btn-primary">Salvar</button>';
        } else {
            echo '<p class="text-success">Esta petição foi aprovada e não pode ser editada.</p>';
        }
        ?>
        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<script>

    document.querySelector('form').addEventListener('submit', function(e) {
        let descricaoValue = tinymce.get('descricao').getContent();
        if (!descricaoValue) { // replace this check with whatever validation you need
            e.preventDefault();
            alert('Descricao is required');
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: '#descricao',
            plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code', 'fullscreen', 'insertdatetime media table paste'],
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
        });

        const toggleViewButton = document.getElementById("toggle-view");
        const previewContainer = document.getElementById("preview-container");
        const descricaoEditor = tinymce.get("descricao");

        toggleViewButton.addEventListener("click", function() {
            if (descricaoEditor.isHidden()) {
                previewContainer.style.display = "none";
                descricaoEditor.show();
                toggleViewButton.textContent = "Ver HTML";
            } else {
                descricaoEditor.hide();
                previewContainer.style.display = "block";
                previewContainer.innerHTML = descricaoEditor.getContent();
                toggleViewButton.textContent = "Ver Visual";
            }
        });
    });
</script>
</body>
</html>
