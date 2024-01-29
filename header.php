<?php
require_once "includes/db_connection.php";

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];

// Consulta para calcular o saldo total e verificar se a vaquinha está finalizada
    $sql_saldo = "
    SELECT 
        SUM(CASE WHEN d.status = 'Aprovado' THEN d.valor ELSE 0 END) AS arrecadado,
        (CASE WHEN COUNT(v.id) > 0 AND v.status = 'finalizada' THEN 1 ELSE 0 END) AS vaquinha_finalizada
    FROM 
        vaquinhas v
        LEFT JOIN doacoes d ON v.id = d.id_vaquinha
    WHERE 
        v.id_usuario = ?
    GROUP BY 
        v.status
";
    $stmt_saldo = $conn->prepare($sql_saldo);
    $stmt_saldo->bind_param("i", $user_id);
    $stmt_saldo->execute();
    $result_saldo = $stmt_saldo->get_result();
    $row_saldo = $result_saldo->fetch_assoc();

    $vaquinhaFinalizada = $row_saldo["vaquinha_finalizada"] ?? 0;
    $totalArrecadado = $vaquinhaFinalizada ? 0 : ($row_saldo["arrecadado"] ?? 0);

    $desconto = 0.15; // 15%
    $totalRepassar = $totalArrecadado * (1 - $desconto);
    $stmt_saldo->close();

}

// Consulta para obter as últimas 5 doações para as vaquinhas criadas pelo usuário logado
$sql_ultimas_doacoes = "SELECT d.*, v.titulo FROM doacoes d
                            LEFT JOIN vaquinhas v ON d.id_vaquinha = v.id
                            WHERE d.status = 'Aprovado' AND v.id_usuario = ?
                            ORDER BY d.data DESC LIMIT 5";
$stmt_ultimas_doacoes = $conn->prepare($sql_ultimas_doacoes);
$stmt_ultimas_doacoes->bind_param("i", $user_id);
$stmt_ultimas_doacoes->execute();
$result_ultimas_doacoes = $stmt_ultimas_doacoes->get_result();
$total_notificacoes = $result_ultimas_doacoes->num_rows;

// consultar para saber quantidade de doacoes pendentes
$sql_count_pendentes = "SELECT COUNT(*) as num_pendentes FROM doacoes WHERE status = 'Pendente'";
$result_count = $conn->query($sql_count_pendentes);
$row_count = $result_count->fetch_assoc();
$num_pendentes = $row_count['num_pendentes'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Participa Tocantins</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> <!-- Versão completa do jQuery -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <link rel="icon" href="/sistema/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/sistema/assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/jr5azrsekth852dmtlbhhpicv6uzvkqn76qvngomcu1rsayk/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-HY20QCEVPK"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-HY20QCEVPK');
    </script>
</head>
<body>
<div id="progress-bar"></div>
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top" style="background-color: #ffffff !important;">
    <div class="container">
        <a class="navbar-brand" href="/sistema/index.php">
            <img src="/sistema/assets/imagem_site/logo.png" alt="Logo" width="120" height="auto" class="d-inline-block align-top">
        </a>
        <?php if (isset($_SESSION["user_tipo"]) && ($_SESSION["user_tipo"] == "usuario" || $_SESSION["user_tipo"] == "administrador")): ?>
        <!-- Ícone de notificações para dispositivos móveis -->
        <div class="d-lg-none mobile-notification-icon">
            <a href="#" class="nav-link" id="mobileAlertsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <span class="badge badge-danger badge-counter" id="mobileNotificationCounter"><?php echo $total_notificacoes; ?></span>
            </a>
            <div class="dropdown-menu" aria-labelledby="mobileAlertsDropdown">
                <h6 class="dropdown-header">Centro de Alertas</h6>
                <?php foreach ($result_ultimas_doacoes as $doacao): ?>
                    <a class="dropdown-item d-flex align-items-center notification-item" href="#">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-donate text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500"><?php echo date('d/m/Y', strtotime($doacao['data'])); ?></div>
                            <span class="font-weight-bold" style="font-size:12px;">Doação recebida R$ <?php echo number_format($doacao['valor'], 2, ',', '.'); ?></span>
                            <div class="nav-divider"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <a class="dropdown-item text-center small text-gray-500" href="/sistema/todos_os_alertas.php">Ver todos os alertas</a>
            </div>
        </div>
        <?php endif; ?>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <!-- Saldo para dispositivos móveis -->
                <?php if (isset($_SESSION["user_id"])): ?>
                    <div class="d-block d-lg-none" style="background-color: #4c9d60; padding: 5px 10px; border-radius: 3px; margin-top: 10px;">
                        <a href="/sistema/vaquinha/my_crowdfunds.php" class="navbar-link" style="color:white; font-weight: bold; font-size: 13px;">
                            Saldo: <span id="mobile-saldo-valor">R$ <?php echo number_format($totalRepassar, 2, ',', '.'); ?></span>
                            <span id="mobile-saldo-oculto" style="display: none;">R$ ***,**</span>
                        </a>
                        <i id="mobile-toggle-saldo" class="fas fa-eye-slash" style="color: white; cursor: pointer;"></i>
                    </div>
                <?php endif; ?>
                <!-- FIM Saldo para dispositivos móveis -->
                <div class="nav-divider"></div>
                <?php
                // Para usuarios administrador
                if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "administrador") {
                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Notícias
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <a class="dropdown-item" href="/sistema/noticias/cadastro_noticia.php">Cadastrar Notícias</a>
                            <a class="dropdown-item" href="/sistema/noticias/listar_noticias.php">Lista Notícias</a>
                            <a class="dropdown-item" href="/sistema/noticias/index.php">Todas Notícias</a>
                        </div>
                    </li>';
                }
                // PARA USUARIOS LOGADOS
                else if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "usuario") {
                    echo '
                  <li class="nav-item">
                    <a class="nav-link" href="/sistema/user/dashboard.php">Inicio</a>
                </li>';
                }
                // PARA USUARIOS NÃO LOAGADOS
                else echo '
                <li class="nav-item">
                    <a class="nav-link" href="/sistema/noticias/index.php">Notícias</a>
                </li> ';
                ?>

                <?php
                // PARA ADMINISTRADOR
                if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "administrador") {
                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Petições
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <a class="dropdown-item" href="/sistema/user/create_petition.php">Criar Petição</a>
                            <a class="dropdown-item" href="/sistema/admin/petition_pedding.php">Petições Pedentes</a>
                            <a class="dropdown-item" href="/sistema/admin/petitions_list.php">Lista de Petições</a>
                            <a class="dropdown-item" href="/sistema/admin/adicionar_assinaturas.php">Adcionar Assinaturas</a>
                            <a class="dropdown-item" href="/sistema/peticoes.php">Todas Petições</a>
                        </div>
                    </li>';
                }
                // PARA USUARIOS LOGADOS
                else if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "usuario") {
                    echo '
                 <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Petições
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                         <a class="dropdown-item" href="/sistema/user/create_petition.php">Criar Petição</a>
                            <a class="dropdown-item" href="/sistema/user/petition_pending.php">Petições Pendentes</a>
                            <a class="dropdown-item" href="/sistema/user/my_petitions.php">Minhas Petições</a>
                            <a class="dropdown-item" href="/sistema/user/petition_assinada.php">Petições Assinadas</a>
                            <a class="dropdown-item" href="/sistema/peticoes.php"> Todas Petições</a>
                    </div>
                </li>';
                }
                // PARA USUARIOS NÃO LOAGADOS
                else echo '
                        <li class="nav-item">
                    <a class="nav-link" href="/sistema/peticoes.php">Petições</a>
                </li> ';
                ?>

                <?php
                // Para usuarios administrador
                if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "administrador") {
                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Vaquinha
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                        <a class="dropdown-item" href="/sistema/vaquinha/create_crowdfund.php">Cadastrar Vaquinha</a>
                        <a class="dropdown-item" href="/sistema/vaquinha/process_approval.php">Aprovar Vaquinha</a>
                        <a class="dropdown-item" href="/sistema/vaquinha/all_crowdfunds.php">Vaquinha Cadastradas</a>
                        <a class="dropdown-item" href="/sistema/admin/approve_donations.php">
                        Aprovar Doações 
                        <span class="badge badge-danger">' . $num_pendentes . '</span>
                        </a>                        
                        <a class="dropdown-item" href="/sistema/admin/list_approved_donations.php">Listar Doações</a>
                        <a class="dropdown-item" href="/sistema/admin/notify_pending_donations.php">Enviar E-mails</a>
                        <a class="dropdown-item" href="/sistema/vaquinha/index.php">Todas Vaquinhas</a>
                        </div>
                    </li>';
                }
                // PARA USUARIOS LOGADOS
                else if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "usuario") {
                    echo '
                 <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Vaquinha
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <a class="dropdown-item" href="/sistema/vaquinha/create_crowdfund.php">Criar Vaquinha</a>
                            <a class="dropdown-item" href="/sistema/vaquinha/my_crowdfunds.php">Minhas Vaquinha</a>
                            <!-- <a class="dropdown-item" href="/sistema/vaquinha/minhas_doacoes.php">Minhas Doações</a> -->
                            <a class="dropdown-item" href="/sistema/vaquinha/list_crowdfunds.php">Fazer Doação</a>
                            <a class="dropdown-item" href="/sistema/vaquinha/index.php">Todas Vaquinhas</a>
                    </div>
                </li>';
                }
                // PARA USUARIOS NÃO LOAGADOS
                else echo '
                        <li class="nav-item">
                    <a class="nav-link" href="/sistema/vaquinha/index.php">Vaquinhas</a>
                </li> ';
                ?>

                <?php
                // Para usuarios administrador
                if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "administrador") {
                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Enquetes
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                        <a class="dropdown-item" href="/sistema/enquete/create_poll.php">Criar Enquete</a>
                        <a class="dropdown-item" href="/sistema/enquete/list_poll.php">Listar Enquetes</a> 
                        <a class="dropdown-item" href="/sistema/enquete/view_results.php">Ver Resultados</a>
                        <a class="dropdown-item" href="/sistema/enquete/index.php">Todas Enquetes</a>
                        </div>
                    </li>';
                }

                // Para outros usuarios que não seja administrador
                else echo '
                 <li class="nav-item">
                    <a class="nav-link" href="/sistema/enquete/index.php">Enquetes</a>
                </li>
                ';
                ?>

                <?php
                // Para usuarios administrador
                if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "administrador") {
                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Ranking
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                       <a class="dropdown-item" href="/sistema/ranking/ranking_politicos.php">Ranking de Políticos</a>
                        <a class="dropdown-item" href="/sistema/ranking/comparar_politicos.php">Comparar Políticos</a>
                        <div class="nav-divider"></div> <!-- Adicione a linha separadora aqui -->
                                <a class="dropdown-item" href="/sistema/ranking/cadastro_politico.php">Cadastrar Politico</a>
                                <a class="dropdown-item" href="/sistema/ranking/lista_politicos.php">Listar Politico</a>
                                <a class="dropdown-item" href="/sistema/ranking/lista_projetos_legislativos.php">Lista de Projetos Legislativos</a>
                        </div>
                    </li>';
                }
                // Para outros usuarios que não seja administrador
                else echo '
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="rankingDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Ranking
                    </a>
                    <div class="dropdown-menu" aria-labelledby="rankingDropdown">
                        <a class="dropdown-item" href="/sistema/ranking/ranking_politicos.php">Ranking de Políticos</a>
                        <a class="dropdown-item" href="/sistema/ranking/comparar_politicos.php">Comparar Políticos</a>
                    </div>
                </li>
                ';
                ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="rankingDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Institucional
                    </a>
                    <div class="dropdown-menu" aria-labelledby="rankingDropdown">
                        <a class="dropdown-item" href="/sistema/sobre_nos.php">Sobre Nós</a>
                        <a class="dropdown-item" href="/sistema/doacao.php">Fazer Doação</a>
                        <a class="dropdown-item" href="/sistema/duvidas.php">Dúvidas?</a>
                        <a class="dropdown-item" href="/sistema/contato.php">Contato</a>
                    </div>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <?php
                if (isset($_SESSION["user_id"])) {
                    echo '
             <div class="saldo_vaquinha">
             <li class="nav-item">
             <a href="/sistema/vaquinha/my_crowdfunds.php" class="navbar-link" style="color:white; font-weight: bold;font-size: 13px;">
            Saldo: <span id="saldo-valor" style="display: none;">R$ ' . number_format($totalRepassar, 2, ',', '.') . '</span>
            <span id="saldo-oculto">R$ ***,**</span>
               </a>
               <i id="toggle-saldo" class="fas fa-eye-slash" style="color: white; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
               </li> 
               </div>  
               ';} ?>

                <?php if (isset($_SESSION["user_tipo"]) && ($_SESSION["user_tipo"] == "usuario" || $_SESSION["user_tipo"] == "administrador")): ?>
                    <!-- Ícone de notificações para Desktop -->
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell fa-fw"></i>
                            <span class="badge badge-danger badge-counter" id="notificationCounter"><?php echo $total_notificacoes; ?></span>
                        </a>
                        <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                            <h6 class="dropdown-header">Centro de Alertas</h6>
                            <?php foreach ($result_ultimas_doacoes as $doacao): ?>
                                <a class="dropdown-item d-flex align-items-center notification-item" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500"><?php echo date('d/m/Y', strtotime($doacao['data'])); ?></div>
                                        <span class="font-weight-bold" style="font-size:12px;">Doação recebida R$ <?php echo number_format($doacao['valor'], 2, ',', '.'); ?></span>
                                        <div class="nav-divider"></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                            <a class="dropdown-item text-center small text-gray-500" href="/sistema/todos_os_alertas.php">Ver todos os alertas</a>
                        </div>
                    </li>
                <?php endif; ?>

                <?php
                if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "usuario") {
                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Minha Conta
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <span class="dropdown-text">Perfil</span> <!-- Texto descritivo -->
                            <a class="dropdown-item" href="/sistema/user/dashboard.php">Dashboard</a>
                            <div class="nav-divider"></div> <!-- Adicione a linha separadora aqui -->
                            <a class="dropdown-item" href="/sistema/logout.php">Sair</a>
                        </div>
                    </li>';
                }

                if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] == "administrador") {
                    echo '
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Minha Conta
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                            <a class="dropdown-item" href="/sistema/admin/dashboard.php">Dashboard admin</a>
                            <a class="dropdown-item" href="/sistema/admin/listar_usuarios_notificacoes.php">Usuários Notificações</a>
                            <a class="dropdown-item" href="/sistema/admin/admin_aprovar_doacoes.php">Doações Turbinar</a>
                            <a class="dropdown-item" href="/sistema/admin/enquete_admin.php">Lista de Votos</a>
                                <div class="nav-divider"></div> <!-- Adicione a linha separadora aqui -->
                                <a class="dropdown-item" href="/sistema/admin/listar_usuarios.php">Lista Usuários</a>
                                <div class="nav-divider"></div> <!-- Adicione a linha separadora aqui -->     
                                <a class="dropdown-item" href="/sistema/logout.php">Sair</a>
                        </div>
                    </li>';
                }

                if (!isset($_SESSION["user_id"])) {
                    echo '
                    <li class="nav-item">
                         <a class="nav-link btn btn-light" href="/sistema/login.php" style="color: rgb(0 0 0 / 85%); padding: 10px 20px;border: 1px solid #ebebeb;">
                         <i class="fas fa-user"></i> Entrar
                         </a>
                    </li>';
                } else {
                    echo '';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>
<div style="padding-bottom:30px;"></div>
<script>

    document.addEventListener('DOMContentLoaded', function() {
        const notificationItems = document.querySelectorAll('.notification-item');
        const notificationCounter = document.getElementById('notificationCounter');
        const mobileNotificationCounter = document.getElementById('mobileNotificationCounter'); // Referência ao contador móvel

        // Carregar o estado inicial das notificações e da contagem
        const readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');
        let unreadCount = parseInt(localStorage.getItem('unreadNotificationCount') || '<?php echo $total_notificacoes; ?>');

        notificationItems.forEach((item, index) => {
            if (readNotifications.includes(index)) {
                item.classList.add('read');
            }

            item.addEventListener('click', function() {
                if (!this.classList.contains('read')) {
                    this.classList.add('read');
                    readNotifications.push(index);
                    localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
                    unreadCount = Math.max(0, unreadCount - 1);
                    localStorage.setItem('unreadNotificationCount', unreadCount.toString());
                    updateNotificationCount();
                }
            });
        });

        // Atualizar a contagem de notificações
        function updateNotificationCount() {
            notificationCounter.innerText = unreadCount > 0 ? unreadCount + '+' : '0';
            mobileNotificationCounter.innerText = unreadCount > 0 ? unreadCount + '+' : '0'; // Atualiza também no contador móvel
        }

        // Inicializar a contagem de notificações na página
        updateNotificationCount();
    });

    document.addEventListener('DOMContentLoaded', function() {
        var mobileToggleSaldo = document.getElementById('mobile-toggle-saldo');
        var mobileSaldoValor = document.getElementById('mobile-saldo-valor');
        var mobileSaldoOculto = document.getElementById('mobile-saldo-oculto');
        var vaquinhaFinalizada = <?php echo $vaquinhaFinalizada; ?>;

        // Configura o ícone inicial para olho sem risco, pois o saldo está oculto
        mobileToggleSaldo.classList.add('fa-eye');
        mobileToggleSaldo.classList.remove('fa-eye-slash');

        // Oculta o saldo e mostra o saldo oculto
        mobileSaldoValor.style.display = 'none';
        mobileSaldoOculto.style.display = 'inline';

        mobileToggleSaldo.addEventListener('click', function() {
            if (mobileSaldoValor.style.display === 'none') {
                mobileSaldoValor.innerHTML = vaquinhaFinalizada ? 'R$ 0,00' : 'R$ <?php echo number_format($totalRepassar, 2, ',', '.'); ?>'; // Alterado aqui
                mobileSaldoValor.style.display = 'inline';
                mobileSaldoOculto.style.display = 'none';
                this.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                mobileSaldoValor.style.display = 'none';
                mobileSaldoOculto.style.display = 'inline';
                this.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    window.onscroll = function() { scrollFunction() };
    function scrollFunction() {
        var scrollTop = document.documentElement.scrollTop;
        var scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var scrollPercentage = (scrollTop / scrollHeight) * 100;
        document.getElementById("progress-bar").style.width = scrollPercentage + "%";
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggleSaldo = document.getElementById('toggle-saldo');
        var saldoValor = document.getElementById('saldo-valor');
        var saldoOculto = document.getElementById('saldo-oculto');
        var vaquinhaFinalizada = <?php echo $vaquinhaFinalizada; ?>; // Adicione esta linha

        // Configura o ícone inicial com base na visibilidade do saldo
        if (saldoValor.style.display === 'none') {
            toggleSaldo.classList.add('fa-eye');
            toggleSaldo.classList.remove('fa-eye-slash');
        } else {
            toggleSaldo.classList.add('fa-eye-slash');
            toggleSaldo.classList.remove('fa-eye');
        }

        toggleSaldo.addEventListener('click', function() {
            if (saldoValor.style.display === 'none') {
                saldoValor.innerHTML = vaquinhaFinalizada ? 'R$ 0,00' : 'R$ <?php echo number_format($totalRepassar, 2, ',', '.'); ?>'; // Alterado aqui
                saldoValor.style.display = 'inline';
                saldoOculto.style.display = 'none';
                this.classList.replace('fa-eye', 'fa-eye-slash'); // Ícone com risco quando o saldo é visível
            } else {
                saldoValor.style.display = 'none';
                saldoOculto.style.display = 'inline';
                this.classList.replace('fa-eye-slash', 'fa-eye'); // Ícone sem risco quando o saldo é oculto
            }
        });
    });
</script>
</body>
</html>