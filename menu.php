<?php
session_start();
require_once __DIR__ . '/config.php';

// gunakan koneksi dari config (biasanya $conn)
$mysqli = $conn ?? $mysqli ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_id'])) {
    if (!$mysqli) {
        error_log("DB connection missing while handling add-to-cart in menu.php");
        $_SESSION['flash'] = "Koneksi database bermasalah.";
        $_SESSION['flash_type'] = 'danger';
        header("Location: " . ($_SERVER['REQUEST_URI'] ?? '/menu.php'));
        exit;
    }

    // pastikan user login
    if (!isset($_SESSION['user_email'])) {
        $_SESSION['flash'] = "Silakan login terlebih dahulu.";
        $_SESSION['flash_type'] = 'danger';
        header("Location: " . ($_SERVER['REQUEST_URI'] ?? '/menu.php'));
        exit;
    }

    $user_email = $_SESSION['user_email'];
    $menu_id  = isset($_POST['menu_id']) ? (int) $_POST['menu_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    if ($menu_id <= 0 || $quantity <= 0) {
        $_SESSION['flash'] = "Data tidak valid.";
        $_SESSION['flash_type'] = 'danger';
        header("Location: " . ($_SERVER['REQUEST_URI'] ?? '/menu.php'));
        exit;
    }

    // ambil user_id berdasarkan email
    $user_id = null;
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $user_email);
        $stmt->execute();

        if (method_exists($stmt, 'get_result')) {
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $user_id = (int)$row['id'];
            }
        } else {
            $stmt->bind_result($uid);
            if ($stmt->fetch()) $user_id = (int)$uid;
        }
        $stmt->close();
    } else {
        error_log("Prepare failed (select user): " . $mysqli->error);
    }

    if (!$user_id) {
        $_SESSION['flash'] = "User tidak ditemukan.";
        $_SESSION['flash_type'] = 'danger';
        header("Location: " . ($_SERVER['REQUEST_URI'] ?? '/menu.php'));
        exit;
    }

    // mulai transaction
    $mysqli->begin_transaction();
    try {
        // ambil price dari menus
        $price = null;
        $stmt = $mysqli->prepare("SELECT price, name FROM menus WHERE id = ? LIMIT 1");
        if (!$stmt) throw new Exception("Prepare failed (select menu): " . $mysqli->error);
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();

        $menuName = '';
        if (method_exists($stmt, 'get_result')) {
            $r = $stmt->get_result();
            if ($r->num_rows === 0) throw new Exception("Menu tidak ditemukan.");
            $m = $r->fetch_assoc();
            $price = $m['price'];
            $menuName = $m['name'] ?? '';
        } else {
            $stmt->bind_result($p, $n);
            if ($stmt->fetch()) { $price = $p; $menuName = $n; }
            else throw new Exception("Menu tidak ditemukan (fetch).");
        }
        $stmt->close();

        // normalisasi price
        $price = (float)$price;

        // cek apakah sudah ada di carts untuk user yang sama & menu yang sama
        $stmt = $mysqli->prepare("SELECT id, quantity FROM carts WHERE user_id = ? AND menu_id = ? LIMIT 1");
        if (!$stmt) throw new Exception("Prepare failed (select carts): " . $mysqli->error);
        $stmt->bind_param("ii", $user_id, $menu_id);
        $stmt->execute();

        $existing = null;
        if (method_exists($stmt, 'get_result')) {
            $er = $stmt->get_result();
            if ($er->num_rows > 0) $existing = $er->fetch_assoc();
        } else {
            $stmt->bind_result($cid, $cqty);
            if ($stmt->fetch()) $existing = ['id' => $cid, 'quantity' => $cqty];
        }
        $stmt->close();

        if ($existing) {
            // update quantity
            $newQty = (int)$existing['quantity'] + $quantity;
            $cartId = (int)$existing['id'];

            $stmt = $mysqli->prepare("UPDATE carts SET quantity = ?, price = ?, updated_at = NOW() WHERE id = ?");
            if (!$stmt) throw new Exception("Prepare failed (update carts): " . $mysqli->error);
            $stmt->bind_param("idi", $newQty, $price, $cartId);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash'] = "Jumlah {$menuName} diperbarui di keranjang.";
            $_SESSION['flash_type'] = 'success';
        } else {
            // insert baru
            $stmt = $mysqli->prepare("INSERT INTO carts (user_id, menu_id, quantity, price, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            if (!$stmt) throw new Exception("Prepare failed (insert carts): " . $mysqli->error);
            $stmt->bind_param("iiid", $user_id, $menu_id, $quantity, $price);
            $stmt->execute();
            $stmt->close();

            $_SESSION['flash'] = "{$menuName} berhasil ditambahkan ke keranjang.";
            $_SESSION['flash_type'] = 'success';
        }

        $mysqli->commit();
    } catch (Exception $e) {
        // rollback and log
        $mysqli->rollback();
        error_log("menu.php add-to-cart error: " . $e->getMessage());
        $_SESSION['flash'] = "ERROR: " . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }

    // redirect untuk menghindari re-post pada reload
    header("Location: " . ($_SERVER['REQUEST_URI'] ?? '/menu.php'));
    exit;
}
// ==============================
// END POST HANDLER
// ==============================

// pastikan charset benar
if ($mysqli) {
    mysqli_set_charset($mysqli, 'utf8mb4');
}

// Ambil kategori aktif
$categories = [];
$catSql = "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY id";
if ($res = mysqli_query($mysqli, $catSql)) {
    while ($r = mysqli_fetch_assoc($res)) {
        $cid = (int)$r['id'];
        $categories["cat_{$cid}"] = [
            'id' => $cid,
            'name' => $r['name'],
        ];
    }
    mysqli_free_result($res);
} else {
    error_log("Failed to fetch categories: " . mysqli_error($mysqli));
}

/// Ambil menu items
$data = [];
$sql = "
SELECT 
    m.id,
    m.name,
    m.description,
    m.price,
    m.image,
    m.category_id,
    c.name AS category_name
FROM menus m
JOIN categories c ON c.id = m.category_id
WHERE m.is_available = 1
ORDER BY c.id, m.name
";
// ...
if ($res = mysqli_query($mysqli, $sql)) {
    while ($row = mysqli_fetch_assoc($res)) {
        $cid = (int)$row['category_id'];
        $key = "cat_{$cid}";

        // Path diasumsikan dari SUBFOLDER (naik satu level dulu: ../)
        $imageName = $row['image'] ?? '';
        $imagePath = empty($imageName) ? '' : '../public/images/menus/' . $imageName;

        if (empty($imagePath) || !file_exists(__DIR__ . '/' . $imagePath)) {
            $row['image'] = 'img/americano.jpg'; 
        } else {
            $row['image'] = $imagePath; 
        }
        // ...

        $row['category_name'] = $row['category_name'] ?? '';
        // ... (lanjutan kode)

        if (!isset($data[$key])) $data[$key] = [];
        $data[$key][] = $row;
    }
    mysqli_free_result($res);
} else {
    error_log("Failed to fetch menus: " . mysqli_error($mysqli));
}

// Pastikan setiap kategori ada di data
foreach ($categories as $key => $meta) {
    if (!isset($data[$key])) $data[$key] = [];
}

// Encode untuk JS
$data_json = json_encode($data, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kopi Senja – Menu</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico" />

    <!-- Bootstrap & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="css/style.css?v=1.0" />
    <!-- Modal CSS dipindah ke partials/*.php -->
</head>
<body>
<?php if (file_exists(__DIR__ . '/partials/navbar.php')) include __DIR__ . '/partials/navbar.php'; ?>

<header class="hero">
    <h1>MENU</h1>
</header>

<?php
// Ambil flash untuk ditampilkan di modal (jika ada)
$flash_msg = $_SESSION['flash'] ?? '';
$flash_type = $_SESSION['flash_type'] ?? '';
// unset agar tidak tampil lagi setelah reload (modal akan digunakan)
unset($_SESSION['flash'], $_SESSION['flash_type']);

// include modal partials dari folder partials/ (sesuai strukturmu)
if (file_exists(__DIR__ . '/partials/flash_modal.php')) {
    include __DIR__ . '/partials/flash_modal.php';
}
if (file_exists(__DIR__ . '/partials/qty_modal.php')) {
    include __DIR__ . '/partials/qty_modal.php';
}
?>

<div class="section-intro" style="max-width:1100px;margin:18px auto 0;padding:0 16px;display:flex;justify-content:space-between;align-items:center;">
    <p style="margin:0;color:var(--muted)">Buka setiap hari bagi tamu hotel dan umum.<br />Senin–Minggu: 10.30 – 22.00</p>
    <strong style="font-size:1rem">Ala Carte Menu</strong>
</div>

<div class="page-wrap">
    <aside class="sidebar" aria-label="Kategori Menu">
        <h4>Kategori</h4>
        <ul class="category-list" id="categoryList">
        <?php
        if (!empty($categories)) {
            $first = true;
            foreach ($categories as $key => $meta) {
                $active = $first ? 'active' : '';
                $first = false;
                $nameEsc = htmlspecialchars($meta['name'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
                echo "<li><a href=\"#{$key}\" data-target=\"{$key}\" class=\"{$active}\">{$nameEsc}</a></li>";
            }
        } else {
            echo '<li><em>Tidak ada kategori</em></li>';
        }
        ?>
        </ul>
    </aside>

    <main class="content">
    <?php
    if (!empty($categories)) {
        foreach ($categories as $key => $meta) {
            $catNameEsc = htmlspecialchars($meta['name'], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
            echo <<<HTML
            <div class="menu-section" id="{$key}-section">
                <div class="section-head">
                    <h2>{$catNameEsc}</h2>
                    <p></p>
                </div>
                <div class="menu-grid" id="{$key}"></div>
            </div>
HTML;
        }
    } else {
        echo '<p style="padding:20px">Belum ada kategori. Silakan buat kategori di database.</p>';
    }
    ?>
    </main>
</div>

<?php if (file_exists(__DIR__ . '/partials/footer.php')) include __DIR__ . '/partials/footer.php'; ?>

<script>
const data = <?php echo $data_json ?: '{}'; ?>;

function formatPrice(value) {
    const num = typeof value === 'number' ? value : parseFloat(value || 0);
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);
}

// State untuk quantity modal
let currentQty = 1;
let currentPrice = 0;

function updateTotalPrice() {
    const total = currentPrice * currentQty;
    const tp = document.getElementById('totalPriceDisplay');
    if (tp) tp.textContent = formatPrice(total);
}

function renderMenu(section, items) {
    const container = document.getElementById(section);
    if (!container) return;
    container.innerHTML = "";
    items.forEach((it) => {
        const div = document.createElement("div");
        div.className = "menu-item";
        const imageSrc = it.image || 'img/americano.jpg';
        const priceLabel = formatPrice(it.price);
        const desc = it.description ? it.description : '';

        div.innerHTML = `
  <div class="media-wrap">
    <span class="price-badge">${priceLabel}</span>
    <img class="media" src="${imageSrc}" alt="${(it.name || '')}" loading="lazy">
  </div>
  <div class="card-body">
    <h3>${it.name}</h3>
    <p class="desc">${desc}</p>
    <div class="meta">
      <button type="button" class="btn-order btn btn-sm btn-primary"
              data-id="${it.id || ''}"
              data-name="${it.name || ''}"
              data-price="${it.price || 0}"
              data-image="${imageSrc}">Pesan</button>
    </div>
  </div>
`;
        container.appendChild(div);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    // render menus
    Object.keys(data).forEach(catKey => {
        const grid = document.getElementById(catKey);
        if (grid && Array.isArray(data[catKey])) renderMenu(catKey, data[catKey]);
    });

    // guarded flash modal show
    <?php if (!empty($flash_msg)): ?>
      (function(){
        const flashEl = document.getElementById('flashModal');
        if (flashEl) {
          try { new bootstrap.Modal(flashEl).show(); }
          catch(err){ console.error('Flash modal init error', err); }
        } else {
          console.warn('flashModal not found — check include path for partials/flash_modal.php');
        }
      })();
    <?php endif; ?>

    // delegated click handler for Pesan (guarded)
    document.addEventListener('click', function (e) {
        const btn = e.target && e.target.closest ? e.target.closest('.btn-order') : null;
        if (!btn) return;

        const menuId = btn.getAttribute('data-id');
        const menuName = btn.getAttribute('data-name');
        const menuPrice = parseFloat(btn.getAttribute('data-price') || 0);
        const menuImage = btn.getAttribute('data-image');

        // query modal elements
        const qtyModalEl = document.getElementById('qtyModal');
        const qtyModalImage = document.getElementById('qtyModalImage');
        const qtyModalName = document.getElementById('qtyModalName');
        const qtyModalPrice = document.getElementById('qtyModalPrice');
        const qtyDisplayEl = document.getElementById('qtyDisplay');
        const qtyModalMenuId = document.getElementById('qtyModalMenuId');
        const qtyModalQuantity = document.getElementById('qtyModalQuantity');
        const totalPriceDisplay = document.getElementById('totalPriceDisplay');

        if (!qtyModalEl || !qtyModalImage || !qtyModalName || !qtyModalPrice
            || !qtyDisplayEl || !qtyModalMenuId || !qtyModalQuantity || !totalPriceDisplay) {
            console.warn('Qty modal elements missing — check include and IDs in partials/qty_modal.php');
            return;
        }

        // reset state
        currentQty = 1;
        currentPrice = menuPrice || 0;

        // populate modal
        qtyModalImage.src = menuImage || 'img/americano.jpg';
        qtyModalName.textContent = menuName || '';
        qtyModalPrice.textContent = formatPrice(menuPrice) + ' / item';
        qtyDisplayEl.textContent = currentQty;
        qtyModalMenuId.value = menuId || '';
        qtyModalQuantity.value = currentQty;
        updateTotalPrice();

        // show modal safely
        try {
            new bootstrap.Modal(qtyModalEl).show();
        } catch (err) {
            console.error('Could not show qty modal:', err);
        }
    });

    // guarded plus/minus handlers
    const plusBtn = document.getElementById('qtyPlusBtn');
    if (plusBtn) {
        plusBtn.addEventListener('click', function () {
            currentQty++;
            const qDisplay = document.getElementById('qtyDisplay');
            const qQuantity = document.getElementById('qtyModalQuantity');
            if (qDisplay) qDisplay.textContent = currentQty;
            if (qQuantity) qQuantity.value = currentQty;
            updateTotalPrice();
            const minus = document.getElementById('qtyMinusBtn');
            if (minus) minus.disabled = false;
        });
    }

    const minusBtn = document.getElementById('qtyMinusBtn');
    if (minusBtn) {
        minusBtn.addEventListener('click', function () {
            if (currentQty > 1) {
                currentQty--;
                const qDisplay = document.getElementById('qtyDisplay');
                const qQuantity = document.getElementById('qtyModalQuantity');
                if (qDisplay) qDisplay.textContent = currentQty;
                if (qQuantity) qQuantity.value = currentQty;
                updateTotalPrice();
                if (currentQty === 1) this.disabled = true;
            }
        });
    }

    // Sidebar scroll & click behavior (unchanged)
    const sidebar = document.querySelector('.sidebar');
    const originalTop = sidebar ? getComputedStyle(sidebar).top : '92px';
    const desiredOffset = 100;

    function smoothScrollWithSidebar(el, offset) {
        const sectionTop = el.closest('.menu-section').offsetTop;
        const finalY = sectionTop - offset;
        if (sidebar) sidebar.style.top = offset + 'px';
        window.scrollTo({ top: finalY, behavior: 'smooth' });
        let settled = false;
        function onScroll() {
            if (Math.abs(window.pageYOffset - finalY) <= 2) {
                settled = true;
                window.removeEventListener('scroll', onScroll);
                setTimeout(() => { if (sidebar) sidebar.style.top = originalTop; }, 80);
            }
        }
        window.addEventListener('scroll', onScroll);
        setTimeout(() => {
            if (!settled) {
                window.removeEventListener('scroll', onScroll);
                if (sidebar) sidebar.style.top = originalTop;
            }
        }, 1400);
    }

    function smoothScrollToContent(offset) {
        const contentEl = document.querySelector('.content');
        const finalY = contentEl.offsetTop - offset;
        if (sidebar) sidebar.style.top = offset + 'px';
        window.scrollTo({ top: finalY, behavior: 'smooth' });
        let settled = false;
        function onScroll() {
            if (Math.abs(window.pageYOffset - finalY) <= 2) {
                settled = true;
                window.removeEventListener('scroll', onScroll);
                setTimeout(() => { if (sidebar) sidebar.style.top = originalTop; }, 80);
            }
        }
        window.addEventListener('scroll', onScroll);
        setTimeout(() => {
            if (!settled) {
                window.removeEventListener('scroll', onScroll);
                if (sidebar) sidebar.style.top = originalTop;
            }
        }, 1400);
    }

    document.querySelectorAll('.category-list a[data-target]').forEach(a=>{
        a.addEventListener('click', (e)=>{
            e.preventDefault();
            const target = a.getAttribute('data-target');
            document.querySelectorAll('.category-list a').forEach(x=>x.classList.remove('active'));
            a.classList.add('active');
            if (target === 'all') {
                smoothScrollToContent(desiredOffset);
            } else {
                const el = document.getElementById(target);
                if (el) smoothScrollWithSidebar(el, desiredOffset);
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
