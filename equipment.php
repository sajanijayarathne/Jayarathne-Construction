
<?php
require_once 'includes/functions.php';
require_login();

$role = current_role();
$uid = current_user_id();

$canManageEquipment = in_array($role, ['admin', 'provider'], true);

function equipmentImageUpload($field, $currentImage = null)
{
    if (
        !isset($_FILES[$field]) ||
        $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE
    ) {
        return $currentImage;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be smaller than 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
    }

    $dir = __DIR__ . '/uploads/equipment/';

    if (!is_dir($dir) &&
        !mkdir($dir, 0755, true) &&
        !is_dir($dir)) {
        throw new RuntimeException('Unable to create equipment image folder.');
    }

    $newName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];

    if (!move_uploaded_file($file['tmp_name'], $dir . $newName)) {
        throw new RuntimeException('Unable to save equipment image.');
    }

    // Remove the previous image only if it is inside the equipment upload folder.
    if ($currentImage) {
        $oldName = basename($currentImage);

        if ($oldName === $currentImage) {
            $oldPath = $dir . $oldName;

            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }
    }

    return $newName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $action = $_POST['action'] ?? '';

    // ADD EQUIPMENT WITH IMAGE
    if ($action === 'add' && $canManageEquipment) {
        $name = trim($_POST['equipment_name'] ?? '');

        if ($name !== '') {
            $imageName = null;

            try {
                $imageName = equipmentImageUpload('equipment_image');

                $owner = $role === 'provider' ? $uid : null;
                $availability = $_POST['availability'] ?? 'Available';

                if (!in_array($availability, ['Available', 'Unavailable'], true)) {
                    $availability = 'Available';
                }

                $s = $pdo->prepare(
                    'INSERT INTO equipment
                    (provider_id, equipment_name, description, daily_rate, availability, image)
                    VALUES (?, ?, ?, ?, ?, ?)'
                );

                $s->execute([
                    $owner,
                    $name,
                    trim($_POST['description'] ?? ''),
                    max(0, (float)($_POST['daily_rate'] ?? 0)),
                    $availability,
                    $imageName
                ]);

                message('Equipment added successfully.');
            } catch (RuntimeException $e) {
                message($e->getMessage());
            } catch (Throwable $e) {
                if ($imageName) {
                    $path = __DIR__ . '/uploads/equipment/' . basename($imageName);
                    if (is_file($path)) {
                        unlink($path);
                    }
                }

                throw $e;
            }
        } else {
            message('Please enter an equipment name.');
        }
    }

    // UPDATE EQUIPMENT AND OPTIONAL IMAGE
    if ($action === 'update' && $canManageEquipment) {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['equipment_name'] ?? '');
        $availability = $_POST['availability'] ?? 'Available';

        if (!in_array($availability, ['Available', 'Unavailable'], true)) {
            $availability = 'Available';
        }

        $sql = 'SELECT * FROM equipment WHERE id=?';
        $params = [$id];

        if ($role === 'provider') {
            $sql .= ' AND provider_id=?';
            $params[] = $uid;
        }

        $s = $pdo->prepare($sql);
        $s->execute($params);
        $existing = $s->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            message('Equipment not found or you do not have permission to edit it.');
        } elseif ($name === '') {
            message('Please enter an equipment name.');
        } else {
            $newImage = null;

            try {
                $newImage = equipmentImageUpload(
                    'equipment_image',
                    $existing['image'] ?? null
                );

                $s = $pdo->prepare(
                    'UPDATE equipment
                     SET equipment_name=?, description=?, daily_rate=?,
                         availability=?, image=?
                     WHERE id=?'
                    . ($role === 'provider' ? ' AND provider_id=?' : '')
                );

                $params = [
                    $name,
                    trim($_POST['description'] ?? ''),
                    max(0, (float)($_POST['daily_rate'] ?? 0)),
                    $availability,
                    $newImage,
                    $id
                ];

                if ($role === 'provider') {
                    $params[] = $uid;
                }

                $s->execute($params);
                message('Equipment updated successfully.');
            } catch (RuntimeException $e) {
                message($e->getMessage());
            }
        }
    }

    // DELETE EQUIPMENT
    if ($action === 'delete' && $canManageEquipment) {
        $id = (int)($_POST['id'] ?? 0);

        $sql = 'SELECT image FROM equipment WHERE id=?';
        $params = [$id];

        if ($role === 'provider') {
            $sql .= ' AND provider_id=?';
            $params[] = $uid;
        }

        $s = $pdo->prepare($sql);
        $s->execute($params);
        $existing = $s->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $sql = 'DELETE FROM equipment WHERE id=?';
            $params = [$id];

            if ($role === 'provider') {
                $sql .= ' AND provider_id=?';
                $params[] = $uid;
            }

            $pdo->prepare($sql)->execute($params);

            if (!empty($existing['image'])) {
                $imagePath = __DIR__ . '/uploads/equipment/' .
                    basename($existing['image']);

                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
            }

            message('Equipment deleted.');
        } else {
            message('Equipment not found or you do not have permission to delete it.');
        }
    }

    // RENT EQUIPMENT
    if ($action === 'rent' && $role === 'client') {
        $id = (int)($_POST['id'] ?? 0);
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';

        $validDates = false;

        if ($start !== '' && $end !== '') {
            $startDate = DateTime::createFromFormat('!Y-m-d', $start);
            $endDate = DateTime::createFromFormat('!Y-m-d', $end);

            $validDates = $startDate
                && $endDate
                && $startDate->format('Y-m-d') === $start
                && $endDate->format('Y-m-d') === $end
                && $end >= $start
                && $start >= date('Y-m-d');
        }

        if (!$validDates) {
            message('Please choose valid rental dates.');
        } else {
            $s = $pdo->prepare(
                "SELECT * FROM equipment
                 WHERE id=? AND availability='Available'"
            );
            $s->execute([$id]);
            $item = $s->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                message('Equipment is not available.');
            } else {
                $days = $startDate->diff($endDate)->days + 1;
                $total = $days * (float)$item['daily_rate'];

                $s = $pdo->prepare(
                    'INSERT INTO rentals
                    (client_id, equipment_id, start_date, end_date, total_cost)
                    VALUES (?, ?, ?, ?, ?)'
                );

                $s->execute([$uid, $id, $start, $end, $total]);

                message(
                    'Rental request submitted. Estimated cost: ' . money($total)
                );
            }
        }
    }

    header('Location: equipment.php');
    exit;
}

// LOAD EQUIPMENT
if ($role === 'provider') {
    $s = $pdo->prepare(
        'SELECT e.*, u.name AS provider_name
         FROM equipment e
         LEFT JOIN users u ON u.id=e.provider_id
         WHERE e.provider_id=?
         ORDER BY e.created_at DESC'
    );
    $s->execute([$uid]);
    $items = $s->fetchAll(PDO::FETCH_ASSOC);
} else {
    $items = $pdo->query(
        'SELECT e.*, u.name AS provider_name
         FROM equipment e
         LEFT JOIN users u ON u.id=e.provider_id
         ORDER BY e.created_at DESC'
    )->fetchAll(PDO::FETCH_ASSOC);
}

// AVAILABILITY COUNTS
$availabilityCounts = [
    'Available' => 0,
    'Unavailable' => 0
];

if ($role === 'provider') {
    $chartStmt = $pdo->prepare(
        'SELECT availability, COUNT(*) AS total
         FROM equipment WHERE provider_id=?
         GROUP BY availability'
    );
    $chartStmt->execute([$uid]);
} else {
    $chartStmt = $pdo->query(
        'SELECT availability, COUNT(*) AS total
         FROM equipment GROUP BY availability'
    );
}

foreach ($chartStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    if (array_key_exists($row['availability'], $availabilityCounts)) {
        $availabilityCounts[$row['availability']] = (int)$row['total'];
    }
}

$availableCount = $availabilityCounts['Available'];
$unavailableCount = $availabilityCounts['Unavailable'];
$totalEquipment = array_sum($availabilityCounts);

$availablePercent = $totalEquipment > 0
    ? round(($availableCount / $totalEquipment) * 100)
    : 0;

require_once 'includes/header.php';
?>

<div class="page-heading">
    <p class="eyebrow">EQUIPMENT DIRECTORY</p>
    <h1>Equipment</h1>
    <p>Browse equipment available for construction work.</p>
</div>

<section class="panel equipment-availability">
    <div class="availability-heading">
        <div>
            <p class="eyebrow">EQUIPMENT OVERVIEW</p>
            <h2>Equipment Availability</h2>
            <p>Current equipment availability summary.</p>
        </div>
        <span class="availability-badge"><?= $availablePercent ?>% Available</span>
    </div>

    <div class="availability-layout">
        <div class="availability-chart">
            <canvas id="equipmentAvailabilityChart"
                    role="img"
                    aria-label="Equipment availability doughnut chart"></canvas>
        </div>

        <div class="availability-stats">
            <div class="availability-stat">
                <span class="status-dot available-dot"></span>
                <div><span>Available</span><strong><?= $availableCount ?></strong></div>
            </div>
            <div class="availability-stat">
                <span class="status-dot unavailable-dot"></span>
                <div><span>Unavailable</span><strong><?= $unavailableCount ?></strong></div>
            </div>
            <div class="availability-stat total-stat">
                <div><span>Total Equipment</span><strong><?= $totalEquipment ?></strong></div>
            </div>
        </div>
    </div>
</section>

<?php if ($canManageEquipment): ?>
<section class="panel">
    <h2>Add Equipment</h2>

    <form method="post" class="form-grid" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">

        <label>
            Equipment Name
            <input name="equipment_name" required>
        </label>

        <label>
            Daily Rate (Rs.)
            <input type="number" name="daily_rate" min="0" step="0.01" required>
        </label>

        <label>
            Availability
            <select name="availability">
                <option value="Available">Available</option>
                <option value="Unavailable">Unavailable</option>
            </select>
        </label>

        <label class="full-width">
            Equipment Image
            <input type="file" name="equipment_image"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <small>JPG, PNG, or WEBP. Maximum 5 MB.</small>
        </label>

        <label class="full-width">
            Description
            <textarea name="description" rows="2"></textarea>
        </label>

        <button class="button" type="submit">Add Equipment</button>
    </form>
</section>
<?php endif; ?>

<div class="cards">
    <?php foreach ($items as $item): ?>
    <article class="card">

        <?php if (!empty($item['image'])): ?>
            <img class="equipment-image"
                 src="uploads/equipment/<?= rawurlencode(basename($item['image'])) ?>"
                 alt="<?= clean($item['equipment_name']) ?>"
                 loading="lazy">
        <?php else: ?>
            <div class="equipment-image-placeholder">
                <span>🚜</span>
                <p>Equipment Image</p>
            </div>
        <?php endif; ?>

        <h2><?= clean($item['equipment_name']) ?></h2>
        <p><?= clean($item['description'] ?: 'No description added.') ?></p>

        <p class="price">
            <?= money($item['daily_rate']) ?> <small>/ day</small>
        </p>

        <p>
            Availability:
            <span class="equipment-status <?= $item['availability'] === 'Available'
                ? 'status-available' : 'status-unavailable' ?>">
                <?= clean($item['availability']) ?>
            </span>
        </p>

        <p>
            Provider:
            <?= clean($item['provider_name'] ?: 'Jayarathne Construction') ?>
        </p>

        <?php if ($role === 'client' && $item['availability'] === 'Available'): ?>
        <details>
            <summary>Request Rental</summary>

            <form method="post" class="stack">
                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                <input type="hidden" name="action" value="rent">
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

                <label>
                    Start Date
                    <input type="date" name="start_date"
                           min="<?= date('Y-m-d') ?>" required>
                </label>

                <label>
                    End Date
                    <input type="date" name="end_date"
                           min="<?= date('Y-m-d') ?>" required>
                </label>

                <button class="button" type="submit">Send Request</button>
            </form>
        </details>
        <?php endif; ?>

        <?php
        $canManage = $canManageEquipment &&
            ($role === 'admin' || (int)$item['provider_id'] === $uid);
        ?>

        <?php if ($canManage): ?>
        <details>
            <summary>Edit or Delete</summary>

            <form method="post" class="stack" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

                <label>
                    Name
                    <input name="equipment_name"
                           value="<?= clean($item['equipment_name']) ?>" required>
                </label>

                <label>
                    Description
                    <textarea name="description"><?= clean($item['description']) ?></textarea>
                </label>

                <label>
                    Daily Rate
                    <input type="number" name="daily_rate" min="0" step="0.01"
                           value="<?= clean($item['daily_rate']) ?>" required>
                </label>

                <label>
                    Availability
                    <select name="availability">
                        <option value="Available" <?= $item['availability'] === 'Available' ? 'selected' : '' ?>>Available</option>
                        <option value="Unavailable" <?= $item['availability'] === 'Unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </label>

                <label>
                    Change Equipment Image (optional)
                    <input type="file" name="equipment_image"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <small>Leave empty to keep the current image.</small>
                </label>

                <button class="button" type="submit">Save Changes</button>
            </form>

            <form method="post" data-confirm="Delete this equipment?">
                <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                <button class="button danger" type="submit">Delete Equipment</button>
            </form>
        </details>
        <?php endif; ?>

    </article>
    <?php endforeach; ?>

    <?php if (!$items): ?>
        <p>No equipment available yet.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const canvas = document.getElementById('equipmentAvailabilityChart');

    if (!canvas || typeof Chart === 'undefined') return;

    const available = <?= $availableCount ?>;
    const unavailable = <?= $unavailableCount ?>;
    const total = available + unavailable;

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Available', 'Unavailable'],
            datasets: [{
                data: [available, unavailable],
                backgroundColor: ['#16a34a', '#f59e0b'],
                borderWidth: 0,
                hoverOffset: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 18, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const count = context.raw;
                            const percent = total > 0
                                ? Math.round(count / total * 100) : 0;

                            return context.label + ': ' + count + ' (' + percent + '%)';
                        }
                    }
                }
            }
        }
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
