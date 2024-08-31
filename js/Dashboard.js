function validatePin() {
    var pin1 = document.getElementById("ip3").value;
    var pin2 = document.getElementById("ip4").value;

    if (pin1 !== pin2) {
        alert("PINs do not match. Please re-enter the PINs.");
        return false; 
    }

    return true; 
}







document.getElementById("searchInput").addEventListener("keyup", function(event) {
    // Number 13 is the "Enter" key on the keyboard
    if (event.keyCode === 13) {
        event.preventDefault();
        document.getElementById("searchForm").submit();
    }
});


function restartPage() {
    window.location.replace('Dashboard.php'); 
}
window.history.replaceState({}, document.title, window.location.pathname);



// Function to fetch data from PHP endpoint
function fetchData() {
    fetch('graph.php')
    .then(response => response.json())
    .then(data => {
        renderChart(data.labels, data.salesData);
    })
    .catch(error => console.error('Error fetching data:', error));
}

// Function to render chart with provided data
function renderChart(labels, salesData) {
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales Percentage',
                data: salesData,
                backgroundColor: '#FFDABE',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: '#000000' 
                    }
                }
            },
            scales: {
                y: {
                    min: 0, 
                    max: 100, 
                    stepSize: 10, 
                    ticks: {
                        color: '#000000', 
                        callback: function(value) {
                            return value + '%'; 
                        }
                    },
                    grid: {
                        color: "rgba(0, 0, 0, 0.1)" 
                    }
                },
                x: {
                    grid: {
                        color: "rgba(0, 0, 0, 0.1)" 
                    },
                    ticks: {
                        color: '#000000' 
                    }
                }
            }
        }
    });
}

fetchData();






  

//history of the inventory
   fetchDataFromInventoryHistory();

  function fetchDataFromInventoryHistory() {
      console.log('Fetching data from inventory history');
      
      fetch('history.php')
          .then(response => response.json())
          .then(data => {
              displayInventoryData(data);
          });
  }
  
  function displayInventoryData(data) {
    let tbody = document.querySelector('.tdbody2');
    data.forEach(row => {
        let tr = document.createElement('tr');
        tr.className = 'trdisplay';

        // Parse the date string to a Date object
        let dateObj = new Date(row.date);

        // Format the date as "Month dd, yyyy"
        let formattedDate = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: '2-digit' });

        tr.innerHTML = `
            <td scope='row'>${row.product_name}</td>
            <td scope='row'>${row.brand}</td>
            <td scope='row'>${row.description}</td>
            <td scope='row'>${parseFloat(row.product_price).toFixed(2)}</td>
            <td scope='row'>${row.stocks}</td>
            <td scope='row'>${formattedDate}</td>
        `;
        tbody.appendChild(tr);
    });
}

  