<?php
require_once "includes/db_connection.php"; // Inclua o arquivo de conexão

// Número de registros por página
$recordsPerPage = 6;

if (isset($_POST['page'])) {
    $currentPage = $_POST['page'];
} else {
    $currentPage = 1;
}

$offset = ($currentPage - 1) * $recordsPerPage;

if (isset($_POST['search'])) {
    $search = $_POST['search'];
    $showVictorious = isset($_POST['showVictorious']) ? $_POST['showVictorious'] : 0;

    // Check if the search term is the same as the previous search term
    if (isset($_SESSION['last_search']) && $_SESSION['last_search'] === $search) {
        // Maybe do something different here or just return
        return;
    }

    $query = "SELECT a.id, a.titulo, a.descricao, a.caminho_imagem, a.quantidade_assinaturas, COUNT(s.id) AS assinaturas_atuais, a.data_finalizacao, u.nome_usuario, a.status
      FROM abaixo_assinados a
      LEFT JOIN assinaturas s ON a.id = s.id_abaixo_assinado
      INNER JOIN usuarios u ON a.id_usuario = u.id
      WHERE a.status IN ('aprovado', 'finalizado') AND a.titulo LIKE '%$search%'
      GROUP BY a.id, a.titulo, a.descricao, a.caminho_imagem, a.quantidade_assinaturas, a.data_finalizacao, u.nome_usuario";

    // Adiciona o filtro para mostrar apenas petições vitoriosas
    if ($showVictorious) {
        $query .= " HAVING assinaturas_atuais >= quantidade_assinaturas";
    }

    // Adicionar limit e offset para paginação
    $query .= " LIMIT $offset, $recordsPerPage";

    // Executa a query
    $result = $conn->query($query);

    // Verifica se a query foi bem-sucedida
    if (!$result) {
        die("Erro na query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calcula a quantidade de dias restantes
            $hoje = new DateTime();
            $hoje->setTime(0, 0);

            $data_finalizacao = new DateTime($row["data_finalizacao"]);
            $diferenca = $hoje->diff($data_finalizacao);
            $dias_restantes = $diferenca->days;

            // Se a data de finalização já passou, pule para a próxima iteração
            if ($hoje > $data_finalizacao) {
                continue;
            }

            // Calcula a porcentagem de progresso
            $progress = ($row["assinaturas_atuais"] / $row["quantidade_assinaturas"]) * 100;

            echo '<div class="col-md-4" data-id="' . $row["id"] . '">';
            echo '<a href="peticao/petition_details.php?id=' . $row["id"] . '">';
            echo '<div class="card-peticao">';
            echo '<div class="card petition-card">';
            echo '<img src="assets/' . $row["caminho_imagem"] . '" class="card-img-top" alt="...">';

            // Verificar se a petição está finalizada
            if ($row["status"] === "finalizado") {
                echo '<div class="victory-badge"><i class="bi bi-flag"></i> Vitória</div>'; // Marcar como finalizado
            } elseif ($row["assinaturas_atuais"] >= $row["quantidade_assinaturas"]) {
                echo '<div class="victory-badge"><i class="bi bi-flag"></i> Meta Atingida!</div>'; // Quadrado de vitória
            }

            echo '<div class="card-body">';
            echo '<h5 class="card-title crowdfund-title limited-title">' . $row["titulo"] . '</h5>';

            // Barra de progresso
            echo '<div class="progress mb-3">';
            echo '<div class="progress-bar" role="progressbar" style="width: ' . $progress . '%;" aria-valuenow="' . $progress . '" aria-valuemin="0" aria-valuemax="100">';
            echo '<span class="progress-label">' . round($progress, 2) . '%</span>'; // Arredonda a porcentagem para duas casas decimais
            echo '</div>';
            echo '</div>';

            echo '<div class="sessao-dados">';
            if ($dias_restantes == 0) {
                echo '<p><strong style="color:#c50000">Encerra hoje</strong></p>';
            } else {
                echo '<p><strong>Dias Restantes:</strong> ' . $dias_restantes . '</p>';
            }
            echo '<p><strong>Apoiadores:</strong> ' . number_format($row["assinaturas_atuais"], 0, ',', '.') . '/' . number_format($row["quantidade_assinaturas"], 0, ',', '.') . '</p>';

            echo '</div>';
            echo '</div>';
            echo '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo "<p>Nenhum resultado encontrado.</p>";
    }
} else {
    echo "<p>Nenhum termo de pesquisa fornecido.</p>";
}

$conn->close();
?>
