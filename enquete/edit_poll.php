<?php
ob_start(); // Inicie o buffer de saída
session_start();
include('../includes/db_connection.php');
include('../header.php');

if (isset($_GET['id'])) {
    $enquete_id = intval($_GET['id']);

    // Verifique se a enquete existe
    $sql = "SELECT * FROM enquetes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $enquete_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $enquete = $result->fetch_assoc();

        // Verifique se o formulário de edição foi enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pergunta'])) {
            $nova_pergunta = $_POST['pergunta'];
            $nova_descricao = $_POST['descricao'];
            $novo_status = $_POST['status'];

            // Atualize a pergunta, descrição e status da enquete
            $sql = "UPDATE enquetes SET pergunta = ?, descricao = ?, status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $nova_pergunta, $nova_descricao, $novo_status, $enquete_id);

            if ($stmt->execute()) {
                // Atualize as opções da enquete
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'opcao') === 0) {
                        $opcao_id = substr($key, 5); // Remove o "opcao" do nome do campo para obter o ID
                        $nova_opcao = $value;

                        // Atualize a opção da enquete
                        $sql = "UPDATE opcoes_enquete SET opcao = ? WHERE id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $nova_opcao, $opcao_id);
                        $stmt->execute();
                    }
                }

                // Atualize a imagem, se fornecida
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                    $nomeArquivo = uniqid() . "." . $extensao;
                    $caminhoDestino = 'imagens_enquetes/' . $nomeArquivo;
                    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoDestino)) {
                        $imagemPath = 'imagens_enquetes/' . $nomeArquivo;
                        // Atualize a imagem_path no banco de dados
                        $sql_img = "UPDATE enquetes SET imagem_path = ? WHERE id = ?";
                        $stmt_img = $conn->prepare($sql_img);
                        $stmt_img->bind_param("si", $imagemPath, $enquete_id);
                        $stmt_img->execute();
                    } else {
                        echo "<div class='alert alert-danger'>Erro ao fazer upload da imagem.</div>";
                    }
                }

                // Redirecione de volta à lista de enquetes após a edição
                header("Location: list_poll.php");
                exit;
            } else {
                echo "<div class='alert alert-danger'>Erro ao atualizar a enquete.</div>";
            }
        }

        // Obtenha as opções da enquete
        $sql_opcoes = "SELECT * FROM opcoes_enquete WHERE id_enquete = ?";
        $stmt_opcoes = $conn->prepare($sql_opcoes);
        $stmt_opcoes->bind_param("i", $enquete_id);
        $stmt_opcoes->execute();
        $result_opcoes = $stmt_opcoes->get_result();
        $opcoes_enquete = $result_opcoes->fetch_all(MYSQLI_ASSOC);

        // Exiba o formulário de edição
        ?>
        <div class="container mt-5">
            <h1>Editar Enquete</h1>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pergunta">Pergunta:</label>
                    <input type="text" class="form-control" id="pergunta" name="pergunta" value="<?= htmlspecialchars($enquete['pergunta']) ?>">
                </div>
                <div class="form-group">
                    <label for="descricao">Descrição:</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= htmlspecialchars($enquete['descricao']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="imagem">Atualizar Imagem:</label>
                    <input type="file" class="form-control-file" id="imagem" name="imagem">
                    <?php
                    if (!empty($enquete['imagem_path'])) {
                        echo "<img src='{$enquete['imagem_path']}' alt='Imagem atual da enquete' width='150'>";
                    }
                    ?>
                </div>
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select class="form-control" id="status" name="status">
                        <option value="aberto" <?php if ($enquete['status'] === 'aberto') echo 'selected'; ?>>Aberto</option>
                        <option value="fechado" <?php if ($enquete['status'] === 'fechado') echo 'selected'; ?>>Fechado</option>
                    </select>
                </div>
                <h2>Opções da Enquete:</h2>
                <?php foreach ($opcoes_enquete as $opcao): ?>
                    <div class="form-group">
                        <label for="opcao<?= $opcao['id'] ?>">Opção <?= $opcao['id'] ?>:</label>
                        <input type="text" class="form-control" id="opcao<?= $opcao['id'] ?>" name="opcao<?= $opcao['id'] ?>" value="<?= htmlspecialchars($opcao['opcao']) ?>">
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </form>
        </div>
        <?php
    } else {
        echo "<div class='alert alert-danger'>Enquete não encontrada.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>ID da enquete não fornecido.</div>";
}

include('../footer.php');
ob_end_flush(); // Encerre o buffer de saída
?>

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