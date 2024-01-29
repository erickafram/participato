<?php
header('Content-Type: application/xml; charset=utf-8');

include('includes/db_connection.php');

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

function addUrl($loc, $lastmod = "", $changefreq = "weekly", $priority = "0.5") {
    echo "<url>";
    echo "<loc>$loc</loc>";
    if ($lastmod != "") {
        $formattedDate = date('Y-m-d', strtotime($lastmod)); // Formata a data
        echo "<lastmod>$formattedDate</lastmod>";
    }
    echo "<changefreq>$changefreq</changefreq>";
    echo "<priority>$priority</priority>";
    echo "</url>";
}


addUrl("https://participato.com.br/sistema/index.php", date('Y-m-d'), "daily", "1.0");

$query = "SELECT id, data_publicacao FROM noticias";
$result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($result)) {
    addUrl("https://participato.com.br/sistema/noticias/ver_noticia.php?id=" . $row['id'], $row['data_publicacao']);
}

// Vaquinhas
$query = "SELECT id, criado_em FROM vaquinhas";
$result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($result)) {
    addUrl("https://participato.com.br/sistema/vaquinha/view_crowdfund.php?id=" . $row['id'], $row['criado_em']);
}

// Enquetes
$query = "SELECT id, criado_em FROM enquetes";
$result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($result)) {
    addUrl("https://participato.com.br/sistema/enquete/votar.php?id=" . $row['id'], $row['criado_em']);
}

// Abaixo-assinados
$query = "SELECT id, criado_em FROM abaixo_assinados";
$result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($result)) {
    addUrl("https://participato.com.br/sistema/peticao/petition_details.php?id=" . $row['id'], $row['criado_em']);
}

echo '</urlset>';
