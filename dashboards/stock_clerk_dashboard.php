<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Stock Clerk Dashboard</title>
  <link rel="stylesheet" href="clerk_dashboard.css">
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <h2>Clerk Panel</h2>
      <ul>
        <li><a href="#generate_report.php" class="nav-link">📦 View Stock</a></li>
        <li><a href="#stock_transaction.php" class="nav-link">✏️ Update Stock</a></li>
        <li><a href="logout.php">🚪 Logout</a></li>
      </ul>
    </aside>

    <main class="main-content">
      <section id="view-stock" class="content-section active">
        <h2>View Stock Information</h2>
        <p>This section displays a list of current stock items.</p>
        <!-- Table goes here -->
      </section>

      <section id="update-stock" class="content-section">
        <h2>Update Stock Levels</h2>
        <form action="update_stock.php" method="POST">
          <label for="item">Item Name:</label>
          <input type="text" id="item" name="item" required>
          <label for="quantity">New Quantity:</label>
          <input type="number" id="quantity" name="quantity" required>
          <button type="submit">Update</button>
        </form>
      </section>

      <section id="generate-report" class="content-section">
        <h2>Generate Report</h2>
        <form action="generate_report.php" method="POST">
          <label for="reportType">Select Report Type:</label>
          <select id="reportType" name="reportType">
            <option value="full">Full Inventory</option>
            <option value="low">Low Stock</option>
          </select>
          <button type="submit">Generate</button>
        </form>
      </section>
    </main>
  </div>

  <script>
    // JavaScript to switch between tabs
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.content-section');

    navLinks.forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        sections.forEach(s => s.classList.remove('active'));
        document.querySelector(link.getAttribute('href')).classList.add('active');
      });
    });
  </script>
</body>
</html>
