<?php
/**
 * List roles in a dropdown
 * This file generates a dropdown list of user roles for selection
 * Can be used directly in browser or included in other files
 */

// Include database connection
require_once 'connection.php';

// SQL query to select all roles
$sql = "SELECT id, role_name, description FROM role_users ORDER BY role_name ASC";
$result = mysqli_query($conn, $sql);

// Check if query was successful
if (!$result) {
    die("<div style='max-width: 600px; margin: 50px auto; padding: 20px; background-color: #ffebee; border-left: 5px solid #f44336; color: #b71c1c;'>
        <h3>Database Error</h3>
        <p>Error retrieving roles: " . mysqli_error($conn) . "</p>
        <p>Please make sure the 'role_users' table exists in the database.</p>
        <p>You may need to run the SQL script: <code>role_users_table.sql</code></p>
        </div>");
}

// Get all roles as an associative array
$roles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $roles[] = $row;
}

// Function to generate HTML for the dropdown
function generateRoleDropdown($selectedRole = '', $name = 'role', $id = 'role', $required = true) {
    global $roles;
    
    $requiredAttr = $required ? 'required' : '';
    $html = "<select id=\"$id\" name=\"$name\" $requiredAttr>\n";
    $html .= "\t<option value=\"\">Select Role</option>\n";
    
    foreach ($roles as $role) {
        $selected = ($selectedRole == $role['role_name']) ? 'selected' : '';
        $html .= "\t<option value=\"" . htmlspecialchars($role['role_name']) . "\" $selected>" . 
                htmlspecialchars(ucfirst($role['role_name'])) . "</option>\n";
    }
    
    $html .= "</select>";
    return $html;
}

// If this file is accessed directly, display the roles in a styled dropdown
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    echo "<!DOCTYPE html>\n";
    echo "<html lang=\"en\">\n";
    echo "<head>\n";
    echo "\t<meta charset=\"UTF-8\">\n";
    echo "\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    echo "\t<title>User Roles</title>\n";
    echo "\t<style>\n";
    echo "\t\tbody { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }\n";
    echo "\t\t.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }\n";
    echo "\t\th1 { color: #333; }\n";
    echo "\t\tselect { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }\n";
    echo "\t\t.role-table { width: 100%; border-collapse: collapse; margin-top: 20px; }\n";
    echo "\t\t.role-table th, .role-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }\n";
    echo "\t\t.role-table th { background-color: #f2f2f2; }\n";
    echo "\t</style>\n";
    echo "</head>\n";
    echo "<body>\n";
    echo "\t<div class=\"container\">\n";
    echo "\t\t<h1>Available User Roles</h1>\n";
    
    // Display dropdown
    echo "\t\t<label for=\"role\">Select a role:</label>\n";
    echo "\t\t" . generateRoleDropdown() . "\n";
    
    // Display table of roles
    echo "\t\t<h2>Role Details</h2>\n";
    echo "\t\t<table class=\"role-table\">\n";
    echo "\t\t\t<tr><th>ID</th><th>Role Name</th><th>Description</th></tr>\n";
    
    foreach ($roles as $role) {
        echo "\t\t\t<tr>\n";
        echo "\t\t\t\t<td>" . htmlspecialchars($role['id']) . "</td>\n";
        echo "\t\t\t\t<td>" . htmlspecialchars(ucfirst($role['role_name'])) . "</td>\n";
        echo "\t\t\t\t<td>" . htmlspecialchars($role['description']) . "</td>\n";
        echo "\t\t\t</tr>\n";
    }
    
    echo "\t\t</table>\n";
    echo "\t</div>\n";
    echo "</body>\n";
    echo "</html>\n";
}

// Close connection
mysqli_close($conn);
?>