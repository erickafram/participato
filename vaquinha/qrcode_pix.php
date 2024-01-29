<?php
require_once "phpqrcode/qrlib.php";
include "funcoes_pix.php";

$pixCode = ""; // Inicialize a variável para armazenar o código do Pix

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recupere os valores do formulário
    $valor_pix = isset($_POST["valor_pix"]) ? $_POST["valor_pix"] : "0.00";
    $beneficiario_pix = "Renato Gomes Aguiar"; // Substitua pelo nome do beneficiário
    $cidade_pix = "SAO PAULO"; // Cidade padrão
    $identificador = "***"; // Identificador padrão
    $descricao = "Doação"; // Descrição padrão

    // Limitar o tamanho da descrição e do identificador
    if (strlen($descricao) > 99) {
        $descricao = substr($descricao, 0, 99);
    }
    if (strlen($identificador) > 25) {
        $identificador = substr($identificador, 0, 25);
    }

    // Converter $valor_pix para float e formatá-lo
    $valor_pix = (float) str_replace(",", ".", $valor_pix);
    $valor_pix = number_format($valor_pix, 2, '.', '');

    // Montar os dados do Pix
    $px[00] = "01";
    $px[26][00] = "br.gov.bcb.pix";
    $px[26][01] = "renato5462514@gmail.com"; // Substitua pela chave Pix
    $px[52] = "0000";
    $px[53] = "986";
    $px[54] = $valor_pix;
    $px[58] = "BR";
    $px[59] = $beneficiario_pix;
    $px[60] = $cidade_pix;
    $px[62][05] = $identificador;
    if (!empty($descricao)) {
        $px[26][02] = $descricao;
    }

    $pix = montaPix($px);
    $pix .= "6304";
    $pix .= crcChecksum($pix);

    // Armazene o código do Pix na variável $pixCode
    $pixCode = $pix;

    // Gerar o QRCode
    ob_start();
    QRCode::png($pix, null, 'M', 5);
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doar para a Vaquinha - Minha Plataforma</title>
    <!-- Adicione aqui seus links CSS e JavaScript necessários -->
</head>
<body>
<!-- Se a imagem do QRCode foi gerada, exiba-a -->
<?php if (!empty($imageString)) : ?>
    <img src="data:image/png;base64,<?= $imageString ?>" alt="QRCode">
<?php endif; ?>

<script>
    <?php
    if (!empty($pixCode)) : ?>
    document.getElementById("pixCode").value = "<?= $pixCode ?>";
    <?php endif; ?>
</script>
</body>
</html>
