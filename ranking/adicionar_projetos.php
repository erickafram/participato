<?php
session_start();
require_once "../includes/db_connection.php";

if (!isset($_SESSION["user_id"])) {
    // Redirecionar para a página de login se o usuário não estiver autenticado
    header("Location: ../login.php");
    exit();
}

$politico_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Processar o formulário de adição de quantidades de PLO e REQ
    $politico_id = (isset($_POST["politico_id"])) ? $_POST["politico_id"] : null;
    $plo = (isset($_POST["plo"])) ? $_POST["plo"] : 0;
    $req = (isset($_POST["req"])) ? $_POST["req"] : 0;
    $ano = (isset($_POST["ano"])) ? $_POST["ano"] : date("Y"); // Ano padrão é o ano atual

    // Inserir novos valores para este político e ano
    $insert_query = "INSERT INTO projetos_legislativos (politico_id, plo, req, ano) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiii", $politico_id, $plo, $req, $ano);
    $stmt->execute();

    // Redirecionar para a lista de políticos
    header("Location: lista_politicos.php");
    exit();
} else {
    // Verifique se um ID de político foi fornecido na URL
    if (isset($_GET["id"])) {
        $politico_id = $_GET["id"];
    } else {
        // ID do político não fornecido na URL, redirecione ou mostre uma mensagem de erro
        header("Location: lista_politicos.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Adicionar PLO/REQ por Ano | Sua Plataforma</title>
    <!-- Adicione links para folhas de estilo CSS e outros recursos aqui -->
</head>
<body>
<?php include "../header.php"; ?>

<div class="container mt-5">
    <h2>Adicionar PLO/REQ por Ano</h2>
    <form method="post" action="adicionar_projetos.php">
        <input type="hidden" name="politico_id" value="<?php echo $politico_id; ?>">
        <div class="form-group">
            <label for="ano">Ano:</label>
            <select name="ano" class="form-control" required>
                <option value="2016">2016</option>
                <option value="2017">2017</option>
                <option value="2018">2018</option>
                <option value="2019">2019</option>
                <option value="2020">2020</option>
                <option value="2021">2021</option>
                <option value="2022">2022</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
            </select>
        </div>
        <div class="form-group">
            <label for="plo">Quantidade de PLO:</label>
            <input type="number" name="plo" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="req">Quantidade de REQ:</label>
            <input type="number" name="req" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar</button>
    </form>

    <!-- Tabela para exibir os dados de PLO e REQ do político -->
    <h5 style="padding-top:15px;">Dados de PLO/REQ do Político</h5>
    <table class="table">
        <thead>
        <tr>
            <th>Ano</th>
            <th>Quantidade de PLO</th>
            <th>Quantidade de REQ</th>
            <th>Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php
        // Consulta SQL para obter os dados de PLO e REQ do político
        $dados_query = "SELECT id, ano, plo, req FROM projetos_legislativos WHERE politico_id = ?";
        $stmt = $conn->prepare($dados_query);
        $stmt->bind_param("i", $politico_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["ano"] . "</td>";
                echo "<td>" . $row["plo"] . "</td>";
                echo "<td>" . $row["req"] . "</td>";
                echo "<td>
                          <a href='editar_projetos.php?id=" . $row["id"] . "' class='btn btn-warning btn-sm'>Editar</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Nenhum dado de PLO/REQ disponível para este político.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<?php include "../footer.php"; ?>
</body>
</html>
