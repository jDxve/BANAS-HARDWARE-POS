
document.addEventListener("DOMContentLoaded", function() {
    const buttons = document.querySelectorAll('.button');


    //buttons for different catageries
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            const categoryList = document.querySelector('.category-list');
            categoryList.innerHTML = '';
        });
    });


// Event listener for the "All Products" button
document.getElementById("button0").addEventListener("click", function() {
    hideAllProducts();
    document.getElementById("allProducts").style.display = "block";
});

// Event listener for the "Nails" button
document.getElementById("button1").addEventListener("click", function() {
    hideAllProducts();
    document.getElementById("nailProducts").style.display = "block";
});

// Event listener for the "Cement" button
document.getElementById("button2").addEventListener("click", function() {
    hideAllProducts();
    document.getElementById("cementProducts").style.display = "block";
});

// Event listener for the "Roofsheet" button
document.getElementById("button3").addEventListener("click", function() {
    hideAllProducts();
    document.getElementById("roofsheetProducts").style.display = "block";
});
document.getElementById("button4").addEventListener("click", function() {
    hideAllProducts();
    document.getElementById("paintProducts").style.display = "block";
});
document.getElementById("button5").addEventListener("click", function() {
    hideAllProducts();
    document.getElementById("plywoodProducts").style.display = "block";
});
document.getElementById("button6").addEventListener("click", function() {
    hideAllProducts();
    document.getElementById("steelbarProducts").style.display = "block";
});


// Function to hide all product categories
function hideAllProducts() {
    var allProducts = document.getElementById("allProducts");
    var nailProducts = document.getElementById("nailProducts");
    var cementProducts = document.getElementById("cementProducts");
    var roofsheetProducts = document.getElementById("roofsheetProducts");
    var paintProducts = document.getElementById("paintProducts");
    var plywoodProducts= document.getElementById("plywoodProducts");
    var steelbarProducts= document.getElementById("steelbarProducts");

    allProducts.style.display = "none";
    nailProducts.style.display = "none";
    cementProducts.style.display = "none";
    roofsheetProducts.style.display = "none";
    paintProducts.style.display = "none";
    plywoodProducts.style.display = "none";
    steelbarProducts.style.display = "none";
}


     //code to show the list of other products
    document.getElementById("button7").addEventListener("click", function() {
        
        const categoryList = document.querySelector('.category-list');
        categoryList.innerHTML = '';


        var table = document.createElement('table');
        table.classList.add('table');

        var thead = table.createTHead();
        var headerRow = thead.insertRow();
        var headers = ['Type', 'Brand', 'Description','Price'];
        headers.forEach(function(headerText, index) {
            var th = document.createElement('th');
            th.textContent = headerText;
            if (index === headers.length - 1) {
                th.setAttribute('colspan', '2'); 
            }
            headerRow.appendChild(th);
        });

        var  otherProducts = [
            ['Hammer', '?', '?','₱ 50'],
            ['Pliers', '?', 'Set','₱ 570'],
            ['Drivers', '?', 'Set','₱ 590'],
            ['Grinders', '?', 'Set','₱ 790'],
        ];

        otherProducts.forEach(function(productData) {
            addProductToTable(table, ...productData);
        });

        categoryList.appendChild(table);
    });

    function addToPendingPurchases(type, brand, inches, price) {
        console.log('Adding to pending purchases:', type, brand, inches, price);
    
        var productDiv = document.createElement('div');
        productDiv.textContent = type + ' ' + brand + ' ' + inches + ' ' + price;
    
        var pendingContainer = document.querySelector('.pending-purchases');
        console.log('Pending container:', pendingContainer);
        if (pendingContainer) {
            pendingContainer.appendChild(productDiv);
        } else {
            console.error('Pending purchases container not found.');
        }
    }
    
    //sa category lists, each list may button, then if na trigger un itong modal lalabas
    //it handles the quantity
    function showModal(type, brand, description, price, unit) {
        var modal = document.getElementById("myModal");
        var span = document.getElementsByClassName("close")[0];
        var saveBtn = document.getElementById("saveBtn");
        var inputField = document.getElementById("additionalInfo");
        var productInfo = document.getElementById("productInfo");
    
      
        productInfo.textContent = `${type} ${brand} ${description} ${price}`;
     
        inputField.value = '';
    
        modal.style.display = "block";
    
        span.onclick = function() {
            modal.style.display = "none";
        };

        saveBtn.onclick = function() {
            modal.style.display = "none";
            var quantity = parseFloat(inputField.value);
            if (!isNaN(quantity) && quantity > 0) {
                var totalPrice = quantity * parseFloat(price.replace('₱ ', ''));
                addToPendingPurchases(type, brand, description, quantity, totalPrice.toFixed(2));
            } else {
                alert('Please enter a valid quantity.');
            }
        };
    
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };
    }
    
    //function append the the updated lists of the product. included na dgdi su quantity after ma click ung save button
    function addToPendingPurchases(type, brand, description, quantity, totalPrice) {
        console.log('Adding to pending purchases:', type, brand, description, quantity, totalPrice);

        var row = document.createElement('tr');

        var typeCell = document.createElement('td');
        typeCell.textContent = type;
        row.appendChild(typeCell);

        var brandCell = document.createElement('td');
        brandCell.textContent = brand;
        row.appendChild(brandCell);

        var descriptionCell = document.createElement('td');
        descriptionCell.textContent = description;
        row.appendChild(descriptionCell);

        var quantityCell = document.createElement('td');
        quantityCell.textContent = quantity + ' kilos';
        row.appendChild(quantityCell);

        var priceCell = document.createElement('td');
        priceCell.textContent = '₱ ' + totalPrice;
        row.appendChild(priceCell);

        var tbody = document.querySelector('.pending-purchases tbody');
        if (tbody) {
            tbody.appendChild(row);
        } else {
            console.error('Table body not found.');
        }

        calculateTotalAmount();
    }
    //function to show the total amount
    function updateTotalAmount(amount) {
        var totalAmountElement = document.getElementById('totalAmount');
        if (totalAmountElement) {
            totalAmountElement.textContent = ' ₱ ' + amount.toFixed(2);
        } else {
            console.error('Total amount element not found.');
        }
    }
    //function to calculate the total amount of all the products in the pending purchases       
    function calculateTotalAmount() {
        var rows = document.querySelectorAll('.pending-purchases tbody tr');
        var totalAmount = 0;

        rows.forEach(function(row) {
            var priceCell = row.querySelector('td:last-child');
            var price = parseFloat(priceCell.textContent.replace('₱ ', '').trim());
            totalAmount += price;
        });
        updateTotalAmount(totalAmount);
    }
    
    //function to show the modals for calculation, this modal gets triggered when the button proceed is clicked
    function showProceedModal() {
    var modal = document.getElementById("proceedModal");
    var span = modal.querySelector(".close");
    var totalAmountModal = modal.querySelector("#totalAmountModal");
    var totalAmount = document.getElementById('totalAmount').textContent.trim(); 

    var listsAmountContainer = modal.querySelector('.lists-amount');


    totalAmountModal.textContent = totalAmount;

    var rows = document.querySelectorAll('.pending-purchases tbody tr');
    rows.forEach(function(row) {
        var type = row.querySelector('td:nth-child(1)').textContent;
        var brand = row.querySelector('td:nth-child(2)').textContent;
        var description = row.querySelector('td:nth-child(3)').textContent;
        var quantity = row.querySelector('td:nth-child(4)').textContent;
        var price = row.querySelector('td:nth-child(5)').textContent;
        var listItem = document.createElement('tr'); 
       
        var typeCell = document.createElement('td');
        typeCell.textContent = type;
        var brandCell = document.createElement('td');
        brandCell.textContent = brand;
        var descriptionCell = document.createElement('td');
        descriptionCell.textContent = description;
        var quantityCell = document.createElement('td');
        quantityCell.textContent = quantity;
        var priceCell = document.createElement('td');
        priceCell.textContent = price;
       
        listItem.appendChild(typeCell);
        listItem.appendChild(brandCell);
        listItem.appendChild(descriptionCell);
        listItem.appendChild(quantityCell);
        listItem.appendChild(priceCell);
        
        listsAmountContainer.querySelector('tbody').appendChild(listItem);
    });
    

    modal.style.display = "block";

    span.onclick = function() {
        modal.style.display = "none";
    }
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    }
    document.querySelector('.proceed-btn').addEventListener('click', function() {
        showProceedModal();
        
    });

    var input = document.getElementById("customer-money");


    input.addEventListener("input", function() {
       
        var inputValue = input.value;

        // Remove any non-numeric characters
        var numericValue = inputValue.replace(/[^0-9]/g, '');
        input.value = numericValue;
    });
    
    document.querySelector('.cancel-btn').addEventListener('click', function() {

        const pendingList = document.querySelector('.pending-purchases tbody');
        if (pendingList) {
            pendingList.innerHTML = '';
            // Reset the total amount
            updateTotalAmount(0);
        } else {
            console.error('Pending purchases list not found.');
        }
    });
   
    //function to calculate the change
    function calculateChange() {
        // Get the total amount and customer-entered amount
        var totalAmountText = document.getElementById("totalAmountModal").textContent.trim();
        var customerAmountText = document.getElementById("customer-money").value.trim();
        var totalAmountNumeric = parseFloat(totalAmountText.split(" ")[1]); 
    
        var totalAmount = totalAmountNumeric;
        var customerAmount = parseFloat(customerAmountText);
    
        if (isNaN(totalAmount) || isNaN(customerAmount)) {
            alert("Please enter valid amounts.");
            return;
        }
    
        if (customerAmount < totalAmount) {
            alert("Insufficient amount. Please enter a sufficient amount to proceed.");
            return;
        }
    
        var change = customerAmount - totalAmount;
    
        var changeContainer = document.getElementById("changeContainer");
        changeContainer.textContent = "Change: ₱" + change.toFixed(2);
    
        // Append the amount received and change to the receipt modal
        var amountReceivedReceipt = document.querySelector('.amount-received-receipt');
        amountReceivedReceipt.textContent = `Amount Received: ₱${customerAmount.toFixed(2)}`;
    
        var changeReceipt = document.querySelector('.change-receipt');
        changeReceipt.textContent = `Change: ₱${change.toFixed(2)}`;
    }
    
    // Add event listener to the "Done" button
    
    
    // Add event listener to calculate change when the "Change" button is clicked
    document.getElementById("payButton").addEventListener("click", calculateChange);
    

    // Add event listener to the "Done" button
    document.querySelector('.done-btn').addEventListener('click', function() {
        var transactionId = generateTransactionId();
        // Call the function to show the receipt modal
        showReceiptModal(transactionId);
        
    });

    //fucntion to show the receipt modal, this modal gets triggered when the done button is clicked
    function showReceiptModal() {
        var receiptModal = document.getElementById('receiptModal');
        receiptModal.style.display = 'block';

        var closeButton = receiptModal.querySelector('.close');
        closeButton.addEventListener('click', function() {
          
            receiptModal.style.display = 'none';
        });

        fillReceiptDetails();
    }
    //details of the receipt
    function fillReceiptDetails() {

        var receiptDetails = document.querySelector('.receipt-details');
        var receiptTableBody = receiptDetails.querySelector('.receipt-table tbody');
        var rows = document.querySelectorAll('.pending-purchases tbody tr');

        rows.forEach(function(row) {
            var type = row.querySelector('td:nth-child(1)').textContent;
            var brand = row.querySelector('td:nth-child(2)').textContent;
            var description = row.querySelector('td:nth-child(3)').textContent;
            var quantity = row.querySelector('td:nth-child(4)').textContent;
            var price = row.querySelector('td:nth-child(5)').textContent;

            var receiptRow = document.createElement('tr');
            receiptRow.innerHTML = `
                <td>${type}</td>
                <td>${brand}</td>
                <td>${description}</td>
                <td>${quantity}</td>
                <td>${price}</td>
            `;

            
            receiptTableBody.appendChild(receiptRow);
        });


        var totalAmountText = document.getElementById("totalAmountModal").textContent.trim();
        var totalAmountNumeric = parseFloat(totalAmountText.split(" ")[1]);
        var totalAmountReceipt = document.querySelector('.total-amount-receipt');
        totalAmountReceipt.textContent = `Total Amount: ₱${totalAmountNumeric.toFixed(2)}`;

      
        var transactionId = generateTransactionId();
        var transactionIdElement = document.createElement('p');
        receiptDetails.appendChild(transactionIdElement);

        var transactionIdReceipt = document.querySelector('.transaction-id');
        transactionIdReceipt.textContent = `Transaction ID: ${transactionId}`;
    }

    document.querySelector('.new-btn').addEventListener('click', function() {
       
        const categoryList = document.querySelector('.category-list');
        categoryList.innerHTML = '';
    
        
        const pendingList = document.querySelector('.pending-purchases tbody');
        if (pendingList) {
            pendingList.innerHTML = '';
        }
    
        updateTotalAmount(0);
        closeModals();
    });
    
    function closeModals() {

        var modal = document.getElementById("myModal");
        modal.style.display = "none";
    
      
        var proceedModal = document.getElementById("proceedModal");
        proceedModal.style.display = "none";
    
        var receiptModal = document.getElementById('receiptModal');
        receiptModal.style.display = 'none';
    }
    document.querySelector('.print-btn').addEventListener('click', function() {
        printReceipt();
    });
    
    function printReceipt() {
       
        var receiptContainer = document.querySelector('.receipt-container');
        var clonedContainer = receiptContainer.cloneNode(true);
        var printButton = clonedContainer.querySelector('.print-btn');
        if (printButton) {
            printButton.remove();
        }
    
        var newOrderButton = clonedContainer.querySelector('.new-btn');
        if (newOrderButton) {
            newOrderButton.remove();
        }
    
       
        var printWindow = window.open('', '_blank');
    
        
        printWindow.document.write(clonedContainer.innerHTML);   
        printWindow.print();
        printWindow.close();
    }
    
    function generateTransactionId() {

        var transactionId = Math.floor(100000 + Math.random() * 900000);
        return transactionId;
    }

});


document.addEventListener("DOMContentLoaded", function() {
    const buttons = document.querySelectorAll('.add-to-pending'); // Corrected class name

    // Event listener for each button
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            // Your button click logic here
            showModal(/* pass parameters if needed */);
        });
    });

    // Other code goes here...
});