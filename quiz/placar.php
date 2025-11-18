
<?php
require_once 'config.php';
if (empty($_SESSION['user_id'])) {
    header('Location: entrar.php');
    exit;
}

$res = $mysqli->query('SELECT s.score, s.total, s.created_at, u.username FROM scores s JOIN users u ON u.id = s.user_id ORDER BY s.score DESC, s.created_at ASC LIMIT 20');

$rows = '';
while ($r = $res->fetch_assoc()) {
    $rows .= '<tr>' .
        '<td>' . htmlspecialchars($r['username']) . '</td>' .
        '<td>' . htmlspecialchars($r['score']) . ' / ' . htmlspecialchars($r['total']) . '</td>' .
        '<td>' . htmlspecialchars($r['created_at']) . '</td>' .
      '</tr>';
}

$tpl = file_get_contents(__DIR__ . '/views/placar.html');
$out = str_replace('%%ROWS%%', $rows, $tpl);
echo $out;

?>
