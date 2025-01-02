<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - CONFIRM BOOKING</title>
</head>
<body class="bg-light">

  <?php require('inc/header.php'); ?>

  <?php 
    if (!isset($_GET['id']) || $settings_r['shutdown'] == true) {
        redirect('rooms.php');
    } elseif (!(isset($_SESSION['login']) && $_SESSION['login'] == true)) {
        redirect('rooms.php');
    }

    $data = filteration($_GET);

    $room_res = select("SELECT * FROM `rooms` WHERE `id`=? AND `status`=? AND `removed`=?", [$data['id'], 1, 0], 'iii');

    if (mysqli_num_rows($room_res) == 0) {
        redirect('rooms.php');
    }

    $room_data = mysqli_fetch_assoc($room_res);

    $_SESSION['room'] = [
        "id" => $room_data['id'],
        "name" => $room_data['name'],
        "price" => $room_data['price'],
        "payment" => null,
        "available" => false,
    ];

    $user_res = select("SELECT * FROM `user_cred` WHERE `id`=? LIMIT 1", [$_SESSION['uId']], "i");
    $user_data = mysqli_fetch_assoc($user_res);


  ?>

  <div class="container">
    <div class="row">
      <div class="col-12 my-5 mb-4 px-4">
        <h2 class="fw-bold">CONFIRM BOOKING</h2>
        <div style="font-size: 14px;">
          <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
          <span class="text-secondary"> > </span>
          <a href="rooms.php" class="text-secondary text-decoration-none">ROOMS</a>
          <span class="text-secondary"> > </span>
          <a href="#" class="text-secondary text-decoration-none">CONFIRM</a>
        </div>
      </div>

      <div class="col-lg-7 col-md-12 px-4">
        <div class="card p-3 shadow-sm rounded">
        <?php
        // Default fallback image
        $room_image = ROOMS_IMG_PATH . "default_thumbnail.jpg";

        // Fetch the thumbnail image for the room
        $img_q = mysqli_query($con, "SELECT * FROM `room_images` WHERE `room_id` = '$room_data[id]' AND `thumb` = 1 LIMIT 1");

        if (mysqli_num_rows($img_q) > 0) {
            $img_res = mysqli_fetch_assoc($img_q);
            $room_image = ROOMS_IMG_PATH . $img_res['image']; // Use the thumbnail image
        }
        ?>
        <img src="<?php echo $room_image; ?>" class="img-fluid rounded mb-3">
        <h5><?php echo $room_data['name']; ?></h5>
        <h6>₱<?php echo $room_data['price']; ?> per night</h6>
        </div>
      </div>

      <div class="col-lg-5 col-md-12 px-4">
        <div class="card mb-4 border-0 shadow-sm rounded-3">
          <div class="card-body">
          <form action="book_room.php" method="POST">
  <h6 class="mb-3">BOOKING DETAILS</h6>
  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label">Name</label>
      <input name="name" type="text" value="<?php echo $user_data['name']; ?>" class="form-control shadow-none" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Phone Number</label>
      <input name="phonenum" type="number" value="<?php echo $user_data['phonenum']; ?>" class="form-control shadow-none" required>
    </div>
    <div class="col-md-12 mb-3">
      <label class="form-label">Address</label>
      <textarea name="address" class="form-control shadow-none" rows="1" required><?php echo $user_data['address']; ?></textarea>
    </div>
    <div class="col-md-12 mb-3">
  <label class="form-label">Reference</label>
  <textarea name="reference" class="form-control shadow-none" rows="1" required></textarea>
</div>
  <div class="col-md-12 mb-3">
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#referenceImageModal">
    View Gcash qr
  </button>
</div>
    <div class="col-md-6 mb-3">
      <label class="form-label">Check-in</label>
      <input id="checkin" name="checkin" type="date" class="form-control shadow-none" required>
    </div>
    <div class="col-md-6 mb-4">
      <label class="form-label">Check-out</label>
      <input id="checkout" name="checkout" type="date" class="form-control shadow-none" required>
    </div>
    <div class="col-md-6 mb-4">
      <label class="form-label">Days</label>
      <input id="days" name="days" type="number" class="form-control shadow-none" readonly>
    </div>
    <div class="col-md-6 mb-4">
      <label class="form-label">Total Price</label>
      <input id="total_price" name="total_price" type="number" class="form-control shadow-none" readonly>
    </div>
    <div class="col-12">
      <button name="book_now" class="btn w-100 text-white custom-bg shadow-none mb-1">Book Now</button>
      <script>
         function showThankYouMessage() 
        alert("Thank you for your booking!"); // Displays a thank-you alert box
        // Optionally, you can also dynamically show a thank-you message on the page.
        const messageDiv = document.createElement('div');
        messageDiv.innerHTML = `
            <p class="text-center text-success mt-3">Thank you for your booking!</p>
        `;
        document.body.appendChild(messageDiv); // Appends the message to the bottom of the page
    
</script>
    </div>
   
  </div>

</form>
 <!-- gcash Modal -->
 <div class="modal fade" id="referenceImageModal" tabindex="-1" aria-labelledby="referenceImageLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="referenceImageLabel">Reference Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="gcash.jpg" alt="Reference Image" class="img-fluid rounded">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
  document.getElementById('checkout').addEventListener('change', validateDates);
document.getElementById('checkin').addEventListener('change', validateDates);

function validateDates() {
  const checkin = new Date(document.getElementById('checkin').value);
  const checkout = new Date(document.getElementById('checkout').value);

  if (checkin && checkout) {
    if (checkout <= checkin) {
      alert("Check-out date must be later than the check-in date.");
      document.getElementById('checkout').value = ""; // Clear invalid check-out date
      document.getElementById('days').value = 0;
      document.getElementById('total_price').value = 0;
      return;
    }

    // If dates are valid, calculate price
    calculatePrice();
  }
}

function calculatePrice() {
  const checkin = new Date(document.getElementById('checkin').value);
  const checkout = new Date(document.getElementById('checkout').value);
  const oneDay = 24 * 60 * 60 * 1000;

  if (checkin && checkout && checkout > checkin) {
    const days = Math.round(Math.abs((checkout - checkin) / oneDay));
    const pricePerNight = <?php echo $room_data['price']; ?>;
    const totalPrice = days * pricePerNight;

    document.getElementById('days').value = days;
    document.getElementById('total_price').value = totalPrice;
  } else {
    document.getElementById('days').value = 0;
    document.getElementById('total_price').value = 0;
  }
}

</script>
  <?php require('inc/footer.php'); ?>
</body>
</html>
