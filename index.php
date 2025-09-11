<?php
include 'koneksi.php';
date_default_timezone_set("Asia/Jakarta");

// cek barang yang kadaluarsa atau perlu dicek
$today = date('Y-m-d'); 
$sql = "SELECT * FROM barang";
$result = mysqli_query($conn, $sql);

$warning = false;
while ($row = mysqli_fetch_assoc($result)) {
    $exp_date = $row['tanggal_exp'];
    $diff = (strtotime($exp_date) - strtotime($today)) / (60 * 60 * 24);

    if ($diff <= 3 && $diff >= 0) {
        $warning = true;
        break;
    } elseif ($diff < 0) {
        $warning = true;
        break;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$role = $_SESSION['user']['role'];

// ---------------- DATA UNTUK GRAFIK ---------------- //
// 1. Grafik stok barang
$barangLabels = [];
$barangJumlah = [];
$sqlBarang = "SELECT nama_barang, jumlah FROM barang";
$resBarang = mysqli_query($conn, $sqlBarang);
while ($row = mysqli_fetch_assoc($resBarang)) {
    $barangLabels[] = $row['nama_barang'];
    $barangJumlah[] = $row['jumlah'];
}

// 2. Grafik kategori barang
$kategoriLabels = [];
$kategoriJumlah = [];
$sqlKategori = "SELECT kategori, COUNT(*) as total FROM barang GROUP BY kategori";
$resKategori = mysqli_query($conn, $sqlKategori);
while ($row = mysqli_fetch_assoc($resKategori)) {
    $kategoriLabels[] = $row['kategori'];
    $kategoriJumlah[] = $row['total'];
}

// 3. Grafik penjualan per hari (7 hari terakhir)
$penjualanTanggal = [];
$penjualanJumlah = [];
$sqlPenjualan = "SELECT DATE(tanggal) as tgl, SUM(jumlah) as total 
                 FROM penjualan 
                 WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                 GROUP BY DATE(tanggal)";
$resPenjualan = mysqli_query($conn, $sqlPenjualan);
while ($row = mysqli_fetch_assoc($resPenjualan)) {
    $penjualanTanggal[] = $row['tgl'];
    $penjualanJumlah[] = $row['total'];
}

// 4. Grafik transaksi dibatalkan
$batalTanggal = [];
$batalJumlah = [];
$sqlBatal = "SELECT DATE(tanggal_hapus) as tgl, COUNT(*) as total 
             FROM riwayat_hapus_penjualan 
             WHERE tanggal_hapus >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DATE(tanggal_hapus)";
$resBatal = mysqli_query($conn, $sqlBatal);
while ($row = mysqli_fetch_assoc($resBatal)) {
    $batalTanggal[] = $row['tgl'];
    $batalJumlah[] = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Aplikasi Toko</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="p-4">

<!-- Navbar Bootstrap -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">TokoApp</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="listBarangDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            List Barang
          </a>
          <ul class="dropdown-menu" aria-labelledby="listBarangDropdown">
            <li><a class="dropdown-item" href="barang.php">Barang</a></li>
            <?php if ($role === 'admin'): ?>
            <li><a class="dropdown-item" href="tambah_barang.php">Tambah Barang</a></li>
            <?php endif; ?>
            <li><a class="dropdown-item" href="cek_barang.php">Cek Barang</a></li>
            <li><a class="dropdown-item" href="record.php">Record Barang</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <?php if ($role === 'user'): ?>
            <a href="penjualan.php" class="nav-link">Penjualan</a>
          <?php endif; ?>
        </li>
        <?php if ($role === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link" href="user_management.php">User Management</a>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item d-flex align-items-center me-2">
          <span class="text-white fw-semibold"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-5"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
            <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                Logout
              </button>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Modal Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p>Apakah Anda yakin ingin logout?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
        <a href="logout.php" class="btn btn-danger">Ya, Logout</a>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <h1 class="mb-4">Dashboard Pemilik Toko</h1>

  <?php if ($warning): ?>
    <div class="alert alert-danger" role="alert">
      🚨 Barang ada yang harus dicek segera!!!
    </div>
  <?php else: ?>
    <div class="alert alert-success" role="alert">
      ✅ Barang aman semua
    </div>
  <?php endif; ?>

  <a href="barang.php" class="btn btn-primary">📦 Daftar Barang</a>
  <?php if ($role === 'admin'): ?>
    <a href="tambah_barang.php" class="btn btn-success">➕ Input Barang</a>
  <?php endif; ?>
  <a href="cek_barang.php" class="btn btn-warning">⚠️ Cek Barang</a>

  <!-- Bagian Grafik -->
  <div class="row mt-5">
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">📦 Stok Barang</div>
        <div class="card-body">
          <canvas id="stokChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">📊 Kategori Barang</div>
        <div class="card-body">
          <canvas id="kategoriChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">💰 Penjualan 7 Hari Terakhir</div>
        <div class="card-body">
          <canvas id="penjualanChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">❌ Transaksi Dibatalkan (7 Hari)</div>
        <div class="card-body">
          <canvas id="batalChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Data dari PHP ke JS
const barangLabels = <?= json_encode($barangLabels) ?>;
const barangJumlah = <?= json_encode($barangJumlah) ?>;
const kategoriLabels = <?= json_encode($kategoriLabels) ?>;
const kategoriJumlah = <?= json_encode($kategoriJumlah) ?>;
const penjualanTanggal = <?= json_encode($penjualanTanggal) ?>;
const penjualanJumlah = <?= json_encode($penjualanJumlah) ?>;
const batalTanggal = <?= json_encode($batalTanggal) ?>;
const batalJumlah = <?= json_encode($batalJumlah) ?>;

// Grafik stok barang
new Chart(document.getElementById('stokChart'), {
    type: 'bar',
    data: {
        labels: barangLabels,
        datasets: [{
            label: 'Jumlah Stok',
            data: barangJumlah,
            backgroundColor: 'rgba(54, 162, 235, 0.7)'
        }]
    }
});

// Grafik kategori barang
new Chart(document.getElementById('kategoriChart'), {
    type: 'pie',
    data: {
        labels: kategoriLabels,
        datasets: [{
            data: kategoriJumlah,
            backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff']
        }]
    }
});

// Grafik penjualan
new Chart(document.getElementById('penjualanChart'), {
    type: 'line',
    data: {
        labels: penjualanTanggal,
        datasets: [{
            label: 'Jumlah Terjual',
            data: penjualanJumlah,
            borderColor: 'rgba(75, 192, 192, 1)',
            fill: false
        }]
    }
});

// Grafik transaksi batal
new Chart(document.getElementById('batalChart'), {
    type: 'bar',
    data: {
        labels: batalTanggal,
        datasets: [{
            label: 'Jumlah Transaksi Dibatalkan',
            data: batalJumlah,
            backgroundColor: 'rgba(255, 99, 132, 0.7)'
        }]
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
