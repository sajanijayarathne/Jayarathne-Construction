
<?php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'client';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6 || !in_array($role, ['client', 'provider'], true)) {
        message('Please enter valid details. Password must have at least 6 characters.');
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            message('Account created. You can now log in.');
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            message('This email may already be registered. Try another email.');
        }
    }
}

require_once 'includes/header.php';
?>

<section class="form-card">
    <h1>Create an Account</h1>
    <p>Register as a client or equipment provider.</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= clean(csrf_token()) ?>">
        <label>Full Name<input name="name" required value="<?= clean($_POST['name'] ?? '') ?>"></label>
        <label>Email Address<input type="email" name="email" required value="<?= clean($_POST['email'] ?? '') ?>"></label>
        <label>Password<input type="password" name="password" minlength="6" required></label>
        <label>Account Type
            <select name="role">
                <option value="client">Client</option>
                <option value="provider">Equipment Provider</option>
            </select>
        </label>
        <button class="button" type="submit">Register</button>
    </form>
    <p>Already registered? <a href="login.php">Login here</a>.</p>
</section>

<?php require_once 'includes/footer.php'; ?>

