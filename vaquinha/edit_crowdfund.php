<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../user/login.php");
    exit();
}

if (isset($_GET["id"])) {
    $id_vaquinha = $_GET["id"];

    $sql_vaquinha = "SELECT * FROM vaquinhas WHERE id = ?";
    $stmt_vaquinha = $conn->prepare($sql_vaquinha);
    $stmt_vaquinha->bind_param("i", $id_vaquinha);
    $stmt_vaquinha->execute();
    $result_vaquinha = $stmt_vaquinha->get_result();
    $vaquinha = $result_vaquinha->fetch_assoc();

    if (!$vaquinha) {
        header("Location: all_crowdfunds.php");
        exit();
    }
} else {
    header("Location: all_crowdfunds.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = $_POST["titulo"];
    $descricao = $_POST["descricao"];
    $meta = $_POST["meta"];

    // Atualiza os campos no banco de dados
    $status = $_POST["status"];
    $sql_update = "UPDATE vaquinhas SET titulo = ?, descricao = ?, meta = ?, status = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssdss", $titulo, $descricao, $meta, $status, $id_vaquinha);

    if ($stmt_update->execute()) {
        // Verifica se um novo arquivo de imagem foi enviado
        if ($_FILES["imagem"]["error"] === UPLOAD_ERR_OK) {
            $imagem_tmp = $_FILES["imagem"]["tmp_name"];
            $imagem_nome = $_FILES["imagem"]["name"];

            // Define o diretório de upload
            $upload_dir = "images/";
            $imagem_destino = $upload_dir . $imagem_nome;

            // Move a nova imagem para o diretório de upload
            if (move_uploaded_file($imagem_tmp, $imagem_destino)) {
                // Atualize o campo de imagem no banco de dados
                $sql_update_imagem = "UPDATE vaquinhas SET imagem = ? WHERE id = ?";
                $stmt_update_imagem = $conn->prepare($sql_update_imagem);
                $stmt_update_imagem->bind_param("si", $imagem_nome, $id_vaquinha);
                $stmt_update_imagem->execute();
            } else {
                $error_message = "Erro ao fazer o upload da imagem.";
            }
        }

        header("Location: all_crowdfunds.php");
        exit();
    } else {
        $error_message = "Erro ao atualizar a vaquinha.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Vaquinha</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
<?php include_once "../header.php"; ?>
<div class="container mt-5">
    <h1>Editar Vaquinha</h1>
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titulo">Título</label>
            <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo $vaquinha["titulo"]; ?>" required>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="4" required><?php echo $vaquinha["descricao"]; ?></textarea>
        </div>
        <div class="form-group">
            <label for="meta">Meta</label>
            <input type="number" class="form-control" id="meta" name="meta" value="<?php echo $vaquinha["meta"]; ?>" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="aberto" <?php if($vaquinha["status"] === "aberto") echo "selected"; ?>>Aberto</option>
                <option value="fechado" <?php if($vaquinha["status"] === "fechado") echo "selected"; ?>>Fechado</option>
                <option value="finalizada" <?php if($vaquinha["status"] === "finalizada") echo "selected"; ?>>Finalizada</option>
            </select>
        </div>


        <div class="form-group">
            <label for="imagem">Imagem</label>
            <input type="file" class="form-control-file" id="imagem" name="imagem">
        </div>

        <div class="form-group">
            <label for="imagem_atual">Imagem Atual</label>
            <?php if ($vaquinha["imagem"]): ?>
                <br>
                <img src="images/<?php echo $vaquinha["imagem"]; ?>" alt="Imagem da Vaquinha" style="max-width: 300px;">
            <?php else: ?>
                <p>Nenhuma imagem encontrada.</p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
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
