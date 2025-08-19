<?php
include 'koneksi.php';
date_default_timezone_set("Asia/Jakarta");
$today = date('Y-m-d');

$sql = "SELECT * FROM barang";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cek Barang</title>
  <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<div class="container">
  <h2>Barang Mendekati Kadaluarsa</h2>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Nama Barang</th>
        <th>Kategori</th>
        <th>Jumlah</th>
        <th>Tanggal Expired</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($result)) : 
        $exp_date = $row['tanggal_exp'];
        $diff = (strtotime($exp_date) - strtotime($today)) / (60 * 60 * 24);

        if ($diff <= 3 && $diff >= 0) {
            $status = "⚠️ Perlu dicek";
            $class = "table-warning";
        } elseif ($diff < 0) {
            $status = "❌ Kadaluarsa";
            $class = "table-danger";
        } else {
            continue;
        }
      ?>
        <tr class="<?= $class ?>">
          <td><?= $row['nama_barang'] ?></td>
          <td><?= $row['kategori'] ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= $row['tanggal_exp'] ?></td>
          <td><?= $status ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="index.php" class="btn btn-secondary">Kembali</a>
</div>

</body>
</html>
