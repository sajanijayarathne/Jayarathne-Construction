
<?php
require_once 'includes/functions.php';

$adminEmail = 'admin@jayarathne.lk';
$stmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
$stmt->execute([$adminEmail]);

if (!$stmt->fetch()) {
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
    $stmt->execute(['System Administrator', $adminEmail, password_hash('Admin@123', PASSWORD_DEFAULT), 'admin']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        message('Welcome, ' . $user['name'] . '!');
        header('Location: dashboard.php');
        exit;
    } else {
        message('Incorrect email or password.');
    }
}

require_once 'includes/header.php';
?>

<section class="form-card">
    <h1>Login</h1>
    <p>Enter your email and password to continue.</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
        <label>Email Address<input type="email" name="email" required></label>
        <label>Password<input type="password" name="password" required></label>
        <button class="button" type="submit">Login</button>
    </form>
    <p>New here? <a href="register.php">Create an account</a>.</p>
    
</section>

<?php require_once 'includes/footer.php'; ?>

