<?php
session_start();
include('../includes/db_connection.php');
include('../header.php'); // Inclua o arquivo header.php aqui

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $subtitulo = mysqli_real_escape_string($conn, $_POST['subtitulo']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);
    $palavra_chave = mysqli_real_escape_string($conn, $_POST['palavra_chave']);

    // Upload da nova imagem de destaque (caso tenha sido selecionada)
    $imagemPath = null;
    if (isset($_FILES['imagem_destaque']) && $_FILES['imagem_destaque']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['imagem_destaque']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . "." . $extensao;
        $caminhoDestino = 'imagens_noticias/' . $nomeArquivo;
        if (move_uploaded_file($_FILES['imagem_destaque']['tmp_name'], $caminhoDestino)) {
            $imagemPath = 'imagens_noticias/' . $nomeArquivo;
        } else {
            echo "Erro ao fazer upload da imagem.";
        }
    }

    // Atualizar a notícia no banco de dados
    $sql = "UPDATE noticias SET titulo='$titulo', subtitulo='$subtitulo', descricao='$descricao', tags='$tags', palavra_chave='$palavra_chave'";
    if ($imagemPath) {
        $sql .= ", imagem_destaque='$imagemPath'";
    }
    $sql .= " WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        $success_message = "Notícia atualizada com sucesso!";
    } else {
        echo "Erro ao atualizar notícia: " . mysqli_error($conn);
    }
}

// Recuperar a notícia para edição
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM noticias WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
}
?>

<div class="container mt-5">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    <h1>Editar Notícia</h1>
    <hr>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo $row['titulo']; ?>" required>
        </div>
        <div class="form-group">
            <label for="subtitulo">Subtítulo:</label>
            <input type="text" class="form-control" id="subtitulo" name="subtitulo" value="<?php echo $row['subtitulo']; ?>">
        </div>
        <div class="form-group">
            <label for="descricao">Descrição:</label>
            <textarea class="form-control" name="descricao" id="descricao" rows="5" required><?php echo $row['descricao']; ?></textarea>
            <!-- Inclua o script do TinyMCE aqui -->
            <script>
                tinymce.init({
                    selector: '#descricao',
                    plugins: [
                        'advlist autolink lists link image charmap print preview anchor',
                        'searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste'
                    ],
                    content_style: "img, video {max-width: 100%; height: auto;}",
                    toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media',
                    setup: function(editor) {
                        editor.on('change', function() {
                            tinymce.triggerSave();
                        });
                    },
                    images_upload_url: 'upload.php', // Script PHP para upload de imagens
                    file_picker_callback: function(callback, value, meta) {
                        if (meta.filetype == 'image' || meta.filetype == 'media') {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'file');
                            input.setAttribute('accept', meta.filetype == 'image' ? 'image/*' : 'video/*');
                            input.onchange = function() {
                                var file = this.files[0];
                                var reader = new FileReader();
                                reader.onload = function () {
                                    var id = 'blobid' + (new Date()).getTime();
                                    var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
                                    var base64 = reader.result.split(',')[1];
                                    var blobInfo = blobCache.create(id, file, base64);
                                    blobCache.add(blobInfo);
                                    callback(blobInfo.blobUri(), { title: file.name });
                                };
                                reader.readAsDataURL(file);
                            };
                            input.click();
                        }
                    }
                });
            </script>
        </div>
        <div class="form-group">
            <label for="tags">Tags:</label>
            <input type="text" class="form-control" id="tags" name="tags" value="<?php echo $row['tags']; ?>">
        </div>
        <div class="form-group">
            <label for="palavra_chave">Palavra-chave:</label>
            <input type="text" class="form-control" id="palavra_chave" name="palavra_chave" value="<?php echo $row['palavra_chave']; ?>">
        </div>
        <div class="form-group">
            <label for="imagem_destaque">Imagem de Destaque Atual:</label>
            <?php if ($row['imagem_destaque']): ?>
                <img src="<?php echo $row['imagem_destaque']; ?>" alt="Imagem de Destaque Atual" width="200">
            <?php else: ?>
                <p>Nenhuma imagem de destaque atual disponível.</p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="imagem_destaque">Nova Imagem de Destaque:</label>
            <input type="file" class="form-control-file" id="imagem_destaque" name="imagem_destaque">
        </div>
        <button type="submit" class="btn btn-primary">Atualizar Notícia</button>
    </form>
</div>

<!-- Inclua os scripts do Bootstrap 4.5.2 e outros scripts personalizados aqui -->
</body>
</html>
