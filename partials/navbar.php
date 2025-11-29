<?php
// partials/navbar.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

$mysqli = $conn ?? $mysqli ?? null;
$cartCount = 0;
$isLogged = false;
$displayName = null;

if ($mysqli) {
    // Cari user_id dari session
    $user_id = $_SESSION['user_id'] ?? null;

    // Fallback kalau hanya ada email
    if (!$user_id && !empty($_SESSION['user_email'])) {
        $email = $_SESSION['user_email'];
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if (method_exists($stmt, 'get_result')) {
                $r = $stmt->get_result();
                if ($r && $r->num_rows) {
                    $row = $r->fetch_assoc();
                    $user_id = (int)$row['id'];
                }
            } else {
                $stmt->bind_result($uid);
                if ($stmt->fetch()) $user_id = (int)$uid;
            }
            $stmt->close();
        }
    }

    // Jika berhasil menemukan user_id
    if ($user_id) {
        $isLogged = true;

        // Ambil nama user
        $displayName = $_SESSION['user_name'] ?? null;
        if (!$displayName) {
            $stmt = $mysqli->prepare("SELECT name,email FROM users WHERE id = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                if (method_exists($stmt, 'get_result')) {
                    $r = $stmt->get_result();
                    if ($r && $r->num_rows) {
                        $u = $r->fetch_assoc();
                        $displayName = $u['name'] ?: $u['email'];
                    }
                } else {
                    $stmt->bind_result($name, $mail);
                    if ($stmt->fetch()) $displayName = $name ?: $mail;
                }
                $stmt->close();
            }
        }

        // Hitung jumlah item cart
        $stmt = $mysqli->prepare("SELECT COUNT(id) FROM carts WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            if (method_exists($stmt, 'get_result')) {
                $r = $stmt->get_result();
                if ($r && $r->num_rows) {
                    $c = $r->fetch_assoc();
                    $cartCount = (int)$c['COUNT(id)'];
                }
            } else {
                $stmt->bind_result($cnt);
                if ($stmt->fetch()) $cartCount = (int)$cnt;
            }
            $stmt->close();
        }
    }
}
?>

<header class="header">
  <div class="container-navbar">
    <nav class="navbar navbar-expand-lg navbar-dark">

      <a class="navbar-brand d-flex align-items-center" href="./index.php">
        <img src="./img/logo_kopisenja.jpg" alt="Logo Kopi Senja" class="header-logo me-2" />
        <h5 class="mb-0">Kopi Senja</h5>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarMenu">

        <ul class="nav ms-lg-auto flex-column flex-lg-row text-lg-end align-items-center">

          <li class="nav-item"><a class="nav-link text-white" href="./index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="./menu.php">Menu</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="./about.php">Tentang Kami</a></li>
          <li class="nav-item"><a class="nav-link text-white" href="./kontak.php">Kontak</a></li>

          <!-- RIWAYAT PESANAN – muncul hanya jika login -->
          <?php if ($isLogged): ?>
          <li class="nav-item">
            <a class="nav-link text-white" href="./riwayat_pesanan.php">Riwayat Pesanan</a>
          </li>
          <?php endif; ?>

          <!-- RIGHT ICONS -->
          <li class="nav-item nav-icons d-flex align-items-center gap-3">

            <!-- CART -->
            <?php if ($isLogged): ?>
              <a id="cartToggle" class="nav-link position-relative"
                 href="#" role="button" aria-label="Keranjang"
                 data-bs-toggle="modal" data-bs-target="#cartModal">
            <?php else: ?>
              <a id="cartToggle" class="nav-link position-relative"
                 href="./login.php" role="button" aria-label="Keranjang">
            <?php endif; ?>

                <i data-lucide="shopping-cart" class="icon-svg"></i>

                <span id="cartCountBadge"
                      style="position:absolute; top:-2px; right:-6px; background:#dc3545; color:white;
                             border-radius:50%; padding:2px 6px; font-size:10px;">
                    <?= htmlspecialchars((string)$cartCount) ?>
                </span>
              </a>

            <!-- PROFILE DROPDOWN -->
            <?php if ($isLogged): ?>
              <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center text-white"
                   href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                  <i data-lucide="user" class="icon-svg me-1"></i>
                  <span><?= htmlspecialchars($displayName ?? 'User') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item" href="./profile.php">Profil</a></li>
                  <li><a class="dropdown-item text-danger" href="./logout.php">Logout</a></li>
                </ul>
              </div>

            <?php else: ?>
              <a class="nav-link d-flex align-items-center text-white"
                 href="./login.php">
                <i data-lucide="user" class="icon-svg me-1"></i>
                <span>Login</span>
              </a>
            <?php endif; ?>

          </li>
        </ul>

      </div>
    </nav>
  </div>
</header>

<!-- Ikon Lucide -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>

<?php
// Tambahkan modal cart hanya jika login
if ($isLogged && file_exists(__DIR__ . '/cart-modal.php')) {
    include __DIR__ . '/cart-modal.php';
}
?>
