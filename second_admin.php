<?php
session_start();
if ($_SESSION['role'] !== 'second_admin') {
    header("Location: index.php");
    exit();
}

require 'db.php'; // Include the database connection file

// Establish a database connection using the get_db_connection() function
$pdo = get_db_connection();

// Fetch the username from the database
$userName = 'User'; // Default value if not found
try {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT username FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user_info) {
        $userName = htmlspecialchars($user_info['username']);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch data for charts
try {
    $query = "SELECT DATE(submission_timestamp) AS date, COUNT(*) AS count FROM deathcertificate_information WHERE user_id = :user_id GROUP BY DATE(submission_timestamp)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $recordCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $queryAdmins = "SELECT COUNT(*) AS count FROM users WHERE role = 'second_admin'";
    $queryUsers = "SELECT COUNT(*) AS count FROM users WHERE role = 'user'";
    $stmtAdmins = $pdo->query($queryAdmins);
    $stmtUsers = $pdo->query($queryUsers);
    $adminCount = $stmtAdmins->fetchColumn();
    $userCount = $stmtUsers->fetchColumn();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Second Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="second_admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark text-white sidebar" style="height: 100vh;">
                <div class="position-sticky">
                    <h3 class="text-center mt-4 mb-3">Welcome, <?php echo htmlspecialchars($userName); ?>!</h3>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="register.php" class="nav-link text-white">Register New User</a>
                        </li>
                        <li class="nav-item">
                            <a href="./log/fill.php" class="nav-link text-white">Data Entry</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="logout.php" class="mt-3">
                                <button type="submit" class="btn btn-danger btn-lg rounded-pill w-100">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2 class="text-primary">Dashboard Overview</h2>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow-lg p-4 mb-4">
                            <div class="card-body">
                                <h4 class="card-title">Records Filled by You</h4>
                                <canvas id="recordsBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-lg p-4 mb-4">
                            <div class="card-body">
                                <h4 class="card-title">User & Admin Distribution</h4>
                                <canvas id="userPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
    // Data for the Bar Chart (Records Filled by You)
    const recordCounts = <?php echo json_encode($recordCounts); ?>;

    // Get the start and end date of the current week (Monday to Sunday)
    function getWeekStartEnd() {
        const currentDate = new Date();
        const firstDayOfWeek = currentDate.getDate() - currentDate.getDay() + 1; // Get Monday of the current week
        const lastDayOfWeek = firstDayOfWeek + 6; // Get Sunday of the current week
        const startOfWeek = new Date(currentDate.setDate(firstDayOfWeek));
        const endOfWeek = new Date(currentDate.setDate(lastDayOfWeek));
        
        // Reset time to 00:00:00 to ensure we include all records for the day
        startOfWeek.setHours(0, 0, 0, 0);
        endOfWeek.setHours(23, 59, 59, 999);

        return { startOfWeek, endOfWeek };
    }

    // Function to filter records that fall within the current week
    function filterCurrentWeekRecords(records) {
        const { startOfWeek, endOfWeek } = getWeekStartEnd();
        
        return records.filter(record => {
            const recordDate = new Date(record.date);
            return recordDate >= startOfWeek && recordDate <= endOfWeek;
        });
    }

    // Filter the records to include only those from the current week
    const currentWeekRecords = filterCurrentWeekRecords(recordCounts);

    // Function to convert date to day of the week
    function getDayName(dateStr) {
        const date = new Date(dateStr);
        const options = { weekday: 'short' }; // This will give you 'Mon', 'Tue', etc.
        return new Intl.DateTimeFormat('en-US', options).format(date);
    }

    // Create an array of all days of the week
    const allDays = ['Sun','Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // Initialize an object to hold counts for each day
    const dayCounts = {
        'Sun': 0,
        'Mon': 0,
        'Tue': 0,
        'Wed': 0,
        'Thu': 0,
        'Fri': 0,
        'Sat': 0
       
    };

    // Fill dayCounts with actual data from currentWeekRecords
    currentWeekRecords.forEach(item => {
        const dayName = getDayName(item.date);
        dayCounts[dayName] += item.count;
    });

    // Prepare data for the chart
    const labels = allDays;
    const data = labels.map(day => dayCounts[day]);

    const ctxBar = document.getElementById('recordsBarChart').getContext('2d');
    const recordsBarChart = new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Records Filled (This Week)',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
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


        // Data for the Pie Chart (User & Admin Distribution)
        const adminCount = <?php echo $adminCount; ?>;
        const userCount = <?php echo $userCount; ?>;

        const ctxPie = document.getElementById('userPieChart').getContext('2d');
        const userPieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Second Admins', 'Users'],
                datasets: [{
                    data: [adminCount, userCount],
                    backgroundColor: ['#FE9496', '#4BCBEB'],
                    borderColor: ['', ''],
                    borderWidth: 0
                }]
            }
        });
    </script>
</body>
</html>
