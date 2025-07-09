<?php
/**
 * View Users File
 * This file retrieves and displays all registered users from the database
 */

// Include database connection
require_once 'connection.php';

// SQL query to select all users
$sql = "SELECT id, fullname, email, gender, role, registration_date FROM users ORDER BY registration_date DESC";
$result = mysqli_query($conn, $sql);

echo "result";

// Check if query was successful
if (!$result) {
    die("<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #ffebee; border-left: 5px solid #f44336; color: #b71c1c;'>
        <h3>Database Error</h3>
        <p>Error retrieving users: " . mysqli_error($conn) . "</p>
        <p>Please make sure the 'users' table exists in the database.</p>
        </div>");
}

// Get all users as an associative array
// Using while loop to fetch all rows since we don't know how many rows we have for as long as the users keeps on registering
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

$row = mysqli_fetch_assoc($result);
echo "$row";

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Users - W&SM System</title>
    <link rel="stylesheet" href="/static/css/style.css">
    <style>
        .users-container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .users-table th, .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .users-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
        }
        
        .users-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .no-users {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
            cursor: pointer;
            transition: all 0.3s ease;
            color: black;
        }
    </style>
</head>
<body>
    <div class="users-container">
        <h2>Registered Users</h2>
        
        <?php if (empty($users)): ?>
            <p class="no-users">No users have registered yet.</p>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Role</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['gender']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo htmlspecialchars($user['registration_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <a href="templates/auth/sign-up.html" class="back-link">Back to Registration</a>
    </div>
</body>
</html>
