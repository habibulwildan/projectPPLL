<?php
// partials/modals/flash_modal.php
// Mengandalkan $flash_msg dan $flash_type yang sudah didefinisikan di menu.php
// $flash_type diharapkan 'success' atau lainnya (error)
?>
<style>
/* --- Card + subtle entrance animation --- */
@keyframes flashIn {
  from { opacity: 0; transform: translateY(8px) scale(0.99); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes flashOut {
  from { opacity: 1; transform: translateY(0) scale(1); }
  to   { opacity: 0; transform: translateY(6px) scale(0.99); }
}

.modal-flash .modal-dialog { max-width: 420px; }
.modal-flash .modal-content {
  border-radius: 14px;
  overflow: hidden;
  border: 0;
  box-shadow: 0 10px 30px rgba(16,24,40,0.12);
  animation: flashIn 220ms cubic-bezier(.2,.9,.3,1);
}

/* Header */
.flash-header {
  display:flex;
  align-items:center;
  justify-content:center;
  padding:20px 18px;
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
}

/* Big Icon */
.flash-icon {
  width:88px;
  height:88px;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:34px;
  box-shadow:0 6px 18px rgba(16,24,40,0.08);
}
.flash-icon.success {
  background: linear-gradient(180deg,#e6fbef,#ffffff);
  color:#057a3a;
  border:1px solid rgba(5,122,58,0.06);
}
.flash-icon.danger {
  background: linear-gradient(180deg,#fff4f4,#ffffff);
  color:#b91c1c;
  border:1px solid rgba(185,28,28,0.06);
}

/* Body */
.modal-flash .modal-body {
  padding: 12px 22px 8px 22px;
}
.modal-flash .modal-title {
  margin:0;
  font-weight:700;
  font-size:1.05rem;
  text-align:center;
}
.modal-flash .msg {
  margin-top:8px;
  color:#475569;
  font-size:0.95rem;
  text-align:center;
  line-height:1.35;
}

/* Footer + buttons */
.modal-flash .modal-footer {
  display:flex;
  justify-content:center;
  gap:10px;
  border-top:1px solid rgba(16,24,40,0.03);
  padding:14px 18px 18px;
}
.modal-flash .btn {
  padding:8px 16px;
  font-weight:600;
  border-radius:999px;
}
.modal-flash .btn-primary {
  background: linear-gradient(135deg,#f6ad55,#ed8936);
  color:white;
  border:none;
  box-shadow:0 6px 18px rgba(237,139,13,0.12);
}
.modal-flash .btn-outline {
  background:white;
  border:1px solid rgba(16,24,40,0.06);
  color:#374151;
}

/* Mobile tweak */
@media (max-width:460px){
  .flash-icon { width:72px; height:72px; font-size:28px; }
  .modal-flash .modal-dialog { margin: 0 12px; }
}
</style>

<div class="modal fade modal-flash" id="flashModal" tabindex="-1" aria-hidden="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="flash-header">
        <?php if (!empty($flash_type) && $flash_type === 'success'): ?>
          <div class="flash-icon success" aria-hidden="true"><i class="fa fa-check" aria-hidden="true"></i></div>
        <?php else: ?>
          <div class="flash-icon danger" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i></div>
        <?php endif; ?>
      </div>

      <div class="modal-body">
        <h5 class="modal-title">
          <?= (!empty($flash_type) && $flash_type === 'success') ? 'Berhasil' : 'Terjadi Kesalahan' ?>
        </h5>
        <p class="msg"><?= htmlspecialchars($flash_msg ?? '', ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') ?></p>
      </div>

      <div class="modal-footer">
        <?php if (!empty($flash_type) && $flash_type === 'success'): ?>
          <button type="button" class="btn btn-outline" data-bs-dismiss="modal" id="flashCloseBtn">Tutup</button>
        <?php else: ?>
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="flashCloseBtn">Tutup</button>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  var flashModalEl = document.getElementById('flashModal');
  if (!flashModalEl) return;

  // If server provided a message, show the modal via bootstrap so backdrop is handled properly.
  <?php if (!empty($flash_msg)): ?>
    try {
      var flashModal = new bootstrap.Modal(flashModalEl, { backdrop: true, keyboard: true });
      flashModal.show();

      // focus primary button if exists
      setTimeout(function() {
        var primary = document.getElementById('flashPrimaryBtn');
        var closeBtn = document.getElementById('flashCloseBtn');
        if (primary) primary.focus();
        else if (closeBtn) closeBtn.focus();
      }, 200);
    } catch (err) {
      console.error('Failed to init flash modal:', err);
    }
  <?php endif; ?>

  // CLEANUP: ensure backdrop/class removed if something goes wrong or bootstrap didn't clean properly
  flashModalEl.addEventListener('hidden.bs.modal', function () {
    try {
      // remove leftover backdrops
      document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });

      // restore body scroll
      document.body.classList.remove('modal-open');

      // clear padding-right added by bootstrap
      if (document.body.style && document.body.style.paddingRight) {
        document.body.style.paddingRight = '';
      }
    } catch (e) {
      console.error('Flash modal cleanup error:', e);
    }
  });

  // Safety: if someone clicks an element with data-bs-dismiss inside the modal and bootstrap fails
  flashModalEl.addEventListener('click', function(e){
    var target = e.target.closest('[data-bs-dismiss]');
    if (!target) return;
    // attempt to hide through bootstrap API if available
    try {
      if (typeof flashModal !== 'undefined' && flashModal && typeof flashModal.hide === 'function') {
        flashModal.hide();
        return;
      }
    } catch (e) {
      // fallback manual cleanup
    }
    // fallback manual cleanup
    document.querySelectorAll('.modal-backdrop').forEach(function(b){ b.remove(); });
    document.body.classList.remove('modal-open');
    if (document.body.style) document.body.style.paddingRight = '';
  });
});
</script>
