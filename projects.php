
<?php
require_once 'includes/functions.php';
require_login();

$role = current_role();
$uid = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $action = $_POST['action'] ?? '';

    // ADD PROJECT WITH IMAGE
    if ($action === 'add' && $role === 'admin') {
        $name = trim($_POST['project_name'] ?? '');
        $imageName = null;

        if ($name !== '') {
            $uploadDir = __DIR__ . '/uploads/projects/';

            if (!is_dir($uploadDir) &&
                !mkdir($uploadDir, 0755, true) &&
                !is_dir($uploadDir)) {
                message('Unable to create image upload folder.');
                header('Location: projects.php');
                exit;
            }

            if (
                isset($_FILES['project_image']) &&
                $_FILES['project_image']['error'] !== UPLOAD_ERR_NO_FILE
            ) {
                $file = $_FILES['project_image'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    message('Image upload failed. Please try again.');
                    header('Location: projects.php');
                    exit;
                }

                if ($file['size'] > 5 * 1024 * 1024) {
                    message('Image must be smaller than 5 MB.');
                    header('Location: projects.php');
                    exit;
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowedTypes[$mime])) {
                    message('Only JPG, PNG, and WEBP images are allowed.');
                    header('Location: projects.php');
                    exit;
                }

                $imageName = bin2hex(random_bytes(16)) . '.' .
                    $allowedTypes[$mime];

                if (!move_uploaded_file(
                    $file['tmp_name'],
                    $uploadDir . $imageName
                )) {
                    message('Unable to save the project image.');
                    header('Location: projects.php');
                    exit;
                }
            }

            $status = $_POST['status'] ?? 'Planning';
            $allowedStatuses = [
                'Planning', 'In Progress', 'Completed', 'On Hold'
            ];

            if (!in_array($status, $allowedStatuses, true)) {
                $status = 'Planning';
            }

            $progress = max(
                0,
                min(100, (int)($_POST['progress'] ?? 0))
            );

            if ($status === 'Completed') {
                $progress = 100;
            }

            try {
                $s = $pdo->prepare(
                    'INSERT INTO projects
                    (project_name, description, location, start_date,
                     end_date, progress, status, image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $s->execute([
                    $name,
                    trim($_POST['description'] ?? ''),
                    trim($_POST['location'] ?? ''),
                    ($_POST['start_date'] ?? '') ?: null,
                    ($_POST['end_date'] ?? '') ?: null,
                    $progress,
                    $status,
                    $imageName
                ]);

                message('Project added successfully.');
            } catch (Throwable $e) {
                if ($imageName && is_file($uploadDir . $imageName)) {
                    unlink($uploadDir . $imageName);
                }

                throw $e;
            }
        } else {
            message('Please enter a project name.');
        }
    }

    // CLIENT PROJECT REQUEST
    if ($action === 'request' && $role === 'client') {
        $pid = (int)($_POST['project_id'] ?? 0);

        $s = $pdo->prepare(
            'SELECT id FROM projects
             WHERE id = ? AND is_available = 1 AND client_id IS NULL'
        );
        $s->execute([$pid]);

        if ($s->fetch()) {
            $s = $pdo->prepare(
                "SELECT id FROM project_requests
                 WHERE project_id = ? AND client_id = ?
                 AND status IN ('Pending', 'Approved')"
            );
            $s->execute([$pid, $uid]);

            if (!$s->fetch()) {
                $s = $pdo->prepare(
                    'INSERT INTO project_requests
                     (project_id, client_id, message) VALUES (?, ?, ?)'
                );
                $s->execute([
                    $pid,
                    $uid,
                    trim($_POST['message'] ?? '')
                ]);

                message('Project request sent to administrator.');
            } else {
                message('You already have an active request for this project.');
            }
        } else {
            message('This project is not available.');
        }
    }

    // UPDATE PROJECT STATUS
    if ($action === 'status' && $role === 'admin') {
        $pid = (int)($_POST['project_id'] ?? 0);
        $status = $_POST['status'] ?? 'Planning';

        $allowedStatuses = [
            'Planning', 'In Progress', 'Completed', 'On Hold'
        ];

        if (in_array($status, $allowedStatuses, true)) {
            $progress = $status === 'Completed'
                ? 100
                : max(0, min(100, (int)($_POST['progress'] ?? 0)));

            $s = $pdo->prepare(
                'UPDATE projects SET status = ?, progress = ? WHERE id = ?'
            );
            $s->execute([$status, $progress, $pid]);

            message('Project status updated.');
        }
    }

    header('Location: projects.php');
    exit;
}

// LOAD PROJECTS
if ($role === 'client') {
    $s = $pdo->prepare(
        'SELECT * FROM projects
         WHERE (is_available = 1 AND client_id IS NULL) OR client_id = ?
         ORDER BY created_at DESC'
    );
    $s->execute([$uid]);
    $projects = $s->fetchAll();
} else {
    $projects = $pdo->query(
        'SELECT * FROM projects ORDER BY created_at DESC'
    )->fetchAll();
}

require_once 'includes/header.php';
?>

<div class="page-heading">
    <p class="eyebrow">CONSTRUCTION MANAGEMENT</p>
    <h1>Projects</h1>
    <p>Explore construction projects, track progress, and manage requests.</p>
</div>

<?php if ($role === 'admin'): ?>
<section class="panel">
    <h2>Add Project</h2>

    <form method="post" class="form-grid" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token"
               value="<?= clean(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">

        <label>
            Project Name
            <input name="project_name" required>
        </label>

        <label>
            Location
            <input name="location">
        </label>

        <label>
            Start Date
            <input type="date" name="start_date">
        </label>

        <label>
            End Date
            <input type="date" name="end_date">
        </label>

        <label>
            Status
            <select name="status">
                <option>Planning</option>
                <option>In Progress</option>
                <option>Completed</option>
                <option>On Hold</option>
            </select>
        </label>

        <label>
            Progress (%)
            <input type="number" name="progress"
                   min="0" max="100" value="0">
        </label>

        <label class="full-width">
            Project Image
            <input type="file" name="project_image"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <small>JPG, PNG, or WEBP. Maximum size: 5 MB.</small>
        </label>

        <label class="full-width">
            Description
            <textarea name="description" rows="3"></textarea>
        </label>

        <button class="button" type="submit">Add Project</button>
    </form>
</section>
<?php endif; ?>

<div class="cards">
<?php foreach ($projects as $p): ?>
    <article class="card">

        <?php if (!empty($p['image'])): ?>
            <img
                src="uploads/projects/<?= rawurlencode(basename($p['image'])) ?>"
                alt="<?= clean($p['project_name']) ?>"
                class="project-image"
                loading="lazy">
        <?php else: ?>
            <div class="project-image-placeholder">
                <span>🏗️</span>
                <p>Construction Project</p>
            </div>
        <?php endif; ?>

        <p class="eyebrow"><?= clean($p['status']) ?></p>

        <h2><?= clean($p['project_name']) ?></h2>

        <p>
            <?= clean(
                $p['description'] ?:
                'Construction project details will be updated here.'
            ) ?>
        </p>

        <p>
            <b>Location:</b>
            <?= clean($p['location'] ?: 'Not specified') ?>
        </p>

        <p>
            <b>Dates:</b>
            <?= clean($p['start_date'] ?: 'TBD') ?>
            to
            <?= clean($p['end_date'] ?: 'TBD') ?>
        </p>

        <div class="progress">
            <span style="width:<?= max(0, min(100, (int)$p['progress'])) ?>%"></span>
        </div>

        <p><?= (int)$p['progress'] ?>% complete</p>

        <?php if (
            $role === 'client' &&
            empty($p['client_id']) &&
            (int)$p['is_available'] === 1
        ): ?>
            <form method="post">
                <input type="hidden" name="csrf_token"
                       value="<?= clean(csrf_token()) ?>">
                <input type="hidden" name="action" value="request">
                <input type="hidden" name="project_id"
                       value="<?= (int)$p['id'] ?>">

                <label>
                    Message (optional)
                    <textarea name="message" rows="2"></textarea>
                </label>

                <button class="button" type="submit">Request Project</button>
            </form>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
            <form method="post" class="inline-form">
                <input type="hidden" name="csrf_token"
                       value="<?= clean(csrf_token()) ?>">
                <input type="hidden" name="action" value="status">
                <input type="hidden" name="project_id"
                       value="<?= (int)$p['id'] ?>">

                <select name="status">
                    <option <?= $p['status'] === 'Planning' ? 'selected' : '' ?>>Planning</option>
                    <option <?= $p['status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                    <option <?= $p['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option <?= $p['status'] === 'On Hold' ? 'selected' : '' ?>>On Hold</option>
                </select>

                <input type="number" name="progress"
                       min="0" max="100"
                       value="<?= (int)$p['progress'] ?>"
                       aria-label="Progress percent">

                <button class="button" type="submit">Update</button>
            </form>
        <?php endif; ?>

    </article>
<?php endforeach; ?>

<?php if (!$projects): ?>
    <p>No projects have been added yet.</p>
<?php endif; ?>
</div>

<?php if ($role === 'client'): ?>
<section class="panel">
    <h2>My Project Requests</h2>

    <?php
    $s = $pdo->prepare(
        'SELECT pr.status, pr.created_at, p.project_name
         FROM project_requests pr
         JOIN projects p ON p.id = pr.project_id
         WHERE pr.client_id = ?
         ORDER BY pr.created_at DESC'
    );
    $s->execute([$uid]);
    $requests = $s->fetchAll();
    ?>

    <?php if ($requests): ?>
        <table>
            <tr>
                <th>Project</th>
                <th>Requested</th>
                <th>Status</th>
            </tr>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= clean($r['project_name']) ?></td>
                    <td><?= clean($r['created_at']) ?></td>
                    <td><?= clean($r['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>You have not requested a project yet.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($role === 'admin'): ?>
<section class="panel">
    <h2>Pending Project Requests</h2>

    <?php
    $requests = $pdo->query(
        "SELECT pr.id, pr.message, p.project_name, u.name AS client_name
         FROM project_requests pr
         JOIN projects p ON p.id = pr.project_id
         JOIN users u ON u.id = pr.client_id
         WHERE pr.status = 'Pending'
         ORDER BY pr.created_at"
    )->fetchAll();
    ?>

    <?php if ($requests): ?>
        <table>
            <tr>
                <th>Project</th>
                <th>Client</th>
                <th>Message</th>
                <th>Action</th>
            </tr>

            <?php foreach ($requests as $r): ?>
                <tr>
                    <td><?= clean($r['project_name']) ?></td>
                    <td><?= clean($r['client_name']) ?></td>
                    <td><?= clean($r['message']) ?></td>
                    <td>
                        <form method="post"
                              action="project_request_action.php"
                              class="inline-form">
                            <input type="hidden" name="csrf_token"
                                   value="<?= clean(csrf_token()) ?>">
                            <input type="hidden" name="request_id"
                                   value="<?= (int)$r['id'] ?>">

                            <button class="button" name="decision"
                                    value="Approved">Approve</button>
                            <button class="button danger" name="decision"
                                    value="Rejected">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No pending project requests.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
