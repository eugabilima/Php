<?php
require_once 'config.php';
session_unset();
session_destroy();
header('Location: entrar.php?msg=' . urlencode('Desconectado.'));
exit;

?>
