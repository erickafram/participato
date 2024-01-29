<?php
session_start();
require_once "../includes/db_connection.php"; // Inclua o arquivo de conexão

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

function getOpcoes() {
    global $conn;
    $opcoes = array();
    $query = "SELECT id, opcao FROM opcoes_enquete";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $opcoes[] = $row;
        }
    }

    return $opcoes;
}

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_opcao = $_POST['id_opcao'];
    $quantity = intval($_POST['quantity']);

    $success = true;
    $conn->begin_transaction();
    try {
        for ($i = 0; $i < $quantity; $i++) {
            $query = "INSERT INTO `votos_enquete` (`id_opcao`, `id_usuario`, `ip`, `criado_em`) VALUES ($id_opcao, NULL, NULL, CURRENT_TIMESTAMP)";
            if (!$conn->query($query)) {
                $success = false;
                throw new Exception($conn->error);
            }
        }

        if ($success) {
            $conn->commit();
            echo "Votos adicionados com sucesso!";
        } else {
            $conn->rollback();
            echo "Erro ao adicionar votos.";
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro: " . $e->getMessage();
    }
}

$opcoes = getOpcoes();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Painel do Administrador - Minha Plataforma</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Painel do Administrador</h2>

    <form action="" method="post">
        <div class="form-group">
            <label for="id_opcao">ID da Opção:</label>
            <select name="id_opcao" id="id_opcao" class="form-control">
                <?php
                foreach ($opcoes as $opcao) {
                    echo '<option value="' . $opcao['id'] . '">' . $opcao['id'] . ' - ' . $opcao['opcao'] . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Quantidade de Votos:</label>
            <input type="number" name="quantity" id="quantity" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Adicionar Votos</button>
    </form>
</div>

</body>
</html>

