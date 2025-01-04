<?php
session_start();
require 'db.php';

// Check if the logged-in user is the main admin
if ($_SESSION['role'] !== 'main_admin') {
    header("Location: index.php");
    exit();
}

// Function to get all users from the database
function get_all_users() {
    $conn = get_db_connection();
    $stmt = $conn->query("SELECT id, userName, role FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to update the user's role
function update_user_role($id, $new_role) {
    $conn = get_db_connection();
    $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
    $stmt->execute(['role' => $new_role, 'id' => $id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update user role
    $id = $_POST['user_id'];
    $new_role = $_POST['role'];
    update_user_role($id, $new_role);
    header("Location: change_roles.php"); // Refresh the page to see the changes
    exit();
}

$users = get_all_users();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta Name_of_the_filler="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Roles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body {
            background-color: rgba(87, 68, 118, 0.25); /* Purple color with 25% opacity */
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 800px;
        }
        .btn-rounded {
            border-radius: 50px;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
}
    </style>
</head>
<body> 
        
        
    <div class="container">
       <form method="POST" action="logout.php" class="mt-4">
            <button type="submit" class="btn btn-danger btn-lg rounded-pill shadow">Logout</button>
        </form>
        <h2 class="text-center mb-4">Change User Roles</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>UserName</th>
                    <th>Current Role</th>
                    <th>New Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['userName']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <form method="POST" action="change_roles.php">
                            <input type="hidden" Name="user_id" value="<?php echo $user['id']; ?>">
                            <select Name="role" class="form-select" required>
                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="main_admin" <?php echo $user['role'] == 'main_admin' ? 'selected' : ''; ?>>Main Admin</option>
                                <option value="second_admin" <?php echo $user['role'] == 'second_admin' ? 'selected' : ''; ?>>Second Admin</option>
                            </select>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-primary btn-rounded">Update Role</button>
                        <button type="button" class="btn btn-delete btn-rounded delete-user">Delete</button>
                    </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle delete user button click
            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', function() {
                    // Confirm the deletion
                    if (confirm("Are you sure you want to remove this user from the table?")) {
                        // Remove the parent row of the delete button
                        this.closest('tr').remove();
                    }
                });
            });
        });
    </script>
</body>
</html>
