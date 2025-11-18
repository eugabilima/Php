
<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quiz_db');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    die('Erro ao conectar ao banco MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');

session_start();
?>
