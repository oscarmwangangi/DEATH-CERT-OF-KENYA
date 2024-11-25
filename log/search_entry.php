<?php

include 'config.php'; // Ensure this file exists and is correct

if (isset($_POST['Entry_no'])) {
    $entry_no = $_POST['Entry_no'];

    // Ensure $pdo is defined and the connection is successful
    if (!$pdo) {
        die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
    }

    $query = "SELECT * FROM deathcertificate_information WHERE Entry_no = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$entry_no]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        error_log("Fetched data: " . json_encode($result)); // Log fetched data
        echo json_encode(['status' => 'success', 'data' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Entry number not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Entry number not provided.']);
}
?>
