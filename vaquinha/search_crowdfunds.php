<?php
session_start();
require_once "../includes/db_connection.php";

if (isset($_POST['search'])) {
    $searchValue = $_POST['search'];

    $sql = "SELECT * FROM vaquinhas WHERE (status = 'aberto' OR status = 'finalizada') AND aprovacao = 'aprovado' AND (titulo LIKE ? OR descricao LIKE ?)";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%{$searchValue}%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Consulta para obter a soma dos valores doados com status "Aprovado"
            $sql_soma_arrecadado = "SELECT SUM(valor) AS soma_arrecadado FROM doacoes WHERE id_vaquinha = ? AND status = 'Aprovado'";
            $stmt_soma_arrecadado = $conn->prepare($sql_soma_arrecadado);
            $stmt_soma_arrecadado->bind_param("i", $row['id']);
            $stmt_soma_arrecadado->execute();
            $result_soma_arrecadado = $stmt_soma_arrecadado->get_result();
            $soma_arrecadado = $result_soma_arrecadado->fetch_assoc();
            $valor_arrecadado = $soma_arrecadado['soma_arrecadado'] ?? 0;

            $meta = $row["meta"];
            $porcentagem = ($valor_arrecadado / $meta) * 100;

            echo '<div class="col-md-4 mb-4">';
            echo '<a href="view_crowdfund.php?id=' . $row["id"] . '" class="crowdfund-link" style="color:black;">';
            echo '<div class="card-vaquinha">';
            echo '<div class="card petition-card">';
            echo '<img src="images/' . $row["imagem"] . '" class="card-img-top" alt="Imagem da Vaquinha">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title crowdfund-title limited-title">' . $row["titulo"] . '</h5>';
            echo '<div class="verificado">';
            echo '<i class="bi bi-check-circle"></i> História Verificada';
            echo '</div>';
            echo '</a>';   // Fecha link

            // Barra de Progresso
            echo '<div class="progress mb-2">';
            echo '<div class="progress-bar" role="progressbar" style="width: ' . $porcentagem . '%;" aria-valuenow="' . $porcentagem . '" aria-valuemin="0" aria-valuemax="100">' . round($porcentagem, 2) . '%</div>';
            echo '</div>';

            echo '<div class="crowdfund-details">';
            echo '<div class="petition-details">';
            echo '<div class="sessao-dados">';
            echo '<p><i class="bi bi-wallet2"></i> Meta: R$ ' . number_format($meta, 2, ',', '.') . '</p>';
            echo '<p><i class="bi bi-wallet2"></i> Arrecadado: R$ ' . number_format($valor_arrecadado, 2, ',', '.') . '</p>';

            // Adicione um retângulo com a mensagem "Meta Atingida" se a meta for alcançada
            if ($valor_arrecadado >= $meta) {
                echo '<div class="meta-atingida">Meta Atingida</div>';
            }

            echo '</div>'; // Fecha div sessao-dados

            // Botão DOAR com redirecionamento para a página da vaquinha
            echo '<div class="text-center" style="padding-bottom:10px;">';
            echo '<a href="view_crowdfund.php?id=' . $row["id"] . '" class="btn btn-primary btn-lg btn-block">DOAR</a>';
            echo '</div>';

            echo '</div>'; // Fecha div petition-details
            echo '</div>'; // Fecha div crowdfund-details
            echo '</div>'; // Fecha div card-body
            echo '</div>'; // Fecha div card
            echo '</div>'; // Fecha div col-md-4
            echo '</div>'; // Fecha div col-md-4
        }
    } else {
        echo "<p>Nenhuma vaquinha disponível no momento.</p>";
    }

    $conn->close();
} else {
    echo "<p>Nenhum termo de pesquisa fornecido.</p>";
}

?>
