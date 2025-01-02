<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://unpkg.com/swiper@7/swiper-bundle.min.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://bclma.io/elbriza/css/common.css">




  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - HOME</title>
  <style>

  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

    .availability-form {
      margin-top: -50px;
      z-index: 2;
      position: relative;
    }

    

    @media screen and (max-width: 575px) {
      .availability-form {
        margin-top: 25px;
        padding: 0 35px;
      }
    }

    /* Add styles for the submit button */
    .custom-bg {
      background-color: #2ec1ac; 
    }
    .custom-bg:hover {
      background-color: #279e8c;
    }
  </style>
</head>
<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <!-- Carousel -->

  <div class="container-fluid px-lg-4 mt-4">
    <div class="swiper swiper-container">
      <div class="swiper-wrapper">
        <?php 
          $res = selectAll('carousel');
          while ($row = mysqli_fetch_assoc($res)) {
            $path = CAROUSEL_IMG_PATH;
            echo <<<data
              <div class="swiper-slide">
                <img src="$path$row[image]" class="w-100 d-block">
              </div>
            data;
          }
        ?>
      </div>
    </div>
  </div>

  <!-- check availability form -->

  <div class="container availability-form">
    <div class="row">
      <div class="col-lg-12 bg-white shadow p-4 rounded">
        <h5 class="mb-4">Check Booking Availability</h5>
        <form action="rooms.php">
          <div class="row align-items-end">
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Check-in</label>
              <input type="date" class="form-control shadow-none" name="checkin" required>
            </div>
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Check-out</label>
              <input type="date" class="form-control shadow-none" name="checkout" required>
            </div>
            <div class="col-lg-3 mb-3">
              <label class="form-label" style="font-weight: 500;">Adult</label>
              <select class="form-select shadow-none" name="adult">
                <?php 
                  $guests_q = mysqli_query($con,"SELECT MAX(adult) AS `max_adult`, MAX(children) AS `max_children` 
                    FROM `rooms` WHERE `status`='1' AND `removed`='0'");  
                  $guests_res = mysqli_fetch_assoc($guests_q);
                  
                  for ($i = 1; $i <= $guests_res['max_adult']; $i++) {
                    echo "<option value='$i'>$i</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-lg-2 mb-3">
              <label class="form-label" style="font-weight: 500;">Children</label>
              <select class="form-select shadow-none" name="children">
                <?php 
                  for ($i = 1; $i <= $guests_res['max_children']; $i++) {
                    echo "<option value='$i'>$i</option>";
                  }
                ?>
              </select>
            </div>
            <input type="hidden" name="check_availability">
            <div class="col-lg-1 mb-lg-3 mt-2 text-center">
              <button type="submit" class="btn text-white shadow-none custom-bg" style="z-index: 2; position: relative;">Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Packages and Services -->

  <h2 class="mt-5 pt-4 mb-4 text-center fw-bold h-font">Check out our Deal Packages</h2>
  <div class="container">
    <div class="row text-center">
      <?php 
        $packages = [
          ['name' => 'Birthday Party Package', 'description' => 'A fun-filled celebration.', 'image' => 'images/rooms/birthday.jpg'],
          ['name' => 'Wedding Package', 'description' => 'A romantic getaway for couples.', 'image' => 'images/rooms/wedding.jpg'],
          ['name' => 'Debut Package', 'description' => 'An enchanting experience.', 'image' => 'images/rooms/debut.jpg']
        ];

        foreach ($packages as $package) {
          echo '<div class="col-lg-4 col-md-6 mb-4">';
          echo '<div class="card shadow-sm">';
          echo '<img src="' . $package['image'] . '" class="card-img-top" alt="' . $package['name'] . '">';
          echo '<div class="card-body">';
          echo '<h5 class="card-title">' . $package['name'] . '</h5>';
          echo '<p class="card-text">' . $package['description'] . '</p>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
      ?>
    </div>
    <div class="col-lg-12 text-center mt-5">
      <a href="rooms.php" class="btn btn-sm btn-outline-dark rounded-0 fw-bold shadow-none">More>>></a>
    </div>
  </div>

  <?php require('inc/footer.php'); ?>

  <script src="https://unpkg.com/swiper@7/swiper-bundle.min.js"></script>
  <script>
    var swiper = new Swiper(".swiper-container", {
      spaceBetween: 30,
      effect: "fade",
      loop: true,
      autoplay: { delay: 3500, disableOnInteraction: false }
    });
  </script>
</body>
</html>
