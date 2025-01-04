<?php
session_start();
if ($_SESSION['role'] !== 'main_admin') {
    header("Location: index.php");
    exit();
}

// Database connection
require './log/config.php'; // Ensure you have the correct path to your config file

// Get search parameters
$Name_of_the_filler = isset($_GET['Name_of_the_filler']) ? $_GET['Name_of_the_filler'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Initialize the query
$query = "SELECT * FROM deathcertificate_information WHERE 1=1";

// Add conditions to the query
$params = [];
if (!empty($Name_of_the_filler)) {
    $query .= " AND Name_of_the_filler LIKE ?";
    $params[] = '%' . $Name_of_the_filler . '%';
}
if (!empty($start_date) && !empty($end_date)) {
    if ($start_date > $end_date) {
        echo "Error: Start date must be before or equal to end date.";
        exit();
    }
    $query .= " AND DATE(submission_timestamp) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} elseif (!empty($start_date)) {
    $query .= " AND DATE(submission_timestamp) >= ?";
    $params[] = $start_date;
} elseif (!empty($end_date)) {
    $query .= " AND DATE(submission_timestamp) <= ?";
    $params[] = $end_date;
}

// Prepare and execute the query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Display results
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <h2>Search Results</h2>
        <a href="./index.php" class="btn btn-primary btn-lg shadow mb-1">Back</a>
        <?php if (empty($results)): ?>
            <div class="alert alert-warning">No results found.</div>
            
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th> <!-- Column for numbering -->
                        <th>ID</th>
                        <th>Name of Filler</th>
                        <th>Submission Timestamp</th>
                        <!-- Add other columns as needed -->
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; // Initialize counter ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td> <!-- Increment counter for each row -->
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['Name_of_the_filler']); ?></td>
                            <td><?php echo htmlspecialchars($row['submission_timestamp']); ?></td>
                            <!-- Add other columns as needed -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

