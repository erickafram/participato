<?php
// Conexão com o banco de dados
require_once "../includes/db_connection.php";

$offset = $_GET['offset'] ?? 0;
$limit = $_GET['limit'] ?? 5;
$petition_id = $_GET['petition_id'] ?? 0;

$query_comments = "SELECT c.comentario, u.nome_usuario, c.data
                   FROM comentarios c
                   INNER JOIN usuarios u ON c.id_usuario = u.id
                   WHERE c.id_abaixo_assinado = ?
                   ORDER BY c.data DESC
                   LIMIT ?, ?";
$stmt_comments = $conn->prepare($query_comments);
$stmt_comments->bind_param("iii", $petition_id, $offset, $limit);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

while ($row_comment = $result_comments->fetch_assoc()) {
    echo '<div class="comment">';
    echo '<p><strong>' . $row_comment["nome_usuario"] . '</strong> (' . date("d/m/Y H:i", strtotime($row_comment["data"])) . ')</p>';
    echo '<p>' . nl2br($row_comment["comentario"]) . '</p>';
    echo '</div>';
}

$stmt_comments->close();
$conn->close();
?>
