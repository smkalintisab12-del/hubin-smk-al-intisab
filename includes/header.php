<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$active = $_GET['page'] ?? 'dashboard';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Hubin SMK AL INTISAB</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">HUBIN <span class="text-info">SMK AL INTISAB</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto">
        <?php
        $menus = [
          'dashboard' => 'Dashboard', 'siswa' => 'Data Siswa', 'guru' => 'Data Guru',
          'industri' => 'Data Industri', 'pkl' => 'Penempatan PKL',
          'monitoring' => 'Monitoring', 'penilaian' => 'Penilaian', 'laporan' => 'Laporan'
        ];
        foreach ($menus as $key => $label):
        ?>
          <li class="nav-item"><a class="nav-link <?= $active === $key ? 'active' : '' ?>" href="index.php?page=<?= e($key) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <span class="navbar-text me-3"><?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
      <a class="btn btn-outline-light btn-sm" href="logout.php">Keluar</a>
    </div>
  </div>
</nav>
<main class="container-fluid py-4">
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
    <?= e($f['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
