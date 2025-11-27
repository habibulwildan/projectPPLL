<?php
include("templates/section_head.php"); ?>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Kopi Senja POS</a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    Kasir: **Nama Kasir** (Shift Pagi) </span>
                <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="modal" data-bs-target="#customerModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check-fill" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/><path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/></svg>
                    Pilih Pembeli
                </button>
                <button class="btn btn-kopi" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/></svg>
                    Logout
                </button>
            </div>
        </div>
    </nav>

    <main class="container-fluid pos-container">
        <div class="row h-100">

            <div class="col-md-8 p-3 product-list">
                <h3 class="mb-3">Daftar Produk</h3>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                    <div class="col">
                        <div class="card card-product shadow-sm">
                            <img src="/img/espresso.jpg" class="card-img-top" alt="Espresso">
                            <div class="card-body p-2">
                                <h5 class="card-title mb-0">Espresso</h5>
                                <p class="card-text text-muted mb-1">Kopi Hitam</p>
                                <p class="h6 text-end text-success">Rp 15.000</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-product shadow-sm">
                            <img src="/img/latte.jpg" class="card-img-top" alt="Caffe Latte">
                            <div class="card-body p-2">
                                <h5 class="card-title mb-0">Caffe Latte</h5>
                                <p class="card-text text-muted mb-1">Susu & Kopi</p>
                                <p class="h6 text-end text-success">Rp 25.000</p>
                            </div>
                        </div>
                    </div>
                    </div>
            </div>

            <div class="col-md-4 p-0 order-summary d-flex flex-column">
                <div class="p-3 flex-grow-1" style="overflow-y: auto;">
                    <h3 class="mb-3">Detail Pesanan</h3>
                    <div id="order-items" class="list-group mb-3">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">2x Espresso</h6>
                                <small class="text-muted">@ Rp 15.000</small>
                            </div>
                            <span class="fw-bold">Rp 30.000</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">1x Caffe Latte</h6>
                                <small class="text-muted">@ Rp 25.000</small>
                            </div>
                            <span class="fw-bold">Rp 25.000</span>
                        </div>
                        <div class="alert alert-info mt-3 p-2" role="alert">
                             Pembeli Saat Ini: **Pelanggan Umum**
                        </div>
                    </div>
                </div>

                <div class="total-section shadow-lg">
                    <div class="d-flex justify-content-between fw-bold fs-5 mb-2">
                        <span>TOTAL:</span>
                        <span id="grand-total" class="text-danger">Rp 55.000</span>
                    </div>

                    <button class="btn btn-lg btn-kopi w-100 mt-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-wallet2" viewBox="0 0 16 16"><path d="M12.136 7.604c.321.084.55.37.55.706 0 .416-.316.748-.725.748H4.25a.774.774 0 0 1-.725-.748c0-.336.229-.622.55-.706a1.18 1.18 0 0 1 1.25-.975c.348 0 .66.215.776.541.118.326.046.68-.184.92z"/><path d="M12 4H.9A.9.9 0 0 0 0 4.9v4.2A.9.9 0 0 0 .9 10H12a.9.9 0 0 0 .9-.9V4.9A.9.9 0 0 0 12 4zm0 5.1-.9.9H.9V4.9h11.2V9.1z"/><path d="M15 4a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h12z"/></svg>
                        PROSES PEMBAYARAN
                    </button>
                </div>
            </div>
        </div>
    </main>

    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Pilih / Cari Pembeli</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" placeholder="Cari nama atau nomor telepon pembeli">
                    <button class="btn btn-outline-success">Gunakan Pelanggan Baru/Umum</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Metode Pembayaran (Total: Rp 55.000)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <button type="button" class="list-group-item list-group-item-action">Tunai</button>
                        <button type="button" class="list-group-item list-group-item-action">Debit/Kredit</button>
                        <button type="button" class="list-group-item list-group-item-action">QRIS (E-Wallet)</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-kopi">Bayar & Cetak Struk</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/kasir/templates/bootstrap.bundle.min.js"></script>

<?php
include("templates/section_foot.php");
