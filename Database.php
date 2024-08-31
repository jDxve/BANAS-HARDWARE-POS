<?php

$con=new mysqli('localhost', 'root', '', 'banas hardware pos system');

if(!$con){
    die(mysqli_error($con));
}

?>