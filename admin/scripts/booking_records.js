function get_bookings(search = '', page = 1) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "ajax/booking_records.php", true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onload = function () {
    try {
      let data = JSON.parse(this.responseText);
      document.getElementById('table-data').innerHTML = data.table_data;
      document.getElementById('table-pagination').innerHTML = data.pagination;
    } catch (e) {
      console.error('Error parsing JSON:', e.message, this.responseText);
    }
  };

  xhr.send('get_bookings=true&search=' + encodeURIComponent(search) + '&page=' + page);
}

function change_page(page) {
  get_bookings(document.getElementById('search_input').value, page);
}

function download(id) {
  window.location.href = 'generate_pdf.php?gen_pdf&id=' + id;
}
function deletebooking(id) {
  if (confirm('Are you sure you want to delete this booking?')) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/booking_records.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
      if (this.responseText == 1) {
        alert('Booking deleted successfully!');
        get_bookings(); // Refresh bookings
      } else {
        alert('Error deleting booking!');
      }
    };

    xhr.send('delete_booking=true&id=' + id);
  }
}



window.onload = function () {
  get_bookings();
};
