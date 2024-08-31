<?php

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== '627-999-726') {
    header("Location: LoginFinal.php");
    exit();
}

include 'Database.php';

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Function to sanitize user input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = sanitize_input($_POST["Clerk-name"]);
    $user_id = sanitize_input($_POST["user-id"]);
    $pin = sanitize_input($_POST["pin"]);

    // Prepare and bind SQL statement
    $stmt = $con->prepare("INSERT INTO users (name, user_id, pin) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $user_id, $pin); 

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!');</script>";
        header("Location: Dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch transaction history with search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT transaction_id, total_amount, amount_received, change_amount, transaction_date FROM transactions_history WHERE transaction_id LIKE '%$search%'";
$result = $con->query($sql);


// Calculate total revenue
$sql_total_revenue = "SELECT SUM(total_amount) AS total_revenue FROM transactions_history WHERE WEEK(transaction_date) = WEEK(CURRENT_DATE())";
$result_total_revenue = $con->query($sql_total_revenue);
$row_total_revenue = $result_total_revenue->fetch_assoc();
$total_revenue = $row_total_revenue['total_revenue'];

// Calculate total transactions
$sql_total_transactions = "SELECT COUNT(id) AS total_transactions FROM transactions_history WHERE WEEK(transaction_date) = WEEK(CURRENT_DATE())";
$result_total_transactions = $con->query($sql_total_transactions);
$row_total_transactions = $result_total_transactions->fetch_assoc();
$total_transactions = $row_total_transactions['total_transactions'];






// Close connection
$con->close();

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/Sidebar.css">
    <link rel="stylesheet" href="css/RegisterForm.css">
    <link rel="stylesheet" href="css/Dashboard.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.3/css/all.css">
</head>
<body>


<div class="main-container d-flex">
    <div class="sidebar" id="side_nav">
        <div class="header-box px-2 pt-3 pb-4 d-flex justify-content-between">
            <img class="logo" src="images/Logo.png" alt="">
            <button class="btn d-md-none d-block close-btn px-1 py-0 text-white"><i class="fal fa-stream"></i></button>
        </div>

        <ul class="list-unstyled px-2">
            <li class="active"><a href="Dashboard.php" class="text-decoration-none px-3 py-2 d-block"><i class="fal fa-home" id="custom-icon"></i> Dashboard</a></li>
            <li class=""><a href="Sales.php" class="text-decoration-none px-3 py-2 d-block"><i class="far fa-chart-line" id="custom-icon"></i> Sales Report</a></li>
            <li class=""><a href="Inventory.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-archive" id="custom-icon"></i> Inventory</a></li>
        </ul>   
        <button class="Register" id="Register-btn"><i class="fas fa-user-plus" id="custom-icon"></i>Register</button>     
        <form action="logout.php" method="post">
            <button class="Logout" id="Logout-btn" type="submit" name="logout"><i class="fas fa-sign-out-alt" id="custom-icon"></i>Logout</button>
        </form>



    </div>

    <div class="content">
        <nav class="navbar navbar-expand-md navbar-light bg-light">
            <div class="container-fluid">
                <div class="d-flex justify-content-between d-md-none d-block">
                    <button class="btn px-1 py-0 open-btn me-2"><i class="fal fa-stream"></i></button>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="dashboard-content">
            <div class="card-containers">
            <div class="total-revenue">
                <h3 class="Toatalrevenueweek">₱ <?php echo number_format($total_revenue, 2); ?></h3>
                <h4 class="revenue-text">Weekly Revenue</h4>
                <img id="image" class="revenue-img" src="images/revenue.png" alt="">
            </div>

            <div class="total-transactions">
                <h3 class="ToatalTranweek"><?php echo $total_transactions; ?></h3>
                <h4 class="transactions-text">Weekly Transactions</h4>
                <img id="image" class="transactions-img" src="images/transactions.png" alt="">
            </div>
            <div class="store-updates container-xl">
            <h2 class="updates-header">Last Week Inventory</h2>
            <div class="table-container">
                <table class="table" id="historyTable">
                    <thead class="transHead2">
                        <tr>
                            <th scope="col">Product Name</th>
                            <th scope="col">Brand</th>
                            <th scope="col">Description</th>
                            <th scope="col">Price</th>
                            <th scope="col">Stocks</th>
                            <th scope="col">Date</th>
                        </tr>
                    </thead>
                    <tbody class="tdbody2">
                        <!-- Data will be appended here via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
            </div>
           
            <div class="sales-analytics">
                <div class="graph-box">
                    <h2 class="sales-header">Sales Analytics</h2>

                    <canvas id="myChart"></canvas>      
                </div>

                <div class="transaction-history container-xl">
                    <h2 class="transaction-header">Transaction History</h2>
                    <form action="" method="get" id="searchForm">
                    <input class="search-input" autocomplete="off" name="search" type="text" id="searchInput" placeholder=" Search Transaction No." value="">
                    </form>
                    <table class="table2" id="transactionTable">
                        <thead class="transHead" id="head">
                            <tr >
                                <th scope="col">Transaction ID</th>
                                <th scope="col">Total Amount</th>
                                <th scope="col">Amount Received</th>
                                <th scope="col">Change Amount</th>
                                <th scope="col">Transaction Date</th>
                            </tr>
                        </thead>
                        <tbody class="tablebody">
                        <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='trdisplay' onclick='restartPage()'>";
                                    echo "<td scope='row' id='tid'>" . $row["transaction_id"] . "</td>";
                                    echo "<td scope='row'>" . number_format($row["total_amount"], 2) . "</td>"; // Format total amount
                                    echo "<td scope='row'>" . number_format($row["amount_received"], 2) . "</td>"; // Format amount received
                                    echo "<td scope='row'>" . number_format($row["change_amount"], 2) . "</td>"; // Format change amount
                                    echo "<td scope='row'>" . (new DateTime($row["transaction_date"]))->format('F j, Y g:ia') . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr class='erorrwarn' onclick='restartPage()'><td colspan='5'>No transactions found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
            
            
        </div>

        <!-- register clerk form -->
        
                    <div class="container" id="container">
                        <button type="button" class="btn-close" id="closebtnAdmin" aria-label="Close"></button>
                        <img class="Logo" src="images/Logo.png" alt="">
                        <div class="register-form">
                            <img src="" alt="">
                            <h3 class="Rg">Register Clerk</h3>
                            <form method="post" action="Dashboard.php" onsubmit="return validatePin()">
                                <input autocomplete="off" id="ip1" class="ip1" type="text" placeholder="Name" name="Clerk-name" required>
                                <input autocomplete="off" pattern="\d{3}-\d{3}-\d{3}" id="ip2" class="ip2" type="text" placeholder="Clerk-Id (000-000-000)" name="user-id" required>
                                <div class="password-toggle">
                                    <input id="ip3" class="ip3 password-field" type="password" placeholder="Pin" name="pin" required>
                                    <span class="toggle-icon" onclick="togglePasswordVisibility('ip3', 'ip4', this)">
                                        <i class="fas fa-eye-slash"></i>
                                    </span>
                                </div>
                                <div class="password-toggle">
                                    <input id="ip4" class="ip4 password-field" type="password" placeholder="Confirm Pin" name="confirm_password" required>
                                    <span class="toggle-icon" onclick="togglePasswordVisibility('ip4', 'ip3', this)">
                                        <i class="fas fa-eye-slash"></i>
                                    </span>
                                </div>
                                <button type="submit" class="register" name="submit" onclick="register()">Register</button>
                            </form>
                        </div>
                    </div>
                    <!-- hanggang dgd and idv -->

            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="js/Slidebar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/Dashboard.js"></script>
</body>
</html>
