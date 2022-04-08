<?php
session_start();
if (!isset($_SESSION["part"])) {
    $_SESSION["server"] = $_POST["server"];
    $_SESSION["u_name"] = $_POST["u_name"];
    $_SESSION["pass"] = $_POST["pass"];
} else if ($_SESSION["part"] == 1) {
    $_SESSION["database"] = $_POST["database"];
} else if ($_SESSION["part"] == 2) {
    $_SESSION["table"] = $_POST["table"];
}
define('DB_SERVER', $_SESSION["server"]);
define('DB_USERNAME', $_SESSION["u_name"]);
define('DB_PASSWORD', $_SESSION["pass"]);
define('DB_NAME', $_SESSION["database"]);
define('table', $_SESSION["table"]);

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

//for if the user does not have the proper login creedentials
if (!isset($_SESSION["part"])) {
    $_SESSION["part"] = NULL;
    if (!($link === false)) {
        $_SESSION["part"] = 1;
    } else {
        echo '<!DOCTYPE html>
    <html>
        <body>
            <form action="database.php" method="post">
                server: <input type="text" name="server"><br>
                username: <input type="text" name="u_name"><br>
                password: <input type="password" name="pass"><br>
                <input type="submit" value="submit">
            </form>
    </body>
</html>';
        exit;
    }
}
//for if they are logged in, but do not have a database selected
if ($_SESSION["part"] === 1) {
    if (!mysqli_select_db($link, DB_NAME)) {
        echo '<!DOCTYPE html>
    <html>
        <body>
            <form action="database.php" method="post">';
        $stmt = mysqli_query($link, "SHOW DATABASES;");
        while ($row = mysqli_fetch_row($stmt)) {
            echo '<input type="radio" name="database" value="' . $row[0] . '"><label for="' . $row[0] . '">' . $row[0] . '</label><br>';
        }
        echo '
            <input type="submit">
        </form>';
        include 'hotlinks.php';
        echo '
    </body>
</html>';
        exit;
    } else {
        $_SESSION["part"] = 2;
    }
}
//for is there is not a table selected
if ($_SESSION["part"] === 2) {
    mysqli_select_db($link, DB_NAME);
    if (!mysqli_query($link, "SELECT * FROM " . table . ";")) {
        echo '<!DOCTYPE html>
    <html>
        <body>
            <form action="database.php" method="post">';
        $stmt = mysqli_query($link, "SHOW TABLES;");
        while ($row = mysqli_fetch_row($stmt)) {
            echo '<input type="radio" name="table" value="' . $row[0] . '"><label for="' . $row[0] . '">' . $row[0] . '</label><br>';
        }
        echo '
            <input type="submit">
        </form>';
        include 'hotlinks.php';
        echo '
    </body>
</html>';
        exit;
        //if the login, database, table have all been selected.
    } else {
        // $_SESSION["part"] = 3;
        $sql = "SELECT * FROM " . table;

        $stmt = mysqli_prepare($link, $sql);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            $resultmeta = mysqli_stmt_result_metadata($stmt);
            list($columns, $columns_vars) = array(array(), array());
            while ($field = mysqli_fetch_field($resultmeta)) {
                $columns[] = $field->name;
                $columns_vars[] = &${'column_' . $field->name};
            }
            call_user_func_array('mysqli_stmt_bind_result', array_merge(array($stmt), $columns_vars));

            // get return results
            $return_array = array();
            while (mysqli_stmt_fetch($stmt)) {
                $row = array();
                foreach ($columns as $col) {
                    $row[$col] = ${'column_' . $col}; // populate assoc. array with data
                }
                $return_array[] = $row; // push row data onto return array
            }
            $head_done = false;
            echo '<!DOCTYPE html>
    <html>
        <body>
            <style>td, th{border: 1px solid black;}</style>
            <table style="border-collapse: collapse;">';
            foreach($return_array as $curr_row){
                if(!$head_done){
                    echo '<tr>';
                    foreach($curr_row as $col_name => $data){
                        echo '<th>' . $col_name . '</th>';
                    }
                    echo '</tr>';
                    $head_done = true;
                }
                echo '<tr>';
                foreach($curr_row as $col_name => $data){
                    echo '<td>' . $data . '</td>';
                }
                echo '</tr>';
            }
            echo '</table>';
            include 'hotlinks.php';
            echo '</body></html>';
            exit;
        }
    }
}
$_SESSION["part"] = 2;
mysqli_close($link);
?>