<?php
session_start();

header("location: /database.php");

$_SESSION["server"] = null;
$_SESSION["u_name"] = null;
$_SESSION["pass"] = null;
$_SESSION["database"] = null;
$_SESSION["table"] = null;
$_SESSION["part"] = null;
?>