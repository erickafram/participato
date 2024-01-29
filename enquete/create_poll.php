<?php
session_start();
include('../includes/db_connection.php');
if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

// Habilitar a exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../header.php');

$success_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $descricao = mysqli_real_escape_string($conn, $_POST['descricao']);
    $opcoes = $_POST['opcao']; // Isso será um array
    $status = 'aberto';

    // Insira imagempath
    $imagemPath = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . "." . $extensao;
        $caminhoDestino = 'imagens_enquetes/' . $nomeArquivo;
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino)) {
            $imagemPath = 'imagens_enquetes/' . $nomeArquivo;
        } else {
            echo "Erro ao fazer upload da imagem.";
        }
    }

    // Insira a enquete na tabela de enquetes
    $sql = "INSERT INTO enquetes (pergunta, descricao, status, imagem_path) VALUES ('$titulo', '$descricao', '$status', '$imagemPath')";
    if (mysqli_query($conn, $sql)) {
        $poll_id = mysqli_insert_id($conn);

        // Insira as opções na tabela de opções
        foreach ($opcoes as $opcao) {
            $opcao = mysqli_real_escape_string($conn, $opcao);
            $sql_opcao = "INSERT INTO opcoes_enquete (id_enquete, opcao) VALUES ('$poll_id', '$opcao')";
            if (!mysqli_query($conn, $sql_opcao)) {
                echo "Erro ao inserir opção: " . mysqli_error($conn);
            }
        }
        $success_message = "Enquete criada com sucesso!";
    } else {
        echo "Erro ao inserir enquete: " . mysqli_error($conn);
    }
}
?>

<div class="container mt-5">
    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>
    <h1>Criar Enquete</h1>
    <hr>
    <form action="" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titulo">Título da Enquete:</label>
            <input type="text" class="form-control" id="titulo" name="titulo" required>
        </div>
        <div class="form-group">
            <label for="descricao">Descrição:</label>
            <textarea class="form-control" name="descricao" id="descricao" rows="5"></textarea>
        </div>
        <div class="form-group">
            <label for="imagem">Imagem da Enquete:</label>
            <input type="file" class="form-control-file" id="imagem" name="imagem">
        </div>
        <div class="form-group">
            <label for="opcao">Opções:</label>
            <input type="text" class="form-control mb-2" name="opcao[]" required>
            <input type="text" class="form-control mb-2" name="opcao[]" required>
            <input type="text" class="form-control mb-2" name="opcao[]">
            <input type="text" class="form-control mb-2" name="opcao[]">
        </div>
        <button type="submit" class="btn btn-primary">Criar Enquete</button>
    </form>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        let descricaoValue = tinymce.get('descricao').getContent();
        let fileInput = document.getElementById('imagem');
        let filePath = fileInput.value;
        let allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        if (!descricaoValue) { // replace this check with whatever validation you need
            e.preventDefault();
            alert('Descricao is required');
            return;
        }

        if (!allowedExtensions.exec(filePath) && fileInput.files.length > 0) {
            alert('Por favor, carregue arquivos com as extensões .jpg/.jpeg/.png apenas.');
            e.preventDefault();
            return;
        }

        if (fileInput.files.length > 0 && fileInput.files[0].size > 2097152) {
            alert('O arquivo deve ter no máximo 2 MB');
            e.preventDefault();
            return;
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

<?php
include('../footer.php');
?>
