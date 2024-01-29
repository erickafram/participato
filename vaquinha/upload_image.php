<?php
// Verifica se o arquivo foi enviado com sucesso
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Define o diretório de destino para salvar a imagem
    $targetDirectory = 'images/';

    // Obtém o nome original do arquivo
    $originalName = $_FILES['file']['name'];

    // Cria um nome de arquivo único para evitar colisões
    $uniqueName = uniqid() . '_' . $originalName;

    // Define o caminho completo para o arquivo de destino
    $targetPath = $targetDirectory . $uniqueName;

    // Move o arquivo carregado para o diretório de destino
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        // Retorna a URL da imagem para o TinyMCE
        $response = [
            'location' => $targetPath,
        ];
        echo json_encode($response);
    } else {
        // Se houver um erro ao mover o arquivo, retorne uma mensagem de erro
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Erro ao fazer o upload do arquivo.';
    }
} else {
    // Se não foi enviado um arquivo ou ocorreu um erro no upload, retorne uma mensagem de erro
    header('HTTP/1.1 400 Bad Request');
    echo 'Nenhum arquivo enviado ou ocorreu um erro no upload.';
}
?>
