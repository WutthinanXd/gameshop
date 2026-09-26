<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "game_shop_db";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
}

mysqli_set_charset($conn, "utf8mb4");

?>