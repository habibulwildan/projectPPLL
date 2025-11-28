<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kopi Senja - Tentang Kami</title>
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
                justify-content: center; /* horizontal center */
                align-items: center;     /* vertical center */
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
                font-weight: bold;
                font-weight: 500;
                font-family: 'Georgia', serif;
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

            .cardtext_contactus strong {
                font-weight: 600;
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

            .homepage-2-img {
                margin-top: 25px;
                width: 350px;
                height: 550px;
                object-fit: cover;
                border-radius: 15px;
            }

            .homepage-3-img {
                margin-top: 25px;
                width: 350px;
                height: 350px;
                object-fit: cover;
                border-radius: 15px;
            }

            .homepage-4-img {
                margin-top: 25px;
                width: 350px;
                height: 350px;
                object-fit: cover;
                border-radius: 15px;
                margin-right: 40px;
            }

            .img-homepage-5 .homepage-5-img {
                width: 500px;
                height: 450px;
                border-radius: 15px;
                margin-left: 35px;
                margin-top: 25px;
                margin-bottom: 30px;
            }

            .contact-card {
                border-radius: 12px;
                background-color: #ffffff;
            }

            .contact-us {
                display: inline-block;
                background-color: #007bff;
                color: white;
                padding: 6px 12px;
                border-radius: 12px;
                font-size: 14px;
                margin-bottom: 15px;
            }


            .footer-custom {
                background: #252220;
                color: #fff;
                padding: 50px 0 40px 0;
                font-family: 'Georgia', serif;
                font-weight: 300; /* font ringan */
                font-size: 0.95rem;
            }

            .container-footer {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 30px;
            }

            .footer-row {
                display: flex;
                flex-wrap: nowrap; /* supaya kolom berjajar */
                justify-content: space-between;
                border-bottom: 1px solid #484848;
                padding-bottom: 24px;
                gap: 60px; /* jarak antar kolom diperlebar */
            }

            .footer-col {
                flex: 1 1 0;
                min-width: 220px;
            }

            .footer-col h4 {
                font-weight: 500;
                font-size: 1.2rem;
                letter-spacing: 1px;
                margin-bottom: 12px;
            }

            .footer-col p,
            .footer-col ul {
                margin: 0;
                color: white;
                font-weight: 300;
                line-height: 1.8;
            }

            .footer-col ul {
                list-style: none;
                padding: 0;
            }

            .footer-col ul li {
                margin-bottom: 1px;
            }

            .footer-col ul li a {
                color: #bbb;
                text-decoration: none;
                transition: color 0.2s;
                font-weight: 300;
            }

            .footer-col ul li a:hover {
                color: #fff;
            }

            .footer-social {
                margin-top: 18px;
            }

            .footer-social a {
                color: #fff;
                margin-right: 16px;
                font-size: 1.4rem;
                text-decoration: none;
                vertical-align: middle;
            }

            .footer-social a:hover {
                color: #e2b873;
            }

            .footer-credit {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-top: 30px;
                font-size: 0.9rem;
                color: #999;
                font-weight: 300;
            }

            /* Jarak antara menu dan jam operasional */
            .footer-col h4 + ul {
                margin-top: 20px;
            }

            /* Jarak bawah jam operasional (kolom kedua) */
            .footer-col:nth-child(2) ul:first-of-type {
                margin-bottom: 20px;
            }

            .footer-col:nth-child(2) {
                margin-left: 150px; /* sesuaikan dengan kebutuhan */
            }

            .footer-col:nth-child(3) {
                margin-left: 340px; /* sesuaikan dengan kebutuhan */
            }

            .footer-col:nth-child(3) ul li a {
                color: white;
            }

            .footer-credit span {
                color: white !important;
            }

            .footer-row {
                border-bottom: 1px solid white !important;
            }

            .footer-custom p a[href^="mailto:"] {
                color: white !important; /* Override to white text color */
            }

            .authentic-menu-section {
                background-color: #f4f1e9;
                padding: 60px 0;
                font-family: 'Georgia', serif;
                color: black;
                text-align: center; 
            }

            .container-authentic {
                max-width: 900px;
                margin: 0 auto; 
                display: flex;
                flex-direction: column;  /* susun ke bawah, bukan sejajar */
                align-items: center;     /* center horizontal */
                justify-content: center; /* center vertical */
            }

            .text-authentic {
            /* flex: 1; */
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
            font-family: 'Georgia', serif;
            font-weight: 300;
            font-size: 12px;
            color: black;       
            background: none;     
            border: 1px solid black;   
            cursor: pointer;
            padding: 10px 25px;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
            }

            .btn-authentic:hover {
            background-color: #7a7a7a; 
            color: white;               
            border-color: #7a7a7a;    
            }

            .image-authentic {
            flex: 1;
            max-width: 800px;
            }

            .image-authentic img {
            width: 105%;
            object-fit: cover;
            }

            /* RESPONSIF */
            @media (max-width: 992px) {
            .hero-subtitle {
                font-size: 2.2rem;
            }

            .hero-title,
            .hero-year {
                margin-left: 0;
                text-align: center;
            }

            .footer-row {
                flex-wrap: wrap; 
                gap: 30px;
            }

            .footer-col {
                min-width: 100%;
            }

            .footer-col:nth-child(2),
            .footer-col:nth-child(3) {
                margin-left: 0;
            }

            .badge-penjelasan {
                margin: 20px auto;
                display: block;
            }

            .title-tujuan {
                margin-left: 0;
                text-align: center;
            }

            .container_contactus {
                margin-left: 0;
                padding: 0 15px;
            }
            }

            @media (max-width: 576px) {
            .hero-subtitle {
                font-size: 1.8rem;
            }

            .text-authentic h2 {
                font-size: 22px;
            }

            .text-authentic p {
                font-size: 13px;
                margin-bottom: 30px;
            }

            .homepage-2-img,
            .homepage-3-img,
            .homepage-4-img,
            .img-homepage-5 .homepage-5-img {
                width: 100%;
                height: auto;
                margin: 15px 0;
            }
            }

            .footer-credit {
            flex-direction: column;
            gap: 10px;
            text-align: center;
            }
    </style>
    </head>
    <body>
        <?php include 'partials/navbar.php'; ?>
        <section class="hero-section text-white">
            <div class="hero-content">
                <p class="hero-subtitle">Tentang Kami</p>
            </div>
        </section>
        <section class="authentic-menu-section">
            <div class="container-authentic">
                <div class="text-authentic">
                    <h2>Selamat Datang di Kopi Senja</h2>
                    <p> Kopi Senja hadir sebagai ruang hangat untuk menikmati secangkir kopi berkualitas dan momen berharga bersama orang-orang tercinta. 
                    Sejak berdiri, kami berkomitmen untuk menghadirkan pengalaman minum kopi terbaik dengan cita rasa otentik dari biji kopi pilihan 
                    nusantara. Suasana tenang dengan sentuhan desain klasik modern menjadikan Kopi Senja tempat yang tepat untuk bersantai, berdiskusi, 
                    ataupun sekadar melepas penat setelah beraktivitas seharian.</p>
                    <p>Dengan dukungan barista berpengalaman, kami tidak hanya menyajikan kopi, tetapi juga cerita dalam setiap seduhannya. 
            Bagi kami, kopi bukan hanya minuman, melainkan seni yang mampu menyatukan berbagai perbedaan dalam satu meja. 
            Mari hadir dan rasakan pengalaman berbeda hanya di Kopi Senja.</p>
            </div>
        </section>
        <?php include 'partials/footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
