<?php
// Configurações de conexão com o banco de dados
$servername = "localhost"; // Nome do servidor (normalmente localhost)
$username = "root"; // Seu nome de usuário do banco de dados
$password = "D2*MtzLgceuh"; // Sua senha do banco de dados
$dbname = "participato"; // Nome do banco de dados

// Criação da conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

?>