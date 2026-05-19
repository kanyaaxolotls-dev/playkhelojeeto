<?php
  $data  = $this->db_model->select_multi('*', 'tbl_settings', array('id' => 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?= $data->name ?></title>
  <meta content="" name="description">
  <meta content="" name="keywords">
  <link href="<?= base_url('axxests/setting/'.$data->logo); ?>" rel="icon">
  <link href="<?= base_url('axxests/setting/'.$data->logo); ?>" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Jost:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="Web-assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="Web-assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="Web-assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="Web-assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="Web-assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="Web-assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="Web-assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="Web-assets/css/style.css" rel="stylesheet">

  <script>
    $(document).ready(function () {
      setInterval(function () {
        $('#imageSlider').carousel('next');
      }, 4000); 
    });
  </script>
  <style>
    .link{
    	padding: 15px 30px;
    	margin: 10px;
    	display: inline-block;
    	color: #000;
    	background: #ffce54;
        text-decoration: none;
    }
    
    .link:hover{
    	text-decoration: none;
    	color: #000;
    }
    
    .shrink-on-hover {
       display: inline-block;
       vertical-align: middle;
       -webkit-transform: perspective(1px) translateZ(0);
       transform: perspective(1px) translateZ(0);
       box-shadow: 0 0 1px rgba(0, 0, 0, 0);
       -webkit-transition-duration: 0.3s;
       transition-duration: 0.3s;
       -webkit-transition-property: transform;
       transition-property: transform;
    }
    .shrink-on-hover:hover, .shrink-on-hover:focus, .shrink-on-hover:active {
      -webkit-transform: scale(0.8);
      transform: scale(0.8);
    }
    
    .ak-img{
        height: 9em;
    }
</style>
</head>
<body>
  <!-- ======= Header ======= -->
  <header id="header" class="fixed-top ">
    <div class="container d-flex align-items-center">

      <h1 class="logo me-auto"><img src="<?= base_url('axxests/setting/'.$data->logo); ?>" alt=""><a href="" style="margin-left: 15px;"><?= $data->name ?></a></h1>
      <nav id="navbar" class="navbar">
        <ul>
          <li><a class="nav-link scrollto active" href="#hero">Home</a></li>
          <li><a class="nav-link scrollto" href="#about">About</a></li>
          <li><a class="nav-link scrollto" href="#how-to-play">How To play</a></li>
          <li><a class="nav-link   scrollto" href="#how-to-add-cash">How To add cash</a></li>
          <li><a class="nav-link scrollto" href="#how-to-withdraw">how To withdraw</a></li>
        </ul>
        <i class="bi bi-list mobile-nav-toggle"></i>
      </nav>

    </div>
  </header>

<section id="hero" class="d-flex justify-content-center align-items-center">

    <div class="container">
      <div class="row">
        <div class="col-lg-6 d-flex flex-column justify-content-center align-items-center pt-4 pt-lg-0 order-2 order-lg-1"
          data-aos="fade-up" data-aos-delay="200">
          <h1>PLAY <?= $data->name ?></h1>
          <h2>Funtarget is a popular skill-based game traditionally played. <b>"FUNTARGET"</b> is a thrilling and skill-based game that challenges players to aim, time, and strike with precision in a dynamic arena of fast-paced action and fun.
          </h2>
          <div class="d-flex justify-content-center justify-content-lg-start">
            <a href="<?php echo base_url() . '/axxests/game.apk'; ?>" class="link link-two shrink-on-hover btn-get-started scrollto">Download App</a>
          </div>
        </div>
        <div class="col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-in" data-aos-delay="200">
          <img src="Web-assets/img/main game.jpg" class="img-thumbnail animated" alt="">
        </div>
      </div>
    </div>
  </section>

  <id="main">

    <!-- ======= Clients Section ======= -->
    <section id="clients" class="clients section-bg">
      <div class="container">

        <div class="row" data-aos="zoom-in">

          <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center justify-content-center">


            <p style="font-weight: bold;">Safe and Secure</p>
          </div>

          <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center justify-content-center">
            <p style="font-weight: bold;">24x7 Services</p>
          </div>

          <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center justify-content-center">
            <p style="font-weight: bold;">Play on Mobiles</p>
          </div>

          <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center justify-content-center">
            <p style="font-weight: bold;">Win Real Cash</p>

          </div>

          <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center justify-content-center">
            <p style="font-weight: bold;">Eassy To Play</p>
          </div>

          <div class="col-lg-2 col-md-4 col-6 d-flex align-items-center justify-content-center">
            <p style="font-weight: bold;">24x7 Services</p>
          </div>

        </div>

      </div>
    </section>

    <!-- ======= About Us Section ======= -->
    <section id="about" class="about" style="background: rgb(243, 245, 250);">
      <div class="container" data-aos="fade-up">
    
        <div class="section-title">
          <h2 class="section-title text-center">About Us</h2>
        </div>
    
        <div class="row content">
          <div class="col-lg-6">
            <p>
              At Funtarget, we are redefining the thrill of digital gaming with fast-paced challenges that test your focus,
              reflexes, and strategy. Designed by a passionate team of developers and creative minds, Funtarget delivers a
              one-of-a-kind experience where every tap, swipe, and move matters.
            </p>
            <ul>
              <li><i class="ri-check-double-line"></i> We blend skill-based mechanics with fun, competitive gameplay</li>
              <li><i class="ri-check-double-line"></i> Funtarget isn't just a game — it's a test of timing, precision, and quick thinking</li>
              <li><i class="ri-check-double-line"></i> Our mission is to bring players together through engaging challenges that are easy to learn and hard to master</li>
            </ul>
          </div>
          <div class="col-lg-6 pt-4 pt-lg-0">
            <p>
              Whether you're a casual player or a competitive gamer aiming for the leaderboard, Funtarget has something for
              everyone. Dive into a world of addictive levels, unlock new targets, and show off your skills. Every shot
              counts — are you ready to hit your mark?
            </p>
            <a href="#" class="btn-learn-more">Learn More</a>
          </div>
        </div>
    
      </div>
    </section>

    <!-- ======= How to Play Section ======= -->
    <section id="how-to-play">
      <div class="container" data-aos="fade-up">
        <h2 class="section-title text-center">How to Play</h2>
        <div class="row how-to-play-row">
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/main login.jpg" class="ak-img" alt="Step 1" >
              <p>Step 1: Enter the phone number and Enter the Password then click on login.</p>
              <p>Step 2:if you are new user then click on register and fill.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/main home.jpg" class="ak-img" alt="Step 2" >
              <p>Step 2: It show the home screen of game.</p>
              <p>Step 3: There are many option like add cash , withdraw cash , settings,profile. </p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/main home.jpg" class="ak-img" alt="Step 3" >
              <p>Step 4:If you want to play Funtarget.</p>
              <p>Step 5: Click on Funtarget game. It will open Funtarget game screen.</p>

            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/main game.jpg" class="ak-img" alt="Step 4" >
              <p>Step 6: Screen look like this.</p>
              <p>Step 7: There are multiple options like. You can see chips, Points, battle profile. you can play with
                other players to.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======= Payment section info Section ======= -->
    <section id="how-to-add-cash">
      <div class="container" data-aos="fade-up">
        <h2 class="section-title text-center">How to Add Cash</h2>
        <div class="row how-to-play-row">
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/main home.jpg" alt="Step 2" class="img-fluid mb-3">
              <p>Step 1:Click on + sign which is shown on profile right column side click there.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/dragon-addcash-2.png" alt="Step 1" class="img-fluid mb-3">
              <p>Step 2: Deposite screen display select chips for add & click on add chips.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/dragon-add-cash-4.png" alt="Step 2" class="img-fluid mb-3">
              <p>Step 3: It will display the payment screen select method and click on method.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/dragon-addcash-3.png" alt="Step 3" class="img-fluid mb-3">
              <p>Step 4: If you select manuall method. it will show this screen you can fill all details to add cash.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ======= Portfolio Section ======= -->

    <section id="how-to-withdraw">
      <!-- ======= Payment section info Section ======= -->
      <div class="container" data-aos="fade-up">

        <h2 class="section-title text-center">How to Withdraw Cash</h2>
        <div class="row how-to-play-row">
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/main home.jpg" alt="Step 2" class="img-fluid mb-3">
              <p>Step 1:In Home Screen at bottom line you can see the withdraw option click there.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/dragon-withdrawcash-1.png" alt="Step 1" class="img-fluid mb-3">
              <p>Step 2:Enter the phone number and fill further information.It will send OTP to number enter OTP.</p>
            </div>
          </div>
          <div class="col-md-6 col-lg-3 mb-4">
            <div class="how-to-play-item text-center">
              <img src="Web-assets/img/dragon-withdrawcash-2.png" alt="Step 2" class="img-fluid mb-3">
              <p>Step 3: It will display the Withdraw screen. Enter amount and click on the withdraw.</p>
            </div>
          </div>
        </div>
      </div>
      </div>
    </section>


    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer">

      <div class="footer-newsletter">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-6">
              <h4>Join Our Newsletter</h4>
              <form action="" method="post">
                <input type="email" name="email"><input type="submit" value="Subscribe">
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-top">
        <div class="container">
          <div class="row">

            <div class="col-lg-3 col-md-6 footer-contact">
              <h3><?= $data->name ?></h3>
              <p>

                <strong>Phone:</strong> +1 5589 55488 55<br>
                <strong>Email:</strong> DragonTiger@example.com<br>
              </p>
            </div>

            <div class="col-lg-3 col-md-6 footer-links">
              <h4>Useful Links</h4>
              <ul>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Home</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">About us</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">How to add cash</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">How to withdraw cash</a></li>
              </ul>
            </div>

            <div class="col-lg-3 col-md-6 footer-links">
              <h4>Our Social Networks</h4>
              <p>Cras fermentum odio eu feugiat lide par naso tierra videa magna derita valies</p>
              <div class="social-links mt-3">
                <a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
                <a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
                <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
                <a href="#" class="google-plus"><i class="bx bxl-skype"></i></a>
                <a href="#" class="linkedin"><i class="bx bxl-linkedin"></i></a>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="container footer-bottom clearfix">
        <div class="copyright">
          &copy; Copyright <strong><span><?= $data->name ?></span></strong>. All Rights Reserved
        </div>
        <div class="credits">
        </div>
      </div>
    </footer>
    <div id="preloader"></div>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    <script src="Web-assets/vendor/aos/aos.js"></script>
    <script src="Web-assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="Web-assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="Web-assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="Web-assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="Web-assets/vendor/waypoints/noframework.waypoints.js"></script>
    <script src="Web-assets/vendor/php-email-form/validate.js"></script>
    <script src="Web-assets/js/main.js"></script>
</body>
</html>