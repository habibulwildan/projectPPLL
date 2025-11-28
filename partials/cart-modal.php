<?php
// partials/cart-modal.php
// Cart modal dengan qty pill (tidak tumpuk), action trash, total simple.
// Uses localStorage key "kopiSenjaCart"
?>
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header border-0">
        <h5 class="modal-title">My Shopping Cart</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Desktop header row -->
        <div class="d-none d-md-flex px-3 py-2 bg-light border rounded fw-semibold" style="gap:1rem;">
          <div class="flex-grow-1" style="min-width:260px;">Product</div>
          <div style="width:180px; text-align:center;">Quantity</div>
          <div style="width:120px; text-align:right;">Price</div>
          <div style="width:60px; text-align:center;">Action</div>
        </div>

        <!-- Items list -->
        <div id="cartList" class="mt-3"></div>

        <!-- TOTAL row -->
        <div class="mt-4 d-flex justify-content-between align-items-center" style="font-size:1.15rem; font-weight:700;">
          <div>Total</div>
          <div id="cartTotalText">Rp0</div>
        </div>

      </div>

      <div class="modal-footer border-0 d-flex justify-content-between">
        <a href="/KopiSenja/menu.php" class="btn btn-dark px-4">Back to Shop</a>

        <div>
          <button id="clearCartBtn" class="btn btn-outline-secondary me-2">Clear Cart</button>
          <button id="checkoutBtn" class="btn btn-primary px-4">Checkout</button>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
/* =========================
   Product Row Structure
========================= */
.cart-item-row {
  border-bottom: 1px solid #e9ecef;
  padding: 12px;
  display: flex;
  gap: 1rem;
  align-items: center;
}

.cart-item-media {
  width: 84px;
  flex: 0 0 84px;
}

.cart-item-media img {
  width: 84px;
  height: 64px;
  object-fit: cover;
  border-radius: 8px;
}

.cart-item-info {
  flex-grow: 1;
  min-width: 180px;
}

.cart-item-price {
  width:120px;
  text-align:right;
  font-weight:600;
}

.cart-item-action {
  width:60px;
  text-align:center;
}

.btn-trash {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #d9534f;
  font-size: 20px;
  border-radius: 6px;
}
.btn-trash:hover { background: rgba(217,83,79,0.08); }

/* =========================
      Qty Pill Controls
   (fixed: no stacking/wrapping)
========================= */

.qty-control {
  display: inline-flex !important;
  flex-direction: row !important;
  align-items: center;
  justify-content: center;
  gap: 0;
  border: 1px solid #e6e7eb;
  border-radius: 999px;
  overflow: hidden;
  background: #fff;
  height: 44px;
  width: 180px;
  box-sizing: border-box;
  white-space: nowrap;
}

.qty-control > .qty-btn,
.qty-control > .qty-input {
  box-sizing: border-box;
  border: none;
  margin: 0;
  padding: 0;
  background: transparent;
  outline: none;
  height: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
}

/* minus / plus buttons */
.qty-control > .qty-btn {
  width: 56px;
  min-width: 56px;
  max-width: 56px;
  font-size: 20px;
  color: #111827;
  cursor: pointer;
  user-select: none;
}

/* center number */
.qty-control > .qty-input {
  width: 68px;
  min-width: 68px;
  max-width: 68px;
  text-align: center;
  font-size: 17px;
  color: #111827;
  -moz-appearance: textfield;
  background: transparent;
}

/* subtle separators (vertical lines) */
.qty-control > * + * {
  box-shadow: inset 1px 0 0 0 #eef2f6;
}

/* hover states */
.qty-control > .qty-btn:hover {
  background: rgba(17,24,39,0.03);
}

/* mobile tweaks */
@media (max-width: 480px) {
  .qty-control { height: 40px; width: 160px; }
  .qty-control > .qty-btn { width: 48px; min-width:48px; max-width:48px; font-size:18px; }
  .qty-control > .qty-input { width: 64px; min-width:64px; max-width:64px; font-size:15px; }
}

/* mobile layout */
@media (max-width:767px){
  .d-none.d-md-flex { display:none !important; }
  .cart-item-row { flex-direction:column; align-items:flex-start; }
  .cart-item-price { text-align:left; width:auto; }
  .cart-item-action { display:none; } /* desktop-only trash */
}
</style>

<script>
(function(){

  const KEY = "kopiSenjaCart";

  const getCart = () => {
    try { return JSON.parse(localStorage.getItem(KEY)) || { items: [] }; }
    catch { return { items: [] }; }
  };

  const saveCart = cart => {
    localStorage.setItem(KEY, JSON.stringify(cart));
    window.dispatchEvent(new Event("cart:updated"));
  };

  const formatPrice = v =>
    new Intl.NumberFormat("id-ID", { style:"currency", currency:"IDR" })
      .format(Number(v||0));

  const computeTotal = cart =>
    cart.items.reduce((s,i)=> s + (Number(i.price||0) * Number(i.qty||0)), 0);


  function updateTotalUI(){
    const cart = getCart();
    document.getElementById("cartTotalText").textContent = formatPrice(computeTotal(cart));
    updateBadge();
  }

  function updateBadge(){
    const badge = document.getElementById("cartCountBadge");
    if (!badge) return;
    const cart = getCart();
    const t = (cart.items||[]).reduce((s,i)=> s + Number(i.qty||0), 0);
    badge.textContent = t > 0 ? t : '0';
  }

  function renderCart(){
    const cart = getCart();
    const container = document.getElementById("cartList");
    container.innerHTML = "";

    if (!cart.items || cart.items.length === 0){
      container.innerHTML = `<div class="text-center py-4 text-muted">Your cart is empty</div>`;
      updateTotalUI();
      return;
    }

    cart.items.forEach((item, idx)=>{

      const row = document.createElement("div");
      row.className = "cart-item-row";

      row.innerHTML = `
        <div class="cart-item-media">
          <img src="${escapeHtml(item.image || 'img/americano.jpg')}" alt="${escapeHtml(item.name||'')}">
        </div>

        <div class="cart-item-info">
          <div class="fw-semibold">${escapeHtml(item.name || '')}</div>
          <div class="text-muted small">${escapeHtml(item.category || '')}</div>
        </div>

        <div class="qty-control">
          <div class="qty-btn btn-dec" role="button" aria-label="Decrease">−</div>
          <input class="qty-input" value="${escapeHtml(item.qty || 1)}" />
          <div class="qty-btn btn-inc" role="button" aria-label="Increase">+</div>
        </div>

        <div class="cart-item-price">${formatPrice(item.price)}</div>

        <!-- Desktop trash -->
        <div class="cart-item-action">
          <button class="btn-trash" data-index="${idx}" aria-label="Hapus item">🗑</button>
        </div>
      `;

      container.appendChild(row);

      // hooks
      const dec = row.querySelector(".btn-dec");
      const inc = row.querySelector(".btn-inc");
      const qin = row.querySelector(".qty-input");
      const trash = row.querySelector(".btn-trash");

      dec && (dec.onclick = () => changeQty(idx, -1));
      inc && (inc.onclick = () => changeQty(idx, +1));

      if (qin) {
        qin.onchange = e => {
          const v = parseInt(e.target.value) || 1;
          setQty(idx, v);
        };
        qin.onkeypress = e => {
          if (!/[0-9]/.test(e.key || String.fromCharCode(e.which || e.keyCode))) e.preventDefault();
        };
      }

      trash && (trash.onclick = () => removeItem(idx));

      // mobile remove button
      if (window.matchMedia("(max-width:767px)").matches){
        const mob = document.createElement("button");
        mob.className = "btn btn-sm btn-outline-danger mt-2";
        mob.textContent = "hapus";
        row.querySelector('.cart-item-info').appendChild(mob);
        mob.onclick = () => removeItem(idx);
      }

    });

    updateTotalUI();
  }


  function changeQty(i, delta){
    const cart = getCart();
    if (!cart.items[i]) return;
    cart.items[i].qty = Math.max(1, Number(cart.items[i].qty || 1) + delta);
    saveCart(cart);
    renderCart();
  }

  function setQty(i, qty){
    const cart = getCart();
    if (!cart.items[i]) return;
    cart.items[i].qty = Math.max(1, Number(qty || 1));
    saveCart(cart);
    renderCart();
  }

  function removeItem(i){
    const cart = getCart();
    if (!cart.items[i]) return;
    cart.items.splice(i, 1);
    saveCart(cart);
    renderCart();
  }

  function clearCart(){
    if (!confirm("Clear cart?")) return;
    saveCart({ items: [] });
    renderCart();
  }

  function escapeHtml(s){
    if (s === undefined || s === null) return '';
    return String(s)
      .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
      .replaceAll('"','&quot;').replaceAll("'",'&#039;');
  }

  document.addEventListener("DOMContentLoaded", ()=>{
    document.getElementById("cartModal")?.addEventListener("show.bs.modal", renderCart);
    document.getElementById("clearCartBtn")?.addEventListener("click", clearCart);
    document.getElementById("checkoutBtn")?.addEventListener("click", ()=> location.href="/KopiSenja/cart.php");
    window.addEventListener("cart:updated", ()=>{ updateBadge(); renderCart(); });
    updateBadge();
  });

})();
</script>
