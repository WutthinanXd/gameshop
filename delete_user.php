<?php

include "config/connectDB.php";

$user_id = $_POST["user_id"];

$sql = "DELETE FROM tb_users
        WHERE user_id = $user_id";

mysqli_query($conn, $sql);

header("Location: users.php");

exit;

?>