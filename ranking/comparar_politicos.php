<?php
session_start();
require_once "../includes/db_connection.php";

// Inicialize as variáveis
$vencedor = null;
$politico1_nome = '';
$politico2_nome = '';
$politico1_plo_req = [];
$politico2_plo_req = [];
$politico1_imagem = [];
$politico2_imagem = [];
$erro = '';

// Verifique se a ação de comparação foi acionada
if (isset($_POST["compare"])) {
    $politico1_id = $_POST["politico1_id"];
    $politico2_id = $_POST["politico2_id"];

    if ($politico1_id == $politico2_id) {
        $erro = "Não é possível selecionar o mesmo político em ambos os campos.";
    } else {
        // Consulta SQL para obter as informações do primeiro político
        $query1 = "SELECT nome, cargo, partido, caminho_imagem FROM politicos WHERE id = ?";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("i", $politico1_id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        $politico1 = $result1->fetch_assoc();
        $politico1_nome = $politico1["nome"];
        $politico1_imagem = $politico1["caminho_imagem"];

        // Consulta SQL para obter as informações do segundo político
        $query2 = "SELECT nome, cargo, partido, caminho_imagem FROM politicos WHERE id = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("i", $politico2_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $politico2 = $result2->fetch_assoc();
        $politico2_nome = $politico2["nome"];
        $politico2_imagem = $politico2["caminho_imagem"];

        // Consulta SQL para obter as pontuações de REQ e PLO de ambos os políticos
        $query3 = "SELECT
            politico_id, ano, SUM(plo) as total_plo, SUM(req) as total_req
            FROM projetos_legislativos
            WHERE politico_id IN (?, ?)
            GROUP BY politico_id, ano";
        $stmt3 = $conn->prepare($query3);
        $stmt3->bind_param("ii", $politico1_id, $politico2_id);
        $stmt3->execute();
        $result3 = $stmt3->get_result();

        while ($row = $result3->fetch_assoc()) {
            if ($row["politico_id"] == $politico1_id) {
                $politico1_plo_req[$row["ano"]] = [
                    "total_plo" => $row["total_plo"],
                    "total_req" => $row["total_req"],
                ];
            } elseif ($row["politico_id"] == $politico2_id) {
                $politico2_plo_req[$row["ano"]] = [
                    "total_plo" => $row["total_plo"],
                    "total_req" => $row["total_req"],
                ];
            }
        }

        // Consulta SQL para obter a pontuação total de PLO e REQ
        $query4 = "
            SELECT
                p.id AS politico_id,
                IFNULL(SUM(pl.plo), 0) * 0.5 + IFNULL(SUM(pl.req), 0) * 0.1 AS pontuacao
            FROM politicos p
            LEFT JOIN projetos_legislativos pl ON p.id = pl.politico_id
            WHERE p.id IN (?, ?)
            GROUP BY p.id
        ";
        $stmt4 = $conn->prepare($query4);
        $stmt4->bind_param("ii", $politico1_id, $politico2_id);
        $stmt4->execute();
        $result4 = $stmt4->get_result();

        $pontuacoes = [];
        while ($row = $result4->fetch_assoc()) {
            $pontuacoes[$row["politico_id"]] = $row["pontuacao"];
        }

        // Comparação para determinar o vencedor com base na pontuação total de PLO e REQ
        if ($pontuacoes[$politico1_id] > $pontuacoes[$politico2_id]) {
            $vencedor = $politico1_nome;
        } elseif ($pontuacoes[$politico1_id] < $pontuacoes[$politico2_id]) {
            $vencedor = $politico2_nome;
        } else {
            $vencedor = "Empate";
        }
    }
}

// Consulta SQL para obter a lista de políticos em ordem alfabética por nome
$queryPoliticos = "SELECT id, nome, cargo, partido FROM politicos ORDER BY nome";
$resultPoliticos = $conn->query($queryPoliticos);
?>

<?php include "../header.php"; // Inclua o arquivo de cabeçalho aqui ?>
<!DOCTYPE html>
<html>
<head>
    <title>Comparação de Políticos | Sua Plataforma</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Comparação de Políticos</h2>

    <div class="row">
        <div class="col-md-6">
            <form method="post" action="comparar_politicos.php">
                <div class="form-group">
                    <label for="politico1_id">Selecione o Primeiro Político:</label>
                    <select id="politico1_id" name="politico1_id" class="form-control" required>
                        <option value="">Selecione um político</option>
                        <?php
                        while ($row = $resultPoliticos->fetch_assoc()) {
                            echo "<option value='{$row["id"]}'>{$row["nome"]} ({$row["cargo"]} - {$row["partido"]})</option>";
                        }
                        ?>
                    </select>
                </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for "politico2_id">Selecione o Segundo Político:</label>
                <select id="politico2_id" name="politico2_id" class="form-control" required>
                    <option value="">Selecione um político</option>
                    <?php
                    // Volte para o início dos resultados dos políticos
                    $resultPoliticos->data_seek(0);
                    while ($row = $resultPoliticos->fetch_assoc()) {
                        echo "<option value='{$row["id"]}'>{$row["nome"]} ({$row["cargo"]} - {$row["partido"]})</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center" style="padding-bottom:20px;"> <!-- Centralize o botão -->
            <button type="submit" name="compare" class="btn btn-primary btn-lg">Comparar</button> <!-- Tornar o botão maior -->
        </div>
    </div>
    </form>

    <?php
    if (isset($erro) && !empty($erro)) {
        echo "<div class='alert alert-danger'>$erro</div>";
    } elseif (isset($vencedor)) {
        echo "<div class='row'>";
        echo "<div class='col-md-6 border-right d-flex flex-column align-items-center'>"; // Adicione a borda na coluna
        if ($politico1_imagem) {
            echo "<div class='position-relative'>";
            echo "<img src='imagens/{$politico1_imagem}' alt='{$politico1_nome}' class='img-fluid'>";
            echo "<div class='overlay'>";
            echo "<div class='text-center'>";
            if ($vencedor === $politico1_nome) {
                echo "<div class='winner-badge vencedor'><i class='bi bi-trophy'></i> Vencedor</div>";
            }
            echo "<p><strong>$politico1_nome</strong></p>";
            echo "<p><strong>Detalhes:</strong></p>";
            echo "<ul>";
            foreach ($politico1_plo_req as $ano => $plo_req) {
                echo "<li>Ano: $ano, PLO: {$plo_req["total_plo"]}, REQ: {$plo_req["total_req"]}</li>";
            }
            echo "<p><strong>Pontuação Total (PLO + REQ):</strong> {$pontuacoes[$politico1_id]}</p>";
            echo "</ul>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
        echo "<div class='col-md-6 d-flex flex-column align-items-center'>";
        if ($politico2_imagem) {
            echo "<div class='position-relative'>";
            echo "<img src='imagens/{$politico2_imagem}' alt='{$politico2_nome}' class='img-fluid'>";
            echo "<div class='overlay'>";
            echo "<div class='text-center'>";
            if ($vencedor === $politico2_nome) {
                echo "<div class='winner-badge vencedor'><i class='bi bi-trophy'></i> Vencedor</div>";
            }
            echo "<p><strong>$politico2_nome</strong></p>";
            echo "<p><strong>Detalhes:</strong></p>";
            echo "<ul>";
            foreach ($politico2_plo_req as $ano => $plo_req) {
                echo "<li>Ano: $ano, PLO: {$plo_req["total_plo"]}, REQ: {$plo_req["total_req"]}</li>";
            }
            echo "<p><strong>Pontuação Total (PLO + REQ):</strong> {$pontuacoes[$politico2_id]}</p>";
            echo "</ul>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
        echo "<div class='col-md-12 text-center mx-auto'>"; // Centralize o conteúdo na coluna
        echo "<h5>Resultado da Comparação:</h5>";
        echo "<h3>Vencedor: $vencedor</h3>";
        echo "</div>";
    }
    ?>
</div>
</body>
</html>
