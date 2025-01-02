<?php

require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
date_default_timezone_set("Asia/Manila");

session_start();

if (isset($_GET['fetch_rooms'])) {
    // Load settings
    $settings_q = "SELECT * FROM `settings` WHERE `sr_no`=1";
    $settings_result = mysqli_query($con, $settings_q);

    if (!$settings_result) {
        die("Query failed: " . mysqli_error($con));
    }

    $settings_r = mysqli_fetch_assoc($settings_result);

    if (!$settings_r || !isset($settings_r['shutdown'])) {
        echo "<h3 class='text-center text-danger'>Settings data could not be loaded!</h3>";
        exit;
    }

    // Decode data from the frontend
    $chk_avail = isset($_GET['chk_avail']) ? json_decode($_GET['chk_avail'], true) : ['checkin' => '', 'checkout' => ''];
    $guests = isset($_GET['guests']) ? json_decode($_GET['guests'], true) : ['adults' => 0, 'children' => 0];
    $facility_list = isset($_GET['facility_list']) ? json_decode($_GET['facility_list'], true) : ['facilities' => []];

    $adults = $guests['adults'];
    $children = $guests['children'];

    // Validate Check-in and Check-out Dates
    if ($chk_avail['checkin'] != '' && $chk_avail['checkout'] != '') {
        $today_date = new DateTime(date("Y-m-d"));
        $checkin_date = new DateTime($chk_avail['checkin']);
        $checkout_date = new DateTime($chk_avail['checkout']);

        if ($checkin_date == $checkout_date || $checkout_date < $checkin_date || $checkin_date < $today_date) {
            echo "<h3 class='text-center text-danger'>Invalid Dates Entered!</h3>";
            exit;
        }
    }

    $output = "";
    $count_rooms = 0;

    // Fetch available rooms based on guests
    $room_res = select(
        "SELECT * FROM `rooms` WHERE `adult` >= ? AND `children` >= ? AND `status` = ? AND `removed` = ?",
        [$adults, $children, 1, 0],
        'iiii'
    );

    while ($room_data = mysqli_fetch_assoc($room_res)) {
        // Check booking availability
        if ($chk_avail['checkin'] != '' && $chk_avail['checkout'] != '') {
            $tb_query = "SELECT COUNT(*) AS `total_bookings` FROM `bookings` 
                        WHERE `room_id` = ? AND `checkout` > ? AND `checkin` < ?";
            $values = [$room_data['id'], $chk_avail['checkin'], $chk_avail['checkout']];
            $tb_fetch = mysqli_fetch_assoc(select($tb_query, $values, 'iss'));

            if (($room_data['quantity'] - $tb_fetch['total_bookings']) == 0) {
                continue;
            }
        }

        // Fetch facilities and check if all required facilities are available
        $facilities_data = getRoomFacilities($room_data['id'], $facility_list['facilities']);
        if (!$facilities_data['all_match']) {
            continue;
        }

        // Get room features
        $features_data = getRoomFeatures($room_data['id']);

        // Get room thumbnail
        $room_thumb = ROOMS_IMG_PATH . "thumbnail.jpg";
        $thumb_q = mysqli_query($con, "SELECT * FROM `room_images` WHERE `room_id` = '{$room_data['id']}' AND `thumb` = '1'");
        if (mysqli_num_rows($thumb_q) > 0) {
            $thumb_res = mysqli_fetch_assoc($thumb_q);
            $room_thumb = ROOMS_IMG_PATH . $thumb_res['image'];
        }

        // Generate Book Button
        $book_btn = "";
        if (!$settings_r['shutdown']) {
            $login = isset($_SESSION['login']) && $_SESSION['login'] == true ? 1 : 0;
            $book_btn = "<button onclick='checkLoginToBook($login, {$room_data['id']})' class='btn btn-sm w-100 text-white custom-bg shadow-none mb-2'>Book Now</button>";
        }

        // Generate room card
        $output .= "
        <div class='card mb-4 border-0 shadow'>
          <div class='row g-0 p-3 align-items-center'>
            <div class='col-md-5 mb-lg-0 mb-md-0 mb-3'>
              <img src='$room_thumb' class='img-fluid rounded'>
            </div>
            <div class='col-md-5 px-lg-3 px-md-3 px-0'>
              <h5 class='mb-3'>{$room_data['name']}</h5>
              <div class='features mb-3'>
                <h6 class='mb-1'>Features</h6>
                {$features_data}
              </div>
              <div class='facilities mb-3'>
                <h6 class='mb-1'>Facilities</h6>
                {$facilities_data['html']}
              </div>
              <div class='guests'>
                <h6 class='mb-1'>Guests</h6>
                <span class='badge rounded-pill bg-light text-dark text-wrap'>
                  {$room_data['adult']} Adults
                </span>
                <span class='badge rounded-pill bg-light text-dark text-wrap'>
                  {$room_data['children']} Children
                </span>
              </div>
            </div>
            <div class='col-md-2 mt-lg-0 mt-md-0 mt-4 text-center'>
              <h6 class='mb-4'>₱{$room_data['price']} per night</h6>
              $book_btn
              <a href='room_details.php?id={$room_data['id']}' class='btn btn-sm w-100 btn-outline-dark shadow-none'>More details</a>
            </div>
          </div>
        </div>";
        $count_rooms++;
    }

    echo $count_rooms > 0 ? $output : "<h3 class='text-center text-danger'>No rooms to show!</h3>";
}

function getRoomFacilities($room_id, $facility_filter) {
    global $con;
    $fac_q = mysqli_query($con, "SELECT f.name, f.id FROM `facilities` f INNER JOIN `room_facilities` rfac ON f.id = rfac.facilities_id WHERE rfac.room_id = '$room_id'");
    $facilities_data = "";
    $fac_count = 0;

    while ($fac_row = mysqli_fetch_assoc($fac_q)) {
        if (in_array($fac_row['id'], $facility_filter)) {
            $fac_count++;
        }
        $facilities_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>{$fac_row['name']}</span>";
    }

    return [
        'html' => $facilities_data,
        'all_match' => count($facility_filter) == $fac_count
    ];
}

function getRoomFeatures($room_id) {
    global $con;
    $fea_q = mysqli_query($con, "SELECT f.name FROM `features` f INNER JOIN `room_features` rfea ON f.id = rfea.features_id WHERE rfea.room_id = '$room_id'");
    $features_data = "";

    while ($fea_row = mysqli_fetch_assoc($fea_q)) {
        $features_data .= "<span class='badge rounded-pill bg-light text-dark text-wrap me-1 mb-1'>{$fea_row['name']}</span>";
    }

    return $features_data;
}

?>
