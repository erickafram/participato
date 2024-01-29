<?php
require_once "../includes/db_connection.php";

if (isset($_GET["politico_id"])) {
    $politico_id = $_GET["politico_id"];
    $query = "
        SELECT
    ano,
    SUM(plo) as quantidade_plo,
    SUM(req) as quantidade_req,
    SUM(plo) + SUM(req) as total_plo_req
FROM projetos_legislativos
WHERE politico_id = ?
GROUP BY ano
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $politico_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $projetosLegislativos = array();
    while ($row = $result->fetch_assoc()) {
        $projetosLegislativos[] = $row;
    }

    echo json_encode($projetosLegislativos);
} else {
    echo "ID do político não especificado.";
}
?>
