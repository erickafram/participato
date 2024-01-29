<?php
session_start();
require_once "../includes/db_connection.php";

if (isset($_GET["politico_id"])) {
    $politico_id = $_GET["politico_id"];
    $query = "
        SELECT SUM(plo) as total_plo, SUM(req) as total_req
        FROM projetos_legislativos
        WHERE politico_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $politico_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $totals = $result->fetch_assoc();

    echo json_encode($totals);
} else {
    echo "";
}

// Consulta SQL para obter a pontuação de cada político com base em PLO e REQ
$query = "
    SELECT
        p.id AS politico_id,
        p.nome AS nome,
        p.cargo AS cargo,
        p.partido AS partido,
        p.caminho_imagem AS caminho_imagem,
        p.telefone AS telefone,
        p.email AS email,
        IFNULL(SUM(pl.plo), 0) * 0.5 + IFNULL(SUM(pl.req), 0) * 0.1 AS pontuacao
    FROM politicos p
    LEFT JOIN projetos_legislativos pl ON p.id = pl.politico_id
    GROUP BY p.id, p.nome, p.cargo, p.partido, p.caminho_imagem, p.telefone, p.email
    ORDER BY pontuacao DESC
";

$result = $conn->query($query);

?>

<?php include "../header.php"; // Inclua o arquivo de cabeçalho aqui ?>
<!DOCTYPE html>
<html>
<head>
    <title>Ranking de Políticos | Sua Plataforma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Ranking de Políticos</h2>

    <!-- Filtro de pesquisa por nome -->
    <div class="input-group mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Pesquisar por nome...">
    </div>

    <div class="row">
        <div class="col-md-6">
            <!-- Filtro de pesquisa por partido -->
            <div class="form-group">
                <label for="partidoFilter">Filtrar por Partido:</label>
                <select id="partidoFilter" class="form-control" onchange="filtrarPorPartido()">
                    <option value="">Todos os Partidos</option>
                    <?php
                    // Consulta SQL para obter todos os partidos distintos
                    $partidosQuery = "SELECT DISTINCT partido FROM politicos";
                    $partidosResult = $conn->query($partidosQuery);

                    if ($partidosResult->num_rows > 0) {
                        while ($partidoRow = $partidosResult->fetch_assoc()) {
                            echo "<option value='" . $partidoRow['partido'] . "'>" . $partidoRow['partido'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Opções de ordenação -->
            <div class="form-group">
                <label for="sortOrder">Ordenar por:</label>
                <select id="sortOrder" class="form-control" onchange="ordenarPoliticos()">
                    <option value="melhor_pior">Melhor para Pior</option>
                    <option value="pior_melhor">Pior para Melhor</option>
                </select>
            </div>
        </div>
    </div>

    <center><button style="margin-bottom:10px;" type="button" class="btn btn-info" data-toggle="modal" data-target="#pontuacaoAlert">
            Entenda como funciona os pontos
        </button></center>

    <ul class="list-group" id="politicosList">
        <?php
        $trofeu = true; // Inicializa a variável $trofeu como verdadeira (será usada para destacar o primeiro colocado)

        if ($result->num_rows > 0) {
            $posicao = 1;
            while ($row = $result->fetch_assoc()) {
                // Adicione a classe 'primeiro-colocado' à primeira linha da tabela
                $classePrimeiroColocado = $posicao === 1 ? 'primeiro-colocado' : '';

                // Defina classes CSS com base na posição do político
                $classeColocacao = '';
                if ($posicao >= 1 && $posicao <= 3) {
                    $classeColocacao = 'verde';
                } elseif ($posicao >= 4 && $posicao <= 12) {
                    $classeColocacao = 'laranja';
                } else {
                    $classeColocacao = 'vermelho';
                }

                echo "<style>
            .colocacao.verde {
                background-color: green;
            }
            .colocacao.laranja {
                background-color: orange;
            }
            .colocacao.vermelho {
                background-color: red;
            }
            .pontuacao-right {
                text-align: right; /* Alinha o texto à direita */
                padding-right: 10px; /* Adicione algum espaço à direita para afastar o texto da borda direita */
            }
             .list-group-item:hover {
                background-color: #f5f5f5; /* Cor de fundo ao passar o mouse */
            }
        </style>";

                echo "<li class='list-group-item $classePrimeiroColocado' data-nome='{$row['nome']}' data-partido='{$row['partido']}' onclick='abrirPopup(" . json_encode($row) . ")'>";
                echo "<div class='row'>";
                echo "<div class='col-md-2 politico-container'>";
                if ($trofeu) {
                    // Adicione um ícone de troféu apenas à primeira linha
                    echo "<div class='colocacao $classeColocacao'><i class='bi bi-trophy-fill'></i> {$posicao}°</div>";
                    $trofeu = false; // Define $trofeu como falso para não adicionar o ícone nas próximas linhas
                } else {
                    echo "<div class='colocacao $classeColocacao'>{$posicao}°</div>";
                }
                echo "<img class='politico-imagem' src='imagens/{$row['caminho_imagem']}' alt='Imagem do Político'>";
                echo "</div>";
                echo "<div class='col-md-6 politico-descricao'>";
                echo "<strong>{$row['nome']}</strong><br>{$row['cargo']} - {$row['partido']}";
                echo "</div>";
                echo "<div class='col-md-4 text-right'>";
                echo "<div class='pontuacao-right'><strong>Pontuação:</strong></div>";
                echo "<div class='pontuacao'>{$row['pontuacao']}</div>";
        echo "</div>";
        echo "</div>";
        echo "</li>";
        $posicao++;
    }
        } else {
            echo "<li class='list-group-item'>Nenhum político encontrado.</li>";
        }
        ?>

    </ul>
</div>

<!-- Modal (Alert) para a explicação da pontuação -->
<div class="modal fade" id="pontuacaoAlert" tabindex="-1" role="dialog" aria-labelledby="pontuacaoAlertLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pontuacaoAlertLabel">Pontuação Política: Entendendo a Lógica por Trás</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>A avaliação do desempenho político é possível por meio de uma métrica de pontuação que considera o envolvimento do político no processo legislativo. Essa pontuação é baseada em dois tipos de projetos legislativos:</p>
                <p>PLO (Projetos de Lei Ordinária), que envolvem a criação e apoio a novas leis, e REQ (Requerimentos), que refletem a atenção do político a questões políticas existentes.</p>

                <p>A fórmula de pontuação é simples: cada PLO contribui com 0.5 ponto, destacando o papel do político na criação de leis, e cada REQ contribui com 0,1 ponto, indicando seu envolvimento em questões políticas em andamento.</p>

                <p>Por exemplo, se um político patrocina 3 PLOs e 5 REQs, sua pontuação seria:</p>

                <ul>
                    <li style="margin-left:10px;">3 PLOs * 0.5 ponto = 1,5 pontos</li>
                    <li style="margin-left:10px;">5 REQs * 0,1 ponto = 0,3 pontos</li>
                </ul>

                <p>Portanto, a pontuação total do político seria 1,8 pontos.</p>

                <p>Essa métrica destaca o engajamento do político na produção de leis e na fiscalização do governo, fornecendo aos eleitores uma maneira objetiva de entender o trabalho de seus representantes no governo.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Código JavaScript para o popup -->
<script>
    function abrirPopup(politico) {
        var popup = document.createElement("div");
        popup.className = "popup_ranking";

        popup.innerHTML = `
        <div class="popup-content popup-content-ranking">
            <div class="popup-header">
                <button class="close-popup" onclick="fecharPopup()">&times;</button>
            </div>
            <div class="popup-body">
                <div class="popup-info row">
                    <div class="popup-image-container col-md-4">
                        <img src="imagens/${politico.caminho_imagem}" alt="Imagem do Político">
                    </div>
                    <div class="popup-details col-md-6">
                        <center style="padding-bottom:3px !important;">
                            <p style="font-size: 20px;"><strong><i class="bi bi-bar-chart-fill"></i> Pontuação:</strong> ${politico.pontuacao}</p>
                        </center>
                        <p><strong>Nome:</strong> ${politico.nome}</p>
                        <p><strong>Cargo/Partido:</strong> ${politico.cargo} - ${politico.partido}</p>
                        <p><strong><i class="bi bi-telephone-fill"></i></strong> ${politico.telefone}</p>
                        <p><strong><i class="bi bi-envelope-fill"></i></strong> ${politico.email}</p>
                    </div>
                </div>
            </div>
            <div class="popup-footer">
                <h3>Projetos Legislativos</h3>
                <ul id="projetos-legislativos-list">
                    <!-- As informações dos projetos legislativos serão preenchidas aqui -->
                </ul>
                <p><strong>Total de PLO e REQ todos os anos:</strong> <span id="total-plo-req"></span></p>
            </div>
        </div>
    `;
        document.body.appendChild(popup);

        // Execute a consulta para obter os projetos legislativos
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_projetos_legislativos.php?politico_id=" + politico.politico_id, true);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var projetosLegislativos = JSON.parse(xhr.responseText);
                var projetosLegislativosList = document.getElementById("projetos-legislativos-list");
                var totalPlo = 0;
                var totalReq = 0;

                projetosLegislativos.forEach(function (projeto) {
                    var accordion = document.createElement("div");
                    accordion.className = "accordion";
                    var year = projeto.ano;

                    accordion.innerHTML = `
            <button class="accordion-button">${year}</button>
            <div class="accordion-content">
                <p><strong>Ano:</strong> ${year}</p>
                <p><strong>Quantidade de PLO:</strong> ${projeto.quantidade_plo}</p>
                <p><strong>Quantidade de REQ:</strong> ${projeto.quantidade_req}</p>
                <p><strong>Total de PLO e REQ em  ${year}:</strong> ${projeto.total_plo_req}</p>
            </div>
        `;

                    projetosLegislativosList.appendChild(accordion);
                    totalPlo += parseInt(projeto.quantidade_plo, 10);
                    totalReq += parseInt(projeto.quantidade_req, 10);

                    // Adicionar evento de clique para expandir/recolher
                    var button = accordion.querySelector(".accordion-button");
                    button.addEventListener("click", function () {
                        var content = accordion.querySelector(".accordion-content");
                        content.classList.toggle("active");
                    });
                });

                // Exibir o total de PLO e REQ
                document.getElementById("total-plo-req").textContent = `${totalPlo + totalReq}`;
            }
        };

        xhr.send();

        popup.onclick = function (e) {
            if (e.target === popup) {
                fecharPopup();
            }
        };
    }

    function fecharPopup() {
        var popup = document.querySelector(".popup_ranking");
        if (popup) {
            popup.remove();
        }
    }

    // Filtrar políticos por partido
    function filtrarPorPartido() {
        var select = document.getElementById('partidoFilter');
        var selectedPartido = select.value.toUpperCase();
        var ul = document.getElementById('politicosList');
        var li = ul.getElementsByTagName('li');

        for (var i = 0; i < li.length; i++) {
            var partido = li[i].getAttribute('data-partido').toUpperCase();
            if (selectedPartido === '' || partido === selectedPartido) {
                li[i].style.display = '';
            } else {
                li[i].style.display = 'none';
            }
        }
    }


    // Selecionar o campo de entrada
    var input = document.getElementById('searchInput');

    // Adicionar um ouvinte de eventos para detectar a entrada do usuário
    input.addEventListener('input', function () {
        filtrarPoliticos();
    });

    // Função para filtrar políticos com base na entrada
    function filtrarPoliticos() {
        var input = document.getElementById('searchInput');
        var filter = input.value.toUpperCase();
        var ul = document.getElementById('politicosList');
        var li = ul.getElementsByTagName('li');

        for (var i = 0; i < li.length; i++) {
            var nome = li[i].getAttribute('data-nome');
            if (nome.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = '';
            } else {
                li[i].style.display = 'none';
            }
        }
    }

    // Filtrar políticos por nome
    function filtrarPoliticos() {
        var input = document.getElementById('searchInput');
        var filter = input.value.toUpperCase();
        var ul = document.getElementById('politicosList');
        var li = ul.getElementsByTagName('li');

        for (var i = 0; i < li.length; i++) {
            var nome = li[i].getAttribute('data-nome');
            if (nome.toUpperCase().indexOf(filter) > -1) {
                li[i].style.display = '';
            } else {
                li[i].style.display = 'none';
            }
        }
    }

    // Ordenar políticos
    function ordenarPoliticos() {
        var sortOrder = document.getElementById('sortOrder').value;
        var ul = document.getElementById('politicosList');
        var li = ul.getElementsByTagName('li');

        var sortedLi = Array.from(li).sort(function (a, b) {
            var pontuacaoA = parseFloat(a.querySelector('.pontuacao').textContent);
            var pontuacaoB = parseFloat(b.querySelector('.pontuacao').textContent);

            if (sortOrder === 'melhor_pior') {
                return pontuacaoB - pontuacaoA;
            } else {
                return pontuacaoA - pontuacaoB;
            }
        });

        for (var i = 0; i < sortedLi.length; i++) {
            ul.appendChild(sortedLi[i]);
        }
    }
</script>
</body>
</html>
