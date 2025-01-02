<?php 

  require('../inc/db_config.php');
  require('../inc/essentials.php');
  adminLogin();
  header('Content-Type: application/json');
  
  if (isset($_POST['get_booking_statistics'])) {
    $range = intval($_POST['range']);

    $condition = "";
    if ($range === 1) {
        $condition = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($range === 2) {
        $condition = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
    } elseif ($range === 3) {
        $condition = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }

    // Total Bookings
    $total_bookings_query = "SELECT COUNT(*) AS total_bookings FROM bookings $condition";
    $total_bookings_res = mysqli_fetch_assoc(mysqli_query($con, $total_bookings_query));
    $total_bookings = $total_bookings_res['total_bookings'] ?? 0;

    // Total Profit
    $profit_query = "SELECT SUM(total_price) AS total_profit FROM bookings $condition";
    $profit_res = mysqli_fetch_assoc(mysqli_query($con, $profit_query));
    $total_profit = $profit_res['total_profit'] ?? 0;

    $stats = [
        'total_bookings' => $total_bookings,
        'total_profit' => $total_profit
    ];

    echo json_encode($stats);
    exit;
}

  if(isset($_POST['user_analytics']))
  {
    $frm_data = filteration($_POST);

    $condition="";

    if($frm_data['period']==1){
      $condition="WHERE datentime BETWEEN NOW() - INTERVAL 30 DAY AND NOW()";
    }
    else if($frm_data['period']==2){
      $condition="WHERE datentime BETWEEN NOW() - INTERVAL 90 DAY AND NOW()";
    }
    else if($frm_data['period']==3){
      $condition="WHERE datentime BETWEEN NOW() - INTERVAL 1 YEAR AND NOW()";
    }

    $total_reviews = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(sr_no) AS `count`
      FROM `rating_review` $condition"));

    $total_queries = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(sr_no) AS `count`
      FROM `user_queries` $condition"));

    $total_new_reg = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(id) AS `count`
    FROM `user_cred` $condition"));

    $output = ['total_queries' => $total_queries['count'],
      'total_reviews' => $total_reviews['count'],
      'total_new_reg' => $total_new_reg['count']
    ];

    $output = json_encode($output);

    echo $output;

  }

?>