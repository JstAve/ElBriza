<?php 

require('../inc/db_config.php');
require('../inc/essentials.php');
date_default_timezone_set("Asia/Manila");
adminLogin();
header('Content-Type: application/json'); // Set JSON header
ob_clean();

if (isset($_POST['delete_booking'])) {
  $frm_data = filteration($_POST);

  if (!isset($frm_data['id']) || empty($frm_data['id'])) {
      echo "Error: Missing booking ID";
      exit;
  }

  $query = "DELETE FROM `bookings` WHERE `id`=?";
  $values = [$frm_data['id']];
  $res = delete($query, $values, 'i');

  echo $res; // Output 1 if successful, 0 otherwise
}



if (isset($_POST['get_bookings'])) {
    $frm_data = filteration($_POST);

    $limit = 2;
    $page = isset($frm_data['page']) ? $frm_data['page'] : 1; // Ensure page exists
    $start = ($page - 1) * $limit;

    $query = "SELECT * FROM bookings 
              ORDER BY id DESC 
              LIMIT $start, $limit";

    $res = mysqli_query($con, $query);

    if (!$res) {
        die('Query Failed: ' . mysqli_error($con));
    }

    $total_rows_query = "SELECT COUNT(*) AS total FROM bookings";
    $total_rows_res = mysqli_query($con, $total_rows_query);
    $total_rows = mysqli_fetch_assoc($total_rows_res)['total'];

    if ($total_rows == 0) {
        $output = json_encode(["table_data" => "<b>No Data Found!</b>", "pagination" => '']);
        echo $output;
        exit;
    }

    $i = $start + 1;
    $table_data = "";

    while ($data = mysqli_fetch_assoc($res)) {
        $created_at = date("d-m-Y", strtotime($data['created_at']));
        $checkin = date("d-m-Y", strtotime($data['checkin']));
        $checkout = date("d-m-Y", strtotime($data['checkout']));

        $table_data .= "
            <tr>
              <td>$i</td>
              <td>
                <b>Name:</b> $data[name]<br>
                <b>Phone No:</b> $data[phonenum]<br>
                <b>Address:</b> $data[address]
                <b>gcash reference number:</b> $data[reference]
              </td>
              <td>
                <b>Check-In:</b> $checkin<br>
                <b>Check-Out:</b> $checkout
              </td>
              <td>
                <b>Total Price:</b> ₱$data[total_price]<br>
                <b>Booking Date:</b> $created_at
              </td>
              <td>
               
              </td>
              <td>
        <button type='button' onclick='deletebooking($data[id])' class='btn btn-outline-danger btn-sm'>
          Cancel Booking
        </button>
              </td>
            </tr>
        ";
        $i++;
    }

    // Pagination Logic
    $pagination = "";
    $total_pages = ceil($total_rows / $limit);

    if ($total_pages > 1) {
        if ($page != 1) {
            $pagination .= "<li class='page-item'>
                <button onclick='change_page(1)' class='page-link shadow-none'>First</button>
            </li>";
        }

        $disabled = ($page == 1) ? "disabled" : "";
        $prev = $page - 1;
        $pagination .= "<li class='page-item $disabled'>
            <button onclick='change_page($prev)' class='page-link shadow-none'>Prev</button>
        </li>";

        $disabled = ($page == $total_pages) ? "disabled" : "";
        $next = $page + 1;
        $pagination .= "<li class='page-item $disabled'>
            <button onclick='change_page($next)' class='page-link shadow-none'>Next</button>
        </li>";

        if ($page != $total_pages) {
            $pagination .= "<li class='page-item'>
                <button onclick='change_page($total_pages)' class='page-link shadow-none'>Last</button>
            </li>";
        }
    }

    $output = json_encode(["table_data" => $table_data, "pagination" => $pagination]);

    echo $output;
}



?>
