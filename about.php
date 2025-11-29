<?php
session_start();
include 'config.php';
?>

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
            /* NOTE: only custom classes were suffixed with -about.
               Bootstrap classes (container, row, navbar, card, etc.) are left unchanged. */

            .hero-section-about {
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

            .hero-section-about:before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 40%;
                background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), transparent);
                z-index: 1;
            }

            .hero-section-about:after {
                content: "";
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 15%;
                background: linear-gradient(to top, rgba(255, 255, 255, 0.5), transparent);
                z-index: 1;
            }

            .hero-content-about {
                position: relative;
                max-width: 80%;
            }

            .hero-title-about {
                font-size: 1.7rem;
                font-weight: bold;
                margin-bottom: 1rem;
                color: white;
                margin-left: -400px;
            }

            .hero-subtitle-about {
                font-style: italic;
                font-weight: bold;
                font-weight: 500;
                font-family: 'Georgia', serif;
                font-size: 3.5rem;
                margin: 0;        
                text-align: center;
                color: white;
            }

            .hero-year-about {
                font-size: 1.6rem;
                font-weight: bold;
                margin-top: 1rem;
                color: white;
                margin-left: -400px;
            }

            /* keep .card (bootstrap) untouched; custom contact card variants renamed */
            .card-about {
                border-radius: 10px;
            }

            .card-about img {
                width: 90%;
                border-radius: 15px;
                margin-bottom : 25px;
                margin-left : 25px;
                display: block;
                object-fit: cover;
            }

            /* leave .badge (bootstrap) as-is to avoid interfering with bootstrap badge */
            .badge-about {
                font-size: 1.2rem;
                font-weight: bold;
                color: #007bff;
                background-color: rgba(128, 128, 128, 0.3);
                padding: 20px 35px;
                margin-bottom: 30px;
                margin-left: 25px;
                border-radius: 25px;
            }

            .card-title-about {
                font-size: 1.8rem;
                font-weight: 600;
                color: #343a40;
                margin-bottom: 15px;
                margin-left: 25px;
            }

            .card-text-about {
                font-size: 1.0rem;
                font-weight: 300;
                line-height: 1.6;
                color: #555;
                margin-left: 20px;
                margin-right: 25px;
                text-align:justify;
            }

            .badge_contactus-about {
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

            .card_contactus-about {
                font-size: 1.5rem;
                font-weight: 600;
                color: #343a40;
                margin-top: 30px;
                margin-bottom: 15px;
                margin-left: 30px;
            }

            .cardtext_contactus-about {
                font-weight: 400;
                line-height: 1.6;
                color: #343a40;
                margin-top: 30px;
                margin-left: 30px;
                margin-right: 25px;
                text-align: justify;
            }

            .cardtext_contactus-about strong {
                font-weight: 600;
            }

            .container {
                max-width: 87%;
            }

            .container_contactus-about {
                max-width: 85%;
                margin-left: 100px;
                margin-bottom: 60px;
            }

            .badge-penjelasan-about {
                font-size: 1.2rem;
                font-weight: bold;
                color: #007bff;
                background-color: rgba(128, 128, 128, 0.3);
                padding: 20px 35px;
                margin: 40px 425px;
                border-radius: 25px;
            }

            .title-tujuan-about {
                font-size: 1.8rem;
                font-weight: 600;
                color: #343a40;
                margin-bottom: 25px;
                margin-left: 31%;
            }

            .homepage-2-img-about {
                margin-top: 25px;
                width: 350px;
                height: 550px;
                object-fit: cover;
                border-radius: 15px;
            }

            .homepage-3-img-about {
                margin-top: 25px;
                width: 350px;
                height: 350px;
                object-fit: cover;
                border-radius: 15px;
            }

            .homepage-4-img-about {
                margin-top: 25px;
                width: 350px;
                height: 350px;
                object-fit: cover;
                border-radius: 15px;
                margin-right: 40px;
            }

            .img-homepage-5-about .homepage-5-img-about {
                width: 500px;
                height: 450px;
                border-radius: 15px;
                margin-left: 35px;
                margin-top: 25px;
                margin-bottom: 30px;
            }

            .contact-card-about {
                border-radius: 12px;
                background-color: #ffffff;
            }

            .contact-us-about {
                display: inline-block;
                background-color: #007bff;
                color: white;
                padding: 6px 12px;
                border-radius: 12px;
                font-size: 14px;
                margin-bottom: 15px;
            }

            /* FOOTER CSS was removed previously; keeping page-specific section classes suffixed */
            .authentic-menu-section-about {
                background-color: #f8f9fa;
                padding: 60px 0;
                font-family: 'Georgia', serif;
                color: black;
                text-align: center; 
            }

            .container-authentic-about {
                max-width: 900px;
                margin: 0 auto; 
                display: flex;
                flex-direction: column;  /* susun ke bawah, bukan sejajar */
                align-items: center;     /* center horizontal */
                justify-content: center; /* center vertical */
            }

            .text-authentic-about {
                max-width: 700px;
            }

            .text-authentic-about h2 {
                font-size: 28px;
                font-weight: 550;
                margin-bottom: 50px;
            }

            .text-authentic-about p {
                font-size: 14px;
                line-height: 1.6;
                margin-bottom: 50px;
            }

            .btn-authentic-about {
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

            .btn-authentic-about:hover {
                background-color: #7a7a7a; 
                color: white;               
                border-color: #7a7a7a;    
            }

            .image-authentic-about {
                flex: 1;
                max-width: 800px;
            }

            .image-authentic-about img {
                width: 105%;
                object-fit: cover;
            }

            /* RESPONSIVE rules updated to match suffixed classes where applicable */
            @media (max-width: 992px) {
                .hero-subtitle-about {
                    font-size: 2.2rem;
                }

                .hero-title-about,
                .hero-year-about {
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

                .badge-penjelasan-about {
                    margin: 20px auto;
                    display: block;
                }

                .title-tujuan-about {
                    margin-left: 0;
                    text-align: center;
                }

                .container_contactus-about {
                    margin-left: 0;
                    padding: 0 15px;
                }
            }

            @media (max-width: 576px) {
                .hero-subtitle-about {
                    font-size: 1.8rem;
                }

                .text-authentic-about h2 {
                    font-size: 22px;
                }

                .text-authentic-about p {
                    font-size: 13px;
                    margin-bottom: 30px;
                }

                .homepage-2-img-about,
                .homepage-3-img-about,
                .homepage-4-img-about,
                .img-homepage-5-about .homepage-5-img-about {
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
        <section class="hero-section-about text-white">
            <div class="hero-content-about">
                <p class="hero-subtitle-about">Tentang Kami</p>
            </div>
        </section>

        <section class="authentic-menu-section-about">
            <div class="container-authentic-about">
                <div class="text-authentic-about">
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
