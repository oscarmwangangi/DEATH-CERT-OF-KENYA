<?php

include './log/config.php'; // Remove @ to see errors if the file is not found

if (isset($_POST['submit'])) {
    // Ensure $pdo is defined and the connection is successful
    if (!$pdo) {
        die("Connection failed: " . $pdo->errorInfo()[2]);
    }
}

if (isset($_POST['Entry_no'])) {
    $entry_no = $_POST['Entry_no'];

    $query = "SELECT * FROM deathcertificate_information WHERE Entry_no = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$entry_no]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['status' => 'success', 'data' => $result]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Entry number not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Entry number not provided.']);
}
?>
