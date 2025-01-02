<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php require('inc/links.php'); ?>
  <title><?php echo $settings_r['site_title'] ?> - BOOKINGS</title>
</head>
<body class="bg-light">

  <?php 
    require('inc/header.php'); 

    if(!(isset($_SESSION['login']) && $_SESSION['login']==true)){
      redirect('index.php');
    }
  ?>

  <div class="container">
    <div class="row">
      <div class="col-12 my-5 px-4">
        <h2 class="fw-bold">BOOKINGS</h2>
        <div style="font-size: 14px;">
          <a href="index.php" class="text-secondary text-decoration-none">HOME</a>
          <span class="text-secondary"> > </span>
          <a href="#" class="text-secondary text-decoration-none">BOOKINGS</a>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="col-12 mb-3">
        <input type="text" id="search_bar" class="form-control shadow-none" placeholder="Search by Name, Phone, or Address">
      </div>

      <!-- Bookings Table -->
      <div class="col-12">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>#</th>
              <th>Details</th>
              <th>Dates</th>
              <th>Price</th>
            </tr>
          </thead>
          <tbody id="bookings-data">
            <!-- Booking rows will be dynamically inserted here -->
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="col-12">
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center" id="pagination">
            <!-- Pagination buttons will be dynamically inserted here -->
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <?php require('inc/footer.php'); ?>

  <script>
    let current_page = 1;
    const search_bar = document.getElementById('search_bar');

    // Fetch bookings dynamically
    function get_bookings(page = 1, search = '') {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'ajax/get_bookings.php', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function () {
        const response = JSON.parse(this.responseText);

        document.getElementById('bookings-data').innerHTML = response.table_data;
        document.getElementById('pagination').innerHTML = response.pagination;
      };
      xhr.send(`get_bookings=true&page=${page}&search=${search}`);
    }

    // Handle pagination
    function change_page(page) {
      current_page = page;
      get_bookings(page, search_bar.value.trim());
    }

    // Add event listener for search
    search_bar.addEventListener('input', function () {
      get_bookings(1, this.value.trim());
    });

    // Initial fetch on page load
    window.onload = function () {
      get_bookings();
    };
  </script>
</body>
</html>
