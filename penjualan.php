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

$role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];

// Handle form submit (tambah penjualan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barang_id']) && isset($_POST['jumlah'])) {
    $barang_id = (int) $_POST['barang_id'];
    $jumlah = (int) $_POST['jumlah'];

    // Ambil stok & harga jual dari tabel barang
    $q_barang = mysqli_query($conn, "SELECT jumlah, harga_jual, nama_barang FROM barang WHERE id = '$barang_id'");
    $barang = mysqli_fetch_assoc($q_barang);

    if ($barang) {
        $stok = (int)$barang['jumlah'];
        $harga_jual = (int)$barang['harga_jual'];
        $nama_barang = $barang['nama_barang'];

        if ($stok >= $jumlah && $jumlah > 0) {
            $total_harga = $jumlah * $harga_jual;

            // Simpan ke tabel penjualan
            $sql = "INSERT INTO penjualan (user_id, barang_id, jumlah, total_harga) 
                    VALUES ('$user_id', '$barang_id', '$jumlah', '$total_harga')";
            mysqli_query($conn, $sql);

            // Update stok barang
            $stok_baru = $stok - $jumlah;
            mysqli_query($conn, "UPDATE barang SET jumlah = '$stok_baru' WHERE id = '$barang_id'");

            header("Location: penjualan.php?success=1");
            exit;
        } else {
            // Jika stok tidak cukup
            header("Location: penjualan.php?error=stok_kurang&barang=" . urlencode($nama_barang));
            exit;
        }
    } else {
        header("Location: penjualan.php?error=barang_tidak_ditemukan");
        exit;
    }
}

// Ambil data barang untuk dropdown
$barang_list = mysqli_query($conn, "SELECT * FROM barang");

// Ambil data penjualan user ini (JOIN untuk mendapat nama_barang)
$penjualan_list = mysqli_query($conn, 
    "SELECT p.*, b.nama_barang 
     FROM penjualan p 
     JOIN barang b ON p.barang_id = b.id 
     WHERE p.user_id = '$user_id' 
     ORDER BY p.tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Penjualan - <?= htmlspecialchars($username) ?></title>
  <!-- Bootstrap CSS + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="p-4">
<!-- Navbar Bootstrap (sama seperti halaman dashboard) -->
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
            <a href="penjualan.php" class="nav-link active">Penjualan</a>
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
          <span class="text-white fw-semibold"><?= htmlspecialchars($username) ?></span>
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

<!-- Modal Logout (sama seperti halaman lain) -->
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
  <h2>Transaksi Penjualan - <?= htmlspecialchars($username) ?></h2>

  <!-- Tombol Riwayat Hapus (hanya user) -->
  <?php if ($role === 'user'): ?>
    <a href="riwayat_hapus_penjualan.php" class="btn btn-warning mb-3">
      <i class="bi bi-clock-history"></i> Riwayat Hapus Penjualan
    </a>
  <?php endif; ?>

  <!-- Form Input Penjualan -->
  <form method="POST" class="row g-3 mb-4">
    <div class="col-md-5">
      <label class="form-label">Pilih Barang</label>
      <select name="barang_id" class="form-select" required>
        <option value="">-- Pilih Barang --</option>
        <?php 
        // reset pointer result (jika perlu)
        if ($barang_list) {
            mysqli_data_seek($barang_list, 0);
            while ($b = mysqli_fetch_assoc($barang_list)): ?>
              <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nama_barang']) ?> (Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?>)</option>
        <?php endwhile; } ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Jumlah</label>
      <input type="number" name="jumlah" class="form-control" required min="1">
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">Tambah Penjualan</button>
    </div>
  </form>

  <!-- Tabel Riwayat Penjualan -->
  <div class="card">
    <div class="card-header bg-dark text-white">Riwayat Penjualan Anda</div>
    <div class="card-body">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>No</th>
            <th>Barang</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; while ($p = mysqli_fetch_assoc($penjualan_list)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($p['nama_barang']) ?></td>
            <td><?= (int)$p['jumlah'] ?></td>
            <td>Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></td>
            <td><?= htmlspecialchars($p['tanggal']) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <a href="index.php" class="btn btn-secondary mt-3">⬅ Kembali</a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
