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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Aplikasi Toko</title>
  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

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
