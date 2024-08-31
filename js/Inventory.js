//dispaly the current date
function getCurrentDate() {
  var today = new Date();
  var formattedDate = today.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' });
  return formattedDate;
}
window.onload = function () {
  var currentDate = getCurrentDate();
  document.getElementById("currentDate").innerText = currentDate;
};


//open
function toggleAddProductModal() {
    var modal = document.getElementById('addProduct');
    modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
}

// Close modal when clicking the close button
document.getElementById('closebtnPro').addEventListener('click', function() {
    document.getElementById('addProduct').style.display = 'none';
    window.location.reload(); 
  });

window.onclick = function(event) {
    var modal = document.getElementById('addProduct');
    if (event.target == modal) {
        modal.style.display = 'none';
        window.location.reload(); 
    }
};

 
// Close modal when clicking the close button
document.getElementById('closebtnPro2').addEventListener('click', function() {
  document.getElementById("updateProduct").style.display = 'none';
  window.location.href = 'Inventory.php'; 
});

window.onclick = function(event) {
  var modal = document.getElementById('updateProduct');
  if (event.target == modal) {
      modal.style.display = 'none';
      window.location.href = 'Inventory.php';
  }
};


//get the product id to input in the modal for product updates
$(document).ready(function() {
  $('.pen').click(function(event) {
      event.preventDefault();
      var id = $(this).attr('product-id');

      $('#updateid').val(id);

      console.log('Product ID:', id);
      var updateModal = document.getElementById('updateProduct');
      var modal = new bootstrap.Modal(updateModal);
      modal.show();
  });
});


//search for enetr key
document.addEventListener("DOMContentLoaded", function() {
  document.getElementById("searchInput").addEventListener("keypress", function(event) {
      if (event.key === "Enter") {
          event.preventDefault();
          document.getElementById("searchForm").submit();
      }
  });
});


// Hide the message no exist product after 10 sec
setTimeout(function() {
  document.getElementById("noPro").style.display = "none"; 
}, 1000); 

if (performance.navigation.type === 1) {
  document.getElementById("noPro").style.display = "none";
}

document.addEventListener('DOMContentLoaded', function() {
  if (performance.navigation.type === 1) {
      var noProElement = document.getElementById("noPro");
      if (noProElement) {
          noProElement.style.display = "none";
      }
  }
});

function restartPage() {
  window.location.href = 'Inventory.php';
}


$(document).ready(function() {
  $('.delete').click(function(event) {
    event.preventDefault();
    var id = $(this).find('i').attr('deleteid'); 
    console.log('Product ID:', id);

    $('#deleteid').val(id);

    var updateModal = document.getElementById('Delete_pro');
    var modal = new bootstrap.Modal(updateModal);
    modal.show();
  });


  $('#btnCancel').click(function(event) {
    event.preventDefault();
    console.log('Cancel button clicked');
    window.location.href = "Inventory.php";
  });
});
