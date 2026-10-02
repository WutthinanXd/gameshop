<?php

include "config/connectDB.php";

$game_name = $_POST["game_name"];

$sql = "INSERT INTO tb_games
(  
    game_name

)

VALUES
(
    '$game_name'
)";

mysqli_query($conn, $sql);

header("Location: add_game_type.php");

exit;

?>