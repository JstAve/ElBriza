<?php
require('../admin/inc/db_config.php');
require('../admin/inc/essentials.php');
date_default_timezone_set("Asia/Manila");

header('Content-Type: application/json');

// Check if the user is logged in
session_start();
if (!isset($_SESSION['uId'])) {
    echo json_encode([
        "table_data" => "<tr><td colspan='5' class='text-center'>Unauthorized Access</td></tr>",
        "pagination" => ""
    ]);
    exit;
}

if (isset($_POST['get_bookings'])) {
    $frm_data = filteration($_POST);

    $limit = 5; // Number of records per page
    $page = isset($frm_data['page']) ? $frm_data['page'] : 1;
    $start = ($page - 1) * $limit;
    $search = isset($frm_data['search']) ? $frm_data['search'] : '';

    // Get the logged-in user's ID from the session
    $user_id = $_SESSION['uId'];

    $query = "SELECT * FROM `bookings` 
              WHERE `user_id` = ? 
              AND (`name` LIKE ? OR `phonenum` LIKE ? OR `address` LIKE ?) 
              ORDER BY `id` DESC 
              LIMIT $start, $limit";
    $values = [$user_id, "%$search%", "%$search%", "%$search%"];
    $res = select($query, $values, 'isss');

    $total_query = "SELECT COUNT(*) AS `total` FROM `bookings` 
                    WHERE `user_id` = ? 
                    AND (`name` LIKE ? OR `phonenum` LIKE ? OR `address` LIKE ?)";
    $total_res = select($total_query, $values, 'isss');
    $total_rows = mysqli_fetch_assoc($total_res)['total'];

    if ($total_rows == 0) {
        echo json_encode([
            "table_data" => "<tr><td colspan='5' class='text-center'>No Data Found</td></tr>",
            "pagination" => ""
        ]);
        exit;
    }

    $i = $start + 1;
    $table_data = "";

    while ($data = mysqli_fetch_assoc($res)) {
        $checkin = date("d-m-Y", strtotime($data['checkin']));
        $checkout = date("d-m-Y", strtotime($data['checkout']));
        $created_at = date("d-m-Y", strtotime($data['created_at']));

        $table_data .= "
            <tr>
              <td>$i</td>
              <td>
                <b>Name:</b> {$data['name']}<br>
                <b>Phone:</b> {$data['phonenum']}<br>
                <b>Address:</b> {$data['address']}
              </td>
              <td>
                <b>Check-in:</b> $checkin<br>
                <b>Check-out:</b> $checkout<br>
                <b>Booking Date:</b> $created_at
              </td>
              <td>₱{$data['total_price']}</td>
            </tr>
        ";
        $i++;
    }

    $pagination = "";
    $total_pages = ceil($total_rows / $limit);

    if ($total_pages > 1) {
        if ($page != 1) {
            $pagination .= "<li class='page-item'>
                <button onclick='change_page(1)' class='page-link shadow-none'>First</button>
            </li>";
        }

        $prev = $page - 1;
        $pagination .= "<li class='page-item'>
            <button onclick='change_page($prev)' class='page-link shadow-none'>Prev</button>
        </li>";

        $next = $page + 1;
        $pagination .= "<li class='page-item'>
            <button onclick='change_page($next)' class='page-link shadow-none'>Next</button>
        </li>";

        if ($page != $total_pages) {
            $pagination .= "<li class='page-item'>
                <button onclick='change_page($total_pages)' class='page-link shadow-none'>Last</button>
            </li>";
        }
    }

    echo json_encode(["table_data" => $table_data, "pagination" => $pagination]);
}
?>
