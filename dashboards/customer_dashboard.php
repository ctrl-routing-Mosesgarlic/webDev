<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard - WMS</title>
    <style>
        /* General page styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eef1f5;
            display: flex;
        }

        /* Sidebar navigation */
        .sidebar {
            width: 220px;
            background-color: #1f2a36;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
            position: fixed;
        }

        .sidebar h2 {
            font-size: 22px;
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        /* Main content section */
        .main-content {
            margin-left: 240px;
            padding: 30px;
            flex: 1;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 2px solid #fff;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 14px;
            background-color: #e74c3c;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        h2, h3 {
            color: #2c3e50;
        }

        /* Product and order tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .success-msg {
            color: green;
            font-weight: bold;
        }

        .empty-msg {
            color: #888;
            font-style: italic;
        }

        .order-form input[type='number'] {
            width: 60px;
            padding: 4px;
            margin-right: 6px;
        }
    </style>
</head>
<body>

<!-- Sidebar with static customer info -->
<div class="sidebar">
    <h2>Customer</h2>
    <img src="images/customer1.jpg" alt="Customer" class="profile-pic">
    <p>John Doe</p>
    <a href="#">Dashboard</a>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<!-- Main dashboard content -->
<div class="main-content">
    <?php
    include 'connection.php';

    // Handle order submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];

        $product_sql = "SELECT price FROM products WHERE product_id = '$product_id'";
        $product_result = mysqli_query($conn, $product_sql);

        if ($product_result && mysqli_num_rows($product_result) > 0) {
            $product = mysqli_fetch_assoc($product_result);
            $price = $product['price'];
            $total_price = $price * $quantity;

            // Simulated customer_id for demo purposes
            $customer_id = 1;

            // Insert order into database
            $insert_sql = "INSERT INTO orders (customer_id, product_id, quantity, total_price, created_at)
                           VALUES ('$customer_id', '$product_id', '$quantity', '$total_price', NOW())";

            if (mysqli_query($conn, $insert_sql)) {
                echo "<p class='success-msg'>Order placed successfully!</p>";
            } else {
                echo "<p class='empty-msg'>Failed to place order: " . mysqli_error($conn) . "</p>";
            }
        } else {
            echo "<p class='empty-msg'>Invalid product selected.</p>";
        }
    }

    // Display available products
    $sql = "SELECT * FROM products";
    $result = mysqli_query($conn, $sql);

    echo "<h3>Available Products</h3>";
    echo "<table>
            <tr>
                <th>Product Name</th>
                <th>Price (Ksh)</th>
                <th>Available Quantity</th>
                <th>Action</th>
            </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['product_name']}</td>
                <td>{$row['price']}</td>
                <td>{$row['quantity']}</td>
                <td>
                    <form method='post' class='order-form'>
                        <input type='hidden' name='product_id' value='{$row['product_id']}'>
                        <input type='number' name='quantity' min='1' max='{$row['quantity']}' required>
                        <input type='submit' value='Order'>
                    </form>
                </td>
              </tr>";
    }

    echo "</table>";

    // Display last few orders
    echo "<h3>Recent Orders</h3>";
    $orderQuery = "SELECT o.*, p.product_name FROM orders o JOIN products p ON o.product_id = p.product_id WHERE o.customer_id = 1 ORDER BY o.order_id DESC LIMIT 3";
    $orderResult = mysqli_query($conn, $orderQuery);

    if (mysqli_num_rows($orderResult) > 0) {
        echo "<table>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price (Ksh)</th>
                    <th>Date</th>
                </tr>";
        while ($order = mysqli_fetch_assoc($orderResult)) {
            echo "<tr>
                    <td>{$order['product_name']}</td>
                    <td>{$order['quantity']}</td>
                    <td>{$order['total_price']}</td>
                    <td>{$order['created_at']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='empty-msg'>No past orders found.</p>";
    }
    ?>
</div>

</body>
</html>
