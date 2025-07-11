<?php
include '../session.php';
include '../connection.php';
if ($_SESSION['role'] !== 'supplier') { header('Location: ../dashboard.php'); exit; }
// query stock
$stockRes = $conn->query("SELECT * FROM stock");
// query past deliveries
$delivRes = $conn->prepare("SELECT item, quantity, delivered_at FROM deliveries WHERE supplier_id = ?");
$delivRes->bind_param("i", $_SESSION['user_id']);
$delivRes->execute();
$delivRes = $delivRes->get_result();
?>
<!doctype html>
<html>
<head><link rel="stylesheet" href="../static/supplier.css"></head>
<body>
<h2>Supplier Dashboard</h2>
<section>
  <h3>Current Stock</h3>
  <table>
    <tr><th>Item</th><th>Qty</th></tr>
    <?php while($r = $stockRes->fetch_assoc()): ?>
      <tr><td><?=htmlspecialchars($r['item'])?></td><td><?= (int)$r['quantity']; ?></td></tr>
    <?php endwhile; ?>
  </table>
</section>
<section>
  <h3>Deliver Stock</h3>
  <?php if(isset($_GET['success'])) echo "<p>Delivery recorded!</p>"; ?>
  <form action="../delivery_process.php" method="post">
    <input name="item" placeholder="Item name" required />
    <input name="quantity" type="number" placeholder="Quantity" required />
    <button type="submit">Submit</button>
  </form>
</section>
<section>
  <h3>Your Past Deliveries</h3>
  <table>
    <tr><th>Item</th><th>Qty</th><th>Date</th></tr>
    <?php while($d = $delivRes->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($d['item'])?></td>
        <td><?= (int)$d['quantity']; ?></td>
        <td><?= $d['delivered_at']; ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</section>
</body>
</html>
