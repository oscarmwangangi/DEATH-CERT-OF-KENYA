<?php
session_start();

// Redirect if the user is not logged in or not a user
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php");
    exit();
}

require 'db.php';

$conn = get_db_connection();

// Fetch user data
$user_id = $_SESSION['user_id'];

// Check if user_id is set and valid
if (!isset($user_id) || empty($user_id)) {
    echo "User ID is not set.";
    exit();
}

// Fetch username from the users table
$user_info = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_info->execute([$user_id]);
$user_info = $user_info->fetch(PDO::FETCH_ASSOC);

if ($user_info) {
    $username = htmlspecialchars($user_info['username']);
} else {
    echo "Username not found.";
    exit();
}

// Prepare data for bar chart
$days = [];
$counts = [];

// Get the start and end of the current week (Monday to Sunday)
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

// Fill the $days array with day names (Mon, Tue, etc.)
for ($date = $startOfWeek; $date <= $endOfWeek; $date = date('Y-m-d', strtotime($date . ' +1 day'))) {
    $days[] = date('D', strtotime($date)); // Get day abbreviation (Mon, Tue, etc.)
    // Fetch record count for the current date
    $stmt = $conn->prepare("SELECT COUNT(*) FROM deathcertificate_information WHERE DATE(submission_timestamp) = ? AND user_id = ?");
    $stmt->execute([$date, $user_id]);
    $counts[] = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
   <style>
    nav{
        margin: 0px;
    }
    body {
        background-color: rgba(87, 68, 118, 0.25);  
            color: #ffffff;
        }
        .sidebar{
        background-color: rgba(64, 81, 137, 0.5); /* Blue color with 80% opacity */
        border: none;
        border-radius: none;
       }
       .active{
        color: #ffffff;
       }
   </style>
</head>
<body class="">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar   shadow-lg p-4" style="height: 100vh;">
                <div class="position-fixed">
                    <h4 class="text- mb-4">Welcome,<br> <?php echo $username; ?>!</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="./log/view.php">  <i class="fas fa-pen-square text-xl"></i>
                            <span class="hidden sm:block text-base">Data Entry</span></a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="logout.php">
                                <button type="submit" class="btn btn-danger w-unset w-100">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
                <div class="">
                    <div class="card-body">
                        <h2 class="text-primary mb-3">User Dashboard</h2>
                        
                        <!-- Bar Chart -->
                        <div class="chart-container mt-4">
                            <h3>Progress Over the Week</h3>
                            <canvas id="barChart"></canvas>
                        </div>

                        <!-- Display Data -->
                        
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
         document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('barChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($days); ?>, // Use day names (Mon, Tue, etc.)
                datasets: [{
                    label: 'Records Submitted',
                    data: <?php echo json_encode($counts); ?>,
                    backgroundColor: '#4bc0c0',
                    borderColor: '#36a2eb',
                    borderWidth: 0
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>
</html>
