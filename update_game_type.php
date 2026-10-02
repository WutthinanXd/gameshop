<?php
include "config/connectDB.php";

$game_id = $_POST["game_id"];
$game_name = $_POST["game_name"];

$sql = "UPDATE tb_games SET
        game_name = '$game_name'
        WHERE game_id = $game_id";

mysqli_query($conn, $sql);

header("Location: add_game_type.php");
exit;
?>