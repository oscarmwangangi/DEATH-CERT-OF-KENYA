<!-- database connection -->
<?php
function get_db_connection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "deathcertificate_db";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Connection failed:please check your settings " . $e->getMessage();
        die();
    }
}
?>
 