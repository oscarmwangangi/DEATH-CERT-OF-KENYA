<?php
session_start();
if ($_SESSION['role'] !== 'main_admin') {
    header("Location: index.php");
    exit();
}

// Database connection
require './log/config.php'; // Ensure you have the correct path to your config file

// Initialize variables
$Name_of_the_filler = '';
$start_date = '';
$end_date = '';
$user_name = '';

// Check if GET parameters are set
if (isset($_GET['Name_of_the_filler'])) {
    $Name_of_the_filler = $_GET['Name_of_the_filler'];
}
if (isset($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
}
if (isset($_GET['end_date'])) {
    $end_date = $_GET['end_date'];
}

// Fetch user name based on session user ID
$user_id = $_SESSION['user_id']; // Ensure this is set when the user logs in
$user_query = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user_result = $user_query->fetch(PDO::FETCH_ASSOC);
$user_name = $user_result['username'] ?? '';

// Query data for charts
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));

// Fetch data for today's forms and weekly forms using submission_timestamp
$today_query = $pdo->prepare("SELECT COUNT(*) FROM deathcertificate_information WHERE DATE(submission_timestamp) = ?");
$today_query->execute([$today]);
$today_count = $today_query->fetchColumn() ?: 0;

$week_query = $pdo->prepare("SELECT COUNT(*) FROM deathcertificate_information WHERE DATE(submission_timestamp) BETWEEN ? AND ?");
$week_query->execute([$week_start, $week_end]);
$week_count = $week_query->fetchColumn() ?: 0;

// Fetch user data
$user_query = $pdo->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$user_query->execute();
$user_data = $user_query->fetchAll(PDO::FETCH_ASSOC);

// Initialize default counts
$user_counts = [
    'main_admin' => 0,
    'second_admin' => 0,
    'user' => 0
];

// Populate counts from fetched data
foreach ($user_data as $data) {
    if (array_key_exists($data['role'], $user_counts)) {
        $user_counts[$data['role']] = $data['count'];
    }
}
$query = "SELECT Name_of_the_filler, COUNT(*) as record_count
          FROM deathcertificate_information
          GROUP BY Name_of_the_filler
          ORDER BY record_count DESC
          LIMIT 5";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Fetch the result
$topFillers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Chart.js
$labels = [];
$data = [];

foreach ($topFillers as $filler) {
    $labels[] = $filler['Name_of_the_filler'];
    $data[] = $filler['record_count'];
}

// Initialize an array to hold the monthly counts
$monthly_counts = array_fill(0, 12, 0); // Array to store counts for each month (Jan-Dec)

// Query to get the submission month for each death certificate
$query = "SELECT MONTH(submission_timestamp) as month, COUNT(*) as count FROM deathcertificate_information GROUP BY MONTH(submission_timestamp)";
$stmt = $pdo->query($query);

// Fetch the results and populate the monthly_counts array
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $month = (int)$row['month']; // Get the month (1 = January, 2 = February, etc.)
    $monthly_counts[$month - 1] = $row['count']; // Set the count for that month (zero-indexed)
}

// Print the PHP array as a JavaScript variable for the chart
// SQL query to count the occurrences of each Death_in_the (district)

$currentYear = date('Y');
$currentMonth = date('m');

// SQL query to count the occurrences of each Death_in_the (district) for the current month and year
$query = "SELECT Death_in_the, COUNT(*) AS count 
          FROM deathcertificate_information 
          WHERE YEAR(submission_timestamp) = :currentYear 
            AND MONTH(submission_timestamp) = :currentMonth
          GROUP BY Death_in_the
          ORDER BY count DESC
          LIMIT 5";  // Limit to top 5 districts
$stmt = $pdo->prepare($query);
$stmt->bindParam(':currentYear', $currentYear, PDO::PARAM_INT);
$stmt->bindParam(':currentMonth', $currentMonth, PDO::PARAM_INT);
$stmt->execute();

// Initialize arrays to store labels (districts) and data (counts)
$districts = [];
$deathRates = [];

// Fetch the results
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $districts[] = $row['Death_in_the'];  // Store the district name
    $deathRates[] = $row['count'];  // Store the count of occurrences
}

// Convert the PHP arrays to JavaScript arrays
$districts_js = json_encode($districts);
$deathRates_js = json_encode($deathRates);

// Get the current month and year
$currentYear = date('Y');
$currentMonth = date('m');

// Age groups in ranges
$ageGroups = [
    '0-18' => [0, 18],
    '19-35' => [19, 35],
    '36-50' => [36, 50],
    '51-70' => [51, 70],
    '71+' => [71, 150] // Assuming 150 is the upper limit of age for this dataset
];

// Initialize arrays to store counts of men and women per age group
$ageGroupCountsMen = array_fill_keys(array_keys($ageGroups), 0);
$ageGroupCountsWomen = array_fill_keys(array_keys($ageGroups), 0);

// Loop through each age group and count the number of deaths for men and women in the current month
foreach ($ageGroups as $label => $range) {
    // Query to count deaths of men and women for the current month
    $query = "SELECT COUNT(*) AS count, sex 
              FROM deathcertificate_information 
              WHERE YEAR(submission_timestamp) = :currentYear 
                AND MONTH(submission_timestamp) = :currentMonth 
                AND Age_of_Deceased BETWEEN :minAge AND :maxAge
              GROUP BY sex";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':currentYear', $currentYear, PDO::PARAM_INT);
    $stmt->bindParam(':currentMonth', $currentMonth, PDO::PARAM_INT);
    $stmt->bindParam(':minAge', $range[0], PDO::PARAM_INT);
    $stmt->bindParam(':maxAge', $range[1], PDO::PARAM_INT);
    $stmt->execute();

    // Update counts for men and women
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['sex'] == 'Male') {
            $ageGroupCountsMen[$label] = $row['count'];
        } elseif ($row['sex'] == 'Female') {
            $ageGroupCountsWomen[$label] = $row['count'];
        }
    }
}

// Convert PHP arrays to JavaScript arrays for the chart
$menData = json_encode(array_values($ageGroupCountsMen));
$womenData = json_encode(array_values($ageGroupCountsWomen));
$ageGroupLabels = json_encode(array_keys($ageGroups));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Background color */
        :root {
            --wp--preset--color--blackish-blue: #101B2E;
        }
        body {
            background-color: var(--wp--preset--color--blackish-blue);
            color: #ffffff;
        }
       

    .marquee {
        display: inline-block;
        white-space: nowrap;
        animation: marquee 5s linear infinite;
    } @keyframes marquee {
        0% {
            transform: translateX(100%);
        }
        50% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(100%);
        }
    }
    </style>
</head>
<body class="font-sans bg-gray-900 text-white">

    <!-- Sidebar -->
   
<!-- Sidebar -->
<div class="fixed top-0 left-0 h-screen w-20 sm:w-40 md:w-60 lg:w-65 p-6 bg-black bg-opacity-50 shadow-[0px_10px_20px_rgba(0,0,0,0.5)] rounded-lg">
   

    <!-- Main Title, hidden on small screens -->
    <h4 class="text-xl font-semibold text-white mb-6 sm:block hidden">Admin Dashboard</h4>

    <!-- Menu Items -->
    <div class="space-y-4">
        <!-- Register New User -->
        <a href="register.php" class="flex items-center space-x-3 text-gray-300 hover:text-white py-2 px-4 rounded transition-colors">
            <i class="fas fa-user-plus text-xl"></i> <!-- Always show icon -->
            <span class="hidden sm:block text-base">Register New User</span> <!-- Show text only on sm and above -->
        </a>

        <!-- Correction -->
        <a href="./correction_form.php" class="flex items-center space-x-3 text-gray-300 hover:text-white py-2 px-4 rounded transition-colors">
            <i class="fas fa-edit text-xl"></i>
            <span class="hidden sm:block text-base">Correction</span>
        </a>

        <!-- Data Entry -->
        <a href="./log/fill.php" class="flex items-center space-x-3 text-gray-300 hover:text-white py-2 px-4 rounded transition-colors">
            <i class="fas fa-pen-square text-xl"></i>
            <span class="hidden sm:block text-base">Data Entry</span>
        </a>

        <!-- Change Roles -->
        <a href="./change_roles.php" class="flex items-center space-x-3 text-gray-300 hover:text-white py-2 px-4 rounded transition-colors">
            <i class="fas fa-user-cog text-xl"></i>
            <span class="hidden sm:block text-base">Change Roles</span>
        </a>

        <!-- Logout -->
        <form method="POST" action="logout.php">
            <button type="submit" class="w-full text-gray-300 hover:text-white py-2 px-4 rounded transition-colors">
                <i class="fas fa-sign-out-alt text-xl"></i>
                <span class="hidden sm:block text-base">Logout</span>
            </button>
        </form>
    </div>
</div>



    <!-- Main Content -->
    <div class="ml-60 p-8 space-y-8">
        <!-- Title and Search Bar -->
        <div class="ml-20 sm:ml-40 md:ml-60 lg:ml-65 flex-1">
        <h1 class="text-3xl text-blue-500 font-semibold marquee">
            Welcome, <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>!
        </h1>
        </div>

        <form method="GET" action="search_results.php" class="flex space-x-4">
            <input type="text" name="Name_of_the_filler" placeholder="Search by Filler Name" class="flex-grow p-2 bg-gray-700 rounded text-white">
            <input type="date" name="start_date" class="p-2 bg-gray-700 rounded text-white">
            <input type="date" name="end_date" class="p-2 bg-gray-700 rounded text-white">
            <button type="submit" class="bg-blue-600 px-4 py-2 rounded text-white">Search</button>
        </form>

        <!-- Charts Layout -->
        <div class="grid grid-cols-3 grid-rows-[auto,auto] gap-6 w-full">

            <!-- Row 1: Three charts -->
            <div class="bg-gray-800 p-4 rounded shadow">
                <canvas id="lineChart1"></canvas>
            </div>
            <div class="bg-gray-800 p-4 rounded shadow">
                <canvas id="lineChart2"></canvas>
            </div>
            <div class="bg-gray-800 p-4 rounded shadow">
                <canvas id="horizontalBarChart"></canvas>
            </div>

            <!-- Row 2: Charts in separate grid section -->
            <!-- Column 1: Two charts stacked vertically -->
            <div class="col-span-1 space-y-6">
                <div class="bg-gray-800 p-4 rounded shadow">
                    <canvas id="barChart1"></canvas>
                </div>
                <div class="bg-gray-800 p-4 rounded shadow">
                    <canvas id="barChart2"></canvas>
                </div>
            </div>

            <!-- Column 2: Single chart spanning 2 rows -->
            <div class="col-span-2 row-span-2 bg-gray-800 p-4 rounded shadow">
                <canvas id="comparisonBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- JavaScript for Chart.js -->
    <script>
    // Line chart for Death Certificates filled (Jan - Dec)
    const lineChart1 = new Chart(document.getElementById('lineChart1'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Death Certs Filled',
            data: <?php echo json_encode($monthly_counts); ?>, // Pass the PHP array as JavaScript data
            borderColor: '#4A90E2',
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

    // Line chart for Death Rates (Jan - Dec)
    const lineChart2 = new Chart(document.getElementById('lineChart2'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Death Rates',
                data: [<?php echo implode(',', [5, 7, 6, 10, 12, 8, 9, 15, 14, 13, 17, 19]); ?>],
                borderColor: '#E27D60',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Horizontal bar chart for Top 5 counties with highest death rates
    const horizontalBarChart = new Chart(document.getElementById('horizontalBarChart'), {
    type: 'bar',
    data: {
        labels: <?php echo $districts_js; ?>,  // Death_in_the (districts)
        datasets: [{
            label: 'Death Rate',
            data: <?php echo $deathRates_js; ?>,  // The counts of deaths for each district
            backgroundColor: ['#6B8E23', '#FF6347', '#4682B4', '#FFD700', '#4B0082'],
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',  // Makes it horizontal
        scales: {
            x: {
                beginAtZero: true  // Start x-axis from zero
            }
        }
    }
});

    // Bar chart for Number of users by role
    const barChart1 = new Chart(document.getElementById('barChart1'), {
        type: 'bar',
        data: {
            labels: ['Main Admin', 'Second Admin', 'User'],
            datasets: [{
                label: 'Number of Users',
                data: [
                    <?php echo $user_counts['main_admin']; ?>,
                    <?php echo $user_counts['second_admin']; ?>,
                    <?php echo $user_counts['user']; ?>
                ],
                backgroundColor: ['#4A90E2', '#E27D60', '#8B4513']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });


   // JavaScript: Create the Chart

// JavaScript: Create the Chart
const ctx = document.getElementById('barChart2').getContext('2d');
const barChart2 = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>, // Name of the fillers
        datasets: [{
            label: 'Top 5 Fillers',
            data: <?php echo json_encode($data); ?>, // Record count for each filler
            backgroundColor: '#1E90FF', // Bar color
            borderRadius: 5, // Rounded corners for bars
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y', // Horizontal bar chart
        scales: {
            x: {
                beginAtZero: true, // Start x-axis from 0
                ticks: {
                    min: 0 // Ensure the x-axis starts from 0
                },
                reverse: true, // Reverse the x-axis to make bars go right to left
            },
            y: {
                beginAtZero: true, // Start y-axis from 0
                ticks: {
                    padding: 10 // Add padding between the bars and the labels
                },
                position: 'right' // Position the y-axis labels on the right
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw; // Show value in tooltips
                    }
                }
            }
        },
        layout: {
            padding: {
                left: 50, // Add padding left for spacing
                right: 50, // Add padding right for spacing
            }
        }
    }
});


    // Bar chart for Death comparison between Men and Women by Age Group
    const comparisonBarChart = new Chart(document.getElementById('comparisonBarChart'), {
    type: 'bar',
    data: {
        labels: <?php echo $ageGroupLabels; ?>, // Age groups
        datasets: [
            {
                label: 'Men',
                data: <?php echo json_encode(array_map(function($value) { return -$value; }, array_values($ageGroupCountsMen))); ?>, // Negative values to extend left
                backgroundColor: '#1E90FF',
                borderRadius: 10, // Rounded corners for the bars
                barThickness: 18, // Bar thickness
                categoryPercentage: 1.0, // Ensure the bars are clustered
                barPercentage: 0.4, // Space between bars (lower value increases space)
                borderRadius: 10, // Rounded corners
            },
            {
                label: 'Women',
                data: <?php echo $womenData; ?>, // Counts of women in each age group for the current month
                backgroundColor: '#FF69B4',
                borderRadius: 10, // Rounded corners for the bars
                barThickness: 18, // Bar thickness
                categoryPercentage: 1.0, // Ensure the bars are clustered
                barPercentage: 0.4, // Space between bars
                borderRadius: 10, // Rounded corners
            }
        ]
    },
    options: {
        responsive: true,
        indexAxis: 'y', // Horizontal bar chart
        scales: {
            x: {
                beginAtZero: true, // Ensure both bars start at zero
                ticks: {
                    min: -Math.max(...<?php echo $menData; ?>), // Dynamic min value based on Men data
                    max: Math.max(...<?php echo $womenData; ?>), // Dynamic max value based on Women data
                },
                grid: {
                    drawBorder: false, // Remove the vertical grid lines
                    display: true // Show grid lines for alignment
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false, // Hide the horizontal grid lines
                    display: true // Show grid lines for alignment
                },
                ticks: {
                    // Custom ticks callback to center the labels between the two bars
                    callback: function(value, index, values) {
                        return this.getLabelForValue(value); // Display original labels
                    }
                }
            }
        },
        elements: {
            bar: {
                borderWidth: 0, // Remove borders for cleaner appearance
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        // Display positive values in tooltips
                        return context.dataset.label + ': ' + Math.abs(context.raw); // Always display positive values
                    }
                }
            }
        },
        layout: {
            padding: {
                left: 20, // Padding to create space on the left
                right: 20, // Padding to create space on the right
            }
        },
        barPercentage: 0.5, // More space between bars
        categoryPercentage: 1.0, // Ensure that the labels are centered between bars
    }
});

</script>

</body>
</html>
