<?php

include('w_core.php');

function web() {
    db_connect();
    db_select_db();
    $result = db_query("SELECT nombreobj,tipo,img  FROM objetos");
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<p style='padding: 5px; margin: 0;'> <img style='vertical-align: middle;'src='img/" . $row['img'] . ".gif'>" . $row['nombreobj'] . "</p>";
    }
    db_close();
}

?>