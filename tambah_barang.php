<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $jumlah = $_POST['jumlah'];
    $tgl_masuk = $_POST['tanggal_masuk'];
    $tgl_exp = $_POST['tanggal_exp'];

    $sql = "INSERT INTO barang (nama_barang, kategori, jumlah, tanggal_masuk, tanggal_exp)
            VALUES ('$nama', '$kategori', '$jumlah', '$tgl_masuk', '$tgl_exp')";

    if (mysqli_query($conn, $sql)) {
        header("Location: barang.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Barang</title>
  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
  <h2>Tambah Barang</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Nama Barang</label>
      <input type="text" name="nama_barang" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Kategori</label>
      <input type="text" name="kategori" class="form-control">
    </div>
    <div class="mb-3">
      <label>Jumlah</label>
      <input type="number" name="jumlah" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Tanggal Masuk</label>
      <input type="date" name="tanggal_masuk" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Tanggal Expired</label>
      <input type="date" name="tanggal_exp" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

</body>
</html>
