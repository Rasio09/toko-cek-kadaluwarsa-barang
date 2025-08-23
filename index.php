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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Aplikasi Toko</title>
  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
        <?php if ($role === 'admin'): ?>
        <li class="nav-item">
          <a class="nav-link" href="user_management.php">User Management</a>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-5"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
            <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

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
  <a href="tambah_barang.php" class="btn btn-success">➕ Input Barang</a>
  <a href="cek_barang.php" class="btn btn-warning">⚠️ Cek Barang</a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
