<?php
require __DIR__ . '/app/config/database.php';
require __DIR__ . '/app/helpers/auth.php';

if (is_logged_in()) redirect('index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, nama, username, password FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_name'] = $admin['nama'];
        redirect('index.php');
    }
    $error = 'Username atau password tidak sesuai.';
}
?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Hubin SMK AL INTISAB</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/app.css" rel="stylesheet"></head>
<body class="d-flex align-items-center min-vh-100">
<div class="container"><div class="row justify-content-center"><div class="col-md-5 col-lg-4">
<div class="card p-4">
<h4 class="fw-bold mb-1">HUBIN SMK AL INTISAB</h4><p class="text-muted">Silakan masuk ke sistem</p>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label">Username</label><input class="form-control" name="username" required autofocus></div>
<div class="mb-3"><label class="form-label">Password</label><input class="form-control" type="password" name="password" required></div>
<button class="btn btn-dark w-100">Masuk</button>
</form></div></div></div></div></body></html>
