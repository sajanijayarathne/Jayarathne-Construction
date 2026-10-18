
<?php
require_once 'includes/functions.php';
require_role('admin');

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalProjects = (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$activeProjects = (int)$pdo->query("SELECT COUNT(*) FROM projects WHERE status='In Progress'")->fetchColumn();
$totalEquipment = (int)$pdo->query('SELECT COUNT(*) FROM equipment')->fetchColumn();
$pendingRentals = (int)$pdo->query("SELECT COUNT(*) FROM rentals WHERE status='Pending'")->fetchColumn();

$projectList = $pdo->query(
    'SELECT project_name, location, status, progress
     FROM projects ORDER BY created_at DESC'
)->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">SYSTEM SUMMARY</p>
        <h1>Reports</h1>
        <p>Simple overview of current system records.</p>
    </div>
    <button class="button" type="button" onclick="window.print()">Print Report</button>
</div>

<section class="stats">
    <article class="stat">
        <span>Total Users</span>
        <strong><?= $totalUsers ?></strong>
    </article>
    <article class="stat">
        <span>Total Projects</span>
        <strong><?= $totalProjects ?></strong>
    </article>
    <article class="stat">
        <span>In Progress</span>
        <strong><?= $activeProjects ?></strong>
    </article>
    <article class="stat">
        <span>Equipment Listings</span>
        <strong><?= $totalEquipment ?></strong>
    </article>
    <article class="stat">
        <span>Pending Rentals</span>
        <strong><?= $pendingRentals ?></strong>
    </article>
</section>

<section class="panel">
    <h2>Project Summary</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projectList as $p): ?>
                    <tr>
                        <td><?= clean($p['project_name']) ?></td>
                        <td><?= clean($p['location']) ?></td>
                        <td><?= clean($p['status']) ?></td>
                        <td><?= (int)$p['progress'] ?>%</td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$projectList): ?>
                    <tr><td colspan="4">No projects available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
