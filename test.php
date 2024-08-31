<?php
    session_start(); 

    if (!isset($_SESSION['user_id'])) {
        header("Location: LoginFinal.php");
        exit();
    }

    include 'Database.php';

    if (mysqli_connect_errno()) {
        die("Failed to connect to MySQL: " . mysqli_connect_error());
    }

    $sql_select = "SELECT id, product_name, brand, description, product_price, stocks FROM inventory";

    $result = mysqli_query($con, $sql_select);

    if ($result) {
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $id = $row['id'];
                $productName = $row['product_name'];
                $brand = $row['brand'];
                $description = $row['description'];
                $price = $row['product_price'];
                $stocks = $row['stocks'];
            }
        } else {
            echo "No data found in the inventory table";
        }
    } else {
        echo "Error: " . mysqli_error($con);
    }

    $totalAmountModal = isset($_POST['total_amount']) ? (float)str_replace(',', '', $_POST['total_amount']) : '';
    $amountReceived = isset($_POST['amount_received']) ? (float)str_replace(',', '', $_POST['amount_received']) : '';
    $changeAmount = isset($_POST['change_amount']) ? (float)str_replace(',', '', $_POST['change_amount']) : '';
    $transactionId = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
    
    if (!empty($totalAmountModal) && !empty($amountReceived) && !empty($changeAmount) && !empty($transactionId)) {
        $sql = "INSERT INTO transactions_history (transaction_id, total_amount, amount_received, change_amount)
                VALUES ('$transactionId', '$totalAmountModal', '$amountReceived', '$changeAmount')";
    
        if ($con->query($sql) === TRUE) {
            $sql_update_ids = "SET @num := 0;
                               UPDATE transactions_history SET id = @num := (@num+1);
                               ALTER TABLE transactions_history AUTO_INCREMENT = 1;";
            $result_update_ids = mysqli_multi_query($con, $sql_update_ids);
            echo "Transaction data saved successfully.";
        } else {

            echo "Error: " . $sql . "<br>" . $con->error;
        }
    }
    


// Deduction
if (isset($_POST['pendingPurchasesData'])) {
    $pendingPurchasesData = json_decode($_POST['pendingPurchasesData'], true);

    mysqli_begin_transaction($con);

    $success = true;

    try {
        // Loop through each pending purchase and update stock in inventory
        foreach ($pendingPurchasesData as $purchase) {
            $purchaseId = $purchase['id'];
            $purchaseQuantity = $purchase['quantity'];
        
            // Get current stock value
            $stmt = mysqli_prepare($con, "SELECT stocks FROM inventory WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $purchaseId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $currentStock = $row['stocks'];

            $newStock = $currentStock - $purchaseQuantity;
        
            // Update stock in inventory
            $stmt = mysqli_prepare($con, "UPDATE inventory SET stocks = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $newStock, $purchaseId);
            mysqli_stmt_execute($stmt);
        
            // Fetch existing product_sold value
            $stmt = mysqli_prepare($con, "SELECT product_sold FROM sales WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $purchaseId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $existingProductSold = $row['product_sold'];

            $newProductSold = $existingProductSold + $purchaseQuantity;
        
            // Update product_sold in sales table
            $stmt = mysqli_prepare($con, "UPDATE sales SET product_sold = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "di", $newProductSold, $purchaseId); 
            mysqli_stmt_execute($stmt);
        }

        mysqli_commit($con);
    } catch (Exception $e) {
        mysqli_rollback($con);
        $success = false;
    }
}
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <link rel="stylesheet" href="css/test.css">
        <link rel="stylesheet" href="css/test-pos.css">
        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.3/css/all.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

        <style>
            
        </style>

    
    </head>
    <body>

        <div class="main-container d-flex">
            <div class="sidebar" id="side_nav">
                <div class="header-box px-2 pt-3 pb-4 d-flex justify-content-between">
                    <img class="logo" src="images/Logo.png" alt="">
                    <button class="btn d-md-none d-block close-btn px-1 py-0 text-white"><i class="fal fa-stream"></i></button>
                </div>

                <ul class="list-unstyled px-2">
                    <li class="active"><a  class="text-decoration-none px-3 py-2 d-block"><i class="fal fa-cash-register" id="custom-icon"></i> POS</a></li>
                </ul>   
                <button class="Logout" id="Logout-btn"><i class="fas fa-sign-out-alt" id="custom-icon"></i>Logout</button>        <form action="logout.php" method="post">
                    <button class="Logout" id="Logout-btn" type="submit" name="logout"><i class="fas fa-sign-out-alt" id="custom-icon"></i>Logout</button>
                </form>
            </div>

            <div class="content">
                <div class="container-header">
                    <h2 class="pos-text">POS</h2>
                    <form id="searchForm" action="test.php" method="GET">
                        <input autocomplete="off" type="text" name="search" class="search" placeholder="Search Products" aria-label="Search Products" aria-describedby="button-addon2" id="searchInput">
                    </form>

                </div>
                <div class="POS-content">
                    <div class="category-buttons">
                        <button class="button" id="button0"> All</button>
                        <button class="button" id="button1"> Nails </button>
                        <button class="button" id="button2"> Cements </button>
                        <button class="button" id="button3"> Roofing Sheets </button>
                        <button class="button" id="button4"> Paint </button>
                        <button class="button" id="button5"> Plywood </button>
                        <button class="button" id="button6"> Steelbars </button>
                    </div>
                    <div class="category-list-container">
                        <!-- All -->
                        <div class="category-list1" id="allProducts">
                        <?php
                            $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

                            $sql_select = "SELECT id, product_name, brand, description, product_price, stocks FROM inventory";
                            if (!empty($searchTerm)) {
                                $searchTerm = mysqli_real_escape_string($con, $searchTerm);
                                $sql_select .= " WHERE product_name LIKE '%$searchTerm%' OR brand LIKE '%$searchTerm%' OR description LIKE '%$searchTerm%'";
                            }

                            $result = mysqli_query($con, $sql_select);

                            $tableClass = "table";

                            if ($result && mysqli_num_rows($result) > 0) {

                                echo "<table class=\"$tableClass\">";
                                echo '<thead class="thead"><tr><th scope="col">Product Name</th><th scope="col">Brand</th><th scope="col">Description</th><th scope="col">Price</th><th scope="col"></th></tr></thead>';
                                echo '<tbody>';

                                while ($row = mysqli_fetch_assoc($result)) {
                                    $id = $row['id'];
                                    $productName = $row['product_name'];
                                    $brand = $row['brand'];
                                    $description = $row['description'];
                                    $stocks = $row['stocks'];
                                    $price = $row['product_price'];

                                    echo "<tr class=\"ProductList1\"><td>$productName</td><td>$brand</td><td>$description</td><td>$price</td><td><button class='add-to-pending' onclick='addToPending(\"$id\",\"$productName\", \"$brand\", \"$description\", \"$stocks\", \"$price\")'>+</button></td></tr>";
                                }

                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "<table class=\"$tableClass\">";
                                echo '<thead class="thead2"><tr><th colspan="5">No products found based on your search term.</th></tr></thead>';
                                echo '</table>';
                            }
                            ?>


                    </div>

                    <!-- Nail -->
                        <div class="category-list2" id="nailProducts"> 
                            <?php
                            $sql_select = "SELECT id, product_name, brand, description, stocks, product_price FROM inventory WHERE product_name LIKE '%Nail%'";

                            $result = mysqli_query($con, $sql_select);

                            if ($result && mysqli_num_rows($result) > 0) {
                                echo '<table class="table">';
                                echo '<thead class="thead"><tr><th scope="col">Product Name</th><th scope="col">Brand</th><th scope="col">Description</th><th scope="col">Price</th><th scope="col"></th></tr></thead>';
                                echo '<tbody>';

                                while($row = mysqli_fetch_assoc($result)) {
                                    $id = $row['id'];
                                    $productName = $row['product_name'];
                                    $brand = $row['brand'];
                                    $description = $row['description'];
                                    $stocks = $row['stocks'];
                                    $price = $row['product_price'];

                                    echo "<tr><td>$productName</td><td>$brand</td><td>$description</td><td>$price</td><td><button class='add-to-pending' onclick='addToPending(\"$id\",\"$productName\", \"$brand\", \"$description\", \"$stocks\", \"$price\")'>+</button></td></tr>";
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "No nail products found in the inventory.";
                            }
                            ?>
                        </div>
                        <!-- Cement -->
                        <div class="category-list3" id="cementProducts"> 
                            <?php
                            $sql_select = "SELECT id, product_name, brand, description, stocks, product_price FROM inventory WHERE product_name LIKE '%Cement%'";

                            $result = mysqli_query($con, $sql_select);

                            if ($result && mysqli_num_rows($result) > 0) {
                                echo '<table class="table">';
                                echo '<thead class="thead"><tr><th scope="col">Product Name</th><th scope="col">Brand</th><th scope="col">Description</th><th scope="col">Price</th><th scope="col"></th></tr></thead>';
                                echo '<tbody>';

                                while($row = mysqli_fetch_assoc($result)) {
                                    $id = $row['id'];
                                    $productName = $row['product_name'];
                                    $brand = $row['brand'];
                                    $description = $row['description'];
                                    $stocks = $row['stocks'];

                                    echo "<tr><td>$productName</td><td>$brand</td><td>$description</td><td>$price</td><td><button class='add-to-pending' onclick='addToPending(\"$id\",\"$productName\", \"$brand\", \"$description\", \"$stocks\", \"$price\")'>+</button></td></tr>";
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "No nail products found in the inventory.";
                            }
                            ?>
                        </div>
                        <!-- roof sheet-->
                        <div class="category-list4" id="roofsheetProducts"> 
                            <?php
                            $sql_select = "SELECT id, product_name, brand, description, stocks, product_price FROM inventory WHERE product_name LIKE '%Yero%'";

                            $result = mysqli_query($con, $sql_select);

                            if ($result && mysqli_num_rows($result) > 0) {
                                echo '<table class="table">';
                                echo '<thead class="thead"><tr><th scope="col">Product Name</th><th scope="col">Brand</th><th scope="col">Description</th><th scope="col">Price</th><th scope="col"></th></tr></thead>';
                                echo '<tbody>';

                                while($row = mysqli_fetch_assoc($result)) {
                                    $id = $row['id'];
                                    $productName = $row['product_name'];
                                    $brand = $row['brand'];
                                    $description = $row['description'];
                                    $stocks = $row['stocks'];
                                    $price = $row['product_price'];

                                    echo "<tr><td>$productName</td><td>$brand</td><td>$description</td><td>$price</td><td><button class='add-to-pending' onclick='addToPending(\"$id\",\"$productName\", \"$brand\", \"$description\", \"$stocks\", \"$price\")'>+</button></td></tr>";
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "No nail products found in the inventory.";
                            }
                            ?>
                        </div>
                        <!-- Paint-->
                        <div class="category-list5" id="paintProducts"> 
                            <?php
                            $sql_select = "SELECT id, product_name, brand, description, stocks, product_price FROM inventory WHERE product_name LIKE '%Paint%'";

                            $result = mysqli_query($con, $sql_select);

                            if ($result && mysqli_num_rows($result) > 0) {
                                echo '<table class="table">';
                                echo '<thead class="thead"><tr><th scope="col">Product Name</th><th scope="col">Brand</th><th scope="col">Description</th><th scope="col">Price</th><th scope="col"></th></tr></thead>';
                                echo '<tbody>';

                                while($row = mysqli_fetch_assoc($result)) {
                                    $id = $row['id'];
                                    $productName = $row['product_name'];
                                    $brand = $row['brand'];
                                    $description = $row['description'];
                                    $stocks = $row['stocks'];
                                    $price = $row['product_price'];

                                    echo "<tr><td>$productName</td><td>$brand</td><td>$description</td><td>$price</td><td><button class='add-to-pending' onclick='addToPending(\"$id\",\"$productName\", \"$brand\", \"$description\", \"$stocks\", \"$price\")'>+</button></td></tr>";
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "No nail products found in the inventory.";
                            }
                            ?>
                        </div>

                        <!--Ply wood-->
                        <div class="category-list6" id="plywoodProducts"> 
                            <?php
                            $sql_select = "SELECT id, product_name, brand, description, stocks, product_price FROM inventory WHERE product_name LIKE '%Plywood%'";

                            $result = mysqli_query($con, $sql_select);

                            if ($result && mysqli_num_rows($result) > 0) {

                                echo '<table class="table">';
                                echo '<thead class="thead"><tr><th scope="col">Product Name</th><th scope="col">Brand</th><th scope="col">Description</th><th scope="col">Price</th><th scope="col"></th></tr></thead>';
                                echo '<tbody>';

                                while($row = mysqli_fetch_assoc($result)) {
                                    $id = $row['id'];
                                    $productName = $row['product_name'];
                                    $brand = $row['brand'];
                                    $description = $row['description'];
                                    $stocks = $row['stocks'];
                                    $price = $row['product_price'];

                                    echo "<tr><td>$productName</td><td>$brand</td><td>$description</td><td>$price</td><td><button class='add-to-pending' onclick='addToPending(\"$id\",\"$productName\", \"$brand\", \"$description\", \"$stocks\", \"$price\")'>+</button></td></tr>";
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "No nail products found in the inventory.";
                            }
                            ?>
                        </div>

                        <!--steelbar-->
                        <div class="category-list7" id="steelbarProducts"> 
                            <?php
                            $sql_select = "SELECT id, product_name, brand, description, stocks, product_price FROM inventory WHERE product_name LIKE '%Steelbar%'";

                            $result = mysqli_query($con, $sql_select);

                            if ($result && mysqli_num_rows($result) > 0) {
                                echo '<table class="table">';
                                echo '<thead class="thead"><tr><th scope="col">Product Name</th><th scope="col">Brand</th><th scope="col">Description</th><th scope="col">Price</th><th scope="col"></th></tr></thead>';
                                echo '<tbody>';

                                while($row = mysqli_fetch_assoc($result)) {
                                    $id = $row['id'];
                                    $productName = $row['product_name'];
                                    $brand = $row['brand'];
                                    $description = $row['description'];
                                    $stocks = $row['stocks'];
                                    $price = $row['product_price'];
                                    echo "<tr><td>$productName</td><td>$brand</td><td>$description</td><td>$price</td><td><button class='add-to-pending' onclick='addToPending(\"$id\",\"$productName\", \"$brand\", \"$description\", \"$stocks\", \"$price\")'>+</button></td></tr>";
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                            } else {
                                echo "No nail products found in the inventory.";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="pending-purchases-content container-xl">
                        <div class="pending-purchases">
                            <h2 class="pending-text-header">Pending Purchases</h2>
                            <table class="pending-table">
                                <thead>
                                    <tr><th>ID</th>
                                        <th>Product</th>
                                        <th>Brand</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total Price</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody> 
                                </tbody>
                            </table>
                            
                        </div>
                        <div class="total-amount">
                            <h3 class="total-header">Total Amount:</h3>
                            <div id="totalAmount">0.00</div>
                            <button class="cancel-btn">Cancel</button>
                            <button class="proceed-btn" id="proceedButton">Proceed</button>
                        </div>
                    </div>
                </div>
                <!-- The Modal -->
                    <div id="myModal" class="modal">
                        <div class="modal-content">
                            <span class="close" id="CLOSE">&times;</span>
                            <p id="productInfo"></p>
                            <!-- Add an input field and a save button -->
                            <div class="input-container">
                                <input type="number" id="additionalInfo" placeholder="Enter the quantity" required>
                                <button class="save-btn" id="saveBtn">Save</button>
                            </div>
                        </div>
                    </div>
                <div id="proceedModal" class="modal">
                    <span class="close">&times;</span>
                    <div class="amount-payable">   
                        <div class="lists-amount">
                            <p class="total-amount-text" required>Total Amount: <span id="totalAmountModal"></span></p>
                            <table class="pending-table">
                                <thead>
                                    <tr><th>ID</th>
                                        <th>Product</th>
                                        <th>Brand</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total Price</th>
                                    </tr>
                                </thead>
                                <tbody> 
                                </tbody>
                            </table>
                        </div>
                        <div class="customer-money-container" onclick="closeProceedModal()">      
                            <label for="customer-money" class="customer-money-label">Amount Received</label>
                            <div class="input-button-container">
                                <input id="customer-money" class="customer-money" type="text">
                                <button id="payButton">Change</button>
                            </div>
                            <p id="changeContainer"></p>
                        </div>
                        <button class="done-btn" id="done-btn">Done</button>
                    </div>
                </div>
                <div id="receiptModal" class="modal">
                    <span class="close">&times;</span>
                    <div class="receipt-container">
                        <div class="receipt-details">
                            <h2 class="receipt-header">Receipt</h2>
                            <h4 class="store-name">Bañas Hardware Store</h4>
                            <h4 class="store-address">Cabasan, Bacacay, Albay</h4>
                            <table class="receipt-table">
                                <thead>
                                    <tr>
                                        <th>id</th>
                                        <th>Product</th>
                                        <th>Brand</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>    
                                </tbody>
                            </table>
                            <h3 class="total-amount-receipt">Total Amount: </h3>
                            <h3 class="amount-received-receipt">Amount Received: </h3>
                            <h3 class="change-receipt">Change: </h3>
                            <h3 class="transaction-id">Transaction ID: </h3>
                        </div>
                        <button class="print-btn">Print</button>
                        <button class="new-btn">New Order</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/POS.js"></script>


    </body>
    </html>