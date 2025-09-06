<?php
include 'koneksi.php';
date_default_timezone_set("Asia/Jakarta");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hanya user biasa yang bisa akses
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

// Ambil data riwayat batal transaksi
$q_batal = mysqli_query($conn, "SELECT r.*, b.nama_barang 
                                FROM riwayat_hapus_penjualan r
                                JOIN barang b ON r.barang_id = b.id
                                WHERE r.user_id = '$user_id'
                                ORDER BY r.tanggal_hapus DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Batal Penjualan - <?= htmlspecialchars($username) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="p-4">

<!-- Navbar -->
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

<div class="container">
    <h2>📜 Riwayat Batal Penjualan - <?= htmlspecialchars($username) ?></h2>

    <div class="card mt-3">
        <div class="card-header bg-warning text-dark fw-bold">
            Daftar Riwayat Pembatalan Transaksi
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Barang</th>
                        <th>Jumlah</th>
                        <th>Total Harga</th>
                        <th>Tanggal Penjualan</th>
                        <th>Alasan Hapus</th>
                        <th>Tanggal Hapus</th>
                    </tr>
                </thead>
                <tbody>
                <?php 
                if (mysqli_num_rows($q_batal) > 0): 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($q_batal)): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                        <td><?= (int)$row['jumlah'] ?></td>
                        <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($row['tanggal_penjualan']) ?></td>
                        <td><?= htmlspecialchars($row['alasan_hapus']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal_hapus']) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada riwayat pembatalan transaksi.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="penjualan.php" class="btn btn-secondary mt-3">⬅ Kembali ke Penjualan</a>
</div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
