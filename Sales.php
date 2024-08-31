<?php
session_start(); 


if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== '627-999-726') {
    header("Location: LoginFinal.php");
    exit();
}


include 'Database.php';


if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}




// fucntion for search
$searchResult = null;

if(isset($_GET['searchSales']) && !empty($_GET['searchSales'])) {
    $search = $_GET['searchSales'];
    $sql = "SELECT * FROM `sales` WHERE `Product_name` LIKE '%$search%' OR `Description` LIKE '%$search%' OR `Brand` LIKE '%$search%'";
    $searchResult = mysqli_query($con, $sql);
    if(!$searchResult) {
        die("Error executing the query: " . mysqli_error($con));
    }
} else {
    $sql = "SELECT * FROM `sales` ORDER BY Product_name";
    $searchResult = mysqli_query($con, $sql);
    if(!$searchResult) {
        die("Error executing the query: " . mysqli_error($con));
    }
}

//insert
if (isset($_POST['restart'])) {
    // Check if today is Saturday
    if (date('N') == 6) {
        $currentMonth = date('m');
        $currentYear = date('Y');

        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

        $currentWeek = ceil(date('d') / 7); 

        if ($totalDaysInMonth > 28) {
            $currentWeek = ceil((date('d') + 1) / 7); 
        }

        if (date('j') == 1 && $currentWeek == 1) {
            // Delete all data for the current month from Month_WeeklySales table
            $deleteMonthQuery = "DELETE FROM Month_WeeklySales WHERE MONTH(date_column) = '$currentMonth' AND YEAR(date_column) = '$currentYear'";
            mysqli_query($con, $deleteMonthQuery);
        }

        // Check if the current week already exists in the Month_WeeklySales table
        $checkWeekQuery = "SELECT * FROM Month_WeeklySales WHERE week = '$currentWeek'";
        $checkWeekResult = mysqli_query($con, $checkWeekQuery);

        if (mysqli_num_rows($checkWeekResult) > 0) {
            echo "<script>alert('Week $currentWeek already exists in the database.');</script>";
        } else {
            // Calculate total products sold
            $totalProductSold = 0;
            $sql_total_products_sold = "SELECT SUM(Product_sold) AS total_product_sold FROM `sales`"; // Remove CAST from SUM function
            $result_total_products_sold = mysqli_query($con, $sql_total_products_sold);

            if ($result_total_products_sold && mysqli_num_rows($result_total_products_sold) > 0) {
                $row_total_products_sold = mysqli_fetch_assoc($result_total_products_sold);
                $totalProductSold = $row_total_products_sold['total_product_sold'];
                $totalProductSold = floatval($totalProductSold);
            } else {
                echo "ERROR: Unable to fetch total product sold.";
            }

            // Calculate total stocks
            $totalStocks = 0;
            $sql_total_stocks = "SELECT SUM(Stocks) AS total_stocks FROM `sales`";
            $result_total_stocks = mysqli_query($con, $sql_total_stocks);
            if ($result_total_stocks && mysqli_num_rows($result_total_stocks) > 0) {
                $row_total_stocks = mysqli_fetch_assoc($result_total_stocks);
                $totalStocks = $row_total_stocks['total_stocks'];
            }

            // Calculate sales percentage
            $productSalePercentage = ($totalProductSold / $totalStocks) * 100;

            // Insert data into Month_WeeklySales table
            $insertQuery = "INSERT INTO Month_WeeklySales (week, stocks, product_sold, product_sale_percentage) VALUES ('$currentWeek', '$totalStocks', '$totalProductSold', '$productSalePercentage')";
            if (mysqli_query($con, $insertQuery)) {
                $sql_truncate = "TRUNCATE TABLE sales";
                if (!mysqli_query($con, $sql_truncate)) {
                    die("Error truncating sales table: " . mysqli_error($con));
                }
                $sql_select = "SELECT id, Product_name, Brand, Description, Stocks FROM inventory";
                $result = mysqli_query($con, $sql_select);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $id = $row['id'];
                        $Product_name = $row['Product_name'];
                        $Brand = $row['Brand'];
                        $Description = $row['Description'];
                        $Stocks = $row['Stocks'];
                        $Date = date("Y-m-d");

                        $sql_insert = "INSERT INTO sales (id, Product_name, Brand, Description, Stocks, Date) 
                                      VALUES ('$id', '$Product_name', '$Brand', '$Description', '$Stocks', '$Date')";
                        $result_insert = mysqli_query($con, $sql_insert);

                        if (!$result_insert) {
                            die("Error: " . mysqli_error($con));
                        }
                    }
                        // Delete previous data from inventory_history table
                        $deleteHistoryQuery = "DELETE FROM inventory_history";
                        if (!mysqli_query($con, $deleteHistoryQuery)) {
                            die("Error deleting previous data from inventory_history table: " . mysqli_error($con));
                        }

                        // Insert data from inventory and sales into inventory_history table
                        $insertHistoryQuery = "INSERT INTO inventory_history (product_name, brand, description, product_price, date, stocks)
                                            SELECT i.product_name, i.brand, i.description, i.product_price, i.date, s.stocks
                                            FROM inventory i
                                            JOIN sales s ON i.id = s.id";

                        if (!mysqli_query($con, $insertHistoryQuery)) {
                            die("Error inserting data into inventory_history table: " . mysqli_error($con));
                        }

                        // Delete all data from transactions_history table
                        $deleteTransactionsQuery = "DELETE FROM transactions_history";
                        if (!mysqli_query($con, $deleteTransactionsQuery)) {
                            die("Error deleting data from transactions_history table: " . mysqli_error($con));
                        }

                    if (!mysqli_query($con, $insertHistoryQuery)) {
                        die("Error inserting data into inventory_history table: " . mysqli_error($con));
                    }
                } else {
                    echo '<script>alert("No data found in the inventory table");</script>';
                }
            } else {
                echo "ERROR: Could not execute $insertQuery. " . mysqli_error($con);
                echo "<script>setTimeout(function(){ window.location.href = 'Sales.php'; }, 2000);</script>";
            }
        }
    } else {
        echo "<script>alert('Restart is only allowed on Saturdays.');</script>";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/Sidebar.css">
    <link rel="stylesheet" href="css/Sales.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-6lHFSRnbrLsU3bLkxK6nSbFS7Mv5i1F3Zn1tFkM0JwpbF5f5cOoQrHvK6L+9ivde" crossorigin="anonymous">
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
            <li class=""><a href="Dashboard.php" class="text-decoration-none px-3 py-2 d-block"><i class="fal fa-home" id="custom-icon"></i> Dashboard</a></li>
            <li class="active"><a href="Sales.php" class="text-decoration-none px-3 py-2 d-block"><i class="far fa-chart-line" id="custom-icon"></i> Sales Report</a></li>
            <li class=""><a href="Inventory.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-archive" id="custom-icon"></i> Inventory</a></li>
        </ul>        
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

                            <div class="modal-overlay2" id="modal-confimrestart">
                                <div id="deleteForm">
                                    <div class="delete-modal">
                                        <p class="ConfirmP">Are you sure you want to RESTART the Sales?</p>
                                        <form method="post" action="Sales.php">
                                            <button id="btnConfirm" name="restart" class="btn" type="submit" >Confirm</button>
                                            <button id="btnCancel" class="btn"> Cancel</button>
                                        </form>
                                    </div>
                                </div>
                            </div>        

         <!-- Content -->
        <div class="dashboard-content px-3 pt-4">
            <h2 class="fs">Sales Report</h2>
            <div class="container-sm">
            <p  class="Dateindi"></p><i class="fas fa-calendar"></i><p id="currentDate"></p>
            </div>

            <div class="container1">

                <div class="row align-items-center" id="front">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto">
                            <h2 class="st">Weekly Sales</h2>
                        </div>
                        <div class="col-auto search">
                            <i id="searchIcon" class="fas fa-search"></i>
                            <form id="searchForm" action="Sales.php" method="GET">
                                <input autocomplete="off" type="text" name="searchSales" class="form-control" placeholder="Search Products" aria-label="Search Products" aria-describedby="button-addon2" id="searchInput">
                            </form>
                                <div class="button-group">
                                <button type="submit" id="restart" class="btn"><i class="fas fa-redo-alt"></i> Restart</button>
                                </div>
                                <button type="submit" id="print" class="btn"><i class="fas fa-print"></i> Print Report</button>
                        </div>
                    </div>
                </div>


                         <!-- Table -->
                         <div class="table-wrapper">
                        <form id="stockForm">
                            <table class="table" id="Table-stocks">
                                <thead class="thead">
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">No.</th>
                                        <td></td>
                                        <th scope="col">PRODUCT NAME</th>
                                        <th scope="col"></th>
                                        <th scope="col">BRAND</th>
                                        <th scope="col"></th>
                                        <th scope="col">DESCRIPTION</th>
                                        <th scope="col">STOCKS</th>
                                        <th scope="col">PRODUCT SOLD</th>
                                    </tr>
                                </thead>
                                <tbody id="table-body">
                                    <?php
                                    //Display the search
                                    if(isset($_GET['searchSales']) && !empty($_GET['searchSales'])) {
                                        if($searchResult && mysqli_num_rows($searchResult) > 0) {
                                            $count = 1;
                                            while($row = mysqli_fetch_assoc($searchResult)) {
                                                echo '<tr onclick="restartPage()" class="row-highlight" id="Resfresh">
                                                        <td></td>
                                                        <th scope="row">' . $count. '</th>
                                                        <td></td>                                            
                                                        <td>'.$row['Product_name'].'</td>
                                                        <th scope="col"></th>
                                                        <td>'.$row['Brand'].'</td>
                                                        <th scope="col"></th>
                                                        <td>'.$row['Description'].'</td>
                                                        <td>'.$row['Stocks'].'</td>';
                                    
                                                $productSold = $row['Product_sold'];
                                                if($productSold == intval($productSold)) {
                                                    echo '<td>'.intval($productSold).'</td>';
                                                } else {
                                                    echo '<td>'.$productSold.'</td>';
                                                }
                                                echo '</tr>';
                                                $count++;
                                            }
                                        } else {
                                            echo '<tr id="noPro" class="noPro"><td></td><td></td><td colspan="7">No Products Exist!</td><td></td></tr>';
                                        }
                                    }
                                    

                                    // Display all products info
                                    $sql_all_products = "SELECT * FROM `sales` ORDER BY `Product_name` ASC";
                                    $result_all_products = mysqli_query($con, $sql_all_products);
                                    $count = 1;
                                    if ($result_all_products && mysqli_num_rows($result_all_products) > 0) {
                                        while ($row = mysqli_fetch_assoc($result_all_products)) {
                                            echo '<tr class="ProductList">
                                                    <td></td>
                                                    <th scope="row">' . $count . '</th>
                                                    <td></td>
                                                    <td>'.$row['Product_name'].'</td>
                                                    <th scope="col"></th>
                                                    <td>'.$row['Brand'].'</td>
                                                    <th scope="col"></th>
                                                    <td>'.$row['Description'].'</td>
                                                    <td>'.$row['Stocks'].'</td>';

                                            $productSold = $row['Product_sold'];
                                            if($productSold == intval($productSold)) {
                                                echo '<td>'.intval($productSold).'</td>';
                                            } else {
                                                echo '<td>'.$productSold.'</td>';
                                            }
                                            echo '</tr>';
                                            $count++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </form>
                    </div>



            </div>  
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="js/Slidebar.js"></script>
<script src="js/Sales.js"></script>
</body>
</html>
