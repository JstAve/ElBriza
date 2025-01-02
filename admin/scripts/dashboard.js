function booking_analytics(range = 1) {
  const xhr = new XMLHttpRequest();
  xhr.open('POST', 'ajax/dashboard.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function () {
    if (xhr.status === 200) {
      const stats = JSON.parse(this.responseText);

      // Update statistics in the DOM
      document.getElementById('total_bookings').textContent = stats.total_bookings;
      document.getElementById('total_profit').textContent = `₱${stats.total_profit}`;
    } else {
      console.error('Failed to fetch booking analytics.');
    }
  };

  xhr.send(`get_booking_statistics=true&range=${range}`);
}

// Fetch default analytics for the past 30 days on page load
document.addEventListener('DOMContentLoaded', function () {
  booking_analytics(1);
});


function user_analytics(period=1)
{
  let xhr = new XMLHttpRequest();
  xhr.open("POST","ajax/dashboard.php",true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function(){
    let data = JSON.parse(this.responseText);

    document.getElementById('total_new_reg').textContent = data.total_new_reg;
    document.getElementById('total_queries').textContent = data.total_queries;
    document.getElementById('total_reviews').textContent = data.total_reviews;
  }

  xhr.send('user_analytics&period='+period);
}



window.onload = function(){
  booking_analytics();
  user_analytics();
}