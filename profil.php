<?php
include 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$role    = $_SESSION['user']['role'];

$uploadDirFs  = __DIR__ . '/uploads/';   // path di server (filesystem)
$uploadDirUrl = 'uploads/';              // path untuk <img src="...">

// pastikan folder uploads tersedia & writable
if (!is_dir($uploadDirFs)) {
    @mkdir($uploadDirFs, 0755, true);
}

$query = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result  = $query->get_result();
$profile = $result->fetch_assoc();

$uploadError = '';
$msg = '';

if (isset($_POST['simpan'])) {
    $full_name = $_POST['full_name'] ?? null;
    $ttl       = $_POST['ttl'] ?? null;
    $alamat    = $_POST['alamat'] ?? null;
    $no_hp     = $_POST['no_hp'] ?? null;
    $status    = $_POST['status'] ?? null;
    $deskripsi = $_POST['deskripsi'] ?? null;

    // foto: default = yang lama (jika ada)
    $finalFoto = $profile['foto'] ?? null;

    // jika ada file yang diupload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $err = $_FILES['foto']['error'];

        if ($err === UPLOAD_ERR_OK) {
            $allowedExt = ['jpg','jpeg','png','gif','webp'];
            $maxSize    = 2 * 1024 * 1024; // 2MB

            $origName = $_FILES['foto']['name'];
            $tmpPath  = $_FILES['foto']['tmp_name'];
            $size     = $_FILES['foto']['size'];
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt)) {
                $uploadError = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, GIF, atau WEBP.";
            } elseif ($size > $maxSize) {
                $uploadError = "Ukuran file terlalu besar (maksimal 2MB).";
            } elseif (!is_writable($uploadDirFs)) {
                $uploadError = "Folder uploads tidak bisa ditulis. Periksa permission (0755/0775).";
            } else {
                // sanitize nama & buat unik
                $base   = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
                $newName = 'pf_' . uniqid() . '_' . $base . '.' . $ext;
                $destFs  = $uploadDirFs . $newName;

                if (move_uploaded_file($tmpPath, $destFs)) {
                    // hapus file lama jika ada & berbeda
                    if (!empty($finalFoto) && $finalFoto !== $newName && file_exists($uploadDirFs . $finalFoto)) {
                        @unlink($uploadDirFs . $finalFoto);
                    }
                    $finalFoto = $newName;
                } else {
                    $uploadError = "Gagal memindahkan file upload. Coba lagi.";
                }
            }
        } else {
            // map error code
            $map = [
                UPLOAD_ERR_INI_SIZE   => "File melebihi batas upload_max_filesize.",
                UPLOAD_ERR_FORM_SIZE  => "File melebihi batas MAX_FILE_SIZE di form.",
                UPLOAD_ERR_PARTIAL    => "File terupload sebagian.",
                UPLOAD_ERR_NO_FILE    => "Tidak ada file yang diupload.",
                UPLOAD_ERR_NO_TMP_DIR => "Folder tmp hilang.",
                UPLOAD_ERR_CANT_WRITE => "Gagal menulis file ke disk.",
                UPLOAD_ERR_EXTENSION  => "Upload dihentikan ekstensi PHP.",
            ];
            $uploadError = $map[$err] ?? "Terjadi kesalahan upload (code: $err).";
        }
    }

    // jika tidak ada error upload, simpan DB
    if ($uploadError === '') {
        if ($profile) {
            $stmt = $conn->prepare("UPDATE user_profiles 
                SET full_name=?, ttl=?, alamat=?, no_hp=?, status=?, deskripsi=?, foto=? 
                WHERE user_id=?");
            $stmt->bind_param("sssssssi", $full_name, $ttl, $alamat, $no_hp, $status, $deskripsi, $finalFoto, $user_id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("INSERT INTO user_profiles 
                (user_id, full_name, ttl, alamat, no_hp, status, deskripsi, foto) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $user_id, $full_name, $ttl, $alamat, $no_hp, $status, $deskripsi, $finalFoto);
            $stmt->execute();
        }
        header("Location: profil.php"); // refresh agar data terbaru tampil
        exit;
    } else {
        // ada error upload -> tampilkan pesan tanpa redirect
        $msg = $uploadError;
        // update data non-foto di variabel supaya form tidak kosong
        $profile = array_merge($profile ?: ['user_id'=>$user_id], [
            'full_name'=>$full_name,
            'ttl'=>$ttl,
            'alamat'=>$alamat,
            'no_hp'=>$no_hp,
            'status'=>$status,
            'deskripsi'=>$deskripsi,
            'foto'=>$profile['foto'] ?? null
        ]);
    }
}

// siapkan foto untuk ditampilkan
$displayPhoto = $uploadDirUrl . (!empty($profile['foto']) ? $profile['foto'] : 'default.png');
// NOTE: pastikan file uploads/default.png tersedia
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Profil Saya</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light">

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

<!-- Modal Logout -->
<div class="modal fade" id="logoutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Konfirmasi Logout</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
  <?php if (!empty($msg)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="card shadow-lg p-4">
    <div class="text-center">
      <img src="<?= htmlspecialchars($displayPhoto) ?>" 
           class="rounded-circle mb-3" width="150" height="150" style="object-fit:cover;">
      <h3><?= htmlspecialchars($profile['full_name'] ?? 'Nama belum diisi') ?></h3>
      <p class="text-muted small mb-3">(<?= htmlspecialchars($role) ?>)</p>
    </div>

    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Tempat Tanggal Lahir</label>
        <input type="text" name="ttl" class="form-control" value="<?= htmlspecialchars($profile['ttl'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Alamat Domisili</label>
        <textarea name="alamat" class="form-control"><?= htmlspecialchars($profile['alamat'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Nomor HP</label>
        <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($profile['no_hp'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <input type="text" name="status" class="form-control" value="<?= htmlspecialchars($profile['status'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Deskripsi Diri</label>
        <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($profile['deskripsi'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Foto Profil</label>
        <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
        <div class="form-text">Maks 2MB. Format: JPG, JPEG, PNG, GIF, WEBP.</div>
      </div>
      <button type="submit" name="simpan" class="btn btn-primary w-100">Simpan Profil</button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
