<?php
session_start(); 

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== '627-999-726') {
    header("Location: LoginFinal.php");
    exit();
}


include 'Database.php';

if(isset($_POST['submit_product'])){
    $Product_name = $_POST['Product_name'];
    $Brand = $_POST['Brand'];
    $Description = $_POST['Description'];
    $Product_price = $_POST['Product_price'];
    $Stocks = $_POST['Stocks'];
    $Date = date("Y-m-d");

    $sql = "INSERT INTO `inventory` (Product_name, Brand, Description, Product_price, Stocks, Date) 
            VALUES ('$Product_name', '$Brand', '$Description', '$Product_price', '$Stocks', '$Date')";

    $result = mysqli_query($con, $sql);
    if($result){
        header('Location: Inventory.php');
        exit(); 
    } else {
        die(mysqli_error($con));
    }
}


// Delete the record
if(isset($_GET['deleteid'])){
    $id = $_GET['deleteid'];
    $sql_delete = "DELETE FROM `inventory` WHERE id=$id";
    $result_delete = mysqli_query($con, $sql_delete);

    if($result_delete){
        // Update IDs of remaining records
        $sql_update_ids = "SET @num := 0;
                           UPDATE `inventory` SET id = @num := (@num+1);
                           ALTER TABLE `inventory` AUTO_INCREMENT = 1;";
        $result_update_ids = mysqli_multi_query($con, $sql_update_ids);

        if($result_update_ids){
            header('location:Inventory.php');
        } else {
            die(mysqli_error($con));
        }
    } else {
        die(mysqli_error($con));
    }
}

// Update product
if(isset($_POST['update_product'])){
    if(isset($_POST['updateid'])) {
        $id = mysqli_real_escape_string($con, $_POST['updateid']);
        $Product_price = mysqli_real_escape_string($con, $_POST['Product_price']);
        $StocksToAdd = mysqli_real_escape_string($con, $_POST['Stocks']); // Stocks to be added
        $Date = date("Y-m-d");
        
        // Retrieve current Stocks from the database
        $query = "SELECT Stocks FROM inventory WHERE id = $id";
        $result = mysqli_query($con, $query);
        if($result) {
            $row = mysqli_fetch_assoc($result);
            $currentStocks = $row['Stocks'];

            // Calculate new total Stocks
            $newStocks = $currentStocks + $StocksToAdd;

            // Update database with new total Stocks and the provided date
            $sql = "UPDATE `inventory` SET Product_price = '$Product_price', Stocks = '$newStocks', Date = '$Date' WHERE id = $id";
            $result = mysqli_query($con, $sql);

            if($result){
                echo '<script>window.location.href = "Inventory.php";</script>';
                exit();
            } else {
                echo '<script>alert("Unable to update product!!!");</script>';
            }
        } else {
            echo "Error: Unable to fetch product details.";
        }
    } else {
        echo "Error: Missing product ID.";
    }
}


$result = null;

// Search functionality
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $sql = "SELECT * FROM `inventory` WHERE `Product_name` LIKE '%$search%' OR `Description` LIKE '%$search%' OR `Brand` LIKE '%$search%'";
    $result = mysqli_query($con, $sql);
    if(!$result) {
        die("Error executing the query: " . mysqli_error($con));
    }
}



?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INVENTORY</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Inventory.css">
    <link rel="stylesheet" href="css/Sidebar.css">
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
            <li class=""><a href="Sales.php" class="text-decoration-none px-3 py-2 d-block"><i class="far fa-chart-line" id="custom-icon"></i> Sales Report</a></li>
            <li class="active"><a href="Inventory.php" class="text-decoration-none px-3 py-2 d-block"><i class="fas fa-archive" id="custom-icon"></i> Inventory</a></li>
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

         <!-- Content -->
        <div class="dashboard-content px-3 pt-4">
            <h2 class="fs">Inventory</h2>
            <div class="container-sm">
            <p  class="Dateindi"></p> <i class="fas fa-calendar"></i> <p id="currentDate"></p>
            </div>

            <div class="container1">
                <div class="row align-items-center" id="front">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto">
                            <h2 class="st">Stocks</h2>
                        </div>
                        <div class="col-auto search">
                            <i id="searchIcon" class="fas fa-search"></i>
                            <form id="searchForm" action="Inventory.php" method="GET">
                                <input autocomplete="off" type="text" name="search" class="form-control" placeholder="Search Products" aria-label="Search Products" aria-describedby="button-addon2" id="searchInput">
                            </form>

                            <div class="button-group">
                                <button type="button" id="btnAddStocks" class="btn" onclick="toggleAddProductModal()">+ New Stocks</button>
                            </div>
                        </div>
                    </div>
                </div>

                        <!-- Product info input form -->
                            <div class="modal-overlay"  id="addProduct">
                                <form method="post" class="form-pro">
                                    <div class="Product-modal">
                                        <button type="button" class="btn-close" id="closebtnPro" aria-label="Close"></button>
                                        <h2 class="I-logo">Input Product Data</h2>
                                        <input autocomplete="off" class="ip1" type="text" name="Product_name" placeholder="Product Name" required>
                                        <input autocomplete="off" class="ip1" type="text" name="Brand"  placeholder="Brand" required>
                                        <input autocomplete="off" class="ip1" type="text" name="Description"  placeholder="Description" required>
                                        <input autocomplete="off" class="ip1" type="number" name="Product_price"  placeholder="Product Price" required>
                                        <input autocomplete="off" class="ip1" type="number" name="Stocks"  placeholder="Stocks" required>
                                        <button type="submit" id="btnSave" name="submit_product" class="btn">Save</button>
                                    </div>
                                </form>
                            </div>
                            <!-- Update Product info input form -->
                            <div class="modal-overlay2" id="updateProduct">
                                <form method="post" class="form-pro" id="updateForm">
                                    <div class="Update-modal">
                                        <button type="button" class="btn-close" id="closebtnPro2" aria-label="Close"></button>
                                        <h2 class="I-logo">Update Product Data</h2>
                                        <input autocomplete="off" class="ip1" type="number" name="Product_price"  placeholder="Product Price" required>
                                        <input autocomplete="off" class="ip1" type="number" name="Stocks"  placeholder="Stocks" required>
                                        <input type="hidden" id="updateid" name="updateid">
                                        <button type="submit" id="btnSave" name="update_product" class="btn">Update</button>
                                    </div>
                                </form>
                            </div>

                            <!-- delete modal -->
                            <div class="modal-overlay2" id="Delete_pro">
                                <form action="" id="deleteForm">
                                    <div class="delete-modal">
                                        <input type="hidden" id="deleteid" name="deleteid">
                                        <p class="ConfirmP">Are you sure you want to delete this product?</p>
                                        <button id="btnDel" class="btn">Confirm</button>
                                        <button id="btnCancel" class="btn"> Cancel</button>
                                    </div>
                                </form>
                            </div>


                    <!-- hanggang dgd and idv -->

                         <!-- Table -->
                         <div class="table-wrapper">
                         <form id="stockForm">
                         <table class="table" id="Table-stocks">
                            <thead class="thead">
                                <tr>
                                    <th scope="col">No.</th>
                                    <th scope="col">PRODUCT NAME</th>
                                    <th scope="col">BRAND</th>
                                    <th scope="col">DESCRIPTION</th>
                                    <th scope="col">PRODUCT PRICE</th>
                                    <th scope="col">CURRENT STOCKS</th>
                                    <th scope="col">EDIT</th>
                                </tr>
                            </thead>
                            <tbody id="table-body" class="table-body">
                            <!-- Display Product Data from Database-->
                            <?php

                                if($result && mysqli_num_rows($result) > 0) {
                                    while($row = mysqli_fetch_assoc($result)) {
                                        // Display search results
                                        echo '<tr onclick="restartPage()" class="row-highlight" class="trtable">
                                            <th scope="row">'.$row['id'].'</th>
                                            <td>'.$row['product_name'].'</td>
                                            <td>'.$row['brand'].'</td>
                                            <td>'.$row['description'].'</td>
                                            <td>Php ' . number_format($row['product_price'], 2, '.', ',') . '</td>
                                            <td>'.$row['stocks'].'</td>
                                            <td>
                                                <button id="update" class="pen" product-id="'.$row['id'].'">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button id="delete" class="delete">
                                                    <i class="fas fa-trash" deleteid="'.$row['id'].'" onclick="deletemodal(event)"></i>
                                                </button>
                                            </td>
                                        </tr>';
                                    }
                                }else{
                                   if( !empty($search)){
                                    echo '<tr id="noPro" class="noPro"><td colspan="7">No Products Exist!</td></tr>';
                                    }
                                }

                                // Display remaining products if search result is empty or not set
                                $sql = "SELECT * FROM `inventory` ORDER BY product_name";
                                $result = mysqli_query($con, $sql);
                                if($result){
                                    $count = 1;
                                    while($row = mysqli_fetch_assoc($result)){
                                        // Display the count in sequential order and the other product 
                                        echo'<tr class="ProductList">
                                            <th scope="row">' . $count . '</th>
                                            <td>'.$row['product_name'].'</td>
                                            <td>'.$row['brand'].'</td>
                                            <td>'.$row['description'].'</td>
                                            <td>Php ' . number_format($row['product_price'], 2, '.', ',') . '</td>
                                            <td>'.$row['stocks'].'</td>
                                            <td>
                                                <button id="update" class="pen" product-id="'.$row['id'].'">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <button id="delete" class="delete">
                                                    <i class="fas fa-trash" deleteid="'.$row['id'].'" onclick="deletemodal(event)"></i>
                                                </button>
                                            
                                            </td>
                                        </tr>';
                                        $count++;
                                    }
                                }

                            ?>
                            </tbody>
                        </form>
                    </div>
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
<script src="js/Inventory.js"></script>
</body>
</html>
