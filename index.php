<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kopi Senja - Home</title>
    <link rel="icon" type="image/x-icon" href="./img/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css?v=1.0" />
    <style>
      .hero-section-index {
        height: 100vh;
        background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), url("./img/cafe.jpg");
        background-size: 103%;
        background-position: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-shadow: 0px 4px 6px rgba(0, 0, 0, 0.3);
        position: relative;
      }

      .hero-section-index:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 40%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), transparent);
        z-index: 1;
      }

      .hero-section-index:after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 15%;
        background: linear-gradient(to top, rgba(255, 255, 255, 0.5), transparent);
        z-index: 1;
      }

      .hero-content-index {
        position: relative;
        max-width: 80%;
      }

      .hero-title-index {
        font-size: 1.7rem;
        font-weight: bold;
        margin-bottom: 1rem;
        color: white;
        margin-left: -400px;
      }

      .hero-subtitle-index {
        font-style: italic;
        font-weight: 500;
        font-family: "Georgia", serif;
        font-size: 2rem;
        margin-top: 4rem;
        margin-bottom: 0;
        margin-left: -580px;
        color: white;
      }

      .hero-year-index {
        font-size: 1.6rem;
        font-weight: bold;
        margin-top: 1rem;
        color: white;
        margin-left: -400px;
      }

      /* Jika kamu punya custom card-style, diganti ke .card-index agar tidak override bootstrap */
      .card-index {
        border: none;
        border-radius: 10px;
      }

      .card-index img {
        width: 90%;
        border-radius: 15px;
        margin-bottom: 25px;
        margin-left: 25px;
        display: block;
        object-fit: cover;
      }

      .badge-index {
        font-size: 1.2rem;
        font-weight: bold;
        color: #007bff;
        background-color: rgba(128, 128, 128, 0.3);
        padding: 20px 35px;
        margin-bottom: 30px;
        margin-left: 25px;
        border-radius: 25px;
      }

      .card-title-index {
        font-size: 1.8rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 15px;
        margin-left: 25px;
      }

      .card-text-index {
        font-size: 1rem;
        font-weight: 300;
        line-height: 1.6;
        color: #555;
        margin-left: 20px;
        margin-right: 25px;
        text-align: justify;
      }

      .badge_contactus-index {
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

      .card_contactus-index {
        font-size: 1.5rem;
        font-weight: 600;
        color: #343a40;
        margin-top: 30px;
        margin-bottom: 15px;
        margin-left: 30px;
      }

      .cardtext_contactus-index {
        font-weight: 400;
        line-height: 1.6;
        color: #343a40;
        margin-top: 30px;
        margin-left: 30px;
        margin-right: 25px;
        text-align: justify;
      }

      .cardtext_contactus-index strong {
        font-weight: 600;
      }

      /* Jangan ubah kelas bootstrap "container" — ini adalah kelas bawaan bootstrap.
         Jika kamu punya container custom, gunakan nama lain; di sini saya hanya tambahkan
         container_contactus-index untuk custom yang sebelumnya ada. */
      .container_contactus-index {
        max-width: 85%;
        margin-left: 100px;
        margin-bottom: 60px;
      }

      .badge-penjelasan-index {
        font-size: 1.2rem;
        font-weight: bold;
        color: #007bff;
        background-color: rgba(128, 128, 128, 0.3);
        padding: 20px 35px;
        margin: 40px 425px;
        border-radius: 25px;
      }

      .title-tujuan-index {
        font-size: 1.8rem;
        font-weight: 600;
        color: #343a40;
        margin-bottom: 25px;
        margin-left: 31%;
      }

      .homepage-2-img-index {
        margin-top: 25px;
        width: 350px;
        height: 550px;
        object-fit: cover;
        border-radius: 15px;
      }

      .homepage-3-img-index {
        margin-top: 25px;
        width: 350px;
        height: 350px;
        object-fit: cover;
        border-radius: 15px;
      }

      .homepage-4-img-index {
        margin-top: 25px;
        width: 350px;
        height: 350px;
        object-fit: cover;
        border-radius: 15px;
        margin-right: 40px;
      }

      .img-homepage-5-index .homepage-5-img-index {
        width: 500px;
        height: 450px;
        border-radius: 15px;
        margin-left: 35px;
        margin-top: 25px;
        margin-bottom: 30px;
      }

      .contact-card-index {
        border-radius: 12px;
        background-color: #ffffff;
      }

      .contact-us-index {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 14px;
        margin-bottom: 15px;
      }

      .authentic-menu-section-index {
        background-color: #f8f9fa;
        padding: 60px 0;
        font-family: "Georgia", serif;
        color: black;
      }

      .container-authentic-index {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 50px;
        padding: 0 20px;
      }

      .text-authentic-index {
        flex: 1;
        max-width: 500px;
      }

      .text-authentic-index h2 {
        font-size: 28px;
        font-weight: 550;
        margin-bottom: 50px;
      }

      .text-authentic-index p {
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 50px;
      }

      .btn-authentic-index {
        font-family: "Georgia", serif;
        font-weight: 300;
        font-size: 12px;
        color: black; /* Gray text */
        background: none; /* No background */
        border: 1px solid black; /* Visible gray border line */
        cursor: pointer;
        padding: 10px 25px;
        transition: background-color 0.3s, color 0.3s, border-color 0.3s;
      }

      .btn-authentic-index:hover {
        background-color: #7a7a7a; /* Gray background on hover */
        color: white; /* White text on hover */
        border-color: #7a7a7a; /* Border stays same color */
      }

      .image-authentic-index {
        flex: 1;
        max-width: 800px;
      }

      .image-authentic-index img {
        width: 105%;
        object-fit: cover;
      }

      /* === RESPONSIVE FIXES === */
      @media (max-width: 1200px) {
        .hero-title-index,
        .hero-subtitle-index,
        .hero-year-index {
          margin-left: 0;
          text-align: center;
        }

        .container-authentic-index {
          flex-direction: column;
          text-align: center;
        }

        .image-authentic-index img {
          width: 100%;
        }

        .text-authentic-index {
          max-width: 100%;
        }
      }
      @media (max-width: 992px) {
        .hero-section-index {
          background-size: cover;
          padding: 0 20px;
        }

        .hero-subtitle-index {
          font-size: 1.6rem;
          margin-left: 0;
          text-align: center;
        }

        .hero-title-index {
          font-size: 1.5rem;
          margin-left: 0;
          text-align: center;
        }

        .authentic-menu-section-index {
          padding: 40px 20px;
        }

        .container-authentic-index {
          flex-direction: column;
          gap: 20px;
        }

        .text-authentic-index h2 {
          font-size: 24px;
          margin-bottom: 20px;
        }

        .text-authentic-index p {
          font-size: 13px;
          margin-bottom: 20px;
        }

        .btn-authentic-index {
          font-size: 13px;
          padding: 8px 18px;
        }
      }

      @media (max-width: 768px) {
        .hero-subtitle-index {
          font-size: 1.4rem;
          margin-top: 2rem;
        }

        .hero-section-index {
          background-position: center;
          background-size: cover;
          height: 80vh;
        }

        .text-authentic-index h2 {
          font-size: 20px;
        }
      }

      @media (max-width: 576px) {
        .hero-subtitle-index {
          font-size: 1.2rem;
          margin-left: 0;
          text-align: center;
        }

        .hero-section-index {
          height: 70vh;
          padding: 0 10px;
        }

        .text-authentic-index p {
          font-size: 12px;
        }

        .btn-authentic-index {
          font-size: 12px;
          padding: 7px 15px;
        }
      }
    </style>
  </head>

  <body>
    <!-- NAVBAR: hanya brand/logo, tanpa tombol collapse ataupun konten/link -->
    <?php if (file_exists(__DIR__ . '/partials/navbar.php')) include __DIR__ . '/partials/navbar.php'; ?>

    <section class="hero-section-index text-white">
      <div class="hero-content-index">
        <p class="hero-subtitle-index">"Nikmati secangkir kehangatan di setiap senja"</p>
      </div>
    </section>

    <section class="authentic-menu-section-index">
      <div class="container-authentic-index">
        <div class="text-authentic-index">
          <h2>Kelezatan Menu Otentik Kopi Senja</h2>
          <p>
            Manjakan lidah dengan suasana hangat dan nyaman di Kopi Senja. Nikmati setiap cangkir kopi pilihan dan sajian khas yang menghadirkan kenikmatan otentik dalam setiap tegukan. Rasakan pengalaman berbeda
            dengan cita rasa kopi terbaik dan layanan ramah kami.
          </p>
          <p>Sampai jumpa di Kopi Senja untuk menikmati kehangatan dalam setiap senja!</p>
          <a href="menu.html" class="btn-authentic-index">LEBIH LANJUT</a>
        </div>
        <div class="image-authentic-index">
          <img src="./img/cafe.jpg" alt="Suasana Kopi Senja" />
        </div>
      </div>
    </section>
    <?php if (file_exists(__DIR__ . '/partials/footer.php')) include __DIR__ . '/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
