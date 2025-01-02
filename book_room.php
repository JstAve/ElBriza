<?php 
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start();
    
    $data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

    $user_id = $_SESSION['uId'];
    $room_id = $_SESSION['room']['id'];
    $name = $data['name'];
    $phonenum = $data['phonenum'];
    $address = $data['address'];
    $reference = $data['reference'];
    $checkin = $data['checkin'];
    $checkout = $data['checkout'];
    $total_price = $data['total_price'];
    $days = $data['days'];

    // Validate Check-in and Check-out Dates
    $today = new DateTime(date("Y-m-d"));
    $checkin_date = new DateTime($checkin);
    $checkout_date = new DateTime($checkout);

    if ($checkout_date <= $checkin_date) {
        echo "<script>alert('Check-out date must be later than the check-in date.'); window.history.back();</script>";
        exit;
    }

    if ($checkin_date < $today) {
        echo "<script>alert('Check-in date cannot be in the past.'); window.history.back();</script>";
        exit;
    }

    // Check for overlapping bookings
    $overlap_query = "SELECT COUNT(*) AS `total` FROM `bookings`
                      WHERE `room_id` = ? 
                      AND (`checkin` < ? AND `checkout` > ?)";
    $overlap_stmt = $con->prepare($overlap_query);
    $overlap_stmt->bind_param('iss', $room_id, $checkout, $checkin);
    $overlap_stmt->execute();
    $overlap_result = $overlap_stmt->get_result();
    $overlap_count = $overlap_result->fetch_assoc()['total'];

    if ($overlap_count > 0) {
        echo "<script>alert('The selected dates are not available for booking. Please choose different dates.'); window.history.back();</script>";
        exit;
    }

    // Ensure days and total price are valid
    if ($days <= 0 || $total_price <= 0) {
        redirect('rooms.php'); // Redirect on invalid data
    }

    // Insert booking into the database
    $query = "INSERT INTO `bookings` (`user_id`, `room_id`, `name`, `phonenum`, `address`, `reference`, `checkin`, `checkout`, `total_price`) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $con->prepare($query);
    $stmt->bind_param('iissssssd', $user_id, $room_id, $name, $phonenum, $address, $reference, $checkin, $checkout, $total_price);

    if ($stmt->execute()) {
        unset($_SESSION['room']); // Clear room session data after booking
        redirect('index.php');
    } else {
        echo "<script>alert('Error occurred while booking. Please try again later.'); window.history.back();</script>";
        exit;
    }
}
