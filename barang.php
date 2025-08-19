<?php
include 'koneksi.php';
$result = mysqli_query($conn, "SELECT * FROM barang ORDER BY tanggal_exp ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Barang</title>
  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
  <h2>Daftar Barang</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Nama Barang</th>
        <th>Kategori</th>
        <th>Jumlah</th>
        <th>Tanggal Masuk</th>
        <th>Tanggal Expired</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
          <td><?= $row['nama_barang'] ?></td>
          <td><?= $row['kategori'] ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= $row['tanggal_masuk'] ?></td>
          <td><?= $row['tanggal_exp'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="index.php" class="btn btn-secondary">Kembali</a>
</div>

</body>
</html>
