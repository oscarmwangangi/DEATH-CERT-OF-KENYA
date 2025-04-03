<?php
session_start();
require 'db.php'; // Ensure you have the correct path to your config file

// Check authentication
if ($_SESSION['role'] !== 'main_admin') {
    header("Location: index.php");
    exit();
}
$conn = get_db_connection();

// Get filter parameters
$district = $_GET['district'] ?? '';
$cause = $_GET['cause'] ?? '';
$date_start = $_GET['date_start'] ?? '';
$date_end = $_GET['date_end'] ?? '';
$death_in_the = $_GET['death_in_the'] ?? '';  // Get the death_in_the search term

$user_id = $_SESSION['user_id']; 
$user_query = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user_result = $user_query->fetch(PDO::FETCH_ASSOC);
$user_name = $user_result['username'] ?? '';

// Build base query
$query = "SELECT * FROM deathcertificate_information WHERE 1=1";
$params = [];
$types = '';

// Add filter for Death_in_the if provided
if (!empty($death_in_the)) {
    $query .= " AND Death_in_the LIKE :death_in_the";
    $params[':death_in_the'] = "%" . $death_in_the . "%";  // Use LIKE for partial match
}

// Add other filters (District, Cause, Date range)
if (!empty($district)) {
    $query .= " AND District_in_the = :district";
    $params[':district'] = $district;
}

if (!empty($cause)) {
    $query .= " AND Cause_of_Death = :cause";
    $params[':cause'] = $cause;
}

if (!empty($date_start) && !empty($date_end)) {
    $query .= " AND Date_of_death BETWEEN :date_start AND :date_end";
    $params[':date_start'] = $date_start;
    $params[':date_end'] = $date_end;
}

// Prepare the SQL statement
$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export functionality
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="death_records.csv"');
    
    $output = fopen('php://output', 'w');
    // Write CSV headers
    fputcsv($output, array_keys($result[0]));
    
    foreach ($result as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Get statistics
function getStat($conn, $column, $func = 'COUNT') {
    global $query;
    $statQuery = str_replace('*', "$func($column) as stat", $query);
    $stmt = $conn->prepare($statQuery);
    $stmt->execute($GLOBALS['params']);
    return $stmt->fetch(PDO::FETCH_ASSOC)['stat'];
}

$totalDeaths = getStat($conn, 'id');
$avgAge = getStat($conn, 'Age_of_Deceased', 'AVG');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="font-sans bg-[#574476] bg-opacity-25 text-white">
    <!-- Loading Spinner -->
    <div id="loading" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
            <i class="fas fa-spinner fa-spin fa-3x text-white"></i>
        </div>
    </div>

    <!-- User Indicator -->
    <div class="absolute top-4 right-4">
        Logged in as: <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>
        <a href="logout.php" class="text-blue-500 ml-2">Logout</a>
    </div>

    <form id="filterForm" onsubmit="showLoading()">
    <div class="min-h-screen flex">
        <!-- Updated Filters -->
        <div class="w-64 p-4 border-r bg-[#405189] bg-opacity-80 shadow-[0px_10px_20px_rgba(0,0,0,0.1)]">
            <h2 class="text-xl font-bold mb-4">Filters</h2>

            <!-- Search by Death_in_the -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Death In The</label>
                <input type="text" name="death_in_the" value="<?= htmlspecialchars($death_in_the ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                       class="w-full  p-4 rounded shadow" placeholder="Search by Death In The">
            </div>

            <!-- Date Range Picker -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Date Range</label>
                <input type="text" id="dateRange" name="date_range" 
                       class="w-full  p-4 rounded shadow" 
                       data-range="true" data-date-format="Y-m-d">
            </div>

            <!-- District Filter -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">District</label>
                <select name="district" class="w-full  p-4 rounded shadow">
                    <option value="">All Districts</option>
                    <?php
                    $districts = $conn->query("SELECT DISTINCT District_in_the FROM deathcertificate_information");
                    while ($row = $districts->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <option <?= $district == $row['District_in_the'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['District_in_the']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Cause Filter -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Cause of Death</label>
                <select name="cause" class="w-full text-gray-800 p-4 rounded shadow">
                    <option value="">All Causes</option>
                    <?php
                    $causes = $conn->query("SELECT DISTINCT Cause_of_Death FROM deathcertificate_information");
                    while ($row = $causes->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <option <?= $cause == $row['Cause_of_Death'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['Cause_of_Death']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">
                Apply Filters
            </button>
            <a href="?export=1" class="mt-2 block w-full bg-green-500 text-white p-2 rounded text-center">
                Export CSV
            </a>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 text-gray-600">
                <!-- Updated cards with real data -->
                <?php
                $cards = [
                    'Total Deaths' => $totalDeaths,
                    'Average Age' => $avgAge !== null ? number_format($avgAge, 1) : 'N/A',  // Handle null
                    'Top Cause' => getTopCause($conn),
                    'Gender Ratio' => getGenderRatio($conn)
                ];
                
                foreach ($cards as $title => $value): ?>
                <div class="bg-[#405189] bg-opacity-10 p-4 rounded shadow">
                    <h3 class="text-gray-800 text-xl font-bold"><?= $title ?></h3>
                    <p class="text-lg font-semibold"><?= $value ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Charts with real data -->
                <div class="bg-[#405189] bg-opacity-10 p-4 rounded shadow">
                    <h3 class="mb-4 font-semibold text-gray-800">Deaths by Month</h3>
                    <canvas id="monthlyChart" data-stats='<?= json_encode(getMonthlyStats($conn)) ?>'></canvas>
                </div>
                
                <!-- Other charts similarly -->
            </div>
            <!-- Data Table -->
            <div class="mt-8 bg-[#405189] bg-opacity-10 p-4 rounded shadow text-gray-800">
                <h3 class="text-xl font-bold mb-4">Death Records</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Date of Death</th>
                                <th class="px-4 py-2">District</th>
                                <th class="px-4 py-2">Cause</th>
                                <th class="px-4 py-2">Death In The</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row): ?>
                            <tr>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['Name_and_Surname_of_Deceased']) ?></td>
                                <td class="px-4 py-2"><?= $row['Date_of_death'] ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['District_in_the']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['Cause_of_Death']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($row['Death_in_the']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>


    <script>
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d"
        });
    </script>
        <script>
        // Initialize Date Range Picker
        flatpickr('#dateRange', {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: ["<?= $date_start ?>", "<?= $date_end ?>"]
        });

        // Loading State
        function showLoading() {
            document.getElementById('loading').classList.remove('hidden');
        }

        // Initialize Charts with real data
        const monthlyData = JSON.parse(document.getElementById('monthlyChart').dataset.stats);
        new Chart(document.getElementById('monthlyChart'), {
            type: 'bar',
            data: {
                labels: monthlyData.labels,
                datasets: [{
                    label: 'Deaths',
                    data: monthlyData.values,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html>
<?php
// Helper functions
function getTopCause($conn) {
    global $query;
    $causeQuery = str_replace('*', 'Cause_of_Death, COUNT(*) as count', $query)
               . " GROUP BY Cause_of_Death ORDER BY count DESC LIMIT 1";
    $stmt = $conn->prepare($causeQuery);
    $stmt->execute($GLOBALS['params']);
    return $stmt->fetch(PDO::FETCH_ASSOC)['Cause_of_Death'] ?? 'N/A';
}

function getGenderRatio($conn) {
    global $query;
    $maleQuery = str_replace('*', 'COUNT(*)', $query . " AND sex = 'Male'");
    $femaleQuery = str_replace('*', 'COUNT(*)', $query . " AND sex = 'Female'");
    
    $stmtMale = $conn->prepare($maleQuery);
    $stmtMale->execute($GLOBALS['params']);
    $male = $stmtMale->fetchColumn();
    
    $stmtFemale = $conn->prepare($femaleQuery);
    $stmtFemale->execute($GLOBALS['params']);
    $female = $stmtFemale->fetchColumn();
    
    return $female ? round($male/$female, 1).':1' : 'N/A';
}
function getMonthlyStats($conn) {
    global $query;
    $monthQuery = str_replace('*', 'MONTH(Date_of_death) as month, COUNT(*) as count', $query)
                . " GROUP BY MONTH(Date_of_death)";
    
    $stmt = $conn->prepare($monthQuery);
    $stmt->execute($GLOBALS['params']);
    
    $months = [];
    $counts = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $months[] = date('M', mktime(0, 0, 0, $row['month'], 1));
        $counts[] = $row['count'];
    }
    
    return ['labels' => $months, 'values' => $counts];
}
?>


