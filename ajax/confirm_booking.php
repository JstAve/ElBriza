<?php 

require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');

date_default_timezone_set("Asia/Manila");

// Set the Content-Type header for JSON response
header('Content-Type: application/json');

if (isset($_POST['check_availability'])) {
    $frm_data = filteration($_POST);
    $status = "";
    $result = "";

    // Initialize response
    $response = ["status" => "error", "message" => "Unknown error occurred."];

    // Check session data
    session_start();
    if (!isset($_SESSION['room'])) {
        echo json_encode(["status" => "error", "message" => "Room data is missing in session."]);
        exit;
    }

    // Check-in and check-out validations
    $today_date = new DateTime(date("Y-m-d"));
    $checkin_date = new DateTime($frm_data['checkin']);
    $checkout_date = new DateTime($frm_data['checkout']);

    if ($checkin_date == $checkout_date) {
        $status = 'checkin_out_equal';
        $result = ["status" => $status];
    } elseif ($checkout_date < $checkin_date) {
        $status = 'checkout_earlier';
        $result = ["status" => $status];
    } elseif ($checkin_date < $today_date) {
        $status = 'checkin_earlier';
        $result = ["status" => $status];
    }

    // If there are errors, return the result
    if ($status != "") {
        echo json_encode($result);
        exit;
    }

    // Run query to check room availability
    $tb_query = "SELECT COUNT(*) AS `total_bookings` FROM `bookings`
                 WHERE  AND room_id=?
                 AND checkout > ? AND checkin < ?";
    $values = ['booked', $_SESSION['room']['id'], $frm_data['checkin'], $frm_data['checkout']];
    $tb_fetch = mysqli_fetch_assoc(select($tb_query, $values, 'siss'));

    $rq_result = select("SELECT `quantity` FROM `rooms` WHERE `id`=?", [$_SESSION['room']['id']], 'i');
    $rq_fetch = mysqli_fetch_assoc($rq_result);

    if (($rq_fetch['quantity'] - $tb_fetch['total_bookings']) == 0) {
        $status = 'unavailable';
        $result = ["status" => $status];
        echo json_encode($result);
        exit;
    }

    if (isset($_POST['check_availability'])) {
      $checkin = $_POST['checkin'];
      $checkout = $_POST['checkout'];
      $price_per_night = $_SESSION['room']['price'];
  
      $date1 = new DateTime($checkin);
      $date2 = new DateTime($checkout);
  
      // Calculate the number of days
      $days = $date1->diff($date2)->days;
  
      if ($days <= 0) {
          echo json_encode(['status' => 'invalid_dates']);
          exit;
      }
  
      // Calculate total payment
      $total_payment = $days * $price_per_night;
  
      echo json_encode([
          'status' => 'available',
          'days' => $days,
          'payment' => $total_payment
      ]);
      exit;
  }
}
