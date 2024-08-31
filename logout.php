<?php
session_start();

function logout() {

    header("Location: loginFinal.php");
    session_destroy();
    exit;
}

if(isset($_POST['logout'])) {
    logout();
}
?>
