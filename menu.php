<?php
// menu.php
require_once __DIR__ . '/config.php';

// pastikan charset benar
if (isset($conn) && $conn) {
    mysqli_set_charset($conn, 'utf8mb4');
}

// Ambil kategori aktif
$categories = [];
$catSql = "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY id";
if ($res = mysqli_query($conn, $catSql)) {
    while ($r = mysqli_fetch_assoc($res)) {
        $cid = (int)$r['id'];
        $categories["cat_{$cid}"] = [
            'id' => $cid,
            'name' => $r['name'],
        ];
    }
    mysqli_free_result($res);
} else {
    error_log("Failed to fetch categories: " . mysqli_error($conn));
}

// Ambil menu items
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
if ($res = mysqli_query($conn, $sql)) {
    while ($row = mysqli_fetch_assoc($res)) {
        $cid = (int)$row['category_id'];
        $key = "cat_{$cid}";

        // fallback image jika kosong atau file tidak ada
        $imagePath = $row['image'] ?? '';
        if (empty($imagePath) || !file_exists(__DIR__ . '/' . $imagePath)) {
            $row['image'] = 'img/americano.jpg'; // pastikan ada file ini
        }

        $row['category_name'] = $row['category_name'] ?? '';

        if (!isset($data[$key])) $data[$key] = [];
        $data[$key][] = $row;
    }
    mysqli_free_result($res);
} else {
    error_log("Failed to fetch menus: " . mysqli_error($conn));
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
</head>
<body>
<?php if (file_exists(__DIR__ . '/partials/navbar.php')) include __DIR__ . '/partials/navbar.php'; ?>

<header class="hero">
    <h1>MENU</h1>
</header>

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
      <button class="btn-order" 
              data-id="${it.id || ''}"
              data-name="${it.name || ''}" 
              data-price="${it.price || 0}" 
              data-image="${imageSrc}"
      >Pesan</button>
    </div>
  </div>
`;
        container.appendChild(div);
    });
}

document.addEventListener("DOMContentLoaded", () => {
    Object.keys(data).forEach(catKey => {
        const grid = document.getElementById(catKey);
        if (grid && Array.isArray(data[catKey])) renderMenu(catKey, data[catKey]);
    });

    // Sidebar scroll & click behavior
    const sidebar = document.querySelector('.sidebar');
    const originalTop = sidebar ? getComputedStyle(sidebar).top : '92px';
    const desiredOffset = 100;
    
    function smoothScrollWithSidebar(el, offset) {
        const sectionTop = el.closest('.menu-section').offsetTop;
        const finalY = sectionTop - offset;
        sidebar.style.top = offset + 'px';
        window.scrollTo({ top: finalY, behavior: 'smooth' });
        let settled = false;
        function onScroll() {
            if (Math.abs(window.pageYOffset - finalY) <= 2) {
                settled = true;
                window.removeEventListener('scroll', onScroll);
                setTimeout(() => { sidebar.style.top = originalTop; }, 80);
            }
        }
        window.addEventListener('scroll', onScroll);
        setTimeout(() => {
            if (!settled) {
                window.removeEventListener('scroll', onScroll);
                sidebar.style.top = originalTop;
            }
        }, 1400);
    }

    function smoothScrollToContent(offset) {
        const contentEl = document.querySelector('.content');
        const finalY = contentEl.offsetTop - offset;
        sidebar.style.top = offset + 'px';
        window.scrollTo({ top: finalY, behavior: 'smooth' });
        let settled = false;
        function onScroll() {
            if (Math.abs(window.pageYOffset - finalY) <= 2) {
                settled = true;
                window.removeEventListener('scroll', onScroll);
                setTimeout(() => { sidebar.style.top = originalTop; }, 80);
            }
        }
        window.addEventListener('scroll', onScroll);
        setTimeout(() => {
            if (!settled) {
                window.removeEventListener('scroll', onScroll);
                sidebar.style.top = originalTop;
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
// ====== CART HELPERS (simpan di localStorage) ======
function getCart() {
  try {
    return JSON.parse(localStorage.getItem('kopiSenjaCart')) || { items: [] };
  } catch (e) {
    return { items: [] };
  }
}
function saveCart(cart) {
  localStorage.setItem('kopiSenjaCart', JSON.stringify(cart));
  // trigger event agar navbar (atau komponen lain) bisa update
  window.dispatchEvent(new Event('cart:updated'));
}
function addToCart(item) {
  const cart = getCart();
  // cari item berdasarkan id (jika id ada), kalau tidak, cocokkan nama
  const keyMatch = item.id ? 'id' : 'name';
  let found = cart.items.find(i => String(i[keyMatch]) === String(item[keyMatch]));
  if (found) {
    found.qty = (found.qty || 1) + (item.qty || 1);
  } else {
    const toAdd = {
      id: item.id || '',
      name: item.name || '',
      price: Number(item.price || 0),
      image: item.image || '',
      qty: item.qty || 1
    };
    cart.items.push(toAdd);
  }
  saveCart(cart);
}

// update badge lokal (opsional - navbar juga mendengarkan event)
function updateCartBadgeLocal() {
  const cart = getCart();
  const total = (cart.items || []).reduce((s, it) => s + (it.qty || 0), 0);
  const badge = document.getElementById('cartCountBadge');
  if (badge) badge.textContent = total > 0 ? total : '0';
}

// Hook ke tombol Pesan (delegation)
document.addEventListener('click', function(e){
  const btn = e.target.closest && e.target.closest('.btn-order');
  if (!btn) return;
  // Ambil data dari atribut
  const id = btn.getAttribute('data-id') || '';
  const name = btn.getAttribute('data-name') || btn.dataset.name || 'Item';
  const price = parseFloat(btn.getAttribute('data-price') || btn.dataset.price || 0) || 0;
  const image = btn.getAttribute('data-image') || btn.dataset.image || '';

  // buat objek item
  const item = { id, name, price, image, qty: 1 };

  // tambahkan ke cart
  addToCart(item);
  updateCartBadgeLocal();

  // visual feedback sederhana (bisa ganti ke toast)
  btn.textContent = '✓ Ditambahkan';
  btn.disabled = true;
  setTimeout(() => {
    btn.textContent = 'Pesan';
    btn.disabled = false;
  }, 800);
});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
