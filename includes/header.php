<?php require_once __DIR__ . '/functions.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jayarathne Construction</title>
    <link rel="stylesheet" href="css/style.css">
    <script defer src="js/script.js"></script>
</head>
<body>

<header class="topbar">
    <a class="brand" href="index.php">
        <img src="images/1.jpeg" alt="Jayarathne Construction Logo" class="brand-logo">
        <span>Jayarathne Construction</span>
    </a>

    <nav>
        <a href="index.php">Home</a>

        <?php if (!empty($_SESSION['user'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="projects.php">Projects</a>
            <a href="equipment.php">Equipment</a>
            <a href="rentals.php">Rentals</a>

            <?php if (current_role() === 'admin'): ?>
                <a href="reports.php">Reports</a>
            <?php endif; ?>

            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <?php show_message(); ?>