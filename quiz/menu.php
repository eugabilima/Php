<?php
require_once 'config.php';
if (empty($_SESSION['user_id'])) {
    header('Location: entrar.php?msg=' . urlencode('Faça login primeiro.'));
    exit;
}

$msgHtml = '';
if (!empty($_GET['msg'])) {
    $msgHtml = '<p class="info">' . htmlspecialchars($_GET['msg']) . '</p>';
}

$tpl = file_get_contents(__DIR__ . '/views/menu.html');
$out = str_replace('%%USERNAME%%', htmlspecialchars($_SESSION['username']), $tpl);
$out = str_replace('%%MSG%%', $msgHtml, $out);
echo $out;

?>
