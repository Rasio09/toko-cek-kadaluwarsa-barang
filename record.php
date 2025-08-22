<?php
include 'koneksi.php';

// aksi restore
if (isset($_GET['restore'])) {
    $id = $_GET['restore'];

    // ambil data dari record
    $result = mysqli_query($conn, "SELECT * FROM barang_record WHERE id=$id");
    $record = mysqli_fetch_assoc($result);

    if ($record) {
        $nama = $record['nama_barang'];
        $kategori = $record['kategori'];
        $jumlah = $record['jumlah'];
        $tgl_masuk = $record['tanggal_masuk'];
        $tgl_exp = $record['tanggal_exp'];

        // kembalikan ke tabel barang
        mysqli_query($conn, "INSERT INTO barang (nama_barang, kategori, jumlah, tanggal_masuk, tanggal_exp, status) 
                             VALUES ('$nama','$kategori','$jumlah','$tgl_masuk','$tgl_exp','aman')");

        // hapus dari record
        mysqli_query($conn, "DELETE FROM barang_record WHERE id=$id");
    }

    header("Location: record.php");
    exit;
}

// fitur search barang record
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $result = mysqli_query($conn, "SELECT * FROM barang_record WHERE 
        nama_barang LIKE '%$search_escaped%' OR 
        kategori LIKE '%$search_escaped%' 
        ORDER BY tanggal_masuk DESC");
} else {
    $result = mysqli_query($conn, "SELECT * FROM barang_record ORDER BY tanggal_masuk DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Record Barang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
  <h2>Record Barang (Barang yang Sudah Dibuang)</h2>
  
  <!-- Form Search Barang Record -->
  <form class="mb-3" method="get" action="record.php">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Cari nama/kategori barang..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-primary" type="submit">Cari</button>
      <?php if ($search !== ''): ?>
        <a href="record.php" class="btn btn-outline-secondary">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th>Nama Barang</th>
        <th>Kategori</th>
        <th>Jumlah</th>
        <th>Tanggal Masuk</th>
        <th>Tanggal Expired</th>
        <th>Tanggal Dibuang</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($result) === 0): ?>
        <tr>
          <td colspan="6" class="text-center">Tidak ada barang ditemukan.</td>
        </tr>
      <?php endif; ?>
      <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
          <td><?= $row['nama_barang'] ?></td>
          <td><?= $row['kategori'] ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= $row['tanggal_masuk'] ?></td>
          <td><?= $row['tanggal_exp'] ?></td>
          <td><?= $row['tanggal_dibuang'] ?></td>
          <td>
            <!-- Tombol buka modal -->
            <button class="btn btn-success btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#restoreModal<?= $row['id'] ?>">
              Restore
            </button>

            <!-- Modal Bootstrap -->
            <div class="modal fade" id="restoreModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Konfirmasi Restore</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body text-center">
                    <p>Apakah Anda yakin ingin mengembalikan barang <strong><?= $row['nama_barang'] ?></strong> ke daftar aktif?</p>
                  </div>
                  <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="record.php?restore=<?= $row['id'] ?>" class="btn btn-success">Ya, Kembalikan</a>
                  </div>
                </div>
              </div>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="index.php" class="btn btn-secondary">Kembali</a>
  <a href="barang.php" class="btn btn-primary">📦 Lihat Daftar Barang</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
