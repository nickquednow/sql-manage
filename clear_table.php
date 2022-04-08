<?php
session_start();

header("location: /database.php");

$_SESSION["table"] = null;
$_SESSION["part"] = 2;
?>