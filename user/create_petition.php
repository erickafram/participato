<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: /sistema/login.php");
    exit();
}

$aguardando_aprovacao = isset($_SESSION["aguardando_aprovacao"]) && $_SESSION["aguardando_aprovacao"];
if ($aguardando_aprovacao) {
    unset($_SESSION["aguardando_aprovacao"]);
}

// Check if the user has a pending petition
$query = "SELECT id FROM abaixo_assinados WHERE status = 'pendente' AND id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$hasPendingPetition = $result->num_rows > 0;
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Criar Abaixo-Assinado - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/jr5azrsekth852dmtlbhhpicv6uzvkqn76qvngomcu1rsayk/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; ?>
<div class="container mt-5">

    <?php
    if ($aguardando_aprovacao) {
        echo '<div class="alert alert-info">Abaixo-Assinado registrado com sucesso! Aguardando aprovação do administrador.</div>';
    }

    if ($hasPendingPetition) {
        echo '<div class="alert alert-warning">Você possui um abaixo-assinado pendente. Aguarde até que ele seja aprovado para criar um novo.</div>';
    } else {
        ?>


    <form method="post" action="process_create_petition.php" enctype="multipart/form-data">
        <div id="step-1" class="step">
            <div class="form-group">
                <div class="alert alert-info" role="alert">
                    <center>
                        <b>Defina o Título da Sua Petição</b><p>
                    Comunique claramente sua intenção de transformação às pessoas</p>
                   </center>
                </div>
                <label for="titulo">Título da petição:</label>
                <input type="text" class="form-control" name="titulo" id="titulo" required maxlength="90">
                <span id="charCount">90 caracteres restantes</span>
            </div>
            <button type="button" class="btn btn-secondary next-step">Avançar</button>
        </div>

        <div id="step-2" class="step" style="display:none;">
            <div class="form-group">
                <div class="alert alert-info" role="alert">
                    <center>
                        <b>Compartilhe sua narrativa</b>
                        <p>Inicie do início ou siga a estrutura sugerida a seguir. Você terá a liberdade de modificar a sua
                            manifestação sempre que desejar, mesmo após torná-la pública.</p>
                    </center>
                </div>
                <label for="descricao">Descrição:</label>
                <textarea class="form-control" name="descricao" id="descricao" rows="5"></textarea>
            </div>
            <button type="button" class="btn btn-secondary next-step">Continuar</button>
            <button type="button" class="btn btn-secondary prev-step">Voltar</button> <!-- Botão Voltar aqui -->
        </div>

        <div id="step-3" class="step" style="display:none;">
            <div class="form-group">
                <div class="alert alert-info" role="alert">
                    <center><b>Estabeleça o Número de Apoios Necessários</b>
                        <p>Determine a quantidade de apoios requeridos para alcançar a meta estabelecida para esta petição; defina a sua meta.</p>
                    </center>
                </div>
                <label for="quantidade_assinaturas">Quantidade de Assinaturas Necessárias:</label>
                <select class="form-control" name="quantidade_assinaturas" id="quantidade_assinaturas" required>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="400">400</option>
                    <option value="500">500</option>
                    <option value="600">600</option>
                    <option value="700">700</option>
                    <option value="800">800</option>
                    <option value="900">900</option>
                    <option value="1000">1.000</option>
                    <option value="2000">2.000</option>
                    <option value="3000">3.000</option>
                    <option value="4000">4.000</option>
                    <option value="5000">5.000</option>
                    <option value="6000">6.000</option>
                    <option value="7000">7.000</option>
                    <option value="8000">8.000</option>
                    <option value="9000">9.000</option>
                    <option value="10000">10.000</option>
                    <option value="20000">20.000</option>
                    <option value="30000">30.000</option>
                    <option value="40000">40.000</option>
                    <option value="50000">50.000</option>
                    <option value="50000">100.000</option>
                    <option value="50000">200.000</option>
                    <option value="50000">300.000</option>
                    <option value="50000">400.000</option>
                    <option value="500000">500.000</option>
                </select>
            </div>
            <button type="button" class="btn btn-secondary next-step">Continuar</button>
            <button type="button" class="btn btn-secondary prev-step">Voltar</button> <!-- Botão Voltar aqui -->
        </div>

        <div id="step-4" class="step" style="display:none;">
            <div class="form-group">
                <div class="alert alert-info" role="alert">
                    <center>
                        <b>Adicione uma imagem</b>
                        <p>As petições com imagem conseguem seis vezes mais assinaturas.</p>
                    </center>
                </div>
                <div class="upload-imagem">
                <label for="imagem">Imagem:</label>
                <input type="file" class="form-control-file" name="imagem" id="imagem" accept=".png, .jpg, .jpeg">
                </div>
                <p style="margin:0 70px;">Imagens com pelo menos 1200 x 675 pixels podem ser visualizadas em todas as telas</p>
            </div>
            <button type="button" class="btn btn-secondary next-step">Continuar</button>
            <button type="button" class="btn btn-secondary prev-step">Voltar</button> <!-- Botão Voltar aqui -->
        </div>


        <div id="step-5" class="step" style="display:none;">
            <div class="form-group">
                <div class="alert alert-info" role="alert">
                <center>
                    <b>Adicione link para Grupo e Apoiar a Causa</b>
                    <p>Se você deseja que os defensores se envolvam em um grupo no Instagram ou WhatsApp, a fim de
                        acompanhar e respaldar a causa, por favor inclua o link para o grupo.</p>
                </center>
            </div>
                <label for="link_grupo">Link para Grupo do Telegram ou WhatsApp:</label>
                <input type="text" class="form-control" name="link_grupo" id="link_grupo">
            </div>
            <button type="button" class="btn btn-secondary next-step">Continuar</button>
            <button type="button" class="btn btn-secondary prev-step">Voltar</button> <!-- Botão Voltar aqui -->
        </div>

        <div id="step-6" class="step" style="display:none;">
            <div class="form-group">
                <div class="alert alert-info" role="alert">
                <center>
                    <b>Defina um Limite de Tempo para Conclusão</b>
                    <p>Estabeleça um prazo de conclusão, respeitando um período mínimo de 30 dias e máximo de 90 dias.</p>
                </center>
            </div>
                <label for="data_finalizacao">Data de Finalização:</label>
                <input type="date" class="form-control" name="data_finalizacao" id="data_finalizacao">
            </div>
            <button type="button" class="btn btn-secondary next-step">Continuar</button>
            <button type="button" class="btn btn-secondary prev-step">Voltar</button> <!-- Botão Voltar aqui -->
        </div>

        <div id="step-7" class="step" style="display:none;">
            <div class="form-group">
                <p style="font-weight: bold; font-size: 21px;">Seu abaixo-assinado está pronto</p>
                <div class="alert alert-primary" role="alert">
                <ul style="padding:5px;">
                    <li>Após salvar seu abaixo-assinado. Você poderá editá-lo.</li>
                    <li>Compartilhe com as pessoas que você conhece ou em comunidades na Internet.</li>
                    <li>Seu abaixo-assinado ficará visível na ParticipaTO depois de ser aprovado pelo Administrador.</li>
                </ul>
            </div>
            </div>
            <button type="submit" class="btn btn-primary">Salvar Petição</button>
            <button type="button" class="btn btn-secondary prev-step">Voltar</button>
        </div>

    </form>

        <?php
    }
    ?>

</div>
<div style="padding-bottom:40px;"></div>

<script>

    let currentStep = 1;
    const steps = document.querySelectorAll('.step');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const finalStepButtons = document.getElementById("final-step-buttons");

    nextButtons.forEach((button, index) => {
        button.addEventListener('click', () => {
            steps[index].style.display = 'none';
            steps[index + 1].style.display = 'block';
            currentStep++;
            if (currentStep === 7) {
                finalStepButtons.style.display = 'block';
            } else {
                finalStepButtons.style.display = 'none';
            }
        });
    });

    prevButtons.forEach((button, index) => {
        button.addEventListener('click', () => {
            steps[index + 1].style.display = 'none';
            steps[index].style.display = 'block';
            currentStep--;
            finalStepButtons.style.display = 'none';
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const titulo = document.getElementById("titulo");
        const charCount = document.getElementById("charCount");

        titulo.addEventListener("input", function() {
            const remaining = 90 - this.value.length;
            charCount.textContent = remaining + " caracteres restantes";
        });
    });


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
</body>
</html>
