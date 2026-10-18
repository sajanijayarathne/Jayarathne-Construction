
<?php
require_once 'includes/functions.php';
require_login();
require_once 'includes/header.php';

$uid = current_user_id();
$role = current_role();

if ($role === 'client') {
    $s = $pdo->prepare('SELECT pr.status,pr.created_at,p.project_name FROM project_requests pr JOIN projects p ON p.id=pr.project_id WHERE pr.client_id=? ORDER BY pr.created_at DESC');
    $s->execute([$uid]);
    $items = $s->fetchAll();
} else {
    $items = $pdo->query('SELECT pr.status,pr.created_at,p.project_name FROM project_requests pr JOIN projects p ON p.id=pr.project_id ORDER BY pr.created_at DESC LIMIT 30')->fetchAll();
}
?>

<div class="page-heading">
    <p class="eyebrow">UPDATES</p>
    <h1>Notifications</h1>
    <p>Check recent request status updates.</p>
</div>

<section class="panel">
    <h2>Project Request Updates</h2>
    <?php if ($items): ?>
        <ul>
            <?php foreach ($items as $item): ?>
                <li>
                    <b><?= clean($item['project_name']) ?></b> —
                    Request status: <?= clean($item['status']) ?>
                    <small>(<?= clean($item['created_at']) ?>)</small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No notifications yet.</p>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
