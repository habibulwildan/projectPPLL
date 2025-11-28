<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kopi Senja - Kontak dan Lokasi</title>
        <link rel="icon" type="image/x-icon" href="./img/favicon.ico" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-papbJs7X9H0EltiqoZb4b+wnRZk+3HHLji0FslRGlP5e4l+77jEjczy4s8u+09CnVcA4VbEBWbw126C5d3u1Vg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="css/style.css">
        <style>

    .hero-section {
        height: 100vh;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), 
                        url('./img/about.jpg');
        background-size: cover;
        background-position: center;
        display: flex;
        justify-content: center;
        align-items: center;
        text-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .hero-section:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 40%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), transparent);
        z-index: 1;
    }

    .hero-section:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 15%;
        background: linear-gradient(to top, rgba(255, 255, 255, 0.5), transparent);
        z-index: 1;
    }

    .hero-content {
        position: relative;
        max-width: 80%;
    }

    .hero-title {
        font-size: 1.7rem;
        font-weight: bold;
        margin-bottom: 1rem;
        color: white;
        margin-left: -400px;
    }

    .hero-subtitle {
        font-style: italic;
        font-weight: 500;
        font-size: 3.5rem;
        margin: 0;
        text-align: center;
        color: white;
    }

    .hero-year {
        font-size: 1.6rem;
        font-weight: bold;
        margin-top: 1rem;
        color: white;
        margin-left: -400px;
    }

    /* --- BAGIAN CONTACT & INFO --- */
    .card {
        border: none;
        border-radius: 10px;
    }

    .card img {
        width: 90%;
        border-radius: 15px;
        margin-bottom : 25px;
        margin-left : 25px;
        display: block;
        object-fit: cover;
    }

    .badge {
        font-size: 1.2rem;
        font-weight: bold;
        color: #007bff;
        background-color: rgba(128, 128, 128, 0.3);
        padding: 20px 35px;
        margin-bottom: 30px;
        margin-left: 25px;
        border-radius: 25px;
    }

    .card-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 15px;
        margin-left: 25px;
    }

    .card-text {
        font-size: 1.0rem;
        font-weight: 300;
        line-height: 1.6;
        color: #555;
        margin-left: 20px;
        margin-right: 25px;
        text-align:justify;
    }

    .badge_contactus {
        font-size: 1.2rem;
        font-weight: bold;
        color: #007bff;
        background-color: rgba(128, 128, 128, 0.3);
        padding: 20px 35px;
        margin-top: 20px;
        margin-left: 30px;
        margin-right: 405px;
        border-radius: 25px;
    }

    .card_contactus {
        font-size: 1.5rem;
        font-weight: 600;
        color: #343a40;
        margin-top: 30px;
        margin-bottom: 15px;
        margin-left: 30px;
    }

    .cardtext_contactus {
        font-weight: 400;
        line-height: 1.6;
        color: #343a40;
        margin-top: 30px;
        margin-left: 30px;
        margin-right: 25px;
        text-align: justify;
    }

    .container {
        max-width: 87%;
    }

    .container_contactus {
        max-width: 85%;
        margin-left: 100px;
        margin-bottom: 60px;
    }

    .badge-penjelasan {
        font-size: 1.2rem;
        font-weight: bold;
        color: #007bff;
        background-color: rgba(128, 128, 128, 0.3);
        padding: 20px 35px;
        margin: 40px 425px;
        border-radius: 25px;
    }

    .title-tujuan {
        font-size: 1.8rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 25px;
        margin-left: 31%;
    }

    .homepage-2-img,
    .homepage-3-img,
    .homepage-4-img {
        margin-top: 25px;
        width: 350px;
        border-radius: 15px;
        object-fit: cover;
    }

    .homepage-2-img { height: 550px; }
    .homepage-3-img { height: 350px; }
    .homepage-4-img { height: 350px; margin-right: 40px; }

    .img-homepage-5 .homepage-5-img {
        width: 500px;
        height: 450px;
        border-radius: 15px;
        margin-left: 35px;
        margin-top: 25px;
        margin-bottom: 30px;
    }

    .authentic-menu-section {
        background-color: #f8f9fa;
        padding: 60px 0;
        color: black;
        text-align: center;
    }

    .container-authentic {
        max-width: 900px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .text-authentic {
        max-width: 700px;
    }

    .text-authentic h2 {
        font-size: 28px;
        font-weight: 550;
        margin-bottom: 50px;
    }

    .text-authentic p {
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 50px;
    }

    .btn-authentic {
        font-weight: 300;
        font-size: 12px;
        color: black;
        background: none;
        border: 1px solid black;
        cursor: pointer;
        padding: 10px 25px;
        transition: 0.3s;
    }

    .btn-authentic:hover {
        background-color: #7a7a7a;
        color: white;
        border-color: #7a7a7a;
    }

    .sn-info-2 {
        display: flex;
        justify-content: space-around;
        align-items: flex-start;
        gap: 30px;
        padding: 0 15px;
        max-width: 900px;
        margin: 0 auto;
    }

    .sn-info-2 h2 {
        font-size: 24px;
        font-weight: 600;
        margin-top: 20px;
        margin-bottom: 10px;
        color: #343a40;
    }

    .sn-info-2 p {
        font-size: 1rem;
        line-height: 1.5;
        margin-bottom: 15px;
        color: #555;
    }

    .pd-16px { padding: 16px; }

    .map-container {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        max-width: 900px;
        margin: 0 auto 60px auto;
        width: 100%;
    }

    .map-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 10px;
    }

    @media (max-width: 992px) {
        .hero-subtitle { font-size: 2.2rem; }
        .hero-title, .hero-year { margin-left: 0; text-align: center; }

        .sn-info-2 {
            flex-direction: column;
            text-align: center;
            align-items: center;
        }

        .map-container { padding-bottom: 75%; }
    }

    @media (max-width: 576px) {
        .hero-subtitle { font-size: 1.8rem; }
        .text-authentic h2 { font-size: 22px; }
        .map-container { padding-bottom: 100%; }
    }

</style>

    </head>
    <body>
        <?php if (file_exists(__DIR__ . '/partials/navbar.php')) include __DIR__ . '/partials/navbar.php'; ?>
        <section class="hero-section text-white">
            <div class="hero-content">
                <p class="hero-subtitle">Kontak dan Lokasi</p>
            </div>
        </section>

        <section class="authentic-menu-section">
            <div class="container-authentic">
                <div class="text-authentic">
                    <h2>Temukan kami</h2>
                    <p>Kopi Senja merupakan kafe dengan
        gaya yang ramah untuk masyarakat Surabaya dan sekitarnya. Nikmati kopi sambil menikmati suasana
        sore hari di tempat yang terbuka nan teduh.</p>
            </div>
        </section>

        <section class="authentic-menu-section">
            <div class="sn-info-2"> <div class="pd-16px"> <h2>Lokasi Kopi Senja</h2>
                    <p>
                        Pakuwon Indah (Jl. Raya Lontar No.2, Babatan, Surabaya, East
                      Java), Jawa Timur 69115
                    </p>
                    <h2>Kontak Kopi Senja</h2>
                    <p>
                        Telepon/WA: <a href="tel:+62-000-11222-1">+62 000 11222 1</a>
                    </p>
                    <p>
                        Surel (E-mail): <a href="mailto:kopisenja.thesky@bangkalan.svr">kopisenja.thesky@bangkalan.svr</a>
                    </p>
                </div>
                <div class="pd-16px"> <h2>Jam operasional</h2>
                    <p>
                        Senin sampai Kamis:
                        08.00-22.00
                    </p>
                    <p>
                        Jum'at dan Sabtu:
                        08.00-20.00
                    </p>
                    <p>
                        Minggu: 09.00-20.00
                    </p>
                    <p>Hari libur tetap buka</p>
                </div>
            </div>
        </section>

        <section class="authentic-menu-section">
            <div class="map-container"> <iframe src="https://maps.google.com/maps?q=The+Sky,+Jl.+HOS.+Cokroaminoto+No.66,+Demangan+Barat,+Pangeranan,+Kec.+Bangkalan,+Kabupaten+Bangkalan,+Jawa+Timur+69115&t=&z=15&ie=UTF8&iwloc=&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>     
        <?php if (file_exists(__DIR__ . '/partials/footer.php')) include __DIR__ . '/partials/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
