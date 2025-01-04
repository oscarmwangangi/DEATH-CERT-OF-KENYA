<?php
session_start();
if ($_SESSION['role'] !== 'main_admin' && $_SESSION['role'] !== 'second_admin') {
    header("Location: index.php");
    exit();
}

require 'db.php';

$showModal = false;
$modalTitle = "";
$modalBody = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userName = isset($_POST['userName']) ? trim($_POST['userName']) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;
    $role = isset($_POST['role']) ? trim($_POST['role']) : null;

    // Validate form data
    if ($userName && $password && $role) {
        $conn = get_db_connection();

        // Check if the userName already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE userName = :userName");
        $stmt->bindParam(':userName', $userName);
        $stmt->execute();
        $userExists = $stmt->fetchColumn();

        if ($userExists) {
            $modalTitle = "Registration Error";
            $modalBody = "Username already exists. Please choose a different username.";
            $showModal = true; // Show modal if user already exists
        } else {
            // Check if the second admin is trying to register a main admin
            if ($_SESSION['role'] == 'second_admin' && $role == 'main_admin') {
                $modalTitle = "Registration Error";
                $modalBody = "Second admin cannot register a main admin.";
                $showModal = true; // Show modal if second admin tries to register a main admin
            } else {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO users (userName, password, role) VALUES (:userName, :password, :role)");
                $stmt->bindParam(':userName', $userName);
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':role', $role);
                $stmt->execute();

                $modalTitle = "Registration Successful";
                $modalBody = "The user has been successfully registered.";
                $showModal = true; // Show modal on successful registration
            }
        }
    } else {
        $modalTitle = "Form Error";
        $modalBody = "All fields are required.";
        $showModal = true; // Show modal if form fields are missing
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta Name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            background-color: rgba(87, 68, 118, 0.25); /* Purple color with 25% opacity */
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .form-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            width: 100%;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="text-center mb-4">Register User</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="userName" class="form-label">UserName:</label>
                <input type="text" id="userName" Name="userName" class="form-control" required placeholder="Enter username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" Name="password" class="form-control" required placeholder="Enter password">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select id="role" Name="role" class="form-select" required>
                    <?php if ($_SESSION['role'] == 'main_admin'): ?>
                        <option value="main_admin">Main Admin</option>
                    <?php endif; ?>
                    <option value="second_admin">Second Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Register</button>
            </div>
        </form>
        <a href="./index.php" class="btn btn-dark btn-lg shadow mt-1">Back</a>
    </div>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resultModalLabel"><?php echo htmlspecialchars($modalTitle); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo htmlspecialchars($modalBody); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>

    <!-- Show Modal if needed -->
    <?php if ($showModal): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('resultModal'), {
                keyboard: false
            });
            myModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
