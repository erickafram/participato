<?php
$folderPath = "imagens_noticias/";

$file = $_FILES['file']['tmp_name'];
$fileName = $_FILES['file']['name'];
$filePath = $folderPath . uniqid() . '-' . $fileName;

if (move_uploaded_file($file, $filePath)) {
    $location = $filePath;
    echo json_encode(['location' => $location]);
} else {
    header("HTTP/1.1 500 Internal Server Error");
}
?>
