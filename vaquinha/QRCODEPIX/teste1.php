<?php
require_once "phpqrcode/qrlib.php";
include "funcoes_pix.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recupere o valor digitado pelo usuário no campo "valor_pix"
    $valor_pix = isset($_POST["valor_pix"]) ? $_POST["valor_pix"] : "0.00"; // Valor padrão é 0.00 se não for especificado

    // Recupere os dados do Pix que você deseja gerar o QRCode
    $chave_pix = "01758848111"; // Substitua pelo valor correto
    $beneficiario_pix = "Erick Vinicius Rodrigues"; // Substitua pelo valor correto
    $cidade_pix = "SAO PAULO"; // Substitua pelo valor correto
    $identificador = "***"; // Substitua pelo valor correto
    $descricao = "Doação"; // Substitua pelo valor correto
    $gerar_qrcode = true; // Substitua pelo valor correto

    // Converter $valor_pix para float
    $valor_pix = (float) str_replace(",", ".", $valor_pix);

    // Montar os dados do Pix
    $px[00] = "01";
    $px[26][00] = "br.gov.bcb.pix";
    $px[26][01] = $chave_pix;
    $px[52] = "0000";
    $px[53] = "986";
    $px[54] = number_format($valor_pix, 2, '.', ''); // Converter $valor_pix em string com duas casas decimais
    $px[59] = $beneficiario_pix;
    $px[60] = $cidade_pix;
    $px[62][05] = $identificador;
    $px[26][02] = $descricao; // Adiciona a descrição
    $pix = montaPix($px);
    $pix .= "6304";
    $pix .= crcChecksum($pix);

    // Gerar o QRCode
    ob_start();
    QRCode::png($pix, null, 'M', 5);
    $imageString = base64_encode(ob_get_contents());
    ob_end_clean();

    // Exiba apenas a imagem do QRCode
    echo '<img src="data:image/png;base64,' . $imageString . '">';
}
?>
