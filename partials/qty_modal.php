<?php
// partials/qty_modal.php
// Include this file in menu.php BEFORE any JS that references the modal IDs.
// Trigger example in your menu rendering:
// <button class="btn btn-sm btn-primary btn-order"
//         data-id="123"
//         data-name="Americano"
//         data-price="25000"
//         data-image="img/americano.jpg">Pesan</button>
?>
<style>
/* Modal size & look */
#qtyModal .modal-dialog { max-width: 420px; margin: 0 auto; }
#qtyModal .modal-content { border-radius: 14px; border: none; }

/* Body compact */
.qty-modal-body { 
    padding: 1.5rem 1.6rem; 
}

/* Top row: image | (name + price) [left aligned] | qty (right) */
.qty-top {
  display: flex;
  align-items: flex-start; /* keep name & price left */
  gap: 0.75rem;
  width: 100%;
}

/* image */
.qty-img-wrap { flex: 0 0 auto; }
.qty-modal-img {
  width: 68px;
  height: 68px;
  object-fit: cover;
  border-radius: 10px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

/* Name + price stacked and left aligned */
.qty-col-main {
  flex: 1 1 auto;
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start; /* IMPORTANT: left align */
}
.qty-name {
  margin: 0;
  font-weight: 700;
  font-size: 1.05rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.price-small { display: none; } /* we use only price-display */
.price-display-modal {
  margin-top: 4px;
  font-weight: 700;
  color: #16a34a;
  font-size: 1rem;
}

/* Qty on the right */
.qty-col-qty {
  flex: 0 0 auto;
  display: flex;
  justify-content: flex-end;
  align-items: center;
}
.qty-control-modal {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #f8f9fa;
  padding: 0.18rem 0.35rem;
  border-radius: 999px;
  border: 1px solid rgba(0,0,0,0.05);
}
.qty-btn-modal {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #fff;
  border: 1px solid #ddd;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  cursor: pointer;
}
.qty-btn-modal:active { transform: translateY(1px); }
.qty-display-modal {
  min-width: 28px;
  text-align: center;
  font-weight: 700;
}

/* Footer button style */
.modal-footer {
  border: none;
  padding: 1.2rem 1.6rem 1.6rem;
}
#qtyModal .btn-primary {
  background: linear-gradient(135deg,#f6ad55,#ed8936);
  border: none;
  padding: 0.55rem 1.4rem;
  border-radius: 999px;
  font-weight: 600;
}

/* Responsive tweaks */
@media (max-width: 380px) {
  #qtyModal .modal-dialog { max-width: 92%; }
  .qty-modal-img { width: 56px; height: 56px; }
  .qty-name { font-size: 0.98rem; }
}
</style>

<!-- Modal -->
<div class="modal fade" id="qtyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow">

      <div class="modal-body qty-modal-body">
        <div class="qty-top">

          <!-- Image -->
          <div class="qty-img-wrap">
            <img id="qtyModalImage" src="img/americano.jpg" alt="menu image" class="qty-modal-img">
          </div>

          <!-- Name + Price (left aligned) -->
          <div class="qty-col-main">
            <p id="qtyModalName" class="qty-name">Product Name</p>
            <div id="qtyModalPrice" class="price-display-modal">Rp 0</div>
          </div>

          <!-- Qty control (right) -->
          <div class="qty-col-qty">
            <div class="qty-control-modal" role="group" aria-label="Quantity controls">
              <button type="button" id="qtyMinusBtn" class="qty-btn-modal" aria-label="Kurangi">−</button>
              <div id="qtyDisplay" class="qty-display-modal">1</div>
              <button type="button" id="qtyPlusBtn" class="qty-btn-modal" aria-label="Tambah">+</button>
            </div>
          </div>

        </div> <!-- .qty-top -->
      </div> <!-- .modal-body -->

      <div class="modal-footer justify-content-center">
        <form method="post" action="" id="qtyModalForm" class="w-100 d-flex justify-content-center">
          <input type="hidden" name="menu_id" id="qtyModalMenuId" value="">
          <input type="hidden" name="quantity" id="qtyModalQuantity" value="1">
          <button type="submit" class="btn btn-primary">Add to Cart →</button>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
(function(){
  // format IDR (no fractional digits)
  function formatRupiah(n){
    return new Intl.NumberFormat('id-ID', {
      style: 'currency', currency: 'IDR', maximumFractionDigits: 0
    }).format(Number(n) || 0);
  }

  // Elements & state
  var qtyModal = document.getElementById('qtyModal');
  var qtyModalImage = document.getElementById('qtyModalImage');
  var qtyModalName  = document.getElementById('qtyModalName');
  var qtyModalPrice = document.getElementById('qtyModalPrice');
  var qtyDisplay    = document.getElementById('qtyDisplay');
  var qtyMinusBtn   = document.getElementById('qtyMinusBtn');
  var qtyPlusBtn    = document.getElementById('qtyPlusBtn');
  var qtyModalMenuId = document.getElementById('qtyModalMenuId');
  var qtyModalQuantity = document.getElementById('qtyModalQuantity');

  var quantity = 1;

  function updateQtyUI(){
    if (qtyDisplay) qtyDisplay.textContent = quantity;
    if (qtyModalQuantity) qtyModalQuantity.value = quantity;
  }

  // Buttons
  qtyPlusBtn && qtyPlusBtn.addEventListener('click', function(){
    quantity++;
    updateQtyUI();
  });

  qtyMinusBtn && qtyMinusBtn.addEventListener('click', function(){
    if (quantity > 1) {
      quantity--;
      updateQtyUI();
    }
  });

  // delegated: open modal when .btn-order clicked
  document.addEventListener('click', function(e){
    var btn = e.target && e.target.closest ? e.target.closest('.btn-order') : null;
    if (!btn) return;

    e.preventDefault();

    // reset qty
    quantity = 1;
    updateQtyUI();

    // populate fields from data-*
    if (qtyModalMenuId) qtyModalMenuId.value = btn.dataset.id || '';
    if (qtyModalName) qtyModalName.textContent = btn.dataset.name || '';
    if (qtyModalImage && btn.dataset.image) qtyModalImage.src = btn.dataset.image;
    if (qtyModalPrice) qtyModalPrice.textContent = formatRupiah(btn.dataset.price || 0);

    // show via bootstrap so backdrop appears
    if (typeof bootstrap !== 'undefined') {
      new bootstrap.Modal(qtyModal, { backdrop: true }).show();
    } else {
      // fallback — not recommended: won't show backdrop
      qtyModal.classList.add('show');
      qtyModal.style.display = 'block';
      qtyModal.removeAttribute('aria-hidden');
    }
  });
})();
</script>
