<?php
require_once 'config.php';
if (empty($_SESSION['user_id'])) {
    header('Location: entrar.php?msg=' . urlencode('Faça login primeiro.'));
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['start'])) {
    $res = $mysqli->query("SELECT id FROM questions WHERE subject = 'História Geral' ORDER BY RAND() LIMIT 20");
    $ids = [];
    while ($r = $res->fetch_assoc()) $ids[] = $r['id'];
    if (count($ids) < 20) {
        die('Não há 20 perguntas no banco. Importe o db.sql.');
    }
    $_SESSION['quiz_questions'] = $ids;
    $_SESSION['quiz_index'] = 0;
    $_SESSION['quiz_score'] = 0;
    header('Location: quiz.php');
    exit;
}

if (empty($_SESSION['quiz_questions'])) {
    $tpl = file_get_contents(__DIR__ . '/views/quiz_start.html');
    echo $tpl;
    exit;
}

$ids = $_SESSION['quiz_questions'];
$idx = $_SESSION['quiz_index'];
$score = $_SESSION['quiz_score'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['option'] ?? '';
    $q_id = $ids[$idx];
    $stmt = $mysqli->prepare('SELECT correct_option FROM questions WHERE id = ?');
    $stmt->bind_param('i', $q_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row && strtoupper($selected) === strtoupper($row['correct_option'])) {
        $_SESSION['quiz_score'] = $score + 1;
    }
    $_SESSION['quiz_index'] = $idx + 1;
    if ($_SESSION['quiz_index'] >= count($ids)) {
        $final = $_SESSION['quiz_score'];
        $total = count($ids);
        $ins = $mysqli->prepare('INSERT INTO scores (user_id, score, total) VALUES (?, ?, ?)');
        $ins->bind_param('iii', $user_id, $final, $total);
        $ins->execute();
        header('Location: resultados.php');
        exit;
    }
    header('Location: quiz.php');
    exit;
}

$q_id = $ids[$idx];
$stmt = $mysqli->prepare('SELECT * FROM questions WHERE id = ?');
$stmt->bind_param('i', $q_id);
$stmt->execute();
$res = $stmt->get_result();
$q = $res->fetch_assoc();

$tpl = file_get_contents(__DIR__ . '/views/quiz.html');
$out = str_replace('%%INDEX%%', $idx+1, $tpl);
$out = str_replace('%%TOTAL%%', count($ids), $out);
$out = str_replace('%%QUESTION%%', htmlspecialchars($q['question_text']), $out);
$out = str_replace('%%A%%', htmlspecialchars($q['option_a']), $out);
$out = str_replace('%%B%%', htmlspecialchars($q['option_b']), $out);
$out = str_replace('%%C%%', htmlspecialchars($q['option_c']), $out);
$out = str_replace('%%D%%', htmlspecialchars($q['option_d']), $out);
$out = str_replace('%%E%%', htmlspecialchars($q['option_e']), $out);
$out = str_replace('%%SCORE%%', htmlspecialchars($_SESSION['quiz_score']), $out);
echo $out;
