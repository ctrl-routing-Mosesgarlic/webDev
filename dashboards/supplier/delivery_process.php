<?php
include 'connection.php';
include 'session.php';

// Only supplier role allowed
if ($_SESSION['role'] !== 'supplier') { header('Location: dashboard.php'); exit; }

$item = $_POST['item'];
$quantity = (int)$_POST['quantity'];
$supplierId = $_SESSION['user_id'];

$sql = "INSERT INTO deliveries (supplier_id, item, quantity) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $supplierId, $item, $quantity);
$stmt->execute();

// optional: update stock table
$u = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE item = ?");
$u->bind_param("is", $quantity, $item);
$u->execute();

header('Location: dashboards/supplier_dashboard.php?success=1');
exit;
