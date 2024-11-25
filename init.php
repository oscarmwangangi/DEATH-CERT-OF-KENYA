<?php
require 'db.php';

function is_first_user() {
    $conn = get_db_connection();
    $stmt = $conn->query("SELECT * FROM users");
    $user = $stmt->fetch();
    return $user === false;
}

if (is_first_user()) {
    // Create the first main admin user
    $conn = get_db_connection();
    $password = password_hash("admin123", PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (userName, password, role) VALUES ('admin', '$password', 'main_admin')");
}
?>
