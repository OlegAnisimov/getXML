<?php
function db_connection () {
    $dsn    = "mysql:host=localhost;dbname=tenderok;charset=utf8";
    $dbuser = "root";
    $dbpass = "mysql";

    global $db_conn;
    $db_conn = new PDO($dsn, $dbuser, $dbpass);
    $db_conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

    return $db_conn;
}
