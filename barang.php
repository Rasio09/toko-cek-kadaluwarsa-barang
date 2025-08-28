<?php
include 'koneksi.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$role = $_SESSION['user']['role'];

// aksi hapus barang
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // ambil data barang dulu
    $result = mysqli_query($conn, "SELECT * FROM barang WHERE id=$id");
    $barang = mysqli_fetch_assoc($result);

    if ($barang) {
        // pindahkan ke tabel record
        $nama = $barang['nama_barang'];
        $kategori = $barang['kategori'];
        $jumlah = $barang['jumlah'];
        $tgl_masuk = $barang['tanggal_masuk'];
        $tgl_exp = $barang['tanggal_exp'];
        $harga_beli = $barang['harga_beli'];
        $harga_jual = $barang['harga_jual'];

        mysqli_query($conn, "INSERT INTO barang_record (nama_barang, kategori, jumlah, tanggal_masuk, tanggal_exp, harga_beli, harga_jual, status) 
                             VALUES ('$nama','$kategori','$jumlah','$tgl_masuk','$tgl_exp','$harga_beli','$harga_jual','dibuang')");

        // hapus dari tabel barang
        mysqli_query($conn, "DELETE FROM barang WHERE id=$id");
    }

    header("Location: barang.php");
    exit;
}

// fitur search barang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $result = mysqli_query($conn, "SELECT * FROM barang WHERE 
        nama_barang LIKE '%$search_escaped%' OR 
        kategori LIKE '%$search_escaped%' 
        ORDER BY tanggal_exp ASC");
} else {
    $result = mysqli_query($conn, "SELECT * FROM barang ORDER BY tanggal_exp ASC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Barang</title>
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
  <h2>Daftar Barang Aktif</h2>
  
  <!-- Form Search Barang -->
  <form class="mb-3" method="get" action="barang.php">
    <div class="input-group">
      <input type="text" name="search" class="form-control" placeholder="Cari nama/kategori barang..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-primary" type="submit">Cari</button>
      <?php if ($search !== ''): ?>
        <a href="barang.php" class="btn btn-outline-secondary">Reset</a>
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
        <?php if ($role === 'admin'): ?>
          <th>Harga Beli/pcs</th>
        <?php endif; ?>
        <th>Harga Jual/pcs</th>
        <?php if ($role === 'admin'): ?>
          <th>Aksi</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php if (mysqli_num_rows($result) === 0): ?>
        <tr>
          <td colspan="<?= $role === 'admin' ? '8' : '6' ?>" class="text-center">Tidak ada barang ditemukan.</td>
        </tr>
      <?php endif; ?>
      <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
          <td><?= $row['nama_barang'] ?></td>
          <td><?= $row['kategori'] ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td><?= $row['tanggal_masuk'] ?></td>
          <td><?= $row['tanggal_exp'] ?></td>
          <?php if ($role === 'admin'): ?>
            <td><?= number_format($row['harga_beli'],2) ?></td>
          <?php endif; ?>
          <td><?= number_format($row['harga_jual'],2) ?></td>
          <?php if ($role === 'admin'): ?>
          <td>
            <!-- Tombol buka modal -->
            <button class="btn btn-danger btn-sm" 
                    data-bs-toggle="modal" 
                    data-bs-target="#hapusModal<?= $row['id'] ?>">
              Hapus
            </button>

            <!-- Modal Bootstrap -->
            <div class="modal fade" id="hapusModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body text-center">
                    <p>Apakah Anda yakin ingin membuang barang <strong><?= $row['nama_barang'] ?></strong> ?</p>
                  </div>
                  <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="barang.php?hapus=<?= $row['id'] ?>" class="btn btn-danger">Ya, Buang</a>
                  </div>
                </div>
              </div>
            </div>
          </td>
          <?php endif; ?>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="index.php" class="btn btn-secondary">Kembali</a>
  <a href="record.php" class="btn btn-info">📜 Lihat Record Barang</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
