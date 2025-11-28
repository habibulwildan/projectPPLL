<?php
// partials/navbar.php
// Pastikan file ini bisa di-include dari halaman lain.
// Jangan letakkan markup modal ganda — modal ada di partials/cart-modal.php
?>
<header class="header">
  <div class="container-navbar">
    <nav class="navbar navbar-expand-lg navbar-dark">
      <a class="navbar-brand d-flex align-items-center" href="/KopiSenja/index.php">
        <img src="/KopiSenja/img/logo_kopisenja.jpg" alt="Logo Kopi Senja" class="header-logo me-2" />
        <h5 class="mb-0">Kopi Senja</h5>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
              aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarMenu">
        <ul class="nav ms-lg-auto flex-column flex-lg-row text-lg-end align-items-center">
          <li class="nav-item">
            <a class="nav-link text-white" href="/KopiSenja/index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/KopiSenja/menu.php">Menu</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/KopiSenja/about.php">Tentang Kami</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white" href="/KopiSenja/kontak.php">Kontak</a>
          </li>

          <!-- RIGHT SIDE ICONS (profile + cart) -->
          <li class="nav-item nav-icons d-flex align-items-center gap-3">
            <!-- Cart (tombol buka modal) -->
            <a id="cartToggle" class="nav-link position-relative" href="#" role="button" aria-label="Keranjang"
               title="Keranjang" data-bs-toggle="modal" data-bs-target="#cartModal">
              <i data-lucide="shopping-cart" class="icon-svg"></i>

              <!-- Badge jumlah item (diperbarui via JS) -->
              <span id="cartCountBadge"
                    style="position:absolute; top:-2px; right:-6px; background:#dc3545; color:white; border-radius:50%; padding:2px 6px; font-size:10px; min-width:18px; text-align:center; display:inline-block;"
                    aria-live="polite">0</span>
            </a>

            <!-- Profile -->
            <a class="nav-link" href="/KopiSenja/login.php" id="profileLink" aria-label="Profile" title="Profil">
              <i data-lucide="user" class="icon-svg"></i>
            </a>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</header>

<!-- Lucide icons (ikon) -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>lucide.createIcons();</script>

<?php
// Sertakan partial modal keranjang sekali (modal + scriptnya ada dalam file ini).
// Pastikan file partials/cart-modal.php ada dan hanya berisi 1 modal dengan id="cartModal".
if (file_exists(__DIR__ . '/cart-modal.php')) {
    include __DIR__ . '/cart-modal.php';
}
?>

<!-- Small badge sync script (supaya badge update saat halaman load) -->
<script>
(function(){
  // minimal helper (jika partial cart-modal.php juga meng-handle event cart:updated, ini hanya safety)
  function getCart() {
    try {
      return JSON.parse(localStorage.getItem('kopiSenjaCart')) || { items: [] };
    } catch (e) {
      return { items: [] };
    }
  }
  function totalQty(cart) {
    return (cart.items || []).reduce((s, it) => s + (it.qty || 0), 0);
  }
  function updateBadge() {
    const badge = document.getElementById('cartCountBadge');
    if (!badge) return;
    const cart = getCart();
    const total = totalQty(cart);
    badge.textContent = total > 0 ? total : '0';
  }

  document.addEventListener('DOMContentLoaded', updateBadge);
  // dengarkan event global yang dikirim saat cart berubah
  window.addEventListener('cart:updated', updateBadge);
})();
</script>
