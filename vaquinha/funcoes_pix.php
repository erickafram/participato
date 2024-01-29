<?php
/*
# Biblioteca de funções para geração da linha do Pix copia e cola
# cujo texto é utilizado para a geração do QRCode para recebimento
# de pagamentos através do Pix do Banco Central.
#
#
# Desenvolvido em 2020 por Renato Monteiro Batista - http://renato.ovh
#
# Este código pode ser copiado, modificado, redistribuído
# inclusive comercialmente desde que mantida a refereência ao autor.
*/


function montaPix($px){
    $ret="";
    foreach ($px as $k => $v) {
        if (!is_array($v)) {
            if ($k == 54) { $v=number_format($v,2,'.',''); } // Formata o campo valor com 2 digitos.
            else { $v=remove_char_especiais($v); }
            $ret.=c2($k).cpm($v).$v;
        }
        else {
            $conteudo=montaPix($v);
            $ret.=c2($k).cpm($conteudo).$conteudo;
        }
    }
    return $ret;
}

function remove_char_especiais($txt){
    /*
    # Esta função retorna somente os caracteres alfanuméricos (a-z,A-Z,0-9) de uma string.
    # Caracteres acentuados são convertidos pelos equivalentes sem acentos.
    # Emojis são removidos, mantém espaços em branco.
    #
    # Autor: Eng. Renato Monteiro Batista
    */
    return preg_replace('/\W /','',remove_acentos($txt));
}

function remove_acentos($texto){
    /*
    # Esta função retorna uma string substituindo os caracteres especiais de acentuação
    # pelos respectivos caracteres não acentuados em português-br.
    #
    # Autor: Eng. Renato Monteiro Batista
    */
    $search = explode(",","à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż");
    $replace =explode(",","a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z");
    return remove_emoji(str_replace($search, $replace, $texto));
}

function remove_emoji($string){
    /*
    # Esta função retorna o conteúdo de uma string removendo oas caracteres especiais
    # usados para representação de emojis.
    #
    */
    return preg_replace('%(?:
   \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
 | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
 | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
)%xs', '  ', $string);
}


function cpm($tx){
    /*
    # Esta função auxiliar retorna a quantidade de caracteres do texto $tx com dois dígitos.
    #
    # Autor: Renato Monteiro Batista
    */
    if (strlen($tx) > 99) {
        die("Tamanho máximo deve ser 99, inválido: $tx possui " . strlen($tx) . " caracteres.");
    }
    /*
    Não aprecio o uso de die no código, é um tanto deselegante pois envolve matar.
    Mas considerando que 99 realmente é o tamanho máximo aceitável, estou adotando-o.
    Mas aconselho que essa verificação seja feita em outras etapas do código.
    Caso não tenha entendido a problemática consulte  a página 4 do Manual de Padrões para Iniciação do Pix.
    Ou a issue 4 deste projeto: https://github.com/renatomb/php_qrcode_pix/issues/4
    */
    return c2(strlen($tx));
}

function c2($input){
    /*
    # Esta função auxiliar trata os casos onde o tamanho do campo for < 10 acrescentando o
    # dígito 0 a esquerda.
    #
    # Autor: Renato Monteiro Batista
    */
    return str_pad($input, 2, "0", STR_PAD_LEFT);
}


function crcChecksum($str) {

    function charCodeAt($str, $i) {
        return ord(substr($str, $i, 1));
    }

    $crc = 0xFFFF;
    $strlen = strlen($str);
    for($c = 0; $c < $strlen; $c++) {
        $crc ^= charCodeAt($str, $c) << 8;
        for($i = 0; $i < 8; $i++) {
            if($crc & 0x8000) {
                $crc = ($crc << 1) ^ 0x1021;
            } else {
                $crc = $crc << 1;
            }
        }
    }
    $hex = $crc & 0xFFFF;
    $hex = dechex($hex);
    $hex = strtoupper($hex);
    $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);

    return $hex;
}

?>