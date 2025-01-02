<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link  rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css">
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - ABOUT</title>
  <style>
    .box{
      border-top-color: var(--teal) !important;
    }
  </style>
</head>
<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <div class="my-5 px-4">
    <h2 class="fw-bold h-font text-center">ABOUT US</h2>
  </div>

  <div class="container">
    <div class="row justify-content-between align-items-center">
      <div class="col-lg-6 col-md-5 mb-4 order-lg-1 order-md-1 order-2">
        <h3 class="mb-3">Welcome to Elbriza De Silang</h3>
        <p>
        At Elbriza, we believe in creating unforgettable experiences that combine luxury with the tranquility of nature.
        Our private resort and event space is designed to be your home away from home—a perfect sanctuary for relaxation, 
        staycations, and memorable gatherings. 
        Nestled in the picturesque landscape of Silang, Cavite,
        El Briza de Silang invites you to unwind amidst lush greenery and serene surroundings.
        We're thrilled to announce that we’ll be opening our doors soon! Get ready to embark on 
        a journey of relaxation and rejuvenation that you won't soon forget.
        </p>
      </div>
      <div class="col-lg-5 col-md-5 mb-4 order-lg-2 order-md-2 order-1">
        <img src="images/about/elbriza.jpg" class="w-100">
      </div>
    </div>
  </div>

 


  <?php require('inc/footer.php'); ?>

</body>
</html>