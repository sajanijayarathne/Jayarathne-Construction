
<?php
require_once 'includes/functions.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: projects.php');
    exit;
}

check_token();
$id = (int)($_POST['request_id'] ?? 0);
$decision = $_POST['decision'] ?? '';

if (!in_array($decision, ['Approved', 'Rejected'], true)) {
    message('Invalid request action.');
    header('Location: projects.php');
    exit;
}

$s = $pdo->prepare("SELECT * FROM project_requests WHERE id=? AND status='Pending'");
$s->execute([$id]);
$r = $s->fetch();

if (!$r) {
    message('Request already processed.');
    header('Location: projects.php');
    exit;
}

if ($decision === 'Approved') {
    $s = $pdo->prepare('UPDATE projects SET client_id=?,is_available=0 WHERE id=? AND client_id IS NULL AND is_available=1');
    $s->execute([$r['client_id'], $r['project_id']]);

    if (!$s->rowCount()) {
        message('Project is no longer available.');
        header('Location: projects.php');
        exit;
    }

    $s = $pdo->prepare("UPDATE project_requests SET status='Rejected' WHERE project_id=? AND id<>? AND status='Pending'");
    $s->execute([$r['project_id'], $id]);
}

$s = $pdo->prepare('UPDATE project_requests SET status=? WHERE id=?');
$s->execute([$decision, $id]);

message('Request ' . $decision . '.');
header('Location: projects.php');
exit;



