<?php
session_start();
session_unset(); // Limpa todas as variáveis de sessão
session_destroy(); // Destrói a sessão

header("Location: ../index.php"); // Redireciona de volta para a página inicial após o logout
exit();
?>
