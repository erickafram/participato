<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: /sistema/login.php");
    exit();
}
$user_id = $_SESSION["user_id"];
$errors = [];
$successMessage = '';

// Busca os dados atuais do usuário para preenchimento automático do formulário
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard do Usuário - Minha Plataforma</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include_once "../header.php"; // Inclua o cabeçalho ?>
<div class="container mt-5">
    <div class="user-profile" style="font-size: 20px; padding-top: 20px; padding: 0 5px 15px; border:1px solid #eee;">
        <div style="display: flex; align-items: center;">
            <!-- Container da imagem e do botão de configurações -->
            <div style="position: relative; margin-right: 10px;">
                <!-- Exibição da imagem de perfil ou ícone padrão -->
                <?php if ($user_data['imagem_perfil']): ?>
                    <img class="profile-image" src="../user/profile/<?php echo htmlspecialchars($user_data['imagem_perfil']); ?>" alt="Imagem de Perfil" style="width:128px;height:128px; object-fit: cover;">
                <?php else: ?>
                    <img src="../user/profile/avatar.png" alt="Avatar Padrão" style="width:128px;height:128px; object-fit: cover;">
                <?php endif; ?>

                <!-- Botão de configurações do perfil sobre a imagem -->
                <div class="dropdown" style="position: absolute; bottom: 0; right: 9px;">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-camera"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="../user/editar_perfil.php">Editar Perfil</a>
                        <a class="dropdown-item" href="#" onclick="document.getElementById('fileUpload').click(); return false;">Fazer Upload de Imagem</a>
                        <?php if ($user_data['imagem_perfil']): ?>
                            <a class="dropdown-item" href="#" onclick="document.getElementById('deleteForm').submit(); return false;">Excluir Imagem</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Exibição do nome do usuário -->
            <p style="margin: 0;">Olá, <?php echo htmlspecialchars($user_data['nome_usuario']); ?>!</p>
        </div>

        <!-- Formulário oculto para upload de imagem -->
        <form method="post" action="../user/processar_upload_imagem.php" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="fileUpload" name="imagem_perfil" onchange="this.form.submit();">
        </form>

        <!-- Formulário oculto para exclusão de imagem -->
        <?php if ($user_data['imagem_perfil']): ?>
            <form id="deleteForm" method="post" action="../user/excluir_imagem.php" style="display: none;"></form>
        <?php endif; ?>
    </div>

    <!-- Atalhos para criar petições e vaquinhas -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Criar Petições</h5>
                    <p class="card-text">Crie abaixo-assinados para as causas que você apoia.</p>
                    <a href="../user/create_petition.php" class="btn btn-primary">
                        <i class="fas fa-pen"></i> Criar Petição
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Criar Vaquinhas</h5>
                    <p class="card-text">Inicie campanhas de arrecadação de fundos para suas causas.</p>
                    <a href="/sistema/vaquinha/create_crowdfund.php" class="btn btn-primary">
                        <i class="fas fa-hand-holding-usd"></i> Criar Vaquinha
                    </a>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mt-4">Cadastro Administrador</h5>
    <div class="list-group mt-3" style="padding-bottom:25px;">
        <a href="editar_perfil.php" class="list-group-item list-group-item-action">
            <i class="fas fa-user-edit"></i> Editar Perfil
        </a>
        <a href="cadastro_dados_bancarios.php" class="list-group-item list-group-item-action">
            <i class="fas fa-money-check-alt"></i> Cadastrar Conta Bancária para Receber Fundos
        </a>
    </div>

    <h5 class="mt-4">Atalhos Rápidos Administrador</h5>
    <div class="row mt-3" style="padding-bottom:25px;">
        <div class="col-md-4">
            <a href="../noticias/cadastro_noticia.php" class="list-group-item list-group-item-action">
                <i class="fas fa-newspaper"></i> Cadastrar Notícias
            </a>
            <a href="../noticias/listar_noticias.php" class="list-group-item list-group-item-action">
                <i class="fas fa-list"></i> Listar Notícias
            </a>
            <a href="../admin/petition_pedding.php" class="list-group-item list-group-item-action">
                <i class="fas fa-file-alt"></i> Petições Pendentes
            </a>
            <a href="../admin/petitions_list.php" class="list-group-item list-group-item-action">
                <i class="fas fa-list-alt"></i> Lista de Petições
            </a>
            <a href="../admin/adicionar_assinaturas.php" class="list-group-item list-group-item-action">
                <i class="fas fa-pen-square"></i> Adicionar Assinaturas
            </a>
        </div>
        <div class="col-md-4">
            <a href="../vaquinha/approve_crowdfunds.php" class="list-group-item list-group-item-action">
                <i class="fas fa-check"></i> Aprovar Vaquinha
            </a>
            <a href="../vaquinha/all_crowdfunds.php" class="list-group-item list-group-item-action">
                <i class="fas fa-list-ul"></i> Vaquinhas Cadastradas
            </a>
            <a href="../admin/approve_donations.php" class="list-group-item list-group-item-action">
                <i class="fas fa-check-square"></i> Aprovar Doações
            </a>
            <a href="../admin/list_approved_donations.php" class="list-group-item list-group-item-action">
                <i class="fas fa-list-ol"></i> Lista de Doações
            </a>
        </div>
        <div class="col-md-4">
            <a href="../enquete/create_poll.php" class="list-group-item list-group-item-action">
                <i class="fas fa-poll"></i> Criar Enquete
            </a>
            <a href="../enquete/list_poll.php" class="list-group-item list-group-item-action">
                <i class="fas fa-clipboard-list"></i> Lista de Enquetes
            </a>
            <a href="../ranking/cadastro_politico.php" class="list-group-item list-group-item-action">
                <i class="fas fa-user-tie"></i> Cadastrar Políticos
            </a>
            <a href="../ranking/lista_politicos.php" class="list-group-item list-group-item-action">
                <i class="fas fa-users"></i> Listar Políticos
            </a>
        </div>
    </div>



    <h5 class="mt-4">Atalhos Rápidos Sistema</h5>
    <div class="list-group mt-3" style="padding-bottom:25px;">
        <a href="/sistema/index.php" class="list-group-item list-group-item-action">
            <i class="fas fa-file-signature"></i> Petições em Andamento
        </a>
        <a href="/sistema/vaquinha/index.php" class="list-group-item list-group-item-action">
            <i class="fas fa-hands-helping"></i> Vaquinhas Abertas
        </a>
        <a href="/sistema/ranking/ranking_politicos.php" class="list-group-item list-group-item-action">
            <i class="fas fa-chart-bar"></i> Ranking de Políticos
        </a>
        <a href="/sistema/enquete/index.php" class="list-group-item list-group-item-action">
            <i class="fas fa-poll"></i> Enquetes
        </a>
    </div>

    <!-- <h5 class="mt-4">Petições que eu Assinei</h5>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
    $user_id = $_SESSION["user_id"];

    // Consulta para obter abaixo-assinados assinados pelo usuário logado
    $query = "SELECT abaixo_assinados.id, abaixo_assinados.titulo
                    FROM abaixo_assinados
                    INNER JOIN assinaturas ON abaixo_assinados.id = assinaturas.id_abaixo_assinado
                    WHERE assinaturas.id_usuario = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row["id"] . '</td>';
            echo '<td>' . $row["titulo"] . '</td>';
            echo '<td><a href="../peticao/petition_details.php?id=' . $row["id"] . '">Ver detalhes</a></td>';
            echo '</tr>';
        }
    } else {
        echo "<tr><td colspan='3'>Nenhum abaixo-assinado assinado por você.</td></tr>";
    }
    $stmt->close();
    ?>
        </tbody>
    </table> -->
</div>
</body>
</html>
