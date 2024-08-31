//no exist product warning
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
    window.location.href = 'Sales.php';
}




document.addEventListener('DOMContentLoaded', function() {

  document.getElementById('modal-confimrestart').style.display = 'none';

  document.getElementById('restart').addEventListener('click', function() {
      document.getElementById('modal-confimrestart').style.display = 'block';
  });
});

    function submitForm() {
        document.getElementById("deleteForm").submit();
    }



    function getCurrentDate() {
      var currentDate = new Date();
      var formattedDate = currentDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' });
      return formattedDate;
  } 
  function updateCurrentDate() {
      var currentDateElement = document.getElementById("currentDate");
      currentDateElement.textContent = getCurrentDate();
  }
  document.getElementById("btnConfirm").addEventListener("click", function () {
      updateCurrentDate();
  });
  updateCurrentDate();
  



  function printTable() {
    var printContents = `
        <div id="printSection">
            <div style="text-align: center;">
                <img src="images/Logo.png" alt="Logo" style="width: 100px; height: auto;">
                <h2>Sales Report</h2>
            </div>
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid black;">
                ${document.getElementById('Table-stocks').innerHTML}
            </table>
        </div>
    `;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload(); // Reload the page to restore the original content
}

document.getElementById('print').addEventListener('click', printTable);