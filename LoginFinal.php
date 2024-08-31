<?php
session_start();
include 'Database.php';

$error = ''; 

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login_id = htmlspecialchars($_POST["Login-id"]);
    $pin = htmlspecialchars($_POST["pin"]);

    $query = "SELECT user_id, name FROM users WHERE user_id = ? AND pin = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $login_id, $pin);
    $stmt->execute();
    $result = $stmt->get_result();

    $user = $result->fetch_assoc();
    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];

        // Check if the user_id is '627-999-726' for the admin to access the dashboard
        if ($login_id === '627-999-726') {
            header("Location: Dashboard.php");
            exit();
        } else {
            header("Location: test.php");
            exit();
        }
    } else {
        $error = "Incorrect ID or Pin"; 
    }
}
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="css/LoginFinal.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="container" id="Login">
        <img class="Logo" src="images/Logo.png" alt="">
        <div class="Login-form">
            <img src="" alt="">
            <h3 class="Rg">Login</h3>
            <form method="post" action="">
                <input autocomplete="off" pattern="\d{3}-\d{3}-\d{3}" id="Input-Id" class="ip1" type="text" placeholder="ID (000-000-000)" name="Login-id" required>
                <div class="password-toggle">
                    <input id="Login-pin" class="ip2 password-field" type="password" placeholder="Pin" name="pin" required>
                    <span class="toggle-icon" onclick="togglePasswordVisibility('Login-pin', this)">
                        <i class="fas fa-eye-slash"></i>
                    </span>
                  <!-- Display error message if present -->
                    <?php if (!empty($error)) : ?>
                        <span class="Warn" role="alert">
                            <?php echo $error; ?>
                    </span>
                    <?php endif; ?>
                </div>
                <button type="submit" class="login">Login</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/Login.js"></script>
</body>
</html>
