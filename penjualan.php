<?php
include 'koneksi.php';
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

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barang_id = $_POST['barang_id'];
    $jumlah = $_POST['jumlah'];

    // Ambil harga jual dari tabel barang
    $q_barang = mysqli_query($conn, "SELECT harga_jual FROM barang WHERE id = '$barang_id'");
    $barang = mysqli_fetch_assoc($q_barang);
    $harga_jual = $barang['harga_jual'];

    $total_harga = $jumlah * $harga_jual;

    // Simpan ke tabel penjualan
    $sql = "INSERT INTO penjualan (user_id, barang_id, jumlah, total_harga) 
            VALUES ('$user_id', '$barang_id', '$jumlah', '$total_harga')";
    mysqli_query($conn, $sql);
    header("Location: penjualan.php");
    exit;
}

// Ambil data barang untuk dropdown
$barang_list = mysqli_query($conn, "SELECT * FROM barang");

// Ambil data penjualan user ini
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
  <h2>Transaksi Penjualan - <?= htmlspecialchars($username) ?></h2>

  <!-- Form Input Penjualan -->
  <form method="POST" class="row g-3 mb-4">
    <div class="col-md-5">
      <label class="form-label">Pilih Barang</label>
      <select name="barang_id" class="form-select" required>
        <option value="">-- Pilih Barang --</option>
        <?php while ($b = mysqli_fetch_assoc($barang_list)): ?>
          <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nama_barang']) ?> (Rp <?= number_format($b['harga_jual'], 0, ',', '.') ?>)</option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Jumlah</label>
      <input type="number" name="jumlah" class="form-control" required>
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
            <td><?= $p['jumlah'] ?></td>
            <td>Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></td>
            <td><?= $p['tanggal'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <a href="index.php" class="btn btn-secondary mt-3">⬅ Kembali</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
