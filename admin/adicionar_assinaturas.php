<?php
session_start();
include('../includes/db_connection.php');
include('../header.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: \participabr\sistema\login.php");
    exit();
}

// Verifique se o usuário é um administrador
if (isset($_SESSION["user_tipo"]) && $_SESSION["user_tipo"] === "usuario") {
    header("Location: access_denied.php");
    exit();
}

// Recuperar todas as petições disponíveis para os usuários
$sqlPeticoes = "SELECT id, titulo FROM abaixo_assinados";
$resultPeticoes = $conn->query($sqlPeticoes);
$peticoes = [];
if ($resultPeticoes->num_rows > 0) {
    while ($row = $resultPeticoes->fetch_assoc()) {
        $peticoes[$row['id']] = $row['titulo'];
    }
}

// Função para gerar um nome fictício com base no gênero
function gerarNomeFicticio($genero) {
    $nomesMasculinos = ["Erick", "Vinicius", "José", "Carlos", "João", "Miguel"];
    $nomesMeioMasculinos = ["Silva", "Santos", "Oliveira"];
    $sobrenomesMasculinos = ["Souza", "Costa", "Lima", "Pereira"];

    $nomesFemininos = ["Karla", "Amanda", "Ludmila", "Maria", "Ana", "Clara"];
    $nomesMeioFemininos = ["Campos", "Lorena", "Feber"];
    $sobrenomesFemininos = ["Fernandes", "Rodrigues", "Martins", "Rocha"];

    if ($genero == 'masculino') {
        return $nomesMasculinos[array_rand($nomesMasculinos)] . " " .
            $nomesMeioMasculinos[array_rand($nomesMeioMasculinos)] . " " .
            $sobrenomesMasculinos[array_rand($sobrenomesMasculinos)];
    } else {
        return $nomesFemininos[array_rand($nomesFemininos)] . " " .
            $nomesMeioFemininos[array_rand($nomesMeioFemininos)] . " " .
            $sobrenomesFemininos[array_rand($sobrenomesFemininos)];
    }
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantidade = $_POST['quantidade'];
    $id_abaixo_assinado = $_POST['abaixo_assinado'];
    $usarNomeFicticio = isset($_POST['usar_nome_ficticio']) && $_POST['usar_nome_ficticio'] == "sim";
    $generoNomeFicticio = $_POST['genero_nome_ficticio'];

    // Insere as assinaturas
    for ($i = 0; $i < $quantidade; $i++) {
        if ($usarNomeFicticio) {
            $nomeFicticio = gerarNomeFicticio($generoNomeFicticio);
            $stmt = $conn->prepare("INSERT INTO assinaturas (id_abaixo_assinado, nome_temp, anonimo, criado_em) VALUES (?, ?, 0, NOW())");
            $stmt->bind_param("is", $id_abaixo_assinado, $nomeFicticio);
        } else {
            $stmt = $conn->prepare("INSERT INTO assinaturas (id_abaixo_assinado, anonimo, criado_em) VALUES (?, 1, NOW())");
            $stmt->bind_param("i", $id_abaixo_assinado);
        }
        $stmt->execute();
    }

    echo "<p>$quantidade assinaturas " . ($usarNomeFicticio ? "com nomes fictícios" : "anônimas") . " adicionadas para a petição $id_abaixo_assinado.</p>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Assinaturas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Adicionar Assinaturas</h2>
    <form action="adicionar_assinaturas.php" method="post" class="mt-4">
        <div class="form-group">
            <label for="abaixo_assinado">Selecione a Petição:</label>
            <select id="abaixo_assinado" name="abaixo_assinado" class="form-control" required>
                <?php foreach ($peticoes as $id => $titulo): ?>
                    <option value="<?php echo $id; ?>"><?php echo $titulo; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="quantidade">Quantidade de Assinaturas:</label>
            <input type="number" id="quantidade" name="quantidade" class="form-control" required>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="usar_nome_ficticio" name="usar_nome_ficticio" value="sim">
            <label class="form-check-label" for="usar_nome_ficticio">Usar nomes fictícios</label>
        </div>
        <div class="form-group">
            <label for="genero_nome_ficticio">Gênero para nomes fictícios:</label>
            <select id="genero_nome_ficticio" name="genero_nome_ficticio" class="form-control">
                <option value="masculino">Masculino</option>
                <option value="feminino">Feminino</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Adicionar Assinaturas</button>
    </form>
</div>
</body>
</html>
