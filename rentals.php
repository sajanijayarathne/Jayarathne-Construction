
<?php
require_once 'includes/functions.php';
require_login();
$role = current_role();
$uid = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'admin') {
    check_token();
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (in_array($status, ['Pending', 'Approved', 'Rejected', 'Completed'], true)) {
        $s = $pdo->prepare('UPDATE rentals SET status=? WHERE id=?');
        $s->execute([$status, $id]);
        message('Rental status updated.');
    }

    header('Location: rentals.php');
    exit;
}

$sql = 'SELECT r.*, e.equipment_name, u.name client_name, u.email
        FROM rentals r
        JOIN equipment e ON e.id = r.equipment_id
        JOIN users u ON u.id = r.client_id';

if ($role === 'admin') {
    $s = $pdo->query($sql . ' ORDER BY r.created_at DESC');
} elseif ($role === 'provider') {
    $s = $pdo->prepare($sql . ' WHERE e.provider_id=? ORDER BY r.created_at DESC');
    $s->execute([$uid]);
} else {
    $s = $pdo->prepare($sql . ' WHERE r.client_id=? ORDER BY r.created_at DESC');
    $s->execute([$uid]);
}

$rentals = $s->fetchAll();
require_once 'includes/header.php';
?>

<div class="page-heading">
    <div>
        <p class="eyebrow">RENTAL MANAGEMENT</p>
        <h1><?= $role === 'client' ? 'My Rentals' : 'Rental Requests' ?></h1>
        <p>Review equipment, dates, estimated cost, and request status.</p>
    </div>
</div>

<section class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Equipment</th>
                    <?php if ($role !== 'client'): ?><th>Client</th><?php endif; ?>
                    <th>Dates</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <?php if ($role === 'admin'): ?><th>Update Status</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rentals as $r): ?>
                    <tr>
                        <td><?= clean($r['equipment_name']) ?></td>
                        <?php if ($role !== 'client'): ?>
                            <td><?= clean($r['client_name']) ?><small><?= clean($r['email']) ?></small></td>
                        <?php endif; ?>
                        <td><?= clean($r['start_date']) ?> to <?= clean($r['end_date']) ?></td>
                        <td><?= money($r['total_cost']) ?></td>
                        <td><?= clean($r['status']) ?></td>

                        <?php if ($role === 'admin'): ?>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                    <select name="status">
                                        <?php foreach (['Pending', 'Approved', 'Rejected', 'Completed'] as $status): ?>
                                            <option value="<?= $status ?>" <?= $r['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="button" type="submit">Save</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rentals): ?>
                    <tr><td colspan="<?= $role === 'admin' ? 6 : ($role === 'client' ? 4 : 5) ?>">No rental records yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>



<?php require_once 'includes/footer.php'; ?>

