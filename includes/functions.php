<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/database.php';
function clean($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function require_login() { if (empty($_SESSION['user'])) { header('Location: login.php'); exit; } }
function require_role($roles) { require_login(); if (!in_array($_SESSION['user']['role'], (array)$roles, true)) { header('Location: dashboard.php'); exit; } }
function current_user_id() { return (int)($_SESSION['user']['id'] ?? 0); }
function current_role() { return $_SESSION['user']['role'] ?? 'guest'; }
function money($amount) { return 'Rs. ' . number_format((float)$amount, 2); }
function message($text) { $_SESSION['message'] = $text; }
function show_message() { if (!empty($_SESSION['message'])) { echo '<div class="notice">' . clean($_SESSION['message']) . '</div>'; unset($_SESSION['message']); } }
function csrf_token() { if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(24)); return $_SESSION['csrf_token']; }
function check_token() { if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) exit('Invalid form token. Go back and try again.'); }
