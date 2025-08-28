<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$role = $_SESSION['user']['role'];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $role = $_POST['role'];
    $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if (mysqli_query($conn, $sql)) {
        $msg = "User berhasil ditambahkan!";
    } else {
        $msg = "Gagal: " . mysqli_error($conn);
    }
}
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $ttl = $_POST['ttl'];
    $alamat = $_POST['alamat'];
    $email = $_POST['email'];
    $foto = $_POST['foto']; // atau proses upload file

    mysqli_query($conn, "INSERT INTO users (nama, ttl, alamat, email, foto) VALUES ('$nama', '$ttl', '$alamat', '$email', '$foto')");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah User</title>
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
  <h2>Tambah User Baru</h2>
  <?php if ($msg): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label>Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Role</label>
      <select name="role" class="form-control" required>
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Tambah User</button>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
  </form>

  <h3 class="mt-5">Atau</h3>
  <form method="post">
    <div class="mb-3">
      <label>Nama Lengkap</label>
      <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
    </div>
    <div class="mb-3">
      <label>Tempat, Tanggal Lahir</label>
      <input type="text" name="ttl" class="form-control" placeholder="Tempat, Tanggal Lahir" required>
    </div>
    <div class="mb-3">
      <label>Alamat</label>
      <input type="text" name="alamat" class="form-control" placeholder="Alamat" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" placeholder="Email" required>
    </div>
    <div class="mb-3">
      <label>Foto (nama file)</label>
      <input type="text" name="foto" class="form-control" placeholder="Foto (nama file)" required>
    </div>
    <button type="submit" name="tambah" class="btn btn-success">Tambah User</button>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>
</body>
</html>
