<?php
// partials/cart-modal.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';

$mysqli = $conn ?? $mysqli ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// fallback: cari user_id dari email jika belum di-session
if (!$user_id && !empty($_SESSION['user_email']) && $mysqli) {
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['user_email']);
        $stmt->execute();
        if (method_exists($stmt, 'get_result')) {
            $r = $stmt->get_result();
            if ($r && $r->num_rows) {
                $u = $r->fetch_assoc();
                $user_id = (int)$u['id'];
            }
        } else {
            $stmt->bind_result($uid);
            if ($stmt->fetch()) $user_id = (int)$uid;
        }
        $stmt->close();
    }
}

// === HANDLE POST ACTIONS HERE (remove / clear / update_qty) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cart_action']) && $mysqli) {
    $action = $_POST['cart_action'];

    // pastikan user logged-in ketika melakukan aksi yang butuh user_id
    if ($user_id) {
        if ($action === 'remove' && !empty($_POST['cart_id'])) {
            $cid = (int)$_POST['cart_id'];
            $stmt = $mysqli->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
            if ($stmt) {
                $stmt->bind_param("ii", $cid, $user_id);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($action === 'clear') {
            $stmt = $mysqli->prepare("DELETE FROM carts WHERE user_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();
            }
        } elseif ($action === 'update_qty' && !empty($_POST['cart_id']) && isset($_POST['new_qty'])) {
            $cid = (int)$_POST['cart_id'];
            $new_qty = (int)$_POST['new_qty'];
            
            // Jika quantity jadi 0 atau negatif, hapus item
            if ($new_qty <= 0) {
                $stmt = $mysqli->prepare("DELETE FROM carts WHERE id = ? AND user_id = ?");
                if ($stmt) {
                    $stmt->bind_param("ii", $cid, $user_id);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Update quantity
                $stmt = $mysqli->prepare("UPDATE carts SET quantity = ? WHERE id = ? AND user_id = ?");
                if ($stmt) {
                    $stmt->bind_param("iii", $new_qty, $cid, $user_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    // Redirect kembali ke halaman asal via JS (fallback ke menu.php)
    $redirect = $_SERVER['HTTP_REFERER'] ?? './menu.php';
    echo "<script>window.location.href = " . json_encode($redirect) . ";</script>";
    exit;
}
// === END POST HANDLER ===

// Ambil data cart hanya jika user ada
$cartRows = [];
$total = 0.0;
if ($user_id && $mysqli) {
    $sql = "
      SELECT c.id AS cart_id, c.menu_id, c.quantity, c.price AS cart_price,
             m.name AS menu_name, m.image AS menu_image
      FROM carts c
      JOIN menus m ON m.id = c.menu_id
      WHERE c.user_id = ?
      ORDER BY c.created_at DESC
    ";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) {
                $r['subtotal'] = (float)$r['cart_price'] * (int)$r['quantity'];
                $total += $r['subtotal'];
                $cartRows[] = $r;
            }
        } else {
            $stmt->bind_result($cart_id, $menu_id, $qty, $cart_price, $menu_name, $menu_image);
            while ($stmt->fetch()) {
                $row = [
                    'cart_id' => $cart_id,
                    'menu_id' => $menu_id,
                    'quantity' => $qty,
                    'cart_price' => $cart_price,
                    'menu_name' => $menu_name,
                    'menu_image' => $menu_image,
                ];
                $row['subtotal'] = (float)$row['cart_price'] * (int)$row['quantity'];
                $total += $row['subtotal'];
                $cartRows[] = $row;
            }
        }
        $stmt->close();
    }
}

// helper escape & format
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function money($v){ return number_format($v,0,',','.'); }
?>

<style>
.btn-delete-icon {
    background: none;
    border: none;
    color: #dc3545;
    font-size: 1.2rem;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-delete-icon:hover {
    background-color: #dc3545;
    color: white;
    transform: scale(1.1);
}

.btn-delete-icon:active {
    transform: scale(0.95);
}

.btn-delete-icon svg {
    width: 18px;
    height: 18px;
}

.qty-control {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 4px 8px;
}

.qty-btn {
    background: white;
    border: 1px solid #dee2e6;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.qty-btn:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.qty-btn:active {
    transform: scale(0.9);
}

.qty-display {
    min-width: 30px;
    text-align: center;
    font-weight: 600;
    color: #212529;
}
</style>

<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header border-0">
        <h5 class="modal-title">Keranjang Saya</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <?php if (!$user_id): ?>
          <div class="text-center py-4">
            <p class="mb-2">Silakan <a href="./login.php">login</a> untuk melihat keranjang Anda.</p>
          </div>
        <?php else: ?>

          <?php if (empty($cartRows)): ?>
            <div class="text-center py-4 text-muted">Keranjang Anda kosong.</div>
          <?php else: ?>

            <div class="list-group list-group-flush">
              <?php foreach ($cartRows as $row): ?>
                <div class="list-group-item d-flex align-items-center gap-3">
                  <div style="width:84px;flex:0 0 84px;">
                    <img src="<?= h($row['menu_image'] ?: './img/americano.jpg') ?>" alt="<?= h($row['menu_name']) ?>"
                         style="width:84px;height:64px;object-fit:cover;border-radius:8px;">
                  </div>

                  <div class="flex-grow-1">
                    <div class="fw-semibold mb-2"><?= h($row['menu_name']) ?></div>
                    
                    <!-- Quantity Control -->
                    <div class="qty-control">
                      <form method="post" action="" style="display:inline">
                        <input type="hidden" name="cart_action" value="update_qty">
                        <input type="hidden" name="cart_id" value="<?= (int)$row['cart_id'] ?>">
                        <input type="hidden" name="new_qty" value="<?= (int)$row['quantity'] - 1 ?>">
                        <button type="submit" class="qty-btn">−</button>
                      </form>
                      
                      <span class="qty-display"><?= (int)$row['quantity'] ?></span>
                      
                      <form method="post" action="" style="display:inline">
                        <input type="hidden" name="cart_action" value="update_qty">
                        <input type="hidden" name="cart_id" value="<?= (int)$row['cart_id'] ?>">
                        <input type="hidden" name="new_qty" value="<?= (int)$row['quantity'] + 1 ?>">
                        <button type="submit" class="qty-btn">+</button>
                      </form>
                    </div>
                  </div>

                  <div style="width:140px;text-align:right">
                    <div class="fw-semibold">Rp <?= money($row['cart_price']) ?></div>
                    <div class="text-muted small">Subtotal: Rp <?= money($row['subtotal']) ?></div>
                  </div>

                  <div style="width:40px;text-align:center">
                    <form method="post" action="" style="display:inline">
                      <input type="hidden" name="cart_action" value="remove">
                      <input type="hidden" name="cart_id" value="<?= (int)$row['cart_id'] ?>">
                      <button type="submit" class="btn-delete-icon" title="Hapus item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                          <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                          <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                        </svg>
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center" style="font-size:1.15rem; font-weight:700;">
              <div>Total</div>
              <div>Rp <?= money($total) ?></div>
            </div>

          <?php endif; ?>

        <?php endif; ?>
      </div>

      <div class="modal-footer border-0 d-flex justify-content-between">
        <a href="./menu.php" class="btn btn-dark px-4">Belanja lagi</a>
        <div>
          <?php if (!$user_id): ?>

            <!-- User belum login: tampilkan tombol ke login -->
            <a href="./login.php" class="btn btn-primary px-4">Login untuk lihat keranjang</a>

          <?php elseif (!empty($cartRows)): ?>

            <!-- User login & keranjang ADA -->
            <form method="post" action="" style="display:inline">
              <input type="hidden" name="cart_action" value="clear">
              <button type="submit" class="btn btn-outline-secondary me-2">Kosongkan</button>
            </form>

            <a href="./checkout.php" class="btn btn-primary px-4">Checkout</a>

          <?php else: ?>

            <!-- User login & keranjang KOSONG → TIDAK tampilkan tombol checkout -->
            <button class="btn btn-secondary px-4" disabled>Keranjang kosong</button>

          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>