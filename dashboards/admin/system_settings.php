<?php
/**
 * Admin System Settings
 * This page allows administrators to configure system settings
 */

// Include session management
require_once '../../session.php';
require_once '../../connection.php';

// Check if user is logged in and has admin role
if (!isLoggedIn() || getSessionVar('role') !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: ../../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process settings update
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        // In a real application, you would validate and save these settings to a database
        // For now, we'll just show a success message
        $message = "System settings updated successfully.";
        $message_type = "success";
    }
}

// Fetch current settings (in a real application, these would come from a database)
$settings = [
    'site_name' => 'W&SM System',
    'site_description' => 'Workforce & System Management',
    'admin_email' => 'admin@wsm-system.com',
    'items_per_page' => 10,
    'enable_registration' => true,
    'enable_password_reset' => true,
    'session_timeout' => 30, // minutes
    'maintenance_mode' => false,
    'timezone' => 'Africa/Nairobi',
    'date_format' => 'Y-m-d H:i:s',
    'smtp_host' => 'smtp.example.com',
    'smtp_port' => 587,
    'smtp_encryption' => 'tls'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .settings-form {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .form-section h3 {
            margin-bottom: 15px;
            color: #344767;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="number"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group .checkbox-label {
            display: flex;
            align-items: center;
        }
        .form-group .checkbox-label input[type="checkbox"] {
            margin-right: 10px;
        }
        .btn-container {
            margin-top: 20px;
            text-align: right;
        }
        .btn-save {
            padding: 10px 20px;
            background-color: #4a6fdc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-save:hover {
            background-color: #3a5bb9;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .message.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 5px solid #4caf50;
        }
        .message.error {
            background-color: #ffebee;
            color: #b71c1c;
            border-left: 5px solid #f44336;
        }
        .settings-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #8898aa;
            position: relative;
        }
        .tab-btn.active {
            color: #4a6fdc;
        }
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #4a6fdc;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>Administration</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="user_management.php"><i class="fas fa-users"></i> User Management</a></li>
                    <li class="active"><a href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="database.php"><i class="fas fa-database"></i> Database</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>System Settings</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="settings-tabs">
                <button class="tab-btn active" onclick="openTab('general')">General</button>
                <button class="tab-btn" onclick="openTab('security')">Security</button>
                <button class="tab-btn" onclick="openTab('email')">Email</button>
                <button class="tab-btn" onclick="openTab('appearance')">Appearance</button>
            </div>
            
            <form class="settings-form" method="POST">
                <input type="hidden" name="action" value="update_settings">
                
                <!-- General Settings Tab -->
                <div id="general" class="tab-content active">
                    <div class="form-section">
                        <h3>General Settings</h3>
                        
                        <div class="form-group">
                            <label for="site_name">Site Name:</label>
                            <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">Site Description:</label>
                            <input type="text" id="site_description" name="site_description" value="<?php echo htmlspecialchars($settings['site_description']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Admin Email:</label>
                            <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="timezone">Timezone:</label>
                            <select id="timezone" name="timezone">
                                <option value="Africa/Nairobi" <?php echo $settings['timezone'] === 'Africa/Nairobi' ? 'selected' : ''; ?>>Africa/Nairobi</option>
                                <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                <option value="America/New_York" <?php echo $settings['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
                                <option value="Europe/London" <?php echo $settings['timezone'] === 'Europe/London' ? 'selected' : ''; ?>>Europe/London</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_format">Date Format:</label>
                            <select id="date_format" name="date_format">
                                <option value="Y-m-d H:i:s" <?php echo $settings['date_format'] === 'Y-m-d H:i:s' ? 'selected' : ''; ?>>YYYY-MM-DD HH:MM:SS</option>
                                <option value="m/d/Y H:i:s" <?php echo $settings['date_format'] === 'm/d/Y H:i:s' ? 'selected' : ''; ?>>MM/DD/YYYY HH:MM:SS</option>
                                <option value="d/m/Y H:i:s" <?php echo $settings['date_format'] === 'd/m/Y H:i:s' ? 'selected' : ''; ?>>DD/MM/YYYY HH:MM:SS</option>
                                <option value="F j, Y, g:i a" <?php echo $settings['date_format'] === 'F j, Y, g:i a' ? 'selected' : ''; ?>>Month DD, YYYY, HH:MM AM/PM</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="items_per_page">Items Per Page:</label>
                            <input type="number" id="items_per_page" name="items_per_page" value="<?php echo htmlspecialchars($settings['items_per_page']); ?>" min="5" max="100">
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                Enable Maintenance Mode
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings Tab -->
                <div id="security" class="tab-content">
                    <div class="form-section">
                        <h3>Security Settings</h3>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_registration" <?php echo $settings['enable_registration'] ? 'checked' : ''; ?>>
                                Enable User Registration
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="enable_password_reset" <?php echo $settings['enable_password_reset'] ? 'checked' : ''; ?>>
                                Enable Password Reset
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="session_timeout">Session Timeout (minutes):</label>
                            <input type="number" id="session_timeout" name="session_timeout" value="<?php echo htmlspecialchars($settings['session_timeout']); ?>" min="5" max="1440">
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn-save" onclick="resetSecuritySettings()">Reset Security Settings to Default</button>
                        </div>
                    </div>
                </div>
                
                <!-- Email Settings Tab -->
                <div id="email" class="tab-content">
                    <div class="form-section">
                        <h3>Email Settings</h3>
                        
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host:</label>
                            <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($settings['smtp_host']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port:</label>
                            <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($settings['smtp_port']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_encryption">SMTP Encryption:</label>
                            <select id="smtp_encryption" name="smtp_encryption">
                                <option value="none" <?php echo $settings['smtp_encryption'] === 'none' ? 'selected' : ''; ?>>None</option>
                                <option value="ssl" <?php echo $settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="tls" <?php echo $settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_username">SMTP Username:</label>
                            <input type="text" id="smtp_username" name="smtp_username" value="">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password:</label>
                            <input type="password" id="smtp_password" name="smtp_password" value="">
                        </div>
                        
                        <div class="form-group">
                            <button type="button" class="btn-save" onclick="testEmailSettings()">Test Email Settings</button>
                        </div>
                    </div>
                </div>
                
                <!-- Appearance Settings Tab -->
                <div id="appearance" class="tab-content">
                    <div class="form-section">
                        <h3>Appearance Settings</h3>
                        
                        <div class="form-group">
                            <label for="theme">Theme:</label>
                            <select id="theme" name="theme">
                                <option value="default">Default</option>
                                <option value="dark">Dark Mode</option>
                                <option value="light">Light Mode</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="primary_color">Primary Color:</label>
                            <input type="text" id="primary_color" name="primary_color" value="#4a6fdc">
                        </div>
                        
                        <div class="form-group">
                            <label for="secondary_color">Secondary Color:</label>
                            <input type="text" id="secondary_color" name="secondary_color" value="#5a8dee">
                        </div>
                        
                        <div class="form-group">
                            <label for="logo">Logo:</label>
                            <input type="file" id="logo" name="logo">
                        </div>
                        
                        <div class="form-group">
                            <label for="favicon">Favicon:</label>
                            <input type="file" id="favicon" name="favicon">
                        </div>
                    </div>
                </div>
                
                <div class="btn-container">
                    <button type="submit" class="btn-save">Save Settings</button>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        // Tab functionality
        function openTab(tabName) {
            // Hide all tab contents
            var tabContents = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Deactivate all tab buttons
            var tabButtons = document.getElementsByClassName('tab-btn');
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            
            // Show the selected tab content and activate the button
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        // Reset security settings
        function resetSecuritySettings() {
            if (confirm('Are you sure you want to reset security settings to default values?')) {
                document.getElementById('session_timeout').value = '30';
                document.getElementsByName('enable_registration')[0].checked = true;
                document.getElementsByName('enable_password_reset')[0].checked = true;
                alert('Security settings have been reset to default values. Click "Save Settings" to apply changes.');
            }
        }
        
        // Test email settings
        function testEmailSettings() {
            alert('This would send a test email using the provided SMTP settings. Feature not implemented in this demo.');
        }
    </script>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>
