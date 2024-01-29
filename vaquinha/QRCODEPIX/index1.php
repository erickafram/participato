<?php
if (!isset($_GET["doacao"])) {
   if (isset($_POST["chave"]) && isset($_POST["beneficiario"]) && isset($_POST["cidade"])) {
      $chave_pix=strtolower($_POST["chave"]);
      $beneficiario_pix=$_POST["beneficiario"];
      $cidade_pix=$_POST["cidade"];
      if (isset($_POST["descricao"])){
         $descricao=$_POST["descricao"];
      }
      else { $descricao=''; }
      if ((!isset($_POST["identificador"])) || (empty($_POST["identificador"]))) {
         $identificador="***";
      }
      else {
         $identificador=$_POST["identificador"];
         if (strlen($identificador) > 25) {
            $identificador=substr($identificador,0,25);
         }
      }
      $gerar_qrcode=true;
   }
   else {
      $cidade_pix="SAO PAULO";
      $gerar_qrcode=false;
   }
}
else {
   $chave_pix="01758848111";
   $beneficiario_pix="ERICK VINICIUS RODRIGUES";
   $cidade_pix="SÃO PAULO";
   $identificador="***";
   $descricao="Doação";
   $gerar_qrcode=true;
}
if (isset($_POST["valor"]) && is_numeric($_POST["valor"])) {
    $valor_pix = preg_replace("/[^0-9.]/", "", $_POST["valor"]);
} else {
    $valor_pix = "0.00";
}

?>

<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Gerador gratuito de QR Code e BR Code do Pix. Gere o seu QR Code ou a linha digitável do Pix Copia e Cola.">
<meta name="keywords" content="pix, qrcode pix, qr code, br code, brcode pix, pix copia e cola" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
<script src="https://kit.fontawesome.com/0f8eed42e7.js" crossorigin="anonymous"></script>
<script>
function copiar() {
  var copyText = document.getElementById("brcodepix");
  copyText.select();
  copyText.setSelectionRange(0, 99999); /* For mobile devices */
  document.execCommand("copy");
  document.getElementById("clip_btn").innerHTML='<i class="fas fa-clipboard-check"></i>';
}
function reais(v){
    v=v.replace(/\D/g,"");
    v=v/100;
    v=v.toFixed(2);
    return v;
}
function mascara(o,f){
    v_obj=o;
    v_fun=f;
    setTimeout("execmascara()",1);
}
function execmascara(){
    v_obj.value=v_fun(v_obj.value);
}
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-E6M96X7Y2Y"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-E6M96X7Y2Y');
</script>
<style>
a {text-decoration: none;} 
p {text-align: center;}
</style>
</head>
<body>
<?php

if ($gerar_qrcode){
   include "phpqrcode/qrlib.php";
   include "funcoes_pix.php";
   $px[00]="01"; //Payload Format Indicator, Obrigatório, valor fixo: 01
   // Se o QR Code for para pagamento único (só puder ser utilizado uma vez), descomente a linha a seguir.
   //$px[01]="12"; //Se o valor 12 estiver presente, significa que o BR Code só pode ser utilizado uma vez.
   $px[26][00]="br.gov.bcb.pix"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
   $px[26][01]=$chave_pix;
   if (!empty($descricao)) {
      /*
      Não é possível que a chave pix e infoAdicionais cheguem simultaneamente a seus tamanhos máximos potenciais.
      Conforme página 15 do Anexo I - Padrões para Iniciação do PIX  versão 1.2.006.
      */
      $tam_max_descr=99-(4+4+4+14+strlen($chave_pix));
      if (strlen($descricao) > $tam_max_descr) {
         $descricao=substr($descricao,0,$tam_max_descr);
      }
      $px[26][02]=$descricao;
   }
   $px[52]="0000"; //Merchant Category Code “0000” ou MCC ISO18245
   $px[53]="986"; //Moeda, “986” = BRL: real brasileiro - ISO4217
   if ($valor_pix > 0) {
      // Na versão 1.2.006 do Anexo I - Padrões para Iniciação do PIX estabelece o campo valor (54) como um campo opcional.
      $px[54]=$valor_pix;
   }
   $px[58]="BR"; //“BR” – Código de país ISO3166-1 alpha 2
   $px[59]=$beneficiario_pix; //Nome do beneficiário/recebedor. Máximo: 25 caracteres.
   $px[60]=$cidade_pix; //Nome cidade onde é efetuada a transação. Máximo 15 caracteres.
   $px[62][05]=$identificador;
//   $px[62][50][00]="BR.GOV.BCB.BRCODE"; //Payment system specific template - GUI
//   $px[62][50][01]="1.2.006"; //Payment system specific template - versão
   $pix=montaPix($px);
   $pix.="6304"; //Adiciona o campo do CRC no fim da linha do pix.
   $pix.=crcChecksum($pix); //Calcula o checksum CRC16 e acrescenta ao final.
   $linhas=round(strlen($pix)/120)+1;
   ?>
   <div class="card">
   <h3>Linha do Pix (copia e cola):</h3>
   <div class="row">
      <div class="col">
      <textarea class="text-monospace" id="brcodepix" rows="<?= $linhas; ?>" cols="130" onclick="copiar()"><?= $pix;?></textarea>
      </div>
      <div class="col md-1">
      <p><button type="button" id="clip_btn" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Copiar código pix" onclick="copiar()"><i class="fas fa-clipboard"></i></button></p>
      </div>
   </div>
   </div>
   <h3>Imagem de QRCode do Pix:</h3>
   <p>
   <img src="logo_pix.png"><br>
   <?php
   ob_start();
   QRCode::png($pix, null,'M',5);
   $imageString = base64_encode( ob_get_contents() );
   ob_end_clean();
   // Exibe a imagem diretamente no navegador codificada em base64.
   echo '<img src="data:image/png;base64,' . $imageString . '"></p>';
}
?>
    <h3>Gerador de QR Code do PIX</h3>
<div class="card">
<div class="card-body">
<form method="post" action="index1.php">
    <div class="row row-cols-lg-auto g-3 align-items-center">
        <input type="text" id="chave" name="chave" value="01758848111" style="display: none;">
    </div>

    <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="valor" class="form-label">Valor a pagar:</label>
      <input type="text" id="valor" name="valor" placeholder="Informe o valor a cobrar" size="15" maxlength="13" value="<?= $valor_pix; ?>" onclick="this.select();" onkeypress="mascara(this,reais)">
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
       <input type="hidden" id="beneficiario" name="beneficiario" value="Erick Vinicius Rodrigues">
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
       <input type="hidden" name="cidade" value="SAO PAULO">
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
       <input type="hidden" id="descricao" name="descricao" value="doação">
   </div>

   <p><button type="submit" class="btn btn-primary">Gerar QR Code <i class="fas fa-qrcode"></i></button></i></p>
</form>
</div></div>
<div class="card">
</div>
</body>
</html>