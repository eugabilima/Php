<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: menu.php');
    exit;
}

$msgHtml = '';
if (!empty($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
    $msgHtml = "<p class=\"info\">$msg</p>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $msgHtml = '<p class="error">Preencha usuário e senha.</p>';
    } else {
        $stmt = $mysqli->prepare('SELECT id, password_hash, failed_attempts, locked_until FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if ($row['locked_until'] !== null && strtotime($row['locked_until']) > time()) {
                $remain = (strtotime($row['locked_until']) - time());
                $minutes = ceil($remain/60);
                header('Location: entrar.php?msg=' . urlencode("Conta bloqueada por $minutes minuto(s)."));
                exit;
            }
            if (password_verify($password, $row['password_hash'])) {
                $upd = $mysqli->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
                $upd->bind_param('i', $row['id']);
                $upd->execute();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $username;
                header('Location: menu.php');
                exit;
            } else {
                $failed = $row['failed_attempts'] + 1;
                $locked_until = null;
                if ($failed >= 3) {
                    $locked_until = date('Y-m-d H:i:s', time() + 5*60);
                }
                $upd = $mysqli->prepare('UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?');
                $upd->bind_param('isi', $failed, $locked_until, $row['id']);
                $upd->execute();
                if ($failed >= 3) {
                    header('Location: entrar.php?msg=' . urlencode('Conta bloqueada por 5 minutos após 3 tentativas.'));
                } else {
                    header('Location: entrar.php?msg=' . urlencode('Usuário ou senha incorretos.'));
                }
                exit;
            }
        } else {
            header('Location: entrar.php?msg=' . urlencode('Usuário não encontrado.'));
            exit;
        }
    }
}

$tpl = file_get_contents(__DIR__ . '/views/entrar.html');
$out = str_replace('%%MSG%%', $msgHtml, $tpl);
echo $out;

?>
