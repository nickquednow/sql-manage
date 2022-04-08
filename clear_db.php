<?php
session_start();

header("location: /database.php");

$_SESSION["database"] = null;
$_SESSION["table"] = null;
$_SESSION["part"] = 1;
?>