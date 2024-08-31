  // Function to hide all products
  function hideAllProducts() {
    var categoryLists = document.querySelectorAll(
      ".category-list-container > div"
    );
    categoryLists.forEach(function (list) {
      list.style.display = "none";
    });
  }


  function showAllProducts() {
    hideAllProducts();
    document.getElementById("allProducts").style.display = "block";
  }

  // Event listener for the "All" button click
  document.getElementById("button0").addEventListener("click", function () {
    showAllProducts();
  });

  // Trigger click event on page load
  window.addEventListener("load", function () {
    document.getElementById("button0").click();
  });

  document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".button");

    //buttons for different categories
    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        buttons.forEach((btn) => btn.classList.remove("active"));
        button.classList.add("active");
        const categoryList = document.querySelector(".category-list");
        categoryList.innerHTML = "";
      });
    });

    // Set "All Products" button as active by default
    document.getElementById("button0").classList.add("active");

    // Event listener for the "All Products" button
    document.getElementById("button0").addEventListener("click", function () {
      hideAllProducts();
      document.getElementById("allProducts").style.display = "block";
    });

    // Event listener for the "Nails" button
    document.getElementById("button1").addEventListener("click", function () {
      hideAllProducts();
      document.getElementById("nailProducts").style.display = "block";
    });

    // Event listener for the "Cement" button
    document.getElementById("button2").addEventListener("click", function () {
      hideAllProducts();
      document.getElementById("cementProducts").style.display = "block";
    });

    // Event listener for the "Roofsheet" button
    document.getElementById("button3").addEventListener("click", function () {
      hideAllProducts();
      document.getElementById("roofsheetProducts").style.display = "block";
    });

    document.getElementById("button4").addEventListener("click", function () {
      hideAllProducts();
      document.getElementById("paintProducts").style.display = "block";
    });
    document.getElementById("button5").addEventListener("click", function () {
      hideAllProducts();
      document.getElementById("plywoodProducts").style.display = "block";
    });
    document.getElementById("button6").addEventListener("click", function () {
      hideAllProducts();
      document.getElementById("steelbarProducts").style.display = "block";
    });
  });

  // Function to hide all product categories
  function hideAllProducts() {
    var allProducts = document.getElementById("allProducts");
    var nailProducts = document.getElementById("nailProducts");
    var cementProducts = document.getElementById("cementProducts");
    var roofsheetProducts = document.getElementById("roofsheetProducts");
    var paintProducts = document.getElementById("paintProducts");
    var plywoodProducts = document.getElementById("plywoodProducts");
    var steelbarProducts = document.getElementById("steelbarProducts");

    allProducts.style.display = "none";
    nailProducts.style.display = "none";
    cementProducts.style.display = "none";
    roofsheetProducts.style.display = "none";
    paintProducts.style.display = "none";
    plywoodProducts.style.display = "none";
    steelbarProducts.style.display = "none";
  }

  var addedProductIds = [];

  function addToPending(
    idValue,
    productNameValue,
    brandValue,
    descriptionValue,
    stocksValue,
    priceValue
  ) {

    if (addedProductIds.includes(idValue)) {
      alert("This product is already added to the pending list.");
      return;
    }

    id = idValue;
    productName = productNameValue;
    brand = brandValue;
    description = descriptionValue;
    stocks = stocksValue;
    price = priceValue;


    var modal = document.getElementById("myModal");
    var productInfo = document.getElementById("productInfo");
    var additionalInfo = document.getElementById("additionalInfo");

    productInfo.innerHTML =
      "<span>Product Name: </span> " +
      productName +
      "<br><span>Brand: </span> " +
      brand +
      "<br><span>Description: </span> " +
      description +
      "<br><span>Stocks: </span> " +
      stocks +
      "<br><span>Price: </span> " +
      price;

    if (productName.toLowerCase().includes("nail")) {
      additionalInfo.placeholder = "Enter the quantity in kilos";
    } else {
      additionalInfo.placeholder = "Enter the quantity";
    }

    // Display the modal
    modal.style.display = "block";
  }

  var id, productName, brand, stocks, description, price;
  document.addEventListener("DOMContentLoaded", function () {

    var saveBtn = document.getElementById("saveBtn");

    saveBtn.addEventListener("click", function () {
      var additionalInfoValue = document.getElementById("additionalInfo").value;

      var quantity = parseFloat(additionalInfoValue);

      if (quantity > stocks) {
        alert("Out of stock or insufficient stock!");
        return;
      }
      var totalPrice = quantity * price;
      var tableBody = document.querySelector(".pending-table tbody");
      var newRow = document.createElement("tr");

      newRow.innerHTML = `
          <td>${id}</td>
          <td>${productName}</td>
          <td>${brand}</td>
          <td>${description}</td>
          <td>${quantity}</td>
          <td>${new Intl.NumberFormat().format(price)}</td>
          <td>${new Intl.NumberFormat().format(totalPrice)}</td>
          <td><button onclick="deleteRow(this)" style="background-color: transparent; border: none;"><i class="fa fa-trash"></i></button></td>
      `;

      tableBody.appendChild(newRow);

      document.getElementById("additionalInfo").value = "";

      addedProductIds.push(id);

      var totalAmount = 0;
      var rows = document.querySelectorAll(".pending-table tbody tr");
      rows.forEach(function (row) {
        var totalPriceCell = row.querySelector("td:nth-child(7)");
        var totalPrice = parseFloat(totalPriceCell.textContent.replace(/,/g, ""));
        totalAmount += totalPrice;
      });

      var formattedTotalAmount = new Intl.NumberFormat().format(
        totalAmount.toFixed(2)
      );
      document.getElementById("totalAmount").textContent = formattedTotalAmount;
    });
  });

  document.addEventListener("DOMContentLoaded", function () {
    var closeButtons = document.getElementsByClassName("close");
    for (var i = 0; i < closeButtons.length; i++) {
      closeButtons[i].addEventListener("click", function () {
        var modal = this.parentElement.parentElement;
        var additionalInfo = document.getElementById("additionalInfo");


        modal.style.display = "none";

        additionalInfo.value = "";
      });
    }
  });

  function deleteRow(button) {

    var row = button.parentNode.parentNode;

    var totalPriceCell = row.querySelector("td:nth-child(6)");
    var totalPrice = parseFloat(totalPriceCell.textContent.replace(/,/g, ""));

    row.parentNode.removeChild(row);

    var totalAmountElement = document.getElementById("totalAmount");
    var currentTotalAmount = parseFloat(
      totalAmountElement.textContent.replace(/[^\d.]/g, "")
    );
    var newTotalAmount = currentTotalAmount - totalPrice;
    var formattedTotalAmount = new Intl.NumberFormat().format(
      newTotalAmount.toFixed(2)
    );
    totalAmountElement.textContent = "Total: " + formattedTotalAmount;
  }

  document.addEventListener("DOMContentLoaded", function () {
    var cancelButton = document.querySelector(".cancel-btn");

    cancelButton.addEventListener("click", function () {
        var confirmCancel = confirm("Are you sure you want to cancel?");

        if (confirmCancel) {
            var rows = document.querySelectorAll(".pending-table tbody tr");

            rows.forEach(function (row) {
                row.parentNode.removeChild(row);
            });

            document.getElementById("totalAmount").textContent = "0.00";
            window.location.reload();
        }
    });
});

var modal = document.getElementById("myModal");
var saveBtn = document.getElementById("saveBtn");

saveBtn.addEventListener("click", function () {
    modal.style.display = "none";
});



  //proceed
  // Define a function to retrieve pending purchases data from the table
// Function to retrieve pending purchases data from the table
function retrievePendingPurchases() {
  var pendingPurchasesData = [];
  var rows = document.querySelectorAll(".pending-table tbody tr");
  rows.forEach(function (row) {
    var rowData = {
      id: row.cells[0].textContent,
      productName: row.cells[1].textContent,
      brand: row.cells[2].textContent,
      description: row.cells[3].textContent,
      quantity: parseFloat(row.cells[4].textContent),
      price: parseFloat(row.cells[5].textContent.replace(/,/g, "")),
      totalPrice: parseFloat(row.cells[6].textContent.replace(/,/g, "")),
    };
    console.log("Quantity value:", rowData.quantity); 
    pendingPurchasesData.push(rowData);
  });
  return pendingPurchasesData;
}


  proceedButton.addEventListener("click", function () {
        var pendingPurchasesRows = document.querySelectorAll(".pending-table tbody tr");
        if (pendingPurchasesRows.length === 0) {
            alert("Please add products before proceeding.");
            return; 
        }
    var modal = document.getElementById("proceedModal");


    var totalAmount = document.getElementById("totalAmount").textContent;

    var totalAmountModal = document.getElementById("totalAmountModal");
    totalAmountModal.textContent = totalAmount;

    var modalTableBody = modal.querySelector(".pending-table tbody");

    modalTableBody.innerHTML = "";

    var pendingPurchasesData = retrievePendingPurchases();

    pendingPurchasesData.forEach(function (purchase) {
      var newRow = document.createElement("tr");
      newRow.innerHTML = `
            <td>${purchase.id}</td>
            <td>${purchase.productName}</td>
            <td>${purchase.brand}</td>
            <td>${purchase.description}</td>
            <td>${purchase.quantity}</td>
            <td>${purchase.price}</td>
            <td>${purchase.totalPrice}</td>
        `;
      modalTableBody.appendChild(newRow);
    });

    modal.style.display = "block";

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "test.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      alert("Pending purchases data saved successfully.");
    }
  };
  var data = "pendingPurchasesData=" + encodeURIComponent(JSON.stringify(pendingPurchasesData));
  xhr.send(data);
  });

  function calculateChange() {
    var amountReceivedInput = document.getElementById("customer-money");
    var amountReceived = parseFloat(amountReceivedInput.value.replace(/,/g, ""));

    if (isNaN(amountReceived) || amountReceived <= 0) {
      alert("Please enter a valid positive amount received.");
      return;
    }

    var totalAmount = parseFloat(document.getElementById("totalAmount").textContent.replace(/,/g, ""));

    if (amountReceived < totalAmount) {
      alert("Amount received is insufficient to cover the total amount.");
      return;
    }

    var change = amountReceived - totalAmount;

    var formattedChange = new Intl.NumberFormat().format(change.toFixed(2));
    var changeContainer = document.getElementById("changeContainer");
    changeContainer.textContent = "Change: " + formattedChange;
  }

  var payButton = document.getElementById("payButton");
  payButton.addEventListener("click", function () {
    calculateChange();
  });








  // Function to generate a random 6-digit number
  function generateTransactionId() {
    var min = 100000; 
    var max = 999999; 
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }

  function calculateChange() {
    var amountReceived = parseFloat(
      document.getElementById("customer-money").value.replace(/,/g, "")
    );

    var totalAmount = parseFloat(
      document.getElementById("totalAmount").textContent.replace(/,/g, "")
    );

    if (amountReceived < totalAmount) {
      alert("Amount received is insufficient.");
      return;
    }

    var change = amountReceived - totalAmount;

    var formattedChange = new Intl.NumberFormat().format(change.toFixed(2));
    var changeContainer = document.getElementById("changeContainer");
    changeContainer.textContent = "Change: " + formattedChange;
  }

  var payButton = document.getElementById("payButton");
  payButton.addEventListener("click", function () {
    calculateChange();
  });

  function displayReceiptModal() {
    var totalAmountModal = document.getElementById("totalAmountModal").textContent;
    var amountReceived = document.getElementById("customer-money").value;
    var change = document.getElementById("changeContainer").textContent;
    var pendingPurchasesTableRows = document.querySelectorAll("#proceedModal .pending-table tbody tr");

    var receiptTableBody = document.querySelector("#receiptModal .receipt-table tbody");
    receiptTableBody.innerHTML = ""; 
    pendingPurchasesTableRows.forEach(function (row) {
      var receiptRow = document.createElement("tr");
      receiptRow.innerHTML = row.innerHTML;
      receiptTableBody.appendChild(receiptRow);
    });

    document.querySelector(".total-amount-receipt").textContent = "Total Amount: " + totalAmountModal;
    document.querySelector(".amount-received-receipt").textContent = "Amount Received: " + amountReceived;
    document.querySelector(".change-receipt").textContent = change;

    displayTransactionId();

    var receiptModal = document.getElementById("receiptModal");
    receiptModal.style.display = "block";
  }

  var doneButton = document.getElementById("done-btn");
  doneButton.addEventListener("click", function () {

    displayReceiptModal();
  });

  function displayTransactionId() {
    var transactionId = generateTransactionId();

    var transactionIdElement = document.querySelector(".transaction-id");
    transactionIdElement.textContent = "Transaction ID: " + transactionId;
  }




  // Get the print button and the receipt details div
  const printBtn = document.querySelector('.print-btn');
  const receiptDetails = document.querySelector('.receipt-details');

  // Add event listener to the print button
  printBtn.addEventListener('click', () => {
    // Create a new window and set its properties
    const printWindow = window.open('', '', 'width=800,height=600');

    // Write the HTML content of the receipt details div to the new window
    printWindow.document.write(`
      <html>
        <head>
          <title>Receipt</title>
          <style>
            /* Add any necessary styles for the receipt */
            .receipt-table {
              width: 100%;
              border-collapse: collapse;
            }
            .receipt-table th,
            .receipt-table td {
              border: 1px solid black;
              padding: 5px;
            }
            .receipt-header {
              text-align: center;
            }
            .store-name {
              text-align: center;
            }
            .store-address {
              text-align: center;
            }
            .total-amount-receipt,
            .amount-received-receipt,
            .change-receipt,
            .transaction-id {
              text-align: right;
              font-size: 10px;
            }          
          </style>
        </head>
        <body>
          ${receiptDetails.innerHTML}
        </body>
      </html>
    `);

    // Print the new window and close it
    printWindow.print();
    printWindow.close();
  });

  var newOrderButton = document.querySelector(".new-btn");
  newOrderButton.addEventListener("click", function () {
    window.location.href = 'test.php';
  });





  var printButton = document.querySelector(".print-btn");
  printButton.addEventListener("click", function () {
      // Get data from the receipt modal
      var totalAmountModal = document.querySelector(".total-amount-receipt").textContent.replace("Total Amount: ", "").trim();
      var amountReceived = document.querySelector(".amount-received-receipt").textContent.replace("Amount Received: ", "").trim();
      var change = document.querySelector(".change-receipt").textContent.replace("Change: ", "").trim();
      var transactionId = document.querySelector(".transaction-id").textContent.replace("Transaction ID: ", "").trim();

      // Check if data is already set and send it to the server
      if (totalAmountModal && amountReceived && change && transactionId) {
          // Send data to PHP script using AJAX
          var xhr = new XMLHttpRequest();
          xhr.open("POST", "test.php", true);
          xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
          xhr.onreadystatechange = function() {
              if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                  // On successful save, alert the user or perform any other action
                  alert("Transaction data saved successfully.");
              }
          };
          // Prepare data to send
          var data = "total_amount=" + totalAmountModal + "&amount_received=" + amountReceived + "&change_amount=" + change + "&transaction_id=" + transactionId;
          xhr.send(data);
      }
  });




  
//pay 
  document.addEventListener("DOMContentLoaded", function() {
    const payButton = document.getElementById('payButton');
    const doneBtn = document.getElementById('done-btn');

    doneBtn.disabled = true; // Initially disable done-btn

    payButton.addEventListener('click', function() {
        doneBtn.disabled = false; // Enable done-btn when payButton is clicked
    });

    doneBtn.addEventListener('click', function() {

    });
});


document.addEventListener("DOMContentLoaded", function() {
  var additionalInfoInput = document.getElementById("additionalInfo");
  var saveBtn = document.getElementById("saveBtn");

  // Function to enable or disable the save button based on input value
  function toggleSaveBtn() {
      if (!additionalInfoInput.checkValidity()) {
          saveBtn.disabled = true;
      } else {
          saveBtn.disabled = false;
      }
  }

  // Initial state check
  toggleSaveBtn();

  // Add event listeners to input field
  additionalInfoInput.addEventListener("input", toggleSaveBtn);
  additionalInfoInput.addEventListener("change", toggleSaveBtn);
  additionalInfoInput.addEventListener("keyup", toggleSaveBtn);
});




document.addEventListener("DOMContentLoaded", function() {
  const additionalInfoInput = document.getElementById("customer-money");
  const saveBtn = document.getElementById("payButton");

  // Function to enable or disable the save button based on input value validity
  function toggleSaveBtn() {
    saveBtn.disabled = additionalInfoInput.value.trim() === '' || !additionalInfoInput.checkValidity();
  }

  // Initial state check
  toggleSaveBtn();

  // Add event listeners to input field
  additionalInfoInput.addEventListener("input", toggleSaveBtn);
});

