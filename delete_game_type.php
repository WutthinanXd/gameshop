<?php

include "config/connectDB.php";

$game_id = $_POST["game_id"];

$sql = "DELETE FROM tb_games
        WHERE game_id = $game_id";

mysqli_query($conn, $sql);

header("Location: add_game_type.php");

exit;

?>