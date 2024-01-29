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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Editar Petição</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: '#descricao', // ID do textarea
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
            height: 300,
            toolbar: 'bold italic underline | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
        });

        tinymce.init({
            selector: '#link_artigos', // ID do textarea
            plugins: 'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            toolbar_mode: 'floating',
            height: 300,
            toolbar: 'bold italic underline | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
        });
    </script>
</head>
<body>
<?php include "../header.php"; // Inclua o cabeçalho para administrador ?>
<div class="container mt-5">
    <?php
    require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
        $petition_id = $_GET["id"];

        $query = "SELECT * FROM abaixo_assinados WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $petition_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $petition = $result->fetch_assoc();

                echo '<h2>Editar Petição</h2>';
                echo '<form method="post" action="process_edit_petition.php">';
                echo '<input type="hidden" name="petition_id" value="' . $petition_id . '">';
                echo '<div class="form-group">';
                echo '<label for="titulo">Título:</label>';
                echo '<input type="text" class="form-control" name="titulo" id="titulo" value="' . $petition["titulo"] . '" required>';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="descricao">Descrição:</label>';
                echo '<textarea class="form-control" name="descricao" id="descricao" rows="5" required>' . $petition["descricao"] . '</textarea>';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="caminho_imagem">Caminho da Imagem:</label>';
                echo '<input type="text" class="form-control" name="caminho_imagem" id="caminho_imagem" value="' . $petition["caminho_imagem"] . '">';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="status">Status:</label>';
                echo '<select class="form-control" name="status" id="status">';
                echo '<option value="pendente" ' . ($petition["status"] === "pendente" ? "selected" : "") . '>Pendente</option>';
                echo '<option value="aprovado" ' . ($petition["status"] === "aprovado" ? "selected" : "") . '>Aprovado</option>';
                echo '<option value="finalizado" ' . ($petition["status"] === "finalizado" ? "selected" : "") . '>Finalizado</option>';
                echo '</select>';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="quantidade_assinaturas">Quantidade de Assinaturas Necessárias:</label>';
                echo '<input type="number" class="form-control" name="quantidade_assinaturas" id="quantidade_assinaturas" value="' . $petition["quantidade_assinaturas"] . '" required>';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="link_artigos">Link de Artigos Relacionados (um por linha):</label>';
                echo '<textarea class="form-control" name="link_artigos" id="link_artigos" rows="5">' . $petition["link_artigos"] . '</textarea>';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="link_grupo">Link para Grupo do Telegram ou WhatsApp:</label>';
                echo '<input type="text" class="form-control" name="link_grupo" id="link_grupo" value="' . $petition["link_grupo"] . '">';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="data_finalizacao">Data de Finalização:</label>';
                echo '<input type="date" class="form-control" name="data_finalizacao" id="data_finalizacao" value="' . $petition["data_finalizacao"] . '">';
                echo '</div>';
                echo '<div class="form-group">';
                echo '<label for="turbinado">Turbinar Petição:</label>';
                echo '<input type="checkbox" name="turbinado" id="turbinado" value="1" ' . ($petition["turbinado"] ? "checked" : "") . '>';
                echo '</div>';
                echo '<button type="submit" class="btn btn-primary">Salvar</button>';
                echo '<a href="petitions_list.php" class="btn btn-secondary">Cancelar</a>';
                echo '</form>';
            } else {
                echo "<p>Petição não encontrada.</p>";
            }

            $stmt->close();
        } else {
            echo "Erro na preparação da declaração: " . $conn->error;
        }
    } else {
        echo "<p>ID da petição não especificado.</p>";
    }

    $conn->close();
    ?>
</div>
<?php include "../footer.php"; // Inclua o rodapé para administrador ?>
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        let descricaoValue = tinymce.get('descricao').getContent();
        let linkArtigosValue = tinymce.get('link_artigos').getContent(); // Conteúdo do TinyMCE para o campo "Link de Artigos Relacionados"
        let fileInput = document.getElementById('imagem');
        let filePath = fileInput.value;
        let allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

        if (!descricaoValue || !linkArtigosValue) { // Verifica se a descrição e os artigos relacionados estão preenchidos
            e.preventDefault();
            alert('Descrição e Link de Artigos Relacionados são campos obrigatórios.');
            return;
        }

        if (!allowedExtensions.exec(filePath)) {
            alert('Por favor, carregue arquivos com as extensões .jpg/.jpeg/.png apenas.');
            e.preventDefault();
            return;
        }

        if(fileInput.files[0].size > 2097152){
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

        tinymce.init({
            selector: '#link_artigos',
            plugins: ['advlist autolink lists link image charmap print preview anchor', 'searchreplace visualblocks code', 'fullscreen', 'insertdatetime media table paste'],
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
        });
    });
</script>
</body>
</html>
