<?php
require __DIR__ . '/app/config/database.php';
require __DIR__ . '/app/helpers/auth.php';
require_login();

if (isset($_GET['export'])) {
    $type = $_GET['export'];
    if ($type === 'pkl') {
        $sql = 'SELECT s.nis,s.nama siswa,i.nama_perusahaan,g.nama guru,p.tanggal_mulai,p.tanggal_selesai,p.status FROM pkl p JOIN siswa s ON s.id=p.siswa_id JOIN industri i ON i.id=p.industri_id JOIN guru g ON g.id=p.guru_id ORDER BY p.id DESC';
        $filename = 'laporan-penempatan-pkl.csv';
    } elseif ($type === 'nilai') {
        $sql = 'SELECT s.nis,s.nama siswa,i.nama_perusahaan,n.aspek,n.nilai,n.keterangan FROM penilaian n JOIN pkl p ON p.id=n.pkl_id JOIN siswa s ON s.id=p.siswa_id JOIN industri i ON i.id=p.industri_id ORDER BY n.id DESC';
        $filename = 'laporan-penilaian-pkl.csv';
    } else {
        http_response_code(404); exit('Laporan tidak ditemukan.');
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $rows = $pdo->query($sql);
    $first = $rows->fetch();
    if ($first) {
        fputcsv($out, array_keys($first));
        fputcsv($out, array_values($first));
        while ($row = $rows->fetch()) fputcsv($out, array_values($row));
    }
    fclose($out); exit;
}

$page = $_GET['page'] ?? 'dashboard';
$allowed = ['dashboard','siswa','guru','industri','pkl','monitoring','penilaian','laporan'];
if (!in_array($page, $allowed, true)) $page = 'dashboard';

$entities = [
  'siswa' => ['title'=>'Data Siswa','table'=>'siswa','fields'=>[
    'nis'=>'NIS','nama'=>'Nama Siswa','kelas'=>'Kelas','jurusan'=>'Jurusan','no_hp'=>'No. HP'
  ]],
  'guru' => ['title'=>'Data Guru','table'=>'guru','fields'=>[
    'nip'=>'NIP','nama'=>'Nama Guru','jabatan'=>'Jabatan','no_hp'=>'No. HP'
  ]],
  'industri' => ['title'=>'Data Industri','table'=>'industri','fields'=>[
    'nama_perusahaan'=>'Nama Perusahaan','bidang'=>'Bidang Usaha','alamat'=>'Alamat','nama_pic'=>'Nama PIC','no_hp'=>'No. HP','email'=>'Email'
  ]],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $target = $_POST['target'] ?? '';
    if (isset($entities[$target]) && in_array($action, ['save','delete'], true)) {
        $cfg = $entities[$target]; $table = $cfg['table'];
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
            $stmt->execute([(int)($_POST['id'] ?? 0)]);
            flash('Data berhasil dihapus.');
        } else {
            $id = (int)($_POST['id'] ?? 0); $values = [];
            foreach ($cfg['fields'] as $field => $label) $values[$field] = trim($_POST[$field] ?? '');
            if ($id > 0) {
                $sets = implode(', ', array_map(fn($f) => "{$f} = ?", array_keys($values)));
                $stmt = $pdo->prepare("UPDATE {$table} SET {$sets} WHERE id = ?");
                $stmt->execute([...array_values($values), $id]);
            } else {
                $cols = implode(', ', array_keys($values));
                $marks = implode(', ', array_fill(0, count($values), '?'));
                $stmt = $pdo->prepare("INSERT INTO {$table} ({$cols}) VALUES ({$marks})");
                $stmt->execute(array_values($values));
            }
            flash('Data berhasil disimpan.');
        }
        redirect('index.php?page=' . $target);
    }
    if ($action === 'save_pkl') {
        $stmt = $pdo->prepare('INSERT INTO pkl (siswa_id, industri_id, guru_id, tanggal_mulai, tanggal_selesai, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([(int)$_POST['siswa_id'],(int)$_POST['industri_id'],(int)$_POST['guru_id'],$_POST['tanggal_mulai'],$_POST['tanggal_selesai'],$_POST['status']]);
        flash('Penempatan PKL berhasil ditambahkan.'); redirect('index.php?page=pkl');
    }
    if ($action === 'save_monitoring') {
        $stmt = $pdo->prepare('INSERT INTO monitoring (pkl_id, tanggal, catatan, tindak_lanjut) VALUES (?,?,?,?)');
        $stmt->execute([(int)$_POST['pkl_id'],$_POST['tanggal'],trim($_POST['catatan']),trim($_POST['tindak_lanjut'])]);
        flash('Monitoring berhasil disimpan.'); redirect('index.php?page=monitoring');
    }
    if ($action === 'save_penilaian') {
        $nilai = max(0, min(100, (float)$_POST['nilai']));
        $stmt = $pdo->prepare('INSERT INTO penilaian (pkl_id, aspek, nilai, keterangan) VALUES (?,?,?,?)');
        $stmt->execute([(int)$_POST['pkl_id'],trim($_POST['aspek']),$nilai,trim($_POST['keterangan'])]);
        flash('Penilaian berhasil disimpan.'); redirect('index.php?page=penilaian');
    }
}

$pageTitle = $page === 'dashboard' ? 'Dashboard' : ($entities[$page]['title'] ?? ucfirst($page));
require __DIR__ . '/includes/header.php';

function count_rows(PDO $pdo, string $table): int {
    return (int)$pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
}
function pkl_options(PDO $pdo): array {
    return $pdo->query("SELECT p.id, s.nama AS siswa, i.nama_perusahaan FROM pkl p JOIN siswa s ON s.id=p.siswa_id JOIN industri i ON i.id=p.industri_id ORDER BY p.id DESC")->fetchAll();
}
?>

<?php if ($page === 'dashboard'): ?>
<h3 class="mb-4">Dashboard Hubungan Industri</h3>
<div class="row g-3">
<?php foreach (['siswa'=>'Data Siswa','guru'=>'Data Guru','industri'=>'Data Industri','pkl'=>'Penempatan PKL'] as $table=>$label): ?>
<div class="col-6 col-xl-3"><div class="card p-3"><div class="text-muted"><?= e($label) ?></div><div class="stat-number"><?= count_rows($pdo,$table) ?></div></div></div>
<?php endforeach; ?>
</div>
<div class="card p-3 mt-4"><h5>Selamat datang di Sistem Informasi Hubin</h5>
<p class="mb-0 text-muted">Kelola data peserta didik, guru pembimbing, mitra industri, penempatan PKL, monitoring, dan penilaian dalam satu aplikasi.</p></div>

<?php elseif (isset($entities[$page])): $cfg=$entities[$page]; $rows=$pdo->query("SELECT * FROM {$cfg['table']} ORDER BY id DESC")->fetchAll(); ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h3><?= e($cfg['title']) ?></h3><button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#formData">Tambah Data</button></div>
<div class="collapse mb-3" id="formData"><div class="card p-3"><form method="post" class="row g-3">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="target" value="<?= e($page) ?>">
<?php foreach($cfg['fields'] as $field=>$label): ?><div class="col-md-6"><label class="form-label"><?= e($label) ?></label><input class="form-control" name="<?= e($field) ?>" required></div><?php endforeach; ?>
<div class="col-12"><button class="btn btn-success">Simpan</button></div></form></div></div>
<div class="card p-3"><div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><?php foreach($cfg['fields'] as $label): ?><th><?= e($label) ?></th><?php endforeach; ?><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $row): ?><tr><?php foreach($cfg['fields'] as $field=>$label): ?><td><?= e((string)$row[$field]) ?></td><?php endforeach; ?><td><form method="post" onsubmit="return confirm('Hapus data ini?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="target" value="<?= e($page) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="<?= count($cfg['fields'])+1 ?>" class="text-center text-muted">Belum ada data.</td></tr><?php endif; ?>
</tbody></table></div></div>

<?php elseif ($page === 'pkl'): ?>
<h3 class="mb-3">Penempatan PKL</h3>
<div class="card p-3 mb-3"><form method="post" class="row g-3">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_pkl">
<div class="col-md-6"><label class="form-label">Siswa</label><select class="form-select" name="siswa_id" required><option value="">Pilih siswa</option><?php foreach($pdo->query('SELECT id,nis,nama FROM siswa ORDER BY nama') as $r): ?><option value="<?= (int)$r['id'] ?>"><?= e($r['nis'].' - '.$r['nama']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Industri</label><select class="form-select" name="industri_id" required><option value="">Pilih industri</option><?php foreach($pdo->query('SELECT id,nama_perusahaan FROM industri ORDER BY nama_perusahaan') as $r): ?><option value="<?= (int)$r['id'] ?>"><?= e($r['nama_perusahaan']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Guru Pembimbing</label><select class="form-select" name="guru_id" required><option value="">Pilih guru</option><?php foreach($pdo->query('SELECT id,nama FROM guru ORDER BY nama') as $r): ?><option value="<?= (int)$r['id'] ?>"><?= e($r['nama']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Tanggal Mulai</label><input type="date" class="form-control" name="tanggal_mulai" required></div>
<div class="col-md-3"><label class="form-label">Tanggal Selesai</label><input type="date" class="form-control" name="tanggal_selesai" required></div>
<div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option>Berjalan</option><option>Selesai</option><option>Dibatalkan</option></select></div>
<div class="col-12"><button class="btn btn-success">Simpan Penempatan</button></div></form></div>
<div class="card p-3 table-responsive"><table class="table table-striped"><thead><tr><th>Siswa</th><th>Industri</th><th>Guru</th><th>Periode</th><th>Status</th></tr></thead><tbody>
<?php $rows=$pdo->query('SELECT p.*,s.nama siswa,i.nama_perusahaan,g.nama guru FROM pkl p JOIN siswa s ON s.id=p.siswa_id JOIN industri i ON i.id=p.industri_id JOIN guru g ON g.id=p.guru_id ORDER BY p.id DESC')->fetchAll(); foreach($rows as $r): ?><tr><td><?= e($r['siswa']) ?></td><td><?= e($r['nama_perusahaan']) ?></td><td><?= e($r['guru']) ?></td><td><?= e($r['tanggal_mulai'].' s.d. '.$r['tanggal_selesai']) ?></td><td><?= e($r['status']) ?></td></tr><?php endforeach; ?></tbody></table></div>

<?php elseif ($page === 'monitoring'): ?>
<h3 class="mb-3">Monitoring PKL</h3>
<div class="card p-3 mb-3"><form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_monitoring">
<div class="col-md-6"><label class="form-label">Penempatan PKL</label><select class="form-select" name="pkl_id" required><option value="">Pilih penempatan</option><?php foreach(pkl_options($pdo) as $r): ?><option value="<?= (int)$r['id'] ?>"><?= e($r['siswa'].' — '.$r['nama_perusahaan']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" class="form-control" name="tanggal" required></div>
<div class="col-md-12"><label class="form-label">Catatan Monitoring</label><textarea class="form-control" name="catatan" required></textarea></div>
<div class="col-md-12"><label class="form-label">Tindak Lanjut</label><textarea class="form-control" name="tindak_lanjut"></textarea></div>
<div class="col-12"><button class="btn btn-success">Simpan Monitoring</button></div></form></div>
<div class="card p-3 table-responsive"><table class="table"><thead><tr><th>Tanggal</th><th>Siswa</th><th>Industri</th><th>Catatan</th><th>Tindak Lanjut</th></tr></thead><tbody>
<?php $rows=$pdo->query('SELECT m.*,s.nama siswa,i.nama_perusahaan FROM monitoring m JOIN pkl p ON p.id=m.pkl_id JOIN siswa s ON s.id=p.siswa_id JOIN industri i ON i.id=p.industri_id ORDER BY m.tanggal DESC')->fetchAll(); foreach($rows as $r): ?><tr><td><?= e($r['tanggal']) ?></td><td><?= e($r['siswa']) ?></td><td><?= e($r['nama_perusahaan']) ?></td><td><?= e($r['catatan']) ?></td><td><?= e($r['tindak_lanjut']) ?></td></tr><?php endforeach; ?></tbody></table></div>

<?php elseif ($page === 'penilaian'): ?>
<h3 class="mb-3">Penilaian PKL</h3>
<div class="card p-3 mb-3"><form method="post" class="row g-3"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save_penilaian">
<div class="col-md-6"><label class="form-label">Penempatan PKL</label><select class="form-select" name="pkl_id" required><option value="">Pilih penempatan</option><?php foreach(pkl_options($pdo) as $r): ?><option value="<?= (int)$r['id'] ?>"><?= e($r['siswa'].' — '.$r['nama_perusahaan']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Aspek</label><input class="form-control" name="aspek" placeholder="Contoh: Disiplin" required></div>
<div class="col-md-3"><label class="form-label">Nilai (0-100)</label><input type="number" min="0" max="100" step="0.01" class="form-control" name="nilai" required></div>
<div class="col-md-12"><label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan"></textarea></div>
<div class="col-12"><button class="btn btn-success">Simpan Penilaian</button></div></form></div>
<div class="card p-3 table-responsive"><table class="table"><thead><tr><th>Siswa</th><th>Industri</th><th>Aspek</th><th>Nilai</th><th>Keterangan</th></tr></thead><tbody>
<?php $rows=$pdo->query('SELECT n.*,s.nama siswa,i.nama_perusahaan FROM penilaian n JOIN pkl p ON p.id=n.pkl_id JOIN siswa s ON s.id=p.siswa_id JOIN industri i ON i.id=p.industri_id ORDER BY n.id DESC')->fetchAll(); foreach($rows as $r): ?><tr><td><?= e($r['siswa']) ?></td><td><?= e($r['nama_perusahaan']) ?></td><td><?= e($r['aspek']) ?></td><td><?= e((string)$r['nilai']) ?></td><td><?= e($r['keterangan']) ?></td></tr><?php endforeach; ?></tbody></table></div>

<?php elseif ($page === 'laporan'): ?>
<h3 class="mb-3">Laporan Hubin</h3>
<div class="row g-3">
<div class="col-md-6"><div class="card p-4"><h5>Laporan Penempatan PKL</h5><p class="text-muted">Unduh daftar penempatan PKL dalam format CSV.</p><a class="btn btn-primary" href="index.php?export=pkl">Unduh CSV</a></div></div>
<div class="col-md-6"><div class="card p-4"><h5>Laporan Penilaian PKL</h5><p class="text-muted">Unduh rekap penilaian peserta PKL.</p><a class="btn btn-primary" href="index.php?export=nilai">Unduh CSV</a></div></div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
