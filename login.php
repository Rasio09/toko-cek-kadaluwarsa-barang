<?php
include 'koneksi.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $user = mysqli_fetch_assoc($result);
    if ($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - TokoApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
      body {
        background: linear-gradient(135deg, #6dd5ed 0%, #2193b0 100%);
        min-height: 100vh;
      }
      .login-card {
        max-width: 400px;
        margin: 60px auto;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        border-radius: 16px;
      }
      .login-logo {
        font-size: 3rem;
        color: #2193b0;
      }
    </style>
</head>
<body>
  <div class="container">
    <div class="login-card bg-white p-4">
      <div class="text-center mb-4">
        <span class="login-logo"><i class="bi bi-shop"></i></span>
        <h3 class="mt-2 mb-0 fw-bold">TokoApp</h3>
        <small class="text-muted">Silakan login untuk masuk</small>
      </div>
      <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= $error ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" required autofocus>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold">Login</button>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
